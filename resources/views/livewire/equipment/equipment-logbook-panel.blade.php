<div>
    @if(session()->has('logbook_message'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            {{ session('logbook_message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Section 1: Column configuration --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-table-column"></i> Logbook columns</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="showCreateLogbookColumnModal">
                <i class="mdi mdi-plus"></i> Add column
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Define the fields users fill when capturing log entries. Types include user input, checkbox, derived expressions, and dataset lookups.
            </p>
            @if(empty($logbookColumns))
                <div class="alert alert-light border mb-0">
                    <i class="mdi mdi-information-outline"></i> No columns configured yet. Add at least one column before capturing logs.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Label</th>
                                <th>Key</th>
                                <th>Type</th>
                                <th>Input / details</th>
                                <th>Required</th>
                                <th style="width:120px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logbookColumns as $col)
                                <tr>
                                    <td>{{ $col['order'] }}</td>
                                    <td>{{ $col['label'] }}</td>
                                    <td><code>{{ $col['key'] }}</code></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $columnTypeOptions[$col['column_type']] ?? $col['column_type'] }}</span>
                                    </td>
                                    <td class="small text-muted">
                                        @if($col['column_type'] === 'input')
                                            {{ $inputDataTypeOptions[$col['input_data_type']] ?? $col['input_data_type'] }}
                                        @elseif($col['column_type'] === 'derived')
                                            <span title="{{ $col['expression'] }}">Expression</span>
                                        @elseif($col['column_type'] === 'dataset')
                                            {{ ($col['dataset_config']['source_table'] ?? '') }}.{{ ($col['dataset_config']['source_display_column'] ?? '') }}
                                        @endif
                                    </td>
                                    <td>{{ ($col['is_required'] ?? false) ? 'Yes' : 'No' }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-link p-0 me-2" wire:click="showEditLogbookColumnModal('{{ $col['id'] }}')">Edit</button>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="showDeleteLogbookColumnModalInit('{{ $col['id'] }}')">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Section 2: Logs --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-book-open-page-variant"></i> Logs</h5>
            <button type="button" class="btn btn-sm btn-primary" wire:click="toggleCaptureForm" @disabled($configuredColumns->isEmpty())>
                <i class="mdi mdi-{{ $showCaptureForm ? 'close' : 'plus' }}"></i>
                {{ $showCaptureForm ? 'Cancel' : 'Capture log' }}
            </button>
        </div>
        <div class="card-body">
            @if($showCaptureForm)
                <div class="border rounded p-3 mb-4 bg-light">
                    <h6 class="mb-3">New log entry</h6>
                    <form wire:submit.prevent="saveLogbookEntry">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Date & time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model="captureLoggedAt">
                                @error('captureLoggedAt') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Notes</label>
                                <input type="text" class="form-control" wire:model="captureNotes" placeholder="Optional notes for this entry">
                                @error('captureNotes') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row">
                            @foreach($configuredColumns as $column)
                                @if($column->column_type === 'derived')
                                    @continue
                                @endif
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        {{ $column->label }}
                                        @if($column->is_required)<span class="text-danger">*</span>@endif
                                    </label>
                                    @if($column->column_type === 'dataset')
                                        <select class="form-control" wire:model="captureValues.{{ $column->key }}">
                                            <option value="">— Select —</option>
                                            @foreach(($datasetOptionsByColumn[$column->key] ?? collect()) as $opt)
                                                <option value="{{ $opt->id }}">{{ $opt->label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($column->input_data_type === 'boolean')
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="lb-{{ $column->key }}" wire:model.boolean="captureValues.{{ $column->key }}">
                                            <label class="form-check-label" for="lb-{{ $column->key }}">Yes</label>
                                        </div>
                                    @elseif($column->input_data_type === 'textarea')
                                        <textarea class="form-control" rows="2" wire:model="captureValues.{{ $column->key }}"></textarea>
                                    @elseif($column->input_data_type === 'date')
                                        <input type="date" class="form-control" wire:model="captureValues.{{ $column->key }}">
                                    @elseif($column->input_data_type === 'number')
                                        <input type="number" class="form-control" step="any" wire:model="captureValues.{{ $column->key }}">
                                    @else
                                        <input type="text" class="form-control" wire:model="captureValues.{{ $column->key }}">
                                    @endif
                                    @if($column->help_text)
                                        <small class="text-muted">{{ $column->help_text }}</small>
                                    @endif
                                    @error('captureValues.'.$column->key) <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-content-save"></i> Save log entry
                        </button>
                    </form>
                </div>
            @endif

            @if($entries->isEmpty())
                <div class="alert alert-light border mb-0">
                    <i class="mdi mdi-information-outline"></i> No log entries recorded yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Logged at</th>
                                <th>Recorded by</th>
                                @foreach($configuredColumns as $column)
                                    <th>{{ $column->label }}</th>
                                @endforeach
                                <th>Notes</th>
                                <th style="width:70px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                                @php
                                    $valuesByColumnId = $entry->values->keyBy('column_id');
                                @endphp
                                <tr>
                                    <td>{{ $entry->logged_at?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $entry->loggedByUser?->name ?? '—' }}</td>
                                    @foreach($configuredColumns as $column)
                                        @php $cell = $valuesByColumnId->get($column->id); @endphp
                                        <td>{{ $cell ? $this->formatCellDisplay($cell) : '—' }}</td>
                                    @endforeach
                                    <td class="small">{{ $entry->notes ?: '—' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                                wire:click="deleteLogbookEntry('{{ $entry->id }}')"
                                                wire:confirm="Delete this log entry?">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Column modal --}}
    @if($showLogbookColumnModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingLogbookColumn ? 'Edit column' : 'Add column' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showLogbookColumnModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model.live="columnLabel">
                                @error('columnLabel') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Key <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="columnKey" placeholder="e.g. operator_name">
                                @error('columnKey') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Column type</label>
                                <select class="form-control" wire:model.live="columnType">
                                    @foreach($columnTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($columnType === 'input')
                            <div class="col-md-4">
                                <label class="form-label">Input type</label>
                                <select class="form-control" wire:model="columnInputDataType">
                                    @foreach($inputDataTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <label class="form-label">Order</label>
                                <input type="number" class="form-control" wire:model="columnOrder" min="1">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Help text</label>
                                <input type="text" class="form-control" wire:model="columnHelpText">
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" wire:model="columnIsRequired" id="col-required">
                                    <label class="form-check-label" for="col-required">Required when capturing logs</label>
                                </div>
                            </div>
                            @if($columnType === 'derived')
                            <div class="col-md-12">
                                <label class="form-label">Expression <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="3" wire:model="columnExpression" placeholder="e.g. {reading_a} + {reading_b}"></textarea>
                                @error('columnExpression') <small class="text-danger">{{ $message }}</small> @enderror
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" wire:click="validateLogbookExpressionPreview">Validate</button>
                                @if($expressionValidationMessage)
                                    <small class="d-block mt-1 {{ str_contains($expressionValidationMessage, 'valid') ? 'text-success' : 'text-danger' }}">{{ $expressionValidationMessage }}</small>
                                @endif
                            </div>
                            @endif
                            @if($columnType === 'dataset')
                            <div class="col-md-6">
                                <label class="form-label">Source table <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model.live="columnDatasetSourceTable">
                                    <option value="">— Select table —</option>
                                    @foreach($schemaTableOptions as $opt)
                                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('columnDatasetSourceTable') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display column <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model="columnDatasetSourceColumn" @disabled($columnDatasetSourceTable === '')>
                                    <option value="">— Select column —</option>
                                    @foreach($schemaColumnOptions as $opt)
                                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('columnDatasetSourceColumn') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showLogbookColumnModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveLogbookColumn">Save column</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteLogbookColumnModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete column</h5>
                        <button type="button" class="btn-close" wire:click="$set('showDeleteLogbookColumnModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        Delete column <strong>{{ $deletingLogbookColumn?->label }}</strong>? Existing log values for this column will be removed.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteLogbookColumnModal', false)">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteLogbookColumn">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
