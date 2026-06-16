@php
    $template = $workspace['template'];
    $matrix = $workspace['matrix'];
    $nextCapture = $matrix['next_capture'] ?? null;
    $displayColumns = $matrix['display_columns'] ?? $matrix['user_input_columns'] ?? [];
    $derivedColumns = $matrix['derived_columns'] ?? [];
    $remarkColumnIndex = collect($displayColumns)->search(function (array $column): bool {
        $needle = strtolower((string) ($column['key'] ?? '').' '.(string) ($column['label'] ?? ''));

        return str_contains($needle, 'remark');
    });
    $remarkColumnIndex = $remarkColumnIndex === false ? null : (int) $remarkColumnIndex;
    $subColCount = max(1, count($displayColumns));
    $totalColspan = 1 + (count($matrix['frequency_columns']) * $subColCount) + ($matrix['has_unslotted'] ? $subColCount : 0);
    $tid = $template->id;

    $remarkSubcellClass = function (?string $verdict): string {
        if ($verdict === 'pass') {
            return 'env-matrix-subcell--pass';
        }

        if ($verdict === 'fail') {
            return 'env-matrix-subcell--fail';
        }

        return '';
    };
@endphp

<div class="env-template-block mb-4">
    <header class="env-template-block__header">
        <div>
            <h3 class="env-template-block__title">{{ $template->name }}</h3>
            <p class="env-template-block__doc">
                Doc {{ $template->document_control_number ?: '—' }} · v{{ $template->version }}
            </p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('monitoring.export-lws-011') }}" method="GET" target="_blank" class="d-inline-flex align-items-center gap-2">
                <input type="hidden" name="section_id" value="{{ $section?->id ?? $this->selectedSectionId }}">
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                <div class="input-group input-group-sm" style="width: auto;">
                    <span class="input-group-text bg-light text-muted border-secondary-subtle">
                        <i class="mdi mdi-calendar"></i>
                    </span>
                    <input type="month" name="month" value="{{ date('Y-m') }}" required class="form-control form-control-sm border-secondary-subtle" style="max-width: 140px;">
                    <button type="submit" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="mdi mdi-file-pdf-box"></i> Export LWS-011 PDF
                    </button>
                </div>
            </form>

            @if($nextCapture)
                <div class="env-next-capture-pill">
                    <i class="mdi mdi-arrow-right-circle" aria-hidden="true"></i>
                    <span>Next: <strong>{{ $nextCapture['label'] }}</strong></span>
                    <span class="env-next-capture-pill__count">{{ $nextCapture['slot'] }}/{{ count($matrix['frequency_columns']) }}</span>
                </div>
            @else
                <div class="env-next-capture-pill env-next-capture-pill--complete">
                    <i class="mdi mdi-check-circle" aria-hidden="true"></i>
                    All readings captured today
                </div>
            @endif
    </header>

    @php
        $isEditingLog = filled($editingSavedLogId ?? null);
        $activeCaptureSlot = $nextCapture;
        $showCaptureCard = $activeCaptureSlot || $isEditingLog;
    @endphp

    @if($showCaptureCard)
        <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #802424 !important; background: #fafafa; border-radius: 8px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h5 class="card-title mb-0 text-dark fw-bold d-flex align-items-center" style="font-size: 1.1rem;">
                        <span class="badge me-2" style="background-color: #802424; color: #fff; padding: 6px 12px; font-size: 0.75rem;">
                            {{ $isEditingLog ? 'EDIT MODE' : 'LOG READINGS' }}
                        </span>
                        {{ $isEditingLog ? 'Modify Saved Entry' : 'Capture New Entry' }}
                        <span class="ms-2 text-muted fw-normal" style="font-size: 0.85rem;">
                            &mdash; {{ $isEditingLog ? 'Selected Log' : ($activeCaptureSlot['label'] ?? 'Daily check') }}
                        </span>
                    </h5>
                    @if($isEditingLog)
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" wire:click="toggleEditSavedLog('{{ $editingSavedLogId }}')">
                            <i class="mdi mdi-close me-1"></i>Close Edit
                        </button>
                    @endif
                </div>

                <!-- Form Grid -->
                <div class="row g-3">
                    @foreach($displayColumns as $col)
                        @php
                            $isColDerived = collect($derivedColumns ?? [])->contains(
                                fn (array $dCol) => ($dCol['key'] ?? '') === ($col['key'] ?? '')
                            );
                            $colNeedle = strtolower((string) ($col['key'] ?? '').' '.(string) ($col['label'] ?? ''));
                            $isColRemark = str_contains($colNeedle, 'remark');
                        @endphp

                        @if(! $isColDerived && ! $isColRemark)
                            @php
                                $field = $template->fields->firstWhere('field_key', $col['key']);
                                $fieldType = $field?->field_type ?? 'text';
                                
                                $inputModel = $isEditingLog
                                    ? "editingSavedLogInputsById.{$editingSavedLogId}.{$col['key']}"
                                    : "inlineCaptureInputsByTemplate.{$tid}.{$col['key']}";
                                
                                $errorKey = $isEditingLog
                                    ? "editingSavedLogInputsById.{$editingSavedLogId}.{$col['key']}"
                                    : "inlineCaptureInputsByTemplate.{$tid}.{$col['key']}";

                                $fieldChangeAction = $isEditingLog
                                    ? "autoUpdateSavedLogCapture('{$editingSavedLogId}')"
                                    : (str_contains(strtolower((string) ($col['key'] ?? '')), 'initial')
                                        ? "autoSaveInlineCapture('{$tid}')"
                                        : "recomputeInlineFormulaForTemplate('{$tid}')");

                                $equipmentId = $isEditingLog
                                    ? ($editingSavedLogInputsById[$editingSavedLogId]['equipment_id'] ?? $this->selectedSection?->equipment_id)
                                    : ($inlineCaptureInputsByTemplate[$tid]['equipment_id'] ?? $this->selectedSection?->equipment_id);
                                
                                $resolvedInputConfig = $field ? $this->resolveFieldInputConfig($field, $equipmentId) : null;
                                $inputConfig = $field?->field_config['input_config'] ?? [];
                                $inputType = $inputConfig['type'] ?? 'text';
                                $options = $inputConfig['options'] ?? [];
                            @endphp

                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small fw-bold text-secondary mb-1" for="card-input-{{ $col['key'] }}">
                                    {{ $col['label'] }}
                                    @if($field?->is_required) <span class="text-danger">*</span> @endif
                                </label>

                                @if($inputType === 'select' || count($options) > 0)
                                    <select id="card-input-{{ $col['key'] }}"
                                            class="form-select form-select-sm"
                                            wire:model.live.debounce.400ms="{{ $inputModel }}"
                                            wire:change="{{ $fieldChangeAction }}">
                                        <option value="">-- Select --</option>
                                        @foreach($options as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @elseif($fieldType === 'number')
                                    <input id="card-input-{{ $col['key'] }}"
                                           type="number"
                                           step="any"
                                           class="form-control form-control-sm"
                                           placeholder="Enter value"
                                           wire:model.live.debounce.400ms="{{ $inputModel }}"
                                           wire:change="{{ $fieldChangeAction }}">
                                @else
                                    <input id="card-input-{{ $col['key'] }}"
                                           type="text"
                                           class="form-control form-control-sm"
                                           placeholder="Enter value"
                                           wire:model.live.debounce.400ms="{{ $inputModel }}"
                                           wire:change="{{ $fieldChangeAction }}">
                                @endif

                                @if($resolvedInputConfig)
                                    @php $vType = $resolvedInputConfig['value_type'] ?? 'text'; @endphp
                                    @if($vType === 'constant')
                                        <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                                            Expected: {{ $resolvedInputConfig['expected_value'] ?? '—' }}
                                        </div>
                                    @elseif($vType === 'range')
                                        <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                                            Range: {{ $resolvedInputConfig['min_value'] ?? '—' }} to {{ $resolvedInputConfig['max_value'] ?? '—' }}
                                        </div>
                                    @endif
                                @endif

                                @error($errorKey)
                                    <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    @endforeach

                    <!-- Remark / Comment Field -->
                    @php
                        $remarkModel = $isEditingLog
                            ? "savedLogRemarksById.{$editingSavedLogId}"
                            : "inlineCaptureRemarksByTemplate.{$tid}";
                        
                        $remarkChangeAction = $isEditingLog
                            ? "autoSaveSavedLogRemark('{$editingSavedLogId}')"
                            : "autoSaveInlineCapture('{$tid}')";
                    @endphp
                    <div class="col-md-6 col-12">
                        <label class="form-label small fw-bold text-secondary mb-1" for="card-input-remark">Comment / Remarks</label>
                        <input id="card-input-remark"
                               type="text"
                               class="form-control form-control-sm"
                               placeholder="Optional remarks"
                               wire:model.live.debounce.600ms="{{ $remarkModel }}"
                               wire:change="{{ $remarkChangeAction }}">
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="d-flex align-items-center justify-content-end mt-4 gap-2">
                    @if($isEditingLog)
                        <button type="button"
                                wire:click="toggleEditSavedLog('{{ $editingSavedLogId }}')"
                                class="btn btn-sm btn-success px-4 fw-bold shadow-sm">
                            <i class="mdi mdi-check me-1"></i> Done Editing
                        </button>
                    @else
                        <button type="button"
                                wire:click="resetInlineCaptureForTemplate('{{ $tid }}')"
                                class="btn btn-sm btn-outline-secondary px-3">
                            Clear
                        </button>
                        <button type="button"
                                wire:click="saveInlineCapture('{{ $tid }}', false)"
                                class="btn btn-sm text-white px-4 fw-bold shadow-sm"
                                style="background-color: #802424;">
                            <i class="mdi mdi-content-save me-1"></i> Save Reading
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="table-responsive env-matrix-wrap">
        <table class="table table-sm table-bordered env-matrix env-matrix--nested mb-0">
            <thead class="table-light">
                <tr class="env-matrix-head-row env-matrix-head-row--freq">
                    <th rowspan="2" class="env-matrix-date-head">Date</th>
                    @foreach($matrix['frequency_columns'] as $freqCol)
                        <th colspan="{{ $subColCount }}"
                            class="env-matrix-freq-head {{ ($nextCapture && $freqCol['slot'] === $nextCapture['slot']) ? 'env-matrix-freq-head--active' : '' }}">
                            {{ $freqCol['label'] }}
                            @if($nextCapture && $freqCol['slot'] === $nextCapture['slot'])
                                <span class="env-col-capture-badge">Now</span>
                            @endif
                        </th>
                    @endforeach
                    @if($matrix['has_unslotted'])
                        <th colspan="{{ $subColCount }}" class="env-matrix-freq-head">Unslotted</th>
                    @endif
                </tr>
                <tr class="env-matrix-head-row env-matrix-head-row--fields">
                    @foreach($matrix['frequency_columns'] as $freqCol)
                        @forelse($displayColumns as $col)
                            <th class="env-matrix-field-head">{{ $col['label'] }}</th>
                        @empty
                            <th class="env-matrix-field-head">Value</th>
                        @endforelse
                    @endforeach
                    @if($matrix['has_unslotted'])
                        @forelse($displayColumns as $col)
                            <th class="env-matrix-field-head">{{ $col['label'] }}</th>
                        @empty
                            <th class="env-matrix-field-head">Value</th>
                        @endforelse
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($matrix['rows'] as $row)
                    @php
                        $hasCaptureInRow = ($row['is_today'] ?? false) && collect($matrix['frequency_columns'])->contains(
                            fn (array $freqCol) => ($row['cells'][$freqCol['slot']]['is_capture'] ?? false)
                        );
                        $hasFilledFooterInRow = collect($row['cells'] ?? [])->contains(
                            fn (array $cell) => ($cell['filled'] ?? false) && ! ($cell['is_capture'] ?? false)
                        );
                        $hasFooterInRow = $hasCaptureInRow || $hasFilledFooterInRow;
                    @endphp
                    <tr class="{{ ($row['is_today'] ?? false) ? 'env-matrix-row--today' : '' }}">
                        <td class="env-matrix-date" rowspan="{{ $hasFooterInRow ? 2 : 1 }}">{{ $row['date_label'] }}</td>

                        @foreach($matrix['frequency_columns'] as $freqCol)
                            @php
                                $cell = $row['cells'][$freqCol['slot']] ?? ['filled' => false];
                                $isEditingCell = filled($cell['log_id'] ?? null)
                                    && ($editingSavedLogId ?? null) === ($cell['log_id'] ?? null);
                                $hasDerivedValues = collect($derivedColumns)->contains(function (array $varCol) use ($cell) {
                                    $val = $cell['variables'][$varCol['key']] ?? null;
                                    return $val !== null && $val !== '';
                                });
                                $lastColIndex = count($displayColumns) - 1;
                            @endphp

                            @if($cell['is_waiting'] ?? false)
                                <td colspan="{{ $subColCount }}" class="env-matrix-cell env-matrix-cell--waiting">
                                    <span class="env-matrix-cell--empty">Up next</span>
                                </td>
                            @elseif(($cell['is_capture'] ?? false) || $isEditingCell)
                                @forelse($displayColumns as $col)
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">
                                        @include('livewire.monitoring.partials.environmental.section-inline-capture-field', [
                                            'workspace' => $workspace,
                                            'template' => $template,
                                            'column' => $col,
                                            'tid' => $tid,
                                            'derivedColumns' => $derivedColumns,
                                            'savedLogId' => $isEditingCell ? $cell['log_id'] : null,
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">
                                        <span class="text-muted small">No capture fields configured</span>
                                    </td>
                                @endforelse
                            @else
                                @forelse($displayColumns as $index => $col)
                                    @php
                                        $columnNeedle = strtolower((string) ($col['key'] ?? '').' '.(string) ($col['label'] ?? ''));
                                        $isRemarkColumn = str_contains($columnNeedle, 'remark');
                                        $subcellClass = 'env-matrix-subcell';
                                        if ($cell['filled'] ?? false) {
                                            $subcellClass .= ' env-matrix-subcell--filled';
                                        }
                                        if ($isRemarkColumn) {
                                            $subcellClass .= ' '.$remarkSubcellClass($cell['verdict'] ?? null);
                                        }
                                    @endphp
                                    <td class="{{ trim($subcellClass) }}">
                                        @include('livewire.monitoring.partials.environmental.section-matrix-field-cell', [
                                            'cell' => $cell,
                                            'column' => $col,
                                            'derivedColumns' => $derivedColumns,
                                            'hasDerivedValues' => $hasDerivedValues,
                                            'showMore' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'showRemark' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'templateId' => $tid,
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell">
                                        @include('livewire.monitoring.partials.environmental.section-matrix-field-cell', [
                                            'cell' => $cell,
                                            'column' => ['key' => '', 'label' => 'Value', 'is_primary' => true],
                                            'derivedColumns' => [],
                                            'hasDerivedValues' => false,
                                            'templateId' => $tid,
                                        ])
                                    </td>
                                @endforelse
                            @endif
                        @endforeach

                        @if($matrix['has_unslotted'])
                            @php
                                $cell = $row['cells']['unslotted'] ?? ['filled' => false];
                                $isEditingCell = filled($cell['log_id'] ?? null)
                                    && ($editingSavedLogId ?? null) === ($cell['log_id'] ?? null);
                                $hasDerivedValues = collect($derivedColumns)->contains(function (array $varCol) use ($cell) {
                                    $val = $cell['variables'][$varCol['key']] ?? null;
                                    return $val !== null && $val !== '';
                                });
                                $lastColIndex = count($displayColumns) - 1;
                            @endphp
                            @if($isEditingCell)
                                @forelse($displayColumns as $col)
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">
                                        @include('livewire.monitoring.partials.environmental.section-inline-capture-field', [
                                            'workspace' => $workspace,
                                            'template' => $template,
                                            'column' => $col,
                                            'tid' => $tid,
                                            'derivedColumns' => $derivedColumns,
                                            'savedLogId' => $cell['log_id'],
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">—</td>
                                @endforelse
                            @else
                                @forelse($displayColumns as $index => $col)
                                    @php
                                        $columnNeedle = strtolower((string) ($col['key'] ?? '').' '.(string) ($col['label'] ?? ''));
                                        $isRemarkColumn = str_contains($columnNeedle, 'remark');
                                        $subcellClass = 'env-matrix-subcell';
                                        if ($cell['filled'] ?? false) {
                                            $subcellClass .= ' env-matrix-subcell--filled';
                                        }
                                        if ($isRemarkColumn) {
                                            $subcellClass .= ' '.$remarkSubcellClass($cell['verdict'] ?? null);
                                        }
                                    @endphp
                                    <td class="{{ trim($subcellClass) }}">
                                        @include('livewire.monitoring.partials.environmental.section-matrix-field-cell', [
                                            'cell' => $cell,
                                            'column' => $col,
                                            'derivedColumns' => $derivedColumns,
                                            'hasDerivedValues' => $hasDerivedValues,
                                            'showMore' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'showRemark' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'templateId' => $tid,
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell">—</td>
                                @endforelse
                            @endif
                        @endif
                    </tr>

                    @if($hasFooterInRow)
                        <tr class="env-matrix-capture-footer-row">
                            @foreach($matrix['frequency_columns'] as $freqCol)
                                @php $cell = $row['cells'][$freqCol['slot']] ?? ['filled' => false]; @endphp
                                @if($cell['is_capture'] ?? false)
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--capture-footer">
                                        @include('livewire.monitoring.partials.environmental.section-inline-capture-footer', [
                                            'workspace' => $workspace,
                                        ])
                                    </td>
                                @elseif($cell['filled'] ?? false)
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--saved-footer">
                                        @include('livewire.monitoring.partials.environmental.section-saved-log-footer', [
                                            'workspace' => $workspace,
                                            'cell' => $cell,
                                        ])
                                    </td>
                                @else
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--spacer"></td>
                                @endif
                            @endforeach
                            @if($matrix['has_unslotted'])
                                @php $cell = $row['cells']['unslotted'] ?? ['filled' => false]; @endphp
                                @if($cell['filled'] ?? false)
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--saved-footer">
                                        @include('livewire.monitoring.partials.environmental.section-saved-log-footer', [
                                            'workspace' => $workspace,
                                            'cell' => $cell,
                                        ])
                                    </td>
                                @else
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--spacer"></td>
                                @endif
                            @endif
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ $totalColspan }}" class="text-center text-muted py-4">
                            No logs in the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
