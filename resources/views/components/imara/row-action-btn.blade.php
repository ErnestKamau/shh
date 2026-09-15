@props([
    'variant' => 'edit',
    'title' => null,
    'icon' => null,
    'href' => null,
])

@php
    $variantConfig = [
        'edit' => ['icon' => 'mdi-pencil', 'class' => 'btn-outline-primary', 'defaultTitle' => 'Edit'],
        'view' => ['icon' => 'mdi-eye', 'class' => 'btn-outline-success', 'defaultTitle' => 'View'],
        'delete' => ['icon' => 'mdi-delete', 'class' => 'btn-outline-danger', 'defaultTitle' => 'Delete'],
        'description' => ['icon' => 'mdi-message-text', 'class' => 'btn-outline-dark', 'defaultTitle' => 'Description'],
    ];
    $config = $variantConfig[$variant] ?? $variantConfig['edit'];
    $btnIcon = $icon ?? $config['icon'];
    $btnTitle = $title ?? $config['defaultTitle'];
    $isLink = ! empty($href);
@endphp

@if($isLink)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => 'btn btn-sm '.$config['class']]) }}
        title="{{ $btnTitle }}"
    >
        <i class="mdi {{ $btnIcon }}"></i>
    </a>
@else
    <button
        type="button"
        {{ $attributes->merge(['class' => 'btn btn-sm '.$config['class']]) }}
        title="{{ $btnTitle }}"
    >
        <i class="mdi {{ $btnIcon }}"></i>
    </button>
@endif
