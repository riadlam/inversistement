@php
    $badgeCount = $unreadCount + count($alerts);
@endphp
<div class="relative">
    <button type="button" wire:click="toggle"
            class="relative flex h-8 w-8 items-center justify-center rounded-full border border-[var(--ds-border)] bg-white text-[var(--ds-text-muted)] transition hover:text-[var(--ds-text)]"
            title="{{ __('ui.alerts.bell') }}">
        <x-dashboard.nav-icon name="bell" class="h-4 w-4" />
        @if ($badgeCount > 0)
            <span @class([
                'absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[9px] font-bold text-white',
                'bg-red-500' => $criticalCount > 0 || $unreadCount > 0,
                'bg-amber-500' => $criticalCount === 0 && $unreadCount === 0,
            ])>{{ $badgeCount > 99 ? '99+' : $badgeCount }}</span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 z-50 mt-2 w-96 overflow-hidden rounded-lg border border-[var(--ds-border)] bg-white shadow-lg">
            <div class="flex items-center justify-between border-b border-[var(--ds-border)] px-3 py-2">
                <p class="text-[11px] font-bold uppercase tracking-wide text-[var(--ds-text-muted)]">{{ __('ui.notifications.heading') }}</p>
                <div class="flex items-center gap-2">
                    @if ($unreadCount > 0)
                        <button type="button" wire:click="markAllRead" class="text-[10px] font-semibold text-[var(--ds-primary)] hover:underline">
                            {{ __('ui.notifications.mark_all_read') }}
                        </button>
                    @endif
                    @if ($criticalCount > 0)
                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-600">{{ __('ui.alerts.criticals_n', ['n' => $criticalCount]) }}</span>
                    @endif
                </div>
            </div>

            <div class="max-h-96 divide-y divide-[var(--ds-border)] overflow-y-auto">
                @if (count($notifications) > 0)
                    <div class="bg-[var(--ds-surface-sunken)] px-3 py-1.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.notifications.workflow') }}</p>
                    </div>
                    @foreach ($notifications as $n)
                        <a href="{{ $n['url'] }}"
                           wire:click="markAsRead('{{ $n['id'] }}')"
                           class="block px-3 py-2.5 transition hover:bg-[var(--ds-surface-alt)] {{ $n['read'] ? 'opacity-70' : '' }}">
                            <div class="flex items-start gap-2">
                                <span @class([
                                    'mt-1 h-1.5 w-1.5 shrink-0 rounded-full',
                                    'bg-emerald-500' => $n['kind'] === 'project_approved',
                                    'bg-red-500' => $n['kind'] === 'project_rejected',
                                    'bg-amber-500' => $n['kind'] === 'project_pending_visa',
                                    'bg-blue-500' => ! in_array($n['kind'], ['project_approved', 'project_rejected', 'project_pending_visa'], true),
                                ])></span>
                                <div class="min-w-0">
                                    <p class="text-[12px] font-semibold text-[var(--ds-text)]">
                                        {{ $n['title'] }}
                                        @unless ($n['read'])
                                            <span class="ml-1 inline-block h-1.5 w-1.5 rounded-full bg-[var(--ds-primary)]"></span>
                                        @endunless
                                    </p>
                                    <p class="mt-0.5 line-clamp-2 text-[11px] text-[var(--ds-text-muted)]">{{ $n['body'] }}</p>
                                    <p class="mt-1 text-[10px] text-[var(--ds-text-faint)]">{{ $n['time'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endif

                @if (count($alerts) > 0)
                    <div class="bg-[var(--ds-surface-sunken)] px-3 py-1.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.alerts.open') }}</p>
                    </div>
                    @foreach ($alerts as $alert)
                        <a href="{{ $alert['fip_id'] ? route('dashbored.projects.show', $alert['fip_id']) : route('dashbored.alerts') }}"
                           class="block px-3 py-2.5 transition hover:bg-[var(--ds-surface-alt)]">
                            <div class="flex items-start gap-2">
                                <span @class([
                                    'mt-1 h-1.5 w-1.5 shrink-0 rounded-full',
                                    'bg-red-500' => $alert['type'] === 'critical',
                                    'bg-amber-500' => $alert['type'] === 'warning',
                                    'bg-blue-500' => $alert['type'] === 'info',
                                ])></span>
                                <div class="min-w-0">
                                    <p class="text-[12px] font-semibold text-[var(--ds-text)]">{{ $alert['title'] }}</p>
                                    <p class="mt-0.5 line-clamp-2 text-[11px] text-[var(--ds-text-muted)]">{{ $alert['body'] }}</p>
                                    <p class="mt-1 text-[10px] text-[var(--ds-text-faint)]">{{ $alert['time'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endif

                @if (count($notifications) === 0 && count($alerts) === 0)
                    <p class="px-3 py-6 text-center text-[12px] text-[var(--ds-text-faint)]">{{ __('ui.notifications.empty') }}</p>
                @endif
            </div>

            <a href="{{ route('dashbored.alerts') }}"
               class="block border-t border-[var(--ds-border)] px-3 py-2 text-center text-[11px] font-semibold text-[var(--ds-text-muted)] hover:text-[var(--ds-text)]">
                {{ __('ui.alerts.view_all') }}
            </a>
        </div>
    @endif
</div>
