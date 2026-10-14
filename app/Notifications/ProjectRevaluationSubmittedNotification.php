<?php

namespace App\Notifications;

use App\Models\AeRevaluation;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectRevaluationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public AeRevaluation $revaluation,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $amount = number_format((float) $this->revaluation->requested_amount_da, 2, ',', ' ');

        return [
            'kind' => 'reevaluation_submitted',
            'project_id' => $this->project->id,
            'fip_id' => $this->project->fip_id,
            'revaluation_id' => $this->revaluation->id,
            'title' => __('ui.notifications.reeval_submitted_title'),
            'body' => __('ui.notifications.reeval_submitted_body', [
                'id' => $this->project->fip_id,
                'name' => $this->project->title_fr,
                'amount' => $amount,
                'year' => $this->revaluation->year,
            ]),
            'url' => route('dashbored.projects.show', $this->project->fip_id),
        ];
    }
}
