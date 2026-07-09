@if($statusMessage !== '')
    <div
        class="alert alert-{{ $statusLevel === 'error' ? 'danger' : ($statusLevel === 'success' ? 'success' : ($statusLevel === 'warning' ? 'warning' : 'info')) }} py-2 mb-3"
        @if($statusAutoDismiss)
            wire:key="enquiry-status-{{ md5($statusMessage) }}"
            x-data
            x-init="window.clearTimeout($el._dismissTimer); $el._dismissTimer = window.setTimeout(() => $wire.clearStatus(), 3000)"
        @endif
    >
        {{ $statusMessage }}
    </div>
@endif
