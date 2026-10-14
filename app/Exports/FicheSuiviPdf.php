<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\ProjectNetworkLink;
use App\Services\Budget\ProjectBudget;
use App\Services\Fiche\LotBuckets;
use App\Support\PdfConcatenator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * "FICHE DE SUIVI DU PROJET", the document the DASS sends the ministry,
 * rendered from the same metrics the dashboards show.
 *
 * Section order and captions follow the scanned pages for identity and lot
 * tables. Every number comes from {@see ProjectBudget}, so a formula edited
 * in the admin page changes the document too.
 *
 * The scans are portrait for the narrative pages and landscape for the lot
 * tables. DomPDF has one paper size per document, so the two halves are
 * rendered separately and stitched by {@see PdfConcatenator}.
 */
class FicheSuiviPdf
{
    /** @deprecated Use {@see Project::phaseLabels()} — kept as alias for the PDF. */
    public const PHASES = [
        Project::PHASE_ETUDES => 'En études',
        Project::PHASE_LANCEMENT => 'En cours de lancement',
        Project::PHASE_REALISATION => 'En cours de réalisation',
        Project::PHASE_ARRET => "A l'arrêt",
        Project::PHASE_ACHEVE => 'Achevé',
        Project::PHASE_EN_EQUIPEMENT => "En cours d'équipement",
        Project::PHASE_ACHEVE_EQUIPE => 'Achevé et équipé',
    ];

    /** Eager loads for one render; keeps the fiche to a bounded query count. */
    private const RELATIONS = [
        'wilaya', 'daira', 'secteur', 'sousSecteur', 'chapitre', 'programmeType',
        'programme', 'establishment', 'land', 'designMissions', 'networkLinks',
        'aeRevaluations', 'aeEngagements', 'cpPayments',
        'physicalProgress', 'maturationItems', 'lots.contractor', 'lots.milestones',
    ];

    public function __construct(private readonly Project $project) {}

    public static function for(Project $project): self
    {
        return new self($project->loadMissing(self::RELATIONS));
    }

    /** The complete fiche: portrait narrative followed by landscape lot tables. */
    public function contents(): string
    {
        $data = $this->data();

        $narrative = Pdf::loadView('exports.fiche-suivi.narrative', $data)
            ->setPaper('a4', 'portrait')
            ->output();

        $lots = Pdf::loadView('exports.fiche-suivi.lots', $data)
            ->setPaper('a4', 'landscape')
            ->output();

        return PdfConcatenator::join([$narrative, $lots]);
    }

    public function filename(): string
    {
        $slug = Str::slug($this->project->short_label ?: $this->project->fip_id);

        return "fiche-suivi-{$slug}-".now()->format('Ymd').'.pdf';
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        $project = $this->project;
        $buckets = LotBuckets::for($project);

        return [
            'project' => $project,
            'budget' => ProjectBudget::for($project),
            'buckets' => $buckets,
            'phases' => Project::phaseLabels(),
            'awardedLots' => $this->awardedLots($buckets),
            'revaluationSlots' => $this->revaluationSlots(),
            'networks' => $this->networks(),
            'generatedAt' => now(),
        ];
    }

    /**
     * Lots that reached a marché, for the "engagement contrôleur financier /
     * ODS / délai" table — the only page that mixes the two awarded states.
     *
     * @return Collection<int, \App\Models\ProjectLot>
     */
    private function awardedLots(LotBuckets $buckets): Collection
    {
        return $buckets->lots('total_receptionne')
            ->concat($buckets->lots('total_3'))
            ->sortBy([['tranche', 'asc'], ['lot_number', 'asc']])
            ->values();
    }

    /**
     * The AP block prints five numbered réévaluation slots whether or not they
     * are used, so empty ones have to be materialised.
     *
     * @return list<array{sequence: int, year: int|null, amount: float|null, reference: string|null}>
     */
    private function revaluationSlots(): array
    {
        $approved = $this->project->aeRevaluations
            ->where('status', 'approved')
            ->keyBy('sequence');

        $slots = [];
        for ($sequence = 1; $sequence <= 5; $sequence++) {
            $row = $approved->get($sequence);

            $slots[] = [
                'sequence' => $sequence,
                'year' => $row?->year,
                'amount' => $row ? (float) $row->requested_amount_da : null,
                'reference' => $row?->decision_ref,
            ];
        }

        return $slots;
    }

    /**
     * All four réseaux in a fixed order, so a project with no row recorded
     * still prints the line with an empty answer rather than dropping it.
     *
     * @return list<array{label: string, connected: bool|null, motif: string|null}>
     */
    private function networks(): array
    {
        $recorded = $this->project->networkLinks->keyBy('network');
        $labels = ProjectNetworkLink::networkLabels();

        $rows = [];
        foreach (ProjectNetworkLink::networkCodes() as $network) {
            $link = $recorded->get($network);

            $rows[] = [
                'label' => $labels[$network] ?? $network,
                'connected' => $link?->connected,
                'motif' => $link?->motif,
            ];
        }

        return $rows;
    }
}
