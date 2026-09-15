@props([
    'subject' => '',
    'icon' => 'mdi-plus',
    'loadingTarget' => null,
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'btn btn-primary btn-sm']) }}
    @if($loadingTarget) wire:loading.attr="disabled" wire:target="{{ $loadingTarget }}" @endif
>
    @if($loadingTarget)
        <span wire:loading.remove wire:target="{{ $loadingTarget }}"><i class="mdi {{ $icon }}"></i> {{ $subject ? 'Add ' . $subject : $slot }}</span>
        <span wire:loading wire:target="{{ $loadingTarget }}"><i class="mdi mdi-loading mdi-spin"></i></span>
    @else
        <i class="mdi {{ $icon }}"></i> {{ $subject ? 'Add ' . $subject : $slot }}
    @endif
</button>
