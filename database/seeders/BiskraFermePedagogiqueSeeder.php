<?php

namespace Database\Seeders;

use App\Models\AeEngagement;
use App\Models\AeRevaluation;
use App\Models\Chapitre;
use App\Models\Commune;
use App\Models\Contractor;
use App\Models\CpPayment;
use App\Models\Daira;
use App\Models\LotMilestone;
use App\Models\PhysicalProgress;
use App\Models\Programme;
use App\Models\ProgrammeType;
use App\Models\Project;
use App\Models\ProjectDesignMission;
use App\Models\ProjectLand;
use App\Models\ProjectLot;
use App\Models\ProjectNetworkLink;
use App\Models\Secteur;
use App\Models\SousSecteur;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;

/**
 * The scanned "FICHE DE SUIVI DU PROJET (ferme ped)" of El Hadjeb, Biskra,
 * reproduced field for field from img903–img911.
 *
 * This is the fixture the PDF export is diffed against page by page, so every
 * amount, date and Arabic annotation here is transcribed rather than invented.
 */
class BiskraFermePedagogiqueSeeder extends Seeder
{
    private const OPERATION = 'Nk.5.764.8.262.107.10.01';

    public function run(): void
    {
        $wilaya = Wilaya::where('code', '07')->first();

        if (! $wilaya) {
            $this->command?->warn('Biskra (code 07) missing, run WilayaSeeder first.');

            return;
        }

        $project = $this->project($wilaya);

        $this->land($project);
        $this->designMissions($project);
        $this->revaluations($project);

        $contractors = $this->contractors();
        $this->receivedLots($project, $contractors);
        $this->lotsUnderConstruction($project, $contractors);
        $this->lotsBeingTendered($project);
        $this->lotsNotLaunched($project);
        $this->networks($project);
        $this->execution($project);
    }

    private function project(Wilaya $wilaya): Project
    {
        $secteur = Secteur::where('code', '5')->first();

        return Project::updateOrCreate(
            ['fip_id' => 'FIP-2010-BISKRA-FP'],
            [
                'operation_prefix' => 'Nk',
                'operation_number' => self::OPERATION,
                'title_fr' => "Etude, réalisation et équipement d'une ferme pédagogique BISKRA.",
                'title_ar' => 'دراسة وإنجاز وتجهيز مزرعة بيداغوجية بالحاجب - بسكرة',
                'short_label' => 'ferme ped',
                'wilaya_id' => $wilaya->id,
                'daira_id' => Daira::where('wilaya_id', $wilaya->id)->where('name_fr', 'Biskra')->value('id'),
                'commune_code' => Commune::where('code_wilaya', $wilaya->legacyCode())
                    ->where('lib_comm_fr', 'like', '%HADJEB%')
                    ->value('code_comm'),
                'programme_id' => Programme::where('code', 'PS-01')->value('id') ?? Programme::value('id'),
                'nature' => 'new',
                'status' => 'in_execution',
                'phase' => 'realisation',
                'capacity' => 100,
                'nbr_operations' => 1,
                'secteur_id' => $secteur?->id,
                'sous_secteur_id' => SousSecteur::where('code', '76')->value('id'),
                'chapitre_id' => Chapitre::where('code', '764')->value('id'),
                'programme_type_id' => ProgrammeType::where('code', 'PCCE')->value('id'),
                'decision_programme_ref' => 'MF/2010/54/DP/54',
                'inscription_date' => '2010-11-02',
                'notification_year' => 2010,
                'titre' => 't3',
                'managed_by' => 'dass',
                'budget_type' => 'T3',

                // AP initiale, before the two réévaluations (img905).
                'ae_da' => 80_000_000,
                'reevaluation_count' => 2,
                'cp_da' => 120_000_000,

                // img903: 60 % d'avancement, 00 % d'équipement.
                'equipment_progress_pct' => 0,

                'ods_date' => '2013-06-19',
                'initial_launch_date' => '2013-06-19',
                'initial_launch_scope' => 'partiel',
                'project_duration_months' => 20,
                'deadline' => '2017-12-31',
                'fiche_updated_at' => '2017-07-31',
                'created_by' => User::where('role', 'admin')->value('id'),
            ],
        );
    }

    private function land(Project $project): void
    {
        ProjectLand::updateOrCreate(
            ['project_id' => $project->id],
            [
                'affectation_decision_ref' => 'N° 96',
                'affectation_decision_date' => '2006-12-13',
                'superficie_ha' => 11,
                'superficie_a' => 94,
                'superficie_ca' => 42,
                'location' => 'Commune El Hadjeb, daïra de Biskra',
            ],
        );
    }

    private function designMissions(Project $project): void
    {
        $project->designMissions()->delete();

        ProjectDesignMission::create([
            'project_id' => $project->id,
            'esquisse_approved_at' => '2011-11-20',
            'avant_projet_approved_at' => '2011-12-20',
            'projet_execution_approved_at' => '2012-09-30',
        ]);
    }

