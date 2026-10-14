<div class="ds-page-enter max-w-5xl">
    <x-dashboard.page-header
        :title="__('ui.alerts.title')"
        :subtitle="__('ui.alerts.subtitle')"
    >
        <x-slot:actions>
            <span class="rounded-full bg-red-50 px-3 py-1 text-[11px] font-bold text-red-600">
                {{ __('ui.alerts.critical_n', ['n' => collect($alerts)->where('type', 'critical')->count()]) }}
            </span>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-600">
                {{ __('ui.alerts.warnings_n', ['n' => collect($alerts)->where('type', 'warning')->count()]) }}
            </span>
        </x-slot:actions>
    </x-dashboard.page-header>

    @if (count($notifications) > 0)
        <div class="mb-6">
            <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.notifications.workflow') }}</h2>
            <div class="space-y-3">
                @foreach ($notifications as $i => $n)
                    <a href="{{ $n['url'] }}"
                       wire:click="markNotificationRead('{{ $n['id'] }}')"
                       class="ds-alert-row {{ $n['read'] ? 'opacity-70' : '' }} {{ $n['type'] === 'warning' ? 'ds-alert-critical' : '' }}"
                       style="animation: ds-fade-up 0.4s var(--ds-ease) {{ $i * 0.03 }}s both">
                        <span @class([
                            'ds-pill-dot',
                            'bg-red-500' => $n['kind'] === 'project_rejected',
                            'bg-emerald-500' => $n['kind'] === 'project_approved',
                            'bg-blue-500' => ! in_array($n['kind'], ['project_rejected', 'project_approved'], true),
                        ])></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-[13px] font-semibold text-[var(--ds-text)]">{{ $n['title'] }}</p>
                                @unless ($n['read'])
                                    <span class="rounded bg-[var(--ds-primary)]/10 px-1.5 py-0.5 text-[9px] font-bold uppercase text-[var(--ds-primary)]">{{ __('ui.notifications.unread') }}</span>
                                @endunless
                            </div>
                            <p class="mt-0.5 text-[12px] text-[var(--ds-text-muted)]">{{ $n['body'] }}</p>
                            <p class="mt-1.5 text-[10px] font-medium text-[var(--ds-text-faint)]">{{ $n['time'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.alerts.open') }}</h2>
        <div class="space-y-3">
            @forelse ($alerts as $i => $alert)
                <div class="ds-alert-row {{ $alert['type'] === 'critical' ? 'ds-alert-critical' : '' }}"
                     style="animation: ds-fade-up 0.4s var(--ds-ease) {{ $i * 0.05 }}s both">
                    <span @class([
                        'ds-pill-dot',
                        'bg-red-500' => $alert['type'] === 'critical',
                        'bg-amber-500' => $alert['type'] === 'warning',
                        'bg-blue-500' => ($alert['type'] ?? '') === 'info',
                    ])></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if (! empty($alert['fip_id']))
                                <a href="{{ route('dashbored.projects.show', $alert['fip_id']) }}" class="text-[13px] font-semibold text-[var(--ds-text)] hover:text-[var(--ds-primary)]">
                                    {{ $alert['title'] }}
                                </a>
                            @else
                                <p class="text-[13px] font-semibold text-[var(--ds-text)]">{{ $alert['title'] }}</p>
                            @endif
                            @php
                                $typeColor = match($alert['type']) {
                                    'critical' => 'bg-red-50 text-red-600',
                                    'warning' => 'bg-amber-50 text-amber-600',
                                    default => 'bg-blue-50 text-blue-600',
                                };
                            @endphp
                            <span class="rounded px-1.5 py-0.5 text-[9px] font-bold uppercase {{ $typeColor }}">{{ $alert['type'] }}</span>
                        </div>
                        <p class="mt-0.5 text-[12px] text-[var(--ds-text-muted)]">{{ $alert['body'] }}</p>
                        <p class="mt-1.5 text-[10px] font-medium text-[var(--ds-text-faint)]">{{ $alert['time'] ?? '' }}</p>
                    </div>
                    <button type="button" wire:click="acknowledge({{ $alert['id'] }})"
                            class="btn btn-sm btn-outline-secondary shrink-0 opacity-70 hover:opacity-100">
                        {{ __('ui.alerts.acknowledge') }}
                    </button>
                </div>
            @empty
                @if (count($notifications) === 0)
                    <p class="rounded-xl border border-dashed border-[var(--ds-border)] px-4 py-10 text-center text-[13px] text-[var(--ds-text-faint)]">
                        {{ __('ui.alerts.empty') }}
                    </p>
                @else
                    <p class="px-1 py-4 text-[12px] text-[var(--ds-text-faint)]">{{ __('ui.alerts.empty') }}</p>
                @endif
            @endforelse
        </div>
    </div>
</div>
