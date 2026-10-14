<?php

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Models\AeRevaluation;
use App\Models\Contractor;
use App\Models\DesignOffice;
use App\Models\LotMilestone;
use App\Models\Project;
use App\Models\ProjectDesignMission;
use App\Models\ProjectLand;
use App\Models\ProjectLot;
use App\Models\ProjectNetworkLink;
use App\Models\PhysicalProgress;
use App\Services\Budget\ProjectBudget;
use App\Services\Fiche\LotBuckets;
use App\Services\Workflow\ProjectWorkflowNotifier;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Data entry for everything the official fiche prints and the dossier screen
 * only displays: lots and their procurement milestones, réévaluations, the
 * assiette foncière, the maîtrise d'œuvre, the réseaux and physical situations.
 *
 * Each save goes through the model observers, so the metrics, the SPU snapshot
 * and the alerts are recomputed and broadcast without anything extra here.
 */
#[Layout('layouts.dashboard')]
class ProjectFiche extends Component
{
    public function title(): string
    {
        return __('ui.fiche.title');
    }
    public string $projectId = '';

    #[Url]
    public string $tab = 'lots';

    /** Lot being created or edited; null means the form is closed. */
    public ?array $lotForm = null;

    /** Milestone form, keyed by the lot it belongs to. */
    public ?int $milestoneLotId = null;

    public array $milestoneForm = ['type' => '', 'date' => null, 'reference' => '', 'notes' => ''];

    public ?array $revaluationForm = null;

    public array $land = [
        'affectation_decision_ref' => '',
        'affectation_decision_date' => null,
        'superficie_ha' => 0,
        'superficie_a' => 0,
        'superficie_ca' => 0,
        'location' => '',
    ];

    public array $mission = [
        'design_office_id' => null,
        'bet_name' => '',
        'esquisse_approved_at' => null,
        'avant_projet_approved_at' => null,
        'projet_execution_approved_at' => null,
    ];

    /** network => ['connected' => bool, 'motif' => string] */
    public array $networks = [];

    public string $situationTitle = '';

    public ?float $situationPct = null;

    public ?string $phase = null;

    public string $individualisationNumber = '';

    public ?string $individualisationDate = null;

    public ?int $worksReceptionQuarter = null;

    public ?int $worksReceptionYear = null;

    public ?int $equipmentReceptionQuarter = null;

    public ?int $equipmentReceptionYear = null;

    public ?int $inaugurationQuarter = null;

    public ?int $inaugurationYear = null;

    public array $contractorForm = ['name' => '', 'legal_form' => 'etb', 'nif' => '', 'business_register' => '', 'address' => ''];

    public bool $showContractorForm = false;

    public function mount(string $projectId): void
    {
        $project = $this->project();

        $this->authorize('view', $project);

        $this->projectId = $projectId;
        $this->loadPhase($project);
        $this->loadIdentity($project);
        $this->loadLand($project);
        $this->loadMission($project);
        $this->loadNetworks($project);
    }

    // -----------------------------------------------------------------
    // Access
    // -----------------------------------------------------------------

    public function project(): Project
    {
        return Project::with([
            'wilaya',
            'lots.contractor', 'lots.milestones', 'aeRevaluations.submitter.wilaya', 'land',
            'designMissions', 'networkLinks', 'physicalProgress.reporter.wilaya',
            'physicalProgress.acceptedBy', 'physicalProgress.declinedBy',
        ])
            ->where('fip_id', $this->projectId ?: request()->route('projectId'))
            ->visibleTo()
            ->firstOrFail();
    }

    public function canManageLots(): bool
    {
        return auth()->user()->hasPermission(Permission::LotsManage->value)
            && auth()->user()->can('updateExecution', $this->project());
    }

    public function canReportSituation(): bool
    {
        $project = $this->project();

        return auth()->user()->can('create', PhysicalProgress::class)
            && ! $project->isLocked()
            && in_array($project->status, ['approved', 'in_execution', 'completed'], true);
    }

