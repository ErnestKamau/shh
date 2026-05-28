@php
    $template = $workspace['template'];
    $matrix = $workspace['matrix'];
    $tid = $template->id;
    $inputs = $inlineCaptureInputsByTemplate[$tid] ?? [];
    $userColumns = $matrix['user_input_columns'] ?? [];
    $derivedColumns = $matrix['derived_columns'] ?? [];
    $showDerived = $inlineCaptureShowDerivedByTemplate[$tid] ?? false;
@endphp

<div class="env-inline-capture">
    <div class="env-inline-capture__head">
        <div class="env-inline-capture__banner">
            <i class="mdi mdi-clock-outline" aria-hidden="true"></i>
            <span><strong>{{ $matrix['next_capture']['label'] ?? '—' }}</strong></span>
            <span class="env-inline-capture__step">{{ ($matrix['next_capture']['slot'] ?? 0) }}/{{ count($matrix['frequency_columns']) }}</span>
        </div>
        @if(count($derivedColumns) > 0)
            <button type="button"
                    class="env-more-btn {{ $showDerived ? 'is-open' : '' }}"
                    wire:click="toggleInlineCaptureDerived('{{ $tid }}')"
                    title="{{ $showDerived ? 'Hide calculated values' : 'Show calculated & system values' }}"
                    aria-expanded="{{ $showDerived ? 'true' : 'false' }}"
                    aria-label="Toggle calculated and system values">
                <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
            </button>
        @endif
    </div>

    <div class="env-inline-capture__fields">
        @foreach($userColumns as $varCol)
            @php
                $field = $template->fields->firstWhere('field_key', $varCol['key']);
                $fieldType = $field?->field_type ?? 'text';
            @endphp
            <div class="env-inline-field">
                <label class="env-inline-field__label" for="capture-{{ $tid }}-{{ $varCol['key'] }}">{{ $varCol['label'] }}</label>
                @if($fieldType === 'number')
                    <input id="capture-{{ $tid }}-{{ $varCol['key'] }}"
                           type="number"
                           step="any"
                           class="form-control form-control-sm env-inline-input"
                           wire:model.live.debounce.400ms="inlineCaptureInputsByTemplate.{{ $tid }}.{{ $varCol['key'] }}"
                           wire:change="recomputeInlineFormulaForTemplate('{{ $tid }}')">
                @else
                    <input id="capture-{{ $tid }}-{{ $varCol['key'] }}"
                           type="text"
                           class="form-control form-control-sm env-inline-input"
                           wire:model.live.debounce.400ms="inlineCaptureInputsByTemplate.{{ $tid }}.{{ $varCol['key'] }}"
                           wire:change="recomputeInlineFormulaForTemplate('{{ $tid }}')">
                @endif
                @error('inlineCaptureInputsByTemplate.'.$tid.'.'.$varCol['key'])
                    <div class="env-inline-field__error">{{ $message }}</div>
                @enderror
            </div>
        @endforeach

        <div class="env-inline-field env-inline-field--remark">
            <label class="env-inline-field__label" for="capture-{{ $tid }}-remark">Remark</label>
            <input id="capture-{{ $tid }}-remark"
                   type="text"
                   class="form-control form-control-sm env-inline-input"
                   wire:model="inlineCaptureRemarksByTemplate.{{ $tid }}"
                   placeholder="Optional">
        </div>
    </div>

    @if($showDerived && count($derivedColumns) > 0)
        <div class="env-inline-derived" role="region" aria-label="Calculated and system values">
            <p class="env-inline-derived__title">Calculated &amp; system values</p>
            <div class="env-inline-derived__grid">
                @foreach($derivedColumns as $varCol)
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

    <div class="env-inline-capture__actions">
        <button type="button"
                class="btn btn-primary btn-sm env-inline-save"
                wire:click="saveInlineCapture('{{ $tid }}')"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="saveInlineCapture('{{ $tid }}')">
                <i class="mdi mdi-check" aria-hidden="true"></i> Save reading
            </span>
            <span wire:loading wire:target="saveInlineCapture('{{ $tid }}')">Saving…</span>
        </button>
    </div>
</div>
