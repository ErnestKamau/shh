@php
    $stepId = (string) $step->id;
    $columns = $formulaStepTableColumnsByStep[$stepId] ?? collect();
    $rowEntries = $formulaStepTableRowsByStep[$stepId] ?? [];
    $tableData = $formulaStepTableData[$stepId] ?? [];
    $sampleCount = collect($rowEntries)->filter(fn ($entry) => ! empty($entry['sample_detail_id']))->count();
@endphp
<section class="mb-3" wire:key="formula-custom-table-{{ $stepId }}">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">
                <i class="mdi mdi-table-large text-success"></i>
                {{ $step->label }}
                @if($sampleCount > 0)
                <span class="badge badge-light border ml-1">{{ $sampleCount }} {{ Str::plural('sample', $sampleCount) }}</span>
                @endif
                <small class="text-muted d-block mt-1">{{ ucfirst($step->table_mode ?? 'dynamic') }} table · applies to all samples on this worksheet</small>
            </h6>
            <div class="d-flex align-items-center">
                @if($step->table_mode === 'dynamic')
                <button type="button" class="btn btn-sm btn-outline-primary mr-2" wire:click="syncFormulaStepTableRows(@js($stepId))">
                    <i class="mdi mdi-sync"></i> Sync rows
                </button>
                @endif
                @if($step->allow_manual_rows)
                <button type="button" class="btn btn-sm btn-outline-success" wire:click="addFormulaStepTableManualRow(@js($stepId))">
                    <i class="mdi mdi-plus"></i> Add row
                </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($columns->isEmpty())
            <div class="text-center text-muted py-4 small">
                No columns configured. Edit the formula and use <strong>Configure table</strong> on this step.
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            @foreach($columns as $col)
                            <th>{{ $col->label }}@if($col->is_required)<span class="text-danger">*</span>@endif</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rowEntries as $entry)
                        @php
                            $row = $entry['row'];
                            $sampleDetailId = $entry['sample_detail_id'] ?? null;
                        @endphp
                        <tr wire:key="fst-{{ $stepId }}-{{ $row->id }}">
                            <td class="text-muted">{{ $row->row_index }}</td>
                            @foreach($columns as $col)
                            <td>
                                @php
                                    $columnConfig = is_array($col->dataset_config ?? null) ? $col->dataset_config : [];
                                    $isFixedColumn = ($columnConfig['column_mode'] ?? '') === 'fixed';
                                    $wirePath = "formulaStepTableData.{$stepId}.{$row->id}.{$col->key}";
                                @endphp
                                @if($col->column_type === 'derived')
                                <span class="text-muted">{{ $tableData[$row->id][$col->key] ?? '—' }}</span>
                                @elseif($col->column_type === 'dataset' && ($col->model_tied_to ?? '') === 'samples')
                                <span class="text-muted">{{ $this->resolveCustomTableDatasetValueForSample($col, $sampleDetailId) ?: '—' }}</span>
                                @elseif($isFixedColumn)
                                <span class="text-muted">{{ $tableData[$row->id][$col->key] ?? '—' }}</span>
                                @elseif($col->input_data_type === 'textarea')
                                <textarea class="form-control form-control-sm" rows="2"
                                    wire:model.live.debounce.800ms="{{ $wirePath }}"></textarea>
                                @else
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live.debounce.800ms="{{ $wirePath }}">
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $columns->count() + 1 }}" class="text-center text-muted py-3 small">
                                @if($step->table_mode === 'dynamic')
                                No rows yet. Click <strong>Sync rows</strong> to generate one row per sample.
                                @else
                                No static rows configured.
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
