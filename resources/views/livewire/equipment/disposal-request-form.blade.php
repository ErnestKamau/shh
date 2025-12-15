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
                            <!-- Equipment Selection -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-tools text-primary"></i> Equipment Selection
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            Equipment <span class="text-danger">*</span>
                                        </label>
                                        <select wire:model.live="form.equipment_id" class="form-select" required>
                                            <option value="">Select Equipment</option>
                                            @foreach($equipmentList as $eq)
                                                <option value="{{ $eq->id }}">{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                                            @endforeach
                                        </select>
                                        @error('form.equipment_id') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Equipment Metadata (Autofilled) -->
                                    @if($equipmentMetadata && count($equipmentMetadata) > 0)
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading">
                                                <i class="mdi mdi-information"></i> Equipment Metadata
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Equipment Number:</strong> {{ $equipmentMetadata['equipment_number'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Serial Number:</strong> {{ $equipmentMetadata['serial_number'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Model:</strong> {{ $equipmentMetadata['model'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Make:</strong> {{ $equipmentMetadata['make'] ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Condition:</strong> {{ $equipmentMetadata['condition'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Department:</strong> {{ $equipmentMetadata['assigned_department'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Calibration:</strong> {{ $equipmentMetadata['calibration_status'] ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Maintenance:</strong> {{ $equipmentMetadata['maintenance_status'] ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Disposal Justification -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-file-document-text text-primary"></i> Disposal Justification
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            Justification <span class="text-danger">*</span>
                                        </label>
                                        <textarea wire:model="form.justification" class="form-control" rows="5" required placeholder="Provide detailed justification for disposal..."></textarea>
                                        @error('form.justification') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        <small class="text-muted">Minimum 10 characters required</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    Proposed Disposal Method <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.proposed_method" class="form-select" required>
                                                    <option value="">Select Method</option>
                                                    <option value="scrap">Scrap</option>
                                                    <option value="donation">Donation</option>
                                                    <option value="auction">Auction</option>
                                                    <option value="recycling">Recycling</option>
                                                    <option value="destruction">Destruction</option>
                                                </select>
                                                @error('form.proposed_method') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    Risk Assessment <span class="text-danger">*</span>
                                                </label>
                                                <select wire:model="form.risk_level" class="form-select" required>
                                                    <option value="">Select Risk Level</option>
                                                    <option value="Low">Low</option>
                                                    <option value="Medium">Medium</option>
                                                    <option value="High">High</option>
                                                    <option value="Critical">Critical</option>
                                                </select>
                                                @error('form.risk_level') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            Regulatory Category <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="form.regulatory_category" class="form-control" required placeholder="e.g., hazardous, non-hazardous, e-waste">
                                        @error('form.regulatory_category') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        <small class="text-muted">Examples: hazardous, non-hazardous, e-waste, etc.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Evidence Uploads -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-file-upload text-primary"></i> Evidence Uploads
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            Upload Evidence Files
                                        </label>
                                        <input type="file" wire:model="evidenceFiles" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                                        @error('evidenceFiles.*') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        <small class="text-muted">You can upload multiple files (images, PDFs, documents). Max 10MB per file.</small>
                                    </div>

                                    <!-- Uploaded Files List -->
                                    @if(count($uploadedFiles) > 0)
                                        <div class="mt-3">
                                            <h6>Uploaded Files:</h6>
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
                                            <h6>Files to Upload:</h6>
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

                            <!-- Requester Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-account text-primary"></i> Requester Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Requested By:</strong> {{ auth()->user()->name }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Date Initiated:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Role:</strong> {{ auth()->user()->position_name() ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="button" wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="mdi mdi-content-save"></i> Save as Draft
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="mdi mdi-loading mdi-spin"></i> Saving...
                            </span>
                        </button>
                        <button type="button" wire:click="submit" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submit">
                                <i class="mdi mdi-send"></i> Submit for Approval
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

