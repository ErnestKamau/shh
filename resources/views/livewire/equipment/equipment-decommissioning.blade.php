<div>
    @if($disposal)
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="mdi mdi-alert-outline"></i> Equipment Decommissioning
                </h5>
            </div>
            <div class="card-body">
                <!-- Message Alert -->
                @if($message)
                    <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                        {{ $message }}
                        <button type="button" class="btn-close" wire:click="dismissMessage"></button>
                    </div>
                @endif

                <!-- Equipment Information -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Equipment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Name:</strong> {{ $disposal->equipment->name }}</p>
                                <p class="mb-1"><strong>Equipment Number:</strong> {{ $disposal->equipment->equipment_number }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Status:</strong> {{ $disposal->equipment->status }}</p>
                                <p class="mb-1"><strong>Current Location:</strong> {{ $disposal->equipment->assigned_department ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Decommissioning Date:</strong> {{ $disposal->decommissioning_date ? $disposal->decommissioning_date->format('Y-m-d') : 'Not started' }}</p>
                                <p class="mb-1"><strong>Decommissioned By:</strong> {{ $disposal->decommissioned_by ? \App\User::find($disposal->decommissioned_by)->name : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Start Decommissioning Button -->
                @if(empty($checklist))
                    <div class="alert alert-info">
                        <strong>Action Required:</strong> Decommissioning has not been started yet. Click the button below to initiate the decommissioning process.
                    </div>
                    <button wire:click="startDecommissioning" class="btn btn-warning" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="startDecommissioning">
                            <i class="mdi mdi-play"></i> Start Decommissioning
                        </span>
                        <span wire:loading wire:target="startDecommissioning">
                            <i class="mdi mdi-loading mdi-spin"></i> Starting...
                        </span>
                    </button>
                @else
                    <!-- Decommissioning Checklist -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="mdi mdi-checkbox-marked-circle-outline"></i> Decommissioning Checklist
                                <small class="text-muted">(ISO 17025 Clause 6.4.13)</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                @foreach($checklist as $key => $item)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="checklist_{{ $key }}"
                                                       wire:click="toggleChecklistItem('{{ $key }}')"
                                                       {{ $item['completed'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="checklist_{{ $key }}">
                                                    {{ $item['label'] }}
                                                    @if($item['required'])
                                                        <span class="badge badge-danger ms-2">Required</span>
                                                    @endif
                                                </label>
                                            </div>
                                            @if($item['completed'])
                                                <span class="badge badge-success">
                                                    <i class="mdi mdi-check"></i> Completed
                                                    @if(isset($item['completed_by']))
                                                        by {{ $item['completed_by'] }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                        @if(isset($item['completed_at']))
                                            <small class="text-muted d-block mt-1">
                                                Completed at: {{ \Carbon\Carbon::parse($item['completed_at'])->format('Y-m-d H:i:s') }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Labeling -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="mdi mdi-tag"></i> Equipment Labeling
                            </h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Required Label:</strong> "OUT OF SERVICE - FOR DISPOSAL"</p>
                            
                            @if($disposal->equipment_labeled && $disposal->label_photo_path)
                                <div class="alert alert-success">
                                    <i class="mdi mdi-check-circle"></i> Equipment has been labeled
                                </div>
                                <div>
                                    <strong>Label Photo:</strong><br>
                                    <img src="{{ $disposal->label_photo_path }}" alt="Equipment Label" style="max-width: 300px; border-radius: 8px; margin-top: 10px;">
                                </div>
                            @else
                                <div class="form-group mt-3">
                                    <label class="form-label fw-bold">Upload Label Photo <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="labelPhoto" class="form-control" accept="image/*">
                                    @error('labelPhoto') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    <small class="text-muted">Upload a photo showing the "OUT OF SERVICE - FOR DISPOSAL" label on the equipment</small>
                                </div>
                                <button wire:click="uploadLabelPhoto" class="btn btn-primary mt-2" 
                                        wire:loading.attr="disabled" 
                                        {{ !$labelPhoto ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="uploadLabelPhoto">
                                        <i class="mdi mdi-upload"></i> Upload Photo
                                    </span>
                                    <span wire:loading wire:target="uploadLabelPhoto">
                                        <i class="mdi mdi-loading mdi-spin"></i> Uploading...
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Schedule Removal -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="mdi mdi-calendar-remove"></i> Schedule Removal
                            </h6>
                        </div>
                        <div class="card-body">
                            <p>Remove equipment from future calibration and maintenance schedules</p>
                            
                            @if($disposal->removed_from_calibration_schedule && $disposal->removed_from_maintenance_schedule)
                                <div class="alert alert-success">
                                    <i class="mdi mdi-check-circle"></i> Equipment removed from all schedules
                                </div>
                            @else
                                <button wire:click="removeFromSchedules" class="btn btn-warning" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="removeFromSchedules">
                                        <i class="mdi mdi-calendar-remove"></i> Remove from Schedules
                                    </span>
                                    <span wire:loading wire:target="removeFromSchedules">
                                        <i class="mdi mdi-loading mdi-spin"></i> Removing...
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Complete Decommissioning -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="mdi mdi-check-all"></i> Complete Decommissioning
                            </h6>
                        </div>
                        <div class="card-body">
                            <p>Once all required checklist items are completed, click below to finalize decommissioning.</p>
                            
                            @php
                                $allRequiredComplete = true;
                                foreach($checklist as $item) {
                                    if ($item['required'] && !$item['completed']) {
                                        $allRequiredComplete = false;
                                        break;
                                    }
                                }
                            @endphp

                            @if(!$allRequiredComplete)
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert"></i> All required checklist items must be completed before finalizing.
                                </div>
                            @endif

                            <button wire:click="completeDecommissioning" 
                                    class="btn btn-success" 
                                    wire:loading.attr="disabled"
                                    {{ !$allRequiredComplete ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="completeDecommissioning">
                                    <i class="mdi mdi-check-circle"></i> Complete Decommissioning
                                </span>
                                <span wire:loading wire:target="completeDecommissioning">
                                    <i class="mdi mdi-loading mdi-spin"></i> Completing...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            Disposal request not found.
        </div>
    @endif
</div>


