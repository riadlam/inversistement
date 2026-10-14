<?php

namespace App\Exports;

use App\Models\Project;
use App\Services\Budget\ProjectBudget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * "ETAT PHYSIQUE ET FINANCIER GLOBAL DES OPERATIONS D'INVESTISSEMENT PUBLIC",
 * the DGB canevas, reproduced cell for cell from the ministry's workbook.
 *
 * The geometry is fixed: banners on rows 1, 4, 7 and 9, the unit note at U12,
 * a two-level header on rows 13–14, the vocabulary hints on row 15, data from
 * row 16, a SUBTOTAL row underneath and the two NB footnotes. Anything that
 * moves breaks the recipients' own macros, so the row numbers are constants
 * rather than computed offsets.
 */
class EtatGlobalExport implements FromArray, WithEvents, WithTitle
{
    private const FIRST_COLUMN = 'B';
    private const LAST_COLUMN = 'AD';
    private const CLOSURE_COLUMN = 'AE';

    private const ROW_REPUBLIQUE = 1;
    private const ROW_MINISTERE = 4;
    private const ROW_TITLE = 7;
    private const ROW_ARRETEE = 9;
    private const ROW_UNIT = 12;
    private const ROW_HEADER = 13;
    private const ROW_SUBHEADER = 14;
    private const ROW_HINTS = 15;
    private const ROW_FIRST_DATA = 16;

    /** The taux columns print an underscore when the figure is not available. */
    private const NOT_APPLICABLE = '_';

    /** column => the row 13 caption, verbatim. */
    private const HEADINGS = [
        'B' => 'Code W.',
        'C' => 'Réf. Décision Prog. (DP) émanant du MF',
        'D' => 'Wilaya/Ministère',
        'E' => 'Nbr. opér.',
        'F' => "Numéro et date d'individualisation de l'opération",
        'G' => "intitulé de l'opération (fr)",
        'H' => "intitulé de l'opération (ar)",
        'I' => 'Année de notification',
        'J' => 'TITRE',
        'K' => 'S/ Secteur',
        'L' => 'Struct.programme',
        'M' => 'Sous Programme',
        'N' => 'Type Programme',
        'O' => "Autorisation d'engagement (AE)",
        'S' => "Cumul d'engagements (2)",
        'T' => 'Cumul de paiements (3)',
        'U' => 'Programme en cours (PEC) (4)=(1)-(3)',
        'V' => "Taux d'avancement (%)",
        'X' => 'Date de réception-Provisoire',
        'Y' => 'Date de réception-Définitive',
        'Z' => 'Opér. gérée par Centrale/DASS/ DEP',
        'AA' => 'Notification CP annee en cours',
        'AB' => 'reliquat des CP annee en cours',
        'AC' => 'Besoins des CP annee en cours',
        'AD' => 'Observations (ou contraintes rencontrées)',
    ];

    /** Row 14, under the two grouped headers. */
    private const SUBHEADINGS = [
        'O' => 'Initiale (1)',
        'P' => 'reevaluations (2)',
        'Q' => 'années réévalutation',
        'R' => 'Actuelle (3)=(1)+(2)',
        'V' => 'Physique global(*)',
        'W' => 'Financier',
    ];

    /** Row 15, the controlled vocabularies the sheet expects. */
    private const HINTS = [
        'J' => 'TITRE 3-TITRE4',
        'K' => '57-62-76',
        'L' => 'P1-P2-P3-P4',
        'M' => 'P1,1-P1,2;;;;;;;',
        'N' => 'PACE-PCCE-PN-FSDRS-',
        'W' => 'cumul de paiment divisé par AE-ACTUELLE',
    ];

    private const COLUMN_WIDTHS = [
        'B' => 8, 'C' => 26, 'D' => 16, 'E' => 8, 'F' => 22, 'G' => 62, 'H' => 42,
        'I' => 10, 'J' => 9, 'K' => 9, 'L' => 11, 'M' => 12, 'N' => 12,
        'O' => 16, 'P' => 16, 'Q' => 12, 'R' => 16, 'S' => 16, 'T' => 16, 'U' => 16,
        'V' => 11, 'W' => 11, 'X' => 13, 'Y' => 13, 'Z' => 12,
        'AA' => 16, 'AB' => 16, 'AC' => 16, 'AD' => 40,
    ];