    public function canRequestRevaluation(): bool
    {
        return auth()->user()->can('create', AeRevaluation::class);
    }

    public function canApproveRevaluation(): bool
    {
        return auth()->user()->hasPermission(Permission::ReevaluationApprove->value);
    }

    private function assertCanManage(): void
    {
        abort_unless($this->canManageLots(), 403);
    }

    // -----------------------------------------------------------------
    // Situation du projet (PDF header boxes)
    // -----------------------------------------------------------------

    private function loadPhase(Project $project): void
    {
        $this->phase = $project->phase;
    }

    public function savePhase(): void
    {
        $this->assertCanManage();

        $validated = $this->validate([
            'phase' => ['required', Rule::in(array_keys(Project::phaseLabels()))],
        ]);

        $this->project()->update([
            'phase' => $validated['phase'],
            'fiche_updated_at' => now()->toDateString(),
        ]);

        session()->flash('fiche-saved', __('ui.flash.phase_saved'));
    }

    // -----------------------------------------------------------------
    // Identification (PDF narrative fields)
    // -----------------------------------------------------------------

    private function loadIdentity(Project $project): void
    {
        $this->individualisationNumber = $project->individualisation_number ?? '';
        $this->individualisationDate = $project->individualisation_date?->format('Y-m-d');
        $this->worksReceptionQuarter = $project->works_reception_quarter;
        $this->worksReceptionYear = $project->works_reception_year;
        $this->equipmentReceptionQuarter = $project->equipment_reception_quarter;
        $this->equipmentReceptionYear = $project->equipment_reception_year;
        $this->inaugurationQuarter = $project->inauguration_quarter;
        $this->inaugurationYear = $project->inauguration_year;
    }

    public function saveIdentity(): void
    {
        $this->assertCanManage();

        foreach ([
            'worksReceptionQuarter', 'worksReceptionYear',
            'equipmentReceptionQuarter', 'equipmentReceptionYear',
            'inaugurationQuarter', 'inaugurationYear',
        ] as $prop) {
            if ($this->{$prop} === '' || $this->{$prop} === 0) {
                $this->{$prop} = null;
            }
        }

        if ($this->individualisationDate === '') {
            $this->individualisationDate = null;
        }

        $validated = $this->validate([
            'individualisationNumber' => 'nullable|string|max:100',
            'individualisationDate' => 'nullable|date',
            'worksReceptionQuarter' => 'nullable|integer|min:1|max:4',
            'worksReceptionYear' => 'nullable|integer|min:1990|max:2100',
            'equipmentReceptionQuarter' => 'nullable|integer|min:1|max:4',
            'equipmentReceptionYear' => 'nullable|integer|min:1990|max:2100',
            'inaugurationQuarter' => 'nullable|integer|min:1|max:4',
            'inaugurationYear' => 'nullable|integer|min:1990|max:2100',
        ]);

        $this->assertQuarterYearPair('worksReceptionQuarter', 'worksReceptionYear', 'worksReceptionYear');
        $this->assertQuarterYearPair('equipmentReceptionQuarter', 'equipmentReceptionYear', 'equipmentReceptionYear');
        $this->assertQuarterYearPair('inaugurationQuarter', 'inaugurationYear', 'inaugurationYear');

        $this->project()->update([
            'individualisation_number' => $validated['individualisationNumber'] ?: null,
            'individualisation_date' => $validated['individualisationDate'] ?: null,
            'works_reception_quarter' => $validated['worksReceptionQuarter'],
            'works_reception_year' => $validated['worksReceptionYear'],
            'equipment_reception_quarter' => $validated['equipmentReceptionQuarter'],
            'equipment_reception_year' => $validated['equipmentReceptionYear'],
            'inauguration_quarter' => $validated['inaugurationQuarter'],
            'inauguration_year' => $validated['inaugurationYear'],
            'fiche_updated_at' => now()->toDateString(),
        ]);

        session()->flash('fiche-saved', __('ui.flash.identity_saved'));
    }

