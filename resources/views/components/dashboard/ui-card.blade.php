@props([
    'title' => null,
    'subtitle' => null,
    'noPadding' => false,
])

<div {{ $attributes->merge(['class' => 'ds-card overflow-hidden']) }}>
    @if ($title || $subtitle)
        <div class="ds-card-header flex flex-wrap items-start justify-between gap-2">
            <div>
                @if ($title)
                    <h2 class="text-base font-semibold text-[var(--ds-text)]">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 text-sm text-[var(--ds-text-muted)]">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif
    <div @class(['ds-card-body', '!p-0' => $noPadding])>
        {{ $slot }}
    </div>
</div>
