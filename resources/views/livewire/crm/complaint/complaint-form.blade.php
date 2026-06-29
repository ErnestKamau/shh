<div class="complaint-form-wrapper" x-data="complaintFormHandler" x-init="init()">
    <style>
        /* Force width of the modal-dialog from within */
        .modal-dialog:has(.complaint-form-wrapper) {
            min-width: 850px !important;
        }
        @media (max-width: 900px) {
            .modal-dialog:has(.complaint-form-wrapper) {
                min-width: 95% !important;
            }
        }

        .form-section {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
        }

        .form-section-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 8px;
        }
        .form-section-title i {
            margin-right: 12px;
            font-size: 1.3rem;
            color: #007bff;
        }
        .modal-body {
            background-color: #f4f7fa;
            max-height: 65vh;
            overflow-y: auto;
            padding: 15px 20px !important;
        }
        .modal-content {
            border-radius: 12px;
        }
        /* Radio Styling */
        .custom-radio .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }
        .custom-radio .custom-control-label {
            font-weight: 600;
            color: #343a40;
            cursor: pointer;
            padding-top: 3px;
        }
        .field-container {
            min-height: 75px;
        }
    </style>

    {{-- The script block is handled by Livewire 3 @script directive --}}
    @script
    <script>
        Alpine.data('complaintFormHandler', () => ({
            _tinyInitialized: false,

            init() {
                this.$nextTick(() => {
                    this.setModalWidth();
                });
                
                this.initTiny();
                
                this.$wire.on('reinit-tinymce', () => {
                    const ed = tinymce.get('complaint-description');
                    if (ed) {
                        ed.setContent(this.$wire.get('description') || '');
                    } else {
                        this._tinyInitialized = false;
                        this.initTiny();
                    }
                });
            },

            setModalWidth() {
                let dialog = this.$el.closest('.modal-dialog');
                if (dialog) {
                    dialog.classList.remove('modal-sm');
                    dialog.classList.add('modal-lg');
                    dialog.style.width = '850px';
                    dialog.style.maxWidth = '95%';
                }
            },

            initSelect2(el, wireModel, placeholder) {
                let $el = $(el);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }

                $el.select2({
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $(document.body)
                }).on('change', (e) => {
                    this.$wire.set(wireModel, $el.val());
                });

                // Sync initial value
                let val = this.$wire.get(wireModel);
                if (val) {
                    $el.val(val).trigger('change.select2');
                }
            },

            initTiny() {
                if (this._tinyInitialized) return;

                if (typeof tinymce === 'undefined') {
                    if (!document.querySelector("script[src='/tinymce/tinymce.min.js']")) {
                        const script = document.createElement('script');
                        script.src = '/tinymce/tinymce.min.js';
                        script.onload = () => {
                            this._tinyInitialized = false;
                            this.initTiny();
                        };
                        document.head.appendChild(script);
                    }
                    return;
                }

                const existing = tinymce.get('complaint-description');
                if (existing) {
                    existing.remove();
                }

                tinymce.init({
                    selector: '#complaint-description',
                    height: 180,
                    menubar: false,
                    plugins: [ 'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount' ],
                    toolbar: 'undo redo | bold italic | bullist numlist outdent indent | removeformat',
                    setup: (editor) => {
                        editor.on('init', () => {
                            editor.setContent(this.$wire.get('description') || '');
                        });
                        editor.on('blur change', () => {
                            this.$wire.set('description', editor.getContent());
                        });
                    }
                });
                
                this._tinyInitialized = true;
            }
        }));
    </script>
    @endscript

    <div class="modal-content border-0">
        <div class="modal-header bg-white border-bottom-0 pt-3 px-4">
            <h4 class="modal-title text-dark font-weight-bold d-flex align-items-center">
                <div class="bg-primary-light p-2 rounded mr-3" style="background: rgba(0, 123, 255, 0.1);">
                    <i class="mdi mdi-{{ $complaintId ? 'pencil' : 'plus' }} text-primary"></i>
                </div>
                {{ $complaintId ? 'Edit' : 'Add' }} Complaint
            </h4>
            <button type="button" class="close" wire:click="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form wire:submit.prevent="save">
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger py-2 mb-3 shadow-sm border-0" style="border-radius: 8px;">
                        <ul class="mb-0 small font-weight-bold">
                            @foreach ($errors->all() as $error)
                                <li><i class="mdi mdi-alert-circle mr-1"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- SECTION 01: COMPLAINT INFORMATION -->
                <div class="form-section" wire:key="section-01-info">
                    <h6 class="form-section-title"><i class="mdi mdi-domain"></i>01. Complaint Information</h6>
                    
                    <div class="form-group mb-3 pl-1">
                        <label class="control-label d-block text-dark font-weight-bold mb-3" style="font-size: 1.05rem;">Complaint Received From <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center">
                            <div class="custom-control custom-radio custom-control-inline mr-5">
                                <input type="radio" id="typeCustomer" value="Customer" class="custom-control-input" wire:model.live="received_from_type">
                                <label class="custom-control-label" for="typeCustomer" style="font-size: 1.1rem;">Customer</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="typeOther" value="Other Party" class="custom-control-input" wire:model.live="received_from_type">
                                <label class="custom-control-label" for="typeOther" style="font-size: 1.1rem;">Other Party</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group field-container" wire:key="org-wrapper-{{ $received_from_type }}">
                                <label class="control-label font-weight-bold">Organization Name <span class="text-danger">*</span></label>
                                @if($received_from_type === 'Customer')
                                    <div wire:ignore x-init="initSelect2($el.querySelector('select'), 'organization_name', 'Select Customer')">
                                        <select class="form-control" required>
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
                                @error('organization_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group field-container" wire:key="contact-wrapper-{{ $received_from_type }}-{{ count($contacts) }}">
                                <label class="control-label font-weight-bold">Contact Name <span class="text-danger">*</span></label>
                                @if($received_from_type === 'Customer')
                                    <div wire:ignore x-init="initSelect2($el.querySelector('select'), 'contact_name', 'Select Contact')">
                                        <select class="form-control" required>
                                            <option value="">Select Contact</option>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->id }}" {{ $contact_name == $contact->id ? 'selected' : '' }}>{{ $contact->first_name }} {{ $contact->middle_name }} {{ $contact->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="text" class="form-control @error('contact_name') is-invalid @enderror" wire:model="contact_name" placeholder="e.g. John Doe" required />
                                @endif
                                @error('contact_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group field-container" wire:key="field-title">
                                <label class="control-label text-muted small font-weight-bold">Title / Position</label>
                                <div class="p-2 border rounded bg-white shadow-sm" style="min-height: 40px; border-color: #ddd !important;">
                                    <span class="small font-weight-bold {{ $title_position ? 'text-dark' : 'text-muted italic' }}">
                                        {{ $title_position ?: 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($received_from_type === 'Customer')
                                <div class="form-group field-container" wire:key="lab-related-wrapper">
                                    <label class="control-label small text-danger font-weight-bold">Related to Laboratory Activities? <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center mt-2">
                                        <div class="custom-control custom-radio custom-control-inline mr-4">
                                            <input type="radio" id="labRelatedYes" value="1" class="custom-control-input" wire:model.live="is_lab_related">
                                            <label class="custom-control-label font-weight-bold" for="labRelatedYes">Yes</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="labRelatedNo" value="0" class="custom-control-input" wire:model.live="is_lab_related">
                                            <label class="custom-control-label font-weight-bold" for="labRelatedNo">No</label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 02: LOGGING DETAILS -->
                <div class="form-section shadow-sm" wire:key="section-02-logging">
                    <h6 class="form-section-title"><i class="mdi mdi-calendar-clock"></i>02. Logging Details</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group field-container" wire:key="field-date">
                                <label class="control-label font-weight-bold">Complaint Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" wire:model="date" required />
                                @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group field-container" wire:key="field-how-received">
                                <label class="control-label font-weight-bold">Mode of Delivery <span class="text-danger">*</span></label>
                                <div wire:ignore x-init="initSelect2($el.querySelector('select'), 'mode_of_delivery', 'Select Mode(s)')">
                                    <select class="form-control" multiple required>
                                        <option value="Phone">Phone</option>
                                        <option value="E-mail">E-mail</option>
                                        <option value="Fax">Fax</option>
                                        <option value="Verbal/Meeting">Verbal / Meeting</option>
                                        <option value="Customer Feedback Survey">Customer Feedback Survey</option>
                                        <option value="Other">Other</option>
                                    </select> 
                                </div>
                                @error('mode_of_delivery') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group field-container" wire:key="field-category">
                                <label class="control-label font-weight-bold">Complaint Type <span class="text-danger">*</span></label>
                                <div wire:ignore x-init="initSelect2($el.querySelector('select'), 'type', 'Select Type')">
                                    <select class="form-control" required>
                                        <option value="">Select Type</option>
                                        @foreach($complaint_types as $complaint_type)
                                            <option value="{{ $complaint_type->name }}" {{ $type == $complaint_type->name ? 'selected' : '' }}>{{ $complaint_type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group field-container" wire:key="field-severity">
                                <label class="control-label font-weight-bold">Severity <span class="text-danger">*</span></label>
                                <div wire:ignore x-init="initSelect2($el.querySelector('select'), 'priority', 'Select Priority')">
                                    <select class="form-control" required>
                                        <option value="">Select Priority</option>
                                        @foreach(['High', 'Medium', 'Low'] as $p)
                                            <option value="{{ $p }}" {{ $priority == $p ? 'selected' : '' }}>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('priority') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 03: NATURE OF COMPLAINT -->
                <div class="form-section shadow-sm mb-0" wire:key="section-03-nature">
                    <h6 class="form-section-title"><i class="mdi mdi-alert-circle-outline"></i>03. Nature of Complaint</h6>
                    
                    @if($received_from_type === 'Customer' && $is_lab_related)
                        <div class="row" wire:key="nature-lab-fields">
                            <div class="col-md-6">
                                <div class="form-group field-container">
                                    <label class="control-label font-weight-bold">Test Item <span class="text-danger">*</span></label>
                                    <div wire:ignore x-init="initSelect2($el.querySelector('select'), 'test_item', 'Select Test Item')">
                                        <select class="form-control" required>
                                            <option value="">Select Test Item</option>
                                            @foreach($samples as $sample)
                                                <option value="{{ $sample->id }}" {{ $test_item == $sample->id ? 'selected' : '' }}>{{ $sample->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('test_item') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group field-container" wire:key="serial-nos-wrapper-{{ count($serial_nos) }}">
                                    <label class="control-label font-weight-bold">Report Serial No (Batch No) <span class="text-danger">*</span></label>
                                    <div wire:ignore x-init="initSelect2($el.querySelector('select'), 'report_serial_no', 'Select Serial No')">
                                        <select class="form-control" required>
                                            <option value="">Select Serial No</option>
                                            @foreach($serial_nos as $batch_code)
                                                <option value="{{ $batch_code }}" {{ $report_serial_no == $batch_code ? 'selected' : '' }}>{{ $batch_code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('report_serial_no') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="form-group mb-0" wire:ignore wire:key="field-description">
                        <label class="control-label font-weight-bold">Complaint Details <span class="text-danger">*</span></label>
                        <textarea id="complaint-description" class="form-control @error('description') is-invalid @enderror" placeholder="Provide full details here..."></textarea>
                        @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-white border-top-0 pb-3 px-4">
                <button type="button" class="btn btn-light px-4 font-weight-bold" wire:click="close">Close</button>
                <div class="ml-auto">
                    <button type="submit" class="btn btn-primary px-5 shadow-sm font-weight-bold" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save"><i class="mdi mdi-content-save mr-1"></i> Save Complaint</span>
                        <span wire:loading wire:target="save"><i class="mdi mdi-loading mdi-spin mr-1"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
