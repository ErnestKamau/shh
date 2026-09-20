@props([
    'title' => '',
    'size' => 'default',
])

<div class="modal-content imara-form-modal">
    <div class="modal-header border-bottom">
        <h4 class="modal-title imara-section-title">{{ $title }}</h4>
        {{ $header ?? '' }}
    </div>
    <div class="modal-body">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="modal-footer border-top">
            {{ $footer }}
        </div>
    @endif
</div>
