<div class="ds-page-enter-stagger max-w-7xl">
    <x-dashboard.page-header
        :title="__('ui.physical.title')"
        :subtitle="__('ui.physical.subtitle')"
    />

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        {{-- Aggregate stat --}}
        <div class="ds-card ds-card-glow p-6">
            <p class="ds-metric-label">{{ __('ui.physical.portfolio_avg') }}</p>
            <p class="mt-3 text-4xl font-extrabold tracking-tight text-[var(--ds-primary)]">{{ $physicalOverview['avg_progress'] }}%</p>
            <div class="ds-progress mt-4 h-2">
                <div class="ds-progress-bar" style="width: {{ $physicalOverview['avg_progress'] }}%"></div>
            </div>
            <div class="mt-5 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ __('ui.physical.on_track') }} {{ $physicalOverview['on_track'] }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    {{ __('ui.physical.at_risk') }} {{ $physicalOverview['at_risk'] }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-semibold text-red-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    {{ __('ui.physical.stalled') }} {{ $physicalOverview['stalled'] }}
                </span>
            </div>
        </div>

        {{-- Wilaya bars (scoped to the signed-in périmètre) --}}
        <div class="ds-card lg:col-span-2">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.physical.regional') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.physical.regional_hint') }}</p>
            </div>
            <div class="ds-card-body space-y-4">
                @forelse ($physicalOverview['by_region'] as $row)
                    <div>
                        <div class="mb-1 flex justify-between">
                            <span class="text-[13px] font-medium text-[var(--ds-text-secondary)]">{{ $row['label'] }}</span>
                            <span class="text-[13px] font-bold text-[var(--ds-primary)]">{{ $row['pct'] }}%</span>
                        </div>
                        <div class="ds-progress h-2">
                            <div class="ds-progress-bar" style="width: {{ $row['pct'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-[12px] text-[var(--ds-text-faint)]">{{ __('ui.physical.empty') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Project status table — real operations in scope only --}}
    <div class="ds-card">
        <div class="ds-card-header">
            <h3 class="text-[13px] font-semibold">{{ __('ui.physical.details') }}</h3>
        </div>
        <x-dashboard.ui-table>
            <thead>
                <tr>
                    <th>{{ __('ui.physical.project') }}</th>
                    <th>{{ __('ui.physical.region') }}</th>
                    <th>{{ __('ui.physical.progress') }}</th>
                    <th>{{ __('ui.physical.status') }}</th>
                    <th>{{ __('ui.physical.deadline') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($physicalOverview['projects'] ?? []) as $row)
                    <tr>
                        <td class="font-medium">
                            <a href="{{ route('dashbored.projects.show', $row['fip_id']) }}" class="text-[var(--ds-primary)] hover:underline">
                                {{ $row['title'] }}
                            </a>
                        </td>
                        <td class="text-[var(--ds-text-muted)]">{{ $row['wilaya'] }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="ds-progress h-1.5 w-20">
                                    <div class="ds-progress-bar" style="width: {{ $row['pct'] }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold">{{ $row['pct'] }}%</span>
                            </div>
                        </td>
                        <td>
                            @if ($row['status'] === 'on_track')
                                <x-dashboard.ui-badge variant="success">{{ __('ui.physical.on_track') }}</x-dashboard.ui-badge>
                            @elseif ($row['status'] === 'at_risk')
                                <x-dashboard.ui-badge variant="warning">{{ __('ui.physical.at_risk') }}</x-dashboard.ui-badge>
                            @else
                                <x-dashboard.ui-badge variant="danger">{{ __('ui.physical.stalled') }}</x-dashboard.ui-badge>
                            @endif
                        </td>
                        <td class="text-[var(--ds-text-muted)]">{{ $row['deadline'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-[12px] text-[var(--ds-text-faint)]">
                            {{ __('ui.physical.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-dashboard.ui-table>
    </div>
</div>
