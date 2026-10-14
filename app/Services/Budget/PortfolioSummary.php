<?php

namespace App\Services\Budget;

use App\Models\Alert;
use App\Models\CpPayment;
use App\Models\Project;
use App\Models\SpuScore;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Portfolio-level roll-ups, scoped to what the signed-in role may see.
 *
 * Every per-project figure comes from the persisted SPU snapshot the recompute
 * job writes, so a dashboard reads one indexed table instead of evaluating
 * hundreds of expression trees on every page load.
 */
class PortfolioSummary
{
    private ?Collection $projects = null;

    public function __construct(private readonly ?User $user) {}

    public static function for(?User $user = null): self
    {
        return new self($user ?? auth()->user());
    }

    /** @return array{active_projects: int, plf_year: int, critical_alerts: int, avg_spu: int} */
    public function overviewStats(): array
    {
        $scores = $this->latestScores();

        return [
            'active_projects' => $this->projects()->whereNotIn('status', ['closed', 'draft'])->count(),
            'plf_year' => (int) now()->year,
            'critical_alerts' => $this->openAlerts()->where('type', 'critical')->count(),
            'avg_spu' => (int) round($scores->avg('score') ?? 0),
        ];
    }

    /** @return array{ae_open_m: float, ae_engaged_pct: int, cp_notified_m: float, cp_consumed_pct: int, rar_m: float} */
    public function financial(): array
    {
        $projects = $this->projects();

        $ae = (float) $projects->sum('ae_da');
        $cp = (float) $projects->sum('cp_da');

        $engaged = (float) CpPayment::whereIn('project_id', $projects->pluck('id'))->sum('amount_da');

        $paid = (float) CpPayment::whereIn('project_id', $projects->pluck('id'))
            ->where('status', 'validated')
            ->sum('amount_da');

        return [
            'ae_open_m' => round(Money::toMillions($ae), 2),
            'ae_engaged_pct' => $ae > 0 ? (int) round($engaged / $ae * 100) : 0,
            'cp_notified_m' => round(Money::toMillions($cp), 2),
            'cp_consumed_pct' => $cp > 0 ? (int) round($paid / $cp * 100) : 0,
            'rar_m' => round(Money::toMillions(max(0, $cp - $paid)), 2),
        ];
    }

    /** @return array{green: int, orange: int, red: int} */
    public function spuTrafficLights(): array
    {
        $counts = $this->latestScores()->countBy('traffic_light');

        return [
            'green' => (int) ($counts['green'] ?? 0),
            'orange' => (int) ($counts['orange'] ?? 0),
            'red' => (int) ($counts['red'] ?? 0),
        ];
    }