    private function assertQuarterYearPair(string $quarterProp, string $yearProp, string $errorKey): void
    {
        $quarter = $this->{$quarterProp};
        $year = $this->{$yearProp};

        if (($quarter !== null && $year === null) || ($quarter === null && $year !== null)) {
            $this->addError($errorKey, __('ui.fiche.quarter_year_required'));
            throw \Illuminate\Validation\ValidationException::withMessages([
                $errorKey => [__('ui.fiche.quarter_year_required')],
            ]);
        }
    }

    // -----------------------------------------------------------------
    // Lots
    // -----------------------------------------------------------------

    public function newLot(): void
    {
        $this->assertCanManage();

        $this->lotForm = [
            'id' => null,
            'tranche' => 1,
            'lot_number' => ($this->project()->lots->max('lot_number') ?? 0) + 1,
            'designation' => '',
            'designation_ar' => '',
            'kind' => 'travaux',
            'state' => ProjectLot::STATE_NON_LANCE,
            'amount_da' => null,
            'estimation_da' => null,
            'estimated_by' => '',
            'estimation_basis' => null,
            'blocking_reason' => null,
            'blocking_note_ar' => '',
            'delai_months' => null,
            'launch_date' => null,
            'progress_pct' => 0,
            'reception_date' => null,
            'contractor_id' => null,
            'observations' => '',
        ];
    }

    public function editLot(int $lotId): void
    {
        $this->assertCanManage();

        $lot = $this->project()->lots->firstWhere('id', $lotId);
        abort_unless($lot !== null, 404);

        $this->lotForm = array_merge($lot->only([
            'id', 'tranche', 'lot_number', 'designation', 'designation_ar', 'kind',
            'state', 'estimated_by', 'estimation_basis', 'blocking_reason',
            'blocking_note_ar', 'delai_months', 'contractor_id', 'observations',
        ]), [
            'amount_da' => (float) $lot->amount_da,
            'estimation_da' => $lot->estimation_da === null ? null : (float) $lot->estimation_da,
            'progress_pct' => (float) $lot->progress_pct,
            'launch_date' => $lot->launch_date?->format('Y-m-d'),
            'reception_date' => $lot->reception_date?->format('Y-m-d'),
        ]);
    }

    public function saveLot(): void
    {
        $this->assertCanManage();

        $project = $this->project();

        $validated = $this->validate([
            'lotForm.tranche' => 'required|integer|min:1|max:20',
            'lotForm.lot_number' => 'required|integer|min:1|max:999',
            'lotForm.designation' => 'required|string|max:500',
            'lotForm.designation_ar' => 'nullable|string|max:500',
            'lotForm.kind' => ['required', Rule::in(['travaux', 'equipement', 'etude', 'autre'])],
            'lotForm.state' => ['required', Rule::in(array_keys(ProjectLot::stateLabels()))],
            'lotForm.amount_da' => 'nullable|numeric|min:0',
            'lotForm.estimation_da' => 'nullable|numeric|min:0',
            'lotForm.estimated_by' => 'nullable|string|max:255',
            'lotForm.estimation_basis' => ['nullable', Rule::in(['bet', 'consultation', 'autre'])],
            'lotForm.blocking_reason' => ['nullable', Rule::in(array_keys(ProjectLot::blockingReasonLabels()))],
            'lotForm.blocking_note_ar' => 'nullable|string|max:2000',
            'lotForm.delai_months' => 'nullable|integer|min:1|max:600',
            'lotForm.launch_date' => 'nullable|date',
            'lotForm.progress_pct' => 'nullable|numeric|min:0|max:100',
            'lotForm.reception_date' => 'nullable|date',
            'lotForm.contractor_id' => 'nullable|integer|exists:contractors,id',
            'lotForm.observations' => 'nullable|string|max:2000',
        ])['lotForm'];

        $duplicate = $project->lots()
            ->where('tranche', $validated['tranche'])
            ->where('lot_number', $validated['lot_number'])
            ->when($this->lotForm['id'], fn ($q, $id) => $q->whereKeyNot($id))
            ->exists();

        if ($duplicate) {
            $this->addError('lotForm.lot_number', "Le lot {$validated['lot_number']} existe déjà dans la tranche {$validated['tranche']}.");

            return;
        }

        $validated['amount_da'] ??= 0;
        $validated['progress_pct'] ??= 0;

        if ($this->lotForm['id']) {
            $lot = ProjectLot::whereKey($this->lotForm['id'])->firstOrFail();
            $this->authorize('update', $lot);
            $lot->update($validated);
        } else {
            $this->authorize('create', ProjectLot::class);
            $project->lots()->create($validated);
        }

        $this->lotForm = null;
        session()->flash('fiche-saved', __('ui.flash.lot_saved'));
    }

