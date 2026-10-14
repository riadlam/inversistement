<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectArbitrationDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public string $decision,
        public ?string $reason = null,
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

        return [
            'kind' => $approved ? 'project_approved' : 'project_rejected',
            'project_id' => $this->project->id,
            'fip_id' => $this->project->fip_id,
            'decision' => $this->decision,
            'title' => $approved
                ? __('ui.notifications.approved_title')
                : __('ui.notifications.rejected_title'),
            'body' => $approved
                ? __('ui.notifications.approved_body', [
                    'id' => $this->project->fip_id,
                    'name' => $this->project->title_fr,
                ])
                : __('ui.notifications.rejected_body', [
                    'id' => $this->project->fip_id,
                    'name' => $this->project->title_fr,
                    'reason' => $this->reason ?: '—',
                ]),
            'url' => $approved
                ? route('dashbored.projects.show', $this->project->fip_id)
                : route('dashbored.projects.edit', $this->project->fip_id),
            'reason' => $this->reason,
        ];
    }
}
