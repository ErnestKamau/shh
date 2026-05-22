@php
    $field = $step['capture_field'] ?? ['type' => 'text', 'placeholder' => 'Enter value…'];
@endphp
<div class="gw-inner-timeline-item">
    <div class="gw-inner-timeline-marker">{{ $step['order'] }}</div>
    <div class="gw-inner-timeline-card">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
            <div>
                <div class="fw-semibold">{{ $step['title'] }}</div>
                @if(!empty($step['subtitle']))
                    <div class="small text-muted">{{ $step['subtitle'] }}</div>
                @endif
            </div>
            @if(!empty($step['badges']))
                <div class="d-flex flex-wrap gap-1">
                    @foreach($step['badges'] as $badge)
                        <span class="gw-capture-badge">{{ $badge }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        @if(($field['type'] ?? '') === 'capture')
            <div class="gw-capture-field gw-capture-field--panel">
                <i class="mdi mdi-clipboard-edit-outline"></i>
                {{ $field['placeholder'] ?? 'Capture panel' }}
            </div>
        @elseif(($field['type'] ?? '') === 'select')
            <div class="gw-capture-field gw-capture-field--select">
                <span class="text-muted">{{ $field['placeholder'] ?? 'Select…' }}</span>
                <i class="mdi mdi-chevron-down"></i>
            </div>
        @else
            <input
                type="{{ $field['type'] ?? 'text' }}"
                class="gw-capture-field gw-capture-field--input"
                placeholder="{{ $field['placeholder'] ?? '' }}"
                disabled
                readonly
            >
        @endif
    </div>
</div>
