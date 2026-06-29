<div x-data="capaLedger()">
    <script src="{{ asset('tinymce/tinymce.min.js') }}"></script>

    <style>
        /* ============================================
           CAPA Tab — LIMS Scientific Bridge Styles
           ============================================ */

        /* Editor Loading State */
        .capa-editor { opacity: 0; transition: opacity 0.2s ease-in; min-height: 150px; }
        .tox-tinymce { border-radius: 8px !important; border-color: #e2e8f0 !important; }

        /* Breadcrumb Context Bar */
        .capa-context-bar {
            background: var(--crm-neutral-50);
            border: var(--crm-border);
            border-radius: var(--crm-radius-lg);
            padding: var(--crm-space-3) var(--crm-space-5);
            margin-bottom: var(--crm-space-4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--crm-space-4);
            flex-wrap: wrap;
        }
        .capa-context-breadcrumb {
            font-size: 0.78rem;
            color: var(--crm-neutral-500);
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .capa-context-breadcrumb .id-token {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 0.82rem;
            color: var(--crm-neutral-800);
            background: #fff;
            border: var(--crm-border);
            border-radius: 4px;
            padding: 2px 8px;
            letter-spacing: 0.03em;
        }
        .capa-context-breadcrumb .separator {
            color: var(--crm-neutral-400);
            font-size: 0.7rem;
        }
        .capa-context-meta {
            display: flex;
            align-items: center;
            gap: var(--crm-space-4);
            font-size: 0.75rem;
            color: var(--crm-neutral-500);
            flex-wrap: wrap;
        }
        .capa-context-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .capa-context-meta .meta-item strong {
            color: var(--crm-neutral-700);
        }

        .capa-editing-active {
            border-color: var(--crm-neutral-200) !important;
            border-left: 4px solid var(--crm-success) !important;
        }

        /* Risk & CAR badges in edit mode */
        .risk-btn-group .risk-btn {
            cursor: pointer;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            border: 2px solid transparent;
            transition: all 0.15s ease;
            letter-spacing: 0.02em;
        }
        .risk-btn.risk-high       { border-color: #fed7d7; color: #c53030; background: #fff5f5; }
        .risk-btn.risk-high.active { background: #c53030; color: #fff; border-color: #c53030; }
        .risk-btn.risk-medium     { border-color: #feebc8; color: #c05621; background: #fff8f1; }
        .risk-btn.risk-medium.active { background: #dd6b20; color: #fff; border-color: #dd6b20; }
        .risk-btn.risk-low        { border-color: #c6f6d5; color: #276749; background: #f0fff4; }
        .risk-btn.risk-low.active { background: #276749; color: #fff; border-color: #276749; }

        .car-type-btn {
            cursor: pointer;
            padding: 4px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 2px solid transparent;
            transition: all 0.15s ease;
        }
        .car-type-btn.car-major         { border-color: #fed7d7; color: #c53030; background: #fff5f5; }
        .car-type-btn.car-major.active  { background: #c53030; color: #fff; border-color: #c53030; }
        .car-type-btn.car-minor         { border-color: #feebc8; color: #c05621; background: #fff8f1; }
        .car-type-btn.car-minor.active  { background: #dd6b20; color: #fff; border-color: #dd6b20; }

        .capa-linkage-card {
            background: var(--crm-neutral-50);
            border: var(--crm-border);
            border-left: 4px solid var(--crm-success) !important;
            border-radius: var(--crm-radius-lg);
            padding: var(--crm-space-4) var(--crm-space-5);
            margin-bottom: var(--crm-space-4);
        }
        .capa-linkage-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--crm-success);
            margin-bottom: var(--crm-space-2);
        }
        .capa-linkage-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--crm-neutral-800);
            letter-spacing: 0.04em;
        }

        /* Monospace ID tokens in read mode */
        .mono-id {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        /* Previously used green aliases — now mapped to proper tokens */
        .text-indigo  { color: var(--crm-success); }     /* legacy: was #38a169 */
        .bg-soft-indigo { background: var(--crm-success-light); } /* legacy: was #f0fff4 */
        .bg-indigo    { background: var(--crm-success); } /* legacy: was #38a169 */
        .bg-soft-dark { background: var(--crm-neutral-100); }

        /* bg-soft-success and bg-success-light — defer to crm.css global definitions */

        .why-chain { border-left: 2px dashed var(--crm-neutral-300); }
        .why-dot {
            position: absolute;
            left: -25px;
            top: 15px;
            width: 8px;
            height: 8px;
            background: var(--crm-primary);
            border-radius: 50%;
            z-index: 2;
        }
        .why-item:hover .why-dot { box-shadow: 0 0 0 4px var(--crm-primary-light); }

        .tox-tinymce {
            border-radius: var(--crm-radius-md) !important;
            border: var(--crm-border) !important;
            width: 100% !important;
        }
        /* .investigation-label — defined globally in crm.css; kept here for display:block !important override */
        .investigation-label {
            display: block !important;
            font-size: var(--crm-font-muted);
            font-weight: 700;
            color: var(--crm-neutral-600);
            margin-bottom: var(--crm-space-2);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .capa-editor-wrapper { min-height: 250px; }
        .bg-indigo.text-white { background: var(--crm-primary) !important; }

        /* Section Locking */
        .capa-section-locked {
            opacity: 0.5;
            pointer-events: none;
            filter: grayscale(0.5);
            position: relative;
        }
        .capa-section-locked::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 10;
        }
        .capa-section-active {
            border-left: 4px solid var(--crm-success) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .capa-section-complete {
            border-left: 4px solid var(--crm-success) !important;
        }
        .section-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 10px;
        }
        /* .badge-locked, .badge-active, .badge-complete — now defined in crm.css */

        /* Wizard Step Tracker */
        .capa-wizard-stepper {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--crm-space-5);
            position: relative;
            padding: 0 10px;
        }
        .capa-wizard-stepper::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 20px;
            right: 20px;
            height: 2px;
            background: var(--crm-neutral-200);
            z-index: 1;
        }
        .wizard-step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--crm-neutral-300);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--crm-neutral-500);
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        .wizard-step.active .step-circle {
            background: var(--crm-success);
            border-color: var(--crm-success);
            color: #fff;
            box-shadow: 0 0 0 4px var(--crm-success-light);
        }
        .wizard-step.completed .step-circle {
            background: var(--crm-success);
            border-color: var(--crm-success);
            color: #fff;
        }
        .step-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--crm-neutral-500);
            text-align: center;
        }
        .wizard-step.active .step-label { color: var(--crm-success); }
        .wizard-step.completed .step-label { color: var(--crm-success); }
    </style>

    <div class="lab-ledger-container">
        @php
            $fullEditMode = ($isEditing && $capa_status === \App\Livewire\Crm\Complaint\Tabs\ComplaintCapaTab::STATUS_COMPLETED);
        @endphp

        {{-- ═══════════════════════════════════════════
             CONTEXT BAR — Breadcrumb + Status
             ═══════════════════════════════════════════ --}}
        <div class="capa-context-bar">
            <div>
                <div class="capa-context-breadcrumb mb-1">
                    <span>Corrective Action Request</span>
                    <span class="id-token">{{ $resolution->car_no ?? 'NEW-CAR' }}</span>
                </div>
                <div class="capa-context-meta">
                    Status:&nbsp;<span class="crm-badge crm-badge-primary" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">{{ $this->next_pending_action }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap: 12px;">
                <div x-data="{ lastSaved: '' }"
                    x-on:autosave-completed.window="lastSaved = $event.detail.time || ($event.detail[0] ? $event.detail[0].time : '')"
                    class="small text-muted">
                    <template x-if="lastSaved">
                        <span><i class="mdi mdi-check-circle-outline text-success mr-1"></i>Saved: <span x-text="lastSaved"></span></span>
                    </template>
                </div>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════════
             STEP 2-4 — CAPA & VERIFICATION
             ═══════════════════════════════════════════════════ --}}
        <div class="capa-form-container">

            {{-- 01. CASE TRIAGE & ASSIGNMENT --}}
            <div id="section_assignment" class="lab-ledger-card {{ ($isEditing && $activeStep == 1) || $fullEditMode ? 'capa-editing-active' : '' }}" x-show="(!isEditingMode) || (isEditingMode && activeStep >= 1)">
                <div class="lab-ledger-header crm-glass d-flex justify-content-between align-items-center" x-on:click="toggleSection('assignment')">
                    <span class="font-weight-bold text-dark">
                        <i class="mdi mdi-account-cog-outline mr-2 text-primary"></i>
                        SECTION 1: ASSIGNMENT &amp; TIMELINE
                    </span>
                    <i class="mdi {{ (!$isEditing && $activeStep > 1) ? 'mdi-check-circle text-success' : 'mdi-chevron-down' }} text-muted"></i>
                </div>
                <div class="lab-ledger-body" x-show="sections.assignment" x-collapse
                     wire:key="assignment-body-{{ ($isEditing && $activeStep >= 1) || $fullEditMode ? 'edit' : 'read' }}">
                    @if(($isEditing && $activeStep >= 1) || $fullEditMode)
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Date Issued <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model.live="date_issued">
                                @error('date_issued') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Proposed Close Out Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="proposed_close_out_date">
                                @error('proposed_close_out_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Issued To <span class="text-danger">*</span></label>
                                <div wire:ignore>
                                    <select class="form-control capa-select2" multiple="multiple" 
                                            x-data="{
                                                init() {
                                                    let el = $(this.$el);
                                                    el.select2({ placeholder: 'Select personnel...', width: '100%' });
                                                    
                                                    // Sync from Livewire to Select2
                                                    this.$watch('$wire.issued_to', value => {
                                                        el.val(value).trigger('change.select2');
                                                    });

                                                    // Sync from Select2 to Livewire
                                                    el.on('change', () => {
                                                        this.$wire.set('issued_to', el.val());
                                                    });

                                                    // Set initial value
                                                    el.val(this.$wire.issued_to).trigger('change.select2');
                                                }
                                            }">
                                        @foreach($users as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('issued_to') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            @if($complaint->is_lab_related)
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Lab Report No <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="lab_no" placeholder="e.g. LR/001/24">
                                @error('lab_no') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Issued By <span class="text-danger">*</span></label>
                                <div wire:ignore>
                                    <select class="form-control capa-select2" multiple="multiple"
                                            x-data="{
                                                init() {
                                                    let el = $(this.$el);
                                                    el.select2({ placeholder: 'Select personnel...', width: '100%' });
                                                    
                                                    // Sync from Livewire to Select2
                                                    this.$watch('$wire.issued_by', value => {
                                                        el.val(value).trigger('change.select2');
                                                    });

                                                    // Sync from Select2 to Livewire
                                                    el.on('change', () => {
                                                        this.$wire.set('issued_by', el.val());
                                                    });

                                                    // Set initial value
                                                    el.val(this.$wire.issued_by).trigger('change.select2');
                                                }
                                            }">
                                        @foreach($users as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('issued_by') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 border-top pt-3">
                            <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm"
                                wire:click="saveAssignment" wire:loading.attr="disabled" wire:target="saveAssignment">
                                <span wire:loading.remove wire:target="saveAssignment"><i class="mdi mdi-content-save-outline mr-1"></i>{{ $fullEditMode ? 'Update Assignment' : 'Save Assignment' }}</span>
                                <span wire:loading wire:target="saveAssignment"><i class="mdi mdi-loading mdi-spin mr-1"></i>Saving...</span>
                            </button>
                        </div>
                    @else
                        {{-- READ MODE --}}
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <span class="crm-read-label">Proposed Close Out Date</span>
                                <span class="crm-field-value">{{ $proposed_close_out_date ?: '—' }}</span>
                            </div>
                            <div class="col-md-4 mb-3">
                                <span class="crm-read-label">Issued To</span>
                                <span class="crm-field-value">{{ is_array($issued_to) ? implode(', ', $issued_to) : ($issued_to ?: '—') }}</span>
                            </div>
                            <div class="col-md-4 mb-3">
                                <span class="crm-read-label">Lab Report No</span>
                                <span class="crm-field-value">{{ $lab_no ?: '—' }}</span>
                            </div>
                        </div>

                        <div class="crm-attribution-bar">
                            <div><span class="attr-label">Issued By:</span> <span class="attr-value">{{ is_array($issued_by) ? implode(', ', $issued_by) : ($issued_by ?: '—') }}</span></div>
                            <div><span class="attr-label">On:</span> <span class="attr-value mono-id">{{ $date_issued ?: '—' }}</span></div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 02. NON-CONFORMANCE DETAILS --}}
            <div id="section_ncdetails" class="lab-ledger-card {{ ($isEditing && $activeStep == 2) || $fullEditMode ? 'capa-editing-active' : '' }}" x-show="(!isEditingMode && ['Assigned', 'Triaged', 'Action', 'Verify', 'Completed'].includes($wire.capa_status)) || (isEditingMode && activeStep >= 2) || fullEditMode">
                <div class="lab-ledger-header crm-glass d-flex justify-content-between align-items-center" x-on:click="toggleSection('ncdetails')">
                    <span class="font-weight-bold text-dark d-flex align-items-center">
                        <i class="mdi mdi-alert-circle-outline mr-2" style="font-size: 1.2rem; color: var(--crm-success);"></i>
                        02. DETAILS OF NON-CONFORMANCE
                    </span>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <i class="mdi {{ (!$isEditing && $activeStep > 2) ? 'mdi-check-circle text-success' : 'mdi-chevron-down' }} text-muted"></i>
                    </div>
                </div>
                <div class="lab-ledger-body" x-show="sections.ncdetails" x-collapse
                     wire:key="ncdetails-body-{{ ($isEditing && $activeStep >= 2) || $fullEditMode ? 'edit' : 'read' }}">
                    @if(($isEditing && $activeStep >= 2) || $fullEditMode)
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">CAR Type <span class="text-danger">*</span></label>
                                <div class="d-flex" style="gap:8px;">
                                    <span class="car-type-btn car-major {{ $car_type == 'Major' ? 'active' : '' }}" wire:click="$set('car_type', 'Major')">Major</span>
                                    <span class="car-type-btn car-minor {{ $car_type == 'Minor' ? 'active' : '' }}" wire:click="$set('car_type', 'Minor')">Minor</span>
                                </div>
                                @error('car_type') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Ref. Clause</label>
                                <input type="text" class="form-control border mb-3" wire:model="ref_clause" placeholder="e.g. ISO 9001:2015">
                                @error('ref_clause') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="investigation-label">Details of Non-Conformance <span class="text-danger">*</span></label>
                                <div wire:ignore wire:key="ncr-details-editor-container">
                                    <textarea id="ncr_details_editor" class="capa-editor">{{ $details_of_non_conformance }}</textarea>
                                </div>
                                @error('details_of_non_conformance') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Identified By Name <span class="text-danger">*</span></label>
                                <div wire:ignore>
                                    <select class="form-control capa-select2" multiple="multiple"
                                            x-data="{
                                                init() {
                                                    let el = $(this.$el);
                                                    el.select2({ placeholder: 'Select personnel...', width: '100%' });
                                                    this.$watch('$wire.capa_identified_by', value => {
                                                        el.val(value).trigger('change.select2');
                                                    });
                                                    el.on('change', () => {
                                                        this.$wire.set('capa_identified_by', el.val());
                                                    });
                                                    el.val(this.$wire.capa_identified_by).trigger('change.select2');
                                                }
                                            }">
                                        @foreach($users as $user)
                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('capa_identified_by') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="investigation-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="capa_identified_date">
                                @error('capa_identified_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 border-top pt-3">
                            @if(!$fullEditMode)
                                <button type="button" class="btn btn-soft-secondary btn-sm px-4 font-weight-bold"
                                    wire:click="$set('activeStep', 1)">
                                    <i class="mdi mdi-arrow-left mr-1"></i>Back to Assignment
                                </button>
                            @else
                                <div></div> {{-- Spacer to keep "Save" on the right --}}
                            @endif
                            <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm"
                                x-on:click="saveNcStep()" wire:loading.attr="disabled" wire:target="saveNcDetails">
                                <span wire:loading.remove wire:target="saveNcDetails"><i class="mdi mdi-content-save-outline mr-1"></i>{{ $fullEditMode ? 'Update NC Details' : 'Save & Continue' }}</span>
                                <span wire:loading wire:target="saveNcDetails"><i class="mdi mdi-loading mdi-spin mr-1"></i>Saving...</span>
                            </button>
                        </div>
                    @else
                        {{-- READ MODE --}}
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <span class="crm-read-label">CAR Type</span>
                                <span class="badge {{ $car_type == 'Major' ? 'badge-danger' : 'badge-warning' }} px-2 py-1">
                                    {{ $car_type ?: 'Not Set' }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <span class="crm-read-label">Ref. Clause</span>
                                <span class="crm-field-value d-block mb-3">{{ $ref_clause ?: '—' }}</span>
                            </div>
                        </div>

                        {{-- Details Row --}}
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <span class="crm-read-label">Details of Non-Conformance</span>
                                <div class="investigation-text" style="min-height: 60px;">
                                    {!! $details_of_non_conformance ?: '<span class="text-muted">No details provided.</span>' !!}
                                </div>
                            </div>
                        </div>

                        {{-- Identified By Row --}}
                        <div class="crm-attribution-bar" wire:key="display-assignment-personnel">
                            <div><span class="attr-label">Identified By:</span> <span class="attr-value">{{ is_array($capa_identified_by) ? implode(', ', $capa_identified_by) : ($capa_identified_by ?: '—') }}</span></div>
                            <div><span class="attr-label">On:</span> <span class="attr-value mono-id">{{ $capa_identified_date ?: '—' }}</span></div>
                        </div>
                    @endif
                </div>
            </div>


            {{-- 03. CORRECTIVE ACTION --}}
            <div id="section_action" class="lab-ledger-card {{ ($isEditing && $activeStep == 3) || $fullEditMode ? 'capa-editing-active' : '' }}" x-show="(!isEditingMode && ['Triaged', 'Action', 'Verify', 'Completed'].includes($wire.capa_status)) || (isEditingMode && activeStep >= 3) || fullEditMode">
                <div class="lab-ledger-header crm-glass d-flex justify-content-between align-items-center" x-on:click="toggleSection('why_why')">
                    <span class="font-weight-bold text-dark d-flex align-items-center">
                        <i class="mdi mdi-check-decagram-outline mr-2" style="font-size: 1.2rem; color: var(--crm-success);"></i>
                        03. CORRECTIVE ACTION PLAN
                    </span>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <div x-show="lastSaved" class="animate__animated animate__fadeIn mx-3" style="display: none;">
                            <span class="crm-badge crm-badge-success">
                                <i class="mdi mdi-check-all mr-1"></i> Autosaved <span x-text="lastSaved"></span>
                            </span>
                        </div>
                        <i class="mdi {{ (!$isEditing && $activeStep > 3) ? 'mdi-check-circle text-success' : 'mdi-chevron-down' }} text-muted ml-auto"></i>
                    </div>
                </div>
                <div class="lab-ledger-body" x-show="sections.why_why" x-collapse
                     wire:key="action-body-{{ ($isEditing && $activeStep >= 3) || $fullEditMode ? 'edit' : 'read' }}">
                    @if(($isEditing && $activeStep >= 3) || $fullEditMode)
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label class="investigation-label text-success font-weight-bold">Risk Level <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap gap-1 risk-btn-group mb-2" style="gap:6px;">
                                    @foreach(['High','Medium','Low'] as $rl)
                                        <span class="risk-btn risk-{{ strtolower($rl) }} {{ $capa_risk_level == $rl ? 'active' : '' }}"
                                            wire:click="$set('capa_risk_level', '{{ $rl }}')">{{ $rl }}</span>
                                    @endforeach
                                </div>
                                @error('capa_risk_level') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="investigation-label text-dark font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                    <i class="mdi mdi-flash-outline mr-1"></i> IMMEDIATE ACTION TAKEN <span class="text-danger">*</span>
                                </label>
                                <div wire:ignore wire:key="action-taken-editor-container" x-init="initTinyMCE()">
                                    <textarea id="action_taken_editor" class="capa-editor">{{ $action_taken }}</textarea>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-7 mb-3">
                                        <label class="investigation-label">Action Taken By <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select class="form-control capa-select2" multiple="multiple"
                                                    x-data="{
                                                        init() {
                                                            let el = $(this.$el);
                                                            el.select2({ placeholder: 'Select Immediate Action Identifier...', width: '100%' });
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
                                    </div>
                                    <div class="col-md-5 mb-0">
                                        <label class="investigation-label">Date of Action <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model="action_taken_date">
                                        @error('action_taken_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @error('action_taken_by') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                @error('action_taken') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="investigation-label text-dark font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                    <i class="mdi mdi-magnify mr-1"></i> ROOT CAUSE SUMMARY <span class="text-danger">*</span>
                                </label>
                                <div wire:ignore wire:key="root-cause-editor-container" x-init="initTinyMCE()">
                                    <textarea id="root_cause_editor" class="capa-editor">{{ $root_cause }}</textarea>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-7 mb-3">
                                        <label class="investigation-label">Identified By <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select class="form-control capa-select2" multiple="multiple"
                                                    x-data="{
                                                        init() {
                                                            let el = $(this.$el);
                                                            el.select2({ placeholder: 'Select Root Cause Identifier...', width: '100%' });
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
                                    </div>
                                    <div class="col-md-5 mb-0">
                                        <label class="investigation-label">Date Identified <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model="root_cause_date">
                                        @error('root_cause_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @error('root_cause_by') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                @error('root_cause') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
    
                            <div class="col-md-12 mb-4">
                                <label class="investigation-label text-dark font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                    <i class="mdi mdi-check-decagram-outline mr-1"></i> PROPOSED CORRECTIVE ACTION <span class="text-danger">*</span>
                                </label>
                                <div wire:ignore wire:key="corrective-action-editor-container" x-init="initTinyMCE()">
                                    <textarea id="corrective_action_editor" class="capa-editor">{{ $capa_corrective_action }}</textarea>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-7 mb-3">
                                        <label class="investigation-label">Proposed By <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select class="form-control capa-select2" multiple="multiple"
                                                    x-data="{
                                                        init() {
                                                            let el = $(this.$el);
                                                            el.select2({ placeholder: 'Select Corrective Action Identifier...', width: '100%' });
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
                                    </div>
                                    <div class="col-md-5 mb-0">
                                        <label class="investigation-label">Date Proposed <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model="corrective_action_date">
                                        @error('corrective_action_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @error('corrective_action_by') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                @error('capa_corrective_action') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 border-top pt-3">
                            @if(!$fullEditMode)
                                <button type="button" class="btn btn-soft-secondary btn-sm px-4 font-weight-bold"
                                    x-on:click="goBack()">
                                    <i class="mdi mdi-arrow-left mr-1"></i>Back to Non-Conformance Details
                                </button>
                            @else
                                <div></div>
                            @endif
                            <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm"
                                x-on:click="saveStepTwo()" wire:loading.attr="disabled" wire:target="saveCorrectiveAction">
                                <span wire:loading.remove wire:target="saveCorrectiveAction"><i class="mdi mdi-content-save-outline mr-1"></i>{{ $fullEditMode ? 'Update Corrective Action' : 'Save & Continue' }}</span>
                                <span wire:loading wire:target="saveCorrectiveAction"><i class="mdi mdi-loading mdi-spin mr-1"></i>Saving...</span>
                            </button>
                        </div>
                    @else
                        {{-- READ MODE --}}
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label class="investigation-label text-success font-weight-bold">Risk Level</label>
                                <div class="mt-1">
                                    @if($capa_risk_level)
                                        <span class="badge {{ $capa_risk_level == 'High' ? 'badge-danger' : ($capa_risk_level == 'Medium' ? 'badge-warning' : 'badge-success') }} px-2 py-1">
                                            {{ $capa_risk_level }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Not Set</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="investigation-label">
                                    <i class="mdi mdi-flash-outline mr-1"></i> IMMEDIATE ACTION TAKEN
                                </label>
                                <div class="investigation-text">
                                    {!! html_entity_decode($action_taken ?: '<span class="text-muted italic">No immediate actions recorded.</span>') !!}
                                    <div class="crm-attribution-bar">
                                        <div><span class="attr-label">Action Taken By:</span> <span class="attr-value">{{ is_array($action_taken_by) ? implode(', ', $action_taken_by) : ($action_taken_by ?: 'Not Set') }}</span></div>
                                        <div><span class="attr-label">On:</span> <span class="attr-value">{{ $action_taken_date ?: 'No Date' }}</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="investigation-label">
                                    <i class="mdi mdi-magnify mr-1"></i> ROOT CAUSE SUMMARY
                                </label>
                                <div class="investigation-text">
                                    {!! html_entity_decode($root_cause ?: '<span class="text-muted italic">No root cause summary.</span>') !!}
                                    <div class="crm-attribution-bar">
                                        <div><span class="attr-label">Identified By:</span> <span class="attr-value">{{ is_array($root_cause_by) ? implode(', ', $root_cause_by) : ($root_cause_by ?: 'Not Set') }}</span></div>
                                        <div><span class="attr-label">On:</span> <span class="attr-value">{{ $root_cause_date ?: 'No Date' }}</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="investigation-label">
                                    <i class="mdi mdi-check-decagram-outline mr-1"></i> PROPOSED CORRECTIVE ACTION
                                </label>
                                <div class="investigation-text">
                                    {!! html_entity_decode($capa_corrective_action ?: '<span class="text-muted italic">No corrective actions defined.</span>') !!}
                                    <div class="crm-attribution-bar">
                                        <div><span class="attr-label">Proposed By:</span> <span class="attr-value">{{ is_array($corrective_action_by) ? implode(', ', $corrective_action_by) : ($corrective_action_by ?: 'Not Set') }}</span></div>
                                        <div><span class="attr-label">On:</span> <span class="attr-value">{{ $corrective_action_date ?: 'No Date' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- Attribution block removed as individual fields are now present for each section --}}

                    @endif
                </div>
            </div>

            {{-- 04. ACCEPTANCE & VERIFICATION --}}
            <div id="section_closure" class="lab-ledger-card {{ ($isEditing && $activeStep == 4) || $fullEditMode ? 'capa-editing-active' : '' }}" x-show="(!isEditingMode && ['Verify', 'Completed'].includes($wire.capa_status)) || (isEditingMode && activeStep >= 4) || fullEditMode">
                <div class="lab-ledger-header crm-glass d-flex justify-content-between align-items-center" x-on:click="toggleSection('effectiveness')">
                    <span class="font-weight-bold text-dark d-flex align-items-center">
                        <i class="mdi mdi-shield-check-outline mr-2" style="font-size: 1.2rem; color: var(--crm-success);"></i>
                        04. ACCEPTANCE OF CORRECTIVE ACTION AND ACTION TAKEN
                    </span>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <i class="mdi mdi-chevron-down text-muted"></i>
                    </div>
                </div>
                <div class="lab-ledger-body" x-show="sections.effectiveness" x-collapse
                     wire:key="closure-body-{{ ($isEditing && $activeStep == 4) || $fullEditMode ? 'edit' : 'read' }}">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="investigation-label font-weight-bold">Acceptance of Corrective Action and Action Taken</label>
                            @if(($isEditing && $activeStep == 4) || $fullEditMode)
                                <div wire:ignore wire:key="acceptance-editor-container" x-init="initTinyMCE()">
                                    <textarea id="acceptance_editor" class="capa-editor">{{ $acceptance }}</textarea>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="investigation-label">Effectiveness Verified By <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select class="form-control capa-select2" multiple="multiple"
                                                    x-data="{
                                                        init() {
                                                            let el = $(this.$el);
                                                            el.select2({ placeholder: 'Select personnel...', width: '100%' });
                                                            this.$watch('$wire.effectiveness_verified_by', value => {
                                                                el.val(value).trigger('change.select2');
                                                            });
                                                            el.on('change', () => {
                                                                this.$wire.set('effectiveness_verified_by', el.val());
                                                            });
                                                            el.val(this.$wire.effectiveness_verified_by).trigger('change.select2');
                                                        }
                                                    }">
                                                @foreach($users as $user)
                                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('effectiveness_verified_by') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="investigation-label">Date of Verification</label>
                                        <input type="date" class="form-control" wire:model="effectiveness_date">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                    @if(!$fullEditMode)
                                        <button type="button" class="btn btn-soft-secondary btn-sm px-4 font-weight-bold"
                                            x-on:click="goBack()">
                                            <i class="mdi mdi-arrow-left mr-1"></i>Back to Investigation Details
                                        </button>
                                    @else
                                        <div></div>
                                    @endif
                                    @if($this->isReadyToAdvance)
                                        <button type="button" class="btn btn-success px-5 font-weight-bold shadow-sm"
                                            x-on:click="saveFinalAndComplete()" wire:loading.attr="disabled" wire:target="saveFinalVerification">
                                            <span wire:loading.remove wire:target="saveFinalVerification"><i class="mdi mdi-check-decagram-outline mr-2"></i>{{ $fullEditMode ? 'Update Verification' : 'Approve & Advance' }}</span>
                                            <span wire:loading wire:target="saveFinalVerification"><i class="mdi mdi-loading mdi-spin mr-2"></i>Saving...</span>
                                        </button>
                                    @else
                                        <div class="alert alert-soft-warning d-flex align-items-center mb-0 px-4 py-3" style="border-radius: 12px; border: 1px dashed #fbbf24;">
                                            <i class="mdi mdi-information-outline mr-3" style="font-size: 1.4rem;"></i>
                                            <div style="font-size: 0.85rem;">
                                                <strong class="d-block mb-1">Completion Required</strong>
                                                Please ensure all preceding sections (Assignment, NC Details, and Corrective Action Plan) are fully recorded before approving.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="investigation-text mb-3" wire:key="display-effectiveness">
                                    {!! html_entity_decode($acceptance ?: '<span class="text-muted">Acceptance details pending.</span>') !!}
                                </div>
                                <div class="crm-attribution-bar">
                                    <div><span class="attr-label">Verified By:</span> <span class="attr-value">{{ is_array($effectiveness_verified_by) ? implode(', ', $effectiveness_verified_by) : ($effectiveness_verified_by ?: '—') }}</span></div>
                                    <div><span class="attr-label">On:</span> <span class="attr-value mono-id">{{ $effectiveness_date ?: '—' }}</span></div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>


        </div>

    </div>{{-- end lab-ledger-container --}}

    @script
    <script>
        Alpine.data('capaLedger', () => ({
            activeStep: @entangle('activeStep'),
            isEditingMode: @entangle('isEditing').live,
            get fullEditMode() {
                return this.isEditingMode && this.$wire.capa_status === 'Completed';
            },
            sections: {
                assignment: true,
                ncdetails: true,
                why_why: true,
                effectiveness: true
            },
            lastSaved: '',
            init() {
                this.determineActiveStep();

                // 1.1 Listen for Global Draft Save
                this.$wire.on('sync-and-save-draft', () => {
                    if (this.isEditingMode) {
                        const data = this._collectEditorData();
                        this.$wire.syncRichTextAndAutosave(data);
                    }
                });

                // 1. Initial Load
                if (this.isEditingMode) {
                    this.initTinyMCE();
                }

                // 2. Listen for Livewire Step progression
                $wire.on('capa-step-saved', (eventData) => {
                    let data = eventData;
                    if (Array.isArray(eventData)) data = eventData[0];
                    if (data && data.nextStep) {
                        this.activeStep = parseInt(data.nextStep);
                    }
                });

                // Listen for NC completion from the other tab
                $wire.on('nc-completed', () => {
                    this.determineActiveStep();
                    if (this.isEditingMode) {
                        this.initTinyMCE();
                    }
                });

                // 3. Listen for Edit mode changes (Cancel/Save)
                $wire.on('edit-mode-deactivated', () => { 
                    this.activeStep = 1; 
                    if (typeof tinymce !== 'undefined') tinymce.remove('.capa-editor');
                });

                // 4. Watchers for dynamic re-init
                this.$watch('isEditingMode', (val) => {
                    if (val) {
                        this.determineActiveStep();
                        this.initTinyMCE();
                    } else {
                        if (typeof tinymce !== 'undefined') tinymce.remove('.capa-editor');
                    }
                });

                this.$watch('activeStep', (val) => {
                    if (this.isEditingMode) {
                        this.initTinyMCE();
                    }
                });

                this.$wire.on('progress-saved-silently', (eventData) => {
                    let data = eventData;
                    if (Array.isArray(eventData)) data = eventData[0];
                    if (data && data.time) {
                        this.lastSaved = data.time;
                    }
                });

                // 5. Periodic Autosave
                setInterval(() => {
                    if (this.isEditingMode) this.triggerAutosave();
                }, 30000); // 30 seconds
            },
            determineActiveStep() {
                const status = $wire.capa_status;
                if (status === 'Draft') this.activeStep = 1;
                else if (status === 'Assigned') this.activeStep = 2;
                else if (status === 'Triaged') this.activeStep = 3;
                else if (status === 'Action' || status === 'Verify') this.activeStep = 4;
                else this.activeStep = 4;
            },
            goBack() {
                if (this.activeStep > 1) {
                    this.activeStep--;
                }
            },
            toggleSection(section) {
                this.sections[section] = !this.sections[section];
                
                // Jump to step if in edit mode and opening the section
                if (this.isEditingMode && this.sections[section]) {
                    const stepMap = { assignment: 1, ncdetails: 2, why_why: 3, effectiveness: 4 };
                    if (stepMap[section]) {
                        this.activeStep = stepMap[section];
                    }
                }
            },
            initTinyMCE() {
                if (typeof tinymce === 'undefined') {
                    setTimeout(() => this.initTinyMCE(), 100);
                    return;
                }

                // Configuration for editors per step
                const stepEditors = {
                    2: ['ncr_details_editor'],
                    3: ['root_cause_editor', 'corrective_action_editor', 'action_taken_editor'],
                    4: ['acceptance_editor']
                };

                const allPossibleIds = ['ncr_details_editor', 'root_cause_editor', 'corrective_action_editor', 'action_taken_editor', 'acceptance_editor'];

                // In edit mode or full edit mode, init ALL editors that are present in the DOM
                const currentIds = this.isEditingMode ? allPossibleIds : [];

                // Cleanup: Only remove editors for steps that are NOT visible (skip in full edit mode)
                if (!this.fullEditMode) {
                    allPossibleIds.forEach(id => {
                        if (!currentIds.includes(id) && tinymce.get(id)) {
                            tinymce.remove('#' + id);
                        }
                    });
                }
                
                this.$nextTick(() => {
                    // Small timeout ensures targeted textareas are rendered/visible in DOM
                    setTimeout(() => {
                        currentIds.forEach(id => {
                            const textarea = document.getElementById(id);
                            if (!textarea) return;

                            // Check if already initialized
                            if (tinymce.get(id)) {
                                textarea.style.opacity = '1';
                                return;
                            }

                            tinymce.init({
                                selector: '#' + id,
                                height: id === 'ncr_details_editor' ? 300 : 250,
                                menubar: false,
                                plugins: 'lists link',
                                toolbar: 'bold italic underline | bullist numlist | link',
                                branding: false,
                                promotion: false,
                                setup: (editor) => {
                                    editor.on('init', () => {
                                        textarea.style.opacity = '1';
                                        
                                        // Force sync with state on load
                                        const field = this._getFieldNameForEditor(id);
                                        if (field) {
                                            editor.setContent($wire[field] || '');
                                        }
                                    });
                                    editor.on('change', () => {
                                        editor.save(); 
                                    });
                                },
                                content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; padding: 12px; }'
                            });
                        });
                    }, this.fullEditMode ? 150 : 50); // Give extra time in full edit mode for all DOM elements to render
                });
            },
            _getFieldNameForEditor(id) {
                const map = {
                    ncr_details_editor:         'details_of_non_conformance',
                    root_cause_editor:          'root_cause',
                    corrective_action_editor:   'capa_corrective_action',
                    action_taken_editor:        'action_taken',
                    acceptance_editor:          'acceptance'
                };
                return map[id];
            },
            async saveNcStep() {
                const data = this._collectEditorData();
                await $wire.saveNcDetails(data);
            },
            async saveStepTwo() {
                const data = this._collectEditorData();
                await $wire.saveCorrectiveAction(data);
            },
            async saveFinalAndComplete() {
                const data = this._collectEditorData();
                await $wire.saveFinalVerification(data);
            },
            async triggerAutosave() {
                if (typeof tinymce === 'undefined') return;
                const data = this._collectEditorData();
                await $wire.syncRichTextAndAutosave(data);
            },
            _collectEditorData() {
                if (typeof tinymce === 'undefined') return {};
                tinymce.triggerSave();
                
                const ids = ['ncr_details_editor', 'root_cause_editor', 'corrective_action_editor', 'action_taken_editor', 'acceptance_editor'];
                const data = {};
                
                ids.forEach(id => {
                    const ed = tinymce.get(id);
                    if (ed) {
                        data[this._getFieldNameForEditor(id)] = ed.getContent();
                    }
                });
                return data;
            }
        }));
    </script>
    @endscript
</div>
