<?php

namespace App\Services\Calculation;

use App\Models\Project;
use App\Services\Fiche\LotBuckets;
use Illuminate\Support\Carbon;

/**
 * Turns a Project into the flat set of raw inputs the engine expects.
 *
 * This is the only place that knows which column or relation feeds which
 * variable; the arithmetic on top of them lives in `calculation_rules`.
 */
class ProjectVariableResolver
{
    /** Average days per month, for converting a contractual duration. */
    private const DAYS_PER_MONTH = 30.436875;

    /** @return array<string, float|int> */
    public function resolve(Project $project): array
    {
        return array_merge(
            $this->money($project),
            $this->execution($project),
            $this->schedule($project),
            $this->lots($project),
            $this->land($project),
        );
    }

    /** @return array<string, float> */
    private function money(Project $project): array
    {
        $apInitiale = (float) ($project->ae_da ?? 0);

        $reevaluations = $project->relationLoaded('aeRevaluations')
            ? $project->aeRevaluations->where('status', 'approved')->sum('requested_amount_da')
            : $project->aeRevaluations()->where('status', 'approved')->sum('requested_amount_da');

        $engagements = $project->relationLoaded('aeEngagements')
            ? $project->aeEngagements->sum('amount_da')
            : $project->aeEngagements()->sum('amount_da');

        $paiements = $project->relationLoaded('cpPayments')
            ? $project->cpPayments->where('status', 'validated')->sum('amount_da')
            : $project->cpPayments()->where('status', 'validated')->sum('amount_da');

        $envelopes = $project->relationLoaded('aeCpEnvelopes')
            ? $project->aeCpEnvelopes
            : $project->aeCpEnvelopes()->get();

        $aeOuvertes = (float) $envelopes->sum('ae_notified_da');
        $cpNotifies = (float) $envelopes->sum('cp_notified_da');

        // Fall back to the fiche values when no annual envelope is loaded yet.
        if ($aeOuvertes === 0.0) {
            $aeOuvertes = $apInitiale;
        }

        if ($cpNotifies === 0.0) {
            $cpNotifies = (float) ($project->cp_da ?? 0);
        }

        return [
            'ap_initiale' => $apInitiale,
            'total_reevaluations' => (float) $reevaluations,
            'cumul_engagements' => (float) $engagements,
            'cumul_paiements' => (float) $paiements,
            'cp_mandates' => (float) $paiements,
            'cp_consommes' => (float) $paiements,
            'cp_notifies' => $cpNotifies,
            'ae_ouvertes' => $aeOuvertes,
        ];
    }

    /** @return array<string, float|int> */
    private function execution(Project $project): array
    {
        $entries = $project->relationLoaded('physicalProgress')
            ? $project->physicalProgress
            : $project->physicalProgress()->get();

        $accepted = $entries->where('status', 'accepted');

        $items = $project->relationLoaded('maturationItems')
            ? $project->maturationItems
            : $project->maturationItems()->get();

        // "Dossier complet" means every check-list line is ticked; an empty
        // check-list is not a complete file.
        $complete = $items->isNotEmpty()
            && $items->every(fn ($item) => (bool) ($item->pivot->checked ?? false));

        return [
            'physique_pct' => (float) ($accepted->max('progress_pct') ?? 0),
            'equipement_pct' => (float) ($project->equipment_progress_pct ?? 0),
            'dossier_complet' => $complete ? 1 : 0,
        ];
    }

    /** @return array<string, float> */
    private function schedule(Project $project): array
    {
        $today = Carbon::today();
        $ods = $project->ods_date;
        $deadline = $project->deadline;

        $contractuel = 0.0;
        if ($project->project_duration_months) {
            $contractuel = round((int) $project->project_duration_months * self::DAYS_PER_MONTH);
        } elseif ($ods && $deadline) {
            $contractuel = (float) $ods->diffInDays($deadline, absolute: true);
        }

        $ecoules = $ods ? (float) max(0, $ods->diffInDays($today, absolute: false)) : 0.0;

        $restants = $deadline ? (float) $deadline->diffInDays($today, absolute: false) * -1 : 0.0;

        $lastSituation = $project->relationLoaded('physicalProgress')
            ? $project->physicalProgress->max('created_at')
            : $project->physicalProgress()->max('created_at');

        return [
            'delai_contractuel_jours' => $contractuel,
            'delai_reel_jours' => $ecoules,
            'jours_ecoules' => $ecoules,
            'jours_restants' => $restants,
            'jours_inscription_ods' => $ods && $project->created_at
                ? (float) max(0, $project->created_at->startOfDay()->diffInDays($ods, absolute: false))
                : 0.0,
            'jours_depuis_situation' => $lastSituation
                ? (float) Carbon::parse($lastSituation)->diffInDays($today, absolute: true)
                : 0.0,
        ];
    }

    /** @return array<string, float> */
    private function lots(Project $project): array
    {
        return LotBuckets::for($project)->totals();
    }

    /** @return array<string, float> */
    private function land(Project $project): array
    {
        $land = $project->relationLoaded('land') ? $project->land : $project->land()->first();

        return [
            'superficie_ha' => (float) ($land->superficie_ha ?? 0),
            'superficie_a' => (float) ($land->superficie_a ?? 0),
            'superficie_ca' => (float) ($land->superficie_ca ?? 0),
        ];
    }
}
