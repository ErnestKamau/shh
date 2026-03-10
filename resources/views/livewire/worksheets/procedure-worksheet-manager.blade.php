<div class="procedure-worksheet-manager">
    <div wire:loading wire:target="activeTab, selectedWorksheetId, save">
        <div class="d-flex justify-content-center align-items-center" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.7); z-index: 9999;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    @if($flashMessage)
    <div class="alert 
        @if($flashType === 'error') alert-danger 
        @elseif($flashType === 'warning') alert-warning 
        @else alert-success @endif mb-3">
        <i class="mdi 
            @if($flashType === 'error') mdi-alert-circle-outline 
            @elseif($flashType === 'warning') mdi-alert-outline 
            @else mdi-check-circle-outline @endif"></i>
        {{ $flashMessage }}
    </div>
    @endif

    @if($this->paramsWithWorksheets->isEmpty())
    <div class="alert alert-info">
        <i class="mdi mdi-information"></i> No procedure worksheets found for the samples in this batch.
    </div>
    @else
    <div class="col-12">
        @if($activeTab && $this->selectedWorksheet)
        <div class="alert alert-light border mb-4 procedure-info-banner">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-file-document-outline mdi-24px text-primary mr-3"></i>
                    <div>
                        <strong>Procedure Worksheet:</strong> {{ $this->selectedWorksheet->name }}<br>
                        <small class="text-muted">{{ $this->selectedWorksheet->description ?: 'Enter procedure data for the selected parameter and samples.' }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-secondary">{{ $this->paramsWithWorksheets->count() }} parameter(s)</span>
                </div>
            </div>
        </div>
        @endif

        <div class="card procedure-worksheet-card mb-0">
            {{-- Tabs: one per parameter; worksheet from element below --}}
            <div class="procedure-selector-bar">
                <ul class="nav nav-tabs procedure-tabs border-0" role="tablist">
                    @foreach($this->paramsWithWorksheets as $param)
                    <li class="nav-item" role="presentation">
                        <button type="button"
                            class="nav-link procedure-tab {{ $activeTab == $param->id ? 'active' : '' }}"
                            wire:click="$set('activeTab', {{ $param->id }})"
                            role="tab">
                            {{ $param->name }}
                        </button>
                    </li>
                    @endforeach
                </ul>
                @if($activeTab)
                <div class="procedure-tab-worksheet">
                    @if($this->worksheetsForParam->count() === 1)
                    <span class="procedure-worksheet-name">{{ $this->worksheetsForParam->first()->name }}</span>
                    @else
                    <select class="form-select form-select-sm procedure-worksheet-select"
                        wire:model.live="selectedWorksheetId">
                        @foreach($this->worksheetsForParam as $worksheet)
                        <option value="{{ $worksheet->id }}">{{ $worksheet->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
                @endif
            </div>

            <div class="card-body p-4">
                @if($activeTab)
                @if($selectedWorksheetId)
                <section class="procedure-section mb-4">
                    <div class="card procedure-section-card">
                        <div class="card-header procedure-section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0">
                                <i class="mdi mdi-flask-outline text-primary"></i>
                                Select Samples
                            </h6>
                            <button type="button" class="btn btn-sm btn-primary" wire:click="toggleExternalPanel">
                                <i class="mdi mdi-plus"></i> Add Samples from Other Batches
                            </button>
                        </div>
                        <div class="card-body">
                            @if($showExternalPanel)
                            <div class="card mb-3 procedure-inner-card">
                                <div class="card-body p-3">
                                    <label class="form-label small text-muted fw-bold">Add samples from other batches</label>
                                    <p class="text-muted small mb-2">Search and select samples, then click Add Selected.</p>

                                    <div class="tag-select-container worksheet-external-select"
                                        wire:click="$set('showExternalDropdown', true)"
                                        wire:click.outside="$set('showExternalDropdown', false)">
                                        <div class="tag-select-input">
                                            @foreach($externalSelectionItems as $item)
                                            <span class="tag-badge">
                                                {{ $item['batch_code'] }} – {{ $item['sample_code'] }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="removeExternalSampleFromSelection({{ $item['id'] }})"></i>
                                            </span>
                                            @endforeach
                                            <input type="text"
                                                class="tag-input"
                                                wire:model.live.debounce.300ms="externalSearch"
                                                placeholder="{{ count($externalSelectionItems) > 0 ? '' : 'Search by batch or sample code...' }}"
                                                autocomplete="off">
                                        </div>
                                        @if($showExternalDropdown && count($externalSearchResults) > 0)
                                        <div class="tag-dropdown">
                                            @foreach($externalSearchResults as $row)
                                            <div class="tag-dropdown-item"
                                                wire:click.stop="addExternalSampleToSelection({{ $row['captured_result_id'] }})">
                                                <strong>{{ $row['batch_code'] }}</strong> – {{ $row['sample_code'] }}
                                            </div>
                                            @endforeach
                                        </div>
                                        @elseif($showExternalDropdown && $externalSearch !== '' && count($externalSearchResults) === 0)
                                        <div class="tag-dropdown">
                                            <div class="tag-dropdown-item text-muted">No matching samples</div>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="mt-3 d-flex align-items-center justify-content-end gap-2">
                                        @if(count($externalSelectionItems) > 0)
                                        <span class="text-muted small">{{ count($externalSelectionItems) }} selected</span>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-primary" wire:click="addSelectedExternalSamples"
                                            @if(empty($externalSelectionItems)) disabled @endif>
                                            <i class="mdi mdi-plus-circle-outline"></i> Add Selected
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <h6 class="mb-2 small">Samples in this batch:</h6>
                            <div class="border rounded p-3 procedure-samples-box">
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($this->analysisSamples as $result)
                                    @if($result->sample)
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" value="{{ $result->sample->id }}" id="sample-{{ $result->sample->id }}" wire:model.live="selectedSamples">
                                        <label class="form-check-label" for="sample-{{ $result->sample->id }}">{{ $result->sample->sample_code }}</label>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                </section>

                @if($this->selectedWorksheet && ($this->selectedWorksheet->document_control_no || $this->selectedWorksheet->revision || $this->selectedWorksheet->issue_date))
                <div class="alert alert-light border mb-4">
                    <h6 class="mb-2 text-muted small">
                        <i class="mdi mdi-file-document-outline"></i> Document Control
                    </h6>
                    <div class="d-flex flex-wrap gap-4 small">
                        @if($this->selectedWorksheet->document_control_no)
                        <span><strong>Doc. No:</strong> {{ $this->selectedWorksheet->document_control_no }}</span>
                        @endif
                        @if($this->selectedWorksheet->revision)
                        <span><strong>Revision:</strong> {{ $this->selectedWorksheet->revision }}</span>
                        @endif
                        @if($this->selectedWorksheet->issue_date)
                        <span><strong>Issue Date:</strong> {{ $this->selectedWorksheet->issue_date->format('Y-m-d') }}</span>
                        @endif
                    </div>
                </div>
                @endif

                @if(empty($selectedSamples))
                <div class="alert alert-warning">Please select at least one sample to enter data.</div>
                @else
                @php
                $selectedResults = $this->analysisSamples->filter(fn($r) => $r->sample && in_array($r->sample->id, $selectedSamples))->values();
                @endphp
                <section class="procedure-section mb-4">
                    <div class="card procedure-section-card">
                        <div class="card-header procedure-section-header">
                            <h6 class="mb-0">
                                <i class="mdi mdi-timeline text-primary"></i>
                                Steps &amp; Measurands
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive procedure-steps-table-wrap">
                                <table class="table table-bordered procedure-steps-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="step-header-cell">Step</th>
                                            <th class="step-header-cell">Measurand</th>
                                            <th class="step-header-cell">Equipment</th>
                                            <th class="step-header-cell">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->getStepsProperty() as $step)
                                        <tr>
                                            <td class="step-info-cell">{{ $step->step }}</td>
                                            <td class="step-info-cell">{{ $step->measurands->pluck('name')->implode(', ') ?: '—' }}</td>
                                            <td class="step-info-cell">{{ $step->equipment?->name ?? '—' }}</td>
                                            <td>
                                                <input type="text" class="form-control"
                                                    wire:model.defer="inputValues.{{ $selectedResults->first()->id }}.{{ $step->id }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                @if($this->getTestKitColumnsProperty()->isNotEmpty())
                <section class="procedure-section mb-4">
                    <div class="card procedure-section-card">
                        <div class="card-header procedure-section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0">
                                <i class="mdi mdi-table text-primary"></i>
                                Test Kit
                            </h6>
                            <button type="button" class="btn btn-sm btn-primary" wire:click="addTestKitRow">
                                <i class="mdi mdi-plus"></i> Add row
                            </button>
                        </div>
                        <div class="card-body p-0">
                            @if(!empty($this->getOrderedTestKitRowsProperty()))
                            <div class="table-responsive">
                                <table class="table table-bordered procedure-testkit-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="testkit-row-header">#</th>
                                            @foreach($this->getTestKitColumnsProperty() as $col)
                                            <th>{{ $col->label }}</th>
                                            @endforeach
                                            <th class="testkit-actions-header" style="width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->getOrderedTestKitRowsProperty() as $rowMeta)
                                        <tr>
                                            <td class="testkit-row-index">{{ $rowMeta['row_index'] }}</td>
                                            @foreach($this->getTestKitColumnsProperty() as $col)
                                            <td>
                                                @php($type = $col->type ?? 'string')
                                                @if($type === 'number')
                                                <input type="number" class="form-control form-control-sm"
                                                    wire:model.defer="testKitData.{{ $rowMeta['id'] }}.{{ $col->id }}">
                                                @elseif($type === 'date')
                                                <input type="date" class="form-control form-control-sm"
                                                    wire:model.defer="testKitData.{{ $rowMeta['id'] }}.{{ $col->id }}">
                                                @elseif($type === 'boolean')
                                                <div class="form-check form-check-inline mb-0">
                                                    <input type="checkbox" class="form-check-input"
                                                        wire:model.defer="testKitData.{{ $rowMeta['id'] }}.{{ $col->id }}"
                                                        value="1">
                                                </div>
                                                @else
                                                <input type="text" class="form-control form-control-sm"
                                                    wire:model.defer="testKitData.{{ $rowMeta['id'] }}.{{ $col->id }}">
                                                @endif
                                            </td>
                                            @endforeach
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                                    wire:click="removeTestKitRow({{ $rowMeta['id'] }})"
                                                    title="Remove row">
                                                    <i class="mdi mdi-delete-outline"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-table-large"></i>
                                <p class="mb-2 small">No rows yet. Click "Add row" to add data.</p>
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addTestKitRow">
                                    <i class="mdi mdi-plus"></i> Add row
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </section>
                @endif

                @if($configFields->count() > 0)
                <section class="procedure-section mb-4">
                    <div class="card procedure-section-card">
                        <div class="card-header procedure-section-header">
                            <h6 class="mb-0">
                                <i class="mdi mdi-cog-outline text-primary"></i>
                                Configurable Fields
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                        @foreach($configFields as $field)
                                <div class="col-xl-4 col-lg-6 mb-0">
                                    <div class="config-field-block">
                                        <label class="form-label fw-medium">
                                            {{ $field->label }}
                                            @if($field->is_required)
                                            <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        @if($field->help_text)
                                        <small class="text-muted d-block mb-2">{{ $field->help_text }}</small>
                                        @endif
                                        @if($selectedResults->isNotEmpty())
                                        <div class="config-field-row mb-3">
                                            @if($selectedResults->count() > 1)
                                            <small class="text-muted d-block mb-1">Same value for {{ $selectedResults->count() }} selected samples</small>
                                            @endif
                                            @php(
                                                $type = $field->field_type ?: (
                                                    in_array($field->model_tied_to ?? '', ['users','sample_details','sample_types','methods','captured_results','report_formats'])
                                                    ? 'dataset'
                                                    : 'input'
                                                )
                                            )
                                            @if($type === 'datetime')
                                            <input type="datetime-local"
                                                class="form-control"
                                                wire:model.defer="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}">
                                            @elseif($type === 'date')
                                            <input type="date"
                                                class="form-control"
                                                wire:model.defer="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}">
                                            @elseif($type === 'number')
                                            <input type="number"
                                                class="form-control"
                                                wire:model.defer="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}">
                                            @elseif($type === 'checkbox')
                                            <div class="form-check">
                                                <input type="checkbox"
                                                    class="form-check-input"
                                                    wire:model.defer="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"
                                                    value="1">
                                            </div>
                                            @elseif($type === 'textarea')
                                            <textarea
                                                class="form-control"
                                                rows="2"
                                                wire:model.defer="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}"></textarea>
                                            @elseif($type === 'input' || $type === '' || $type === null)
                                            <input type="text"
                                                class="form-control"
                                                wire:model.defer="configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}">
                                            @elseif($type === 'dataset')
                                            <div wire:ignore x-data="{
                                                init() {
                                                    let el = $(this.$refs.select);
                                                    el.select2({
                                                        width: '100%',
                                                        placeholder: 'Select...',
                                                        allowClear: true
                                                    }).on('change', () => {
                                                        @this.set('configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}', el.val());
                                                    });
                                                }
                                            }">
                                                <select x-ref="select" class="form-control" data-placeholder="Select...">
                                                    <option value="">Select...</option>
                                                    @foreach($this->getDatasetOptions($field->model_tied_to ?? '', $field) as $option)
                                                    <option value="{{ $option->id }}" @if(isset($configFieldValues[$selectedResults->first()->id][$field->id]) && $configFieldValues[$selectedResults->first()->id][$field->id] == $option->id) selected @endif>{{ $option->label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @elseif($type === 'dataset_multiselect')
                                            <div wire:ignore x-data="{
                                                init() {
                                                    let el = $(this.$refs.select);
                                                    el.select2({
                                                        width: '100%',
                                                        placeholder: 'Select...',
                                                        allowClear: true
                                                    }).on('change', () => {
                                                        @this.set('configFieldValues.{{ $selectedResults->first()->id }}.{{ $field->id }}', el.val() || []);
                                                    });
                                                }
                                            }">
                                                <select x-ref="select" class="form-control" multiple="multiple" data-placeholder="Select...">
                                                    @foreach($this->getDatasetOptions($field->model_tied_to ?? '', $field) as $option)
                                                    <option value="{{ $option->id }}" @if(isset($configFieldValues[$selectedResults->first()->id][$field->id]) && is_array($configFieldValues[$selectedResults->first()->id][$field->id]) && in_array($option->id, $configFieldValues[$selectedResults->first()->id][$field->id])) selected @endif>{{ $option->label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
                @endif

                <div class="mt-4 pt-3 border-top procedure-save-footer">
                    <button class="btn btn-primary" wire:click="save">
                        <i class="mdi mdi-content-save"></i> Save Worksheet
                    </button>
                </div>
                @endif
                @else
                <div class="alert alert-info mb-0">Select a worksheet to proceed.</div>
                @endif
                @else
                <div class="alert alert-info mb-0">Select a parameter to view worksheets.</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <style>
        .procedure-worksheet-manager {
            padding: 0 0 1.5rem 0;
        }

        /* Method-sequence style: main card */
        .procedure-worksheet-card {
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .procedure-worksheet-card:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .procedure-info-banner {
            border-radius: 12px;
        }

        .procedure-section-card {
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .procedure-section-card:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .procedure-section-header {
            background: #f8f9fa !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 16px 20px !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .procedure-inner-card {
            border-radius: 12px !important;
            border: 1px solid #e5e7eb !important;
        }

        .procedure-samples-box {
            background: #f9fafb;
            border-color: #e5e7eb !important;
        }

        .procedure-save-footer {
            border-color: #e5e7eb !important;
            text-align: right;
        }

        .procedure-selector-bar {
            padding: 0;
            background: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        .procedure-tabs {
            padding: 0.75rem 1.25rem 0 1.25rem;
            gap: 0;
            min-height: auto;
        }

        .procedure-tabs .nav-item {
            margin-bottom: 0;
        }

        .procedure-tab {
            padding: 0.6rem 1.15rem;
            font-size: 0.9rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            background: rgba(0, 0, 0, 0.04);
            color: var(--bs-secondary);
            margin-right: 0.25rem;
            transition: color .15s, background .15s, border-color .15s;
        }

        .procedure-tab:hover {
            color: var(--bs-body-color);
            background: rgba(0, 0, 0, 0.07);
        }

        .procedure-tab.active {
            color: var(--bs-primary);
            background: rgba(13, 110, 253, 0.06);
            font-weight: 500;
            border-left: 3px solid var(--bs-primary);
            border-right: 3px solid var(--bs-primary);
            border-top-color: transparent;
            border-bottom-color: transparent;
        }

        .procedure-tab-worksheet {
            display: flex;
            align-items: center;
            gap: 0.5rem 0.75rem;
            flex-wrap: wrap;
            padding: 0.4rem 1.25rem 0.65rem;
        }

        .procedure-worksheet-name {
            font-size: 0.8rem;
            color: var(--bs-secondary);
            letter-spacing: 0.02em;
        }

        .procedure-worksheet-select {
            max-width: 280px;
            font-size: 0.8rem;
            padding: 0.25rem 2rem 0.25rem 0.5rem;
        }

        .procedure-selector-bar .form-label {
            font-size: 0.7rem;
            letter-spacing: 0.05em;
        }

        .procedure-steps-table-wrap {
            margin-bottom: 0;
        }

        .procedure-steps-table {
            margin-bottom: 0;
            border-color: #e5e7eb;
        }

        .procedure-steps-table th,
        .procedure-steps-table td {
            padding: 0.65rem 0.85rem;
            vertical-align: middle;
        }

        .procedure-steps-table .step-header-cell,
        .procedure-steps-table .step-info-cell {
            min-width: 120px;
            background-color: rgba(0, 0, 0, 0.02);
        }

        .procedure-steps-table .step-header-cell {
            font-weight: 600;
        }

        .procedure-steps-table input.form-control {
            min-height: 38px;
        }

        .procedure-testkit-table {
            border-color: #e5e7eb;
        }

        .procedure-testkit-table th,
        .procedure-testkit-table td {
            padding: 0.5rem 0.65rem;
            vertical-align: middle;
        }

        .procedure-testkit-table .testkit-row-header,
        .procedure-testkit-table .testkit-row-index {
            width: 48px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.02);
        }

        .procedure-testkit-table .testkit-actions-header {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .config-field-block {
            padding: 0.25rem 0;
        }

        .config-field-row:last-child {
            margin-bottom: 0 !important;
        }

        .worksheet-external-select.tag-select-container {
            position: relative;
            cursor: text;
        }

        .worksheet-external-select .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .worksheet-external-select .tag-select-input:hover {
            border-color: #007bff;
        }

        .worksheet-external-select .tag-select-input:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .worksheet-external-select .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background-color: #007bff;
            color: white;
            border-radius: 16px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .worksheet-external-select .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.8;
        }

        .worksheet-external-select .tag-badge i:hover {
            opacity: 1;
        }

        .worksheet-external-select .tag-input {
            flex: 1;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 4px;
            font-size: 0.9rem;
        }

        .worksheet-external-select .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #007bff;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1050;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            margin-top: -2px;
        }

        .worksheet-external-select .tag-dropdown-item {
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .worksheet-external-select .tag-dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .worksheet-external-select .tag-dropdown-item:last-child {
            border-bottom: none;
        }
    </style>
</div>