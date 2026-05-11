<div>
    @if($disposal)
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="mdi mdi-alert-outline"></i> {{ __('equipment.equipment_decommissioning') }}
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
                        <h6 class="mb-0">{{ __('equipment.equipment_information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>{{ __('equipment.name') }}:</strong> {{ $disposal->equipment->name }}</p>
                                <p class="mb-1"><strong>{{ __('equipment.equipment_number') }}:</strong> {{ $disposal->equipment->equipment_number }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>{{ __('equipment.status') }}:</strong> {{ $disposal->equipment->status }}</p>
                                <p class="mb-1"><strong>{{ __('equipment.current_location') }}:</strong> {{ $disposal->equipment->assigned_department ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>{{ __('equipment.decommissioning_date') }}:</strong> {{ $disposal->decommissioning_date ? $disposal->decommissioning_date->format('Y-m-d') : __('equipment.not_started') }}</p>
                                <p class="mb-1"><strong>{{ __('equipment.decommissioned_by') }}:</strong> {{ $disposal->decommissioned_by ? \App\User::find($disposal->decommissioned_by)->name : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Start Decommissioning Button -->
                @if(empty($checklist))
                    <div class="alert alert-info">
                        <strong>{{ __('equipment.action_required') }}:</strong> {{ __('equipment.decommissioning_not_started_message') }}
                    </div>
                    <button wire:click="startDecommissioning" class="btn btn-warning" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="startDecommissioning">
                            <i class="mdi mdi-play"></i> {{ __('equipment.start_decommissioning') }}
                        </span>
                        <span wire:loading wire:target="startDecommissioning">
                            <i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.starting') }}
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
                                                        <span class="badge badge-danger ms-2">{{ __('equipment.required') }}</span>
                                                    @endif
                                                </label>
                                            </div>
                                            @if($item['completed'])
                                                <span class="badge badge-success">
                                                    <i class="mdi mdi-check"></i> {{ __('equipment.completed') }}
                                                    @if(isset($item['completed_by']))
                                                        {{ __('equipment.by') }} {{ $item['completed_by'] }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                        @if(isset($item['completed_at']))
                                            <small class="text-muted d-block mt-1">
                                                {{ __('equipment.completed_at') }}: {{ \Carbon\Carbon::parse($item['completed_at'])->format('Y-m-d H:i:s') }}
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
                                <i class="mdi mdi-tag"></i> {{ __('equipment.equipment_labeling') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p><strong>{{ __('equipment.required_label') }}:</strong> "{{ __('equipment.out_of_service_for_disposal') }}"</p>
                            
                            @if($disposal->equipment_labeled && $disposal->label_photo_path)
                                <div class="alert alert-success">
                                    <i class="mdi mdi-check-circle"></i> {{ __('equipment.equipment_has_been_labeled') }}
                                </div>
                                <div>
                                    <strong>{{ __('equipment.label_photo') }}:</strong><br>
                                    <img src="{{ $disposal->label_photo_path }}" alt="Equipment Label" style="max-width: 300px; border-radius: 8px; margin-top: 10px;">
                                </div>
                            @else
                                <div class="form-group mt-3">
                                    <label class="form-label fw-bold">{{ __('equipment.upload_label_photo') }} <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="labelPhoto" class="form-control" accept="image/*">
                                    @error('labelPhoto') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    <small class="text-muted">{{ __('equipment.upload_label_photo_instruction') }}</small>
                                </div>
                                <button wire:click="uploadLabelPhoto" class="btn btn-primary mt-2" 
                                        wire:loading.attr="disabled" 
                                        {{ !$labelPhoto ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="uploadLabelPhoto">
                                        <i class="mdi mdi-upload"></i> {{ __('equipment.upload_photo') }}
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
                                <i class="mdi mdi-calendar-remove"></i> {{ __('equipment.schedule_removal') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p>{{ __('equipment.remove_from_future_schedules') }}</p>
                            
                            @if($disposal->removed_from_calibration_schedule && $disposal->removed_from_maintenance_schedule)
                                <div class="alert alert-success">
                                    <i class="mdi mdi-check-circle"></i> {{ __('equipment.equipment_removed_from_all_schedules') }}
                                </div>
                            @else
                                <button wire:click="removeFromSchedules" class="btn btn-warning" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="removeFromSchedules">
                                        <i class="mdi mdi-calendar-remove"></i> Remove from Schedules
                                    </span>
                                    <span wire:loading wire:target="removeFromSchedules">
                                        <i class="mdi mdi-loading mdi-spin"></i> {{ __('equipment.removing') }}
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Complete Decommissioning -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="mdi mdi-check-all"></i> {{ __('equipment.complete_decommissioning') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p>{{ __('equipment.complete_decommissioning_description') }}</p>
                            
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
                                    <i class="mdi mdi-alert"></i> {{ __('equipment.all_required_checklist_items_must_be_completed') }}
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
            {{ __('equipment.disposal_request_not_found') }}
        </div>
    @endif
</div>