    /** Columns carrying dinar amounts, and the ones the SUBTOTAL row sums. */
    private const MONEY_COLUMNS = ['O', 'P', 'R', 'S', 'T', 'U', 'AA', 'AB', 'AC'];
    private const SUBTOTAL_COLUMNS = ['O', 'P', 'R', 'S', 'T', 'U', 'AA', 'AB', 'AC'];

    /** @var array<int, array<string, mixed>> */
    private array $rows;

    /**
     * @param  array{wilaya_id?: int|null, titre?: string|null, year?: int|null}  $filters
     */
    public function __construct(
        private readonly array $filters = [],
        private readonly ?Carbon $arreteeAu = null,
    ) {
        $this->rows = $this->buildRows();
    }

    public function title(): string
    {
        return 'Etat global PSC et PSD';
    }

    /**
     * The sheet is drawn in {@see AfterSheet}; returning a single empty row
     * keeps the writer from complaining about an export with no source.
     */
    public function array(): array
    {
        return [[]];
    }

    /** @return array<int, array<string, mixed>> */
    public function operationRows(): array
    {
        return $this->rows;
    }

    public function filename(): string
    {
        $scope = $this->filters['wilaya_id'] ?? null ? 'wilaya-'.$this->filters['wilaya_id'] : 'national';

        return "etat-global-{$scope}-".$this->arreteeDate()->format('Ymd').'.xlsx';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->banners($sheet);
                $this->header($sheet);
                $this->data($sheet);
                $this->subtotal($sheet);
                $this->footnotes($sheet);
                $this->layout($sheet);
            },
        ];
    }

    // -----------------------------------------------------------------
    // Sheet construction
    // -----------------------------------------------------------------

    private function banners(Worksheet $sheet): void
    {
        $span = self::FIRST_COLUMN.'%1$d:'.self::LAST_COLUMN.'%1$d';

        $banners = [
            self::ROW_REPUBLIQUE => ['REPUBLIQUE ALGERIENNE DEMOCRATIQUE ET POPULAIRE', 12],
            self::ROW_MINISTERE => ['MINISTERE DE LA SOLIDARITE NATIONALE, DE LA FAMILLE ET DE LA CONDITION DE LA FEMME', 12],
            self::ROW_TITLE => ["ETAT PHYSIQUE ET FINANCIER GLOBAL, DES OPERATIONS D'INVESTISSEMENT PUBLIC A GESTION  CENTRALISEE/DECONCENTREE", 13],
            self::ROW_ARRETEE => ['ARRETEE AU '.$this->arreteeDate()->format('d/m/Y'), 12],
        ];

        foreach ($banners as $row => [$text, $size]) {
            $range = sprintf($span, $row);
            $sheet->mergeCells($range);
            $sheet->setCellValue(self::FIRST_COLUMN.$row, $text);
            $sheet->getStyle($range)->getFont()->setBold(true)->setSize($size);
            $sheet->getStyle($range)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        $sheet->setCellValue('U'.self::ROW_UNIT, '(U: en Dinars)');
        $sheet->getStyle('U'.self::ROW_UNIT)->getFont()->setItalic(true);
    }

    private function header(Worksheet $sheet): void
    {
        foreach (self::HEADINGS as $column => $caption) {
            $sheet->setCellValue($column.self::ROW_HEADER, $caption);

            // O:R and V:W group two sub-columns; everything else spans both rows.
            $merge = match ($column) {
                'O' => 'O13:R13',
                'V' => 'V13:W13',
                default => "{$column}13:{$column}14",
            };

            $sheet->mergeCells($merge);
        }

        foreach (self::SUBHEADINGS as $column => $caption) {
            $sheet->setCellValue($column.self::ROW_SUBHEADER, $caption);
        }

        foreach (self::HINTS as $column => $hint) {
            $sheet->setCellValue($column.self::ROW_HINTS, $hint);
        }

        $headerRange = self::FIRST_COLUMN.self::ROW_HEADER.':'.self::LAST_COLUMN.self::ROW_SUBHEADER;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
        ]);

        $hintRange = self::FIRST_COLUMN.self::ROW_HINTS.':'.self::LAST_COLUMN.self::ROW_HINTS;
        $sheet->getStyle($hintRange)->applyFromArray([
            'font' => ['italic' => true, 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getRowDimension(self::ROW_HEADER)->setRowHeight(46);
        $sheet->getRowDimension(self::ROW_SUBHEADER)->setRowHeight(30);
        $sheet->getRowDimension(self::ROW_HINTS)->setRowHeight(24);
    }

    private function data(Worksheet $sheet): void
    {
        $row = self::ROW_FIRST_DATA;

        foreach ($this->rows as $values) {
            foreach ($values as $column => $value) {
                $sheet->setCellValue($column.$row, $value);
            }

            // U is a live formula in the ministry's file, not a stored number.
            $sheet->setCellValue("U{$row}", "=R{$row}-T{$row}");

            $row++;
        }

        $last = $this->lastDataRow();

        $sheet->getStyle('V'.self::ROW_FIRST_DATA.":W{$last}")
            ->getNumberFormat()->setFormatCode('0%');

        foreach (self::MONEY_COLUMNS as $column) {
            $sheet->getStyle("{$column}".self::ROW_FIRST_DATA.":{$column}{$last}")
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $sheet->getStyle(self::FIRST_COLUMN.self::ROW_FIRST_DATA.':'.self::LAST_COLUMN.$last)
            ->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    }

    /** Last data row; equals the header row when the selection is empty. */
    private function lastDataRow(): int
    {
        return self::ROW_FIRST_DATA + max(count($this->rows), 1) - 1;
    }

    private function subtotalRow(): int
    {
        return $this->lastDataRow() + 1;
    }

    private function subtotal(Worksheet $sheet): void
    {
        $first = self::ROW_FIRST_DATA;
        $last = $this->lastDataRow();
        $row = $this->subtotalRow();

        $sheet->setCellValue('D'.$row, 'TOTAL');

        foreach (self::SUBTOTAL_COLUMNS as $column) {
            $sheet->setCellValue(
                $column.$row,
                count($this->rows) > 0 ? "=SUBTOTAL(9,{$column}{$first}:{$column}{$last})" : 0,
            );
        }

        $range = self::FIRST_COLUMN.$row.':'.self::LAST_COLUMN.$row;
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']],
        ]);

        foreach (self::SUBTOTAL_COLUMNS as $column) {
            $sheet->getStyle($column.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $this->borders($sheet, self::ROW_HEADER, $row);
    }

    private function footnotes(Worksheet $sheet): void
    {
        $notes = [
            'NB: (*) à renseigner par P1,P2,P3 ou P4 conformément à la sructure programme du ministere',
            '(**) à renseigner par (P1.1,P1.2), (P2.1,P2.2),(P3.1,P3.2) ou (P4.1,P4.2)',
        ];

        foreach ($notes as $index => $note) {
            $row = $this->subtotalRow() + 2 + $index;
            $range = self::FIRST_COLUMN.$row.':'.self::LAST_COLUMN.$row;

            $sheet->mergeCells($range);
            $sheet->setCellValue(self::FIRST_COLUMN.$row, $note);
            $sheet->getStyle($range)->getFont()->setItalic(true)->setSize(9);
        }
    }

    private function borders(Worksheet $sheet, int $from, int $to): void
    {
        $sheet->getStyle(self::FIRST_COLUMN.$from.':'.self::LAST_COLUMN.$to)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function layout(Worksheet $sheet): void
    {
        foreach (self::COLUMN_WIDTHS as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getColumnDimension('A')->setWidth(3);

        // AE carries the CLOTUREE marker the ministry filters on but does not print.
        $sheet->getColumnDimension(self::CLOSURE_COLUMN)->setVisible(false);

        $sheet->freezePane('B'.self::ROW_FIRST_DATA);
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
    }

    // -----------------------------------------------------------------
    // Data
    // -----------------------------------------------------------------

    /** @return array<int, array<string, mixed>> */
    private function buildRows(): array
    {
        $rows = [];

        foreach ($this->query()->cursor() as $project) {
            $rows[] = $this->row($project);
        }

        return $rows;
    }

    private function query(): Builder
    {
        $query = Project::query()
            ->visibleTo()
            ->with([
                'wilaya', 'sousSecteur', 'programmeType', 'programmeStructure', 'sousProgramme',
                'aeRevaluations', 'aeEngagements', 'cpPayments', 'physicalProgress',
                'maturationItems', 'lots', 'land', 'closure',
            ])
            ->orderBy('wilaya_id')
            ->orderBy('operation_number');

        if ($wilaya = $this->filters['wilaya_id'] ?? null) {
            $query->where('wilaya_id', $wilaya);
        }

        if ($titre = $this->filters['titre'] ?? null) {
            $query->where(fn (Builder $q) => $q->where('titre', $titre)->orWhere('budget_type', strtoupper($titre)));
        }

        if ($year = $this->filters['year'] ?? null) {
            $query->where('notification_year', $year);
        }

        return $query;
    }

    /** @return array<string, mixed> */
    private function row(Project $project): array
    {
        $budget = ProjectBudget::for($project);

        return [
            'B' => $project->wilaya?->code,
            'C' => $project->decision_programme_ref,
            'D' => strtoupper($project->wilaya?->name_fr ?? ''),
            'E' => $project->nbr_operations ?? 0,
            'F' => $this->individualisation($project),

            // G16 in the source file concatenates the code in front of the intitulé.
            'G' => trim(($project->operation_number ?? '').' '.$project->title_fr),
            'H' => $project->title_ar,

            'I' => $project->notification_year,
            'J' => strtoupper($project->titre ?: $project->budget_type ?: ''),
            'K' => $project->sousSecteur?->code,
            'L' => $project->programmeStructure?->code,
            'M' => $project->sousProgramme?->code,
            'N' => $project->programmeType?->code,

            'O' => $budget->apInitiale(),
            'P' => $budget->totalReevaluations(),
            'Q' => $this->revaluationYears($project),
            'R' => $budget->apActuelle(),
            'S' => $budget->cumulEngagements(),
            'T' => $budget->cumulPaiements(),
            // U is written as a formula alongside the data.

            'V' => $this->rate($budget->tauxPhysique(), $project->physicalProgress->isNotEmpty()),
            'W' => $this->rate($budget->tauxFinancier(), $budget->apActuelle() > 0),

            'X' => $project->reception_provisoire_date?->format('d/m/Y'),
            'Y' => $project->reception_definitive_date?->format('d/m/Y'),
            'Z' => strtoupper($project->managed_by ?? ''),

            'AA' => $project->cp_notification_current_year_da,
            'AB' => $project->cp_reliquat_da,
            'AC' => $project->cp_besoins_da,
            'AD' => $project->observations,

            self::CLOSURE_COLUMN => $project->is_closed || $project->status === 'closed' ? 'CLOTUREE' : null,
        ];
    }

    private function individualisation(Project $project): ?string
    {
        if (! $project->individualisation_number) {
            return null;
        }

        return trim($project->individualisation_number.' '.(
            $project->individualisation_date ? 'du '.$project->individualisation_date->format('d/m/Y') : ''
        ));
    }

    /** The sheet lists the years of the réévaluations, comma separated. */
    private function revaluationYears(Project $project): ?string
    {
        $years = $project->aeRevaluations
            ->where('status', 'approved')
            ->pluck('year')
            ->filter()
            ->unique()
            ->sort()
            ->implode(', ');

        return $years !== '' ? $years : null;
    }

    /** Percentages go in as fractions; "_" marks a figure that is not renseigné. */
    private function rate(float $percent, bool $available): float|string
    {
        return $available ? round($percent / 100, 4) : self::NOT_APPLICABLE;
    }

    private function arreteeDate(): Carbon
    {
        return $this->arreteeAu ?? Carbon::today();
    }
}
