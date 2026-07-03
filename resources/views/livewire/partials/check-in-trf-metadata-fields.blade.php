@php
    $instanceId = $instanceId ?? '';
@endphp
<div
    class="check-in-trf-metadata"
    wire:key="checkin-trf-{{ $instanceId }}"
>
    @include('livewire.partials.check-in-collection-info-fields', ['instanceId' => $instanceId])
</div>
