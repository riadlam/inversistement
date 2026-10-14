<div class="ds-page-enter-stagger max-w-7xl">
    <x-dashboard.page-header
        :title="__('ui.overview.title')"
        :subtitle="__('ui.overview.subtitle')"
    />

    {{-- Hero stat cards --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="ds-card ds-card-glow ds-hero-stat p-5">
            <div class="flex items-start justify-between">
                <p class="ds-metric-label">{{ __('ui.overview.active_ops') }}</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" /></svg>
                </span>
            </div>
            <p class="ds-stat-value ds-stat-value-brand mt-2">{{ $stats['active_projects'] }}</p>
            <p class="mt-1 text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.overview.across_programmes') }}</p>
        </div>

        <div class="ds-card ds-hero-stat p-5">
            <div class="flex items-start justify-between">
                <p class="ds-metric-label">{{ __('ui.overview.plf_year') }}</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                </span>
            </div>
            <p class="ds-stat-value mt-2">{{ $stats['plf_year'] }}</p>
            <p class="mt-1 text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.overview.plf_hint') }}</p>
        </div>

        <div class="ds-card ds-hero-stat p-5" style="border-color: rgba(220,38,38,0.15);">
            <div class="flex items-start justify-between">
                <p class="ds-metric-label">{{ __('ui.overview.critical_alerts') }}</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </span>
            </div>
            <p class="ds-stat-value mt-2 text-red-600">{{ $stats['critical_alerts'] }}</p>
            <a href="{{ route('dashbored.alerts') }}" class="mt-2 inline-flex text-[11px] font-semibold text-red-600 hover:underline">{{ __('ui.overview.review_alerts') }}</a>
        </div>

        <div class="ds-card ds-hero-stat p-5">
            <div class="flex items-start justify-between">
                <p class="ds-metric-label">{{ __('ui.overview.portfolio_spu') }}</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                </span>
            </div>
            <p class="ds-stat-value mt-2">{{ $stats['avg_spu'] }}<span class="text-sm font-semibold text-[var(--ds-text-faint)]">/100</span></p>
            <a href="{{ route('dashbored.spu') }}" class="mt-2 inline-flex text-[11px] font-semibold text-[var(--ds-primary)] hover:underline">{{ __('ui.overview.spu_breakdown') }}</a>
        </div>
    </div>

    {{-- Middle row --}}
    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        {{-- Financial pulse --}}
        <div class="ds-card lg:col-span-2">
            <div class="ds-card-header flex items-center justify-between">
                <div>
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.overview.financial_pulse') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.overview.ae_cp_snapshot') }}</p>
                </div>
                <a href="{{ route('dashbored.financial') }}" class="text-[11px] font-semibold text-[var(--ds-primary)] hover:underline">{{ __('ui.overview.full_view') }}</a>
            </div>
            <div class="ds-card-body">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <div class="mb-1.5 flex items-baseline justify-between">
                            <p class="ds-metric-label">{{ __('ui.overview.ae_engaged') }}</p>
                            <span class="text-lg font-bold text-[var(--ds-text)]">{{ $financial['ae_engaged_pct'] }}%</span>
                        </div>
                        <div class="ds-progress h-2">
                            <div class="ds-progress-bar" style="width: {{ $financial['ae_engaged_pct'] }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="mb-1.5 flex items-baseline justify-between">
                            <p class="ds-metric-label">{{ __('ui.overview.cp_consumed') }}</p>
                            <span class="text-lg font-bold text-[var(--ds-text)]">{{ $financial['cp_consumed_pct'] }}%</span>
                        </div>
                        <div class="ds-progress h-2">
                            <div class="ds-progress-bar" style="width: {{ $financial['cp_consumed_pct'] }}%; background: linear-gradient(90deg,#f59e0b,#fb923c)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SPU distribution --}}
        <div class="ds-card">
            <div class="ds-card-header">
                <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.overview.spu_distribution') }}</h2>
            </div>
            <div class="ds-card-body space-y-2">
                <div class="flex items-center justify-between rounded-lg bg-emerald-50/80 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-[13px] font-semibold text-emerald-800">{{ __('ui.overview.healthy') }}</span>
                    </div>
                    <span class="text-base font-bold text-emerald-600">{{ $spuTrafficLights['green'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-amber-50/80 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span class="text-[13px] font-semibold text-amber-800">{{ __('ui.overview.watch') }}</span>
                    </div>
                    <span class="text-base font-bold text-amber-600">{{ $spuTrafficLights['orange'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-red-50/80 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span class="text-[13px] font-semibold text-red-800">{{ __('ui.overview.critical') }}</span>
                    </div>
                    <span class="text-base font-bold text-red-600">{{ $spuTrafficLights['red'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts (Chart.js) --}}
    <script type="application/json" id="chart-payload-overview">@json($chartPayload)</script>
    <div class="mb-6 space-y-4">
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="ds-card">
                <div class="ds-card-header">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.overview.traffic') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.overview.traffic_hint') }}</p>
                </div>
                <div class="ds-card-body pt-0">
                    <div wire:ignore class="relative mx-auto h-[240px] max-w-sm">
                        <canvas id="chart-overview-spu" aria-label="SPU distribution chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="ds-card">
                <div class="ds-card-header">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.overview.envelope') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.overview.envelope_hint') }}</p>
                </div>
                <div class="ds-card-body pt-0">
                    <div wire:ignore class="relative mx-auto h-[240px] max-w-sm">
                        <canvas id="chart-overview-envelope" aria-label="Financial envelope chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="ds-card">
            <div class="ds-card-header">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.overview.monthly') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.overview.monthly_hint') }}</p>
            </div>
            <div class="ds-card-body pt-2">
                <div wire:ignore class="relative h-[260px] w-full">
                    <canvas id="chart-overview-monthly" aria-label="Monthly consumption trend"></canvas>
                </div>
            </div>
        </div>
        <div class="ds-card">
            <div class="ds-card-header flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.overview.by_wilaya') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.overview.by_wilaya_hint') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="chart-region-select-overview" class="sr-only">{{ __('ui.overview.macro_region') }}</label>
                    <select id="chart-region-select-overview" class="ds-input min-w-[200px] max-w-full py-1.5 text-[12px]">
                        @foreach (($chartPayload['wilayaByRegion'] ?? []) as $i => $region)
                            <option value="{{ $i }}" @selected($i === 0)>{{ $region['shortLabel'] ?? $region['label'] }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('dashbored.projects') }}" class="text-[11px] font-semibold text-[var(--ds-primary)] hover:underline">{{ __('ui.overview.project_register') }}</a>
                </div>
            </div>
            <div class="ds-card-body pt-2">
                <div class="relative min-h-[280px] w-full sm:min-h-[320px]">
                    <div wire:ignore class="relative h-full min-h-[280px] w-full sm:min-h-[320px]">
                        <canvas id="chart-overview-wilaya" aria-label="AE and CP by wilaya"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent alerts --}}
    <div class="ds-card">
        <div class="ds-card-header flex items-center justify-between">
            <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.overview.recent_signals') }}</h2>
            <a href="{{ route('dashbored.alerts') }}" class="text-[11px] font-semibold text-[var(--ds-primary)] hover:underline">{{ __('ui.overview.view_all') }}</a>
        </div>
        <div class="divide-y divide-[var(--ds-border-subtle)]">
            @foreach ($alerts as $alert)
                <div class="flex items-start gap-3 px-5 py-3.5 transition hover:bg-[var(--ds-surface-sunken)] {{ $alert['type'] === 'critical' ? 'border-l-3 border-l-[var(--ds-danger)] bg-red-50/40' : '' }}">
                    <span @class([
                        'ds-pill-dot mt-1',
                        'bg-red-500' => $alert['type'] === 'critical',
                        'bg-amber-500' => $alert['type'] === 'warning',
                        'bg-blue-500' => ($alert['type'] ?? '') === 'info',
                    ])></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-semibold text-[var(--ds-text)]">{{ $alert['title'] }}</p>
                        <p class="mt-0.5 text-[12px] text-[var(--ds-text-muted)]">{{ $alert['body'] }}</p>
                    </div>
                    <span class="shrink-0 text-[10px] font-medium text-[var(--ds-text-faint)]">{{ $alert['time'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
