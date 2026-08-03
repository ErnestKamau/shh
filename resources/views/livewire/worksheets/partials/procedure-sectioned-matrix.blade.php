{{--
    Sectioned-Matrix Procedure Partial
    ──────────────────────────────────
    Rendered when a ProcedureWorksheet has layout_settings.mode = "sectioned_matrix"
    and the component is loaded with a $sectionKey (from grouped pipeline item config).

    Variables available from parent scope:
        $sectionConfig   array   The section JSON fragment from layout_settings
        $sectionKey      string  e.g. 'enrichment'
        $analysisSamples Collection<CapturedResult>  already-loaded with sample relation
        $selectedSamples array   sample_detail_ids currently selected
        $worksheetId     string
--}}

@php
    $rows        = $sectionConfig['rows'] ?? [];
    $columns     = $sectionConfig['columns'] ?? [];
    $sectionLabel = $sectionConfig['label'] ?? ucfirst(str_replace('_', ' ', $sectionKey));

    // Split columns: shared (volume/text) vs per-sample (step_select)
    $sharedCols  = array_values(array_filter($columns, fn($c) => (bool) ($c['shared'] ?? false)));
    $sampleCols  = array_values(array_filter($columns, fn($c) => ! ($c['shared'] ?? false)));

    // Samples to show in the matrix: respect the selected-sample filter
    $matrixSamples = $analysisSamples
        ->filter(fn($r) => $r->sample && in_array($r->sample->id, $selectedSamples))
        ->values();

    // Build a lookup: step_name (lower) → step object for the current worksheet
    $stepsByName = collect($this->getScalarStepsProperty())
        ->keyBy(fn($s) => strtolower(trim($s->step)));

    // Build the shared data map from matrixInputData (loaded via loadMatrixInputData()).
    // matrixInputData is [row_key => [col_key => {value, uom_id}]]
    $sharedData = $matrixInputData ?? [];
@endphp

