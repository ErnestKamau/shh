<div class="crm-empty-state" {{ $attributes }}>
    @if(isset($icon))
        <i class="mdi {{ $icon }}"></i>
    @else
        <i class="mdi mdi-information-outline"></i>
    @endif
    <div class="crm-empty-message">{{ $message }}</div>
    @if(isset($help))
        <p class="crm-empty-help">{{ $help }}</p>
    @endif
</div>