    public function deleteLot(int $lotId): void
    {
        $this->assertCanManage();

        $lot = $this->project()->lots->firstWhere('id', $lotId);
        abort_unless($lot !== null, 404);
        $this->authorize('delete', $lot);

        $lot->delete();
        session()->flash('fiche-saved', __('ui.flash.lot_deleted'));
    }

    public function cancelLot(): void
    {
        $this->lotForm = null;
        $this->resetErrorBag();
    }

    // -----------------------------------------------------------------
    // Milestones
    // -----------------------------------------------------------------

    public function openMilestones(int $lotId): void
    {
        $this->milestoneLotId = $this->milestoneLotId === $lotId ? null : $lotId;
        $this->milestoneForm = ['type' => $this->defaultMilestoneType(), 'date' => null, 'reference' => '', 'notes' => ''];
    }

    public function addMilestone(): void
    {
        $this->assertCanManage();

        $lot = $this->project()->lots->firstWhere('id', $this->milestoneLotId);
        abort_unless($lot !== null, 404);

        $validated = $this->validate([
            'milestoneForm.type' => ['required', Rule::in(array_keys(LotMilestone::typeLabels()))],
            'milestoneForm.date' => 'nullable|date',
            'milestoneForm.reference' => 'nullable|string|max:255',
            'milestoneForm.notes' => 'nullable|string|max:1000',
        ])['milestoneForm'];

        $lot->milestones()->create($validated);

        $this->milestoneForm = ['type' => $this->defaultMilestoneType(), 'date' => null, 'reference' => '', 'notes' => ''];
        session()->flash('fiche-saved', __('ui.flash.procurement_added'));
    }

    private function defaultMilestoneType(): string
    {
        $keys = array_keys(LotMilestone::typeLabels());

        return $keys[0] ?? '';
    }

    public function deleteMilestone(int $milestoneId): void
    {
        $this->assertCanManage();

        $milestone = LotMilestone::with('lot')->findOrFail($milestoneId);
        abort_unless($milestone->lot->project_id === $this->project()->id, 403);

        $milestone->delete();
    }

    // -----------------------------------------------------------------
    // Réévaluations
    // -----------------------------------------------------------------

    public function newRevaluation(): void
    {
        abort_unless($this->canRequestRevaluation(), 403);

        $project = $this->project();

        $this->revaluationForm = [
            'id' => null,
            'sequence' => min(5, ($project->aeRevaluations->max('sequence') ?? 0) + 1),
            'year' => (int) now()->year,
            'requested_amount_da' => null,
            'decision_ref' => '',
            'justification' => '',
        ];
    }

