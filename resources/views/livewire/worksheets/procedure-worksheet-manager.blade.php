<div>
    <div wire:loading wire:target="activeTab, selectedWorksheetId, save">
        <div class="d-flex justify-content-center align-items-center" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.7); z-index: 9999;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    @if($this->paramsWithWorksheets->isEmpty())
        <div class="alert alert-info">
            <i class="mdi mdi-information"></i> No procedure worksheets found for the samples in this batch.
        </div>
    @else
        <div class="row">
            <div class="col-md-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    @foreach($this->paramsWithWorksheets as $param)
                        <a class="nav-link {{ $activeTab == $param->id ? 'active' : '' }}" 
                           wire:click="$set('activeTab', {{ $param->id }})"
                           href="#param-{{ $param->id }}" 
                           role="tab">
                            {{ $param->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            
            <div class="col-md-9">
                @if($activeTab)
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-light">
                            <ul class="nav nav-tabs card-header-tabs">
                                @foreach($this->worksheetsForParam as $worksheet)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $selectedWorksheetId == $worksheet->id ? 'active' : '' }}" 
                                           href="#" 
                                           wire:click.prevent="selectWorksheet({{ $worksheet->id }})">
                                            {{ $worksheet->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body">
                            @if($selectedWorksheetId)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Select Samples</h5>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                wire:click="toggleExternalPanel">
                                            <i class="mdi mdi-plus"></i> Add Samples from Other Batches
                                        </button>
                                    </div>

                                    @if($showExternalPanel)
                                        <div class="card mt-3">
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
                                                    <button type="button"
                                                            class="btn btn-sm btn-primary"
                                                            wire:click="addSelectedExternalSamples"
                                                            @if(empty($externalSelectionItems)) disabled @endif>
                                                        <i class="mdi mdi-plus-circle-outline"></i>
                                                        Add Selected
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        @foreach($this->analysisSamples as $result)
                                            @if($result->sample)
                                                <div class="form-check mr-3">
                                                    <input class="form-check-input" type="checkbox" value="{{ $result->sample->id }}" id="sample-{{ $result->sample->id }}" wire:model="selectedSamples">
                                                    <label class="form-check-label" for="sample-{{ $result->sample->id }}">
                                                        {{ $result->sample->sample_code }}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <hr>

                                @if(empty($selectedSamples))
                                    <div class="alert alert-warning">Please select at least one sample to enter data.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Parameter / Step</th>
                                                    @foreach($this->getStepsProperty() as $step)
                                                        <th>
                                                            <span class="font-weight-bold">{{ $step->step }}</span>@if($step->measurands->isNotEmpty()) <span class="font-weight-normal text-dark">({{ $step->measurands->pluck('name')->implode(', ') }})</span>@endif
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($this->analysisSamples as $result)
                                                    @if($result->sample && in_array($result->sample->id, $selectedSamples))
                                                        <tr>
                                                            <td class="font-weight-bold">{{ $result->sample->sample_code }}</td>
                                                            @foreach($this->getStepsProperty() as $step)
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           wire:model.defer="inputValues.{{ $result->id }}.{{ $step->id }}">
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    @if($configFields->count() > 0)
                                        <div class="card mt-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">
                                                    <i class="mdi mdi-cog-outline text-primary"></i>
                                                    Configurable Fields
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($configFields as $field)
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">
                                                                {{ $field->label }}
                                                                @if($field->is_required)
                                                                    <span class="text-danger">*</span>
                                                                @endif
                                                            </label>
                                                            @if($field->help_text)
                                                                <small class="text-muted d-block">{{ $field->help_text }}</small>
                                                            @endif
                                                            @foreach($this->analysisSamples as $result)
                                                                @if(in_array($result->sample->id, $selectedSamples))
                                                                    <div class="mb-1">
                                                                        <small class="text-muted">{{ $result->sample->sample_code }}</small>
                                                                        @php($type = $field->field_type)
                                                                        @if($type === 'datetime')
                                                                            <input type="datetime-local"
                                                                                class="form-control form-control-sm"
                                                                                wire:model.defer="configFieldValues.{{ $result->id }}.{{ $field->id }}">
                                                                        @elseif($type === 'date')
                                                                            <input type="date"
                                                                                class="form-control form-control-sm"
                                                                                wire:model.defer="configFieldValues.{{ $result->id }}.{{ $field->id }}">
                                                                        @elseif($type === 'number')
                                                                            <input type="number"
                                                                                class="form-control form-control-sm"
                                                                                wire:model.defer="configFieldValues.{{ $result->id }}.{{ $field->id }}">
                                                                        @elseif($type === 'checkbox')
                                                                            <div class="form-check">
                                                                                <input type="checkbox"
                                                                                       class="form-check-input"
                                                                                       wire:model.defer="configFieldValues.{{ $result->id }}.{{ $field->id }}"
                                                                                       value="1">
                                                                            </div>
                                                                        @elseif($type === 'textarea')
                                                                            <textarea
                                                                                class="form-control form-control-sm"
                                                                                rows="2"
                                                                                wire:model.defer="configFieldValues.{{ $result->id }}.{{ $field->id }}"></textarea>
                                                                        @else
                                                                            <input type="text"
                                                                                class="form-control form-control-sm"
                                                                                wire:model.defer="configFieldValues.{{ $result->id }}.{{ $field->id }}">
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Temporarily disable Test Kit Table block to resolve Blade parse error --}}
                                    
                                    <div class="mt-3 text-right">
                                        <button class="btn btn-primary" wire:click="save">
                                            <i class="mdi mdi-content-save"></i> Save Worksheet
                                        </button>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-info">Select a worksheet to proceed.</div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">Select a parameter to view worksheets.</div>
                @endif
            </div>
        </div>
    @endif

    <style>
    .worksheet-external-select.tag-select-container { position: relative; cursor: text; }
    .worksheet-external-select .tag-select-input {
        display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
        min-height: 42px; padding: 6px 12px; background: #fff;
        border: 2px solid #e0e0e0; border-radius: 8px; transition: all 0.3s ease;
    }
    .worksheet-external-select .tag-select-input:hover { border-color: #007bff; }
    .worksheet-external-select .tag-select-input:focus-within {
        border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); outline: none;
    }
    .worksheet-external-select .tag-badge {
        display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
        background-color: #007bff; color: white; border-radius: 16px; font-size: 0.875rem; font-weight: 500; white-space: nowrap;
    }
    .worksheet-external-select .tag-badge i { cursor: pointer; font-size: 1rem; opacity: 0.8; }
    .worksheet-external-select .tag-badge i:hover { opacity: 1; }
    .worksheet-external-select .tag-input {
        flex: 1; min-width: 120px; border: none; outline: none; padding: 4px; font-size: 0.9rem;
    }
    .worksheet-external-select .tag-dropdown {
        position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #007bff; border-top: none;
        border-radius: 0 0 8px 8px; max-height: 250px; overflow-y: auto; z-index: 1050;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-top: -2px;
    }
    .worksheet-external-select .tag-dropdown-item {
        padding: 10px 16px; cursor: pointer; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;
    }
    .worksheet-external-select .tag-dropdown-item:hover { background-color: #f8f9fa; }
    .worksheet-external-select .tag-dropdown-item:last-child { border-bottom: none; }
    </style>
</div>
