<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Services\Budget\PortfolioSummary;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class PhysicalProgress extends Component
{
    use ListensToPortfolioBroadcasts;

    public array $physicalOverview = [];

    public function mount(): void
    {
        $this->refreshLiveData();
    }

    public function refreshLiveData(): void
    {
        $this->physicalOverview = PortfolioSummary::for()->physicalOverview();
    }

    public function render()
    {
        return view('livewire.dashboard.physical-progress')
            ->title(__('ui.physical.title'));
    }
}