    public function saveRevaluation(): void
    {
        abort_unless($this->canRequestRevaluation(), 403);

        $validated = $this->validate([
            'revaluationForm.sequence' => 'required|integer|min:1|max:5',
            'revaluationForm.year' => 'required|integer|min:1990|max:2100',
            'revaluationForm.requested_amount_da' => 'required|numeric|min:0',
            'revaluationForm.decision_ref' => 'nullable|string|max:100',
            'revaluationForm.justification' => 'required|string|max:2000',
        ])['revaluationForm'];

        $revaluation = $this->project()->aeRevaluations()->create($validated + [
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationSubmitted($this->project(), $revaluation);

        $this->revaluationForm = null;
        session()->flash('fiche-saved', __('ui.flash.reeval_submitted'));
    }

    public function approveRevaluation(int $id): void
    {
        $revaluation = $this->project()->aeRevaluations->firstWhere('id', $id);
        abort_unless($revaluation !== null, 404);
        $this->authorize('approve', $revaluation);

        $revaluation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // The AP block prints the count, so it moves with the approvals.
        $project = $this->project();
        $project->update([
            'reevaluation_count' => $project->aeRevaluations()->where('status', 'approved')->count(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationDecision(
            $project->fresh(),
            $revaluation->fresh(['submitter']),
            'approved'
        );

        session()->flash('fiche-saved', __('ui.flash.reeval_approved'));
    }

    public function rejectRevaluation(int $id): void
    {
        $revaluation = $this->project()->aeRevaluations->firstWhere('id', $id);
        abort_unless($revaluation !== null, 404);
        $this->authorize('approve', $revaluation);

        $revaluation->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationDecision(
            $this->project(),
            $revaluation->fresh(['submitter']),
            'rejected'
        );

        session()->flash('fiche-saved', __('ui.flash.reeval_rejected'));
    }

    // -----------------------------------------------------------------
    // Foncier, maîtrise d'œuvre, réseaux
    // -----------------------------------------------------------------

    private function loadLand(Project $project): void
    {
        $land = $project->land;

        if (! $land) {
            return;
        }

        $this->land = [
            'affectation_decision_ref' => $land->affectation_decision_ref ?? '',
            'affectation_decision_date' => $land->affectation_decision_date?->format('Y-m-d'),
            'superficie_ha' => (int) $land->superficie_ha,
            'superficie_a' => (int) $land->superficie_a,
            'superficie_ca' => (int) $land->superficie_ca,
            'location' => $land->location ?? '',
        ];
    }

    public function saveLand(): void
    {
        $this->assertCanManage();

        $validated = $this->validate([
            'land.affectation_decision_ref' => 'nullable|string|max:100',
            'land.affectation_decision_date' => 'nullable|date',
            'land.superficie_ha' => 'required|integer|min:0|max:100000',
            'land.superficie_a' => 'required|integer|min:0|max:99',
            'land.superficie_ca' => 'required|integer|min:0|max:99',
            'land.location' => 'nullable|string|max:255',
        ])['land'];

        ProjectLand::updateOrCreate(['project_id' => $this->project()->id], $validated);

        session()->flash('fiche-saved', __('ui.flash.land_saved'));
    }

    private function loadMission(Project $project): void
    {
        $mission = $project->designMissions->first();

        if (! $mission) {
            return;
        }

        $this->mission = [
            'design_office_id' => $mission->design_office_id,
            'bet_name' => $mission->bet_name ?? '',
            'esquisse_approved_at' => $mission->esquisse_approved_at?->format('Y-m-d'),
            'avant_projet_approved_at' => $mission->avant_projet_approved_at?->format('Y-m-d'),
            'projet_execution_approved_at' => $mission->projet_execution_approved_at?->format('Y-m-d'),
        ];
    }

    public function updatedMissionDesignOfficeId(mixed $value): void
    {
        if (! $value) {
            $this->mission['bet_name'] = '';

            return;
        }

        $office = DesignOffice::find((int) $value);
        $this->mission['bet_name'] = $office?->name ?? '';
    }

    public function saveMission(): void
    {
        $this->assertCanManage();

        $validated = $this->validate([
            'mission.design_office_id' => 'nullable|integer|exists:design_offices,id',
            'mission.bet_name' => 'nullable|string|max:255',
            'mission.esquisse_approved_at' => 'nullable|date',
            'mission.avant_projet_approved_at' => 'nullable|date',
            'mission.projet_execution_approved_at' => 'nullable|date',
        ])['mission'];

        if (! empty($validated['design_office_id'])) {
            $office = DesignOffice::find($validated['design_office_id']);
            $validated['bet_name'] = $office?->name ?? $validated['bet_name'];
        }

        $project = $this->project();
        $existing = $project->designMissions->first();

        if ($existing) {
            $existing->update($validated);
        } else {
            ProjectDesignMission::create($validated + ['project_id' => $project->id]);
        }

        session()->flash('fiche-saved', __('ui.flash.moe_saved'));
    }

    #[Computed]
    public function designOffices(): array
    {
        return DesignOffice::options();
    }

    private function loadNetworks(Project $project): void
    {
        $recorded = $project->networkLinks->keyBy('network');

        foreach (ProjectNetworkLink::networkCodes() as $network) {
            $link = $recorded->get($network);

            $this->networks[$network] = [
                'connected' => (bool) ($link?->connected ?? false),
                'motif' => $link?->motif ?? '',
            ];
        }
    }

    public function saveNetworks(): void
    {
        $this->assertCanManage();

        $this->validate([
            'networks.*.connected' => 'boolean',
            'networks.*.motif' => 'nullable|string|max:500',
        ]);

        $project = $this->project();

        foreach ($this->networks as $network => $values) {
            ProjectNetworkLink::updateOrCreate(
                ['project_id' => $project->id, 'network' => $network],
                ['connected' => (bool) $values['connected'], 'motif' => $values['motif'] ?: null],
            );
        }

        session()->flash('fiche-saved', __('ui.flash.utilities_saved'));
    }

    public function submitSituation(): void
    {
        abort_unless($this->canReportSituation(), 403);

        $project = $this->project();

        $validated = $this->validate([
            'situationTitle' => 'required|string|max:255',
            'situationPct' => 'required|numeric|min:0|max:100',
        ]);

        PhysicalProgress::create([
            'project_id' => $project->id,
            'title' => $validated['situationTitle'],
            'progress_pct' => $validated['situationPct'],
            'reported_by' => auth()->id(),
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        $this->situationTitle = '';
        $this->situationPct = null;
        $this->resetErrorBag(['situationTitle', 'situationPct']);

        session()->flash('fiche-saved', __('ui.flash.situation_submitted'));
    }

    // -----------------------------------------------------------------
    // Contractors
    // -----------------------------------------------------------------

    public function saveContractor(): void
    {
        $this->assertCanManage();

        $validated = $this->validate([
            'contractorForm.name' => 'required|string|max:255',
            'contractorForm.legal_form' => ['required', Rule::in(['etb', 'sarl', 'eeg', 'eurl', 'spa', 'autre'])],
            'contractorForm.nif' => 'nullable|string|max:50',
            'contractorForm.business_register' => 'nullable|string|max:50',
            'contractorForm.address' => 'nullable|string|max:255',
        ])['contractorForm'];

        $contractor = Contractor::create($validated);

        if ($this->lotForm !== null) {
            $this->lotForm['contractor_id'] = $contractor->id;
        }

        $this->contractorForm = ['name' => '', 'legal_form' => 'etb', 'nif' => '', 'business_register' => '', 'address' => ''];
        $this->showContractorForm = false;
    }

    public function render()
    {
        $project = $this->project();

        return view('livewire.dashboard.project-fiche', [
            'project' => $project,
            'budget' => ProjectBudget::for($project),
            'buckets' => LotBuckets::for($project),
            'contractors' => Contractor::orderBy('name')->get(['id', 'name']),
            'lotStates' => ProjectLot::stateLabels(),
            'phaseLabels' => Project::phaseLabels(),
            'blockingReasons' => ProjectLot::blockingReasonLabels(),
            'milestoneTypes' => LotMilestone::typeLabels(),
            'networkLabels' => ProjectNetworkLink::networkLabels(),
            'revaluations' => $project->aeRevaluations,
        ]);
    }
}
