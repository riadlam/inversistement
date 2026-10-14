<?php

namespace App\Services\Workflow;

use App\Enums\Role;
use App\Models\AeEngagement;
use App\Models\AeRevaluation;
use App\Models\Alert;
use App\Models\CpPayment;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectAeEngagedNotification;
use App\Notifications\ProjectArbitrationDecisionNotification;
use App\Notifications\ProjectCpDecisionNotification;
use App\Notifications\ProjectPendingVisaNotification;
use App\Notifications\ProjectRevaluationDecisionNotification;
use App\Notifications\ProjectRevaluationSubmittedNotification;
use App\Notifications\ProjectSubmittedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class ProjectWorkflowNotifier
{
    public const RETURNED_SIGNATURE = 'workflow.arbitration.returned';

    public const SUBMITTED_SIGNATURE = 'workflow.arbitration.submitted';

    public const PENDING_VISA_SIGNATURE = 'workflow.arbitration.pending_visa';

    public const AE_ENGAGED_SIGNATURE = 'workflow.ae.engaged';

    public const REEVAL_PENDING_SIGNATURE = 'workflow.reevaluation.pending';

    public function notifySubmitted(Project $project): void
    {
        $this->resolveReturnedAlert($project);
        $this->resolvePendingVisaAlert($project);
        $this->raiseSubmittedAlert($project);

        $recipients = $this->arbitrationRecipients();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new ProjectSubmittedNotification($project));
    }

    /** Programme manager accepted — hand off to budget controller. */
    public function notifyPendingVisa(Project $project): void
    {
        $this->resolveSubmittedAlert($project);
        $this->resolveReturnedAlert($project);
        $this->raisePendingVisaAlert($project);

        $recipients = $this->visaRecipients();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new ProjectPendingVisaNotification($project));
    }

    public function notifyDecision(Project $project, string $decision, ?string $reason = null): void
    {
        if ($decision === 'rejected') {
            $this->resolveSubmittedAlert($project);
            $this->resolvePendingVisaAlert($project);
            $this->raiseReturnedAlert($project, $reason);
        } else {
            $this->resolveSubmittedAlert($project);
            $this->resolvePendingVisaAlert($project);
            $this->resolveReturnedAlert($project);
        }

        $creator = $project->creator ?? User::find($project->created_by);

        if (! $creator) {
            return;
        }

        $creator->notify(new ProjectArbitrationDecisionNotification($project, $decision, $reason));
    }

    /** Ordonnateur recorded a legal AE engagement — inform DASS (creator). */
    public function notifyAeEngaged(Project $project, AeEngagement $engagement): void
    {
        $this->raiseAeEngagedAlert($project, $engagement);

        $creator = $project->creator ?? User::find($project->created_by);

        if (! $creator) {
            return;
        }

        $creator->notify(new ProjectAeEngagedNotification($project, $engagement));
    }

    /** DASS submitted a réévaluation — notify programme managers. */
    public function notifyRevaluationSubmitted(Project $project, AeRevaluation $revaluation): void
    {
        $this->raiseRevaluationPendingAlert($project, $revaluation);

        $recipients = $this->arbitrationRecipients();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ProjectRevaluationSubmittedNotification($project, $revaluation)
        );
    }

    /** Programme manager decided on a réévaluation — notify DASS submitter. */
    public function notifyRevaluationDecision(Project $project, AeRevaluation $revaluation, string $decision): void
    {
        $this->resolveRevaluationPendingAlert($project, $revaluation);

        $submitter = $revaluation->submitter ?? User::find($revaluation->submitted_by);
        $creator = $project->creator ?? User::find($project->created_by);
        $recipients = collect([$submitter, $creator])->filter()->unique('id');

        foreach ($recipients as $user) {
            $user->notify(new ProjectRevaluationDecisionNotification($project, $revaluation, $decision));
        }
    }

    /** Ordonnateur / contrôleur decided on a CP request — notify DASS submitter. */
    public function notifyCpDecision(Project $project, CpPayment $payment, string $decision): void
    {
        $submitter = $payment->creator ?? User::find($payment->created_by);
        $creator = $project->creator ?? User::find($project->created_by);
        $recipients = collect([$submitter, $creator])->filter()->unique('id');

        foreach ($recipients as $user) {
            // Do not notify the actor who just validated/rejected.
            if (auth()->id() && (int) $user->id === (int) auth()->id()) {
                continue;
            }

            $user->notify(new ProjectCpDecisionNotification($project, $payment, $decision));
        }
    }

    public function raiseReturnedAlert(Project $project, ?string $reason = null): void
    {
        Alert::updateOrCreate(
            [
                'project_id' => $project->id,
                'signature' => self::RETURNED_SIGNATURE,
            ],
            [
                'type' => 'warning',
                'title' => __('ui.notifications.rejected_title'),
                'body' => __('ui.notifications.rejected_body', [
                    'id' => $project->fip_id,
                    'name' => $project->title_fr,
                    'reason' => $reason ?: '—',
                ]),
                'source' => 'user',
                'last_seen_at' => now(),
                'resolved_at' => null,
                'resolved_by' => null,
            ],
        );
    }

    public function raiseSubmittedAlert(Project $project): void
    {
        Alert::updateOrCreate(
            [
                'project_id' => $project->id,
                'signature' => self::SUBMITTED_SIGNATURE,
            ],
            [
                'type' => 'info',
                'title' => __('ui.notifications.submitted_title'),
                'body' => __('ui.notifications.submitted_body', [
                    'id' => $project->fip_id,
                    'name' => $project->title_fr,
                ]),
                'source' => 'user',
                'last_seen_at' => now(),
                'resolved_at' => null,
                'resolved_by' => null,
            ],
        );
    }

    public function raisePendingVisaAlert(Project $project): void
    {
        Alert::updateOrCreate(
            [
                'project_id' => $project->id,
                'signature' => self::PENDING_VISA_SIGNATURE,
            ],
            [
                'type' => 'warning',
                'title' => __('ui.notifications.pending_visa_title'),
                'body' => __('ui.notifications.pending_visa_body', [
                    'id' => $project->fip_id,
                    'name' => $project->title_fr,
                ]),
                'source' => 'user',
                'last_seen_at' => now(),
                'resolved_at' => null,
                'resolved_by' => null,
            ],
        );
    }

    public function raiseAeEngagedAlert(Project $project, AeEngagement $engagement): void
    {
        $amount = number_format((float) $engagement->amount_da, 2, ',', ' ');

        Alert::updateOrCreate(
            [
                'project_id' => $project->id,
                'signature' => self::AE_ENGAGED_SIGNATURE.'.'.$engagement->id,
            ],
            [
                'type' => 'info',
                'title' => __('ui.notifications.ae_engaged_title'),
                'body' => __('ui.notifications.ae_engaged_body', [
                    'id' => $project->fip_id,
                    'name' => $project->title_fr,
                    'amount' => $amount,
                ]),
                'source' => 'user',
                'last_seen_at' => now(),
                'resolved_at' => null,
                'resolved_by' => null,
            ],
        );
    }

    public function raiseRevaluationPendingAlert(Project $project, AeRevaluation $revaluation): void
    {
        $amount = number_format((float) $revaluation->requested_amount_da, 2, ',', ' ');

        Alert::updateOrCreate(
            [
                'project_id' => $project->id,
                'signature' => self::REEVAL_PENDING_SIGNATURE.'.'.$revaluation->id,
            ],
            [
                'type' => 'warning',
                'title' => __('ui.notifications.reeval_submitted_title'),
                'body' => __('ui.notifications.reeval_submitted_body', [
                    'id' => $project->fip_id,
                    'name' => $project->title_fr,
                    'amount' => $amount,
                    'year' => $revaluation->year,
                ]),
                'source' => 'user',
                'last_seen_at' => now(),
                'resolved_at' => null,
                'resolved_by' => null,
            ],
        );
    }

    public function resolveReturnedAlert(Project $project): void
    {
        $this->resolveAlert($project, self::RETURNED_SIGNATURE);
    }

    public function resolveSubmittedAlert(Project $project): void
    {
        $this->resolveAlert($project, self::SUBMITTED_SIGNATURE);
    }

    public function resolvePendingVisaAlert(Project $project): void
    {
        $this->resolveAlert($project, self::PENDING_VISA_SIGNATURE);
    }

    public function resolveRevaluationPendingAlert(Project $project, AeRevaluation $revaluation): void
    {
        $this->resolveAlert($project, self::REEVAL_PENDING_SIGNATURE.'.'.$revaluation->id);
    }

    private function resolveAlert(Project $project, string $signature): void
    {
        Alert::query()
            ->where('project_id', $project->id)
            ->where('signature', $signature)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
            ]);
    }

    /** @return Collection<int, User> */
    private function arbitrationRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereIn('role', [Role::Admin->value, Role::ProgrammeManager->value])
            ->get();
    }

    /** @return Collection<int, User> */
    private function visaRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereIn('role', [Role::Admin->value, Role::BudgetController->value])
            ->get();
    }
}
