<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-play-circle text-success"></i>
                                Worksheet Executor
                            </h2>
                            <p class="text-muted mb-0">{{ $formulaVersion->formula->name }} - Version {{ $formulaVersion->version_number }}</p>
                        </div>
                        <div>
                            <button wire:click="resetWorksheet" class="btn btn-outline-secondary me-2">
                                <i class="mdi mdi-refresh"></i> Reset
                            </button>
                            <button wire:click="executeWorksheet" class="btn btn-success" 
                                    @if($isExecuting) disabled @endif>
                                <i class="mdi mdi-play"></i> 
                                @if($isExecuting)
                                    Executing...
                                @else
                                    Execute Worksheet
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <div class="row">
        <!-- Input Section -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-input"></i> Input Values
                    </h6>
                </div>
                <div class="card-body">
                    @if($inputSteps->count() > 0)
                        <form>
                            @foreach($inputSteps as $step)
                                <div class="mb-3">
                                    <label for="input_{{ $step->variable_name }}" class="form-label">
                                        {{ $step->label }}
                                        @if($step->description)
                                            <small class="text-muted d-block">{{ $step->description }}</small>
                                        @endif
                                    </label>
                                    <input type="text" 
                                           wire:model.live="inputs.{{ $step->variable_name }}" 
                                           class="form-control" 
                                           id="input_{{ $step->variable_name }}"
                                           placeholder="Enter value for {{ $step->label }}">
                                </div>
                            @endforeach
                        </form>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-input fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No input steps defined for this formula</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-chart-line"></i> Execution Results
                    </h6>
                </div>
                <div class="card-body">
                    @if($executionResult)
                        <div class="mb-3">
                            <h6>Execution Summary</h6>
                            <div class="alert alert-success">
                                <i class="mdi mdi-check-circle"></i>
                                Worksheet executed successfully!
                            </div>
                        </div>

                        @if(isset($executionResult['inputs']))
                            <div class="mb-3">
                                <h6>Input Values</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Variable</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($executionResult['inputs'] as $variable => $value)
                                                <tr>
                                                    <td><code>{{ $variable }}</code></td>
                                                    <td>{{ $value }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(isset($executionResult['derived_values']))
                            <div class="mb-3">
                                <h6>Derived Values</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Variable</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($executionResult['derived_values'] as $variable => $value)
                                                <tr>
                                                    <td><code>{{ $variable }}</code></td>
                                                    <td>{{ $value }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(isset($executionResult['lookup_results']))
                            <div class="mb-3">
                                <h6>Lookup Results</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Variable</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($executionResult['lookup_results'] as $variable => $value)
                                                <tr>
                                                    <td><code>{{ $variable }}</code></td>
                                                    <td>{{ $value }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(isset($executionResult['final_result']))
                            <div class="mb-3">
                                <h6>Final Result</h6>
                                <div class="alert alert-primary">
                                    <strong>{{ $executionResult['final_result'] }}</strong>
                                </div>
                            </div>
                        @endif

                        @if($executionMode === 'workflow')
                            <div class="mt-3">
                                <button wire:click="saveExecution" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Execution
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-chart-line fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Execute the worksheet to see results here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Execution Details -->
    @if($executionResult && isset($executionResult['execution_details']))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="mb-0 text-muted">
                            <i class="mdi mdi-information"></i> Execution Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Execution Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Execution Mode:</strong></td>
                                        <td>{{ ucfirst($executionMode) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Execution Time:</strong></td>
                                        <td>{{ $executionResult['execution_time'] ?? 'N/A' }}ms</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Steps Executed:</strong></td>
                                        <td>{{ $executionResult['steps_executed'] ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Context Information</h6>
                                <table class="table table-sm">
                                    @if($sampleId)
                                        <tr>
                                            <td><strong>Sample ID:</strong></td>
                                            <td>{{ $sampleId }}</td>
                                        </tr>
                                    @endif
                                    @if($batchId)
                                        <tr>
                                            <td><strong>Batch ID:</strong></td>
                                            <td>{{ $batchId }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Save Execution Modal -->
@if($showSaveModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-content-save"></i>
                        Save Execution
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeSaveModal"></button>
                </div>
                <div class="modal-body">
                    <p>Do you want to save this worksheet execution to the history?</p>
                    <div class="form-check">
                        <input type="checkbox" wire:model="saveExecution" class="form-check-input" id="saveExecution">
                        <label class="form-check-label" for="saveExecution">
                            Save execution to history
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeSaveModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveExecution">Save Execution</button>
                </div>
            </div>
        </div>
    </div>
@endif

    <style>
    .modal.show {
        display: block !important;
    }
    </style>
</div>