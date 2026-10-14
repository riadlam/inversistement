<div class="ds-page-enter-stagger max-w-7xl">
    <script type="application/json" id="chart-payload-financial">@json($chartPayload)</script>

    <x-dashboard.page-header
        :title="__('ui.financial.title')"
        :subtitle="__('ui.financial.subtitle')"
    />

    {{-- KPI row --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-dashboard.ui-metric :label="__('ui.financial.ae_notified')" :value="number_format($financial['ae_open_m'], 1) . ' M DA'" icon="chart" trend="+4.2%" :hint="__('ui.financial.vs_last_quarter')" />
        <x-dashboard.ui-metric :label="__('ui.financial.ae_engaged')" :value="$financial['ae_engaged_pct'] . '%'" icon="finance" trend="+2.1%" :hint="__('ui.financial.commitments_ratio')" />
        <x-dashboard.ui-metric :label="__('ui.financial.cp_consumed')" :value="$financial['cp_consumed_pct'] . '%'" icon="finance" :hint="__('ui.financial.cp_notified_hint', ['amount' => number_format($financial['cp_notified_m'], 1)])" />
        <x-dashboard.ui-metric label="RAR" :value="number_format($financial['rar_m'], 1) . ' M DA'" icon="alert" trend="-1.3%" :hint="__('ui.financial.rar_hint')" />
    </div>

    {{-- Charts row --}}
    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.financial.monthly_curve') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.financial.monthly_hint') }}</p>
            </div>
            <div class="ds-card-body pt-2">
                <div wire:ignore class="relative h-[280px] w-full">
                    <canvas id="chart-financial-monthly" aria-label="Monthly CP and AE chart"></canvas>
                </div>
            </div>
        </div>

        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.financial.icr') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.financial.icr_hint') }}</p>
            </div>
            <div class="ds-card-body space-y-5">
                <div>
                    <div class="mb-1.5 flex justify-between text-[11px] font-semibold">
                        <span class="text-[var(--ds-text-secondary)]">{{ __('ui.financial.alignment') }}</span>
                        <span class="text-[var(--ds-primary)]">82%</span>
                    </div>
                    <div class="ds-progress h-2.5 rounded-full">
                        <div class="ds-progress-bar rounded-full" style="width: 82%"></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1.5 flex justify-between text-[11px] font-semibold">
                        <span class="text-[var(--ds-text-secondary)]">{{ __('ui.financial.absorption') }}</span>
                        <span class="text-amber-600">61%</span>
                    </div>
                    <div class="ds-progress h-2.5 rounded-full">
                        <div class="ds-progress-bar rounded-full" style="width: 61%; background: linear-gradient(90deg,#f59e0b,#ea580c)"></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1.5 flex justify-between text-[11px] font-semibold">
                        <span class="text-[var(--ds-text-secondary)]">{{ __('ui.financial.engagement_rate') }}</span>
                        <span class="text-indigo-600">74%</span>
                    </div>
                    <div class="ds-progress h-2.5 rounded-full">
                        <div class="ds-progress-bar rounded-full" style="width: 74%; background: linear-gradient(90deg,#6366f1,#818cf8)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 ds-card">
        <div class="ds-card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-[13px] font-semibold">{{ __('ui.financial.by_wilaya') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.financial.by_wilaya_hint') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <label for="chart-region-select-financial" class="sr-only">{{ __('ui.financial.macro_region') }}</label>
                <select id="chart-region-select-financial" class="ds-input min-w-[200px] max-w-full py-1.5 text-[12px]">
                    @foreach (($chartPayload['wilayaByRegion'] ?? []) as $i => $region)
                        <option value="{{ $i }}" @selected($i === 0)>{{ $region['shortLabel'] ?? $region['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="ds-card-body pt-2">
            <div class="relative min-h-[300px] w-full sm:min-h-[340px]">
                <div wire:ignore class="relative h-full min-h-[300px] w-full sm:min-h-[340px]">
                    <canvas id="chart-financial-wilaya" aria-label="Financial indicators by wilaya"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown table --}}
    <div class="ds-card">
        <div class="ds-card-header flex items-center justify-between">
            <div>
                <h3 class="text-[13px] font-semibold">{{ __('ui.financial.programme_breakdown') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.financial.programme_hint') }}</p>
            </div>
        </div>
        <x-dashboard.ui-table>
            <thead>
                <tr>
                    <th>{{ __('ui.financial.programme') }}</th>
                    <th>{{ __('ui.financial.ae_notified') }}</th>
                    <th>{{ __('ui.financial.ae_engaged') }}</th>
                    <th>{{ __('ui.financial.cp_notified') }}</th>
                    <th>{{ __('ui.financial.cp_consumed') }}</th>
                    <th>{{ __('ui.financial.absorption') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    ['Health Infrastructure', '4,200 M', '3,570 M', '2,800 M', '1,960 M', 70],
                    ['Education Facilities', '3,100 M', '2,480 M', '2,100 M', '1,575 M', 75],
                    ['Water & Sanitation', '2,800 M', '2,100 M', '1,900 M', '1,330 M', 70],
                    ['Transport & Roads', '5,500 M', '4,400 M', '3,800 M', '2,470 M', 65],
                ] as [$prog, $aeN, $aeE, $cpN, $cpC, $abs])
                    <tr>
                        <td class="font-medium">{{ $prog }}</td>
                        <td>{{ $aeN }}</td>
                        <td>{{ $aeE }}</td>
                        <td>{{ $cpN }}</td>
                        <td>{{ $cpC }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="ds-progress h-1.5 w-16">
                                    <div class="ds-progress-bar" style="width: {{ $abs }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold">{{ $abs }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-dashboard.ui-table>
    </div>
</div>
