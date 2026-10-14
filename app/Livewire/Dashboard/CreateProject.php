<?php

namespace App\Livewire\Dashboard;

use App\Models\Commune;
use App\Models\MaturationChecklistDefinition;
use App\Models\Nc13Code;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ProjectOwner;
use App\Models\RisksFile;
use App\Models\TabEtablissement;
use App\Models\Wilaya;
use App\Services\Calculation\CalculationEngine;
use App\Services\Workflow\ProjectWorkflowNotifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.dashboard')]
class CreateProject extends Component
{
    use WithFileUploads;

    public ?string $editingFipId = null;

    public function title(): string
    {
        return $this->editingFipId ? __('ui.create.edit_title') : __('ui.create.title');
    }

    public string $title_fr = '';
    public string $title_ar = '';
    public ?int $project_owner_id = null;

    public string $programme = '';
    public string $region = '';
    public string $wilaya = '';
    public string $commune = '';
    public ?int $establishment_id = null;
    public string $status = 'active';
    public ?string $deadline = null;
    public string $description = '';

    /** @var array<string, bool> */
    public array $maturation = [];

    public string $project_category = '';
    public ?int $nc13_code_id = null;
    public string $budget_type = 'T3';

    #[Computed]
    public function projectCategories(): array
    {
        return Nc13Code::distinct()->orderBy('category')->pluck('category')->toArray();
    }

    #[Computed]
    public function nc13Options(): array
    {
        $options = [];
        foreach ($this->projectCategories as $cat) {
            $codes = Nc13Code::where('category', $cat)
                ->orderBy('code')
                ->get(['id', 'code', 'label']);
            $options[$cat] = $codes->map(fn ($c) => [
                'value' => (string) $c->id,
                'label' => "{$c->code} — {$c->label}",
            ])->all();
        }
        return $options;
    }

    #[Computed]
    public function selectedNc13Code(): ?Nc13Code
    {
        if ($this->nc13_code_id === null) {
            return null;
        }
        return Nc13Code::find($this->nc13_code_id);
    }

    public function updatedNc13CodeId(): void
    {
        $code = $this->selectedNc13Code;
        if ($code) {
            $this->budget_type = $code->budget_type;
        }
    }

    public ?float $ae = null;
    public ?float $cp = null;

    /** @var array<int, array{year_offset: int, amount: float|null}> */
    public array $cpProjections = [
        ['year_offset' => 0, 'amount' => null],
        ['year_offset' => 1, 'amount' => null],
        ['year_offset' => 2, 'amount' => null],
    ];

    public string $company_name = '';
    public string $nif = '';
    public string $business_register = '';
    public ?float $amount_committed = null;
    public ?string $ods_date = null;
    public ?int $project_duration_months = null;

    public bool $showCreateOwnerModal = false;

    public string $new_owner_email = '';
    public string $new_owner_name = '';
    public string $new_owner_company_name = '';
    public string $new_owner_nif = '';
    public string $new_owner_business_register = '';

    public string $notes = '';

    /** @var array<int, array{notes: string, file: mixed}> */
    public array $riskFiles = [['notes' => '', 'file' => null]];

    /** @var array<int, array{title: string, progress_pct: float|null}> */
    public array $physicalProgress = [];

    public function mount(?string $fipId = null): void
    {
        if ($fipId) {
            $this->loadForEditing($fipId);

            return;
        }

        $this->authorize('create', Project::class);
    }

