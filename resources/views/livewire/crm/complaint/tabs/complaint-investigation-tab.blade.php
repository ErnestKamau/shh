@php
    // Define steps and finalStep at the top to make it available throughout the template
    $steps = [['label' => '1. Cause of Complaint', 'icon' => 'mdi-magnify', 'step' => 1]];

    if ($this->carDecision === 'yes') {
        if ($this->ncDecision === 'yes') {
            $steps[] = ['label' => '2. Non-Conformance', 'icon' => 'mdi-alert-circle-outline', 'step' => 2];
            $steps[] = ['label' => '3. CAPA Plan', 'icon' => 'mdi-tools', 'step' => 3];
            $steps[] = ['label' => '4. Review & Close', 'icon' => 'mdi-check-all', 'step' => 4];
        } else {
            $steps[] = ['label' => '2. CAPA Plan', 'icon' => 'mdi-tools', 'step' => 2];
            $steps[] = ['label' => '3. Review & Close', 'icon' => 'mdi-check-all', 'step' => 3];
        }
    } else {
        $steps[] = ['label' => '2. Corrective Actions', 'icon' => 'mdi-flash-outline', 'step' => 2];
        $steps[] = ['label' => '3. Review & Close', 'icon' => 'mdi-check-all', 'step' => 3];
    }
    $finalStep = count($steps);
@endphp

