@php
    $field = $template->fields->firstWhere('field_key', $column['key']);
    $fieldType = $field?->field_type ?? 'text';
    $isDerived = collect($derivedColumns ?? [])->contains(
        fn (array $col) => ($col['key'] ?? '') === ($column['key'] ?? '')
    );
    $isEditingSaved = filled($savedLogId ?? null);
    $editLogId = (string) ($savedLogId ?? '');
    $inputs = $isEditingSaved
        ? ($editingSavedLogInputsById[$editLogId] ?? [])
        : ($inlineCaptureInputsByTemplate[$tid] ?? []);
    $computed = $inputs[$column['key']] ?? null;
    $columnNeedle = strtolower((string) ($column['key'] ?? '').' '.(string) ($column['label'] ?? ''));
    $isRemarkColumn = str_contains($columnNeedle, 'remark');
    $statusPreview = $isEditingSaved
        ? ($editingSavedLogStatusPreviewById[$editLogId] ?? null)
        : ($inlineCaptureStatusPreviewByTemplate[$tid] ?? null);
    $initialFilled = collect($inputs)->contains(function ($value, $key): bool {
        $keyNeedle = strtolower((string) $key);
        if (! str_contains($keyNeedle, 'initial')) {
            return false;
        }

        return $value !== null && trim((string) $value) !== '';
    });
    $showMoreInRemark = $isRemarkColumn && ($initialFilled || $isEditingSaved);
    $timelineLogId = $isEditingSaved ? $editLogId : null;
    $inputModel = $isEditingSaved
        ? "editingSavedLogInputsById.{$editLogId}.{$column['key']}"
        : "inlineCaptureInputsByTemplate.{$tid}.{$column['key']}";
    $errorKey = $isEditingSaved
        ? "editingSavedLogInputsById.{$editLogId}.{$column['key']}"
        : "inlineCaptureInputsByTemplate.{$tid}.{$column['key']}";
    $fieldChangeAction = $isEditingSaved
        ? "autoUpdateSavedLogCapture('{$editLogId}')"
        : (str_contains(strtolower((string) ($column['key'] ?? '')), 'initial')
            ? "autoSaveInlineCapture('{$tid}')"
            : "recomputeInlineFormulaForTemplate('{$tid}')");
@endphp

<div class="env-matrix-capture-field {{ $isDerived ? 'env-matrix-capture-field--derived' : '' }} {{ $isRemarkColumn ? 'env-matrix-capture-field--remark' : '' }}">
    @if($isDerived || $isRemarkColumn)
        <span class="env-matrix-field-value {{ ($column['is_primary'] ?? false) ? 'is-primary' : '' }}">
            @php
                $display = $computed;
                if (($display === null || $display === '' || in_array(trim((string) $display), ['-', '—'], true)) && $isRemarkColumn) {
                    $display = $statusPreview;
                }
            @endphp
            {{ $display !== null && $display !== '' ? $display : '—' }}
        </span>
        @if($showMoreInRemark)
            <button type="button"
                    class="env-more-btn"
                    wire:click="{{ filled($timelineLogId) ? "openSavedLogFormulaTimeline('{$timelineLogId}')" : "openInlineFormulaTimeline('{$tid}')" }}"
                    title="View steps breakdown"
                    aria-label="View steps breakdown">
                <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
            </button>
        @endif
    @else
    @if($fieldType === 'number')
        <input id="capture-{{ $tid }}-{{ $column['key'] }}{{ $isEditingSaved ? '-'.$editLogId : '' }}"
               type="number"
               step="any"
               class="form-control form-control-sm env-inline-input"
               placeholder="{{ $column['label'] }}"
               aria-label="{{ $column['label'] }}"
               wire:model.live.debounce.400ms="{{ $inputModel }}"
               wire:change="{{ $fieldChangeAction }}">
    @else
        <input id="capture-{{ $tid }}-{{ $column['key'] }}{{ $isEditingSaved ? '-'.$editLogId : '' }}"
               type="text"
               class="form-control form-control-sm env-inline-input"
               placeholder="{{ $column['label'] }}"
               aria-label="{{ $column['label'] }}"
               wire:model.live.debounce.400ms="{{ $inputModel }}"
               wire:change="{{ $fieldChangeAction }}">
    @endif
        @error($errorKey)
            <div class="env-inline-field__error">{{ $message }}</div>
        @enderror
    @endif
</div>