    private function loadForEditing(string $fipId): void
    {
        $project = Project::with(['programme', 'nc13Code', 'maturationItems', 'cpProjections', 'riskFiles', 'physicalProgress.acceptedBy', 'physicalProgress.declinedBy'])
            ->where('fip_id', $fipId)
            ->visibleTo()
            ->firstOrFail();

        $this->authorize('update', $project);

        $this->editingFipId = $project->fip_id;
        $this->title_fr = $project->title_fr;
        $this->title_ar = $project->title_ar;
        $this->description = $project->description ?? '';
        $this->project_owner_id = $project->project_owner_id;
        $this->programme = $project->programme?->code ?? '';

        $this->wilaya = Wilaya::whereKey($project->wilaya_id)->value('name_fr') ?? '';
        $this->syncRegionFromWilaya();
        $this->commune = Commune::where('code_comm', $project->commune_code)->value('lib_comm_fr') ?? '';
        $this->establishment_id = $project->establishment_id;
        $this->nc13_code_id = $project->nc13_code_id;
        $nc13 = $this->selectedNc13Code;
        $this->project_category = $nc13?->category ?? '';
        $this->budget_type = $project->budget_type ?? $nc13?->budget_type ?? 'T3';
        $this->deadline = $project->deadline?->format('Y-m-d');
        $this->ae = $project->ae_da;
        $this->cp = $project->cp_da;
        $this->company_name = $project->company_name ?? '';
        $this->nif = $project->nif ?? '';
        $this->business_register = $project->business_register ?? '';
        $this->amount_committed = $project->amount_committed_da;
        $this->ods_date = $project->ods_date?->format('Y-m-d');
        $this->project_duration_months = $project->project_duration_months;

        $checked = $project->maturationItems->pluck('pivot.checked', 'key')->map(fn ($v) => (bool) $v)->all();
        $this->maturation = $checked;

        $this->cpProjections = $project->cpProjections->sortBy('year_offset')->values()->map(
            fn ($p) => ['year_offset' => $p->year_offset, 'amount' => $p->amount_da]
        )->all();

        if (empty($this->cpProjections)) {
            $this->cpProjections = [
                ['year_offset' => 0, 'amount' => null],
                ['year_offset' => 1, 'amount' => null],
                ['year_offset' => 2, 'amount' => null],
            ];
        }

        if ($this->project_owner_id) {
            $owner = ProjectOwner::find($this->project_owner_id);
            if ($owner) {
                $this->company_name = $owner->company_name ?? $owner->name;
                $this->nif = $owner->nif ?? '';
                $this->business_register = $owner->business_register ?? '';
            }
        }

        $this->riskFiles = $project->riskFiles->map(
            fn ($rf) => ['id' => $rf->id, 'notes' => $rf->notes ?? '', 'existing_file' => $rf->file, 'file' => null]
        )->all();

        if (empty($this->riskFiles)) {
            $this->riskFiles = [['notes' => '', 'file' => null]];
        }

        $this->physicalProgress = $project->physicalProgress->sortBy('created_at')->values()->map(
            fn ($pp) => [
                'id' => $pp->id,
                'title' => $pp->title ?? '',
                'progress_pct' => (float) ($pp->progress_pct ?? 0),
                'status' => $pp->status ?? 'pending',
                'accepted_by_name' => $pp->acceptedBy?->name ?? '',
                'declined_by_name' => $pp->declinedBy?->name ?? '',
            ]
        )->all();

        $this->rejection_reason = (string) ($project->rejection_reason ?? '');
        $this->returned_from = (string) ($project->returned_from ?? '');
        $this->project_status = (string) $project->status;
    }

    public function addRiskFile(): void
    {
        $this->riskFiles[] = ['notes' => '', 'file' => null];
    }

    public function removeRiskFile(int $index): void
    {
        unset($this->riskFiles[$index]);
        $this->riskFiles = array_values($this->riskFiles);
    }

    #[Computed]
    public function canShowPhysicalProgress(): bool
    {
        if (!$this->editingFipId) {
            return false;
        }
        $project = Project::where('fip_id', $this->editingFipId)->first();
        return $project && in_array($project->status, ['approved', 'in_execution']);
    }

    public function addPhysicalProgress(): void
    {
        $this->physicalProgress[] = ['title' => '', 'progress_pct' => null];
    }

    public function removePhysicalProgress(int $index): void
    {
        unset($this->physicalProgress[$index]);
        $this->physicalProgress = array_values($this->physicalProgress);
    }

