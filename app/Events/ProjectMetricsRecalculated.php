<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A project's numbers changed.
 *
 * Broadcast immediately rather than through the queue: the dashboards are the
 * live view of the portfolio, and a dinar landing seconds late is the thing
 * the websocket layer exists to avoid.
 */
class ProjectMetricsRecalculated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, float|null>  $metrics
     */
    public function __construct(
        public readonly int $projectId,
        public readonly ?int $wilayaId,
        public readonly array $metrics,
        public readonly string $trafficLight,
    ) {}

    /** @return list<Channel> */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('projects.'.$this->projectId),
            new PrivateChannel('portfolio.national'),
        ];

        if ($this->wilayaId) {
            $channels[] = new PrivateChannel('portfolio.wilaya.'.$this->wilayaId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'metrics.recalculated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'project_id' => $this->projectId,
            'traffic_light' => $this->trafficLight,
            'metrics' => $this->metrics,
            'at' => now()->toIso8601String(),
        ];
    }
}
