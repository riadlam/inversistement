<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Services\Budget\PortfolioSummary;
use Livewire\Component;

/**
 * The live alert bell in the dashboard top bar.
 *
 * Shows Laravel workflow notifications (submit / arbitration) plus portfolio
 * metric alerts when the role can view them.
 */
class NotificationBell extends Component
{
    use ListensToPortfolioBroadcasts;

    public array $notifications = [];

    public array $alerts = [];

    public int $criticalCount = 0;

    public int $unreadCount = 0;

    public bool $open = false;

    public function mount(): void
    {
        $this->refreshLiveData();
    }

    public function refreshLiveData(): void
    {
        $user = auth()->user();

        $this->notifications = [];
        $this->unreadCount = 0;
        $this->alerts = [];
        $this->criticalCount = 0;

        if (! $user) {
            return;
        }

        $this->notifications = $user->notifications()
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => (string) ($n->data['title'] ?? __('ui.notifications.fallback_title')),
                'body' => (string) ($n->data['body'] ?? ''),
                'url' => (string) ($n->data['url'] ?? route('dashbored.projects')),
                'kind' => (string) ($n->data['kind'] ?? 'info'),
                'read' => $n->read_at !== null,
                'time' => $n->created_at?->diffForHumans() ?? '',
            ])
            ->all();

        $this->unreadCount = $user->unreadNotifications()->count();

        if ($user->hasPermission('alerts.view')) {
            $this->alerts = PortfolioSummary::for()->alerts(8);
            $this->criticalCount = collect($this->alerts)->where('type', 'critical')->count();
        }
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        if ($this->open) {
            $this->refreshLiveData();
        }
    }

    public function markAsRead(string $id): void
    {
        $notification = auth()->user()?->notifications()->whereKey($id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }

        $this->refreshLiveData();
    }

    public function markAllRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
        $this->refreshLiveData();
    }

    public function render()
    {
        return view('livewire.dashboard.notification-bell');
    }
}
