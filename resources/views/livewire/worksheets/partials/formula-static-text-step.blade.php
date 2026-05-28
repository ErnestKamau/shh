@php
    $content = $step->staticTextContent();
@endphp
@if(trim($content) !== '')
<div class="alert alert-light border mb-3" wire:key="formula-static-{{ $step->id }}">
    <h6 class="alert-heading mb-2">
        <i class="mdi mdi-text-box-outline text-secondary"></i> {{ $step->label }}
    </h6>
    <div class="text-secondary small mb-0" style="white-space: pre-wrap;">{{ $content }}</div>
</div>
@endif
