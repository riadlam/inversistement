<?php

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Models\AeRevaluation;
use App\Models\AuditLog;
use App\Models\Movement;
use App\Models\Project;
use App\Models\ProjectClosure;
use App\Models\User;
use App\Services\Budget\ProjectBudget;
use App\Services\Workflow\ProjectWorkflowNotifier;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Fiches 04, 05 and 06: réévaluation, mouvement (annulation / transfert) and
 * clôture. Each save is authorised by the matching policy and recomputes the
 * affected operations through the model observers.
 */
#[Layout('layouts.dashboard')]
class QuickActionsPage extends Component
{
    use ListensToPortfolioBroadcasts;

    public function title(): string
    {
        return __('ui.actions.title');
    }

    public string $panel = 'revaluation';

    public ?int $revaluationProjectId = null;

    public ?float $revaluationAmount = null;

    public string $revaluationJustification = '';

    public ?int $revaluationYear = null;

    public string $movementType = 'transfer';

    public ?int $movementSourceId = null;

    public ?int $movementTargetId = null;

    public ?float $movementAmount = null;

    public string $movementJustification = '';

    public ?int $closureProjectId = null;

    public string $closureDecisionRef = '';

    public string $closureNotes = '';

    public function mount(): void
    {
        $this->revaluationYear = (int) now()->year;
        $this->refreshLiveData();
    }

    public function refreshLiveData(): void
    {
        // Computed properties re-read from the database on the next render.
    }

    public function open(string $panel): void
    {
        $this->panel = $panel;
        $this->resetErrorBag();
    }

