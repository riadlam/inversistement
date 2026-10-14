<div class="ds-page-enter-stagger max-w-7xl">
    <x-dashboard.page-header
        :title="__('ui.spu.title')"
        :subtitle="__('ui.spu.subtitle')"
    />

    {{-- Traffic light cards --}}
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        @foreach ([
            ['green', __('ui.spu.healthy'), $spuTrafficLights['green'], __('ui.spu.range_high'), 'emerald', __('ui.spu.healthy_desc')],
            ['orange', __('ui.spu.watch'), $spuTrafficLights['orange'], __('ui.spu.range_mid'), 'amber', __('ui.spu.watch_desc')],
            ['red', __('ui.spu.critical'), $spuTrafficLights['red'], __('ui.spu.range_low'), 'red', __('ui.spu.critical_desc')],
        ] as [$color, $label, $count, $range, $tw, $desc])
            <div class="ds-card group p-6">
                <div class="ds-ribbon bg-gradient-to-r from-{{ $tw }}-500 to-{{ $tw }}-400 -mx-6 -mt-6 mb-5"></div>
                <div class="flex items-center gap-4">
                    <div class="ds-traffic ds-traffic-{{ $color }}">{{ strtoupper(substr($color, 0, 1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-semibold text-[var(--ds-text)]">{{ $label }}</p>
                        <p class="text-[11px] text-[var(--ds-text-faint)]">{{ $range }}</p>
                    </div>
                    <p class="text-3xl font-extrabold text-{{ $tw }}-600">{{ $count }}</p>
                </div>
                <p class="mt-3 text-[12px] text-[var(--ds-text-muted)]">{{ $desc }}</p>
            </div>
        @endforeach
    </div>

    {{-- Weighting model --}}
    <div class="mb-6 ds-card">
        <div class="ds-card-header">
            <h3 class="text-[13px] font-semibold">{{ __('ui.spu.weighting') }}</h3>
            <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.spu.weighting_hint') }}</p>
        </div>
        <div class="ds-card-body">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    [__('ui.spu.dim_cp'), 30, 'emerald', __('ui.spu.dim_cp_desc')],
                    [__('ui.spu.dim_physical'), 40, 'blue', __('ui.spu.dim_physical_desc')],
                    [__('ui.spu.dim_schedule'), 20, 'violet', __('ui.spu.dim_schedule_desc')],
                    [__('ui.spu.dim_dossier'), 10, 'amber', __('ui.spu.dim_dossier_desc')],
                ] as [$dimension, $pts, $color, $desc])
                    <div class="rounded-xl border border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] p-4 transition hover:border-[var(--ds-primary)]/30">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="ds-metric-label">{{ $dimension }}</p>
                            <span class="rounded-md bg-{{ $color }}-50 px-2 py-0.5 text-[10px] font-bold text-{{ $color }}-600">{{ __('ui.spu.pts', ['n' => $pts]) }}</span>
                        </div>
                        <div class="ds-progress h-2 mb-2">
                            <div class="ds-progress-bar" style="width: {{ $pts }}%; background: linear-gradient(90deg, var(--tw-gradient-from, #047857), var(--tw-gradient-to, #10b981))"></div>
                        </div>
                        <p class="text-[11px] text-[var(--ds-text-faint)]">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top performers and worst --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold text-emerald-700">{{ __('ui.spu.top') }}</h3>
            </div>
            <div class="divide-y divide-[var(--ds-border-subtle)]">
                @foreach ([
                    ['Water network — Constantine', 94],
                    ['Hospital expansion — Blida', 88],
                    ['School modernization — Alger', 85],
                ] as [$name, $spu])
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-[13px] font-medium">{{ $name }}</span>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[12px] font-bold text-emerald-600">{{ $spu }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold text-red-700">{{ __('ui.spu.needs') }}</h3>
            </div>
            <div class="divide-y divide-[var(--ds-border-subtle)]">
                @foreach ([
                    ['Road segment — Djelfa', 32],
                    ['Sports hall — Bechar', 41],
                    ['Admin building — Tamanrasset', 45],
                ] as [$name, $spu])
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-[13px] font-medium">{{ $name }}</span>
                        <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-[12px] font-bold text-red-600">{{ $spu }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
