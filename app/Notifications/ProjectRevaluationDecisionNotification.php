<?php

namespace App\Notifications;

use App\Models\AeRevaluation;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectRevaluationDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public AeRevaluation $revaluation,
        public string $decision,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $approved = $this->decision === 'approved';
        $amount = number_format((float) $this->revaluation->requested_amount_da, 2, ',', ' ');

        return [
            'kind' => $approved ? 'reevaluation_approved' : 'reevaluation_rejected',
            'project_id' => $this->project->id,
            'fip_id' => $this->project->fip_id,
            'revaluation_id' => $this->revaluation->id,
            'decision' => $this->decision,
            'title' => $approved
                ? __('ui.notifications.reeval_approved_title')
                : __('ui.notifications.reeval_rejected_title'),
            'body' => $approved
                ? __('ui.notifications.reeval_approved_body', [
                    'id' => $this->project->fip_id,
                    'name' => $this->project->title_fr,
                    'amount' => $amount,
                ])
                : __('ui.notifications.reeval_rejected_body', [
                    'id' => $this->project->fip_id,
                    'name' => $this->project->title_fr,
                    'amount' => $amount,
                ]),
            'url' => route('dashbored.projects.show', $this->project->fip_id),
        ];
    }
}
