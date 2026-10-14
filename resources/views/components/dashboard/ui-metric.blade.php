@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'trend' => null,
])

<div {{ $attributes->merge(['class' => 'ds-card ds-hero-stat p-5']) }}>
    <div class="flex items-start justify-between">
        <p class="ds-metric-label">{{ $label }}</p>
        @if ($icon)
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--ds-primary-soft)] text-[var(--ds-primary)]">
                <x-dashboard.nav-icon :name="$icon" />
            </span>
        @endif
    </div>
    <p class="ds-metric-value mt-2">{{ $value }}</p>
    @if ($hint || $trend)
        <div class="mt-2 flex items-center gap-2">
            @if ($trend)
                <span @class([
                    'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold',
                    'bg-emerald-50 text-emerald-600' => str_starts_with($trend, '+'),
                    'bg-red-50 text-red-600' => str_starts_with($trend, '-'),
                ])>{{ $trend }}</span>
            @endif
            @if ($hint)
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ $hint }}</p>
            @endif
        </div>
    @endif
</div>
