<div @if($viewMode === 'modal') x-data="{
    init() {
        Livewire.on('show-action-modal', () => {
           // We are now using teleport body, so no need to detach or manually append the modal.
           // Add a small delay so Alpine's x-teleport has time to place the element in the body before jQuery accesses it
           setTimeout(() => {
               $('#workflowActionModal').modal('show');
           }, 100);
        });
        Livewire.on('close-action-modal', () => {
           setTimeout(() => {
               $('#workflowActionModal').modal('hide');
           }, 100);
        });
    }
}" @endif class="complaint-workflow-container">

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
                                {{ $custody->action }}
                            </td>
                            <td style="font-size:0.82rem;">{{ $custody->actionTaker->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-light border text-dark" style="font-size:0.75rem;">
                                    {{ $custody->workflow_stage }}
                                </span>
                            </td>
                            <td class="text-wrap" style="max-width: 250px; font-size:0.82rem;">
                                {{ $custody->comments ?? '-' }}
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
        </style>
        <!-- Workflow Action Modal -->
        @teleport('body')
        <div wire:ignore.self wire:key="workflow-modal-{{ $complaintId ?? 'default' }}" class="modal fade"
            id="workflowActionModal" tabindex="-1" role="dialog" aria-labelledby="workflowActionModalLabel"
            aria-hidden="true" data-backdrop="static" data-keyboard="false" data-livewire-id="{{ $this->getId() }}" x-data="{
                                                                                    workflowState: 'ready',
                                                                                    progressText: 'Processing...',
                                                                                    progressStep: 0,
                                                                                    intervalId: null,
                                                                                    wireId: null,
                                                                                    componentRef: null,

                                                                                    resetState() {
                                                                                        this.workflowState = 'ready';
                                                                                        this.progressText = 'Processing...';
                                                                                        this.progressStep = 0;
                                                                                        if (this.intervalId) clearInterval(this.intervalId);
                                                                                    },

                                                                                    initClosureContactsSelect2() {
                                                                                        const select = $('#closure_contacts_select');
                                                                                        if (!select.length || typeof $.fn.select2 === 'undefined') return;
                                                                                        // Destroy existing Select2 instance before re-initializing
                                                                                        if (select.hasClass('select2-hidden-accessible')) {
                                                                                            select.select2('destroy');
                                                                                        }
                                                                                        // Determine parent: use the modal if it's visible, otherwise body
                                                                                        const modalEl = $('#workflowActionModal');
                                                                                        select.select2({
                                                                                            placeholder: 'Search and select contacts...',
                                                                                            allowClear: true,
                                                                                            width: '100%',
                                                                                            dropdownParent: modalEl.length ? modalEl : $('body'),
                                                                                            closeOnSelect: false,
                                                                                            language: {
                                                                                                noResults: function() {
                                                                                                    return 'No matching contacts found';
                                                                                                }
                                                                                            }
                                                                                        }).off('change.closure').on('change.closure', function () {
                                                                                            const data = $(this).val();
                                                                                            @this.set('selectedContactIds', data || []);
                                                                                        });
                                                                                        // Reset visual selection without triggering a Livewire re-render
                                                                                        select.val(null).trigger('change.select2');
                                                                                    },

                                                                                    init() {
                                                                                        // Read the Livewire component ID injected by PHP into the data attribute.
                                                                                        // This is reliable even after Bootstrap moves the modal to <body>.
                                                                                        this.wireId = this.$el.getAttribute('data-livewire-id');

                                                                                        // Store component reference when initialized
                                                                                        // Find the component's root element (the complaint-workflow-container)
                                                                                        // This must be done BEFORE Bootstrap moves the modal to <body>
                                                                                        this.findAndStoreComponent();

                                                                                        Livewire.on('show-action-modal', () => {
                                                                                            this.resetState();
                                                                                            // Refresh component reference when modal is shown
                                                                                            this.findAndStoreComponent();
                                                                                            // Wait for Livewire DOM update + Bootstrap modal animation before initializing Select2
                                                                                            setTimeout(() => this.initClosureContactsSelect2(), 350);
                                                                                        });

                                                                                        Livewire.on('alert', (data) => {
                                                                                            if (data.type === 'error') {
                                                                                                this.workflowState = 'ready';
                                                                                                if (this.intervalId) clearInterval(this.intervalId);
                                                                                            }
                                                                                        });

                                                                                        Livewire.on('close-action-modal', () => {
                                                                                            if (this.workflowState === 'processing') {
                                                                                                return;
                                                                                            }
                                                                                            const closureSelect = $('#closure_contacts_select');
                                                                                            if (closureSelect.length && closureSelect.hasClass('select2-hidden-accessible')) {
                                                                                                closureSelect.select2('destroy');
                                                                                            }
                                                                                            $('#workflowActionModal').modal('hide');
                                                                                        });
                                                                                    },

                                                                                    findAndStoreComponent() {
                                                                                        // Method 1: Find the component's root container element
                                                                                        const rootContainer = document.querySelector('.complaint-workflow-container[wire\\:id]');
                                                                                        if (rootContainer) {
                                                                                            const rootId = rootContainer.getAttribute('wire:id');
                                                                                            if (rootId && typeof Livewire !== 'undefined' && Livewire.find) {
                                                                                                try {
                                                                                                    const component = Livewire.find(rootId);
                                                                                                    // Verify it's a valid Livewire component (has call method or $wire)
                                                                                                    if (component && (typeof component.call === 'function' || component.$wire)) {
                                                                                                        this.componentRef = component;
                                                                                                        console.log('Found component by rootId:', rootId, component);
                                                                                                        return;
                                                                                                    }
                                                                                                } catch(e) {
                                                                                                    console.warn('Error finding component by rootId:', e);
                                                                                                }
                                                                                            }
                                                                                        }

                                                                                        // Method 2: Try to find by wireId from data attribute
                                                                                        if (this.wireId && typeof Livewire !== 'undefined' && Livewire.find) {
                                                                                            try {
                                                                                                const component = Livewire.find(this.wireId);
                                                                                                // Verify it's a valid Livewire component
                                                                                                if (component && (typeof component.call === 'function' || component.$wire)) {
                                                                                                    this.componentRef = component;
                                                                                                    console.log('Found component by wireId:', this.wireId, component);
                                                                                                    return;
                                                                                                }
                                                                                            } catch(e) {
                                                                                                console.warn('Error finding component by wireId:', e);
                                                                                            }
                                                                                        }

                                                                                        // Method 3: Search all Livewire components
                                                                                        if (typeof Livewire !== 'undefined' && Livewire.all) {
                                                                                            try {
                                                                                                const components = Livewire.all();
                                                                                                for (let id in components) {
                                                                                                    const component = components[id];
                                                                                                    // Match by ID and verify it's a valid component
                                                                                                    if (component && (id === this.wireId || (component.getId && component.getId() === this.wireId))) {
                                                                                                        // Verify it has the call method or $wire property
                                                                                                        if (typeof component.call === 'function' || component.$wire) {
                                                                                                            this.componentRef = component;
                                                                                                            console.log('Found component in all() by ID:', id, component);
                                                                                                            return;
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            } catch(e) {
                                                                                                console.warn('Error searching all components:', e);
                                                                                            }
                                                                                        }

                                                                                        // Method 4: Try to find via DOM element's __livewire property
                                                                                        const rootContainerEl = document.querySelector('.complaint-workflow-container[wire\\:id]');
                                                                                        if (rootContainerEl && rootContainerEl.__livewire) {
                                                                                            this.componentRef = rootContainerEl.__livewire;
                                                                                            console.log('Found component via __livewire property:', this.componentRef);
                                                                                            return;
                                                                                        }
                                                                                    },

                                                                                    getComponent() {
                                                                                        // First, try the stored component reference
                                                                                        if (this.componentRef) {
                                                                                            // Verify it's still valid
                                                                                            try {
                                                                                                if (this.componentRef.getId && this.componentRef.getId() === this.wireId) {
                                                                                                    return this.componentRef;
                                                                                                }
                                                                                            } catch(e) {
                                                                                                // Component reference is stale, clear it
                                                                                                this.componentRef = null;
                                                                                            }
                                                                                        }

                                                                                        // If no valid reference, try to find it again
                                                                                        this.findAndStoreComponent();
                                                                                        return this.componentRef;
                                                                                    },

                                                                                    submitAction() {
                                                                                        // Prevent double-clicks
                                                                                        if (this.workflowState !== 'ready') {
                                                                                            console.warn('Action already in progress, ignoring click');
                                                                                            return;
                                                                                        }

                                                                                        // Require at least one contact for Send Report & Close, and sync selection to Livewire
                                                                                        if ('{{ $modalAction }}' === 'sendReportAndClose') {
                                                                                            const select = $('#closure_contacts_select');
                                                                                            const val = select.val();
                                                                                            if (!val || (Array.isArray(val) && val.length === 0)) {
                                                                                                alert('Please select at least one contact to receive the report.');
                                                                                                return;
                                                                                            }
                                                                                            // Sync Select2 value to Livewire so performAction receives selectedContactIds
                                                                                            const ids = Array.isArray(val) ? val : (val ? [val] : []);
                                                                                            @this.set('selectedContactIds', ids);
                                                                                        }

                                                                                        // Set processing state first
                                                                                        if ('{{ $modalAction }}' === 'approveResolution') {
                                                                                            this.workflowState = 'processing';
                                                                                            this.progressStep = 1;
                                                                                            this.progressText = 'Saving Resolution...';

                                                                                            this.intervalId = setInterval(() => {
                                                                                                if (this.progressStep === 1) {
                                                                                                    this.progressStep = 2;
                                                                                                    this.progressText = 'Generating PDF Report...';
                                                                                                } else if (this.progressStep === 2) {
                                                                                                    this.progressStep = 3;
                                                                                                    this.progressText = 'Sending Email to Customer...';
                                                                                                    clearInterval(this.intervalId);
                                                                                                }
                                                                                            }, 2500);
                                                                                        } else {
                                                                                            this.workflowState = 'processing';
                                                                                            this.progressText = 'Processing...';
                                                                                        }

                                                                                        // Method 1: Try to click the hidden button with wire:click (most reliable)
                                                                                        const hiddenBtn = this.$refs.hiddenSubmitBtn || document.getElementById('hidden-perform-action-btn');
                                                                                        if (hiddenBtn) {
                                                                                            // Register a temporary listener via closure
                                                                                            const cleanup = Livewire.on('close-action-modal', () => {
                                                                                                if (this.workflowState === 'processing') {
                                                                                                    this.workflowState = 'success';
                                                                                                    if (this.intervalId) clearInterval(this.intervalId);
                                                                                                    setTimeout(() => {
                                                                                                        $('#workflowActionModal').modal('hide');
                                                                                                    }, 600);
                                                                                                }
                                                                                                // In Livewire v3, executing the function returned by Livewire.on unregisters it
                                                                                                if (typeof cleanup === 'function') cleanup();
                                                                                            });

                                                                                            // Set timeout fallback
                                                                                            setTimeout(() => {
                                                                                                if (this.workflowState === 'processing') {
                                                                                                    this.workflowState = 'ready';
                                                                                                    if (this.intervalId) clearInterval(this.intervalId);
                                                                                                }
                                                                                                if (typeof cleanup === 'function') cleanup();
                                                                                            }, 10000);

                                                                                            // Trigger the hidden button click
                                                                                            hiddenBtn.click();
                                                                                            return; // Exit - Livewire will handle the rest
                                                                                        }

                                                                                        // Method 2: Fallback - Try to find component and call method
                                                                                        let component = this.getComponent();
                                                                                        if (!component) {
                                                                                            console.error('Livewire component not found. wireId:', this.wireId);
                                                                                            this.findAndStoreComponent();
                                                                                            component = this.componentRef;

                                                                                            if (!component) {
                                                                                                alert('Unable to connect to the server. Please refresh the page and try again.');
                                                                                                this.workflowState = 'ready';
                                                                                                return;
                                                                                            }
                                                                                        }

                                                                                        // Try to call performAction using Livewire v3 API
                                                                                        try {
                                                                                            // Try component.call() (Livewire v3 standard)
                                                                                            if (typeof component.call === 'function') {
                                                                                                const result = component.performAction();

                                                                                                if (result && typeof result.then === 'function') {
                                                                                                    result.then(() => {
                                                                                                        this.workflowState = 'success';
                                                                                                        if (this.intervalId) clearInterval(this.intervalId);
                                                                                                        setTimeout(() => {
                                                                                                            $('#workflowActionModal').modal('hide');
                                                                                                        }, 600);
                                                                                                    }).catch((error) => {
                                                                                                        console.error('Error performing action:', error);
                                                                                                        alert('An error occurred: ' + (error.message || 'Unknown error'));
                                                                                                        this.workflowState = 'ready';
                                                                                                        if (this.intervalId) clearInterval(this.intervalId);
                                                                                                    });
                                                                                                    return;
                                                                                                }
                                                                                            }

                                                                                            // Try component.$wire.call()
                                                                                            if (component.$wire && typeof component.$wire.call === 'function') {
                                                                                                const result = component.performAction();

                                                                                                if (result && typeof result.then === 'function') {
                                                                                                    result.then(() => {
                                                                                                        this.workflowState = 'success';
                                                                                                        if (this.intervalId) clearInterval(this.intervalId);
                                                                                                        setTimeout(() => {
                                                                                                            $('#workflowActionModal').modal('hide');
                                                                                                        }, 600);
                                                                                                    }).catch((error) => {
                                                                                                        console.error('Error performing action:', error);
                                                                                                        alert('An error occurred: ' + (error.message || 'Unknown error'));
                                                                                                        this.workflowState = 'ready';
                                                                                                        if (this.intervalId) clearInterval(this.intervalId);
                                                                                                    });
                                                                                                    return;
                                                                                                }
                                                                                            }

                                                                                            console.error('Unable to call performAction - component found but method not accessible');
                                                                                            console.error('Component:', component);
                                                                                            alert('Unable to process request. Please refresh the page and try again.');
                                                                                            this.workflowState = 'ready';

                                                                                        } catch (error) {
                                                                                            console.error('Error calling performAction:', error);
                                                                                            alert('An error occurred: ' + (error.message || 'Unknown error'));
                                                                                            this.workflowState = 'ready';
                                                                                            if (this.intervalId) clearInterval(this.intervalId);
                                                                                        }
                                                                                    }
                                                                                }" x-init="init()">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="workflowActionModalLabel">{{ $modalTitle ?: 'Workflow Action' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            x-show="workflowState === 'ready'" wire:loading.attr="disabled" wire:target="performAction">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Ready State -->
                    <div class="modal-body" x-show="workflowState === 'ready'">
                        @if($modalAction === 'sendReportAndClose' || $modalAction === 'closeComplaint')
                            @if(!empty($closureContacts))
                            <div class="form-group">
                                <label for="closure_contacts_select">
                                    Contacts to Receive Report: <span class="text-danger">*</span>
                                </label>
                                <div wire:ignore>
                                    <select id="closure_contacts_select" class="form-control no-select2" multiple
                                        style="width: 100%;">
                                        @foreach($closureContacts ?? [] as $contact)
                                            @php
                                                $cId = is_array($contact) ? ($contact['id'] ?? '') : ($contact->id ?? '');
                                                $cFirst = is_array($contact) ? ($contact['first_name'] ?? '') : ($contact->first_name ?? '');
                                                $cMiddle = is_array($contact) ? ($contact['middle_name'] ?? '') : ($contact->middle_name ?? '');
                                                $cLast = is_array($contact) ? ($contact['last_name'] ?? '') : ($contact->last_name ?? '');
                                                $cEmail = is_array($contact) ? ($contact['email'] ?? '') : ($contact->email ?? '');
                                            @endphp
                                            <option value="{{ $cId }}">
                                                {{ trim($cFirst . ' ' . $cMiddle . ' ' . $cLast) }}
                                                ({{ $cEmail }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(empty($closureContacts))
                                    <small class="text-warning">
                                        <i class="mdi mdi-alert-outline"></i>
                                        No active contacts with "Receives Report" enabled for this customer.
                                        Add contacts in the customer's Contacts tab and enable 'Receives Report'.
                                    </small>
                                @endif
                            </div>
                            @endif
                        @endif
                        <div class="form-group">
                            <label>Comments:</label>
                            <textarea wire:model="comment" class="form-control" rows="3"></textarea>
                        </div>

                        {{-- CAR Requirement Toggle - ONLY Stage 1 Approval --}}
                        @if($modalAction === 'approveNext' && $complaint->complaint_workflow == 1)
                            <div class="mt-4 p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 0.9rem;">Is a Corrective Action Request (CAR) required? <span class="text-danger">*</span></span>
                                    <small class="text-muted">Choosing 'Yes' will initiate a formal CAPA workflow after investigation.</small>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 20px;">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="modalWorkflowCarYes" name="car_required_wf" class="custom-control-input" value="1" wire:model.live="car_required">
                                        <label class="custom-control-label font-weight-bold text-danger" for="modalWorkflowCarYes" style="font-size: 0.85rem; cursor: pointer;">YES</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="modalWorkflowCarNo" name="car_required_wf" class="custom-control-input" value="0" wire:model.live="car_required">
                                        <label class="custom-control-label font-weight-bold text-success" for="modalWorkflowCarNo" style="font-size: 0.85rem; cursor: pointer;">NO</label>
                                    </div>
                                </div>
                            </div>
                            @error('car_required') <div class="text-right"><span class="text-danger small">{{ $message }}</span></div> @enderror
                        @endif
                    </div>

                    <!-- Processing State -->
                    <div class="modal-body text-center p-5" x-show="workflowState === 'processing'" style="display: none;">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <h5 class="text-muted" x-text="progressText">Processing...</h5>
                    </div>

                    <!-- Success State -->
                    <div class="modal-body text-center p-5" x-show="workflowState === 'success'" style="display: none;">
                        <i class="mdi mdi-check-circle-outline text-success" style="font-size: 5rem; line-height: 1;"></i>
                        <h4 class="mt-3 text-success">Success!</h4>
                        <p class="text-muted">Action completed successfully.</p>
                    </div>

                    <div class="modal-footer" x-show="workflowState === 'ready'">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            wire:key="close-btn-{{ $complaintId ?? 'default' }}"
                            :disabled="workflowState !== 'ready'">Close</button>

                        <!-- Hidden button with wire:click for Livewire to handle -->
                        <button type="button"
                            wire:key="hidden-submit-btn-{{ $complaintId ?? 'default' }}-{{ $modalAction ?? 'default' }}"
                            wire:click="performAction" id="hidden-perform-action-btn" style="display: none;"
                            x-ref="hiddenSubmitBtn">
                            {{ $confirmButtonText }}
                        </button>

                        <!-- Visible button that triggers the hidden one -->
                        <button type="button" class="btn {{ $confirmButtonColor }}"
                            wire:key="submit-btn-{{ $complaintId ?? 'default' }}-{{ $modalAction ?? 'default' }}"
                            x-on:click="submitAction"
                            :disabled="workflowState !== 'ready' {{ $modalAction === 'sendReportAndClose' && empty($selectedContactIds) ? '|| true' : '' }}">
                            <span>{{ $confirmButtonText }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @endif

</div>
@endteleport