    private function revaluations(Project $project): void
    {
        $project->aeRevaluations()->delete();

        $author = User::where('role', 'admin')->value('id');

        foreach ([
            ['sequence' => 1, 'year' => 2014, 'requested_amount_da' => 20_000_000],
            ['sequence' => 2, 'year' => 2015, 'requested_amount_da' => 60_000_000],
        ] as $row) {
            AeRevaluation::create($row + [
                'project_id' => $project->id,
                'decision_ref' => 'MF/'.$row['year'].'/DP/54',
                'justification' => 'Réévaluation accordée au titre du programme PCCE.',
                'status' => 'approved',
                'submitted_by' => $author,
                'approved_by' => $author,
                'approved_at' => $row['year'].'-12-31',
            ]);
        }
    }

    /** @return array<string, Contractor> */
    private function contractors(): array
    {
        $rows = [
            'meziani' => ['name' => 'E.T.B MEZIANI AMAR', 'legal_form' => 'etb'],
            'bouaziz' => ['name' => 'E.T.B BOUAZIZ ZERIBI', 'legal_form' => 'etb'],
            'maswid' => ['name' => 'SARL MASWID', 'legal_form' => 'sarl'],
            'zerrog' => ['name' => 'E E G ZERROG ABDELMADJID', 'legal_form' => 'eeg'],
        ];

        $out = [];
        foreach ($rows as $key => $row) {
            $out[$key] = Contractor::updateOrCreate(['name' => $row['name']], $row + [
                'address' => 'Biskra',
            ]);
        }

        return $out;
    }

    /** img907 — TOTAL 70 084 580,70 DA. */
    private function receivedLots(Project $project, array $contractors): void
    {
        $lots = [
            [1, 'Bloc administrative', 23_996_811.15, '2016-05-26', 'meziani', 12, '2013-06-06', '2013-06-19'],
            [2, 'Bloc Internat', 12_600_572.84, '2015-09-03', 'bouaziz', 15, '2014-04-02', '2014-04-02'],
            [3, 'Bloc pédagogique', 24_804_142.74, '2016-05-26', 'meziani', 12, '2013-06-06', '2013-06-19'],
            [4, 'Chambre froide', 8_683_053.97, '2015-09-03', 'bouaziz', 15, '2014-03-26', '2014-03-26'],
        ];

        foreach ($lots as [$number, $designation, $amount, $reception, $contractor, $delai, $engagement, $ods]) {
            $lot = $this->lot($project, 1, $number, [
                'designation' => $designation,
                'state' => ProjectLot::STATE_RECEPTIONNE,
                'amount_da' => $amount,
                'estimation_da' => $amount,
                'reception_date' => $reception,
                'contractor_id' => $contractors[$contractor]->id,
                'delai_months' => $delai,
                'launch_date' => $ods,
                'progress_pct' => 100,
            ]);

            $this->milestones($lot, $engagement, $ods);
        }
    }

    /** img908 — TOTAL 32 309 823,95 DA. */
    private function lotsUnderConstruction(Project $project, array $contractors): void
    {
        $lot = $this->lot($project, 2, 2, [
            'designation' => 'Bloc hébergement + Infirmerie (2ème tranche)',
            'state' => ProjectLot::STATE_EN_COURS_REALISATION,
            'amount_da' => 24_614_148.95,
            'estimation_da' => 24_614_148.95,
            'delai_months' => 20,
            'launch_date' => '2014-11-23',
            'progress_pct' => 90,
            'contractor_id' => $contractors['maswid']->id,
        ]);
        $this->milestones($lot, '2014-11-04', '2014-11-23');

        $lot = $this->lot($project, 2, 6, [
            'designation' => 'Éclairage extérieur + Equipement post transformateur',
            'state' => ProjectLot::STATE_EN_COURS_REALISATION,
            'amount_da' => 7_695_675.00,
            'estimation_da' => 7_695_675.00,
            'delai_months' => 2,
            'launch_date' => '2016-12-19',
            'progress_pct' => 0,
            'contractor_id' => $contractors['zerrog']->id,
        ]);
        $this->milestones($lot, '2016-11-24', '2016-12-19');
    }