    /**
     * Open alerts, newest first, in the shape the alert rows render.
     *
     * @return list<array{id: int, project_id: int, type: string, title: string, body: string, time: string}>
     */
    public function alerts(int $limit = 50): array
    {
        $alerts = $this->openAlerts()->take($limit);

        // The project route is keyed by fip_id, not by the primary key.
        $fipIds = Project::whereIn('id', $alerts->pluck('project_id')->unique())->pluck('fip_id', 'id');

        return $alerts
            ->map(fn (Alert $alert) => [
                'id' => $alert->id,
                'project_id' => $alert->project_id,
                'fip_id' => $fipIds[$alert->project_id] ?? null,
                'type' => $alert->type,
                'title' => $alert->title,
                'body' => $alert->body,
                'time' => $alert->created_at?->diffForHumans() ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{
     *   avg_progress: int,
     *   on_track: int,
     *   at_risk: int,
     *   stalled: int,
     *   by_region: list<array{label: string, pct: int}>,
     *   projects: list<array{fip_id: string, title: string, wilaya: string, pct: int, status: string, deadline: string}>
     * }
     */
    public function physicalOverview(): array
    {
        $scores = $this->latestScores();

        return [
            'avg_progress' => (int) round($scores->avg('physical_score') !== null
                ? $scores->avg('physical_score') / 40 * 100
                : 0),
            'on_track' => $scores->where('traffic_light', 'green')->count(),
            'at_risk' => $scores->where('traffic_light', 'orange')->count(),
            'stalled' => $scores->where('traffic_light', 'red')->count(),
            'by_region' => $this->byWilaya()
                ->take(6)
                ->map(fn ($row) => ['label' => $row['label'], 'pct' => $row['progress']])
                ->values()
                ->all(),
            'projects' => $this->physicalProjectRows(),
        ];
    }

    /**
     * Per-operation physical rows, already limited to the signed-in périmètre.
     *
     * @return list<array{fip_id: string, title: string, wilaya: string, pct: int, status: string, deadline: string}>
     */
    private function physicalProjectRows(): array
    {
        return Project::query()
            ->visibleTo($this->user)
            ->with(['wilaya:id,name_fr'])
            ->whereNotIn('status', ['closed', 'draft'])
            ->orderBy('title_fr')
            ->get(['id', 'fip_id', 'title_fr', 'wilaya_id', 'deadline', 'status'])
            ->map(function (Project $project) {
                $budget = ProjectBudget::for($project);
                $light = $budget->trafficLight();

                return [
                    'fip_id' => $project->fip_id,
                    'title' => $project->title_fr,
                    'wilaya' => $project->wilaya?->name_fr ?? '—',
                    'pct' => (int) round($budget->tauxPhysique()),
                    'status' => match ($light) {
                        'green' => 'on_track',
                        'orange' => 'at_risk',
                        default => 'stalled',
                    },
                    'deadline' => $project->deadline?->format('Y-m-d') ?? '—',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Monthly AE / CP consumption for the current exercise.
     *
     * @return array{labels: list<string>, cp_m: list<float>, ae_engaged_m: list<float>}
     */
    public function monthlyConsumption(): array
    {
        $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        $rows = CpPayment::whereIn('project_id', $this->projects()->pluck('id'))
            ->whereYear('paid_at', now()->year)
            ->selectRaw('month(paid_at) as m, sum(amount_da) as total, sum(case when status = "validated" then amount_da else 0 end) as paid')
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $cp = [];
        $ae = [];

        foreach (range(1, 12) as $month) {
            $row = $rows->get($month);
            $cp[] = round(Money::toMillions($row->paid ?? 0), 2);
            $ae[] = round(Money::toMillions($row->total ?? 0), 2);
        }

        return ['labels' => $labels, 'cp_m' => $cp, 'ae_engaged_m' => $ae];
    }

    /**
     * AE / CP and operation counts per wilaya.
     *
     * @return Collection<int, array{label: string, ae_m: float, cp_m: float, operations: int, progress: int}>
     */
    public function byWilaya(): Collection
    {
        return Project::query()
            ->visibleTo($this->user)
            ->leftJoin('wilayas', 'wilayas.id', '=', 'projects.wilaya_id')
            ->groupBy('wilayas.id', 'wilayas.name_fr')
            ->select([
                DB::raw('coalesce(wilayas.name_fr, "—") as label'),
                DB::raw('sum(projects.ae_da) as ae'),
                DB::raw('sum(projects.cp_da) as cp'),
                DB::raw('count(*) as operations'),
            ])
            ->orderByDesc('ae')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'ae_m' => round(Money::toMillions($row->ae), 2),
                'cp_m' => round(Money::toMillions($row->cp), 2),
                'operations' => (int) $row->operations,
                'progress' => (int) round($row->ae > 0 ? min(100, $row->cp / $row->ae * 100) : 0),
            ]);
    }

    /**
     * Wilaya series shaped for the region charts the dashboard already draws.
     *
     * @return list<array{key: string, label: string, shortLabel: string, labels: list<string>, ae_m: list<float>, cp_m: list<float>, operations: list<int>}>
     */
    public function wilayaChartSeries(): array
    {
        $rows = $this->byWilaya();

        if ($rows->isEmpty()) {
            return [];
        }

        return [[
            'key' => 'portfolio',
            'label' => 'Portefeuille par wilaya',
            'shortLabel' => 'Par wilaya',
            'labels' => $rows->pluck('label')->all(),
            'ae_m' => $rows->pluck('ae_m')->all(),
            'cp_m' => $rows->pluck('cp_m')->all(),
            'operations' => $rows->pluck('operations')->all(),
        ]];
    }

    /** @return Collection<int, Project> */
    private function projects(): Collection
    {
        return $this->projects ??= Project::visibleTo($this->user)->get(['id', 'status', 'ae_da', 'cp_da', 'wilaya_id']);
    }

    /** One SPU row per project: the most recent snapshot. */
    private function latestScores(): Collection
    {
        return SpuScore::whereIn('project_id', $this->projects()->pluck('id'))
            ->orderByDesc('computed_at')
            ->get()
            ->unique('project_id');
    }

    /** @return Collection<int, Alert> */
    private function openAlerts(): Collection
    {
        return Alert::whereIn('project_id', $this->projects()->pluck('id'))
            ->open()
            ->latest()
            ->get();
    }
}
