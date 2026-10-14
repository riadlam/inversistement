<?php

namespace App\Services\Fiche;

use App\Models\Project;
use App\Models\ProjectLot;
use Illuminate\Support\Collection;

/**
 * Groups a project's lots into the four tables the fiche prints and sums each.
 *
 * Only row aggregation happens here. How the totals combine into
 * "reste à engager" is an expression in `calculation_rules`.
 */
class LotBuckets
{
    /** Bucket key => the lot state that feeds it. */
    public const BUCKETS = [
        'total_receptionne' => ProjectLot::STATE_RECEPTIONNE,
        'total_3' => ProjectLot::STATE_EN_COURS_REALISATION,
        'total_1' => ProjectLot::STATE_EN_COURS_LANCEMENT,
        'total_2' => ProjectLot::STATE_NON_LANCE,
    ];

    /** Table headings and total captions. */
    public const TABLES = [
        'total_receptionne' => ['title' => 'LOTS RECEPTIONNES (Réalisation + Equipement)', 'total' => 'TOTAL'],
        'total_3' => ['title' => 'LOTS EN COURS DE REALISATION (Travaux + Equipement)', 'total' => 'TOTAL'],
        'total_1' => ['title' => 'LOTS EN COURS DE LANCEMENT (Réalisation + Equipement)', 'total' => 'TOTAL'],
        'total_2' => ['title' => 'LOTS NON LANCES EN MATIERE DE TRAVAUX et Equipement', 'total' => 'TOTAL'],
    ];

    private Collection $lots;

    public function __construct(private readonly Project $project)
    {
        $this->lots = $project->relationLoaded('lots')
            ? $project->lots
            : $project->lots()->with('contractor', 'milestones')->get();
    }

    public static function for(Project $project): self
    {
        return new self($project);
    }

    /** The lots of one bucket, in tranche then lot order. */
    public function lots(string $bucket): Collection
    {
        $state = self::BUCKETS[$bucket] ?? null;

        if ($state === null) {
            return collect();
        }

        return $this->lots
            ->where('state', $state)
            ->sortBy([['tranche', 'asc'], ['lot_number', 'asc']])
            ->values();
    }

    /** Lots of a bucket split per tranche, as the fiche lays them out. */
    public function lotsByTranche(string $bucket): Collection
    {
        return $this->lots($bucket)->groupBy('tranche');
    }

    /**
     * Totals for the four lot-state buckets (dinars).
     *
     * @return array<string, float>
     */
    public function totals(): array
    {
        $totals = [];

        foreach (array_keys(self::BUCKETS) as $bucket) {
            $totals[$bucket] = round(
                $this->lots($bucket)->sum(fn (ProjectLot $lot) => $lot->effectiveAmountDa()),
                2
            );
        }

        return $totals;
    }

    public function total(string $bucket): float
    {
        return $this->totals()[$bucket] ?? 0.0;
    }

    public function count(string $bucket): int
    {
        return $this->lots($bucket)->count();
    }

    /** Tranche numbers present on the project, ascending. */
    public function tranches(): array
    {
        return $this->lots->pluck('tranche')->unique()->sort()->values()->all();
    }
}
