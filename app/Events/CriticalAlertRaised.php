<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * A critical alert. Identical payload and event name to {@see AlertRaised},
 * but ShouldBroadcastNow pushes it to Reverb synchronously so it never waits
 * on a queue worker.
 */
class CriticalAlertRaised extends AlertRaised implements ShouldBroadcastNow
{
}