    /** img909 — TOTAL (1) 56 199 575,02 DA, with the Arabic workflow states. */
    private function lotsBeingTendered(Project $project): void
    {
        $refused = 'تم رفض التأشير على الصفقة من طرف الرقابة المالية لولاية بسكرة بسبب تسقيف المدفوعات';

        $lots = [
            [1, "Bâche à eau 60 m3 + logement de gardien", 8_739_964.12, 'visa_refuse_plafond', $refused],
            [3, '2 logements de fonction', 8_467_839.90, 'visa_refuse_plafond', $refused],
            [5, 'Assainissement + AEP', 3_341_520.00, 'attente_visa_controle_financier', 'في انتظار تأشيرة الرقابة المالية على العقد'],
            [7, 'Aménagement extérieur + mur + construction post transformateur', 17_410_536.00, 'visa_refuse_plafond', $refused],
            [8, "Réalisation d'un forage de 500 ML", 14_014_260.00, 'infructueux_offres_excessives', 'عدم جدوى الحصة كون العروض مبالغ فيها'],
            [9, "Equipement hydro-électro-mécanique d'un forage", 4_225_455.00, 'attribution_provisoire', 'تم المنح المؤقت للعقد'],
        ];

        foreach ($lots as [$number, $designation, $estimation, $reason, $note]) {
            $this->lot($project, 2, $number, [
                'designation' => $designation,
                'state' => ProjectLot::STATE_EN_COURS_LANCEMENT,
                'amount_da' => 0,
                'estimation_da' => $estimation,
                'blocking_reason' => $reason,
                'blocking_note_ar' => $note,
            ]);
        }
    }

    /**
     * img910 — TOTAL (2) 21 272 357,50 DA.
     *
     * These are equipment lots on their own 1–5 numbering, independent of the
     * two works tranches, so they get tranche 3 to keep the natural key unique.
     */
    private function lotsNotLaunched(Project $project): void
    {
        $lots = [
            [1, 'Equipements de cuisine et réfectoire + Buanderie', 7_465_967.73],
            [2, "Mobilier d'administration et équipement scolaire", 7_003_152.00],
            [3, 'Equipement de dortoirs', 2_500_290.00],
            [4, 'Caméras de surveillance', 1_802_947.77],
            [5, 'Tracteur utilitaire', 2_500_000.00],
        ];

        foreach ($lots as [$number, $designation, $estimation]) {
            $this->lot($project, 3, $number, [
                'designation' => $designation,
                'kind' => 'equipement',
                'state' => ProjectLot::STATE_NON_LANCE,
                'amount_da' => 0,
                'estimation_da' => $estimation,
                'estimated_by' => 'BET',
                'estimation_basis' => 'bet',
            ]);
        }
    }

    /** The fiche marks all four réseaux as non raccordés. */
    private function networks(Project $project): void
    {
        foreach (ProjectNetworkLink::networkCodes() as $network) {
            ProjectNetworkLink::updateOrCreate(
                ['project_id' => $project->id, 'network' => $network],
                ['connected' => false, 'motif' => 'Raccordement non réalisé à la date de la fiche.'],
            );
        }
    }

    /**
     * Engagements match the lots that have a marché; payments are set so the
     * fiche's 60 % avancement has a plausible financial counterpart.
     */
    private function execution(Project $project): void
    {
        $project->aeEngagements()->delete();
        $project->cpPayments()->delete();
        $project->physicalProgress()->delete();

        $engaged = (float) $project->lots()->sum('amount_da');
        $author = User::where('role', 'admin')->value('id');

        AeEngagement::create([
            'project_id' => $project->id,
            'amount_da' => $engaged,
            'description' => 'Cumul des marchés engagés chez le contrôleur financier',
            'engaged_at' => '2016-12-19',
            'created_by' => $author,
        ]);

        foreach ([
            ['2015-09-03', 30_000_000, 'Situation lots réceptionnés 1ère tranche'],
            ['2016-05-26', 32_000_000, 'Situation blocs administratif et pédagogique'],
            ['2017-06-30', 19_000_000, 'Situation bloc hébergement (2ème tranche)'],
        ] as [$date, $amount, $label]) {
            CpPayment::create([
                'project_id' => $project->id,
                'amount_da' => $amount,
                'payment_type' => 'regular',
                'description' => $label,
                'status' => 'validated',
                'paid_at' => $date,
                'created_by' => $author,
            ]);
        }

        PhysicalProgress::create([
            'project_id' => $project->id,
            'title' => 'Situation physique au 31/07/2017',
            'progress_pct' => 60,
            'status' => 'accepted',
            'reported_by' => $author,
            'accepted_by' => $author,
            'reported_at' => '2017-07-31',
        ]);
    }

    private function lot(Project $project, int $tranche, int $number, array $attributes): ProjectLot
    {
        return ProjectLot::updateOrCreate(
            ['project_id' => $project->id, 'tranche' => $tranche, 'lot_number' => $number],
            $attributes + ['kind' => 'travaux'],
        );
    }

    /** The two dated events the fiche records per awarded lot (img905). */
    private function milestones(ProjectLot $lot, string $engagement, string $ods): void
    {
        $lot->milestones()->delete();

        LotMilestone::create([
            'lot_id' => $lot->id,
            'type' => 'engagement_controleur_financier',
            'date' => $engagement,
        ]);

        LotMilestone::create([
            'lot_id' => $lot->id,
            'type' => 'ods_notification',
            'date' => $ods,
        ]);
    }
}
