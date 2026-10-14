<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Services\Budget\PortfolioSummary;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class SpuOverview extends Component
{
    use ListensToPortfolioBroadcasts;

    public function title(): string
    {
        return __('ui.spu.title');
    }

    public array $spuTrafficLights = [];

    public function mount(): void
    {
        $this->refreshLiveData();
    }

    public function refreshLiveData(): void
    {
        $this->spuTrafficLights = PortfolioSummary::for()->spuTrafficLights();
    }

    public function render()
    {
        return view('livewire.dashboard.spu-overview');
    }
}
