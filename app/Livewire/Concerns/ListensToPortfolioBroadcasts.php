<?php

namespace App\Livewire\Concerns;

/**
 * Subscribes a dashboard page to the portfolio channel its user is allowed to
 * hear, so a recompute anywhere refreshes the page without a reload.
 *
 * The component only needs to implement `refreshLiveData()`.
 */
trait ListensToPortfolioBroadcasts
{
    /** Rising counter the view can key off to animate a refresh. */
    public int $liveTick = 0;

    /** @return array<string, string> */
    public function getListeners(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $channel = $user->isNational()
            ? 'portfolio.national'
            : 'portfolio.wilaya.'.$user->wilaya_id;

        return [
            "echo-private:{$channel},.metrics.recalculated" => 'onPortfolioBroadcast',
            "echo-private:{$channel},.alert.raised" => 'onPortfolioBroadcast',
        ];
    }

    public function onPortfolioBroadcast(): void
    {
        $this->liveTick++;
        $this->refreshLiveData();
    }

    abstract public function refreshLiveData(): void;
}
