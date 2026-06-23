@php
    $valueKey = $card['key'];
    $value = $metrics[$valueKey] ?? 0;
    $color = $card['color'] ?? '#3498db';
    $link = $card['link'] ?? null;
@endphp

<div class="quotation-kpi-card" style="--metric-color: {{ $color }};">
    @if($link)
        <a href="{{ $link }}">
    @endif
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <p class="quotation-kpi-value">{{ $value }}</p>
            <p class="quotation-kpi-label">{{ $card['label'] }}</p>
            <p class="quotation-kpi-sublabel">{{ $card['sublabel'] }}</p>
        </div>
        <div class="quotation-kpi-icon" style="color: {{ $color }};">
            <i class="mdi {{ $card['icon'] }}"></i>
        </div>
    </div>
    @if($link)
        </a>
    @endif
</div>
