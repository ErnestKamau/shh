<div>
    {{-- Decision Gate 2: NCR/Why-Why Toggle --}}
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                        <i class="mdi mdi-clipboard-search-outline mr-2 text-warning"></i>
                        Does this complaint require a Non-Conformance Report (NCR) / Why-Why Analysis?
                    </span>
                    <div class="text-muted" style="font-size: 0.78rem; margin-top: 2px;">
                        Enable if the root cause is unknown or complex (Incident 1). The CAPA form will be locked until the Why-Why analysis is completed first.
                    </div>
                </div>
                <div class="custom-control custom-switch ml-4" style="flex-shrink:0;">
                    <input type="checkbox" class="custom-control-input" id="ncr_required_toggle"
                        wire:model.live="ncr_required"
                        {{ in_array($step_status, ['ncr_verification', 'capa_completed']) ? 'disabled' : '' }}>
                    <label class="custom-control-label font-weight-bold text-{{ $ncr_required ? 'warning' : 'muted' }}" for="ncr_required_toggle">
                        {{ $ncr_required ? 'NCR Required' : 'No NCR Required' }}
                    </label>
                </div>
            </div>
            @if($ncr_required && $step_status === 'ncr_pending_init')
                <div class="alert alert-warning mb-0 mt-2 py-2">
                    <i class="mdi mdi-alert-outline mr-1"></i>
                    <strong>NCR Path:</strong> Please complete Assignment Details (Section 1) and Risk Assessment (Section 2) to unlock the Why-Why Root Cause Analysis.
                </div>
            @endif
        </div>
    </div>

    @php
        $lockSec12 = $ncr_required && in_array($step_status, ['ncr_verification', 'capa_completed']);
        $lockSec3 = $ncr_required && $step_status === 'ncr_pending_init';
        $ncrActive = in_array($step_status, ['ncr_in_progress', 'ncr_verification', 'capa_completed']) || (!$ncr_required && $isCapaSaved);
        $capaDone = in_array($step_status, ['ncr_verification', 'capa_completed']) || (!$ncr_required && $isCapaSaved);
    @endphp

    @if($activeView == 'ncr' && $isNcrSaved)
    <template x-teleport=".workflow-actions">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-danger font-weight-bold shadow-sm d-flex align-items-center ml-2" wire:click="downloadNcrReport" style="border-radius: 20px; font-size: 0.85rem; padding: 6px 14px; height: 32px;">
                <i class="mdi mdi-file-pdf text-danger mr-1" style="font-size: 1.1rem;"></i> Export to PDF
            </button>
            <button type="button" class="btn btn-success font-weight-bold shadow-sm d-flex align-items-center ml-2" wire:click="returnToCapa" style="border-radius: 20px; font-size: 0.85rem; padding: 6px 14px; letter-spacing: 0.5px; height: 32px;">
                <i class="mdi mdi-arrow-left-circle-outline mr-1" style="font-size: 1.1rem; margin-top:-1px;"></i> Return to CAPA
            </button>
        </div>
    </template>
    @endif

    {{-- Sub-Navigation for Sequential Steps --}}
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px; overflow: hidden;">

        <div class="card-body p-0">
            <div class="d-flex bg-light">
                <div 
                    wire:click="switchView('capa')" 
                    class="flex-fill py-3 px-4 text-center cursor-pointer border-right transition-all {{ $activeView == 'capa' ? 'bg-white shadow-sm' : 'text-muted' }}"
                    style="cursor: pointer;"
                >
                    <div class="d-flex align-items-center justify-content-center">
                        <span class="badge {{ $capaDone ? 'badge-success' : 'badge-primary' }} mr-2 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px; height:24px;">
                            @if($capaDone) <i class="mdi mdi-check"></i> @else 1 @endif
                        </span>
                        <span class="font-weight-bold" style="font-size: 0.9rem;">Step 1: Action Plan (CAPA)</span>
                    </div>
                </div>
                <div 
                    @if($ncrActive) wire:click="switchView('ncr')" @endif
                    class="flex-fill py-3 px-4 text-center transition-all {{ $activeView == 'ncr' ? 'bg-white shadow-sm' : 'text-muted' }} {{ $ncrActive ? 'cursor-pointer' : 'bg-light' }}"
                    style="{{ $ncrActive ? 'cursor: pointer;' : 'cursor: not-allowed; opacity: 0.6;' }}"
                >
                    <div class="d-flex align-items-center justify-content-center">
                        <span class="badge {{ in_array($step_status, ['ncr_verification', 'capa_completed']) || (!$ncr_required && $root_cause) ? 'badge-success' : ($step_status === 'ncr_in_progress' || (!$ncr_required && $isCapaSaved) ? 'badge-primary' : 'badge-secondary') }} mr-2 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px; height:24px;">
                            @if(in_array($step_status, ['ncr_verification', 'capa_completed']) || (!$ncr_required && $root_cause)) <i class="mdi mdi-check"></i> @elseif(!$ncrActive) <i class="mdi mdi-lock" style="font-size: 0.8rem;"></i> @else 2 @endif
                        </span>
                        <span class="font-weight-bold" style="font-size: 0.9rem;">Step 2: Why Why Analysis</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Content Wrapper --}}
    <div class="modern-capa-container" style="font-family: 'Inter', sans-serif;" x-data="capaForm">
        
        {{-- Step 1: CAPA Actions --}}
        @if($activeView == 'capa')
            <form wire:submit.prevent="saveCapaInit">
                
                {{-- Header with Title and CAR No --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-1">Corrective and Preventive Action Plan</h4>
                        <p class="text-muted small mb-0">Formal operational plan to address identification findings.</p>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div x-data="{ lastSaved: '' }" x-on:autosave-completed.window="lastSaved = $event.detail.time" class="mr-3 text-muted small">
                            <span x-show="lastSaved" class="bg-soft-success text-success px-2 py-1 rounded" style="font-size: 0.7rem;">
                                <i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i> Autosaved: <span x-text="lastSaved"></span>
                            </span>
                            <span wire:loading wire:target="performAutosave" class="text-primary" style="font-size: 0.7rem;">
                                <i class="mdi mdi-loading mdi-spin mr-1"></i> Saving...
                            </span>
                        </div>
                        @if(!$isEditing)
                            <button type="button" class="btn btn-primary btn-sm rounded-pill font-weight-bold px-3 shadow-sm" wire:click="toggleEdit">
                                <i class="mdi mdi-pencil-outline mr-1"></i> Edit CAPA
                            </button>
                        @endif
                        <span class="badge bg-soft-primary text-primary px-3 py-2 border" style="font-size: 0.9rem; font-weight: 700;">
                            <i class="mdi mdi-ticket-confirmation-outline mr-1"></i>
                            {{ $resolution->car_no ?? 'NEW-CAR' }}
                        </span>
                    </div>
                </div>

                <fieldset {{ $lockSec12 ? 'disabled' : '' }}>
                {{-- Section 1: Assignment Details --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; {{ $lockSec12 ? 'opacity:0.8; background-color:#f8fafc;' : '' }}">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h6 class="font-weight-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">Section 1: Assignment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-dark small">DATE ISSUED</label>
                                <input type="date" wire:model.live.debounce.2000ms="date_issued" class="form-control form-control-lg bg-white border shadow-sm" style="font-weight: 600;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-dark small">PROPOSED CLOSE OUT DATE</label>
                                <input type="date" wire:model.live.debounce.2000ms="proposed_close_out_date" class="form-control form-control-lg bg-white border shadow-sm" style="font-weight: 600;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-dark small">ISSUED TO</label>
                                <input type="text" wire:model.live.debounce.2000ms="issued_to" class="form-control form-control-lg bg-white border shadow-sm" placeholder="Department or Officer Name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-dark small">ISSUED BY</label>
                                <input type="text" wire:model.live.debounce.2000ms="issued_by" class="form-control form-control-lg bg-white border shadow-sm" placeholder="Authorizing Manager">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Risk Assessment --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; {{ $lockSec12 ? 'opacity:0.8; background-color:#f8fafc;' : '' }}">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h6 class="font-weight-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">Section 2: Risk Assessment</h6>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold text-dark small">RISK(S) LEVEL</label>
                                <select wire:model.live.debounce.2000ms="risk_level" class="form-control form-control-lg bg-white border shadow-sm font-weight-bold">
                                    <option value="">Select...</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold text-dark small">REF. CLAUSE</label>
                                <input type="text" wire:model.live.debounce.2000ms="ref_clause" class="form-control form-control-lg bg-white border shadow-sm" placeholder="e.g. ISO/IEC 17025">
                            </div>
                            <div class="col-md-4 mb-3 pb-2">
                                <label class="form-label font-weight-bold text-dark small d-block mb-3">CAR TYPE</label>
                                <div class="d-flex align-items-center">
                                    <div class="custom-control custom-radio custom-control-inline mr-4">
                                        <input type="radio" id="modern_car_major" name="car_type" wire:model.live.debounce.2000ms="car_type" value="Major" class="custom-control-input">
                                        <label class="custom-control-label font-weight-bold text-danger" for="modern_car_major">MAJOR</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="modern_car_minor" name="car_type" wire:model.live.debounce.2000ms="car_type" value="Minor" class="custom-control-input">
                                        <label class="custom-control-label font-weight-bold text-warning" for="modern_car_minor">MINOR</label>
                                    </div>
                                </div>
                            </div>

                            @if($ncr_required)
                            <div class="col-md-12 mb-3 mt-3">
                                <label class="form-label font-weight-bold text-dark small">DETAILS OF NON-CONFORMANCE</label>
                                @if($lockSec12 || !$isEditing)
                                    <div class="bg-white p-3 border rounded shadow-sm" style="min-height: 100px; line-height: 1.6;">
                                        {!! $details_of_non_conformance ?: '<span class="text-muted italic">No details provided.</span>' !!}
                                    </div>
                                @else
                                    <div wire:ignore>
                                        <textarea id="capa_details_editor" class="capa-rich-editor">{{ $details_of_non_conformance }}</textarea>
                                    </div>
                                    @error('details_of_non_conformance') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                </fieldset>

                {{-- Section 3: Action Plan & Verification --}}
                <div class="card shadow-sm border-0 mb-5 position-relative" style="border-radius: 12px; border-left: 5px solid #10b981 !important;">
                    @if($lockSec3)
                        <div class="position-absolute w-100 h-100 bg-white" style="top:0; left:0; z-index:10; opacity:0.8; border-radius: 12px;"></div>
                        <div class="position-absolute w-100 text-center" style="top:40%; left:0; z-index:11;">
                            <i class="mdi mdi-lock text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted font-weight-bold mt-2">Locked until NCR analysis is completed</p>
                        </div>
                    @endif
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h6 class="font-weight-bold text-uppercase text-success" style="font-size: 0.75rem; letter-spacing: 0.05em;">Section 3: Action Plan & Verification</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 pt-2">
                            <label class="form-label font-weight-bold text-dark small d-block mb-3">
                                <span class="bg-soft-primary text-primary px-2 py-1 rounded mr-2">C</span>
                                ROOT CAUSE OF PROBLEM <span class="text-danger">*</span>
                            </label>
                            @if(!$isEditing)
                                <div class="bg-white p-3 border rounded shadow-sm" style="min-height: 100px; line-height: 1.6;">
                                    {!! $root_cause ?: '<span class="text-muted italic">No root cause documented.</span>' !!}
                                </div>
                            @else
                                <div wire:ignore>
                                    <textarea id="capa_root_cause_editor" class="capa-rich-editor">{{ $root_cause }}</textarea>
                                </div>
                                @error('root_cause') <span class="text-danger small mt-2 d-block">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label font-weight-bold text-dark small d-block mb-3">
                                <span class="bg-soft-primary text-primary px-2 py-1 rounded mr-2">C2</span>
                                CORRECTIVE ACTION (CA) <span class="text-danger">*</span>
                            </label>
                            @if(!$isEditing)
                                <div class="bg-white p-3 border rounded shadow-sm" style="min-height: 100px; line-height: 1.6;">
                                    {!! $capa_corrective_action ?: '<span class="text-muted italic">No corrective action documented.</span>' !!}
                                </div>
                            @else
                                <div wire:ignore>
                                    <textarea id="capa_corrective_action_editor" class="capa-rich-editor">{{ $capa_corrective_action }}</textarea>
                                </div>
                                @error('capa_corrective_action') <span class="text-danger small mt-2 d-block">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <hr class="my-5 border-light">

                        <div class="mb-4">
                            <label class="form-label font-weight-bold text-dark small d-block mb-3">
                                <span class="bg-soft-warning text-warning px-2 py-1 rounded mr-2">A</span>
                                ACTION TAKEN <span class="text-danger">*</span>
                            </label>
                            @if(!$isEditing)
                                <div class="bg-white p-3 border rounded shadow-sm" style="min-height: 100px; line-height: 1.6;">
                                    {!! $action_taken ?: '<span class="text-muted italic">No action taken documented.</span>' !!}
                                </div>
                            @else
                                <div wire:ignore>
                                    <textarea id="capa_action_taken_editor" class="capa-rich-editor">{{ $action_taken }}</textarea>
                                </div>
                                @error('action_taken') <span class="text-danger small mt-2 d-block">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <hr class="my-5 border-light">

                        <div class="mb-4 pt-2">
                            <label class="form-label font-weight-bold text-dark small d-block mb-3">
                                <span class="bg-soft-success text-success px-2 py-1 rounded mr-2">B</span>
                                ACCEPTANCE OF CORRECTIVE ACTION AND ACTION TAKEN <span class="text-danger">*</span>
                            </label>
                            @if(!$isEditing)
                                <div class="bg-white p-3 border rounded shadow-sm" style="min-height: 100px; line-height: 1.6;">
                                    {!! $acceptance ?: '<span class="text-muted italic">No acceptance details documented.</span>' !!}
                                </div>
                            @else
                                <div wire:ignore>
                                    <textarea id="capa_acceptance_editor" class="capa-rich-editor">{{ $acceptance }}</textarea>
                                </div>
                                @error('acceptance') <span class="text-danger small mt-2 d-block">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div class="bg-soft-success p-4 rounded mb-4 shadow-xs mt-4">
                            <h6 class="font-weight-bold text-success mb-3 text-uppercase" style="font-size: 0.75rem;">Verification</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-dark text-uppercase mb-1">Verified By (Name/Title)</label>
                                    <input type="text" wire:model.live.debounce.2000ms="effectiveness_verified_by" class="form-control border bg-white shadow-sm" placeholder="Lab Manager / Officer">
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-dark text-uppercase mb-1">Date of Verification</label>
                                    <input type="date" wire:model.live.debounce.2000ms="effectiveness_date" class="form-control border bg-white shadow-sm">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-3 bg-soft-dark rounded text-muted small border-left" style="border-left: 3px solid #64748b !important;">
                            <i class="mdi mdi-information-outline mr-1"></i> Formulate the underlying reason for the non-conformance and define corrective actions.
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 pt-0 pb-4 px-4 position-relative" style="z-index: 12;">
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div>
                                @if(in_array($step_status, ['ncr_verification', 'capa_completed']) || (!$ncr_required && $isCapaSaved))
                                <button type="button" class="btn btn-outline-danger px-4 rounded-pill" wire:click="rejectCapa" wire:confirm="Are you sure you want to reject this CAPA and return to investigation?">
                                    <i class="mdi mdi-close-circle-outline mr-1"></i> Reject & Return
                                </button>
                                @endif
                            </div>

                            @if($isEditing)
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-link text-muted mr-3 font-weight-bold" wire:click="cancelEdit">Cancel</button>
                                    
                                    @if($ncr_required && $step_status === 'ncr_pending_init')
                                        <button type="button" class="btn btn-primary px-5 font-weight-bold shadow rounded-pill" x-on:click.prevent="savePhase1Editors()">
                                            <span wire:loading.remove wire:target="saveCapaInit">Save & Proceed to NCR <i class="mdi mdi-arrow-right ml-1"></i></span>
                                            <span wire:loading wire:target="saveCapaInit"><i class="mdi mdi-loading mdi-spin mr-1"></i> Saving Plan...</span>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success px-5 font-weight-bold shadow rounded-pill" x-on:click="saveWithEditors()">
                                            <span wire:loading.remove wire:target="approveCapaVerification"><i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i> Save Changes & Approve</span>
                                            <span wire:loading wire:target="approveCapaVerification"><i class="mdi mdi-loading mdi-spin mr-1"></i> Finalizing...</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="text-muted small italic">
                                    <i class="mdi mdi-lock-outline mr-1"></i> Click "Edit CAPA" at the top to make changes.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        @endif

        {{-- Step 2: NCR & RCA (Simplified) --}}
        @if($activeView == 'ncr')
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form wire:submit.prevent="finalizeNcr">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="font-weight-bold text-dark mb-0">Step 2: Why Why Analysis</h5>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                @if(!$isEditing)
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill font-weight-bold px-3 shadow-sm" wire:click="toggleEdit">
                                        <i class="mdi mdi-pencil-outline mr-1"></i> Edit Analysis
                                    </button>
                                @endif
                                <span class="badge bg-soft-dark text-dark border px-3 py-2">{{ $resolution->car_no ?? 'NCR-PENDING' }}</span>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="bg-light p-3 rounded d-flex align-items-center mb-4">
                                    <label class="font-weight-bold text-dark mb-0 mr-3 small text-uppercase">Lab No / LR No:</label>
                                    <input type="text" wire:model.live.debounce.2000ms="lab_no" class="form-control form-control-flat border bg-white shadow-sm font-weight-bold text-primary px-3" style="font-size: 1.1rem; width: 300px; border-radius: 8px;">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="font-weight-bold text-dark small text-uppercase mb-2">Identified by</label>
                                <input type="text" wire:model.live.debounce.2000ms="identified_by" class="form-control bg-white border shadow-sm" style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold text-dark small text-uppercase mb-2">Date</label>
                                <input type="date" wire:model.live.debounce.2000ms="ncr_identified_date" class="form-control bg-white border shadow-sm" style="border-radius: 8px;">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase d-block mb-2">Problem Statement</label>
                            @if(!$isEditing)
                                <div class="bg-white p-3 border rounded shadow-sm" style="min-height: 80px; line-height: 1.6;">
                                    {!! $problem_statement ?: '<span class="text-muted italic">No problem statement documented.</span>' !!}
                                </div>
                            @else
                                <div wire:ignore>
                                    <textarea id="ncr_problem_statement_editor" class="ncr-rich-editor">{{ $problem_statement ?? '' }}</textarea>
                                </div>
                                @error('problem_statement') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div class="row no-gutters rounded border mb-4 shadow-sm" style="overflow: hidden;">
                            <div class="col-md-12 bg-white p-4">
                                <h6 class="font-weight-bold text-primary text-uppercase mb-4" style="font-size: 0.75rem; letter-spacing: 1px;">Why-Why Analysis</h6>
                                @foreach(['why_1', 'why_2'] as $index => $field)
                                    <div class="mb-4">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-2">
                                            <span class="bg-primary text-white rounded-circle px-2 py-1 mr-1" style="font-size: 0.7rem;">WHY {{ $index + 1 }}</span>
                                        </label>
                                        @if(!$isEditing)
                                            <div class="bg-light p-3 border rounded mb-3" style="min-height: 60px; line-height: 1.6;">
                                                {!! ${$field} ?: '<span class="text-muted italic">Not documented.</span>' !!}
                                            </div>
                                        @else
                                            <div wire:ignore class="mb-4">
                                                <textarea id="ncr_why_{{ $index + 1 }}_editor" class="ncr-rich-editor">{{ ${$field} }}</textarea>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- RCA and CA have been moved to CAPA Tab Section 3 -->

                        <!-- Acceptance & Verification has been moved to CAPA Tab Section 3 -->

                        <div class="d-flex justify-content-between align-items-center pt-4 border-top mt-4">
                            <button type="button" class="btn btn-link text-muted font-weight-bold" wire:click="switchView('capa')">
                                <i class="mdi mdi-chevron-left mr-1"></i> Back to Action Plan
                            </button>
                            <div class="d-flex align-items-center">
                                @if($isEditing)
                                    <button type="button" class="btn btn-link text-muted mr-3 font-weight-bold" wire:click="cancelEdit">Cancel</button>
                                    <button type="button" class="btn btn-outline-secondary mr-3 px-4 rounded-pill transition-all" wire:click="saveDraftNcr">
                                        Save Progress
                                    </button>
                                    <button type="button" class="btn btn-success px-5 font-weight-bold shadow rounded-pill" x-on:click="saveNcrOnlyEditors()">
                                        <i class="mdi mdi-content-save mr-1"></i> Save NCR
                                    </button>
                                @else
                                    <div class="text-muted small italic">
                                        <i class="mdi mdi-lock-outline mr-1"></i> Click "Edit Analysis" at the top to make changes.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
    
    <style>
        .modern-capa-container { padding: 0; }
        .cursor-pointer { cursor: pointer; }
        .transition-all { transition: all 0.2s ease-in-out; }
        .bg-soft-primary { background-color: #ebf5ff; }
        .bg-soft-secondary { background-color: #f8fafc; }
        .bg-soft-success { background-color: #f0fdf4; }
        .bg-soft-warning { background-color: #fffbeb; }
        .bg-soft-dark { background-color: #f1f5f9; }
        .text-soft-muted { color: #94a3b8; }
        .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .tox-tinymce { border-radius: 8px !important; border: 1px solid #e2e8f0 !important; }
        .form-control-lg { font-size: 0.95rem; }
        .x-small { font-size: 0.7rem; }
    </style>

    @script
    <script>
        (function () {
            if (typeof tinymce === 'undefined') {
                var s = document.createElement('script');
                s.src = '/tinymce/tinymce.min.js';
                s.onload = function () {
                    window.__tinyMCEReady = true;
                    document.dispatchEvent(new Event('tinymce:loaded'));
                };
                document.head.appendChild(s);
            } else {
                window.__tinyMCEReady = true;
            }
        })();

        Alpine.data('capaForm', () => ({
            autosaveTimeout: null,
            triggerAutosave() {
                if (this.autosaveTimeout) clearTimeout(this.autosaveTimeout);
                this.autosaveTimeout = setTimeout(() => {
                    $wire.performAutosave();
                }, 2500); // 2.5s debounce for rich text
            },
            initTinyMCE() {
                const doInit = function () {
                    if (typeof tinymce === 'undefined') return;
                    
                    const edMap = {
                        capa_details_editor: 'details_of_non_conformance',
                        capa_root_cause_editor: 'root_cause',
                        capa_corrective_action_editor: 'capa_corrective_action',
                        capa_action_taken_editor: 'action_taken',
                        capa_acceptance_editor: 'acceptance',
                        ncr_problem_statement_editor: 'problem_statement',
                        ncr_why_1_editor: 'why_1',
                        ncr_why_2_editor: 'why_2'
                    };

                    const initialValues = {
                        capa_details_editor: $wire.get('details_of_non_conformance'),
                        capa_root_cause_editor: $wire.get('root_cause'),
                        capa_corrective_action_editor: $wire.get('capa_corrective_action'),
                        capa_action_taken_editor: $wire.get('action_taken'),
                        capa_acceptance_editor: $wire.get('acceptance'),
                        ncr_problem_statement_editor: $wire.get('problem_statement'),
                        ncr_why_1_editor: $wire.get('why_1'),
                        ncr_why_2_editor: $wire.get('why_2')
                    };

                    tinymce.remove('.capa-rich-editor, .ncr-rich-editor');
                    tinymce.init({
                        selector: '.capa-rich-editor, .ncr-rich-editor',
                        height: 350,
                        menubar: false,
                        auto_focus: false,
                        plugins: 'lists link',
                        toolbar: 'bold italic underline | bullist numlist | link',
                        branding: false,
                        promotion: false,
                        skin: 'oxide',
                        content_css: 'default',
                        content_style: 'body { font-family: Inter, -apple-system, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; padding: 15px; }',
                        setup: function (editor) {
                            editor.on('init', function () {
                                if (initialValues[editor.id]) {
                                    editor.setContent(initialValues[editor.id]);
                                }
                            });
                            editor.on('change keyup input', function () {
                                const fid = edMap[editor.id];
                                if (fid) $wire.set(fid, editor.getContent());
                                this.triggerAutosave();
                            }.bind(this));
                        }
                    });
                };

                if (typeof tinymce !== 'undefined') {
                    doInit();
                } else {
                    document.addEventListener('tinymce:loaded', doInit, { once: true });
                }
            },
            savePhase1Editors() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                    const ed = tinymce.get('capa_details_editor');
                    if (ed) $wire.set('details_of_non_conformance', ed.getContent());
                }
                $wire.saveCapaInit();
            },
            saveWithEditors() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                    const edMap = {
                        capa_root_cause_editor: 'root_cause',
                        capa_corrective_action_editor: 'capa_corrective_action',
                        capa_action_taken_editor: 'action_taken',
                        capa_acceptance_editor: 'acceptance'
                    };
                    Object.keys(edMap).forEach(id => {
                        const ed = tinymce.get(id);
                        if (ed) $wire.set(edMap[id], ed.getContent());
                    });
                }
                
                // If ncr_pending_init button is shown, it calls saveCapaInit natively because it doesn't use this function.
                // This function is only called from the Approve CAPA button.
                $wire.approveCapaVerification();
            },
            saveNcrOnlyEditors() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                    const edMap = {
                        ncr_problem_statement_editor: 'problem_statement',
                        ncr_why_1_editor: 'why_1',
                        ncr_why_2_editor: 'why_2'
                    };
                    Object.keys(edMap).forEach(id => {
                        const ed = tinymce.get(id);
                        if (ed) $wire.set(edMap[id], ed.getContent());
                    });
                }
                
                $wire.saveNcrOnly();
            },
            init() {
                setTimeout(() => this.initTinyMCE(), 100);
                
                $wire.on('view-switched', (data) => {
                    setTimeout(() => this.initTinyMCE(), 100);
                });

                $wire.on('edit-mode-activated', () => {
                    setTimeout(() => this.initTinyMCE(), 100);
                });

                $wire.on('edit-mode-deactivated', () => {
                    if (typeof tinymce !== 'undefined') {
                        tinymce.remove('.capa-rich-editor, .ncr-rich-editor');
                    }
                });
            }
        }));
    </script>
    @endscript
</div>
