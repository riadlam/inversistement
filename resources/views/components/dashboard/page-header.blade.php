@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-[var(--ds-text)] sm:text-2xl">
                {{ $title }}
            </h1>
            @if ($subtitle)
                <p class="mt-1 max-w-2xl text-[13px] leading-relaxed text-[var(--ds-text-muted)]">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
</div>
