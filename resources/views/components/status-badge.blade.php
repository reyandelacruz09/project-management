@props(['status', 'size' => 'sm', 'classes' => null])

@php
    $enum = $status instanceof \BackedEnum ? $status : null;
    $label = $enum?->label() ?? (is_string($status) ? str($status)->headline()->toString() : '');
    $classes ??= $enum?->classes() ?? 'bg-gray-100 text-gray-700';
@endphp

<span {{ $attributes->merge([
    'class' => 'shrink-0 rounded-full font-medium '
        .($size === 'sm' ? 'px-2.5 py-0.5 text-xs' : 'px-3 py-1 text-xs')
        .' '.$classes,
]) }}>{{ $label }}</span>