<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Services\Budget\PortfolioSummary;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class FinancialOverview extends Component
{
    use ListensToPortfolioBroadcasts;

    public function title(): string
    {
        return __('ui.financial.title');
    }

    public array $financial = [];

    /** @var array<string, mixed> */
    public array $chartPayload = [];

    public function mount(): void
    {
        $this->refreshLiveData();
    }

    public function refreshLiveData(): void
    {
        $summary = PortfolioSummary::for();

        $this->financial = $summary->financial();
        $this->chartPayload = [
            'monthly' => $summary->monthlyConsumption(),
            'wilayaByRegion' => $summary->wilayaChartSeries(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.financial-overview');
    }
}
