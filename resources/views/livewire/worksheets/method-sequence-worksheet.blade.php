<div>
    <style>
        /* Vertical Timeline Styles */
        .vertical-timeline {
            position: relative;
            padding-left: 80px;
            padding-top: 10px;
        }

        .vertical-timeline::before {
            content: '';
            position: absolute;
            left: 29px;
            top: 40px;
            bottom: 40px;
            width: 4px;
            background: linear-gradient(180deg, #e5e7eb 0%, #d1d5db 100%);
            border-radius: 2px;
            box-shadow: 0 0 0 4px #f9fafb;
        }

        .timeline-stage {
            position: relative;
            margin-bottom: 24px;
            padding-left: 0;
        }

        .timeline-stage:last-child {
            margin-bottom: 0;
        }

        .stage-number-badge {
            position: absolute;
            left: -80px;
            top: 8px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            color: white;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 4px solid white;
        }

        .stage-number-badge.pending {
            background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
        }

        .stage-number-badge.in_progress {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            animation: pulse-blue 2s ease-in-out infinite;
        }

        .stage-number-badge.completed {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }

        @keyframes pulse-blue {
            0%, 100% {
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3), 0 0 0 4px white;
            }
            50% {
                box-shadow: 0 4px 20px rgba(59, 130, 246, 0.5), 0 0 0 4px white;
            }
        }
        
        .timer-safe {
            color: #10b981;
        }
        
        .timer-warning {
            color: #f59e0b;
        }
        
        .timer-expired {
            color: #ef4444;
        }
        
        .timeline-stage .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .timeline-stage .card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .timeline-stage .card-header {
            border-radius: 12px 12px 0 0 !important;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Result Stage Badge */
        .result-stage-badge {
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .result-stage-badge i {
            font-size: 14px;
        }

        .run-card {
            transition: all 0.3s ease;
        }
        
        .run-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .searchable-dropdown {
            position: relative;
        }
        
        .dropdown-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-results .dropdown-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        
        .dropdown-results .dropdown-item:hover {
            background: #f3f4f6;
        }
    </style>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Method Sequence Info -->
    <div class="alert alert-light border mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-chart-timeline-variant mdi-24px text-primary mr-2"></i>
                <div>
                    <strong>Method Sequence:</strong> {{ $methodSequence->name }}<br>
                    <small class="text-muted">{{ $methodSequence->description }}</small>
                </div>
            </div>
            <button wire:click="openCreateRunModal" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus"></i> Create New Run
            </button>
        </div>
    </div>

    <!-- Runs Section -->
    @if($runs && $runs->count() > 0)
        <div class="runs-section">
                @foreach($runs as $run)
                <div class="card run-card mb-3">
                    <!-- Collapsed Run Header -->
                    <div class="card-header d-flex justify-content-between align-items-center" 
                         style="cursor: pointer; background: #f8f9fa;">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 mr-3">
                                <i class="mdi mdi-run text-primary"></i>
                        {{ $run->run_name }}
                            </h5>
                            <span class="badge badge-{{ $run->status === 'completed' ? 'success' : ($run->status === 'in_progress' ? 'warning' : 'secondary') }} mr-2">
                            {{ ucfirst($run->status) }}
                        </span>
                            <span class="text-muted">
                                <i class="mdi mdi-calendar"></i> 
                                {{ $run->run_date ? $run->run_date->format('Y-m-d') : '-' }}
                            </span>
                            <span class="text-muted ml-3">
                                <i class="mdi mdi-account"></i> 
                                {{ $run->analyst->name ?? 'No analyst assigned' }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-danger mr-2" 
                                    wire:click.stop="deleteRun({{ $run->id }})"
                                    onclick="return confirm('Are you sure you want to delete this run?')">
                                <i class="mdi mdi-delete"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary mr-2" 
                                    wire:click.stop="editRun({{ $run->id }})">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" 
                                    wire:click.stop="toggleRunExpansion({{ $run->id }})">
                                <i class="mdi mdi-chevron-{{ in_array($run->id, $expandedRunIds) ? 'up' : 'down' }}"></i>
                    </button>
            </div>
        </div>

                    <!-- Expanded Run Body -->
                    @if(in_array($run->id, $expandedRunIds))
                <div class="card-body">
                            <!-- Samples in Run -->
                            <div class="mb-4">
                                <h6 class="mb-3">Samples ({{ $run->samples->count() }}):</h6>
                                <div class="d-flex flex-wrap">
                                    @foreach($run->samples as $runSample)
                                        <span class="badge badge-pill badge-info p-2 mr-2 mb-2">
                                    {{ $runSample->capturedResult->sample->sample_code }}
                                </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Alert Banners for Timer Warnings -->
                            @php
                                $hasWarnings = false;
                                $hasExpired = false;
                                foreach($run->stageData as $sd) {
                                    if($sd->status === 'in_progress') {
                                        $timerStatus = $sd->getTimerStatus();
                                        if($timerStatus === 'warning') $hasWarnings = true;
                                        if($timerStatus === 'expired') $hasExpired = true;
                                    }
                                }
                            @endphp
                            
                            @if($hasExpired)
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert"></i> 
                                    <strong>Alert:</strong> One or more stages have exceeded their duration limit!
                                </div>
                            @elseif($hasWarnings)
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-clock-alert"></i> 
                                    <strong>Warning:</strong> One or more stages are approaching their duration limit.
                    </div>
                            @endif

                            <!-- Vertical Timeline -->
                    <h6 class="mb-3">
                        <i class="mdi mdi-timeline"></i> Stages Timeline
                    </h6>
                            <div class="vertical-timeline">
                                @foreach($run->stageData->sortBy('stage.order') as $stageData)
                                    <div class="timeline-stage">
                                        <!-- Stage Number Badge -->
                                        <div class="stage-number-badge {{ $stageData->status }}">
                                            {{ $stageData->stage->order }}
                                        </div>

                                        <!-- Collapsed Stage Block -->
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center" 
                                                 style="cursor: pointer; background: {{ $stageData->status === 'completed' ? '#f0fdf4' : ($stageData->status === 'in_progress' ? '#eff6ff' : '#f9fafb') }};">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h6 class="mb-0 mr-2">{{ $stageData->stage->name }}</h6>
                                                        @if($stageData->stage->is_result_stage)
                                                            <span class="result-stage-badge">
                                                                <i class="mdi mdi-flask"></i>
                                                                Result Stage
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center mt-1">
                                                        <span class="badge badge-{{ $stageData->status === 'completed' ? 'success' : ($stageData->status === 'in_progress' ? 'warning' : 'secondary') }} mr-2">
                                                            {{ ucfirst($stageData->status) }}
                                                        </span>
                                                        
                                                        @if($stageData->status === 'in_progress' && $stageData->getRemainingTime() !== null)
                                                            @php
                                                                $remaining = $stageData->getRemainingTime();
                                                                $timerStatus = $stageData->getTimerStatus();
                                                                $timerClass = $timerStatus === 'expired' ? 'timer-expired' : ($timerStatus === 'warning' ? 'timer-warning' : 'timer-safe');
                                                            @endphp
                                                            <span class="badge {{ $timerClass }}">
                                                                <i class="mdi mdi-timer"></i>
                                                                {{ $remaining > 0 ? number_format($remaining, 1) . ' hrs remaining' : 'EXPIRED' }}
                                                            </span>
                                                        @elseif($stageData->duration_hours)
                                                            <span class="text-muted small">
                                                                <i class="mdi mdi-clock-outline"></i>
                                                                Duration: {{ $stageData->duration_hours }} hrs
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        wire:click.stop="toggleStageExpansion({{ $stageData->id }})">
                                                    <i class="mdi mdi-chevron-{{ in_array($stageData->id, $expandedStageIds) ? 'up' : 'down' }}"></i>
                                                </button>
                                            </div>

                                            <!-- Expanded Stage Form -->
                                            @if(in_array($stageData->id, $expandedStageIds))
                                                <div class="card-body">
                                                    <!-- Stage Timing Fields (3 per row) -->
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Date In</label>
                                                            <input type="date" 
                                                                   class="form-control form-control-sm" 
                                                                   value="{{ $stageData->date_in?->format('Y-m-d') }}"
                                                                   wire:blur="autoSaveStageField({{ $stageData->id }}, 'date_in', $event.target.value)">
                                            </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Time In</label>
                                                            <input type="time" 
                                                                   class="form-control form-control-sm" 
                                                                   value="{{ $stageData->time_in?->format('H:i') }}"
                                                                   wire:blur="autoSaveStageField({{ $stageData->id }}, 'time_in', $event.target.value)">
                                        </div>
                                                <div class="col-md-4">
                                                            <label class="form-label small">Started By</label>
                                                            <select class="form-control form-control-sm" 
                                                                    wire:change="autoSaveStageField({{ $stageData->id }}, 'started_by_user_id', $event.target.value)">
                                                                <option value="">Select...</option>
                                                                @foreach($users as $user)
                                                                    <option value="{{ $user->id }}" {{ $stageData->started_by_user_id == $user->id ? 'selected' : '' }}>
                                                                        {{ $user->name }}
                                                                    </option>
                                                        @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Date Out</label>
                                                            <input type="date" 
                                                                   class="form-control form-control-sm" 
                                                                   value="{{ $stageData->date_out?->format('Y-m-d') }}"
                                                                   wire:blur="autoSaveStageField({{ $stageData->id }}, 'date_out', $event.target.value)">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Time Out</label>
                                                            <input type="time" 
                                                                   class="form-control form-control-sm" 
                                                                   value="{{ $stageData->time_out?->format('H:i') }}"
                                                                   wire:blur="autoSaveStageField({{ $stageData->id }}, 'time_out', $event.target.value)">
                                                </div>
                                                <div class="col-md-4">
                                                            <label class="form-label small">Completed By</label>
                                                            <select class="form-control form-control-sm" 
                                                                    wire:change="autoSaveStageField({{ $stageData->id }}, 'completed_by_user_id', $event.target.value)">
                                                                <option value="">Select...</option>
                                                                @foreach($users as $user)
                                                                    <option value="{{ $user->id }}" {{ $stageData->completed_by_user_id == $user->id ? 'selected' : '' }}>
                                                                        {{ $user->name }}
                                                                    </option>
                                                        @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Equipment Section -->
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">Equipment</h6>
                                                        <div class="searchable-dropdown">
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   placeholder="Search equipment..."
                                                                   wire:model="equipmentSearch"
                                                                   wire:keyup="searchEquipments"
                                                                   wire:focus="$set('showEquipmentDropdown', true)">
                                                            
                                                            @if($showEquipmentDropdown && count($filteredEquipments) > 0)
                                                                <div class="dropdown-results">
                                                                    @foreach($filteredEquipments as $equipment)
                                                                        <div class="dropdown-item" 
                                                                             wire:click="autoSaveEquipmentUsage({{ $stageData->id }}, {{ $equipment['id'] }}, '{{ $equipment['name'] }}')">
                                                                            {{ $equipment['name'] }}
                                                                        </div>
                                                                    @endforeach
                                                </div>
                                            @endif
                                                        </div>
                                                        
                                                        @if($stageData->equipmentUsage->count() > 0)
                                                            <div class="mt-2">
                                                                @foreach($stageData->equipmentUsage as $equip)
                                                                    <span class="badge badge-secondary mr-2">{{ $equip->equipment_name }}</span>
                                                        @endforeach
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Media Section -->
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">Media</h6>
                                                        <div class="row">
                                                            <div class="col-md-5">
                                                                <div class="searchable-dropdown">
                                                                    <input type="text" 
                                                                           class="form-control form-control-sm" 
                                                                           placeholder="Search media..."
                                                                           wire:model="mediaSearch"
                                                                           wire:keyup="searchMedias"
                                                                           wire:focus="$set('showMediaDropdown', true)">
                                                                    
                                                                    @if($showMediaDropdown && count($filteredMedias) > 0)
                                                                        <div class="dropdown-results">
                                                                            @foreach($filteredMedias as $media)
                                                                                <div class="dropdown-item" 
                                                                                     wire:click="selectMedia({{ $stageData->id }}, {{ $media['id'] }}, '{{ $media['name'] }}')">
                                                                                    {{ $media['name'] }}
                                                                                </div>
                                                                            @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                                            <div class="col-md-3">
                                                                <input type="number" 
                                                                       step="0.01"
                                                                       class="form-control form-control-sm" 
                                                                       placeholder="Volume"
                                                                       id="media-volume-{{ $stageData->id }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" 
                                                                       class="form-control form-control-sm" 
                                                                       placeholder="Unit"
                                                                       id="media-unit-{{ $stageData->id }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" 
                                                                       class="form-control form-control-sm" 
                                                                       placeholder="Batch"
                                                                       id="media-batch-{{ $stageData->id }}">
                                                            </div>
                                </div>

                                                        @if($stageData->mediaUsage->count() > 0)
                                                            <table class="table table-sm mt-2">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Media</th>
                                                                        <th>Volume</th>
                                                                        <th>Unit</th>
                                                                        <th>Batch</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                                    @foreach($stageData->mediaUsage as $media)
                                                                        <tr>
                                                                            <td>{{ $media->media_name }}</td>
                                                                            <td>{{ $media->volume }}</td>
                                                                            <td>{{ $media->unit }}</td>
                                                                            <td>{{ $media->batch_number }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                                        @endif
                                                    </div>

                                                    <!-- Controls Section -->
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">Controls</h6>
                                                        <div class="row">
                                                            <div class="col-md-5">
                                                                <div class="searchable-dropdown">
                                                                    <input type="text" 
                                                                           class="form-control form-control-sm" 
                                                                           placeholder="Search controls..."
                                                                           wire:model="controlSearch"
                                                                           wire:keyup="searchControls"
                                                                           wire:focus="$set('showControlDropdown', true)">
                                                                    
                                                                    @if($showControlDropdown && count($filteredControls) > 0)
                                                                        <div class="dropdown-results">
                                                                            @foreach($filteredControls as $control)
                                                                                <div class="dropdown-item" 
                                                                                     wire:click="selectControl({{ $stageData->id }}, {{ $control['id'] }}, '{{ $control['name'] }}')">
                                                                                    {{ $control['name'] }}
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="number" 
                                                                       step="0.01"
                                                                       class="form-control form-control-sm" 
                                                                       placeholder="Volume"
                                                                       id="control-volume-{{ $stageData->id }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" 
                                                                       class="form-control form-control-sm" 
                                                                       placeholder="Unit"
                                                                       id="control-unit-{{ $stageData->id }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" 
                                                                       class="form-control form-control-sm" 
                                                                       placeholder="Batch"
                                                                       id="control-batch-{{ $stageData->id }}">
                                                            </div>
                                        </div>

                                        @if($stageData->controlUsage->count() > 0)
                                                            <table class="table table-sm mt-2">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Control</th>
                                                                        <th>Volume</th>
                                                                        <th>Unit</th>
                                                                        <th>Batch</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                                    @foreach($stageData->controlUsage as $control)
                                                                        <tr>
                                                                            <td>{{ $control->control_name }}</td>
                                                                            <td>{{ $control->volume }}</td>
                                                                            <td>{{ $control->unit }}</td>
                                                                            <td>{{ $control->batch_number }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                        @endif
                                                    </div>

                                                    <!-- Results Section (if result stage) -->
                                                    @if($stageData->stage->is_result_stage)
                                                        <div class="mb-3">
                                                            <h6 class="mb-2">Results</h6>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small">Result</label>
                                                                    <input type="text" 
                                                                           class="form-control form-control-sm" 
                                                                           placeholder="Enter result"
                                                                           id="result-{{ $stageData->id }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small">Remark (Pass/Fail)</label>
                                                                    <select class="form-control form-control-sm" 
                                                                            id="remark-{{ $stageData->id }}"
                                                                            wire:change="updateResult({{ $stageData->id }}, document.getElementById('result-{{ $stageData->id }}').value, $event.target.value)">
                                                                        <option value="">Select...</option>
                                                                        <option value="Pass">Pass</option>
                                                                        <option value="Fail">Fail</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                        </div>
                            </div>
                        @endforeach
                    </div>
                        </div>
                    @endif
                </div>
            @endforeach
            </div>
    @else
        <div class="alert alert-info">
            <i class="mdi mdi-information"></i> 
            No runs created yet. Click "Create New Run" to start processing samples.
        </div>
        
        @if($availableSamples->count() > 0)
            <div class="alert alert-warning">
                <strong>{{ $availableSamples->count() }} samples</strong> are available for this method sequence.
            </div>
        @endif
    @endif

    <!-- Create Run Modal -->
    @if($showCreateRunModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus"></i> Create New Run
                        </h5>
                        <button type="button" class="close" wire:click="$set('showCreateRunModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Run Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   wire:model="newRunName"
                                   placeholder="e.g., Run 1, Morning Batch, etc.">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Analyst <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="selectedAnalystId">
                                <option value="">Select Analyst...</option>
                                @foreach($analysts as $analyst)
                                    <option value="{{ $analyst->id }}">{{ $analyst->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Run Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control" 
                                   wire:model="runDate"
                                   value="{{ now()->toDateString() }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Select Samples <span class="text-danger">*</span></label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @if($availableSamples->count() > 0)
                                    @foreach($availableSamples as $sample)
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   wire:model="selectedSamples" 
                                                   value="{{ $sample->id }}"
                                                   id="sample-{{ $sample->id }}">
                                            <label class="form-check-label" for="sample-{{ $sample->id }}">
                                                {{ $sample->sample->sample_code }} - 
                                                {{ $sample->analysisElement->analyte->name ?? 'Unknown Analyte' }}
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted mb-0">All samples have been assigned to runs.</p>
                                @endif
                            </div>
                            <small class="text-muted">{{ count($selectedSamples) }} sample(s) selected</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCreateRunModal', false)">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="createRun">
                            <i class="mdi mdi-check"></i> Create Run
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Run Modal -->
    @if($showEditRunModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-pencil"></i> Edit Run
                        </h5>
                        <button type="button" class="close" wire:click="$set('showEditRunModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Run Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                               class="form-control" 
                                   wire:model="editRunName"
                                   placeholder="e.g., Run 1, Morning Batch, etc.">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Analyst <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="editRunAnalystId">
                                <option value="">Select Analyst...</option>
                                @foreach($analysts as $analyst)
                                    <option value="{{ $analyst->id }}">{{ $analyst->name }}</option>
                                    @endforeach
                                </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Run Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control" 
                                   wire:model="editRunDate">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showEditRunModal', false)">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="updateRun">
                            <i class="mdi mdi-check"></i> Update Run
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Auto-refresh timers every minute
        setInterval(function() {
            @this.call('loadData');
        }, 60000);
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.searchable-dropdown')) {
                @this.set('showEquipmentDropdown', false);
                @this.set('showMediaDropdown', false);
                @this.set('showControlDropdown', false);
            }
        });
    </script>
</div>
