<div>
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto; z-index: 1060;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-check-circle"></i>
                            Review & Approve Disposal Request
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        @if(!$disposal || !$approvalStep)
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                No pending approval step found or disposal request not found.
                            </div>
                        @else
                            <!-- Message Alert -->
                            @if($message)
                                <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" wire:click="dismissMessage"></button>
                                </div>
                            @endif

                            <!-- Disposal Request Summary -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-information"></i> Disposal Request Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Equipment:</strong> {{ $disposal->equipment->name }}</p>
                                            <p class="mb-2"><strong>Equipment Number:</strong> {{ $disposal->equipment->equipment_number }}</p>
                                            <p class="mb-2"><strong>Proposed Method:</strong> {{ ucfirst($disposal->proposed_method) }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Risk Level:</strong> 
                                                <span class="badge badge-{{ $disposal->risk_level === 'High' || $disposal->risk_level === 'Critical' ? 'danger' : 'warning' }}">
                                                    {{ $disposal->risk_level }}
                                                </span>
                                            </p>
                                            <p class="mb-2"><strong>Requested By:</strong> {{ $disposal->requester->name }}</p>
                                            <p class="mb-2"><strong>Requested Date:</strong> {{ $disposal->created_at->format('Y-m-d') }}</p>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <strong>Justification:</strong>
                                            <div class="bg-light p-3 mt-2" style="border-radius: 5px;">
                                                <p class="mb-0" style="white-space: pre-wrap;">{{ $disposal->justification }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Form -->
                            <form wire:submit.prevent="processApproval">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="mdi mdi-file-check"></i> Approval Decision
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                Decision <span class="text-danger">*</span>
                                            </label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="card {{ $approvalForm['decision'] === 'approve' ? 'border-success' : 'border-light' }}" 
                                                         style="cursor: pointer; transition: all 0.3s;"
                                                         onclick="$('[name=decision-approve]').prop('checked', true); $('[name=decision-approve]').trigger('change');">
                                                        <div class="card-body text-center">
                                                            <input type="radio" name="decision-approve" 
                                                                   wire:model="approvalForm.decision" 
                                                                   value="approve" 
                                                                   id="decision-approve"
                                                                   class="form-check-input">
                                                            <label for="decision-approve" class="form-check-label w-100" style="cursor: pointer;">
                                                                <i class="mdi mdi-check-circle text-success" style="font-size: 2rem;"></i>
                                                                <h5 class="mt-2">Approve</h5>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card {{ $approvalForm['decision'] === 'reject' ? 'border-danger' : 'border-light' }}" 
                                                         style="cursor: pointer; transition: all 0.3s;"
                                                         onclick="$('[name=decision-reject]').prop('checked', true); $('[name=decision-reject]').trigger('change');">
                                                        <div class="card-body text-center">
                                                            <input type="radio" name="decision-reject" 
                                                                   wire:model="approvalForm.decision" 
                                                                   value="reject" 
                                                                   id="decision-reject"
                                                                   class="form-check-input">
                                                            <label for="decision-reject" class="form-check-label w-100" style="cursor: pointer;">
                                                                <i class="mdi mdi-close-circle text-danger" style="font-size: 2rem;"></i>
                                                                <h5 class="mt-2">Reject</h5>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @error('approvalForm.decision') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Remarks (Mandatory for Reject) -->
                                        <div class="form-group mb-3" id="remarks-section" style="display: {{ $approvalForm['decision'] === 'reject' ? 'block' : 'none' }};">
                                            <label class="form-label fw-bold">
                                                Remarks <span class="text-danger">*</span>
                                            </label>
                                            <textarea wire:model="approvalForm.remarks" 
                                                      class="form-control" 
                                                      rows="4" 
                                                      placeholder="Provide detailed remarks for your decision..."
                                                      {{ $approvalForm['decision'] === 'reject' ? 'required' : '' }}></textarea>
                                            @error('approvalForm.remarks') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            <small class="text-muted">
                                                @if($approvalForm['decision'] === 'reject')
                                                    Remarks are mandatory when rejecting a disposal request.
                                                @else
                                                    Optional remarks can be added for approval.
                                                @endif
                                            </small>
                                        </div>

                                        <!-- Signature Upload -->
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                Digital Signature
                                            </label>
                                            <input type="file" wire:model="approvalForm.signature" class="form-control" accept="image/*">
                                            @error('approvalForm.signature') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                            <small class="text-muted">
                                                Upload your signature image. If not provided, your default signature from profile will be used.
                                            </small>
                                            @if(auth()->user()->electronic_sig && !$approvalForm['signature'])
                                                <div class="mt-2">
                                                    <small class="text-info">
                                                        <i class="mdi mdi-information"></i> 
                                                        Default signature available in your profile.
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Current Approval Step Info -->
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information"></i>
                                            <strong>Approval Step:</strong> Step {{ $approvalStep->step }}
                                            <br>
                                            <strong>Approver:</strong> {{ auth()->user()->name }}
                                            <br>
                                            <small>By proceeding, you acknowledge that you have reviewed all documentation and evidence.</small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        @if($disposal && $approvalStep)
                            <button type="button" 
                                    wire:click="processApproval" 
                                    class="btn btn-{{ $approvalForm['decision'] === 'approve' ? 'success' : 'danger' }}" 
                                    wire:loading.attr="disabled"
                                    {{ !$approvalForm['decision'] ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="processApproval">
                                    @if($approvalForm['decision'] === 'approve')
                                        <i class="mdi mdi-check-circle"></i> Approve
                                    @elseif($approvalForm['decision'] === 'reject')
                                        <i class="mdi mdi-close-circle"></i> Reject
                                    @else
                                        <i class="mdi mdi-send"></i> Submit Decision
                                    @endif
                                </span>
                                <span wire:loading wire:target="processApproval">
                                    <i class="mdi mdi-loading mdi-spin"></i> Processing...
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery for conditional rendering -->
        <script>
            $(document).ready(function() {
                // Show/hide remarks section based on decision
                $('input[name^="decision-"]').on('change', function() {
                    var decision = $(this).val();
                    if (decision === 'reject') {
                        $('#remarks-section').slideDown();
                        $('#remarks-section textarea').prop('required', true);
                    } else {
                        $('#remarks-section').slideUp();
                        $('#remarks-section textarea').prop('required', false);
                    }
                });

                // Trigger change on initial load
                if ($('input[name="decision-reject"]:checked').length > 0) {
                    $('#remarks-section').show();
                }
            });

            // Listen for Livewire updates
            document.addEventListener('livewire:init', () => {
                Livewire.on('approval-processed', () => {
                    // Modal will close automatically via Livewire
                });
            });
        </script>
    @endif
</div>