<div x-data="investigationTab" x-init="init()">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="mr-3 d-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;background:var(--crm-success-light);color:var(--crm-success);">
                <i class="mdi mdi-briefcase-search-outline" style="font-size:1.25rem;"></i>
            </div>
            <div>
                <h4 class="mb-0" style="color: var(--crm-neutral-800); font-weight: 700; letter-spacing: -0.02em;">Complaint Resolution</h4>
                <p class="text-muted mb-0" style="font-size: 0.8125rem;">Investigate findings and determine resolution path</p>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap:8px; position: relative; z-index: 50; pointer-events: auto;">
            {{-- Unified Action Bar (Add/Edit/Cancel) --}}
            @if(($complaint->complaint_workflow <= 2 || ($complaint->complaint_workflow == 3 && !$car_required)) && $currentStep != $finalStep)
                @if($isEditing)
                    <button type="button" class="btn btn-soft-secondary btn-sm" wire:key="cancel-edit-btn" wire:click="discardChanges()" style="border-radius: var(--crm-radius-md); font-weight: 600;">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                @else
                    @if(!$complaint->is_closed)
                        @php
                            // Determine if this step has existing data to show Add vs Edit
                            $hasStepData = match((int) $currentStep) {
                                1 => !empty(strip_tags((string) $cause_of_complaint)),
                                2 => match(true) {
                                    $car_required && $ncDecision === 'yes' => false, // NC tab handles its own button
                                    $car_required => !empty(strip_tags((string) $corrective_action_taken)),
                                    default => !empty(strip_tags((string) $action_taken)) || !empty(strip_tags((string) $corrective_action_taken)),
                                },
                                default => false,
                            };
                        @endphp
                        @if($hasStepData)
                            <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold" wire:key="start-edit-btn" wire:click="toggleEdit()" style="border-radius: var(--crm-radius-md);">
                                <i class="mdi mdi-pencil-outline mr-1"></i> Edit
                            </button>
                        @else
                            <button type="button" class="btn btn-success btn-sm px-4 shadow-sm font-weight-bold" wire:key="start-add-btn" wire:click="toggleEdit()" style="border-radius: var(--crm-radius-md);">
                                <i class="mdi mdi-plus-circle-outline mr-1"></i> Add
                            </button>
                        @endif
                    @endif
                @endif
            @endif
        </div>
    </div>

    {{-- Stepper Progress Header --}}
    <div class="investigation-stepper mb-5 card shadow-sm py-4 border-0" style="background: var(--crm-neutral-50); border-radius: var(--crm-radius-lg);">
        
        <div class="d-flex justify-content-between align-items-center w-100 px-4 position-relative">
            {{-- Connecting line --}}
            <div class="position-absolute" style="top: 19px; left: 40px; right: 40px; height: 2px; background: var(--crm-neutral-200); z-index: 1;"></div>

            @foreach($steps as $s)
                @php
                    $isLocked = $s['step'] > 1 && !$checkpoint_completed;
                @endphp
                <div class="step-item {{ $currentStep == $s['step'] ? 'active' : ($currentStep > $s['step'] ? 'completed' : '') }}"
                     @if(!$isLocked) wire:click="goToStep({{ $s['step'] }})" @endif
                     style="cursor: {{ $isLocked ? 'not-allowed' : 'pointer' }}; z-index: 2; transition: all 0.3s ease; opacity: {{ $isLocked ? '0.5' : '1' }};"
                     @if($isLocked) title="Complete Step 1 decision to unlock" @endif>
                    <div class="step-circle shadow-sm">
                        @if($currentStep > $s['step'])
                            <i class="mdi mdi-check"></i>
                        @else
                            <i class="mdi {{ $s['icon'] }}" style="font-size: 1.1rem;"></i>
                        @endif
                    </div>
                    <span class="step-label mt-2">{{ $s['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Next Action Button (Moved from Global Header) --}}
    @php $nxt = $this->parent?->nextAction ?? null; @endphp
    @if(!$complaint->is_closed && $nxt)
        <div class="d-flex justify-content-end mb-4 px-2">
            <button class="btn btn-primary px-4 py-2 d-flex align-items-center shadow-sm" 
                wire:click="$parent.triggerNextAction"
                style="border-radius:6px; font-weight: 600; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border: none;">
                <i class="mdi {{ $nxt['icon'] }} mr-2" style="font-size: 1.15rem;"></i> 
                {{ $nxt['label'] }}
            </button>
        </div>
    @endif

    <!-- Step Content -->
    <div class="step-content-container animate__animated animate__fadeIn">
        
        @if($currentStep == 1)
            <!-- Phase 1: Cause & Observations -->
            <div class="lab-ledger-card {{ $isEditing ? 'border-primary' : '' }}" wire:key="investigation-step-1">
                <div class="lab-ledger-header crm-glass">
                    <span class="font-weight-bold text-dark">
                        <i class="mdi mdi-magnify mr-2 text-warning"></i>
                        STEP 1: CAUSE OF COMPLAINT
                    </span>
                </div>
                <div class="lab-ledger-body">
                    <p class="text-muted small mb-3">Detail the findings to determine the root cause of the complaint.</p>
                    @if($isEditing && $currentStep == 1)
                        <div class="col-md-12" wire:ignore wire:key="cause-editor-container">
                            <textarea id="cause_editor" class="investigation-editor" wire:key="cause_editor_area" x-init="initEditor($el, 'cause_of_complaint')" data-height="300">{{ $cause_of_complaint }}</textarea>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-7 mb-0">
                                <label class="investigation-label">Identified By</label>
                                <div wire:ignore>
                                    <select class="form-control capa-select2" id="root_cause_by_select" multiple required
                                            x-data="{
                                                init() {
                                                    let el = $(this.$el);
                                                    el.select2({ placeholder: 'Select personnel...', width: '100%' });
                                                    this.$watch('$wire.root_cause_by', value => {
                                                        el.val(value).trigger('change.select2');
                                                    });
                                                    el.on('change', () => {
                                                        this.$wire.set('root_cause_by', el.val());
                                                    });
                                                    el.val(this.$wire.root_cause_by).trigger('change.select2');
                                                }
                                            }">
                                        @foreach($users as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('root_cause_by') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-5 mb-0">
                                <label class="investigation-label">Investigated On</label>
                                <input type="date" class="form-control" wire:model="root_cause_date">
                            </div>
                        </div>
                    @else
                        <div class="investigation-text" wire:key="cause-display-text">
                            {!! $cause_of_complaint ? html_entity_decode($cause_of_complaint) : '<span class="text-muted italic">No cause of complaint recorded.</span>' !!}
                        </div>
                        @if($resolution && $resolution->root_cause_by)
                            <div class="crm-attribution-bar mt-2" wire:key="cause-attribution">
                                <div><span class="attr-label">Identified By:</span> <span class="attr-value">{{ $resolution->root_cause_by }}</span></div>
                                @if($resolution->root_cause_date)
                                    <div><span class="attr-label">On:</span> <span class="attr-value">{{ $resolution->root_cause_date->format('d M Y') }}</span></div>
                                @endif
                            </div>
                        @endif
                    @endif
                    @error('cause_of_complaint') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            @if($checkpoint_completed)
                <div class="my-4 d-flex align-items-center">
                    <span class="badge {{ $car_required ? 'badge-info' : 'badge-success' }} px-3 py-2" style="font-size: 0.85rem;">
                        <i class="mdi {{ $car_required ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline' }} mr-2"></i>
                        CLASSIFICATION: {{ $car_required ? 'CAR ASSIGNED' : 'NO CAR ASSIGNED' }}
                    </span>
                    @if($currentStep == 1)
                        <button type="button" class="btn btn-link btn-sm ml-3 text-muted" x-on:click="$wire.dispatch('show-decision-modal')">
                            <i class="mdi mdi-pencil-outline"></i> Change Decision
                        </button>
                    @endif
                </div>
            @endif

            @if($activeStep == 1 && !$checkpoint_completed && $isEditing)
                <div class="mt-4 d-flex justify-content-end">
                    <button type="button" class="btn btn-success btn-lg px-5 shadow-sm" x-on:click="saveCauseOnly()" style="border-radius: 8px; font-weight: 700;">
                        <i class="mdi mdi-check-circle-outline mr-2"></i> Save & Record Decision
                    </button>
                </div>
            @endif

        @elseif($carDecision === 'no' && $currentStep == 2)
            <!-- Phase 2: No-CAR Content (Containment & Corrective) -->
            <div wire:key="phase-nocar-wrapper-2">
                <div class="no-car-content animate__animated animate__fadeIn">
                <div class="lab-ledger-card mb-4 {{ $isEditing ? 'border-success' : '' }}">
                    <div class="lab-ledger-header crm-glass">
                        <span class="font-weight-bold text-dark">
                            <i class="mdi mdi-flash-outline mr-2 text-success"></i>
                            STEP 2: IMMEDIATE ACTIONS 
                        </span>
                    </div>
                    <div class="lab-ledger-body">
                    @if($isEditing)
                        <div class="col-md-12" wire:ignore wire:key="immediate-editor-container">
                            <textarea id="immediate_editor" class="investigation-editor" x-init="initEditor($el, 'action_taken')" data-height="300">{{ $action_taken }}</textarea>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-7 mb-0">
                                <label class="investigation-label">Identified By</label>
                                <div wire:ignore>
                                    <select class="form-control capa-select2" id="action_taken_by_select" multiple required
                                            x-data="{
                                                init() {
                                                    let el = $(this.$el);
                                                    el.select2({ placeholder: 'Select personnel...', width: '100%' });
                                                    this.$watch('$wire.action_taken_by', value => {
                                                        el.val(value).trigger('change.select2');
                                                    });
                                                    el.on('change', () => {
                                                        this.$wire.set('action_taken_by', el.val());
                                                    });
                                                    el.val(this.$wire.action_taken_by).trigger('change.select2');
                                                }
                                            }">
                                        @foreach($users as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('action_taken_by') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-5 mb-0">
                                <label class="investigation-label">Date Completed</label>
                                <input type="date" class="form-control" wire:model="action_taken_date">
                            </div>
                        </div>
                    @else
                        <div class="investigation-text" wire:key="immediate-display-text">
                            {!! $action_taken ? html_entity_decode($action_taken) : '<span class="text-muted italic">No containment actions logged.</span>' !!}
                        </div>
                    @endif
                    @error('action_taken') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="lab-ledger-card {{ $isEditing ? 'border-primary' : '' }}">
                <div class="lab-ledger-header crm-glass">
                    <span class="font-weight-bold text-dark">
                        <i class="mdi mdi-tools mr-2 text-primary"></i>
                        STEP 3: PROPOSED CORRECTIVE ACTIONS
                    </span>
                </div>
                <div class="lab-ledger-body">
                    @if($isEditing)
                        <div class="col-md-12" wire:ignore wire:key="corrective-editor-container">
                            <textarea id="corrective_editor" class="investigation-editor" x-init="initEditor($el, 'corrective_action_taken')" data-height="300">{{ $corrective_action_taken }}</textarea>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-7 mb-0">
                                <label class="investigation-label">Identified By</label>
                                <div wire:ignore>
                                    <select class="form-control capa-select2" id="corrective_action_by_select" multiple required
                                            x-data="{
                                                init() {
                                                    let el = $(this.$el);
                                                    el.select2({ placeholder: 'Select personnel...', width: '100%' });
                                                    this.$watch('$wire.corrective_action_by', value => {
                                                        el.val(value).trigger('change.select2');
                                                    });
                                                    el.on('change', () => {
                                                        this.$wire.set('corrective_action_by', el.val());
                                                    });
                                                    el.val(this.$wire.corrective_action_by).trigger('change.select2');
                                                }
                                            }">
                                        @foreach($users as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('corrective_action_by') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-5 mb-0">
                                <label class="investigation-label">Follow-up Date</label>
                                <input type="date" class="form-control" wire:model="corrective_action_date">
                            </div>
                        </div>
                    @else
                        <div class="investigation-text" wire:key="corrective-display-text">
                            {!! $corrective_action_taken ? html_entity_decode($corrective_action_taken) : '<span class="text-muted italic">No corrective measures defined.</span>' !!}
                        </div>
                    @endif
                        @error('corrective_action_taken') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-4" wire:click="goToPreviousStep">
                    <i class="mdi mdi-arrow-left mr-1"></i> Back
                </button>
                @if($isEditing)
                    <button type="button" class="btn btn-primary px-5 shadow-sm" wire:click="goToNextStep">
                        Next: Final Review <i class="mdi mdi-arrow-right ml-1"></i>
                    </button>
                    @endif
                </div>
            </div>

        @elseif($carDecision === 'yes' && $ncDecision === 'yes' && $currentStep == 2)
            <!-- Phase 2: Non-Conformance -->
            <div wire:key="phase-nc-wrapper-2">
                <div class="nc-component-wrapper">
                    @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintNcTab::class, ['complaintId' => $complaint->id, 'isEditing' => $isEditing], 'nc-tab-' . $complaint->id)
                </div>
                
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-4" wire:click="goToPreviousStep">
                        <i class="mdi mdi-arrow-left mr-1"></i> Back to Investigation
                    </button>
                </div>
            </div>

        @elseif($carDecision === 'yes' && (($ncDecision === 'yes' && $currentStep == 3) || ($ncDecision === 'no' && $currentStep == 2)))
            <!-- Phase 3: CAPA Plan -->
            <div wire:key="phase-capa-wrapper-{{ $currentStep }}">
                <div class="capa-component-wrapper">
                    @livewire(\App\Livewire\Crm\Complaint\Tabs\ComplaintCapaTab::class, [
                        'complaintId' => $complaint->id, 
                        'isEditing' => $isEditing,
                        'prefilledData' => $sharedData
                    ], 'capa-tab-' . $complaint->id)
                </div>
                
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-4" wire:click="goToPreviousStep">
                        <i class="mdi mdi-arrow-left mr-1"></i> Back
                    </button>
                </div>
            </div>

        @elseif($currentStep == $finalStep || $complaint->complaint_workflow >= 2)
            <!-- Phase Final: Case Review & Final Closure -->
            <div class="review-section animate__animated animate__fadeIn" wire:key="phase-review-final">
                
                <div class="row">
                    <div class="col-lg-12">
                        
                        {{-- 1. Complaint Details --}}
                        <div class="lab-ledger-card mb-4 border shadow-sm" style="border-radius: 8px;">
                            <div class="lab-ledger-header bg-light border-bottom py-2 px-3">
                                <span class="font-weight-bold text-dark">1. COMPLAINT DETAILS</span>
                            </div>
                            <div class="lab-ledger-body p-4">
                                <div class="row">
                                    {{-- 1. Nature of Complaint --}}
                                    <div class="col-md-12 mb-4">
                                        <label class="investigation-label">Nature of Complaint</label>
                                        <div class="d-flex align-items-center mb-2">
                                            @if($complaint->is_lab_related)
                                                <span class="crm-badge crm-badge-info mr-3 px-3 py-1">LAB RELATED</span>
                                            @endif
                                            <span class="text-dark font-weight-bold" style="font-size: 1.1rem; letter-spacing: -0.01em;">{{ $complaint->nature_of_complaint }}</span>
                                        </div>
                                        @if($complaint->is_lab_related)
                                            <div class="p-3 bg-soft-light border rounded-lg d-flex align-items-center mt-2 shadow-xs" style="gap: 40px; border-style: dashed !important;">
                                                <div>
                                                    <span class="text-muted small text-uppercase font-weight-bold mr-2">Test Item:</span>
                                                    <span class="text-dark font-weight-bold">{{ $complaint->test_item }}</span>
                                                </div>
                                                <div style="width: 1px; height: 20px; background: #e2e8f0;"></div>
                                                <div>
                                                    <span class="text-muted small text-uppercase font-weight-bold mr-2">Serial No:</span>
                                                    <span class="text-dark font-weight-bold" style="font-family: monospace;">{{ $complaint->report_serial_no }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- 2. Received From --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="investigation-label">Received From</label>
                                        <div class="p-3 bg-white border rounded shadow-xs" style="border-radius: 8px;">
                                            <div class="text-dark font-weight-bold mb-1" style="font-size: 1rem;">{{ $complaint->organization_name ?? ($complaint->client?->name ?? 'Anonymous') }}</div>
                                            <div class="text-muted small d-flex align-items-center">
                                                <i class="mdi mdi-account-circle-outline mr-1 text-primary" style="font-size: 1rem;"></i>
                                                {{ $complaint->contact_name ?: 'N/A' }} 
                                                @if($complaint->title_position)
                                                    <span class="mx-2 text-divider">|</span> {{ $complaint->title_position }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 3. Date --}}
                                    <div class="col-md-3 mb-4">
                                        <label class="investigation-label">Complaint Date</label>
                                        <div class="p-3 bg-white border rounded shadow-xs h-100 d-flex align-items-center" style="border-radius: 8px;">
                                            <div class="text-dark font-weight-bold">
                                                <i class="mdi mdi-calendar-clock mr-2 text-primary" style="font-size: 1.1rem;"></i>
                                                {{ $complaint->date?->format('d M Y') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 4. Priority --}}
                                    <div class="col-md-3 mb-4">
                                        <label class="investigation-label">Priority Level</label>
                                        <div class="p-3 bg-white border rounded shadow-xs h-100 d-flex align-items-center" style="border-radius: 8px;">
                                            <span class="crm-badge {{ $complaint->priority === 'High' ? 'crm-badge-danger' : ($complaint->priority === 'Medium' ? 'crm-badge-warning' : 'crm-badge-primary') }} w-100 text-center py-2" style="font-weight: 800; letter-spacing: 0.05em;">
                                                {{ strtoupper($complaint->priority) }} PRIORITY
                                            </span>
                                        </div>
                                    </div>

                                    {{-- 5. Mode of Delivery --}}
                                    <div class="col-md-12 mb-4">
                                        <label class="investigation-label">Mode of Delivery</label>
                                        <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                                            @forelse(explode(',', $complaint->mode_of_delivery) as $mode)
                                                @if(trim($mode))
                                                    <div class="px-3 py-1 bg-light border rounded-pill d-flex align-items-center shadow-xs">
                                                        <i class="mdi mdi-truck-delivery-outline mr-2 text-secondary"></i>
                                                        <span class="text-dark font-weight-medium small">{{ trim($mode) }}</span>
                                                    </div>
                                                @endif
                                            @empty
                                                <span class="text-muted italic small">N/A</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    
                                    {{-- 6. Description --}}
                                    <div class="col-md-12 mt-2">
                                        <label class="investigation-label">Complaint Description</label>
                                        <div class="p-3 bg-soft-light rounded shadow-xs border-left" style="border-left-width: 4px !important; border-left-color: var(--crm-success) !important;">
                                            <div class="text-dark small" style="line-height: 1.8; font-weight: 500;">
                                                <i class="mdi mdi-format-quote-open mr-2 text-success opacity-50" style="font-size: 1.2rem;"></i>
                                                <span style="display: inline;">{!! $complaint->description !!}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Investigation & Resolution --}}
                        <div class="lab-ledger-card mb-4 border shadow-sm" style="border-radius: 8px;">
                            <div class="lab-ledger-header bg-light border-bottom py-2 px-3">
                                <span class="font-weight-bold text-dark">2. INVESTIGATION & RESOLUTION</span>
                            </div>
                            <div class="lab-ledger-body p-3">
                                <div class="mb-3">
                                    <label class="investigation-label">Cause of Complaint</label>
                                    <div class="p-2 bg-soft-light rounded small mb-1 border-left" style="border-left-width: 3px !important; border-left-color: var(--crm-success) !important;">
                                        {!! html_entity_decode($cause_of_complaint ?: '<span class="text-muted italic">No findings recorded.</span>') !!}
                                    </div>
                                     @if($resolution && $resolution->root_cause_by)
                                         <div class="crm-attribution-bar">
                                             <div><span class="attr-label">Identified By:</span> <span class="attr-value">{{ $resolution->root_cause_by_names }}</span></div>
                                             @if($resolution->root_cause_date)
                                                 <div><span class="attr-label">On:</span> <span class="attr-value">{{ $resolution->root_cause_date->format('d M Y') }}</span></div>
                                             @endif
                                         </div>
                                     @endif
                                 </div>

                                 <div class="row mt-3">
                                     {{-- Immediate Action --}}
                                     <div class="col-md-6 border-right">
                                         <label class="investigation-label">Immediate Action Taken</label>
                                         <div class="p-2 bg-soft-light rounded border-left" style="border-left-width: 3px !important; border-left-color: var(--crm-success) !important;">
                                             {!! $action_taken ?: '<span class="text-muted italic">No action recorded.</span>' !!}
                                         </div>
                                         <div class="crm-attribution-bar">
                                             <div><span class="attr-label">Identified By:</span> <span class="attr-value">{{ $resolution->action_taken_by_names }}</span></div>
                                             <div><span class="attr-label">On:</span> <span class="attr-value">{{ $resolution->action_taken_date ? $resolution->action_taken_date->format('d M Y') : 'N/A' }}</span></div>
                                         </div>
                                     </div>
                                     {{-- Corrective Action --}}
                                     <div class="col-md-6">
                                         <label class="investigation-label">Corrective Action Taken</label>
                                         <div class="p-2 bg-soft-light rounded border-left" style="border-left-width: 3px !important; border-left-color: var(--crm-success) !important;">
                                             {!! $corrective_action_taken ?: '<span class="text-muted italic">No corrective action recorded.</span>' !!}
                                         </div>
                                         <div class="crm-attribution-bar">
                                             <div><span class="attr-label">Identified By:</span> <span class="attr-value">{{ $resolution->corrective_action_by_names }}</span></div>
                                             <div><span class="attr-label">On:</span> <span class="attr-value">{{ $resolution->corrective_action_date ? $resolution->corrective_action_date->format('d M Y') : 'N/A' }}</span></div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

                          {{-- 3. Closure Remarks --}}
                         @if(($complaint->complaint_workflow >= 4) || ($complaint->is_closed && ($resolution->client_remarks || $resolution->internal_remarks)))
                             <div class="lab-ledger-card mb-4 border shadow-sm" style="border-radius: 8px;">
                                 <div class="lab-ledger-header bg-light border-bottom py-2 px-3">
                                     <span class="font-weight-bold text-dark">3. CLOSURE REMARKS</span>
                                 </div>
                                 <div class="lab-ledger-body p-3">

                                      {{-- Client Remarks --}}
                                      <div class="mb-3">
                                          <label class="font-weight-bold small text-uppercase">Client Remarks</label>
                                          @if($complaint->is_closed)
                                              <div class="investigation-text">
                                                  {!! $client_remarks ?: '<span class="text-muted italic">No client remarks recorded.</span>' !!}
                                              </div>
                                          @else
                                               <div wire:ignore wire:key="res-client-remarks-container">
                                                   <textarea id="resolution_client_remarks_editor" class="form-control investigation-editor" data-height="180" x-init="initEditor($el, 'client_remarks')">{{ $client_remarks ?? '' }}</textarea>
                                               </div>
                                          @endif
                                      </div>

                                      {{-- Internal Remarks --}}
                                      <div class="mb-3">
                                          <label class="font-weight-bold small text-uppercase">Internal Review Remarks</label>
                                          @if($complaint->is_closed)
                                              <div class="investigation-text">
                                                  {!! $complaint_review_remarks ?: '<span class="text-muted italic">No internal review remarks recorded.</span>' !!}
                                              </div>
                                          @else
                                               <div wire:ignore wire:key="res-review-remarks-container">
                                                   <textarea id="resolution_review_remarks_editor" class="form-control investigation-editor" data-height="180" x-init="initEditor($el, 'complaint_review_remarks')">{{ $complaint_review_remarks ?? '' }}</textarea>
                                               </div>
                                          @endif
                                      </div>

                                     {{-- Final Action --}}
                                     @if(!$complaint->is_closed)
                                     <div class="text-right pt-2 border-top">
                                         <button type="button" class="btn btn-success px-5 shadow-sm"
                                                 x-on:click.prevent="syncResolutionEditorsAndClose()">
                                             <i class="mdi mdi-content-save-all mr-1"></i> Save & Close Complaint
                                         </button>
                                     </div>
                                     @endif
                                 </div>
                             </div>
                         @elseif($complaint->complaint_workflow == 2)
                            <div class="mt-4 text-right">
                                <button type="button" class="btn btn-success px-4 shadow-sm" x-on:click="$wire.dispatch('initiate-workflow-action', { action: 'approveNext' })">
                                    Approve & Advance <i class="mdi mdi-arrow-right ml-1"></i>
                                </button>
                            </div>
                        @endif

                    </div>
                </div>

    @endif
    </div>

    @script
    <script>
        Alpine.data('investigationTab', () => ({
            sections: {
                cause: true,
                immediate: true,
                corrective: true
            },
            lastSaved: '',
            autosaveInterval: null,
            toggleSection(section) {
                this.sections[section] = !this.sections[section];
            },
            triggerAutosave() {
                if (!this.$wire.isEditing) return;
                this._syncCurrentEditor();
                this.$wire.persistDraft();
            },
            _syncCurrentEditor() {
                if (typeof tinymce === 'undefined') return;
                
                const fieldMap = {
                    cause_editor: 'cause_of_complaint',
                    immediate_editor: 'action_taken',
                    corrective_editor: 'corrective_action_taken',
                    resolution_client_remarks_editor: 'client_remarks',
                    resolution_review_remarks_editor: 'complaint_review_remarks'
                };

                for (const [eid, property] of Object.entries(fieldMap)) {
                    // Robust check for editor existence and readiness
                    const editor = tinymce.get(eid);
                    if (editor && editor.initialized && !editor.isHidden()) {
                        try {
                            const content = editor.getContent();
                            this.$wire.set(property, content, false);
                        } catch (e) {
                            console.warn(`Sync failed for ${eid}:`, e);
                        }
                    }
                }
            },
            init() {
                this.$watch('$wire.isEditing', value => {
                    if (value) {
                        this.startAutosave();
                    } else {
                        this.stopAutosave();
                    }
                });

                Livewire.on('show-decision-modal', () => {
                    $('#decisionModal').modal('show');
                });

                Livewire.on('close-decision-modal', () => {
                    $('#decisionModal').modal('hide');
                });
            },
            
            saveCauseOnly() {
                this._syncCurrentEditor();
                this.$wire.saveCauseOnly();
            },
            saveFinal() {
                this._syncCurrentEditor();
                this.$wire.saveInvestigation();
            },

            syncResolutionEditorsAndClose() {
                this._syncCurrentEditor();
                this.$wire.saveAndCloseComplaint();
            },
            
            initEditor(el, property) {
                if (typeof tinymce === 'undefined') {
                    setTimeout(() => this.initEditor(el, property), 250);
                    return;
                }

                const id = el.id;
                
                // Defer to ensure DOM is fully ready
                setTimeout(() => {
                    if (tinymce.get(id)) {
                        tinymce.get(id).remove();
                    }

                    const expectedHeight = parseInt(el.getAttribute('data-height')) || 300;
                    const isReadonly = this.$wire.complaint && this.$wire.complaint.is_closed && (id.includes('resolution'));

                    tinymce.init({
                        selector: '#' + id,
                        height: expectedHeight,
                        menubar: false,
                        plugins: 'lists link',
                        toolbar: 'bold italic underline | bullist numlist | link',
                        branding: false,
                        promotion: false,
                        readonly: isReadonly,
                        skin: 'oxide',
                        content_style: 'body { font-family: Inter, -apple-system, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; padding: 12px; }',
                        setup: (editor) => {
                            editor.on('init', () => {
                                el.style.opacity = '1';
                            });
                            editor.on('blur', () => {
                                if (!isReadonly) {
                                    this.$wire.set(property, editor.getContent(), false);
                                }
                            });
                        }
                    });
                }, 50);
            },


            startAutosave() {
                if (this.autosaveInterval) return;
                this.autosaveInterval = setInterval(() => {
                    this.triggerAutosave();
                }, 30000); // 30 seconds
            },
            stopAutosave() {
                if (this.autosaveInterval) {
                    clearInterval(this.autosaveInterval);
                    this.autosaveInterval = null;
                }
            }
        }));
    </script>
    @endscript

    {{-- Decision Modal --}}
    @teleport('body')
    <div wire:ignore.self class="modal fade" id="decisionModal" tabindex="-1" role="dialog" aria-labelledby="decisionModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title font-weight-bold text-dark" id="decisionModalLabel">Record CAR Decision</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <p class="text-muted mb-4" style="font-size: 0.9rem;">Based on your initial findings, please determine if this complaint requires a formal Corrective Action Request (CAR).</p>
                    
                    <div class="bg-white border rounded p-4 shadow-sm mb-4">
                        <!-- CAR Switch -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="pr-3">
                                <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">Raise CAR</h6>
                                <small class="text-muted d-block">This will initiate a formal CAPA workflow upon investigation finalization.</small>
                            </div>
                            <div class="custom-control custom-switch custom-control-lg">
                                <input type="checkbox" id="modalToggleCar" class="custom-control-input" wire:model.live="car_required">
                                <label class="custom-control-label" for="modalToggleCar" style="cursor: pointer;"></label>
                            </div>
                        </div>

                        <!-- NCR Switch (Only if CAR is active) -->
                        @if($car_required)
                            <div class="pt-4 mt-4 border-top d-flex justify-content-between align-items-center" wire:transition>
                                <div class="pr-3">
                                    <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">Raise Non-conformance</h6>
                                    <small class="text-muted d-block">Include a root cause analysis as part of the NCR process.</small>
                                </div>
                                <div class="custom-control custom-switch custom-control-lg">
                                    <input type="checkbox" id="modalToggleNcr" class="custom-control-input" wire:model.live="ncr_required">
                                    <label class="custom-control-label" for="modalToggleNcr" style="cursor: pointer;"></label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light px-4 font-weight-bold" data-dismiss="modal text-muted">Cancel</button>
                    <button type="button" class="btn btn-primary px-5 shadow-sm font-weight-bold" wire:click="saveDecision">
                        Save Decision
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport

    <script src="{{ asset('tinymce/tinymce.min.js') }}"></script>
    <style>
        /* ============================================
           Investigation Tab — Local Styles
           Only styles unique to this component.
           Global utilities live in crm.css.
           ============================================ */

        /* Card wrappers (unique to this tab) */
        .investigation-summary-card {
            border: var(--crm-border);
            border-radius: var(--crm-radius-lg);
            background: #fff;
            overflow: hidden;
            box-shadow: var(--crm-shadow-card);
        }
        .investigation-section-header {
            background: var(--crm-neutral-50);
            padding: var(--crm-space-3) var(--crm-space-5);
            border-bottom: var(--crm-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .investigation-section-content { padding: var(--crm-space-5); }

        /* Field label — matches .investigation-label in crm.css, kept for legacy selectors */
        .investigation-label {
            font-size: var(--crm-font-muted);
            font-weight: 700;
            text-transform: uppercase;
            color: var(--crm-neutral-600);
            margin-bottom: var(--crm-space-2);
            display: block;
            letter-spacing: 0.025em;
        }

        /* Read-mode content box */
        .investigation-text {
            font-size: var(--crm-font-body);
            color: var(--crm-neutral-800);
            line-height: 1.6;
            background: var(--crm-neutral-50);
            padding: var(--crm-space-3) var(--crm-space-4);
            border-radius: var(--crm-radius-md);
            border: var(--crm-border);
            min-height: 48px;
        }

        .investigation-date-badge {
            font-size: var(--crm-font-muted);
            background: var(--crm-primary-light);
            color: var(--crm-primary);
            padding: var(--crm-space-1) var(--crm-space-3);
            border-radius: 9999px;
            font-weight: 600;
        }

        /* Stepper — uses crm-primary token; replaces old hardcoded rgba(66,153,225) */
        .investigation-stepper {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            width: 80px;
        }
        .step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--crm-neutral-200);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--crm-neutral-500);
        }
        .step-item.active .step-circle {
            background: var(--crm-primary);
            border-color: var(--crm-primary);
            color: #fff;
            box-shadow: 0 0 0 4px var(--crm-primary-light);
        }
        .step-item.completed .step-circle {
            background: var(--crm-success);
            border-color: var(--crm-success);
            color: #fff;
        }
        .step-label {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: var(--crm-neutral-500);
            text-align: center;
        }
        .step-item.active .step-label  { color: var(--crm-primary); }
        .step-item.completed .step-label { color: var(--crm-success); }

        .lab-ledger-card.border-primary {
            border: 2px solid var(--crm-primary) !important;
            box-shadow: 0 0 15px var(--crm-primary-light);
        }

        /* Decision option cards */
        .decision-option-card {
            display: block;
            border: 2px solid var(--crm-neutral-200);
            border-radius: var(--crm-radius-lg);
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .decision-option-card:hover {
            border-color: var(--crm-neutral-300);
            background: var(--crm-neutral-50);
        }
        .decision-option-card.active {
            border-color: var(--crm-primary);
            background: var(--crm-primary-light);
        }
        .option-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .option-title { font-weight: 700; color: var(--crm-neutral-900); font-size: 0.95rem; }
        .option-desc  { font-size: 0.75rem; color: var(--crm-neutral-500); }
        .option-check i { font-size: 1.25rem; }

        /* Timeline dots */
        .timeline-dot {
            transition: all 0.3s ease;
            box-shadow: 0 0 0 0 var(--crm-primary-light);
        }
        .timeline-item:hover .timeline-dot {
            transform: scale(1.1);
            box-shadow: 0 0 0 4px var(--crm-primary-light);
        }

        /* Misc utilities */
        .italic { font-style: italic; }
        .line-height-lg { line-height: 1.8; }

        /* CRM Attribution Bar (Identified/Verified by Metadata indicator) */
        .crm-attribution-bar {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            background-color: #f8fafc; /* Soft grey indicator background */
            border: 1px solid #e2e8f0; /* Soft grey border */
            border-left: 3px solid #cbd5e1; /* Sturdy grey left indicator border */
            border-radius: 6px;
            padding: 6px 12px;
            margin-top: 10px;
            font-size: 0.8rem;
            line-height: 1.4;
        }
        .crm-attribution-bar div {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .crm-attribution-bar .attr-label {
            font-weight: 600;
            color: #64748b; /* Slate-500 */
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
        }
        .crm-attribution-bar .attr-value {
            font-weight: 700;
            color: #334155; /* Slate-700 */
        }
    </style>
</div>
