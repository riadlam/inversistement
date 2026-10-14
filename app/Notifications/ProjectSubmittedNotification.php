<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public Project $project) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'project_submitted',
            'project_id' => $this->project->id,
            'fip_id' => $this->project->fip_id,
            'title' => __('ui.notifications.submitted_title'),
            'body' => __('ui.notifications.submitted_body', [
                'id' => $this->project->fip_id,
                'name' => $this->project->title_fr,
            ]),
            'url' => route('dashbored.projects.show', $this->project->fip_id),
        ];
    }
}
