<?php

namespace App\Notifications;

use App\Models\CpPayment;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectCpDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public CpPayment $payment,
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
        $validated = $this->decision === 'validated';
        $amount = number_format((float) $this->payment->amount_da, 2, ',', ' ');

        return [
            'kind' => $validated ? 'cp_validated' : 'cp_rejected',
            'project_id' => $this->project->id,
            'fip_id' => $this->project->fip_id,
            'payment_id' => $this->payment->id,
            'decision' => $this->decision,
            'title' => $validated
                ? __('ui.notifications.cp_validated_title')
                : __('ui.notifications.cp_rejected_title'),
            'body' => $validated
                ? __('ui.notifications.cp_validated_body', [
                    'id' => $this->project->fip_id,
                    'name' => $this->project->title_fr,
                    'amount' => $amount,
                ])
                : __('ui.notifications.cp_rejected_body', [
                    'id' => $this->project->fip_id,
                    'name' => $this->project->title_fr,
                    'amount' => $amount,
                    'reason' => $this->payment->rejection_cause ?: '—',
                ]),
            'url' => route('dashbored.projects.show', $this->project->fip_id),
            'amount_da' => (float) $this->payment->amount_da,
        ];
    }
}
