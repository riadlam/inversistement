<?php

namespace App\Notifications;

use App\Models\AeEngagement;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectAeEngagedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public AeEngagement $engagement,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $amount = number_format((float) $this->engagement->amount_da, 2, ',', ' ');

        return [
            'kind' => 'project_ae_engaged',
            'project_id' => $this->project->id,
            'fip_id' => $this->project->fip_id,
            'engagement_id' => $this->engagement->id,
            'title' => __('ui.notifications.ae_engaged_title'),
            'body' => __('ui.notifications.ae_engaged_body', [
                'id' => $this->project->fip_id,
                'name' => $this->project->title_fr,
                'amount' => $amount,
            ]),
            'url' => route('dashbored.projects.show', $this->project->fip_id),
            'amount_da' => (float) $this->engagement->amount_da,
        ];
    }
}
