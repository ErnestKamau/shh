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
                                    <h5>Select Samples</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($this->analysisSamples as $result)
                                            <div class="form-check mr-3">
                                                <input class="form-check-input" type="checkbox" value="{{ $result->sample->id }}" id="sample-{{ $result->sample->id }}" wire:model="selectedSamples">
                                                <label class="form-check-label" for="sample-{{ $result->sample->id }}">
                                                    {{ $result->sample->sample_code }}
                                                </label>
                                            </div>
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
                                                        <th>{{ $step->name }} ({{ $step->unit ?? '-' }})</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($this->analysisSamples as $result)
                                                    @if(in_array($result->sample->id, $selectedSamples))
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

                                    @if($testKitColumns->count() > 0)
                                        <div class="card mt-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">
                                                    <i class="mdi mdi-table text-primary"></i>
                                                    Test Kit Table
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                @foreach($testKitColumns as $column)
                                                                    <th>
                                                                        {{ $column->label }}
                                                                        @if($column->help_text)
                                                                            <br><small class="text-muted">{{ $column->help_text }}</small>
                                                                        @endif
                                                                    </th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($testKitRows as $rowId => $rowMeta)
                                                                <tr>
                                                                    <td class="font-weight-bold">
                                                                        {{ $rowMeta['row_index'] ?? 1 }}
                                                                    </td>
                                                                    @foreach($testKitColumns as $column)
                                                                        <td>
                                                                            @php
                                                                                $type = $column->type;
                                                                            @endphp
                                                                            @if($type === 'date')
                                                                                <input type="date"
                                                                                    class="form-control form-control-sm"
                                                                                    wire:model.defer="testKitData.{{ $rowId }}.{{ $column->id }}">
                                                                            @elseif($type === 'number')
                                                                                <input type="number"
                                                                                    class="form-control form-control-sm"
                                                                                    wire:model.defer="testKitData.{{ $rowId }}.{{ $column->id }}">
                                                                            @elseif($type === 'boolean')
                                                                                <select class="form-control form-control-sm"
                                                                                        wire:model.defer="testKitData.{{ $rowId }}.{{ $column->id }}">
                                                                                    <option value=\"\">--</option>
                                                                                    <option value=\"1\">Yes</option>
                                                                                    <option value=\"0\">No</option>
                                                                                </select>
                                                                            @else
                                                                                <input type="text"
                                                                                    class="form-control form-control-sm"
                                                                                    wire:model.defer="testKitData.{{ $rowId }}.{{ $column->id }}">
                                                                            @endif
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="{{ $testKitColumns->count() + 1 }}" class="text-center text-muted">
                                                                        No test kit rows defined yet.
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
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
</div>
