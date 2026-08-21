@php
    $valueKey = $card['key'];
    $value = $metrics[$valueKey] ?? 0;
    $color = $card['color'] ?? '#3498db';
    $link = $card['link'] ?? null;
    $livewireStage = array_key_exists('livewireStage', $card) ? $card['livewireStage'] : null;
@endphp

<div class="quotation-kpi-card" style="--metric-color: {{ $color }};">
    @if($livewireStage !== null)
        <button type="button"
                class="quotation-kpi-hit"
                onclick="Livewire.dispatch('set-quotation-stage', { stage: @js($livewireStage) }); document.getElementById('quotation-stage-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });">
    @elseif($link)
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
    @if($livewireStage !== null)
        </button>
    @elseif($link)
        </a>
    @endif
</div>
