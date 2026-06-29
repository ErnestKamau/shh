<div @if($viewMode === 'modal') x-data="workflowContainer" @endif class="complaint-workflow-container">


    @if($viewMode === 'tab')
        <div class="tab-pane fade show active p-3" id="complaint-workflow" role="tabpanel">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                            style="width:28px;height:28px;background:#f0fdf4;">
                            <i class="mdi mdi-swap-horizontal" style="font-size:1rem;color:#16a34a;"></i>
                        </span>
                        <div>
                            <small class="font-weight-bold text-dark"
                                style="font-size:0.82rem;">{{ $currentWorkflowName }}</small>
                            <small class="text-muted d-block" style="font-size:0.67rem;">Complaint lifecycle &amp;
                                chain-of-custody audit trail</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center">
                        <button type="button" class="btn btn-outline-success btn-sm text-nowrap" wire:click="exportToExcel">
                            <i class="mdi mdi-microsoft-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-2 mt-3">
                <i class="mdi mdi-link-variant text-muted mr-2"></i>
                <small class="font-weight-bold text-uppercase text-muted"
                    style="font-size:0.72rem;letter-spacing:0.05em;">Chain of Custody</small>
            </div>
            <div wire:loading  class="crm-loading-indicator"><i class="mdi mdi-loading mdi-spin"></i>
                Loading...</div>
            {{-- ============================================
            CHECKPOINT TIMELINE
            ============================================ --}}
            @if($chainOfCustody->isEmpty())
                <div class="text-center py-5">
                    <i class="mdi mdi-timeline-check-outline text-muted" style="font-size:2.5rem;opacity:0.35;"></i>
                    <p class="text-muted mt-2 mb-1 font-weight-bold" style="font-size:0.85rem;">No custody records yet</p>
                    <p class="text-muted" style="font-size:0.78rem;">The chain of custody will populate as workflow actions are
                        taken.</p>
                </div>
            @else
                <x-crm.data-table>
                    <x-slot:header>
                        <tr>
                            <th>Action</th>
                            <th>Action Taker</th>
                            <th>Workflow Stage</th>
                            <th>Comments</th>
                            <th>Date</th>
                        </tr>
                    </x-slot:header>
                    @foreach($chainOfCustody as $custody)
                        <tr>
                            <td class="font-weight-medium" style="font-size:0.82rem;">
                                @if(str_contains($custody->workflow_stage, 'Approval') || str_contains($custody->action, 'Why-Why') || str_contains($custody->action, 'CAPA Initialized') || str_contains($custody->action, 'Finalized'))
                                    <i class="mdi mdi-check-decagram text-success mr-1"
                                        style="font-size:1.2rem;vertical-align:middle;" title="Process Action"></i>
                                @endif
                                {{ strip_tags($custody->action) }}
                            </td>
                            <td style="font-size:0.82rem;">{{ $custody->actionTaker->name ?? '-' }}</td>
                            <td>
                                <span class="crm-badge crm-badge-neutral">
                                    {{ $custody->workflow_stage }}
                                </span>
                            </td>
                            <td class="text-wrap" style="max-width: 250px; font-size:0.82rem;">
                                {!! $custody->comments ?? '-' !!}
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                {{ \Carbon\Carbon::parse($custody->move_out_date ?? $custody->created_at)->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </x-crm.data-table>
            @endif

        </div>
    @endif

    @if($viewMode === 'modal')
        <style>
            .select2-container {
                z-index: 100000 !important;
            }

            .select2-dropdown {
                z-index: 100000 !important;
            }

            /* Standard Form Styling Replicated from complaint-form.blade.php */
            #workflowActionModal .modal-content {
                border-radius: 12px;
                border: none;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                overflow: hidden;
            }

            #workflowActionModal .modal-header {
                padding: 1.5rem 1.5rem 0.5rem;
                border-bottom: none;
            }

            #workflowActionModal .modal-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #1e293b;
                letter-spacing: -0.025em;
            }

            #workflowActionModal .modal-body {
                padding: 1.5rem !important;
                background-color: #fff;
            }

            #workflowActionModal .description-text {
                font-size: 0.95rem;
                line-height: 1.6;
                color: #64748b;
                margin-bottom: 1.5rem;
            }

            #workflowActionModal .description-text b, 
            #workflowActionModal .description-text strong {
                color: #1e293b;
                font-weight: 600;
            }

            #workflowActionModal hr {
                border-top: 1px solid #f1f5f9;
                margin: 1.5rem 0;
            }

            #workflowActionModal .btn-light {
                background-color: #f8fafc;
                border: 1px solid #f1f5f9;
                color: #475569;
                font-weight: 600;
                transition: all 0.2s;
            }

            #workflowActionModal .btn-light:hover {
                background-color: #f1f5f9;
                color: #1e293b;
            }

            #workflowActionModal .btn-success {
                background: #16a34a;
                border-color: #16a34a;
                color: #ffffff;
                font-weight: 600;
                padding: 10px 24px;
                border-radius: 8px;
                transition: all 0.2s;
            }

            #workflowActionModal .btn-success:hover {
                background: #15803d;
                border-color: #15803d;
                box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
                transform: translateY(-1px);
            }

            #workflowActionModal .btn-danger {
                background: #ef4444;
                border-color: #ef4444;
                font-weight: 600;
                padding: 10px 24px;
                border-radius: 8px;
            }

            #workflowActionModal .modal-footer {
                padding: 1rem 1.5rem 1.5rem;
                border-top: none;
                display: flex;
                justify-content: center;
                gap: 0.75rem;
            }

            #workflowActionModal .form-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: #334155;
                margin-bottom: 0.5rem;
                display: block;
            }

            /* Editor improvements */
            .tox-tinymce {
                border: 1px solid #e2e8f0 !important;
                border-radius: 8px !important;
            }
        </style>
        <!-- Workflow Action Modal -->
        @teleport('body')
        <div wire:ignore.self wire:key="workflow-modal-{{ $complaintId ?? 'default' }}" class="modal fade"
            id="workflowActionModal" tabindex="-1" role="dialog" aria-labelledby="workflowActionModalLabel"
            aria-hidden="true" data-backdrop="static" data-keyboard="false" data-livewire-id="{{ $this->getId() }}" x-data="workflowModal">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 460px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="workflowActionModalLabel">{{ $modalTitle ?: 'Workflow Action' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            x-show="workflowState === 'ready'" wire:loading.attr="disabled" wire:target="performAction">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>

                    <!-- Ready State -->
                    <div class="modal-body">
                        <div x-show="workflowState === 'ready'">
                            @if($modalAction === 'approveNext' || $modalAction === 'approveCapa')
                                <div class="description-text">
                                    @if($modalAction === 'approveCapa' || ($modalAction === 'approveNext' && $complaint->complaint_workflow == 2))
                                        You are about to approve the complaint resolution findings and move this case to the **Resolution Approval** stage for final review. You may optionally send the investigation report to the client now.
                                    @elseif($complaint->complaint_workflow == 4)
                                        You are about to provide final **Resolution Approval** and move this complaint to the next stage. Are you sure you want to proceed?
                                    @else
                                        You are about to approve this complaint intake and move it to **Resolution** stage. Are you sure you want to proceed?
                                    @endif
                                </div>

                                @if($modalAction === 'approveCapa' || ($modalAction === 'approveNext' && $complaint->complaint_workflow == 2))
                                    <div class="form-section shadow-sm border p-3 rounded bg-light mb-3">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" id="sendToCustomerToggle_capa" class="custom-control-input" wire:model.live="send_to_customer">
                                            <label class="custom-control-label font-weight-bold text-dark" for="sendToCustomerToggle_capa" style="cursor: pointer;">Send report to Customer?</label>
                                        </div>

                                        @if($send_to_customer)
                                            <div class="form-group mb-0 mt-3" wire:transition>
                                                <label class="form-label font-weight-bold">Select Contacts: <span class="text-danger">*</span></label>
                                                <div wire:ignore>
                                                    <select id="closure_contacts_select" x-init="initClosureContactsSelect2()" class="form-control" multiple style="width: 100%;">
                                                        @foreach($closureContacts ?? [] as $contact)
                                                            @php
                                                                $cId = is_array($contact) ? ($contact['id'] ?? '') : ($contact->id ?? '');
                                                                $cName = trim((is_array($contact) ? ($contact['first_name'] ?? '') : ($contact->first_name ?? '')) . ' ' . (is_array($contact) ? ($contact['last_name'] ?? '') : ($contact->last_name ?? '')));
                                                                $cEmail = is_array($contact) ? ($contact['email'] ?? '') : ($contact->email ?? '');
                                                            @endphp
                                                            <option value="{{ $cId }}">{{ $cName }} ({{ $cEmail }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('selectedContactIds') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            @if($modalAction === 'investigationCheckpoint')
                                <div class="description-text">
                                    @if($modalTitle === 'Update CAR Decision')
                                        You are updating the existing CAR decision. The current selection is pre-filled below — adjust as needed before saving.
                                    @else
                                        Based on your initial findings, please determine if this complaint requires a formal Corrective Action Request (CAR).
                                    @endif
                                </div>
                                
                                <div class="form-section shadow-sm border p-3 rounded bg-light mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                        <div class="pr-3">
                                            <span class="font-weight-bold text-dark d-block">Raise CAR</span>
                                            <small class="text-muted">Initiate a formal CAPA workflow upon resolution finalization.</small>
                                        </div>
                                        <div class="custom-control custom-switch custom-control-lg">
                                            <input type="checkbox" id="modalWorkflowCar" class="custom-control-input" value="1" wire:model.live="car_required">
                                            <label class="custom-control-label" for="modalWorkflowCar" style="cursor: pointer;"></label>
                                        </div>
                                    </div>

                                    @if($car_required)
                                        <div class="d-flex justify-content-between align-items-center" wire:transition>
                                            <div class="pr-3">
                                                <span class="font-weight-bold text-dark d-block">Raise Non-conformance</span>
                                                <small class="text-muted">Include a root cause analysis as part of the NCR process.</small>
                                            </div>
                                            <div class="custom-control custom-switch custom-control-lg">
                                                <input type="checkbox" id="modalWorkflowNcr" class="custom-control-input" value="1" wire:model.live="ncr_required">
                                                <label class="custom-control-label" for="modalWorkflowNcr" style="cursor: pointer;"></label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Action: Reject (Cancel) --}}
                            @if($modalAction === 'reject')
                                <div class="description-text text-danger">
                                    <strong><i class="mdi mdi-alert-circle-outline mr-1"></i>Cancel Complaint</strong><br>
                                    You are about to cancel this complaint. This will move the complaint to the **Cancelled** stage and stop all further processing. Are you sure you want to proceed?
                                </div>
                            @endif

                            {{-- Action: Approve & Notify --}}
                            @if($modalAction === 'approveAndNotify')
                                <div class="description-text">
                                    This action will record the interim approval and automatically attach the **Investigation Form** to this complaint.
                                </div>
                                
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" id="sendToCustomerToggle" class="custom-control-input" wire:model.live="send_to_customer">
                                    <label class="custom-control-label font-weight-bold" for="sendToCustomerToggle" style="cursor: pointer;">Send report to Customer?</label>
                                </div>

                                @if($send_to_customer)
                                    <div class="form-group mb-3" wire:transition>
                                        <label class="form-label">Select Contacts: <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select id="closure_contacts_select" x-init="initClosureContactsSelect2()" class="form-control" multiple style="width: 100%;">
                                                @foreach($closureContacts ?? [] as $contact)
                                                    @php
                                                        $cId = is_array($contact) ? ($contact['id'] ?? '') : ($contact->id ?? '');
                                                        $cName = trim((is_array($contact) ? ($contact['first_name'] ?? '') : ($contact->first_name ?? '')) . ' ' . (is_array($contact) ? ($contact['last_name'] ?? '') : ($contact->last_name ?? '')));
                                                        $cEmail = is_array($contact) ? ($contact['email'] ?? '') : ($contact->email ?? '');
                                                    @endphp
                                                    <option value="{{ $cId }}">{{ $cName }} ({{ $cEmail }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('selectedContactIds') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                    </div>
                                @endif
                            @endif

                            {{-- Action: Save Remarks --}}
                            @if($modalAction === 'saveClosureRemarks')
                                <div class="form-group mb-3">
                                    <label class="form-label">Client Remarks:</label>
                                    <div wire:ignore>
                                        <textarea id="client_remarks_editor_editor" class="form-control" rows="3">{{ $client_remarks }}</textarea>
                                    </div>
                                    @error('client_remarks') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Review Closure Remarks: <span class="text-danger">*</span></label>
                                    <div wire:ignore>
                                        <textarea id="internal_closure_remarks_editor" class="form-control" rows="3">{{ $internal_remarks }}</textarea>
                                    </div>
                                    @error('internal_remarks') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            @endif

                            {{-- Action: Return --}}
                            @if(in_array($modalAction, ['returnToResolution', 'reverseApproval', 'reverseResolution']))
                                <div class="form-group mb-3">
                                    <label class="form-label text-danger">Reason for Return: <span class="text-danger">*</span></label>
                                    <div wire:ignore>
                                        <textarea id="return_reason_editor" class="form-control" rows="3">{{ $comment }}</textarea>
                                    </div>
                                    @error('comment') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            @endif

                            {{-- General Comment Field (for other actions) --}}
                            @if(!empty($modalAction) && !in_array($modalAction, ['saveClosureRemarks', 'investigationCheckpoint', 'approveAndNotify', 'returnToResolution', 'reverseApproval', 'reverseResolution']))
                                <hr>
                                <div class="form-group mb-0">
                                    <label class="form-label">Comments / Notes:</label>
                                    <div wire:ignore>
                                        <textarea id="general_comment_editor" class="form-control" rows="2" placeholder="Add any additional notes...">{{ $comment }}</textarea>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- States (Processing/Success) -->
                        <div class="text-center py-5" x-show="workflowState === 'processing'" style="display: none;">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <h5 class="text-slate-600" x-text="progressText">Processing...</h5>
                        </div>

                        <div class="text-center py-5" x-show="workflowState === 'success'" style="display: none;">
                            <i class="mdi mdi-check-circle text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-success font-weight-bold">Success!</h4>
                            <p class="text-slate-500">Action completed successfully.</p>
                        </div>
                    </div>

                    <div class="modal-footer" x-show="workflowState === 'ready'">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>

                        <button type="button" class="btn {{ $confirmButtonColor }} shadow-sm"
                            x-on:click="submitAction"
                            :disabled="workflowState !== 'ready'">
                            {{ $confirmButtonText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @endif

</div>
@endteleport

@script
<script>
    if (typeof window.initSelect2 !== 'function') {
        window.initSelect2 = function (selector, $wire, model, placeholder = 'Select options...', dropdownParent = null, extraConfig = {}) {
            let $el = $(selector);
            if (!$el.length) return;

            if ($el.hasClass("select2-hidden-accessible")) {
                $el.select2('destroy');
            }

            let options = {
                placeholder: placeholder,
                allowClear: true,
                width: '100%',
                ...extraConfig
            };
            if (dropdownParent) {
                options.dropdownParent = $(dropdownParent);
            }
            $el.select2(options).on('change', function () {
                $wire.set(model, $(this).val());
            });

            // Sync initial value
            let val = $wire.get(model);
            if (val) {
                $el.val(val).trigger('change.select2');
            }
        };
    }

    Alpine.data('workflowContainer', () => ({
        init() {
            if (typeof tinymce === 'undefined') {
                const script = document.createElement('script');
                script.src = '{{ asset('tinymce/tinymce.min.js') }}';
                document.head.appendChild(script);
            }

            this.$wire.on('show-action-modal', () => {
                setTimeout(() => $('#workflowActionModal').modal('show'), 100);
            });
            this.$wire.on('close-action-modal', () => {
                setTimeout(() => $('#workflowActionModal').modal('hide'), 100);
            });
        }
    }));

    Alpine.data('workflowModal', () => ({
        workflowState: 'ready',
        progressText: 'Processing...',
        
        init() {
            this.$wire.on('show-action-modal', () => {
                this.workflowState = 'ready';
                this.progressText = 'Processing...';
                setTimeout(() => {
                    this.initClosureContactsSelect2();
                    this.initTinyMCE();
                }, 350);
            });

            this.$watch('$wire.send_to_customer', (value) => {
                if (value) {
                    setTimeout(() => {
                        this.initClosureContactsSelect2();
                    }, 150);
                }
            });

            Livewire.hook('morph.updated', ({ el, component }) => {
                const select = $('#closure_contacts_select');
                if (select.length && !select.hasClass('select2-hidden-accessible')) {
                    this.initClosureContactsSelect2();
                }

                if (document.getElementById('internal_closure_remarks_editor') && !tinymce.get('internal_closure_remarks_editor')) {
                    this.initTinyMCE();
                }
            });

            this.$wire.on('alert', (data) => {
                const eventData = Array.isArray(data) ? data[0] : (data.detail ? data.detail : data);
                if (eventData.type === 'error' || eventData.status === 'error') {
                    this.workflowState = 'ready';
                }
            });

            this.$wire.on('close-action-modal', () => {
                if (this.workflowState === 'processing') return;
                const closureSelect = $('#closure_contacts_select');
                if (closureSelect.length && closureSelect.hasClass('select2-hidden-accessible')) {
                    closureSelect.select2('destroy');
                }
                tinymce.remove('#internal_closure_remarks_editor');
                tinymce.remove('#client_remarks_editor_editor');
                $('#workflowActionModal').modal('hide');
            });
        },

        initClosureContactsSelect2() {
            window.initSelect2('#closure_contacts_select', this.$wire, 'selectedContactIds', 'Search and select contacts...', '#workflowActionModal', {
                closeOnSelect: false,
                language: {
                    noResults: () => 'No matching contacts found'
                }
            });
        },

        initTinyMCE() {
            if (typeof tinymce === 'undefined') {
                setTimeout(() => this.initTinyMCE(), 200);
                return;
            }

            const config = {
                height: 180,
                menubar: false,
                plugins: 'lists link autoresize',
                toolbar: 'bold italic underline | bullist numlist | link',
                branding: false,
                statusbar: false,
                promotion: false,
                skin: 'oxide',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #334155; padding: 12px; }'
            };

            const editors = [
                '#internal_closure_remarks_editor',
                '#client_remarks_editor_editor',
                '#return_reason_editor',
                '#general_comment_editor',
                '#resolution_client_remarks_editor',
                '#resolution_review_remarks_editor'
            ];

            editors.forEach(selector => {
                if (document.querySelector(selector)) {
                    tinymce.remove(selector);
                    tinymce.init({ ...config, selector: selector });
                }
            });
        },

        async submitAction() {
            let internalRemarks = this.$wire.internal_remarks;
            let clientRemarks = this.$wire.client_remarks;
            let complaintReviewRemarks = this.$wire.complaint_review_remarks;
            let generalComment = this.$wire.comment;

            if (tinymce.get('internal_closure_remarks_editor')) {
                internalRemarks = tinymce.get('internal_closure_remarks_editor').getContent();
            }
            if (tinymce.get('client_remarks_editor_editor')) {
                clientRemarks = tinymce.get('client_remarks_editor_editor').getContent();
            }
            if (tinymce.get('resolution_client_remarks_editor')) {
                clientRemarks = tinymce.get('resolution_client_remarks_editor').getContent();
            }
            if (tinymce.get('resolution_review_remarks_editor')) {
                complaintReviewRemarks = tinymce.get('resolution_review_remarks_editor').getContent();
            }
            if (tinymce.get('return_reason_editor')) {
                generalComment = tinymce.get('return_reason_editor').getContent();
            }
            if (tinymce.get('general_comment_editor')) {
                generalComment = tinymce.get('general_comment_editor').getContent();
            }

            this.$wire.internal_remarks = internalRemarks;
            this.$wire.client_remarks = clientRemarks;
            this.$wire.complaint_review_remarks = complaintReviewRemarks;
            this.$wire.comment = generalComment;

            const contacts = $('#closure_contacts_select').val();
            this.$wire.selectedContactIds = contacts || [];

            const isSending = ['sendReportToClient', 'approveAndNotify', 'approveNext', 'approveCapa', 'closure'].includes('{{ $modalAction }}');
            
            if (isSending && this.$wire.send_to_customer) {
                if (!contacts || contacts.length === 0) {
                    alert('Please select at least one contact to receive the report.');
                    return;
                }
            }

            this.workflowState = 'processing';
            this.progressText = 'Processing...';

            const cleanup = this.$wire.on('close-action-modal', () => {
                if (this.workflowState === 'processing') {
                    this.workflowState = 'success';
                    setTimeout(() => {
                        $('#workflowActionModal').modal('hide');
                    }, 600);
                }
                if (typeof cleanup === 'function') cleanup();
            });

            try {
                await this.$wire.performAction();
            } catch (error) {
                console.error('Action failed:', error);
                this.workflowState = 'ready';
            }
        }
    }));
</script>
@endscript
