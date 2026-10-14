@props([
    'variant' => 'neutral',
])

@php
    $class = match ($variant) {
        'success' => 'ds-badge-success',
        'warning' => 'ds-badge-warning',
        'danger' => 'ds-badge-danger',
        default => 'ds-badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "ds-badge {$class}"]) }}>
    {{ $slot }}
</span>
