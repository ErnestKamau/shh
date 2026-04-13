<div x-data="{
    initSelect2() {
        // Safe Initialization function
        const initSingleSelect2 = (selector, wireModel, placeholder) => {
            let el = $(selector);
            if (el.length) {
                // Check if already initialized and destroy to prevent duplicates
                if (el.hasClass('select2-hidden-accessible')) {
                    el.select2('destroy');
                }
                
                el.select2({
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%',
                    dropdownParent: el.closest('.modal'), // Ensure dropdown works in modal
                    closeOnSelect: !el.prop('multiple') // Only close on select if not multiple
                }).on('change', function (e) {
                    var data = $(this).val();
                    $wire.set(wireModel, data);
                });

                // Initial value check
                let val = $wire.get(wireModel);
                if (val) {
                    el.val(val).trigger('change.select2');
                }
            }
        };

        const reinitAll = () => {
            initSingleSelect2('#receivedFromSelect', 'organization_name', 'Select Customer');
            initSingleSelect2('#contactNameSelect', 'contact_name', $wire.get('received_from_type') === 'Customer' ? 'Select Contact' : 'e.g. John Doe');
            initSingleSelect2('#complaintTypeSelect', 'type', 'Select Type');
            initSingleSelect2('#prioritySelect', 'priority', 'Select Priority');
            initSingleSelect2('#deliveryModeSelect', 'mode_of_delivery', 'Select Mode(s)');
            initSingleSelect2('#testItemSelect', 'test_item', 'Select Test Item');
            initSingleSelect2('#reportSerialNoSelect', 'report_serial_no', 'Select Serial No');
        };

        // Initial run
        reinitAll();

        // Listen for Livewire updates to re-initialize
        Livewire.hook('morph.updated', (node, content) => {
            reinitAll();
        });
    },
    initTinyMCE() {
        if (typeof tinymce === 'undefined') return;
        
        // Destroy existing instance if any
        if (tinymce.get('complaint-description')) {
            tinymce.get('complaint-description').remove();
        }

        tinymce.init({
            selector: '#complaint-description',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            setup: (editor) => {
                editor.on('init', () => {
                    editor.setContent($wire.get('description') || '');
                });
                editor.on('change blur', () => {
                    $wire.set('description', editor.getContent());
                });
            }
        });

        // Re-init on Livewire update if necessary
        Livewire.on('reinit-tinymce', () => {
            if (tinymce.get('complaint-description')) {
                tinymce.get('complaint-description').setContent($wire.get('description') || '');
            }
        });
    }
}" x-init="initSelect2(); initTinyMCE()">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">
                <i class="mdi mdi-{{ $complaintId ? 'pencil' : 'plus' }}"></i>
                {{ $complaintId ? 'Edit' : 'Add' }} Complaint
            </h4>
            <button type="button" class="close" wire:click="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="modal-body">
                <div class="row">
                    <!-- BLOCK A: Complaint Information -->
                    <div class="col-md-4 border-right">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size:0.75rem; letter-spacing:0.05em;"><i class="mdi mdi-domain mr-1"></i>Complaint Information</h6>
                        
                        <div class="form-group">
                            <label class="control-label d-block">Complaint Received From <span class="text-danger">*</span></label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="typeCustomer" name="received_from_type" class="custom-control-input" value="Customer" wire:model.live="received_from_type">
                                <label class="custom-control-label" for="typeCustomer">Customer</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="typeOther" name="received_from_type" class="custom-control-input" value="Other Party" wire:model.live="received_from_type">
                                <label class="custom-control-label" for="typeOther">Other Party</label>
                            </div>
                        </div>

                        <div class="form-group" wire:ignore.self>
                            <label class="control-label">Organization Name <span class="text-danger">*</span></label>
                            @if($received_from_type === 'Customer')
                                <div wire:ignore>
                                    <select class="form-control" id="receivedFromSelect" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->name }}" {{ ($organization_name ?? $received_from) == $customer->name ? 'selected' : '' }}>
                                                {{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="text" class="form-control @error('organization_name') is-invalid @enderror" wire:model="organization_name" placeholder="Enter Organization Name..." required />
                            @endif
                            @error('organization_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="control-label">Contact Name <span class="text-danger">*</span></label>
                            @if($received_from_type === 'Customer')
                                <div wire:key="contact-select-wrapper-{{ $customerId }}-{{ count($contacts) }}">
                                    <select class="form-control" id="contactNameSelect" required>
                                        <option value="">Select Contact</option>
                                        @foreach($contacts as $contact)
                                            <option value="{{ $contact->id }}" {{ $contact_name == $contact->id ? 'selected' : '' }}>{{ $contact->first_name }} {{ $contact->middle_name }} {{ $contact->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="text" class="form-control @error('contact_name') is-invalid @enderror" wire:model="contact_name" placeholder="e.g. John Doe" required />
                            @endif
                            @error('contact_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="control-label text-muted">Title / Position</label>
                            <div class="p-2 border rounded" style="background-color: #f8f9fa; min-height: 38px;">
                                <span class="font-weight-bold {{ $title_position ? 'text-dark' : 'text-muted italic' }}" style="font-size: 0.85rem;">
                                    {{ $title_position ?: 'Select a contact to view title' }}
                                </span>
                            </div>
                            @error('title_position') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>


                        @if($received_from_type === 'Customer')
                            <div class="form-group mt-4">
                                <label class="control-label d-block text-danger font-weight-bold">Related to Laboratory Activities? <span class="text-danger">*</span></label>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="labRelatedYes" name="is_lab_related" class="custom-control-input" value="1" wire:model.live="is_lab_related">
                                    <label class="custom-control-label font-weight-bold" for="labRelatedYes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="labRelatedNo" name="is_lab_related" class="custom-control-input" value="0" wire:model.live="is_lab_related">
                                    <label class="custom-control-label font-weight-bold" for="labRelatedNo">No</label>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- BLOCK B: Logging Details -->
                    <div class="col-md-4 border-right">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size:0.75rem; letter-spacing:0.05em;"><i class="mdi mdi-calendar-clock mr-1"></i>Logging Details</h6>
                        
                        <div class="form-group">
                            <label class="control-label">Complaint Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" wire:model="date" required />
                            @error('date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group" wire:ignore>
                            <label class="control-label">How Received <span class="text-danger">*</span></label>
                            <select class="form-control" id="deliveryModeSelect" multiple required style="height: 100px;">
                                <option value="Phone">Phone</option>
                                <option value="E-mail">E-mail</option>
                                <option value="Fax">Fax</option>
                                <option value="Verbal/Meeting">Verbal / Meeting</option>
                                <option value="Other">Other</option>
                            </select> 
                            @error('mode_of_delivery') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group mt-4">
                            <label class="control-label">Complaint Category <span class="text-danger">*</span></label>
                            <div wire:ignore>
                                <select class="form-control" id="complaintTypeSelect" required>
                                    <option value="">Select Type</option>
                                    @foreach($complaint_types as $complaint_type)
                                        <option value="{{ $complaint_type->name }}" {{ $type == $complaint_type->name ? 'selected' : '' }}>{{ $complaint_type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">Severity <span class="text-danger">*</span></label>
                            <div wire:ignore>
                                <select class="form-control" id="prioritySelect" required>
                                    <option value="">Select Priority</option>
                                    @foreach(['High', 'Medium', 'Low'] as $p)
                                        <option value="{{ $p }}" {{ $priority == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('priority') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- BLOCK C: Nature of Complaint -->
                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size:0.75rem; letter-spacing:0.05em;"><i class="mdi mdi-alert-circle-outline mr-1"></i>Nature of Complaint</h6>
                        
                        @if($received_from_type === 'Customer' && $is_lab_related)
                            <div class="form-group">
                                <label class="control-label">Test Item</label>
                                <div wire:key="test-item-select-wrapper-{{ $customerId }}-{{ count($samples) }}">
                                    <select class="form-control" id="testItemSelect">
                                        <option value="">Select Test Item</option>
                                        @foreach($samples as $sample)
                                            <option value="{{ $sample->id }}" {{ $test_item == $sample->id ? 'selected' : '' }}>{{ $sample->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('test_item') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label class="control-label">Report Serial No (Batch No)</label>
                                <div wire:key="serial-no-select-wrapper-{{ $test_item }}-{{ count($serial_nos) }}">
                                    <select class="form-control" id="reportSerialNoSelect">
                                        <option value="">Select Serial No</option>
                                        @foreach($serial_nos as $batch_code)
                                            <option value="{{ $batch_code }}" {{ $report_serial_no == $batch_code ? 'selected' : '' }}>{{ $batch_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('report_serial_no') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="form-group mb-0" wire:ignore>
                            <label class="control-label">Complaint Details <span class="text-danger">*</span></label>
                            <textarea id="complaint-description" class="form-control @error('description') is-invalid @enderror" placeholder="Provide full details here..."></textarea>
                            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><i class="mdi mdi-content-save"></i> Save</span>
                    <span wire:loading wire:target="save"><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                </button>
                <button type="button" class="btn btn-default" wire:click="close">Close</button>
            </div>
        </form>


    </div>