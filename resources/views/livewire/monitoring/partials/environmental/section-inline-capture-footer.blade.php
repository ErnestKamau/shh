@php
    $template = $workspace['template'];
    $matrix = $workspace['matrix'];
    $tid = $template->id;
    $inputs = $inlineCaptureInputsByTemplate[$tid] ?? [];
    $displayKeys = array_column($matrix['display_columns'] ?? $matrix['user_input_columns'] ?? [], 'key');
    $extraDerivedColumns = collect($matrix['derived_columns'] ?? [])->filter(
        fn (array $col) => ! in_array($col['key'] ?? '', $displayKeys, true)
    )->values()->all();
    $showDerived = $inlineCaptureShowDerivedByTemplate[$tid] ?? false;
    $freqLabel = $matrix['next_capture']['label'] ?? '—';
@endphp

<div class="env-matrix-capture-footer">
    <div class="env-matrix-capture-footer__meta">
        <span class="env-matrix-capture-footer__freq">
            <i class="mdi mdi-clock-outline" aria-hidden="true"></i>
            {{ $freqLabel }}
            <span class="env-inline-capture__step">{{ ($matrix['next_capture']['slot'] ?? 0) }}/{{ count($matrix['frequency_columns']) }}</span>
        </span>
    </div>

    <div class="env-matrix-capture-footer__actions">
        <input type="text"
               class="form-control form-control-sm env-inline-input env-matrix-capture-footer__remark"
               wire:model.live.debounce.600ms="inlineCaptureRemarksByTemplate.{{ $tid }}"
               wire:change="autoSaveInlineCapture('{{ $tid }}')"
               placeholder="Comment (optional)"
               aria-label="Comment">
    </div>

    @if($showDerived && count($extraDerivedColumns) > 0)
        <div class="env-inline-derived env-inline-derived--compact" role="region" aria-label="Calculated and system values">
            <div class="env-inline-derived__grid">
                @foreach($extraDerivedColumns as $varCol)
                    @php $display = $inputs[$varCol['key']] ?? null; @endphp
                    <div class="env-inline-derived__item">
                        <span class="env-inline-derived__label">{{ $varCol['label'] }}</span>
                        <span class="env-inline-derived__value {{ $varCol['is_primary'] ? 'is-primary' : '' }}">
                            {{ $display !== null && $display !== '' ? $display : '—' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
