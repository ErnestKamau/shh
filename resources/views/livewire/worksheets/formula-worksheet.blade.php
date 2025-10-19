<div>
    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Formula Info -->
    <div class="alert alert-light border mb-4">
        <div class="d-flex align-items-center">
            <i class="mdi mdi-information mdi-24px text-primary mr-2"></i>
            <div>
                <strong>Formula:</strong> {{ $formula->name }}<br>
                <small class="text-muted">{{ $formula->description }}</small>
            </div>
        </div>
    </div>

    @if($capturedResults->count() > 0)
        <!-- Worksheet Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm" style="font-size: 0.9rem;">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 100px;">Date</th>
                        <th style="min-width: 120px;">Lab No</th>
                        <th style="min-width: 200px;">Sample Details</th>
                        <th style="min-width: 100px;">Time In</th>
                        <th style="min-width: 150px;">Done By</th>
                        
                        <!-- Formula Steps -->
                        @foreach($formulaSteps->where('step_type', 'input') as $step)
                            <th style="min-width: 120px;" class="bg-info text-white">
                                {{ $step->label }}
                                <small class="d-block">Input</small>
                            </th>
                        @endforeach

                        @foreach($formulaSteps->where('step_type', 'derived') as $step)
                            <th style="min-width: 120px;" class="bg-success text-white">
                                {{ $step->label }}
                                <small class="d-block">Derived</small>
                            </th>
                        @endforeach

                        @foreach($formulaSteps->where('step_type', 'dataset') as $step)
                            <th style="min-width: 120px;" class="bg-warning text-dark">
                                {{ $step->label }}
                                <small class="d-block">Dataset</small>
                            </th>
                        @endforeach

                        @foreach($formulaSteps->where('step_type', 'lookup') as $step)
                            <th style="min-width: 120px;" class="bg-secondary text-white">
                                {{ $step->label }}
                                <small class="d-block">Lookup</small>
                            </th>
                        @endforeach
                        
                        <th style="min-width: 120px;" class="bg-primary text-white">Final Result</th>
                        <th style="min-width: 100px;">Time Out</th>
                        <th style="min-width: 150px;">Read By</th>
                        <th style="min-width: 100px;">Read Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($capturedResults as $captured)
                        @php
                            $wsData = $worksheetData[$captured->id] ?? [];
                        @endphp
                        <tr wire:key="row-{{ $captured->id }}">
                            <td>
                                <input type="date" 
                                       class="form-control form-control-sm" 
                                       wire:model="worksheetData.{{ $captured->id }}.date"
                                       wire:blur="autoSaveRow({{ $captured->id }})"
                                       value="{{ $wsData['date'] ?? now()->format('Y-m-d') }}">
                            </td>
                            <td>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       value="{{ $batch->batch_code }}" 
                                       readonly>
                            </td>
                            <td>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       value="{{ $captured->sample->sample_code }} - {{ $captured->analysisElement->analyte->name ?? '' }} - {{ $captured->sample->sample_point->name ?? '' }}" 
                                       readonly>
                            </td>
                            <td>
                                <input type="time" 
                                       class="form-control form-control-sm" 
                                       wire:model="worksheetData.{{ $captured->id }}.time_in"
                                       wire:blur="autoSaveRow({{ $captured->id }})">
                            </td>
                            <td>
                                <select class="form-control form-control-sm" 
                                        wire:model="worksheetData.{{ $captured->id }}.done_by_user_id"
                                        wire:blur="autoSaveRow({{ $captured->id }})">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Input Steps -->
                            @foreach($formulaSteps->where('step_type', 'input') as $step)
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model.live="worksheetData.{{ $captured->id }}.steps.{{ $step->id }}"
                                           wire:blur="autoSaveRow({{ $captured->id }})"
                                           placeholder="Enter value">
                                </td>
                            @endforeach

                            <!-- Derived Steps (calculated, readonly) -->
                            @foreach($formulaSteps->where('step_type', 'derived') as $step)
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm bg-light" 
                                           value="{{ $wsData['steps'][$step->id] ?? '' }}"
                                           readonly
                                           placeholder="Calculated">
                                </td>
                            @endforeach

                            <!-- Dataset Steps -->
                            @foreach($formulaSteps->where('step_type', 'dataset') as $step)
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="worksheetData.{{ $captured->id }}.steps.{{ $step->id }}"
                                           wire:blur="autoSaveRow({{ $captured->id }})"
                                           placeholder="Enter value">
                                </td>
                            @endforeach

                            <!-- Lookup Steps -->
                            @foreach($formulaSteps->where('step_type', 'lookup') as $step)
                                <td>
                                    <div class="d-flex align-items-center">
                                        <input type="text" 
                                               class="form-control form-control-sm bg-light flex-grow-1" 
                                               value="{{ $wsData['steps'][$step->id] ?? '' }}"
                                               readonly
                                               placeholder="Lookup">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary ml-1" 
                                                wire:click="openChangeLookupModal({{ $captured->id }}, {{ $step->id }})"
                                                title="Change Lookup Table">
                                            @if(isset($wsData['lookup_overrides'][$step->id]))
                                                <i class="mdi mdi-swap-horizontal text-warning"></i>
                                            @else
                                                <i class="mdi mdi-swap-horizontal"></i>
                                            @endif
                                        </button>
                                    </div>
                                    @if(isset($wsData['lookup_overrides'][$step->id]))
                                        @php
                                            $currentLookup = $this->getCurrentLookupTable($captured->id, $step->id);
                                        @endphp
                                        @if($currentLookup)
                                            <small class="text-warning">
                                                <i class="mdi mdi-alert-circle"></i> Using: {{ $currentLookup->name }}
                                            </small>
                                        @endif
                                    @endif
                                </td>
                            @endforeach

                            <!-- Final Result -->
                            <td>
                                <input type="text" 
                                       class="form-control form-control-sm font-weight-bold" 
                                       value="{{ $wsData['final_result'] ?? '' }}"
                                       readonly
                                       placeholder="Result">
                            </td>

                            <!-- Time Out -->
                            <td>
                                <input type="time" 
                                       class="form-control form-control-sm" 
                                       wire:model="worksheetData.{{ $captured->id }}.time_out"
                                       wire:blur="autoSaveRow({{ $captured->id }})">
                            </td>

                            <!-- Read By -->
                            <td>
                                <select class="form-control form-control-sm" 
                                        wire:model="worksheetData.{{ $captured->id }}.read_by_user_id"
                                        wire:blur="autoSaveRow({{ $captured->id }})">
                                    <option value="">Select...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Read Date -->
                            <td>
                                <input type="date" 
                                       class="form-control form-control-sm" 
                                       wire:model="worksheetData.{{ $captured->id }}.read_date"
                                       wire:blur="autoSaveRow({{ $captured->id }})">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mandatory Fields Section -->
        @if($mandatoryFields->count() > 0)
            <div class="card border mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="mdi mdi-asterisk text-danger"></i> Mandatory Fields (Applies to All Samples)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($mandatoryFields as $field)
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
                                
                                @if($field->field_type === 'datetime')
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           wire:model="sharedMandatoryData.{{ $field->id }}"
                                           placeholder="{{ $field->label }}">
                                @elseif($field->field_type === 'date')
                                    <input type="date" 
                                           class="form-control" 
                                           wire:model="sharedMandatoryData.{{ $field->id }}"
                                           placeholder="{{ $field->label }}">
                                @elseif($field->field_type === 'dataset_related')
                                    <select class="form-control" 
                                            wire:model="sharedMandatoryData.{{ $field->id }}">
                                        <option value="">Select...</option>
                                        @foreach($this->getDatasetOptions($field->model_tied_to) as $option)
                                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" 
                                           class="form-control" 
                                           wire:model="sharedMandatoryData.{{ $field->id }}"
                                           placeholder="{{ $field->label }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="alert alert-info">
            <i class="mdi mdi-information"></i> 
            No captured results found for formula "{{ $formula->name }}" in this batch.
        </div>
    @endif

    <!-- Lookup Table Change Modal -->
    @if($showLookupTableModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-swap-horizontal"></i> Change Lookup Table
                            @if($selectedLookupStepId)
                                @php
                                    $selectedStep = $formulaSteps->firstWhere('id', $selectedLookupStepId);
                                @endphp
                                @if($selectedStep)
                                    - {{ $selectedStep->label }}
                                @endif
                            @endif
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeLookupModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Warning -->
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i>
                            <strong>Note:</strong> This change only affects this worksheet. The formula configuration will not be changed.
                        </div>

                        <!-- Original Lookup Table -->
                        @if($selectedLookupStepId)
                            @php
                                $originalLookup = $this->getOriginalLookupTable($selectedLookupStepId);
                            @endphp
                            @if($originalLookup)
                                <div class="mb-3">
                                    <label class="font-weight-bold">Formula Default:</label>
                                    <div class="alert alert-light border">
                                        {{ $originalLookup->name }}
                                        <small class="d-block text-muted">{{ $originalLookup->description }}</small>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Compatible Lookup Tables Selection -->
                        <div class="mb-3">
                            <label class="font-weight-bold">Select Replacement Lookup Table:</label>
                            @if(count($compatibleLookupTables) > 0)
                                <div class="list-group">
                                    @foreach($compatibleLookupTables as $lookupTable)
                                        <label class="list-group-item list-group-item-action cursor-pointer">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" 
                                                       name="replacement_lookup_table" 
                                                       wire:model="selectedReplacementLookupTableId" 
                                                       value="{{ $lookupTable['id'] }}"
                                                       class="mr-2">
                                                <div class="flex-grow-1">
                                                    <strong>{{ $lookupTable['name'] }}</strong>
                                                    @if($lookupTable['description'])
                                                        <small class="d-block text-muted">{{ $lookupTable['description'] }}</small>
                                                    @endif
                                                    <small class="badge badge-info">
                                                        {{ $lookupTable['lookup_type'] === 'range_based' ? 'Range-Based' : 'Key-Value' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i>
                                    No compatible lookup tables found.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLookupModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        @if($currentCapturedResultId && $selectedLookupStepId && isset($worksheetData[$currentCapturedResultId]['lookup_overrides'][$selectedLookupStepId]))
                            <button type="button" class="btn btn-warning" wire:click="resetLookupTable">
                                <i class="mdi mdi-refresh"></i> Reset to Default
                            </button>
                        @endif
                        <button type="button" 
                                class="btn btn-primary" 
                                wire:click="changeLookupTable"
                                @if(count($compatibleLookupTables) == 0) disabled @endif>
                            <i class="mdi mdi-check"></i> Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
