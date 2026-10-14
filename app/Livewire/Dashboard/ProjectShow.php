<?php

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Models\AeEngagement;
use App\Models\AeRevaluation;
use App\Models\Commune;
use App\Models\CpPayment;
use App\Models\PhysicalProgress;
use App\Models\Project;
use App\Models\User;
use App\Models\Wilaya;
use App\Services\Budget\ProjectBudget;
use App\Services\Workflow\ProjectWorkflowNotifier;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.dashboard')]
class ProjectShow extends Component
{
    use WithFileUploads;

    public function title(): string
    {
        return __('ui.show.title');
    }
    public string $projectId = '';

    /** Primary key, for the private broadcast channel. */
    public ?int $projectKey = null;

    /** Bumped by every broadcast, so the view can flag a live update. */
    public int $liveTick = 0;

    public bool $showRejectModal = false;

    public string $rejectionReason = '';

    public ?float $engageAmount = null;

    public string $engageDescription = '';

    public ?string $engageDate = null;

    public ?int $cpAcceptingId = null;

    public ?int $cpRejectingId = null;

    public string $cpPaymentType = '';

    public string $cpMandateNumber = '';

    public string $cpRejectionCause = '';

    /** Receipt / ministry invoice attached when validating a CP request. */
    public $cpReceipt = null;

    /*     * @var array<string, mixed> */
    public array $detail = [];

    /**
     * Everything buildDetail() and the budget engine read, so a page render
     * stays at a fixed number of queries.
     *
     * @var list<string>
     */
    private const RELATIONS = [
        'programme',
        'nc13Code',
        'projectOwner',
        'maturationItems',
        'cpProjections',
        'riskFiles',
        'physicalProgress.reporter.wilaya',
        'physicalProgress.acceptedBy',
        'physicalProgress.declinedBy',
        'cpPayments.creator.wilaya',
        'aeEngagements.creator.wilaya',
        'aeRevaluations.submitter.wilaya',
        'creator.wilaya',
        'aeCpEnvelopes',
        'auditLogs.user',
    ];

    public function mount(string $projectId): void
    {
        $project = Project::with(self::RELATIONS)
            ->where('fip_id', $projectId)
            ->visibleTo()
            ->first();

        if (! $project) {
            abort(404);
        }

        $this->authorize('view', $project);

        $this->projectId = $projectId;
        $this->projectKey = $project->id;
        $this->engageDate = now()->format('Y-m-d');
        $this->detail = $this->buildDetail($project);
    }

