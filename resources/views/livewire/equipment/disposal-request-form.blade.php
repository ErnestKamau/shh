<div>
    <!-- Trigger Button (only show if modal is closed) -->
    <!-- Trigger Button Removed (handled by parent) -->

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto; z-index: 1050;">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-delete-sweep"></i>
                            {{ $disposalId ? 'Edit' : 'Initiate' }} Disposal Request
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
                            <!-- {{ __('equipment.equipment_selection') }} -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-tools text-primary"></i> {{ __('equipment.equipment_selection') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            Equipment <span class="text-danger">*</span>
                                        </label>
                                        <select wire:model.live="form.equipment_id" class="form-select" required>
                                            <option value="">{{ __('equipment.select_equipment') }}</option>
                                            @foreach($equipmentList as $eq)
                                                <option value="{{ $eq->id }}">{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                                            @endforeach
                                        </select>
                                        @error('form.equipment_id') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- {{ __('equipment.equipment_metadata') }} (Autofilled) -->
                                    @if($equipmentMetadata && count($equipmentMetadata) > 0)
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading">
                                                <i class="mdi mdi-information"></i> {{ __('equipment.equipment_metadata') }}
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>{{ __('equipment.equipment_number') }}:</strong> {{ $equipmentMetadata['equipment_number'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>{{ __('equipment.serial_number') }}:</strong> {{ $equipmentMetadata['serial_number'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>{{ __('equipment.model') }}:</strong> {{ $equipmentMetadata['model'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>{{ __('equipment.make') }}:</strong> {{ $equipmentMetadata['make'] ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>{{ __('equipment.condition') }}:</strong> {{ $equipmentMetadata['condition'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>{{ __('equipment.department') }}:</strong> {{ $equipmentMetadata['assigned_department'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>{{ __('equipment.calibration_log') }}:</strong> {{ $equipmentMetadata['calibration_status'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>{{ __('equipment.maintenance_log') }}:</strong> {{ $equipmentMetadata['maintenance_status'] ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- {{ __('equipment.disposal_justification') }} -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-file-document-text text-primary"></i> {{ __('equipment.disposal_justification') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            {{ __('equipment.justification') }} <span class="text-danger">*</span>
                                        </label>
                                        <textarea wire:model="form.justification" class="form-control" rows="5" required placeholder="{{ __('equipment.provide_detailed_justification_for_disposal') }}"></textarea>
                                        @error('form.justification') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        <small class="text-muted">{{ __('equipment.minimum_10_characters_required') }}</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    {{ __('equipment.proposed_disposal_method') }} <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.proposed_method" class="form-select" required>
                                                    <option value="">{{ __('equipment.select_method') }}</option>
                                                    <option value="scrap">{{ __('equipment.scrap') }}</option>
                                                    <option value="donation">{{ __('equipment.donation') }}</option>
                                                    <option value="auction">{{ __('equipment.auction') }}</option>
                                                    <option value="recycling">{{ __('equipment.recycling') }}</option>
                                                    <option value="destruction">{{ __('equipment.destruction') }}</option>
                                                </select>
                                                @error('form.proposed_method') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    {{ __('equipment.risk_assessment') }} <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.risk_level" class="form-select" required>
                                                    <option value="">{{ __('equipment.select_risk_level') }}</option>
                                                    <option value="Low">{{ __('equipment.low') }}</option>
                                                    <option value="Medium">{{ __('equipment.medium') }}</option>
                                                    <option value="High">{{ __('equipment.high') }}</option>
                                                    <option value="Critical">{{ __('equipment.critical') }}</option>
                                                </select>
                                                @error('form.risk_level') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            {{ __('equipment.regulatory_category') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="form.regulatory_category" class="form-control" required placeholder="{{ __('equipment.regulatory_category_examples') }}">
                                        @error('form.regulatory_category') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        <small class="text-muted">{{ __('equipment.regulatory_category_examples_help') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- {{ __('equipment.evidence_uploads') }} -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-file-upload text-primary"></i> {{ __('equipment.evidence_uploads') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            {{ __('equipment.upload_evidence_files') }}
                                        </label>
                                        <input type="file" wire:model="evidenceFiles" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                                        @error('evidenceFiles.*') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        <small class="text-muted">You can upload multiple files (images, PDFs, documents). Max 10MB per file.</small>
                                    </div>

                                    <!-- Uploaded Files List -->
                                    @if(count($uploadedFiles) > 0)
                                        <div class="mt-3">
                                            <h6>{{ __('equipment.uploaded_files') }}:</h6>
                                            <div class="list-group">
                                                @foreach($uploadedFiles as $file)
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="mdi mdi-file"></i> {{ $file->file_name }}
                                                            <small class="text-muted d-block">Type: {{ $file->file_type }}</small>
                                                        </div>
                                                        @if($disposalId && $disposal && $disposal->canBeEdited())
                                                            <button type="button" wire:click="removeFile({{ $file->id }})" class="btn btn-sm btn-outline-danger">
                                                                <i class="mdi mdi-delete"></i> Remove
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if(count($evidenceFiles) > 0)
                                        <div class="mt-3">
                                            <h6>{{ __('equipment.files_to_upload') }}:</h6>
                                            <ul class="list-unstyled">
                                                @foreach($evidenceFiles as $index => $file)
                                                    <li>
                                                        <i class="mdi mdi-file"></i> {{ $file->getClientOriginalName() }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- {{ __('equipment.requester_information') }} -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-account text-primary"></i> {{ __('equipment.requester_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>{{ __('equipment.requested_by') }}:</strong> {{ auth()->user()->name }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>{{ __('equipment.date_initiated') }}:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>{{ __('equipment.role') }}:</strong> {{ auth()->user()->position_name() ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="mdi mdi-close"></i> {{ __('equipment.cancel') }}
                        </button>
                        <button type="button" wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="mdi mdi-content-save"></i> {{ __('equipment.save_as_draft') }}
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="mdi mdi-loading mdi-spin"></i> Saving...
                            </span>
                        </button>
                        <button type="button" wire:click="submit" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submit">
                                <i class="mdi mdi-send"></i> {{ __('equipment.submit_for_approval') }}
                            </span>
                            <span wire:loading wire:target="submit">
                                <i class="mdi mdi-loading mdi-spin"></i> Submitting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Script for jQuery conditional rendering -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('disposal-saved', () => {
                // Refresh parent component or show success message
            });

            Livewire.on('disposal-submitted', () => {
                // Handle submission success
            });
        });
    </script>
</div>

