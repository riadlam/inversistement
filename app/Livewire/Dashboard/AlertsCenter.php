<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Models\Alert;
use App\Models\Project;
use App\Services\Budget\PortfolioSummary;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class AlertsCenter extends Component
{
    use ListensToPortfolioBroadcasts;

    public function title(): string
    {
        return __('ui.alerts.title');
    }

    public array $alerts = [];

    public array $notifications = [];

    public function mount(): void
    {
        $this->authorize('alerts.view');
        $this->refreshLiveData();
    }

    public function refreshLiveData(): void
    {
        $this->alerts = PortfolioSummary::for()->alerts();

        $user = auth()->user();
        $this->notifications = $user
            ? $user->notifications()
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => (string) ($n->data['title'] ?? __('ui.notifications.fallback_title')),
                    'body' => (string) ($n->data['body'] ?? ''),
                    'url' => (string) ($n->data['url'] ?? route('dashbored.projects')),
                    'kind' => (string) ($n->data['kind'] ?? 'info'),
                    'read' => $n->read_at !== null,
                    'time' => $n->created_at?->diffForHumans() ?? '',
                    'type' => match ((string) ($n->data['kind'] ?? '')) {
                        'project_rejected', 'cp_rejected' => 'warning',
                        'project_pending_visa' => 'warning',
                        'project_approved', 'cp_validated' => 'info',
                        'project_submitted' => 'info',
                        default => 'info',
                    },
                ])
                ->all()
            : [];
    }

    /**
     * Acknowledging closes the alert by hand. A recompute will re-open it if
     * the condition still holds, which is the point: an alert is a statement
     * about the data, not a message to be dismissed.
     */
    public function acknowledge(int $alertId): void
    {
        $alert = Alert::findOrFail($alertId);

        $visible = Project::visibleTo(auth()->user())->whereKey($alert->project_id)->exists();
        abort_unless($visible && auth()->user()->hasPermission('alerts.view'), 403);

        $alert->update([
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        $this->refreshLiveData();
    }

    public function markNotificationRead(string $id): void
    {
        $notification = auth()->user()?->notifications()->whereKey($id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }

        $this->refreshLiveData();
    }

    public function render()
    {
        return view('livewire.dashboard.alerts-center');
    }
}
