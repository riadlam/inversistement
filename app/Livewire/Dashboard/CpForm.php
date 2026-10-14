<?php

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Models\CpPayment;
use App\Models\Project;
use App\Models\User;
use App\Services\Budget\ProjectBudget;
use App\Services\Workflow\ProjectWorkflowNotifier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.dashboard')]
class CpForm extends Component
{
    use WithFileUploads;

    public function title(): string
    {
        return __('ui.cp.title');
    }

    public string $projectId = '';
    public ?Project $project = null;

    public string $amount_da = '';
    public string $description = '';

    /** @var list<string> */
    public array $documentNotes = [];

    /** Flat file slots — Livewire cannot nest TemporaryUploadedFile inside assoc arrays. */
    public $documentFiles = [];

    // Admin accept/reject state
    public ?int $acceptingId = null;
    public ?int $rejectingId = null;
    public string $paymentType = '';
    public $invoiceByMinistry = null;
    public string $mandateNumber = '';
    public string $rejectionCause = '';

    public function mount(string $projectId): void
    {
        $this->project = Project::with(['cpPayments.creator.wilaya', 'wilaya'])
            ->where('fip_id', $projectId)
            ->visibleTo()
            ->firstOrFail();

        $this->authorize('view', $this->project);

        $this->projectId = $projectId;
        $this->addDocument();
    }

    /** Contrôleur budgétaire / ordonnateur / administrateur may visa a mandatement. */
    public function getCanValidateProperty(): bool
    {
        return auth()->user()?->hasPermission(Permission::CpValidate->value) ?? false;
    }

    public function getCanRequestProperty(): bool
    {
        return auth()->user()?->hasPermission(Permission::CpRequest->value) ?? false;
    }

    /**
     * TDR Fiche 03: request must not exceed RAR.
     * Pending requests already reserve part of that headroom.
     */
    public function getRarAvailableProperty(): float
    {
        $budget = ProjectBudget::for($this->project->loadMissing([
            'aeEngagements', 'aeRevaluations', 'aeCpEnvelopes', 'cpPayments',
            'lots', 'land', 'physicalProgress', 'maturationItems',
        ]));

        $rar = $budget->rar();
        $pending = (float) $this->project->cpPayments()
            ->where('status', 'pending')
            ->sum('amount_da');

        return round(max(0, $rar - $pending), 2);
    }

    public function getPaymentsProperty(): array
    {
        $wilayaName = $this->project->wilaya?->name_fr;

        return $this->project->cpPayments()
            ->with('creator.wilaya')
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'reference' => 'CP-' . $this->project->fip_id . '-' . str_pad($p->id, 3, '0', STR_PAD_LEFT),
                'amount_da' => (float) $p->amount_da,
                'description' => $p->description,
                'status' => $p->status ?? 'pending',
                'payment_type' => $p->payment_type,
                'created_at' => $p->created_at?->format('d M Y H:i'),
                'submitted_by' => User::requesterLabelFor($p->creator, $wilayaName),
                'invoice' => $p->invoice,
                'invoice_by_ministry' => $p->invoice_by_ministry,
                'mandate_number' => $p->mandate_number,
                'paid_at' => $p->paid_at?->format('d M Y'),
                'rejection_cause' => $p->rejection_cause,
                'payment_type_label' => $p->payment_type ? match($p->payment_type) {
                    'regular' => 'Regular',
                    'credit_request' => 'Credit Request',
                    'revaluation' => 'Revaluation',
                    default => $p->payment_type,
                } : null,
            ])->all();
    }

    public function getReferenceIdProperty(): string
    {
        $count = CpPayment::where('project_id', $this->project->id)->count();
        return 'CP-' . $this->project->fip_id . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public function addDocument(): void
    {
        $this->documentNotes[] = '';
    }

    public function removeDocument(int $index): void
    {
        $files = is_array($this->documentFiles) ? $this->documentFiles : [];

        unset($this->documentNotes[$index], $files[$index]);

        $this->documentNotes = array_values($this->documentNotes);
        $this->documentFiles = array_values($files);
    }

    public function submit(): void
    {
        $this->authorize('create', CpPayment::class);

        $this->validate([
            'amount_da' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'documentNotes' => 'nullable|array',
            'documentNotes.*' => 'nullable|string|max:1000',
            'documentFiles' => 'nullable|array',
            'documentFiles.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $amount = (float) $this->amount_da;
        $available = $this->rarAvailable;

        if ($amount > ($available + 0.009)) {
            $this->addError(
                'amount_da',
                __('ui.cp.over_rar', [
                    'available' => number_format($available, 2, ',', ' '),
                    'rar' => number_format(ProjectBudget::for($this->project)->rar(), 2, ',', ' '),
                ])
            );

            return;
        }

        $paths = [];
        foreach ($this->documentFiles as $file) {
            if ($file) {
                $paths[] = $file->store('cp-invoices', 'public');
            }
        }

        CpPayment::create([
            'project_id' => $this->project->id,
            'amount_da' => $amount,
            'description' => $this->description,
            'invoice' => ! empty($paths) ? json_encode($paths) : null,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', __('ui.flash.cp_submitted', ['id' => $this->project->fip_id]));
        $this->redirect(route('dashbored.projects'));
    }

    // --- Admin actions ---

    public function showAcceptForm(int $id): void
    {
        $this->acceptingId = $id;
        $this->rejectingId = null;
        $this->paymentType = '';
        $this->invoiceByMinistry = null;
        $this->mandateNumber = '';
    }

    public function showRejectForm(int $id): void
    {
        $this->rejectingId = $id;
        $this->acceptingId = null;
        $this->rejectionCause = '';
    }

    public function cancelAction(): void
    {
        $this->acceptingId = null;
        $this->rejectingId = null;
        $this->paymentType = '';
        $this->invoiceByMinistry = null;
        $this->mandateNumber = '';
        $this->rejectionCause = '';
    }

    public function confirmAccept(): void
    {
        $this->validate([
            'paymentType' => 'required|in:regular,credit_request,revaluation',
            'invoiceByMinistry' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'mandateNumber' => 'nullable|string|max:255',
        ]);

        $payment = CpPayment::with('project')->findOrFail($this->acceptingId);
        $this->authorize('validatePayment', $payment);

        $data = [
            'status' => 'validated',
            'payment_type' => $this->paymentType,
            'mandate_number' => $this->mandateNumber ?: null,
        ];

        if ($this->invoiceByMinistry) {
            $data['invoice_by_ministry'] = $this->invoiceByMinistry->store('cp-invoices/ministry', 'public');
        }

        $payment->update($data);
        $payment = $payment->fresh(['creator', 'project']);

        app(ProjectWorkflowNotifier::class)->notifyCpDecision(
            $payment->project,
            $payment,
            'validated'
        );

        session()->flash('success', __('ui.flash.cp_validated', ['id' => $payment->id]));
        $this->cancelAction();
        $this->project->refresh();
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectionCause' => 'required|string|max:2000',
        ]);

        $payment = CpPayment::with('project')->findOrFail($this->rejectingId);
        $this->authorize('validatePayment', $payment);

        $payment->update([
            'status' => 'rejected',
            'rejection_cause' => $this->rejectionCause,
        ]);
        $payment = $payment->fresh(['creator', 'project']);

        app(ProjectWorkflowNotifier::class)->notifyCpDecision(
            $payment->project,
            $payment,
            'rejected'
        );

        session()->flash('success', __('ui.flash.cp_rejected', ['id' => $payment->id]));
        $this->cancelAction();
        $this->project->refresh();
    }

    public function render()
    {
        return view('livewire.dashboard.cp-form');
    }
}