    public function submitRevaluation(): void
    {
        abort_unless(auth()->user()->can('create', AeRevaluation::class), 403);

        $validated = $this->validate([
            'revaluationProjectId' => 'required|integer|exists:projects,id',
            'revaluationAmount' => 'required|numeric|min:1',
            'revaluationYear' => 'required|integer|min:1990|max:2100',
            'revaluationJustification' => 'required|string|min:10|max:2000',
        ]);

        $project = Project::visibleTo()->findOrFail($validated['revaluationProjectId']);
        $this->authorize('updateExecution', $project);

        $next = min(5, (int) $project->aeRevaluations()->max('sequence') + 1);

        $revaluation = $project->aeRevaluations()->create([
            'sequence' => $next,
            'year' => $validated['revaluationYear'],
            'requested_amount_da' => $validated['revaluationAmount'],
            'justification' => $validated['revaluationJustification'],
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationSubmitted($project, $revaluation);

        $this->reset(['revaluationAmount', 'revaluationJustification']);
        session()->flash('actions-saved', __('ui.flash.reeval_request'));
    }

    public function approveRevaluation(int $id): void
    {
        $revaluation = AeRevaluation::with('project')->findOrFail($id);
        $this->authorize('approve', $revaluation);

        $revaluation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $revaluation->project->update([
            'reevaluation_count' => $revaluation->project->aeRevaluations()->where('status', 'approved')->count(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationDecision(
            $revaluation->project,
            $revaluation->fresh(['submitter']),
            'approved'
        );

        session()->flash('actions-saved', __('ui.flash.reeval_approved'));
    }

    public function rejectRevaluation(int $id): void
    {
        $revaluation = AeRevaluation::with('project')->findOrFail($id);
        $this->authorize('approve', $revaluation);

        $revaluation->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(ProjectWorkflowNotifier::class)->notifyRevaluationDecision(
            $revaluation->project,
            $revaluation->fresh(['submitter']),
            'rejected'
        );

        session()->flash('actions-saved', __('ui.flash.reeval_rejected'));
    }

    public function submitMovement(): void
    {
        abort_unless(auth()->user()->can('create', Movement::class), 403);

        $validated = $this->validate([
            'movementType' => 'required|in:cancellation,transfer',
            'movementSourceId' => 'required|integer|exists:projects,id',
            'movementTargetId' => 'nullable|required_if:movementType,transfer|integer|different:movementSourceId|exists:projects,id',
            'movementAmount' => 'required|numeric|min:1',
            'movementJustification' => 'required|string|min:10|max:2000',
        ]);

        $source = Project::visibleTo()->findOrFail($validated['movementSourceId']);
        $this->authorize('updateExecution', $source);

        if ($validated['movementType'] === 'cancellation' && $source->aeEngagements()->exists()) {
            $this->addError('movementSourceId', "L'annulation est refusée : l'opération a déjà des engagements.");

            return;
        }

        Movement::create([
            'source_project_id' => $source->id,
            'target_project_id' => $validated['movementType'] === 'transfer' ? $validated['movementTargetId'] : null,
            'amount_da' => $validated['movementAmount'],
            'type' => $validated['movementType'],
            'justification' => $validated['movementJustification'],
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
        ]);

        $this->reset(['movementAmount', 'movementJustification', 'movementTargetId']);
        session()->flash('actions-saved', __('ui.flash.movement_submitted'));
    }

    public function approveMovement(int $id): void
    {
        $movement = Movement::with('sourceProject')->findOrFail($id);
        $this->authorize('approve', $movement);

        if ($movement->type === 'cancellation' && $movement->sourceProject?->aeEngagements()->exists()) {
            session()->flash('actions-error', __('ui.flash.movement_blocked'));

            return;
        }

        $movement->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        session()->flash('actions-saved', __('ui.flash.movement_approved'));
    }

    public function rejectMovement(int $id): void
    {
        $movement = Movement::findOrFail($id);
        $this->authorize('approve', $movement);

        $movement->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        session()->flash('actions-saved', __('ui.flash.movement_rejected'));
    }

    public function submitClosure(): void
    {
        abort_unless(auth()->user()->can('create', ProjectClosure::class), 403);

        $validated = $this->validate([
            'closureProjectId' => 'required|integer|exists:projects,id',
            'closureDecisionRef' => 'nullable|string|max:100',
            'closureNotes' => 'nullable|string|max:2000',
        ]);

        $project = Project::visibleTo()->findOrFail($validated['closureProjectId']);
        $this->authorize('close', $project);

        $budget = ProjectBudget::for($project);

        $project->closure()->updateOrCreate([], [
            'final_ae_engaged_da' => $budget->cumulEngagements(),
            'final_cp_consumed_da' => $budget->cumulPaiements(),
            'final_rar_da' => $budget->rar(),
            'closure_decision_ref' => $validated['closureDecisionRef'] ?: null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $validated['closureNotes'] ?: null,
        ]);

        $project->update([
            'status' => 'closed',
            'is_closed' => true,
        ]);

        $this->reset(['closureProjectId', 'closureDecisionRef', 'closureNotes']);
        session()->flash('actions-saved', __('ui.flash.closed'));
    }

    public function projectsForSelect(): array
    {
        return Project::visibleTo()
            ->orderBy('title_fr')
            ->get(['id', 'fip_id', 'title_fr', 'status'])
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'label' => $p->fip_id.' — '.$p->title_fr,
                'status' => $p->status,
            ])
            ->all();
    }

    public function pendingRevaluations()
    {
        return AeRevaluation::query()
            ->with([
                'project:id,fip_id,title_fr,wilaya_id',
                'project.wilaya:id,name_fr',
                'submitter.wilaya',
            ])
            ->whereIn('status', ['submitted', 'draft', 'pending'])
            ->whereHas('project', fn ($q) => $q->visibleTo())
            ->latest()
            ->limit(20)
            ->get();
    }

    public function pendingMovements()
    {
        return Movement::query()
            ->with([
                'sourceProject:id,fip_id,title_fr,wilaya_id',
                'sourceProject.wilaya:id,name_fr',
                'targetProject:id,fip_id,title_fr',
                'submitter.wilaya',
            ])
            ->whereIn('status', ['submitted', 'draft'])
            ->whereHas('sourceProject', fn ($q) => $q->visibleTo())
            ->latest()
            ->limit(20)
            ->get();
    }

    public function recentActivity(): array
    {
        return AuditLog::query()
            ->with(['user:id,name', 'project:id,fip_id,title_fr,wilaya_id'])
            ->where(function ($q) {
                $q->whereNull('project_id')
                    ->orWhereHas('project', fn ($p) => $p->visibleTo());
            })
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(fn (AuditLog $log) => [
                'action' => $log->action,
                'project' => $log->project?->fip_id ?? '—',
                'user' => $log->user?->name ?? 'Système',
                'at' => $log->created_at?->diffForHumans() ?? '',
            ])
            ->all();
    }

    public function canRequestRevaluation(): bool
    {
        return auth()->user()?->hasPermission(Permission::ReevaluationRequest->value) ?? false;
    }

    public function canApproveRevaluation(): bool
    {
        return auth()->user()?->hasPermission(Permission::ReevaluationApprove->value) ?? false;
    }

    public function canRequestMovement(): bool
    {
        return auth()->user()?->hasPermission(Permission::MovementRequest->value) ?? false;
    }

    public function canApproveMovement(): bool
    {
        return auth()->user()?->hasPermission(Permission::MovementApprove->value) ?? false;
    }

    public function canClose(): bool
    {
        return auth()->user()?->hasPermission(Permission::ProjectsClose->value) ?? false;
    }

    public function render()
    {
        return view('livewire.dashboard.quick-actions-page', [
            'projects' => $this->projectsForSelect(),
            'pendingRevaluations' => $this->pendingRevaluations(),
            'pendingMovements' => $this->pendingMovements(),
            'recentActivity' => $this->recentActivity(),
        ]);
    }
}
