@php
    $stepId = (string) $step->id;
    $columns = $stepTableColumnsByStep[$stepId] ?? collect();
    $rowEntries = $stepTableRowsByStep[$stepId] ?? [];
@endphp
<section class="procedure-section mb-4" wire:key="proc-custom-table-{{ $stepId }}-ws-{{ $selectedWorksheetId }}">
    <div class="card shadow-sm border-0 procedure-section-card mb-4">
        <div class="card-header procedure-section-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">
                @if(!empty($stepGroup))
                <span class="text-muted small d-block mb-1">
                    <i class="mdi mdi-folder-outline"></i> {{ $stepGroup->title }}
                </span>
                @endif
                <i class="mdi mdi-table-large text-success"></i>
                {{ $step->step }}
                <span class="tag-badge tag-badge--neutral ml-2">Custom table</span>
                @if($step->table_mode === 'dynamic')
                <small class="text-muted d-block mt-1">Dynamic · {{ \App\Enums\Procedures\ProcedureTableRowDriver::tryFrom($step->row_driver ?? '')?->label() ?? $step->row_driver }}</small>
                @else
                <small class="text-muted d-block mt-1">Static rows</small>
                @endif
            </h6>
            <div class="d-flex gap-2">
                @if($step->table_mode === 'dynamic')
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="syncStepTableRows(@js($stepId))">
                    <i class="mdi mdi-sync"></i> Sync rows
                </button>
                @endif
                @if($step->allow_manual_rows)
                <button type="button" class="btn btn-sm btn-outline-success" wire:click="addStepTableManualRow(@js($stepId))">
                    <i class="mdi mdi-plus"></i> Add row
                </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($columns->isEmpty())
            <div class="text-center text-muted py-4 small">
                No columns configured. Edit the procedure worksheet and use <strong>Configure table</strong> on this step.
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered procedure-testkit-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            @foreach($columns as $col)
                            <th>
                                {{ $col->label }}
                                @if($col->is_required)<span class="text-danger">*</span>@endif
                            </th>
                            @endforeach
                            @if($step->allow_manual_rows)
                            <th style="width: 50px;"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rowEntries as $entry)
                        @php $row = $entry['row']; @endphp
                        <tr wire:key="pstab-{{ $stepId }}-{{ $row->id }}">
                            <td class="text-muted">{{ $row->row_index }}</td>
                            @foreach($columns as $col)
                            <td>
                                @php
                                    $columnConfig = is_array($col->dataset_config ?? null) ? $col->dataset_config : [];
                                    $isFixedColumn = ($columnConfig['column_mode'] ?? '') === 'fixed';
                                    $isChoiceColumn = in_array(($columnConfig['column_mode'] ?? ''), ['choices', 'static'], true);
                                    $choiceControl = (string) ($columnConfig['choice_control'] ?? '');
                                    $choiceOptions = is_array($columnConfig['static_options'] ?? null) ? $columnConfig['static_options'] : [];
                                    $currentCell = $stepTableData[$stepId][$row->id][$col->key] ?? '';
                                    $selectedChoices = is_array($currentCell)
                                        ? $currentCell
                                        : collect(explode(',', (string) $currentCell))->map(fn ($v) => trim($v))->filter()->values()->all();
                                @endphp
                                @if($col->column_type === 'derived')
                                <span class="text-muted">{{ $stepTableData[$stepId][$row->id][$col->key] ?? '—' }}</span>
                                @elseif($isFixedColumn)
                                <span class="text-muted">{{ $currentCell !== '' && $currentCell !== null ? $currentCell : '—' }}</span>
                                @elseif($col->column_type === 'dataset')
                                @php $dsOptions = $this->getStepTableDatasetOptions($col); @endphp
                                <select class="form-control form-control-sm"
                                    wire:model.live.debounce.800ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                    <option value="">—</option>
                                    @foreach($dsOptions as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->label }}</option>
                                    @endforeach
                                </select>
                                @elseif($isChoiceColumn && $choiceControl === 'radio')
                                <div class="d-flex flex-column gap-1">
                                    @foreach($choiceOptions as $opt)
                                    <label class="mb-0 small">
                                        <input type="radio"
                                               name="capture-col-{{ $stepId }}-{{ $row->id }}-{{ $col->id }}"
                                               value="{{ $opt }}"
                                               @checked((string) $currentCell === (string) $opt)
                                               wire:change="persistStepTableCell(@js($stepId), @js($row->id), @js($col->key), $event.target.value)">
                                        {{ $opt }}
                                    </label>
                                    @endforeach
                                </div>
                                @elseif($isChoiceColumn && $choiceControl === 'checkbox')
                                <div class="d-flex flex-column gap-1">
                                    @foreach($choiceOptions as $opt)
                                    <label class="mb-0 small">
                                        <input type="checkbox"
                                               value="{{ $opt }}"
                                               @checked(in_array((string) $opt, $selectedChoices, true))
                                               wire:change="toggleStepTableChoiceOption(@js($stepId), @js($row->id), @js($col->key), @js((string) $opt), $event.target.checked)">
                                        {{ $opt }}
                                    </label>
                                    @endforeach
                                </div>
                                @elseif($isChoiceColumn && $choiceControl === 'select')
                                <select class="form-control form-control-sm"
                                        wire:model.live.debounce.600ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                    <option value="">—</option>
                                    @foreach($choiceOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @elseif($col->input_data_type === 'textarea')
                                <textarea class="form-control form-control-sm" rows="2"
                                    wire:model.live.debounce.800ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}"></textarea>
                                @elseif($col->input_data_type === 'boolean')
                                <div class="form-check mb-0">
                                    <input type="checkbox"
                                        class="form-check-input"
                                        wire:model.live="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                </div>
                                @elseif($col->input_data_type === 'date')
                                <input type="date" class="form-control form-control-sm"
                                    wire:model.live.debounce.800ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                @elseif($col->input_data_type === 'time')
                                <input type="time" class="form-control form-control-sm"
                                    wire:model.live.debounce.800ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                @elseif($col->input_data_type === 'datetime')
                                <input type="datetime-local" class="form-control form-control-sm"
                                    wire:model.live.debounce.800ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                @elseif($col->input_data_type === 'number')
                                <input type="number" class="form-control form-control-sm"
                                    wire:model.live.debounce.800ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                @else
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live.debounce.800ms="stepTableData.{{ $stepId }}.{{ $row->id }}.{{ $col->key }}">
                                @endif
                            </td>
                            @endforeach
                            @if($step->allow_manual_rows)
                            <td class="text-center">
                                @if($row->row_source === 'manual')
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                    wire:click="removeStepTableManualRow(@js($stepId), @js($row->id))"
                                    onclick="return confirm('Remove this row?')">
                                    <i class="mdi mdi-delete-outline"></i>
                                </button>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $columns->count() + ($step->allow_manual_rows ? 2 : 1) }}" class="text-center text-muted py-4">
                                @if($step->table_mode === 'dynamic')
                                No rows yet. Click <strong>Sync rows</strong> to generate rows from the row driver.
                                @else
                                No static rows configured on this step.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</section>
