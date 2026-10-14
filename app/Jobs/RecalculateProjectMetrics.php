<?php

namespace App\Jobs;

use App\Events\AlertRaised;
use App\Events\ProjectMetricsRecalculated;
use App\Models\Alert;
use App\Models\Project;
use App\Models\SpuScore;
use App\Services\Alerts\AlertEvaluator;
use App\Services\Budget\ProjectBudget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Recomputes one project's metrics, persists the SPU snapshot and the open
 * alerts, then broadcasts the result.
 *
 * Queued so a burst of writes does not block the request, but the events it
 * raises broadcast immediately once it runs.
 */
class RecalculateProjectMetrics implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Collapse repeated recomputes of the same project into one run. */
    public int $uniqueFor = 30;

    public function __construct(public readonly int $projectId) {}

    public function uniqueId(): string
    {
        return (string) $this->projectId;
    }

    public function handle(AlertEvaluator $evaluator): void
    {
        $project = Project::with([
            'aeEngagements', 'aeRevaluations', 'aeCpEnvelopes', 'cpPayments',
            'physicalProgress', 'maturationItems', 'lots', 'land',
        ])->find($this->projectId);

        if (! $project) {
            return;
        }

        $budget = ProjectBudget::for($project);
        $metrics = $budget->metrics();
        $trafficLight = $budget->trafficLight();

        $this->persistSpu($project, $budget, $trafficLight);

        foreach ($this->syncAlerts($project, $evaluator->evaluate($project, $budget)) as $alert) {
            event(AlertRaised::for($alert, $project->wilaya_id));
        }

        ProjectMetricsRecalculated::dispatch(
            $project->id,
            $project->wilaya_id,
            $this->broadcastableMetrics($metrics),
            $trafficLight,
        );
    }

    private function persistSpu(Project $project, ProjectBudget $budget, string $trafficLight): void
    {
        $breakdown = $budget->spuBreakdown();

        SpuScore::updateOrCreate(
            ['project_id' => $project->id, 'computed_at' => today()],
            [
                'score' => (int) round($budget->spu()),
                'cp_score' => (int) round($breakdown['cp']),
                'physical_score' => (int) round($breakdown['physical']),
                'delays_score' => (int) round($breakdown['delays']),
                'file_score' => (int) round($breakdown['file']),
                'traffic_light' => $trafficLight,
            ],
        );
    }

    /**
     * Writes the currently-holding alerts and closes the ones that no longer
     * do. Returns only the alerts that are newly open, so a dashboard is not
     * spammed with a notification on every recompute.
     *
     * @param  array<string, array{type: string, title: string, body: string}>  $current
     * @return list<Alert>
     */
    private function syncAlerts(Project $project, array $current): array
    {
        $raised = [];

        DB::transaction(function () use ($project, $current, &$raised) {
            $existing = Alert::where('project_id', $project->id)
                ->whereNotNull('signature')
                ->where('source', 'system')
                ->get()
                ->keyBy('signature');

            foreach ($current as $signature => $data) {
                $alert = $existing->get($signature);
                $wasOpen = $alert && $alert->resolved_at === null;

                $alert = Alert::updateOrCreate(
                    ['project_id' => $project->id, 'signature' => $signature],
                    $data + [
                        'source' => 'system',
                        'last_seen_at' => now(),
                        'resolved_at' => null,
                        'resolved_by' => null,
                    ],
                );

                if (! $wasOpen) {
                    $raised[] = $alert;
                }
            }

            // Conditions that no longer hold close themselves.
            $stale = $existing->keys()->diff(array_keys($current));

            if ($stale->isNotEmpty()) {
                Alert::where('project_id', $project->id)
                    ->whereIn('signature', $stale)
                    ->whereNull('resolved_at')
                    ->update(['resolved_at' => now()]);
            }
        });

        return $raised;
    }

    /**
     * Trims the payload to the numbers a dashboard renders; the full set stays
     * available through ProjectBudget on the next page load.
     *
     * @param  array<string, float|null>  $metrics
     * @return array<string, float|null>
     */
    private function broadcastableMetrics(array $metrics): array
    {
        $keys = [
            'ap_actuelle', 'pec', 'rar', 'taux_financier', 'reliquat_ae',
            'reste_a_engager', 'spu_total', 'ecart_physique_financier',
        ];

        return array_intersect_key($metrics, array_flip($keys));
    }
}
