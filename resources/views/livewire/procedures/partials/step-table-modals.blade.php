@if($showConfigureTableModal)
<div class="pw-modal show d-block pw-table-config-modal" tabindex="-1" wire:click.self="closeConfigureTableModal">
    <div class="modal-dialog modal-xl modal-dialog-scrollable pw-modal-dialog pw-modal-dialog--xl" wire:click.stop>
        <div class="modal-content pw-modal-content">
            <div class="modal-header pw-modal-header pw-table-wizard__header">
                <div>
                    <h5 class="modal-title mb-1">
                        <i class="mdi mdi-table-cog text-primary mr-1"></i>
                        Configure custom table
                    </h5>
                    <p class="text-muted small mb-0">Set up columns, define static row values, then preview.</p>
                </div>
                <button type="button" class="close pw-modal-close" wire:click="closeConfigureTableModal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="pw-table-wizard__stepper px-4 pt-3 pb-0">
                <div class="pw-table-wizard__steps">
                    @foreach([
                        1 => ['icon' => 'mdi-table-column', 'label' => 'Columns', 'hint' => 'Structure'],
                        2 => ['icon' => 'mdi-table-row-plus-after', 'label' => 'Static rows', 'hint' => 'Fixed values'],
                        3 => ['icon' => 'mdi-eye-check-outline', 'label' => 'Preview', 'hint' => 'Review & save'],
                    ] as $stepNum => $stepMeta)
                    @php
                        $isActive = $configureTableWizardStep === $stepNum;
                        $isDone = $configureTableWizardStep > $stepNum;
                        $canJump = $stepNum === 1 || count($stepTableColumns) > 0;
                    @endphp
                    <button type="button"
                            class="pw-table-wizard__step {{ $isActive ? 'pw-table-wizard__step--active' : '' }} {{ $isDone ? 'pw-table-wizard__step--done' : '' }}"
                            wire:click="configureTableWizardGoTo({{ $stepNum }})"
                            @disabled(! $canJump && $stepNum > 1)>
                        <span class="pw-table-wizard__step-index">
                            @if($isDone)
                                <i class="mdi mdi-check"></i>
                            @else
                                {{ $stepNum }}
                            @endif
                        </span>
                        <span class="pw-table-wizard__step-text">
                            <span class="pw-table-wizard__step-label"><i class="mdi {{ $stepMeta['icon'] }} mr-1"></i>{{ $stepMeta['label'] }}</span>
                            <span class="pw-table-wizard__step-hint">{{ $stepMeta['hint'] }}</span>
                        </span>
                    </button>
                    @if($stepNum < 3)
                    <span class="pw-table-wizard__connector {{ $configureTableWizardStep > $stepNum ? 'pw-table-wizard__connector--done' : '' }}"></span>
                    @endif
                    @endforeach
                </div>
            </div>

            <div class="modal-body pw-modal-body pw-table-wizard__body">
                @if($configureTableWizardStep === 1)
                <section class="pw-table-wizard__panel">
                    <div class="pw-table-wizard__panel-head">
                        <div>
                            <h6 class="mb-1"><i class="mdi mdi-table-column text-primary"></i> Table columns</h6>
                            <p class="text-muted small mb-0">Define each column. <strong>Static</strong> columns get values per row in the next step; <strong>user input</strong> columns are filled during capture.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary pw-table-wizard__cta" wire:click="showCreateStepColumnModalInit">
                            <i class="mdi mdi-plus"></i> Add column
                        </button>
                    </div>

                    @if(count($stepTableColumns) > 0)
                    <div class="pw-table-shell pw-table-wizard__table-wrap">
                        <table class="table table-sm mb-0 workflow-table pw-modern-table">
                            <thead>
                                <tr>
                                    <th style="width: 56px;">#</th>
                                    <th>Label</th>
                                    <th>Key</th>
                                    <th>Type</th>
                                    <th style="width: 88px;">Required</th>
                                    <th class="pw-table-wizard__col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stepTableColumns as $col)
                                <tr wire:key="stcol-{{ $col['id'] }}">
                                    <td class="text-muted">{{ $col['order'] }}</td>
                                    <td class="font-weight-medium">{{ $col['label'] }}</td>
                                    <td><code class="pw-table-key">{{ $col['key'] }}</code></td>
                                    <td>
                                        <span class="pw-col-type-badge pw-col-type-badge--{{ $this->stepTableColumnIsStaticConfigurable($col) ? 'static' : 'capture' }}">
                                            <i class="mdi {{ $this->stepTableColumnDisplayTypeIcon($col) }}"></i>
                                            {{ $this->stepTableColumnDisplayTypeLabel($col) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($col['is_required'])
                                        <span class="pw-pill pw-pill--yes">Yes</span>
                                        @else
                                        <span class="pw-pill pw-pill--muted">No</span>
                                        @endif
                                    </td>
                                    <td class="pw-table-wizard__col-actions">
                                        <div class="d-flex flex-nowrap pw-table-wizard__action-btns">
                                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="showEditStepColumnModalInit(@js($col['id']))" title="Edit column">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="deleteStepColumn(@js($col['id']))" title="Delete column">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="pw-table-wizard__empty">
                        <i class="mdi mdi-table-column-plus-after"></i>
                        <p class="mb-2 font-weight-medium">No columns yet</p>
                        <p class="text-muted small mb-0">Add at least one column to continue.</p>
                    </div>
                    @endif

                    @if($showCreateStepColumnModal || $showEditStepColumnModal)
                    <div class="pw-modal-card pw-table-wizard__inline-form mt-3">
                        <div class="pw-modal-card-title">
                            <i class="mdi {{ $showEditStepColumnModal ? 'mdi-pencil' : 'mdi-plus-circle-outline' }}"></i>
                            {{ $showEditStepColumnModal ? 'Edit column' : 'Add column' }}
                        </div>
                        <div class="row pw-inline-column-form__grid">
                            <div class="col-md-6">
                                <label class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model.live="stepColumnLabel">
                                @error('stepColumnLabel') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Key <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="stepColumnKey">
                                @error('stepColumnKey') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Column type</label>
                                <select class="form-control" wire:model.live="stepColumnType">
                                    @foreach($stepColumnTypeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($stepColumnType === 'input')
                            <div class="col-md-6">
                                <label class="form-label">Input data type</label>
                                <select class="form-control" wire:model.live="stepColumnInputDataType">
                                    @foreach($inputDataTypeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(in_array($stepColumnInputDataType, ['radio', 'checkbox', 'select'], true))
                            <div class="col-12">
                                <label class="form-label">Input options (one per line) <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="4" wire:model="stepColumnStaticOptions"></textarea>
                                @error('stepColumnStaticOptions') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            @endif
                            @endif
                            @if($stepColumnType === 'static')
                            <div class="col-12">
                                <div class="pw-table-wizard__hint-box">
                                    <i class="mdi mdi-information-outline"></i>
                                    <span><strong>Static column</strong> — You will enter a value for each row in step 2. These values stay fixed during capture.</span>
                                </div>
                            </div>
                            @endif
                            @if($stepColumnType === 'dataset')
                            <div class="col-md-6">
                                <label class="form-label">Dataset</label>
                                <select class="form-control" wire:model="stepColumnModelTiedTo">
                                    <option value="">Select...</option>
                                    @foreach($stepTableDatasetOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('stepColumnModelTiedTo') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            @endif
                            @if($stepColumnType === 'derived')
                            <div class="col-12">
                                <label class="form-label">Expression</label>
                                <textarea class="form-control" rows="3" wire:model="stepColumnExpression" placeholder="{col_a} + {col_b}"></textarea>
                                @error('stepColumnExpression') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            @endif
                            <div class="col-md-4">
                                <label class="form-label">Order</label>
                                <input type="number" class="form-control" wire:model="stepColumnOrder" min="1">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Help text</label>
                                <input type="text" class="form-control" wire:model="stepColumnHelpText">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="step-col-required-inline" wire:model="stepColumnIsRequired">
                                    <label class="form-check-label" for="step-col-required-inline">Required at capture</label>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end pw-inline-column-form__actions">
                                <button type="button" class="btn btn-light" wire:click="closeStepColumnModal">Cancel</button>
                                @if($showEditStepColumnModal)
                                <button type="button" class="btn btn-primary" wire:click="updateStepColumn">Update column</button>
                                @else
                                <button type="button" class="btn btn-primary" wire:click="createStepColumn">Add column</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </section>
                @endif

                @if($configureTableWizardStep === 2)
                <section class="pw-table-wizard__panel">
                    <div class="pw-table-wizard__panel-head">
                        <div>
                            <h6 class="mb-1"><i class="mdi mdi-table-row text-primary"></i> Static rows</h6>
                            <p class="text-muted small mb-0">Enter values only for <strong>static</strong> columns. User-input columns show their field type and are completed during capture.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary pw-table-wizard__cta" wire:click="addStaticRow" @disabled(count($stepTableColumns) === 0)>
                            <i class="mdi mdi-plus"></i> Add row
                        </button>
                    </div>

                    @php
                        $staticConfigurableColumns = collect($stepTableColumns)->filter(fn ($col) => $this->stepTableColumnIsStaticConfigurable($col))->count();
                    @endphp
                    @if($staticConfigurableColumns === 0)
                    <div class="pw-table-wizard__hint-box pw-table-wizard__hint-box--warn mb-3">
                        <i class="mdi mdi-alert-outline"></i>
                        <span>No static columns defined. Add a <strong>Static</strong> column in step 1, or continue to preview if all columns are user input.</span>
                    </div>
                    @endif

                    @if(count($stepStaticRows) > 0 && count($stepTableColumns) > 0)
                    <div class="pw-table-shell pw-table-wizard__table-wrap">
                        <table class="table table-sm mb-0 pw-modern-table">
                            <thead>
                                <tr>
                                    <th style="width: 56px;">#</th>
                                    @foreach($stepTableColumns as $col)
                                    <th>
                                        {{ $col['label'] }}
                                        @if(! $this->stepTableColumnIsStaticConfigurable($col))
                                        <span class="pw-col-type-badge pw-col-type-badge--capture pw-col-type-badge--xs d-block mt-1">
                                            <i class="mdi {{ $this->stepTableColumnDisplayTypeIcon($col) }}"></i>
                                            Capture
                                        </span>
                                        @endif
                                    </th>
                                    @endforeach
                                    <th style="width: 72px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stepStaticRows as $staticRow)
                                <tr wire:key="strow-{{ $staticRow['id'] }}">
                                    <td class="text-muted align-middle">{{ $staticRow['order'] }}</td>
                                    @foreach($stepTableColumns as $col)
                                    <td class="align-middle">
                                        @if($this->stepTableColumnIsStaticConfigurable($col))
                                        <input type="text"
                                               class="form-control form-control-sm"
                                               value="{{ $stepStaticCellValues[$staticRow['id']][$col['id']] ?? '' }}"
                                               placeholder="Static value"
                                               wire:change="saveStaticCell(@js($staticRow['id']), @js($col['id']), $event.target.value)">
                                        @else
                                        <span class="pw-col-type-badge pw-col-type-badge--readonly">
                                            <i class="mdi {{ $this->stepTableColumnDisplayTypeIcon($col) }}"></i>
                                            {{ $this->stepTableColumnDisplayTypeLabel($col) }}
                                        </span>
                                        @endif
                                    </td>
                                    @endforeach
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="removeStaticRow(@js($staticRow['id']))" title="Remove row">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="pw-table-wizard__empty">
                        <i class="mdi mdi-table-row-plus-after"></i>
                        <p class="mb-2 font-weight-medium">No rows yet</p>
                        <p class="text-muted small mb-0">Add rows to build your static table structure.</p>
                    </div>
                    @endif
                </section>
                @endif

                @if($configureTableWizardStep === 3)
                <section class="pw-table-wizard__panel">
                    <div class="pw-table-wizard__panel-head mb-3">
                        <div>
                            <h6 class="mb-1"><i class="mdi mdi-eye-check-outline text-primary"></i> Table preview</h6>
                            <p class="text-muted small mb-0">Review how this table will appear. Save when you are satisfied.</p>
                        </div>
                    </div>

                    @if(count($stepTableColumns) > 0)
                    <div class="pw-table-shell pw-table-wizard__preview-wrap">
                        <table class="table table-sm mb-0 pw-modern-table pw-table-wizard__preview-table">
                            <thead>
                                <tr>
                                    <th style="width: 56px;">#</th>
                                    @foreach($stepTableColumns as $col)
                                    <th>
                                        <span class="d-block">{{ $col['label'] }}</span>
                                        <span class="pw-col-type-badge pw-col-type-badge--xs {{ $this->stepTableColumnIsStaticConfigurable($col) ? 'pw-col-type-badge--static' : 'pw-col-type-badge--capture' }}">
                                            <i class="mdi {{ $this->stepTableColumnDisplayTypeIcon($col) }}"></i>
                                            {{ $this->stepTableColumnDisplayTypeLabel($col) }}
                                        </span>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stepStaticRows as $staticRow)
                                <tr>
                                    <td class="text-muted">{{ $staticRow['order'] }}</td>
                                    @foreach($stepTableColumns as $col)
                                    @php
                                        $previewVal = $stepStaticCellValues[$staticRow['id']][$col['id']] ?? '';
                                        $isStatic = $this->stepTableColumnIsStaticConfigurable($col);
                                    @endphp
                                    <td>
                                        @if($isStatic)
                                            {{ $previewVal !== '' ? $previewVal : '—' }}
                                        @else
                                            <span class="pw-preview-capture-placeholder">
                                                <i class="mdi mdi-account-edit-outline"></i>
                                                Filled at capture
                                            </span>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ count($stepTableColumns) + 1 }}" class="text-muted text-center py-4">
                                        <i class="mdi mdi-table-off d-block mb-2" style="font-size: 1.5rem;"></i>
                                        No rows — the table will show only column headers until rows are added.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pw-table-wizard__summary mt-3">
                        <div class="pw-table-wizard__summary-item">
                            <i class="mdi mdi-table-column"></i>
                            <span><strong>{{ count($stepTableColumns) }}</strong> column{{ count($stepTableColumns) === 1 ? '' : 's' }}</span>
                        </div>
                        <div class="pw-table-wizard__summary-item">
                            <i class="mdi mdi-table-row"></i>
                            <span><strong>{{ count($stepStaticRows) }}</strong> row{{ count($stepStaticRows) === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                    @endif
                </section>
                @endif
            </div>

            <div class="modal-footer pw-modal-footer pw-table-wizard__footer">
                <button type="button" class="btn btn-light" wire:click="closeConfigureTableModal">Cancel</button>
                <div class="ml-auto d-flex align-items-center">
                    @if($configureTableWizardStep > 1)
                    <button type="button" class="btn btn-outline-secondary mr-2" wire:click="configureTableWizardBack">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </button>
                    @endif
                    @if($configureTableWizardStep < 3)
                    <button type="button" class="btn btn-primary" wire:click="configureTableWizardNext" @disabled($configureTableWizardStep === 1 && count($stepTableColumns) === 0)>
                        Next <i class="mdi mdi-arrow-right"></i>
                    </button>
                    @else
                    <button type="button" class="btn btn-primary" wire:click="closeConfigureTableModal">
                        <i class="mdi mdi-content-save-outline"></i> Save & close
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
