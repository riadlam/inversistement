<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Services\Budget\PortfolioSummary;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Overview extends Component
{
    use ListensToPortfolioBroadcasts;

    public function title(): string
    {
        return __('ui.overview.title');
    }

    public array $stats = [];

    public array $financial = [];

    public array $alerts = [];

    public array $spuTrafficLights = [];

    /** @var array<string, mixed> */
    public array $chartPayload = [];

    public function mount(): void
    {
        $this->refreshLiveData();
    }

    public function refreshLiveData(): void
    {
        $summary = PortfolioSummary::for();

        $this->stats = $summary->overviewStats();
        $this->financial = $summary->financial();
        $this->alerts = array_slice($summary->alerts(3), 0, 3);
        $this->spuTrafficLights = $summary->spuTrafficLights();

        $this->chartPayload = [
            'spuPie' => [
                'labels' => ['Sain (V)', 'Vigilance (O)', 'Critique (R)'],
                'data' => [
                    $this->spuTrafficLights['green'],
                    $this->spuTrafficLights['orange'],
                    $this->spuTrafficLights['red'],
                ],
            ],
            'envelopePie' => [
                'labels' => ['AE notifiées', 'CP notifiés', 'RAR'],
                'data' => [
                    $this->financial['ae_open_m'],
                    $this->financial['cp_notified_m'],
                    $this->financial['rar_m'],
                ],
            ],
            'monthlyLine' => $summary->monthlyConsumption(),
            'wilayaByRegion' => $summary->wilayaChartSeries(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.overview');
    }
}
