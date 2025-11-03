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
        <div class="d-flex align-items-start">
            <i class="mdi mdi-information mdi-24px text-primary mr-2"></i>
            <div class="flex-grow-1">
                <strong>Formula:</strong> {{ $formula->name }}<br>
                <small class="text-muted">{{ $formula->description }}</small>
                
                @if($this->getUsedLookupTables()->count() > 0)
                    <div class="mt-3 pt-3 border-top">
                        <strong class="text-info">
                            <i class="mdi mdi-table-search"></i> Lookup Tables Used:
                        </strong>
                        <div class="mt-2">
                            @foreach($this->getUsedLookupTables() as $lookupTable)
                                <div class="d-inline-flex align-items-center mr-3 mb-2">
                                    <span class="badge badge-light border p-2">
                                        {{ $lookupTable->name }}
                                    </span>
                                    <a href="{{ route('formulars.lookup-table-entries', $lookupTable) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-link text-primary ml-1"
                                       title="View lookup table entries">
                                        <i class="mdi mdi-arrow-expand"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Posted Results Status Badge -->
    @php
        $worksheet = $this->getWorksheetWithPostingInfo();
    @endphp
    @if($worksheet && $worksheet->posted_at)
        <div class="alert alert-success border mb-4">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-circle mdi-24px text-success mr-2"></i>
                <div>
                    <strong>Results Posted</strong><br>
                    <small class="text-muted">
                        Posted on {{ $worksheet->posted_at->format('M d, Y H:i') }} 
                        by {{ $worksheet->postedBy->name ?? 'Unknown' }}
                    </small>
                </div>
            </div>
        </div>
    @endif

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
                                           wire:blur="autoSaveMandatoryField({{ $field->id }})"
                                           placeholder="{{ $field->label }}">
                                @elseif($field->field_type === 'date')
                                    <input type="date" 
                                           class="form-control" 
                                           wire:model="sharedMandatoryData.{{ $field->id }}"
                                           wire:blur="autoSaveMandatoryField({{ $field->id }})"
                                           placeholder="{{ $field->label }}">
                                @elseif($field->field_type === 'dataset_related')
                                    <select class="form-control" 
                                            wire:model="sharedMandatoryData.{{ $field->id }}"
                                            wire:blur="autoSaveMandatoryField({{ $field->id }})">
                                        <option value="">Select...</option>
                                        @foreach($this->getDatasetOptions($field->model_tied_to) as $option)
                                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" 
                                           class="form-control" 
                                           wire:model="sharedMandatoryData.{{ $field->id }}"
                                           wire:blur="autoSaveMandatoryField({{ $field->id }})"
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

    <!-- Post Results Confirmation Modal -->
    @if($showPostResultsModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-upload"></i> Confirm Standards & Post Results
                        </h5>
                        @if(!$postingInProgress)
                            <button type="button" class="close text-white" wire:click="closePostResultsModal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        @endif
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        @if(!$postingInProgress)
                            <!-- Standards Confirmation View -->
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i>
                                <strong>Confirm or update standards</strong> for each sample before posting results. 
                                Changes will update the sample and affect all future results.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Sample</th>
                                            <th>Analysis Type</th>
                                            <th style="min-width: 200px;">Main Standard</th>
                                            <th style="min-width: 200px;">Secondary Standard</th>
                                            @if($lookupStandardInfo)
                                                <th>Use Lookup</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($samplesWithStandards as $index => $sample)
                                            <tr>
                                                <td><strong>{{ $sample['sample_code'] }}</strong></td>
                                                <td><small>{{ $sample['analysis_types'] }}</small></td>
                                                
                                                <!-- Main Standard Searchable Dropdown -->
                                                <td>
                                                    <div class="tag-select-container" wire:click="toggleMainStandardDropdown({{ $sample['id'] }})">
                                                        <div class="tag-select-input">
                                                            @if($sample['main_standard'])
                                                                <span class="tag-badge">
                                                                    {{ $this->getSelectedStandardName($sample['main_standard']) }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="updateSampleStandard({{ $sample['id'] }}, 'main', null)"></i>
                                                                </span>
                                                            @endif
                                                            
                                                            <input type="text" 
                                                                   wire:model.live="mainStandardSearch.{{ $sample['id'] }}" 
                                                                   wire:keyup="searchMainStandards({{ $sample['id'] }})"
                                                                   class="tag-input" 
                                                                   placeholder="{{ $sample['main_standard'] ? '' : 'Not Set' }}"
                                                                   autocomplete="off">
                                                        </div>
                                                        
                                                        @if(isset($showMainStandardDropdown[$sample['id']]) && $showMainStandardDropdown[$sample['id']])
                                                            <div class="tag-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 300px; max-width: 400px;">
                                                                <div style="max-height: 200px; overflow-y: auto;">
                                                                    <div class="tag-dropdown-item" wire:click.stop="updateSampleStandard({{ $sample['id'] }}, 'main', null)">
                                                                        <span class="text-muted">Not Set</span>
                                                                    </div>
                                                                    @foreach($filteredMainStandards[$sample['id']] ?? $availableStandards as $standard)
                                                                        <div class="tag-dropdown-item" wire:click.stop="updateSampleStandard({{ $sample['id'] }}, 'main', {{ $standard->id }})">
                                                                            {{ $standard->name }} ({{ $standard->code }})
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                <!-- Secondary Standard Searchable Dropdown -->
                                                <td>
                                                    <div class="tag-select-container" wire:click="toggleSecondaryStandardDropdown({{ $sample['id'] }})">
                                                        <div class="tag-select-input">
                                                            @if($sample['secondary_standard'])
                                                                <span class="tag-badge">
                                                                    {{ $this->getSelectedStandardName($sample['secondary_standard']) }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="updateSampleStandard({{ $sample['id'] }}, 'secondary', null)"></i>
                                                                </span>
                                                            @endif
                                                            
                                                            <input type="text" 
                                                                   wire:model.live="secondaryStandardSearch.{{ $sample['id'] }}" 
                                                                   wire:keyup="searchSecondaryStandards({{ $sample['id'] }})"
                                                                   class="tag-input" 
                                                                   placeholder="{{ $sample['secondary_standard'] ? '' : 'Not Set' }}"
                                                                   autocomplete="off">
                                                        </div>
                                                        
                                                        @if(isset($showSecondaryStandardDropdown[$sample['id']]) && $showSecondaryStandardDropdown[$sample['id']])
                                                            <div class="tag-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 300px; max-width: 400px;">
                                                                <div style="max-height: 200px; overflow-y: auto;">
                                                                    <div class="tag-dropdown-item" wire:click.stop="updateSampleStandard({{ $sample['id'] }}, 'secondary', null)">
                                                                        <span class="text-muted">Not Set</span>
                                                                    </div>
                                                                    @foreach($filteredSecondaryStandards[$sample['id']] ?? $availableStandards as $standard)
                                                                        <div class="tag-dropdown-item" wire:click.stop="updateSampleStandard({{ $sample['id'] }}, 'secondary', {{ $standard->id }})">
                                                                            {{ $standard->name }} ({{ $standard->code }})
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                <!-- Use Lookup Table Checkbox -->
                                                @if($lookupStandardInfo)
                                                    <td class="text-center">
                                                        <div class="form-check d-inline-block">
                                                            <input type="checkbox" 
                                                                   class="form-check-input" 
                                                                   wire:model="useLookupAsStandard.{{ $sample['id'] }}"
                                                                   id="use-lookup-{{ $sample['id'] }}">
                                                            <label class="form-check-label" for="use-lookup-{{ $sample['id'] }}">
                                                                <small class="text-muted d-block">{{ $lookupStandardInfo['name'] }}</small>
                                                            </label>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="mdi mdi-alert"></i>
                                <strong>Note:</strong> 
                                <ul class="mb-0 mt-2">
                                    <li>Standard changes will update the sample and affect all future results</li>
                                    @if($lookupStandardInfo)
                                        <li>When "Use Lookup" is checked, the remark will come from the lookup table interpretation ({{ $lookupStandardInfo['name'] }}) instead of standard-based calculation</li>
                                    @endif
                                    <li>Posting will proceed with the standards shown above</li>
                                </ul>
                            </div>
                        @else
                            <!-- Progress Tracking View -->
                            <div class="text-center py-4">
                                <h5 class="mb-4">
                                    <i class="mdi mdi-loading mdi-spin text-primary"></i> 
                                    Posting Results...
                                </h5>
                                
                                <!-- Progress Bar -->
                                <div class="mb-4">
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                             role="progressbar" 
                                             style="width: {{ ($currentStep / $totalSteps) * 100 }}%"
                                             aria-valuenow="{{ $currentStep }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $totalSteps }}">
                                            Step {{ $currentStep }} of {{ $totalSteps }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Step Message -->
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-progress-clock mdi-24px text-primary mr-2"></i>
                                        <div>
                                            <strong>{{ $currentStepMessage }}</strong><br>
                                            @if($currentStep > 1)
                                                <small class="text-muted">
                                                    Processing {{ $processedCount }} of {{ $totalCount }} results...
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Step Indicators -->
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card {{ $currentStep >= 1 ? 'border-success' : 'border-secondary' }}">
                                            <div class="card-body text-center py-3">
                                                <i class="mdi mdi-{{ $currentStep > 1 ? 'check-circle text-success' : ($currentStep == 1 ? 'loading mdi-spin text-primary' : 'circle-outline text-secondary') }} mdi-36px"></i>
                                                <p class="mb-0 mt-2"><small>Step 1: Confirm Standards</small></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card {{ $currentStep >= 2 ? 'border-success' : 'border-secondary' }}">
                                            <div class="card-body text-center py-3">
                                                <i class="mdi mdi-{{ $currentStep > 2 ? 'check-circle text-success' : ($currentStep == 2 ? 'loading mdi-spin text-primary' : 'circle-outline text-secondary') }} mdi-36px"></i>
                                                <p class="mb-0 mt-2"><small>Step 2: Post Results</small></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card {{ $currentStep >= 3 ? 'border-success' : 'border-secondary' }}">
                                            <div class="card-body text-center py-3">
                                                <i class="mdi mdi-{{ $currentStep > 3 ? 'check-circle text-success' : ($currentStep == 3 ? 'loading mdi-spin text-primary' : 'circle-outline text-secondary') }} mdi-36px"></i>
                                                <p class="mb-0 mt-2"><small>Step 3: Calculate Remarks</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if(!$postingInProgress)
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closePostResultsModal">
                                <i class="mdi mdi-close"></i> Cancel
                            </button>
                            <button type="button" 
                                    class="btn btn-success" 
                                    wire:click="postResults"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="postResults">
                                    <i class="mdi mdi-check"></i> Yes, Post Results
                                </span>
                                <span wire:loading wire:target="postResults">
                                    <i class="mdi mdi-loading mdi-spin"></i> Posting...
                                </span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <style>
    /* Tag-based Dropdown Styling */
    .tag-select-container {
        position: relative;
        cursor: text;
    }
    
    .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-height: 38px;
        padding: 6px 12px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .tag-select-input:hover {
        border-color: #007bff;
    }
    
    .tag-select-input:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }
    
    .tag-badge {
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
    
    .tag-badge i {
        cursor: pointer;
        font-size: 1rem;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .tag-badge i:hover {
        opacity: 1;
    }
    
    .tag-input {
        flex: 1;
        min-width: 80px;
        border: none;
        outline: none;
        padding: 4px;
        font-size: 0.875rem;
    }
    
    .tag-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #007bff;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: -2px;
    }
    
    .tag-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
        transition: background-color 0.2s;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .tag-dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .tag-dropdown-item:last-child {
        border-bottom: none;
    }
    </style>
    
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container')) {
            Livewire.dispatch('closeAllDropdowns');
        }
    });

    // Simple dropdown positioning
    document.addEventListener('livewire:init', () => {
        Livewire.on('dropdownOpened', function(data) {
            // Just ensure dropdowns are visible
            setTimeout(() => {
                const dropdowns = document.querySelectorAll('.tag-dropdown');
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = 'block';
                });
            }, 10);
        });
    });
    </script>
</div>
