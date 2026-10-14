@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $variantClass = match ($variant) {
        'secondary' => 'ds-btn-secondary',
        'ghost' => 'ds-btn-ghost',
        default => 'ds-btn-primary',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "ds-btn {$variantClass}"]) }}>
    {{ $slot }}
</button>