    public function addCpProjection(): void
    {
        $nextOffset = count($this->cpProjections);
        $this->cpProjections[] = ['year_offset' => $nextOffset, 'amount' => null];
    }

    public function removeCpProjection(int $index): void
    {
        if (count($this->cpProjections) <= 1) {
            return;
        }
        unset($this->cpProjections[$index]);
        $this->cpProjections = array_values($this->cpProjections);
    }

    /** null | 'draft' | 'arbitration' */
    public ?string $successAction = null;
    public ?string $createdFipId = null;

    /** Shown when programme manager / budget controller returned the dossier. */
    public string $rejection_reason = '';

    /** null | arbitration | visa — which step sent the dossier back to DASS. */
    public string $returned_from = '';

    /** Current projects.status — drives which footer actions are shown. */
    public string $project_status = 'draft';

    #[Computed]
    public function isEditing(): bool
    {
        return $this->editingFipId !== null;
    }

    /** Dossier can be (re)submitted into the arbitration/visa pipeline. */
    #[Computed]
    public function canSubmitWorkflow(): bool
    {
        return in_array($this->project_status, ['draft', 'maturation'], true);
    }

    /**
     * Footer mode for DASS actions.
     * new | resubmit_arbitration | resubmit_visa | awaiting | execution | locked
     */
    #[Computed]
    public function workflowFooterMode(): string
    {
        if (in_array($this->project_status, ['draft', 'maturation'], true)) {
            if ($this->returned_from === 'visa') {
                return 'resubmit_visa';
            }
            if ($this->returned_from === 'arbitration') {
                return 'resubmit_arbitration';
            }

            return 'new';
        }

        if (in_array($this->project_status, ['submitted', 'in_arbitration', 'pending_visa'], true)) {
            return 'awaiting';
        }

        if (in_array($this->project_status, ['approved', 'in_execution'], true)) {
            return 'execution';
        }

        return 'locked';
    }

    #[Computed]
    public function etablissements(): Collection
    {
        $wilaya = $this->resolveSelectedWilaya();

        if (! $wilaya) {
            return collect();
        }

        return TabEtablissement::query()
            ->whereIn('wilaya', $wilaya->territoryKeys())
            ->orderBy('libelle')
            ->orderBy('Code établissement')
            ->get(['id', 'wilaya', 'Code établissement', 'libelle']);
    }

    #[Computed]
    public function maturationChecklistItems(): Collection
    {
        return MaturationChecklistDefinition::orderBy('sort_order')
            ->get(['id', 'key', 'label']);
    }

    #[Computed]
    public function communes(): Collection
    {
        $wilaya = $this->resolveSelectedWilaya();

        if (! $wilaya) {
            return collect();
        }

        return $wilaya->communeQuery()
            ->orderBy('lib_comm_fr')
            ->get(['code_comm', 'lib_comm_fr', 'lib_comm_ar', 'code_wilaya']);
    }

    /**
     * Options for the commune Tom Select (value = lib_comm_fr).
     *
     * @return list<array{value: string, text: string}>
     */
    public function getCommuneOptions(): array
    {
        // Fresh query each call (do not rely on a stale Computed cache).
        unset($this->communes);

        return $this->communes
            ->map(fn ($c) => [
                'value' => (string) $c->lib_comm_fr,
                'text' => trim($c->lib_comm_fr.($c->lib_comm_ar ? ' — '.$c->lib_comm_ar : '')),
            ])
            ->values()
            ->all();
    }

