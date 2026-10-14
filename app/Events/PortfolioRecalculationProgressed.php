<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Progress of a portfolio-wide recompute triggered by a formula change.
 *
 * Editing a rule can touch every operation in the country, so the admin page
 * shows a live counter instead of a spinner of unknown length.
 */
class PortfolioRecalculationProgressed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $batchId,
        public readonly int $processed,
        public readonly int $total,
        public readonly string $reason,
        public readonly bool $finished = false,
    ) {}

    /** @return list<Channel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('calculation-engine')];
    }

    public function broadcastAs(): string
    {
        return 'portfolio.recalculation';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'batch_id' => $this->batchId,
            'processed' => $this->processed,
            'total' => $this->total,
            'percent' => $this->total > 0 ? round($this->processed / $this->total * 100, 1) : 100.0,
            'reason' => $this->reason,
            'finished' => $this->finished,
        ];
    }
}
