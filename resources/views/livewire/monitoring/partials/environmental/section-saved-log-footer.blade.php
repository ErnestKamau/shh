@php
    $cell = $cell ?? [];
    $template = $workspace['template'];
    $matrix = $workspace['matrix'];
    $logId = (string) ($cell['log_id'] ?? '');
    $isEditing = ($editingSavedLogId ?? null) === $logId;
    $freqSlot = $cell['frequency_slot'] ?? null;
    $freqLabel = collect($matrix['frequency_columns'] ?? [])
        ->firstWhere('slot', $freqSlot)['label'] ?? ($freqSlot !== null ? 'Reading '.$freqSlot : 'Unslotted');
@endphp

<div class="env-matrix-capture-footer env-matrix-capture-footer--saved">
    <div class="env-matrix-capture-footer__meta">
        <span class="env-matrix-capture-footer__freq">
            <i class="mdi mdi-clock-outline" aria-hidden="true"></i>
            {{ $freqLabel }}
        </span>
    </div>

    <div class="env-matrix-capture-footer__actions env-matrix-capture-footer__actions--saved">
        <input type="text"
               class="form-control form-control-sm env-inline-input env-matrix-capture-footer__remark"
               wire:model.live.debounce.600ms="savedLogRemarksById.{{ $logId }}"
               wire:change="autoSaveSavedLogRemark('{{ $logId }}')"
               placeholder="Comment (optional)"
               aria-label="Comment">
        <button type="button"
                class="btn btn-sm btn-link env-matrix-capture-footer__edit {{ $isEditing ? 'is-active' : '' }}"
                wire:click="toggleEditSavedLog('{{ $logId }}')"
                title="{{ $isEditing ? 'Done editing' : 'Edit reading values' }}"
                aria-label="{{ $isEditing ? 'Done editing' : 'Edit reading values' }}">
            <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
        </button>
    </div>

    @if(filled($cell['user'] ?? null))
        <p class="env-matrix-capture-footer__captured-by mb-0">
            <i class="mdi mdi-account-outline" aria-hidden="true"></i>
            Captured by <strong>{{ $cell['user'] }}</strong>
        </p>
    @endif
</div>