<section class="procedure-matrix-section mb-4" wire:key="matrix-section-{{ $sectionKey }}-ws-{{ $worksheetId }}">
    <div class="card shadow-sm border-0">
        <div class="card-header procedure-section-header bg-white d-flex align-items-center gap-2">
            <i class="mdi mdi-table-large text-success"></i>
            <h6 class="mb-0">{{ $sectionLabel }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered proc-matrix-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            {{-- Row label column --}}
                            <th class="proc-matrix-row-label" style="min-width:150px;">Phase / Medium</th>

                            {{-- Shared columns (volume, incubation, text) --}}
                            @foreach($sharedCols as $col)
                                <th class="text-center proc-matrix-shared-col" style="min-width:110px;">
                                    {{ $col['label'] ?? $col['key'] }}
                                </th>
                            @endforeach

                            {{-- Per-sample columns × samples --}}
                            @if($matrixSamples->isNotEmpty())
                                @foreach($sampleCols as $col)
                                    @foreach($matrixSamples as $cr)
                                        <th class="text-center proc-matrix-sample-col" style="min-width:130px;">
                                            {{ $col['label'] ?? $col['key'] }}<br>
                                            <small class="text-muted fw-normal">{{ $cr->sample?->sample_code ?? 'S'.$loop->parent->index }}</small>
                                        </th>
                                    @endforeach
                                @endforeach
                            @else
                                @foreach($sampleCols as $col)
                                    <th class="text-center proc-matrix-sample-col" style="min-width:130px;">
                                        {{ $col['label'] ?? $col['key'] }}<br>
                                        <small class="text-muted fw-normal">—</small>
                                    </th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        @php
                            $rowKey   = $row['key'];
                            $rowLabel = $row['label'] ?? $rowKey;
                        @endphp
                        <tr>
                            {{-- Row label --}}
                            <td class="proc-matrix-row-label fw-semibold align-middle small">{{ $rowLabel }}</td>

                            {{-- Shared cells --}}
                            @foreach($sharedCols as $col)
                            @php
                                $colKey       = $col['key'];
                                $colType      = $col['type'] ?? 'text';
                                $storedVal    = $sharedData[$rowKey][$colKey]['value'] ?? '';
                                $storedUom    = $sharedData[$rowKey][$colKey]['uom_id'] ?? ($col['default_uom'] ?? '');
                                $defaultVal   = $col['default_value_by_row'][$rowKey] ?? ($col['default_value'] ?? '');
                                $displayVal   = $storedVal !== '' ? $storedVal : $defaultVal;
                                $lwModel      = "matrixInputData.{$rowKey}.{$colKey}.value";
                                $lwUomModel   = "matrixInputData.{$rowKey}.{$colKey}.uom_id";
                            @endphp
                            <td class="align-middle text-center p-1">
                                @if($colType === 'reagent_input')
                                    <div class="input-group input-group-sm proc-matrix-vol-group" style="min-width:100px;">
                                        <input type="number"
                                            step="0.01"
                                            class="form-control form-control-sm text-right"
                                            placeholder="{{ $defaultVal }}"
                                            wire:model.lazy="matrixInputData.{{ $rowKey }}.{{ $colKey }}.value"
                                        >
                                        @if(! empty($col['uom_selectable']))
                                            <div class="input-group-append">
                                                <select class="form-control form-control-sm proc-matrix-uom-select"
                                                    style="max-width:60px;"
                                                    wire:model.lazy="matrixInputData.{{ $rowKey }}.{{ $colKey }}.uom_id">
                                                    @foreach(['mL','L','µL','g','kg','mg'] as $uomOption)
                                                        <option value="{{ $uomOption }}" {{ $storedUom === $uomOption || ($storedUom === '' && ($col['default_uom'] ?? '') === $uomOption) ? 'selected' : '' }}>{{ $uomOption }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($colType === 'date')
                                    <input type="date"
                                        class="form-control form-control-sm"
                                        wire:model.lazy="matrixInputData.{{ $rowKey }}.{{ $colKey }}.value"
                                    >
                                @else
                                    {{-- text --}}
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        placeholder="{{ $defaultVal }}"
                                        wire:model.lazy="matrixInputData.{{ $rowKey }}.{{ $colKey }}.value"
                                    >
                                @endif
                            </td>
                            @endforeach

                            {{-- Per-sample cells --}}
                            @if($matrixSamples->isNotEmpty())
                                @foreach($sampleCols as $col)
                                    @php
                                        $colKey  = $col['key'];
                                        $colType = $col['type'] ?? 'text';
                                        // Resolve which step handles this (row, col) combination.
                                        $stepName = $col['step_key_by_row'][$rowKey] ?? null;
                                        $step = $stepName ? ($stepsByName[strtolower(trim($stepName))] ?? null) : null;
                                        $stepOptions = $step?->select_options ?? $col['options'] ?? [];
                                    @endphp
                                    @foreach($matrixSamples as $cr)
                                        @php
                                            $crId    = (string) $cr->id;
                                            $stepId  = $step ? (string) $step->id : null;
                                        @endphp
                                        <td class="align-middle text-center p-1">
                                            @if($colType === 'step_select' && $stepId && ! empty($stepOptions))
                                                <select class="form-control form-control-sm proc-matrix-obs-select"
                                                    wire:model.lazy="inputValues.{{ $crId }}.{{ $stepId }}"
                                                >
                                                    <option value="">—</option>
                                                    @foreach($stepOptions as $opt)
                                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($colType === 'select' && ! empty($col['options']))
                                                <select class="form-control form-control-sm"
                                                    wire:model.lazy="inputValues.{{ $crId }}.{{ $stepId }}"
                                                >
                                                    <option value="">—</option>
                                                    @foreach($col['options'] as $opt)
                                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text"
                                                    class="form-control form-control-sm"
                                                    wire:model.lazy="inputValues.{{ $crId }}.{{ $colKey }}"
                                                >
                                            @endif
                                        </td>
                                    @endforeach
                                @endforeach
                            @else
                                @foreach($sampleCols as $col)
                                    <td class="text-center text-muted small align-middle p-1">—</td>
                                @endforeach
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Save button for shared inputs --}}
    @if(! empty($sharedCols))
    <div class="d-flex justify-content-end mt-2 mb-2">
        <button type="button" class="btn btn-sm btn-outline-success" wire:click="saveMatrixInputsBulk">
            <i class="mdi mdi-content-save-outline"></i> Save inputs
        </button>
    </div>
    @endif
</section>

<style>
    .proc-matrix-table th,
    .proc-matrix-table td {
        vertical-align: middle;
        font-size: 0.8rem;
    }
    .proc-matrix-row-label {
        background: #f8f9fa;
        font-weight: 600;
    }
    .proc-matrix-shared-col {
        background: #fffdf5;
    }
    .proc-matrix-sample-col {
        background: #f0f9ff;
    }
    .proc-matrix-vol-group input[type="number"] {
        max-width: 70px;
    }
    .proc-matrix-obs-select {
        font-size: 0.78rem;
    }
    .proc-matrix-uom-select {
        font-size: 0.78rem;
        padding: 0 2px;
    }
</style>
