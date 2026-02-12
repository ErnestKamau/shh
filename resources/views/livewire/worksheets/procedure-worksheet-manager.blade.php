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
