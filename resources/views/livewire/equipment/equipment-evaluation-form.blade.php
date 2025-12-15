<div>
    <!-- Trigger Button (only show if modal is closed) -->
    @if(!$showModal)
        <button wire:click="openModal" class="btn btn-primary">
            <i class="mdi mdi-clipboard-check"></i> Create Evaluation
        </button>
    @endif

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto; z-index: 1050;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-clipboard-check"></i>
                            {{ $evaluationId ? 'Edit' : 'Create' }} Equipment Evaluation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Message Alert -->
                        @if($message)
                            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" wire:click="dismissMessage"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="save">
                            <!-- Equipment Information (Read-only) -->
                            @if($equipment)
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="mdi mdi-tools text-primary"></i> Equipment Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Name:</strong> {{ $equipment->name }}</p>
                                                <p class="mb-1"><strong>Equipment Number:</strong> {{ $equipment->equipment_number }}</p>
                                                <p class="mb-1"><strong>Serial Number:</strong> {{ $equipment->serial_number ?? '-' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Model:</strong> {{ $equipment->model }}</p>
                                                <p class="mb-1"><strong>Make:</strong> {{ $equipment->make }}</p>
                                                <p class="mb-1"><strong>Condition:</strong> {{ $equipment->condition ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Evaluation Details -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-file-document-text text-primary"></i> Evaluation Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    Evaluation Date <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" wire:model="form.evaluation_date" class="form-control" required>
                                                @error('form.evaluation_date') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    Physical Condition <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.physical_condition" class="form-select" required>
                                                    <option value="">Select Condition</option>
                                                    <option value="excellent">Excellent</option>
                                                    <option value="good">Good</option>
                                                    <option value="fair">Fair</option>
                                                    <option value="poor">Poor</option>
                                                    <option value="failed">Failed</option>
                                                </select>
                                                @error('form.physical_condition') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    Recommendation <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.recommendation" class="form-select" required>
                                                    <option value="">Select Recommendation</option>
                                                    <option value="continue_use">Continue Use</option>
                                                    <option value="repair">Repair</option>
                                                    <option value="dispose">Dispose</option>
                                                </select>
                                                @error('form.recommendation') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Last Calibration Date</label>
                                                <input type="date" wire:model="form.last_calibration_date" class="form-control">
                                                @error('form.last_calibration_date') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Last Calibration Status</label>
                                                <select wire:model="form.last_calibration_status" class="form-select">
                                                    <option value="">Select Status</option>
                                                    <option value="pass">Pass</option>
                                                    <option value="fail">Fail</option>
                                                    <option value="not_applicable">Not Applicable</option>
                                                </select>
                                                @error('form.last_calibration_status') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Impact on Testing</label>
                                        <textarea wire:model="form.impact_on_testing" class="form-control" rows="3" 
                                                  placeholder="Describe the impact if this equipment fails or is disposed..."></textarea>
                                        @error('form.impact_on_testing') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Cost Analysis -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-calculator text-primary"></i> Cost Analysis
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Repair Cost Estimate</label>
                                                <input type="number" step="0.01" min="0" wire:model.blur="form.repair_cost_estimate" class="form-control" placeholder="0.00">
                                                @error('form.repair_cost_estimate') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Replacement Cost Estimate</label>
                                                <input type="number" step="0.01" min="0" wire:model.blur="form.replacement_cost_estimate" class="form-control" placeholder="0.00">
                                                @error('form.replacement_cost_estimate') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    @if(!empty($costComparison))
                                        <div class="alert alert-{{ $costComparison['recommendation'] === 'replace' ? 'danger' : ($costComparison['recommendation'] === 'repair' ? 'success' : 'warning') }}">
                                            <strong>Cost Comparison Result:</strong>
                                            <p class="mb-0">{{ $costComparison['reason'] ?? 'N/A' }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-information text-primary"></i> Additional Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Fault Report Reference</label>
                                        <input type="text" wire:model="form.fault_report_reference" class="form-control" 
                                               placeholder="Enter fault/nonconformance report reference">
                                        @error('form.fault_report_reference') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Evaluation Notes</label>
                                        <textarea wire:model="form.evaluation_notes" class="form-control" rows="4" 
                                                  placeholder="Additional notes and observations..."></textarea>
                                        @error('form.evaluation_notes') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Calibration History -->
                            @if($calibrationHistory && count($calibrationHistory) > 0)
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="mdi mdi-history text-primary"></i> Recent Calibration History
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Notes</th>
                                                        <th>Overseen By</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($calibrationHistory as $log)
                                                        <tr>
                                                            <td>{{ $log->date->format('Y-m-d') }}</td>
                                                            <td>{{ \Str::limit($log->notes, 50) }}</td>
                                                            <td>{{ $log->overseen_by }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="button" wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="mdi mdi-content-save"></i> Save Evaluation
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="mdi mdi-loading mdi-spin"></i> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