    /**
     * Options for the establishment Tom Select.
     *
     * @return list<array{value: string, text: string}>
     */
    public function getEstablishmentOptions(): array
    {
        unset($this->etablissements);

        return $this->etablissements
            ->map(fn ($e) => [
                'value' => (string) $e->id,
                'text' => trim($e->libelle.($e->{'Code établissement'} ? ' ('.$e->{'Code établissement'}.')' : '')),
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function programmes(): Collection
    {
        return Programme::orderBy('code')->get(['code', 'label_fr', 'label_ar', 'type']);
    }

    #[Computed]
    public function projectOwners(): Collection
    {
        return ProjectOwner::orderByRaw('COALESCE(NULLIF(company_name, ""), name) asc')
            ->get(['id', 'email', 'name', 'company_name', 'nif', 'business_register']);
    }

    public function selectProjectOwner(?int $id): void
    {
        if ($id === null) {
            $this->project_owner_id = null;
            $this->company_name = '';
            $this->nif = '';
            $this->business_register = '';
            return;
        }

        $owner = ProjectOwner::find($id);
        if ($owner) {
            $this->project_owner_id = $owner->id;
            $this->company_name = $owner->company_name ?? $owner->name;
            $this->nif = $owner->nif ?? '';
            $this->business_register = $owner->business_register ?? '';
        }
    }

    public function createProjectOwner(): void
    {
        $this->validate([
            'new_owner_email' => 'required|string|email|max:255|unique:project_owners,email',
            'new_owner_name' => 'required|string|max:255',
            'new_owner_company_name' => 'nullable|string|max:255',
            'new_owner_nif' => 'nullable|string|max:50',
            'new_owner_business_register' => 'nullable|string|max:50',
        ]);

        $owner = ProjectOwner::create([
            'email' => $this->new_owner_email,
            'name' => $this->new_owner_name,
            'company_name' => $this->new_owner_company_name ?: null,
            'nif' => $this->new_owner_nif ?: null,
            'business_register' => $this->new_owner_business_register ?: null,
        ]);

        $this->project_owner_id = $owner->id;
        $this->company_name = $owner->company_name ?? $owner->name;
        $this->nif = $owner->nif ?? '';
        $this->business_register = $owner->business_register ?? '';

        $this->showCreateOwnerModal = false;

        $this->reset('new_owner_email', 'new_owner_name', 'new_owner_company_name', 'new_owner_nif', 'new_owner_business_register');

        $display = $owner->name . ($owner->email ? ' (' . $owner->email . ')' : '');

        $this->dispatch('project-owner-created', id: $owner->id, display: $display);
    }

    /**
     * @return list<string>
     */
    public function wilayas(): array
    {
        return Wilaya::orderBy('code')->pluck('name_fr')->all();
    }

    public function planningYearN(): int
    {
        return (int) now()->year;
    }

    /**
     * Live "reste à réaliser" preview while the fiche is being filled in.
     * Uses the configurable `rar` rule so the form agrees with every other
     * screen and with the exports.
     */
    public function rarPreview(): ?float
    {
        if ($this->ae === null && $this->cp === null) {
            return null;
        }

        $engine = app(CalculationEngine::class);

        return $engine->evaluate('rar', $engine->bag([
            'ap_initiale' => (float) ($this->ae ?? 0),
            'total_reevaluations' => 0,
            'cumul_paiements' => (float) ($this->cp ?? 0),
        ]));
    }

    public function updatedWilaya(mixed $value): void
    {
        $this->commune = '';
        $this->establishment_id = null;
        unset($this->etablissements, $this->communes);
        $this->dispatch('wilaya-changed', wilaya: (string) ($value ?? ''));
    }

    private function resolveSelectedWilaya(): ?Wilaya
    {
        if ($this->wilaya === '') {
            return null;
        }

        return Wilaya::where('name_fr', $this->wilaya)->first();
    }

    /** Kept for callers that refresh region-derived fields before save. */
    private function syncRegionFromWilaya(): void
    {
        // Wilaya is the French name; commune/establishment are resolved at persist
        // time against the selected wilaya's territory keys.
    }

    private function assertRegionMapped(): bool
    {
        if ($this->wilaya !== '' && ! $this->resolveSelectedWilaya()) {
            $this->addError('wilaya', __('Select a valid wilaya from the list.'));

            return false;
        }

        if ($this->commune !== '') {
            $wilaya = $this->resolveSelectedWilaya();

            $ok = $wilaya
                && $wilaya->communeQuery()->where('lib_comm_fr', $this->commune)->exists();

            if (! $ok) {
                $this->addError('commune', __('Select a commune that belongs to the chosen wilaya.'));

                return false;
            }
        }

        return true;
    }

    private function maturationComplete(): bool
    {
        foreach ($this->maturationChecklistItems as $item) {
            if (! ($this->maturation[$item->key] ?? false)) {
                return false;
            }
        }

        return true;
    }

    public function saveDraft(): void
    {
        $this->resetErrorBag();
        $this->successAction = null;
        $this->syncRegionFromWilaya();

        $this->validate([
            'title_fr' => 'nullable|max:255',
            'title_ar' => 'nullable|max:500',
            'project_owner_id' => 'nullable|integer|exists:project_owners,id',
            'programme' => 'nullable|max:255',
            'wilaya' => 'nullable|string|max:100',
            'commune' => 'nullable|string|max:100',
            'establishment_id' => 'nullable|integer|exists:tab_etablissement,id',
            'status' => 'nullable|in:active,completed,red_alert',
            'deadline' => 'nullable|date',
            'description' => 'nullable|max:10000',
            'notes' => 'nullable|max:10000',
            'ae' => 'nullable|numeric|min:0',
            'cp' => 'nullable|numeric|min:0',
            'cpProjections' => 'nullable|array',
            'cpProjections.*.amount' => 'nullable|numeric|min:0',
            'project_category' => 'nullable|max:100',
            'nc13_code_id' => 'nullable|integer|exists:nc13_codes,id',
            'budget_type' => 'nullable|in:T3,T4',
            'physicalProgress' => 'nullable|array',
            'physicalProgress.*.title' => 'required|string|max:255',
            'physicalProgress.*.progress_pct' => 'required|numeric|min:0|max:100',
            'company_name' => 'nullable|max:255',
            'nif' => 'nullable|max:80',
            'business_register' => 'nullable|max:80',
            'amount_committed' => 'nullable|numeric|min:0',
            'ods_date' => 'nullable|date',
            'project_duration_months' => 'nullable|integer|min:1|max:600',
        ]);

        if ($this->wilaya !== '' && ! in_array($this->wilaya, $this->wilayas(), true)) {
            $this->addError('wilaya', __('Select a valid wilaya from the list.'));

            return;
        }

        if ($this->title_fr === '' || $this->programme === '' || $this->wilaya === '') {
            $this->addError('title_fr', __('Title, programme and wilaya are required to save a draft.'));

            return;
        }

        // Never demote an approved / in-pipeline dossier back to draft via "Save".
        $persistStatus = $this->canSubmitWorkflow
            ? 'draft'
            : $this->project_status;

        if (in_array($persistStatus, ['submitted', 'in_arbitration', 'pending_visa', 'closed'], true)) {
            $this->addError('title_fr', __('ui.create.cannot_edit_while_awaiting'));

            return;
        }

        $this->persistDossier($persistStatus);
        $this->project_status = $persistStatus;
        $this->successAction = $this->canSubmitWorkflow ? 'draft' : 'updated';
    }

    public function submitToArbitration(): void
    {
        if (! $this->canSubmitWorkflow) {
            $this->addError('title_fr', __('ui.create.cannot_submit_current_status'));

            return;
        }

        if ($this->returned_from === 'visa') {
            $this->addError('title_fr', __('ui.create.use_resubmit_visa'));

            return;
        }

        if (! $this->validateForWorkflowSubmit()) {
            return;
        }

        DB::transaction(function () {
            $this->persistDossier('in_arbitration');
        });

        if ($this->createdFipId) {
            $project = Project::where('fip_id', $this->createdFipId)->first();
            if ($project) {
                app(ProjectWorkflowNotifier::class)->notifySubmitted($project);
            }
            $this->rejection_reason = '';
            $this->returned_from = '';
            $this->project_status = 'in_arbitration';
        }

        session()->flash('success', __('ui.flash.project_submitted', ['id' => $this->createdFipId]));
        $this->redirectRoute('dashbored.projects', navigate: true);
    }

    /** After a budget-controller decline: send the corrected dossier back to visa. */
    public function resubmitToVisa(): void
    {
        if (! $this->canSubmitWorkflow || $this->returned_from !== 'visa') {
            $this->addError('title_fr', __('ui.create.use_submit_arbitration'));

            return;
        }

        if (! $this->validateForWorkflowSubmit()) {
            return;
        }

        DB::transaction(function () {
            $this->persistDossier('pending_visa');
        });

        if ($this->createdFipId) {
            $project = Project::where('fip_id', $this->createdFipId)->first();
            if ($project) {
                app(ProjectWorkflowNotifier::class)->notifyPendingVisa($project);
            }
            $this->rejection_reason = '';
            $this->returned_from = '';
            $this->project_status = 'pending_visa';
        }

        session()->flash('success', __('ui.flash.project_resubmitted_visa', ['id' => $this->createdFipId]));
        $this->redirectRoute('dashbored.projects', navigate: true);
    }

    private function validateForWorkflowSubmit(): bool
    {
        $this->resetErrorBag();
        $this->successAction = null;
        $this->syncRegionFromWilaya();

        $this->validate([
            'title_fr' => 'required|string|min:3|max:255',
            'title_ar' => 'required|string|min:3|max:500',
            'project_owner_id' => 'required|integer|exists:project_owners,id',
            'programme' => 'required',
            'wilaya' => ['required', Rule::in($this->wilayas())],
            'commune' => 'nullable|string|max:100',
            'establishment_id' => [
                'required',
                'integer',
                Rule::exists('tab_etablissement', 'id')->where(function ($q) {
                    $wilaya = $this->resolveSelectedWilaya();
                    if (! $wilaya) {
                        return $q->whereRaw('1 = 0');
                    }

                    return $q->whereIn('wilaya', $wilaya->territoryKeys());
                }),
            ],
            'deadline' => 'required|date',
            'description' => 'nullable|max:10000',
            'ae' => 'required|numeric|min:0',
            'cp' => 'required|numeric|min:0',
            'cpProjections' => 'required|array|min:1',
            'cpProjections.*.amount' => 'required|numeric|min:0',
            'project_category' => 'required',
            'nc13_code_id' => 'required|integer|exists:nc13_codes,id',
            'budget_type' => 'required|in:T3,T4',
            'physicalProgress' => 'nullable|array',
            'physicalProgress.*.title' => 'required_with:physicalProgress|string|max:255',
            'physicalProgress.*.progress_pct' => 'required_with:physicalProgress|numeric|min:0|max:100',
            'company_name' => 'required|string|max:255',
            'nif' => 'required|string|max:50',
            'business_register' => 'required|string|max:80',
            'amount_committed' => 'required|numeric|min:0',
            'ods_date' => 'required|date',
            'project_duration_months' => 'required|integer|min:1|max:600',
        ]);

        if (! $this->assertRegionMapped()) {
            return false;
        }

        if (! $this->maturationComplete()) {
            $labels = $this->maturationChecklistItems->pluck('label')->implode(', ');
            $this->addError(
                'maturation_checklist',
                __('Confirm all maturation items (' . $labels . ') before submitting to arbitration.')
            );

            return false;
        }

        return true;
    }

    private function persistDossier(string $status): void
    {
        $programme = Programme::where('code', $this->programme)->first();
        if (! $programme) {
            $this->addError('programme', __('Invalid programme selected.'));

            return;
        }

        $wilayaRow = Wilaya::where('name_fr', $this->wilaya)->first(['id', 'code']);
        if (! $wilayaRow) {
            $this->addError('wilaya', __('Wilaya mapping not found.'));

            return;
        }

        $communeCode = null;
        if ($this->commune !== '') {
            $communeCode = $wilayaRow->communeQuery()
                ->where('lib_comm_fr', $this->commune)
                ->value('code_comm');
        }

        $userId = request()->user()?->id;
        if (! $userId) {
            $this->addError('title_fr', __('Authentication required. Please log in again.'));

            return;
        }

        $isEdit = $this->editingFipId !== null;
        $payload = [
            'title_fr' => $this->title_fr ?: 'Sans intitulé',
            'title_ar' => $this->title_ar ?: '—',
            'description' => $this->description ?: null,
            'programme_id' => $programme->id,
            'wilaya_id' => $wilayaRow->id,
            'commune_code' => $communeCode,
            'establishment_id' => $this->establishment_id,
            'project_owner_id' => $this->project_owner_id,
            'deadline' => $this->deadline ?: null,
            'ae_da' => $this->ae,
            'cp_da' => $this->cp,
            'amount_committed_da' => $this->amount_committed,
            'ods_date' => $this->ods_date ?: null,
            'project_duration_months' => $this->project_duration_months,
            'company_name' => $this->company_name,
            'nif' => $this->nif,
            'business_register' => $this->business_register,
            'nc13_code_id' => $this->nc13_code_id,
            'budget_type' => $this->budget_type,
            'status' => $status,
        ];

        if (in_array($status, ['submitted', 'in_arbitration', 'pending_visa'], true)) {
            $payload['rejection_reason'] = null;
            $payload['returned_from'] = null;
        }

        if ($isEdit) {
            $project = Project::where('fip_id', $this->editingFipId)->visibleTo()->firstOrFail();
            $this->authorize('update', $project);
            $project->update($payload);
        } else {
            $this->authorize('create', Project::class);
            $project = Project::create($payload + [
                'fip_id' => $this->generateFipId(),
                'nature' => 'new',
                'phase' => Project::PHASE_ETUDES,
                'created_by' => $userId,
            ]);
            $this->editingFipId = $project->fip_id;
        }

        $this->createdFipId = $project->fip_id;

        $checkedKeys = array_keys(array_filter($this->maturation));
        $definitionIds = MaturationChecklistDefinition::whereIn('key', $checkedKeys)->pluck('id');
        $project->maturationItems()->sync($definitionIds->mapWithKeys(fn ($id) => [$id => ['checked' => true]])->all());

        $project->cpProjections()->delete();
        foreach ($this->cpProjections as $proj) {
            if ($proj['amount'] === null) {
                continue;
            }
            $project->cpProjections()->create([
                'year_offset' => $proj['year_offset'],
                'amount_da' => $proj['amount'],
            ]);
        }

        $project->riskFiles()->delete();
        foreach ($this->riskFiles as $rf) {
            if (! empty($rf['existing_file']) && ! ($rf['file'] ?? null)) {
                RisksFile::create([
                    'project_id' => $project->id,
                    'notes' => $rf['notes'] ?? null,
                    'file' => $rf['existing_file'],
                ]);
            } elseif ($rf['file'] ?? null) {
                $path = $rf['file']->store('risk-files', 'public');
                RisksFile::create([
                    'project_id' => $project->id,
                    'notes' => $rf['notes'] ?? null,
                    'file' => $path,
                ]);
            }
        }

        $project->physicalProgress()->delete();
        foreach ($this->physicalProgress as $pp) {
            if (empty($pp['title']) || $pp['progress_pct'] === null) {
                continue;
            }
            $project->physicalProgress()->create([
                'title' => $pp['title'],
                'progress_pct' => $pp['progress_pct'],
                'reported_by' => $userId,
                'status' => 'pending',
                'reported_at' => now(),
            ]);
        }
    }

    private function generateFipId(): string
    {
        $year = now()->format('Y');
        $prefix = "FIP-{$year}-";
        $last = Project::withTrashed()
            ->where('fip_id', 'like', "{$prefix}%")
            ->orderBy('fip_id', 'desc')
            ->value('fip_id');

        if ($last) {
            $num = (int) Str::afterLast($last, '-') + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.dashboard.create-project');
    }
}
