<div>
    <!-- Trigger Button (only show if modal is closed) -->
    @if(!$showModal)
        <button wire:click="openModal" class="btn btn-primary">
            <i class="mdi mdi-clipboard-check"></i> {{ __('equipment.create_evaluation') }}
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
                            {{ $evaluationId ? __('equipment.edit') : __('equipment.create') }} {{ __('equipment.equipment_evaluation') }}
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
                                            <i class="mdi mdi-tools text-primary"></i> {{ __('equipment.equipment_information') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>{{ __('equipment.name') }}:</strong> {{ $equipment->name }}</p>
                                                <p class="mb-1"><strong>{{ __('equipment.equipment_number') }}:</strong> {{ $equipment->equipment_number }}</p>
                                                <p class="mb-1"><strong>{{ __('equipment.serial_number') }}:</strong> {{ $equipment->serial_number ?? '-' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>{{ __('equipment.model') }}:</strong> {{ $equipment->model }}</p>
                                                <p class="mb-1"><strong>{{ __('equipment.make') }}:</strong> {{ $equipment->make }}</p>
                                                <p class="mb-1"><strong>{{ __('equipment.condition') }}:</strong> {{ $equipment->condition ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Evaluation Details -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                            <i class="mdi mdi-file-document-text text-primary"></i> {{ __('equipment.evaluation_details') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    {{ __('equipment.evaluation_date') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" wire:model="form.evaluation_date" class="form-control" required>
                                                @error('form.evaluation_date') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    {{ __('equipment.physical_condition') }} <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.physical_condition" class="form-select" required>
                                                    <option value="">{{ __('equipment.select_condition') }}</option>
                                                    <option value="excellent">{{ __('equipment.excellent') }}</option>
                                                    <option value="good">{{ __('equipment.good') }}</option>
                                                    <option value="fair">{{ __('equipment.fair') }}</option>
                                                    <option value="poor">{{ __('equipment.poor') }}</option>
                                                    <option value="failed">{{ __('equipment.failed') }}</option>
                                                </select>
                                                @error('form.physical_condition') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    {{ __('equipment.recommendation') }} <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.recommendation" class="form-select" required>
                                                    <option value="">{{ __('equipment.select_recommendation') }}</option>
                                                    <option value="continue_use">{{ __('equipment.continue_use') }}</option>
                                                    <option value="repair">{{ __('equipment.repair') }}</option>
                                                    <option value="dispose">{{ __('equipment.dispose') }}</option>
                                                </select>
                                                @error('form.recommendation') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">{{ __('equipment.last_calibration_date') }}</label>
                                                <input type="date" wire:model="form.last_calibration_date" class="form-control">
                                                @error('form.last_calibration_date') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">{{ __('equipment.last_calibration_status') }}</label>
                                                <select wire:model="form.last_calibration_status" class="form-select">
                                                    <option value="">{{ __('equipment.select_status') }}</option>
                                                    <option value="pass">{{ __('equipment.pass') }}</option>
                                                    <option value="fail">{{ __('equipment.fail') }}</option>
                                                    <option value="not_applicable">{{ __('equipment.not_applicable') }}</option>
                                                </select>
                                                @error('form.last_calibration_status') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('equipment.impact_on_testing') }}</label>
                                        <textarea wire:model="form.impact_on_testing" class="form-control" rows="3" 
                                                  placeholder="{{ __('equipment.impact_on_testing_placeholder') }}"></textarea>
                                        @error('form.impact_on_testing') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Cost Analysis -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                            <i class="mdi mdi-calculator text-primary"></i> {{ __('equipment.cost_analysis') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">{{ __('equipment.repair_cost_estimate') }}</label>
                                                <input type="number" step="0.01" min="0" wire:model.blur="form.repair_cost_estimate" class="form-control" placeholder="0.00">
                                                @error('form.repair_cost_estimate') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">{{ __('equipment.replacement_cost_estimate') }}</label>
                                                <input type="number" step="0.01" min="0" wire:model.blur="form.replacement_cost_estimate" class="form-control" placeholder="0.00">
                                                @error('form.replacement_cost_estimate') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    @if(!empty($costComparison))
                                        <div class="alert alert-{{ $costComparison['recommendation'] === 'replace' ? 'danger' : ($costComparison['recommendation'] === 'repair' ? 'success' : 'warning') }}">
                                            <strong>{{ __('equipment.cost_comparison_result') }}:</strong>
                                            <p class="mb-0">{{ $costComparison['reason'] ?? 'N/A' }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                            <i class="mdi mdi-information text-primary"></i> {{ __('equipment.additional_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                             <label class="form-label fw-bold">{{ __('equipment.fault_report_reference') }}</label>
                                        <input type="text" wire:model="form.fault_report_reference" class="form-control" 
                                                 placeholder="{{ __('equipment.fault_report_reference_placeholder') }}">
                                        @error('form.fault_report_reference') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('equipment.evaluation_notes') }}</label>
                                        <textarea wire:model="form.evaluation_notes" class="form-control" rows="4" 
                                                  placeholder="{{ __('equipment.evaluation_notes_placeholder') }}"></textarea>
                                        @error('form.evaluation_notes') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Calibration History -->
                            @if($calibrationHistory && count($calibrationHistory) > 0)
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="mdi mdi-history text-primary"></i> {{ __('equipment.recent_calibration_history') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('equipment.date') }}</th>
                                                        <th>{{ __('equipment.notes') }}</th>
                                                        <th>{{ __('equipment.overseen_by') }}</th>
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
                            <i class="mdi mdi-close"></i> {{ __('equipment.cancel') }}
                        </button>
                        <button type="button" wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="mdi mdi-content-save"></i> {{ __('equipment.save_evaluation') }}
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.saving') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


