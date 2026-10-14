<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A new alert fired on a project.
 *
 * Warnings and info ride the queue; criticals go out through
 * {@see CriticalAlertRaised}, which bypasses it.
 */
class AlertRaised implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Alert $alert,
        public readonly ?int $wilayaId,
    ) {}

    /** @return list<Channel> */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('projects.'.$this->alert->project_id),
            new PrivateChannel('portfolio.national'),
        ];

        if ($this->wilayaId) {
            $channels[] = new PrivateChannel('portfolio.wilaya.'.$this->wilayaId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'alert.raised';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->alert->id,
            'project_id' => $this->alert->project_id,
            'type' => $this->alert->type,
            'title' => $this->alert->title,
            'body' => $this->alert->body,
            'at' => $this->alert->created_at?->toIso8601String(),
        ];
    }

    /** Picks the queued or the immediate variant based on severity. */
    public static function for(Alert $alert, ?int $wilayaId): self
    {
        return $alert->type === 'critical'
            ? new CriticalAlertRaised($alert, $wilayaId)
            : new self($alert, $wilayaId);
    }
}