    /**
     * Live updates for this operation only. Channel authorization reuses the
     * project policy, so a subscription cannot widen what a role may read.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        if (! $this->projectKey) {
            return [];
        }

        return [
            "echo-private:projects.{$this->projectKey},.metrics.recalculated" => 'refreshLiveData',
            "echo-private:projects.{$this->projectKey},.alert.raised" => 'refreshLiveData',
        ];
    }

    public function refreshLiveData(): void
    {
        $project = Project::with(self::RELATIONS)->find($this->projectKey);

        if (! $project) {
            return;
        }

        $this->liveTick++;
        $this->detail = $this->buildDetail($project);
    }

    private function buildDetail(Project $project): array
    {
        $wilayaName = $project->wilaya_id
            ? Wilaya::whereKey($project->wilaya_id)->value('name_fr') ?? ''
            : '';

        $communeName = $project->commune_code
            ? Commune::where('code_comm', $project->commune_code)->value('lib_comm_fr') ?? ''
            : '';

        // All arithmetic goes through the configurable engine.
        $budget = ProjectBudget::for($project);

        $cpProjected = (float) $project->cpProjections->sum('amount_da');

        $cpByYear = [];
        foreach ($project->cpProjections->sortBy('year_offset') as $i => $cp) {
            $key = $i === 0 ? 'cp_year_n' : ($i === 1 ? 'cp_year_n1' : ($i === 2 ? 'cp_year_n2' : null));
            if ($key) {
                $cpByYear[$key] = (float) ($cp->amount_da ?? 0);
            }
        }

        $maturation = [];
        foreach ($project->maturationItems as $item) {
            $key = match ($item->key) {
                'land' => 'maturation_land',
                'studies' => 'maturation_studies',
                'quote' => 'maturation_quote',
                'technical_opinion' => 'maturation_technical_opinion',
                default => 'maturation_' . $item->key,
            };
            $maturation[$key] = (bool) ($item->pivot->checked ?? false);
        }

        $cpNotified = $budget->get('cp_notifies');
        $validatedPayments = $project->cpPayments->where('status', 'validated');
        $totalValidatedCp = $budget->cumulPaiements();

        return [
            'id' => $project->fip_id,
            'title_fr' => $project->title_fr,
            'title_ar' => $project->title_ar,
            'title' => $project->title_fr,
            'project_owner' => $project->projectOwner?->name ?? '—',
            'program' => $project->programme?->label_fr ?? $project->programme?->code ?? '—',
            'wilaya' => $wilayaName,
            'commune' => $communeName,
            'region_label' => '',
            'description' => $project->description ?? '',
            'deadline' => $project->deadline?->format('Y-m-d') ?? '',
            'status' => $project->displayStatus(),
            'rejection_reason' => $project->rejection_reason ?? '',
            'can_arbitrate' => auth()->user()?->can('arbitrate', $project) ?? false,
            'can_visa' => auth()->user()?->can('visa', $project) ?? false,
            'can_engage_ae' => auth()->user()?->can('engageAe', $project) ?? false,
            'can_edit' => auth()->user()?->can('update', $project) ?? false,
            'can_report_situation' => (auth()->user()?->can('create', PhysicalProgress::class) ?? false)
                && ! $project->isLocked()
                && in_array($project->status, ['approved', 'in_execution', 'completed'], true),
            'can_validate_situation' => auth()->user()?->hasPermission(Permission::SituationsValidate->value) ?? false,
            'can_approve_reevaluation' => auth()->user()?->hasPermission(Permission::ReevaluationApprove->value) ?? false,
            'can_view_cp' => auth()->user()?->hasPermission(Permission::CpView->value) ?? false,
            'pending_situations' => $project->physicalProgress
                ->where('status', 'pending')
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($pp) => [
                    'id' => $pp->id,
                    'title' => $pp->title ?? '',
                    'progress_pct' => (float) ($pp->progress_pct ?? 0),
                    'submitted_by' => User::requesterLabelFor($pp->reporter, $wilayaName),
                    'created_at' => $pp->created_at?->format('d/m/Y H:i') ?? '',
                ])
                ->all(),
            'pending_cp_requests' => $project->cpPayments
                ->where('status', 'pending')
                ->values()
                ->map(fn (CpPayment $c) => [
                    'id' => $c->id,
                    'amount_da' => (float) $c->amount_da,
                    'description' => $c->description ?? '',
                    'submitted_by' => User::requesterLabelFor($c->creator, $wilayaName),
                    'created_at' => $c->created_at?->format('d/m/Y H:i') ?? '',
                    'can_decide' => auth()->user()?->can('validatePayment', $c) ?? false,
                ])
                ->all(),
            'pending_reevaluations' => $project->aeRevaluations
                ->whereIn('status', ['submitted', 'draft', 'pending'])
                ->values()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'sequence' => $r->sequence,
                    'year' => $r->year,
                    'amount_da' => (float) $r->requested_amount_da,
                    'decision_ref' => $r->decision_ref ?? '',
                    'justification' => $r->justification ?? '',
                    'submitted_by' => User::requesterLabelFor($r->submitter, $wilayaName),
                    'can_decide' => auth()->user()?->can('approve', $r) ?? false,
                ])
                ->all(),
            'requester_scope' => User::requesterLabelFor($project->creator, $wilayaName),
            'traffic' => $budget->trafficLight(),
            'spu' => $budget->spu(),
            'physical' => $budget->tauxPhysique(),

            'ap_initiale_da' => $budget->apInitiale(),
            'ap_actuelle_da' => $budget->apActuelle(),
            'total_reevaluations_da' => $budget->totalReevaluations(),
            'ae_da' => $budget->apActuelle(),
            'cp_da' => $cpNotified,
            'cp_projected_da' => $cpProjected,
            'cp_consumed_da' => $budget->cumulPaiements(),
            'cumul_engagements_da' => $budget->cumulEngagements(),
            'recorded_engagements_da' => (float) $project->aeEngagements->sum('amount_da'),
            'pec_da' => $budget->pec(),
            'rar_da' => $budget->rar(),
            // Same definition as the engine: AP actuelle − recorded AE engagements.
            'reliquat_ae_da' => $budget->reliquatAe(),
            'financial_progress_pct' => $budget->tauxFinancier(),
            'ae_engaged_pct' => $budget->get('tc_ae'),
            'cp_consumed_pct_of_notified' => $budget->get('tc_cp'),
            'icr_pct' => $budget->get('icr'),

            'ae_engagements' => $project->aeEngagements->sortByDesc('engaged_at')->values()->map(fn ($e) => [
                'id' => $e->id,
                'amount_da' => (float) $e->amount_da,
                'description' => $e->description ?? '',
                'engaged_at' => $e->engaged_at?->format('d/m/Y') ?? '',
                'created_by' => $e->creator?->name ?? '—',
            ])->all(),

            'amount_committed_da' => (float) ($project->amount_committed_da ?? 0),
            'ods_date' => $project->ods_date?->format('Y-m-d') ?? '',
            'project_duration_months' => $project->project_duration_months ?? '',
            'company_name' => $project->company_name ?? '',
            'nif' => $project->nif ?? '',
            'business_register' => $project->business_register ?? '',

            'budget_type' => $project->budget_type ?? '',
            'nc13_code' => $project->nc13Code?->code ?? '',
            'nc13_label' => $project->nc13Code?->label ?? '',
            'nc13_class_label' => $project->nc13Code?->class_label ?? '',
            'nc13_category' => $project->nc13Code?->category ?? '',

            'maturation_land' => $maturation['maturation_land'] ?? false,
            'maturation_studies' => $maturation['maturation_studies'] ?? false,
            'maturation_quote' => $maturation['maturation_quote'] ?? false,
            'maturation_technical_opinion' => $maturation['maturation_technical_opinion'] ?? false,

            'cp_year_n' => $cpByYear['cp_year_n'] ?? 0,
            'cp_year_n1' => $cpByYear['cp_year_n1'] ?? 0,
            'cp_year_n2' => $cpByYear['cp_year_n2'] ?? 0,

            'spu_breakdown' => $budget->spuBreakdown(),
            'metrics' => $budget->grouped(),
            'documents' => [],
            'audit_trail' => $project->auditLogs()
                ->with('user:id,name')
                ->limit(20)
                ->get()
                ->map(fn ($log) => [
                    'at' => $log->created_at?->format('d/m/Y H:i') ?? '',
                    'action' => $log->action,
                    'user' => $log->user?->name ?? 'Système',
                ])
                ->all(),
            'notes' => '',
            'risk_files' => $project->riskFiles->map(fn ($rf) => [
                'id' => $rf->id,
                'notes' => $rf->notes ?? '',
                'file' => $rf->file,
                'url' => $rf->file ? \Illuminate\Support\Facades\Storage::url($rf->file) : null,
            ])->all(),
            'physical_progress' => $project->physicalProgress
                ->sortBy(fn ($pp) => (($pp->status ?? 'pending') === 'pending' ? 0 : 1).($pp->created_at?->timestamp ?? 0))
                ->values()
                ->map(fn ($pp) => [
                'id' => $pp->id,
                'title' => $pp->title ?? '',
                'progress_pct' => (float) ($pp->progress_pct ?? 0),
                'status' => $pp->status ?? 'pending',
                'accepted_by' => $pp->acceptedBy?->name ?? '',
                'declined_by' => $pp->declinedBy?->name ?? '',
                'added_by' => User::requesterLabelFor($pp->reporter, $wilayaName),
                'added_at' => $pp->created_at?->format('d M Y H:i') ?? '—',
            ])->all(),
            'cp_notified_da' => $cpNotified,
            'cp_validated_da' => $totalValidatedCp,
            'cp_financial_pct' => $budget->get('tc_cp'),
            'cp_validated_payments' => $validatedPayments->sortBy('created_at')->values()->map(fn ($p) => [
                'amount_da' => (float) $p->amount_da,
                'pct' => $cpNotified > 0 ? round(((float) $p->amount_da / $cpNotified) * 100, 1) : 0,
                'payment_type' => $p->payment_type,
                'mandate_number' => $p->mandate_number,
                'paid_at' => $p->paid_at?->format('d M Y'),
                'created_at' => $p->created_at?->format('d M Y H:i'),
            ])->all(),
        ];
    }

    public function acceptArbitration(): void
    {
        $project = $this->projectOrFail();
        $this->authorize('arbitrate', $project);

        $project->update([
            'status' => 'pending_visa',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'rejection_reason' => null,
            'returned_from' => null,
        ]);

        app(ProjectWorkflowNotifier::class)->notifyPendingVisa($project->fresh());

        $this->showRejectModal = false;
        $this->rejectionReason = '';
        session()->flash('success', __('ui.show.arbitration_sent_to_visa'));
        $this->reloadProject();
    }

    public function acceptVisa(): void
    {
        $project = $this->projectOrFail();
        $this->authorize('visa', $project);

        $project->update([
            'status' => 'approved',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'rejection_reason' => null,
            'returned_from' => null,
        ]);

        app(ProjectWorkflowNotifier::class)->notifyDecision($project->fresh(), 'approved');

        $this->showRejectModal = false;
        $this->rejectionReason = '';
        session()->flash('success', __('ui.show.visa_granted'));
        $this->reloadProject();
    }

    public function openRejectModal(): void
    {
        $project = $this->projectOrFail();

        if (auth()->user()?->can('arbitrate', $project)) {
            $this->authorize('arbitrate', $project);
        } else {
            $this->authorize('visa', $project);
        }

        $this->showRejectModal = true;
        $this->rejectionReason = '';
        $this->resetErrorBag('rejectionReason');
    }

    public function cancelReject(): void
    {
        $this->showRejectModal = false;
        $this->rejectionReason = '';
        $this->resetErrorBag('rejectionReason');
    }

    public function confirmRejectArbitration(): void
    {
        $project = $this->projectOrFail();

        if (auth()->user()?->can('arbitrate', $project)) {
            $this->authorize('arbitrate', $project);
        } else {
            $this->authorize('visa', $project);
        }

        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:2000',
        ]);

        $reason = trim($this->rejectionReason);
        $returnedFrom = $project->status === 'pending_visa' ? 'visa' : 'arbitration';

        $project->update([
            'status' => 'draft',
            'rejection_reason' => $reason,
            'returned_from' => $returnedFrom,
            'validated_by' => null,
            'validated_at' => null,
        ]);

        app(ProjectWorkflowNotifier::class)->notifyDecision($project->fresh(), 'rejected', $reason);

        $this->showRejectModal = false;
        $this->rejectionReason = '';
        session()->flash('success', __('ui.show.arbitration_rejected'));
        $this->reloadProject();
    }

    public function engageAe(): void
    {
        $project = $this->projectOrFail();
        $this->authorize('engageAe', $project);

        $this->validate([
            'engageAmount' => 'required|numeric|min:1',
            'engageDate' => 'required|date',
            'engageDescription' => 'nullable|string|max:255',
        ]);

        $amount = (float) $this->engageAmount;
        $recorded = (float) $project->aeEngagements()->sum('amount_da');
        $ceiling = ProjectBudget::for($project)->apActuelle();

        if ($ceiling > 0 && ($recorded + $amount) > ($ceiling + 0.009)) {
            $this->addError(
                'engageAmount',
                __('ui.show.ae_engage_over_ceiling', [
                    'remaining' => number_format(max(0, $ceiling - $recorded), 2, ',', ' '),
                    'ceiling' => number_format($ceiling, 2, ',', ' '),
                ])
            );

            return;
        }

        $engagement = DB::transaction(function () use ($project, $amount) {
            $engagement = AeEngagement::create([
                'project_id' => $project->id,
                'amount_da' => $amount,
                'description' => trim($this->engageDescription) ?: null,
                'engaged_at' => $this->engageDate,
                'created_by' => auth()->id(),
            ]);

            if ($project->status === 'approved') {
                $project->update(['status' => 'in_execution']);
            }

            return $engagement;
        });

        app(ProjectWorkflowNotifier::class)->notifyAeEngaged($project->fresh(['creator']), $engagement);

        $this->engageAmount = null;
        $this->engageDescription = '';
        $this->engageDate = now()->format('Y-m-d');
        $this->resetErrorBag(['engageAmount', 'engageDate', 'engageDescription']);

        session()->flash('success', __('ui.flash.ae_engaged', [
            'id' => $project->fip_id,
            'amount' => number_format($amount, 2, ',', ' '),
        ]));
        $this->reloadProject();
    }

    public function approveRevaluation(int $id): void
    {
        $project = $this->projectOrFail();
        $revaluation = $project->aeRevaluations()->whereKey($id)->firstOrFail();
        $this->authorize('approve', $revaluation);

        $revaluation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $project->update([
            'reevaluation_count' => $project->aeRevaluations()->where('status', 'approved')->count(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationDecision(
            $project->fresh(),
            $revaluation->fresh(['submitter']),
            'approved'
        );

        session()->flash('success', __('ui.flash.reeval_approved'));
        $this->reloadProject();
    }

    public function rejectRevaluation(int $id): void
    {
        $project = $this->projectOrFail();
        $revaluation = $project->aeRevaluations()->whereKey($id)->firstOrFail();
        $this->authorize('approve', $revaluation);

        $revaluation->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationDecision(
            $project->fresh(),
            $revaluation->fresh(['submitter']),
            'rejected'
        );

        session()->flash('success', __('ui.flash.reeval_rejected'));
        $this->reloadProject();
    }

    public function showCpAcceptForm(int $id): void
    {
        $this->cpAcceptingId = $id;
        $this->cpRejectingId = null;
        $this->cpPaymentType = 'regular';
        $this->cpMandateNumber = '';
        $this->cpRejectionCause = '';
        $this->cpReceipt = null;
        $this->resetErrorBag(['cpPaymentType', 'cpMandateNumber', 'cpReceipt']);
    }

    public function showCpRejectForm(int $id): void
    {
        $this->cpRejectingId = $id;
        $this->cpAcceptingId = null;
        $this->cpRejectionCause = '';
        $this->cpReceipt = null;
    }

    public function cancelCpAction(): void
    {
        $this->cpAcceptingId = null;
        $this->cpRejectingId = null;
        $this->cpPaymentType = '';
        $this->cpMandateNumber = '';
        $this->cpRejectionCause = '';
        $this->cpReceipt = null;
    }

    public function confirmCpAccept(): void
    {
        $this->validate([
            'cpPaymentType' => 'required|in:regular,credit_request,revaluation',
            'cpMandateNumber' => 'nullable|string|max:255',
            'cpReceipt' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $payment = CpPayment::with('project')->findOrFail($this->cpAcceptingId);
        $this->authorize('validatePayment', $payment);

        $data = [
            'status' => 'validated',
            'payment_type' => $this->cpPaymentType,
            'mandate_number' => $this->cpMandateNumber ?: null,
        ];

        if ($this->cpReceipt) {
            $data['invoice_by_ministry'] = $this->cpReceipt->store('cp-invoices/ministry', 'public');
        }

        $payment->update($data);
        $payment = $payment->fresh(['creator']);

        app(ProjectWorkflowNotifier::class)->notifyCpDecision(
            $payment->project ?? $this->projectOrFail(),
            $payment,
            'validated'
        );

        session()->flash('success', __('ui.flash.cp_validated', ['id' => $payment->id]));
        $this->cancelCpAction();
        $this->reloadProject();
    }

    public function confirmCpReject(): void
    {
        $this->validate([
            'cpRejectionCause' => 'required|string|max:2000',
        ]);

        $payment = CpPayment::with('project')->findOrFail($this->cpRejectingId);
        $this->authorize('validatePayment', $payment);

        $payment->update([
            'status' => 'rejected',
            'rejection_cause' => $this->cpRejectionCause,
        ]);
        $payment = $payment->fresh(['creator', 'project']);

        app(ProjectWorkflowNotifier::class)->notifyCpDecision(
            $payment->project,
            $payment,
            'rejected'
        );

        session()->flash('success', __('ui.flash.cp_rejected', ['id' => $payment->id]));
        $this->cancelCpAction();
        $this->reloadProject();
    }

    private function projectOrFail(): Project
    {
        $project = Project::with(['aeEngagements', 'aeRevaluations', 'cpPayments'])
            ->where('fip_id', $this->projectId)
            ->visibleTo()
            ->first();

        if (! $project) {
            abort(404);
        }

        return $project;
    }

    public function acceptProgress(int $id): void
    {
        $pp = PhysicalProgress::findOrFail($id);
        $this->authorize('validateEntry', $pp);

        $pp->update([
            'status' => 'accepted',
            'accepted_by' => request()->user()->id,
            'declined_by' => null,
        ]);
        $this->reloadProject();
    }

    public function declineProgress(int $id): void
    {
        $pp = PhysicalProgress::findOrFail($id);
        $this->authorize('validateEntry', $pp);

        $pp->update([
            'status' => 'declined',
            'declined_by' => request()->user()->id,
            'accepted_by' => null,
        ]);
        $this->reloadProject();
    }

    private function reloadProject(): void
    {
        $project = Project::with(self::RELATIONS)
            ->where('fip_id', $this->projectId)
            ->visibleTo()
            ->first();

        if ($project) {
            $this->detail = $this->buildDetail($project);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.project-show');
    }
}
