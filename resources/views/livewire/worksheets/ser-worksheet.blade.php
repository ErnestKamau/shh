<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(!$isRunCreated)
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>{{ $analysisTypeName }} Worksheet</h4>
            <button class="btn btn-success" wire:click="createRun">
                <i class="mdi mdi-plus"></i> Create New Run
            </button>
        </div>

        @if(count($availableRuns) > 0)
            <div class="mb-4">
                <h5>Existing Runs</h5>
                <div class="row">
                    @foreach($availableRuns as $run)
                        <div class="col-md-4 mb-3">
                            <div class="card border-success shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="text-success mb-0">Run for {{ $run->sample->sample_code }}</h6>
                                        <span class="badge badge-success">Created</span>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        <i class="mdi mdi-calendar"></i> {{ $run->date_tested ? $run->date_tested->format('M d, Y') : 'No date' }}
                                    </p>
                                    <button class="btn btn-sm btn-outline-success btn-block" wire:click="selectRun({{ $run->id }})">
                                        <i class="mdi mdi-pencil"></i> View / Edit Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Samples Awaiting Run</h5>
                    <p class="text-muted small mb-0">Select "Create New Run" to start capturing data for these samples.</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Sample Code</th>
                                <th>Analysis Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($samples as $sample)
                                @php 
                                    $sampleId = $sample['id'] ?? $sample->id;
                                    $hasRun = collect($availableRuns)->contains('sample_detail_id', $sampleId);
                                @endphp
                                @if(!$hasRun)
                                    <tr>
                                        <td>{{ $sample['sample_code'] ?? $sample->sample_code }}</td>
                                        <td>{{ $analysisTypeName }}</td>
                                        <td><span class="badge badge-secondary">Pending Run</span></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        @include('worksheets.partials.worksheet-meta-bar', ['metaSummary' => $worksheetMetaSummary])
        @if(!empty($worksheetMetaRows) && count($worksheetMetaRows) > 1)
            @include('worksheets.partials.worksheet-meta-table', ['metaRows' => $worksheetMetaRows, 'compact' => true])
        @endif
        <div class="card">
            <div class="card-header bg-white">
                 <h5 class="mb-0">New Run - {{ $analysisTypeName }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <!-- Top Form Fields -->
                    <div class="row">
                        <!-- Laboratory Number -->
                        <div class="col-md-4 mb-3">
                            <label>Laboratory Number</label>
                            <div class="tag-select-container" wire:click="$set('showLabDropdown', true)">
                                <div class="tag-select-input">
                                    <!-- Display selected samples -->
                                    @foreach($lab_numbers as $id)
                                        @php $sample = collect($availableSamples)->firstWhere('id', $id); @endphp
                                        @if($sample)
                                            <span class="tag-badge">
                                                {{ $sample['code'] }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="removeLab({{ $id }})"></i>
                                            </span>
                                        @endif
                                    @endforeach
                                    
                                    <!-- Search Input -->
                                    <input type="text" 
                                           wire:model.live="labSearch" 
                                           class="tag-input" 
                                           placeholder="{{ count($lab_numbers) > 0 ? '' : 'Search samples...' }}"
                                           autocomplete="off">
                                </div>
                                
                                <!-- Dropdown -->
                                @if($showLabDropdown && count($this->filteredSamples) > 0)
                                    <div class="tag-dropdown">
                                        @foreach($this->filteredSamples as $s)
                                            <div class="tag-dropdown-item" wire:click.stop="selectLab({{ $s['id'] }})">
                                                {{ $s['code'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @error('lab_numbers') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sample Type -->
                        <div class="col-md-4 mb-3">
                            <label>Sample Type</label>
                            <input type="text" class="form-control" wire:model="sample_type" readonly>
                        </div>

                        <!-- Analyst -->
                        <div class="col-md-4 mb-3">
                            <label>Analyst</label>
                            <div class="tag-select-container" wire:click="$set('showAnalystDropdown', true)">
                                <div class="tag-select-input">
                                    <!-- Display selected analysts -->
                                    @foreach($analyst_ids as $id)
                                        @php $analyst = $availableAnalysts->firstWhere('id', $id); @endphp
                                        @if($analyst)
                                            <span class="tag-badge">
                                                {{ $analyst->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="removeAnalyst({{ $id }})"></i>
                                            </span>
                                        @endif
                                    @endforeach
                                    
                                    <!-- Search Input -->
                                    <input type="text" 
                                           wire:model.live="analystSearch" 
                                           class="tag-input" 
                                           placeholder="{{ count($analyst_ids) > 0 ? '' : 'Search analysts...' }}"
                                           autocomplete="off">
                                </div>
                                
                                <!-- Dropdown -->
                                @if($showAnalystDropdown && count($this->filteredAnalysts) > 0)
                                    <div class="tag-dropdown">
                                        @foreach($this->filteredAnalysts as $analyst)
                                            <div class="tag-dropdown-item" wire:click.stop="selectAnalyst({{ $analyst->id }})">
                                                {{ $analyst->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @error('analyst_ids') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Dilution Used -->
                        <div class="col-md-4 mb-3">
                            <label>Dilution Used</label>
                            <input type="text" class="form-control" wire:model="dilution_used">
                        </div>

                        <!-- Date Received -->
                        <div class="col-md-4 mb-3">
                            <label>Date Received</label>
                             <input type="date" class="form-control" wire:model="date_received" readonly>
                        </div>

                        <!-- Date Tested -->
                        <div class="col-md-4 mb-3">
                            <label>Date Tested</label>
                             <input type="date" class="form-control" wire:model="date_tested">
                             @error('date_tested') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Test(s) -->
                        <div class="col-md-4 mb-3">
                            <label>Test(s)</label>
                            <div class="form-control bg-light" style="height: auto;">
                                @if(count($test_analyte_codes) > 0)
                                    {{ implode(', ', $test_analyte_codes) }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <!-- Method Used -->
                         <div class="col-md-4 mb-3">
                            <label>Method Used</label>
                            <div class="tag-select-container" wire:click="$set('showMethodDropdown', true)">
                                <div class="tag-select-input">
                                    <!-- Display selected method -->
                                    @if($method_id)
                                        @php $method = $availableMethods->firstWhere('id', $method_id); @endphp
                                        @if($method)
                                            <span class="tag-badge">
                                                {{ $method->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearMethod"></i>
                                            </span>
                                        @endif
                                    @endif
                                    
                                    <!-- Search Input -->
                                    <input type="text" 
                                           wire:model.live="methodSearch" 
                                           class="tag-input" 
                                           placeholder="{{ $method_id ? '' : 'Search methods...' }}"
                                           autocomplete="off">
                                </div>
                                
                                <!-- Dropdown -->
                                @if($showMethodDropdown && count($this->filteredMethods) > 0)
                                    <div class="tag-dropdown">
                                        @foreach($this->filteredMethods as $method)
                                            <div class="tag-dropdown-item" wire:click.stop="selectMethod({{ $method->id }})">
                                                {{ $method->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Room Temperature -->
                        <div class="col-md-4 mb-3">
                             <label>Room Temperature</label>
                            <input type="text" class="form-control" wire:model="room_temperature">
                        </div>
                        
                         <!-- Start Time -->
                        <div class="col-md-4 mb-3">
                             <label>Start Time</label>
                            <input type="time" class="form-control" wire:model="start_time">
                        </div>
                    </div>

                    <hr>

                    <!-- Steps Table -->
                     <h5 class="mb-3">Worksheet Steps</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;">Step</th>
                                    <th>Description</th>
                                    <th>Measurand</th>
                                    <th>Equipment</th>
                                    <th>Analyst</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($steps as $index => $step)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $step['step_name'] }}</td>
                                        <td>
                                            <!-- Measurand Dropdown -->
                                            <!-- Assuming all analysis elements are valid options, or filter based on something? -->
                                             <select class="form-control form-control-sm" wire:model="steps.{{ $index }}.measurand_id">
                                                <option value="">Select Measurand</option>
                                                @foreach($measurands as $m)
                                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <!-- Equipment Dropdown -->
                                            <select class="form-control form-control-sm" wire:model="steps.{{ $index }}.equipment_id">
                                                <option value="">Select Equipment</option>
                                                 @foreach($equipments as $eq)
                                                    <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                         <td>
                                            <!-- Analyst Dropdown -->
                                            <select class="form-control form-control-sm" wire:model="steps.{{ $index }}.analyst_id">
                                                 @foreach($availableAnalysts as $analyst)
                                                    <option value="{{ $analyst->id }}">{{ $analyst->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Test Kit Information Table -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Test Kit Information</h5>
                        <button type="button" class="btn btn-sm btn-outline-success" wire:click="addTestKit">
                            <i class="mdi mdi-plus"></i> Add Row
                        </button>
                    </div>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Test</th>
                                    <th>Kit Lot Number</th>
                                    <th>Wells Used</th>
                                    <th>Expiry Date</th>
                                    <th style="width: 50px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($testKits as $index => $kit)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" wire:model="testKits.{{ $index }}.test_name">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" wire:model="testKits.{{ $index }}.kit_lot_number">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" wire:model="testKits.{{ $index }}.wells_used">
                                        </td>
                                        <td>
                                            <input type="date" class="form-control form-control-sm" wire:model="testKits.{{ $index }}.expiry_date">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger py-0" wire:click="removeTestKit({{ $index }})">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                @if(count($testKits) === 0)
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No test kit information added.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isRunCreated', false)">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-content-save"></i> Save Run
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

<script>
document.addEventListener('click', function(e) {
    if (!e.target.closest('.tag-select-container')) {
        @this.set('showLabDropdown', false);
        @this.set('showAnalystDropdown', false);
        @this.set('showMethodDropdown', false);
    }
});
</script>

<style>
/* Tag-based Dropdown Styling - Copied from Element Manager */
.tag-select-container {
    position: relative;
    cursor: text;
}

.tag-select-input {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    min-height: 38px; /* Matched form-control height */
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #ced4da; /* Matched bootstrap border */
    border-radius: 0.25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}

.tag-select-input:hover {
    border-color: #80bdff;
}

.tag-select-input:focus-within {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}

.tag-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    background-color: #007bff;
    color: white;
    border-radius: 12px;
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
    min-width: 120px;
    border: none;
    outline: none;
    padding: 2px;
    font-size: 0.9rem;
    background: transparent;
}

.tag-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #80bdff;
    border-top: none;
    border-radius: 0 0 4px 4px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: -1px;
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
</div>
