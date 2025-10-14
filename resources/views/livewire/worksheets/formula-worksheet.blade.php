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
                                    <input type="text" 
                                           class="form-control form-control-sm bg-light" 
                                           value="{{ $wsData['steps'][$step->id] ?? '' }}"
                                           readonly
                                           placeholder="Lookup">
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
</div>
