<div x-data="{
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
}" x-init="initTinyMCE()">
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

                        <div class="form-group">
                            <label class="control-label">Organization Name <span class="text-danger">*</span></label>
                            @if($received_from_type === 'Customer')
                                <div class="tag-select-container" wire:click.outside="$set('showOrganizationDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedOrganization)
                                            <span class="selected-tag">
                                                {{ $this->selectedOrganization->name }}
                                                <i class="mdi mdi-close" wire:click.stop="clearOrganization"></i>
                                            </span>
                                        @endif
                                        <input type="text" class="tag-input" placeholder="Select Customer"
                                            wire:model.live.debounce.200ms="organizationSearch"
                                            wire:focus="$set('showOrganizationDropdown', true)" />
                                        @if($organization_name)
                                            <i class="mdi mdi-close-circle clear-icon" wire:click="clearOrganization"></i>
                                        @endif
                                    </div>
                                    @if($showOrganizationDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredOrganizationOptions as $customer)
                                                <div class="tag-dropdown-item" wire:click="selectOrganization('{{ $customer->name }}')">
                                                    {{ $customer->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-empty">No customer found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            @else
                                <input type="text" class="form-control @error('organization_name') is-invalid @enderror" wire:model="organization_name" placeholder="Enter Organization Name..." required />
                            @endif
                            @error('organization_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="control-label">Contact Name <span class="text-danger">*</span></label>
                            @if($received_from_type === 'Customer')
                                <div class="tag-select-container" wire:key="contact-select-wrapper-{{ $customerId }}-{{ count($contacts) }}" wire:click.outside="$set('showContactDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedContact)
                                            <span class="selected-tag">
                                                {{ trim(($this->selectedContact->first_name ?? '') . ' ' . ($this->selectedContact->middle_name ?? '') . ' ' . ($this->selectedContact->last_name ?? '')) }}
                                                <i class="mdi mdi-close" wire:click.stop="clearContact"></i>
                                            </span>
                                        @endif
                                        <input type="text" class="tag-input" placeholder="Select Contact"
                                            wire:model.live.debounce.200ms="contactSearch"
                                            wire:focus="$set('showContactDropdown', true)" />
                                        @if($contact_name)
                                            <i class="mdi mdi-close-circle clear-icon" wire:click="clearContact"></i>
                                        @endif
                                    </div>
                                    @if($showContactDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredContactOptions as $contact)
                                                <div class="tag-dropdown-item" wire:click="selectContact('{{ $contact->id }}')">
                                                    {{ trim(($contact->first_name ?? '') . ' ' . ($contact->middle_name ?? '') . ' ' . ($contact->last_name ?? '')) }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-empty">No contact found</div>
                                            @endforelse
                                        </div>
                                    @endif
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

                        <div class="form-group">
                            <label class="control-label">How Received <span class="text-danger">*</span></label>
                            <div class="tag-select-container" wire:click.outside="$set('showDeliveryModeDropdown', false)">
                                <div class="tag-select-input">
                                    @forelse($this->selectedDeliveryModes as $mode)
                                        <span class="selected-tag">
                                            {{ $mode }}
                                            <i class="mdi mdi-close" wire:click.stop="removeDeliveryMode('{{ $mode }}')"></i>
                                        </span>
                                    @empty
                                        <span class="text-muted small">No mode selected</span>
                                    @endforelse
                                    <input type="text" class="tag-input" placeholder="Select mode(s)"
                                        wire:model.live.debounce.200ms="deliveryModeSearch"
                                        wire:focus="$set('showDeliveryModeDropdown', true)" />
                                    @if(!empty($mode_of_delivery))
                                        <i class="mdi mdi-close-circle clear-icon" wire:click="clearDeliveryModes"></i>
                                    @endif
                                </div>
                                @if($showDeliveryModeDropdown)
                                    <div class="tag-dropdown">
                                        @forelse($this->filteredDeliveryModeOptions as $mode)
                                            <div class="tag-dropdown-item" wire:click="toggleDeliveryMode('{{ $mode }}')">
                                                {{ $mode }}
                                            </div>
                                        @empty
                                            <div class="tag-dropdown-empty">No delivery mode found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                            @error('mode_of_delivery') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group mt-4">
                            <label class="control-label">Complaint Category <span class="text-danger">*</span></label>
                            <div class="tag-select-container" wire:click.outside="$set('showTypeDropdown', false)">
                                <div class="tag-select-input">
                                    @if($this->selectedComplaintType)
                                        <span class="selected-tag">
                                            {{ $this->selectedComplaintType }}
                                            <i class="mdi mdi-close" wire:click.stop="clearComplaintType"></i>
                                        </span>
                                    @endif
                                    <input type="text" class="tag-input" placeholder="Select Type"
                                        wire:model.live.debounce.200ms="typeSearch"
                                        wire:focus="$set('showTypeDropdown', true)" />
                                    @if($type)
                                        <i class="mdi mdi-close-circle clear-icon" wire:click="clearComplaintType"></i>
                                    @endif
                                </div>
                                @if($showTypeDropdown)
                                    <div class="tag-dropdown">
                                        @forelse($this->filteredComplaintTypeOptions as $typeOption)
                                            <div class="tag-dropdown-item" wire:click="selectComplaintType('{{ $typeOption }}')">
                                                {{ $typeOption }}
                                            </div>
                                        @empty
                                            <div class="tag-dropdown-empty">No complaint type found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label">Severity <span class="text-danger">*</span></label>
                            <div class="tag-select-container" wire:click.outside="$set('showPriorityDropdown', false)">
                                <div class="tag-select-input">
                                    @if($this->selectedPriority)
                                        <span class="selected-tag">
                                            {{ $this->selectedPriority }}
                                            <i class="mdi mdi-close" wire:click.stop="clearPriority"></i>
                                        </span>
                                    @endif
                                    <input type="text" class="tag-input" placeholder="Select Priority"
                                        wire:model.live.debounce.200ms="prioritySearch"
                                        wire:focus="$set('showPriorityDropdown', true)" />
                                    @if($priority)
                                        <i class="mdi mdi-close-circle clear-icon" wire:click="clearPriority"></i>
                                    @endif
                                </div>
                                @if($showPriorityDropdown)
                                    <div class="tag-dropdown">
                                        @forelse($this->filteredPriorityOptions as $priorityOption)
                                            <div class="tag-dropdown-item" wire:click="selectPriority('{{ $priorityOption }}')">
                                                {{ $priorityOption }}
                                            </div>
                                        @empty
                                            <div class="tag-dropdown-empty">No priority found</div>
                                        @endforelse
                                    </div>
                                @endif
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
                                <div class="tag-select-container" wire:key="test-item-select-wrapper-{{ $customerId }}-{{ count($samples) }}" wire:click.outside="$set('showTestItemDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedTestItem)
                                            <span class="selected-tag">
                                                {{ $this->selectedTestItem->name }}
                                                <i class="mdi mdi-close" wire:click.stop="clearTestItem"></i>
                                            </span>
                                        @endif
                                        <input type="text" class="tag-input" placeholder="Select Test Item"
                                            wire:model.live.debounce.200ms="testItemSearch"
                                            wire:focus="$set('showTestItemDropdown', true)" />
                                        @if($test_item)
                                            <i class="mdi mdi-close-circle clear-icon" wire:click="clearTestItem"></i>
                                        @endif
                                    </div>
                                    @if($showTestItemDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredTestItemOptions as $sample)
                                                <div class="tag-dropdown-item" wire:click="selectTestItem('{{ $sample->id }}')">
                                                    {{ $sample->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-empty">No test item found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                @error('test_item') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label class="control-label">Report Serial No (Batch No)</label>
                                <div class="tag-select-container" wire:key="serial-no-select-wrapper-{{ $test_item }}-{{ count($serial_nos) }}" wire:click.outside="$set('showSerialDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedSerial)
                                            <span class="selected-tag">
                                                {{ $this->selectedSerial }}
                                                <i class="mdi mdi-close" wire:click.stop="clearSerial"></i>
                                            </span>
                                        @endif
                                        <input type="text" class="tag-input" placeholder="Select Serial No"
                                            wire:model.live.debounce.200ms="serialSearch"
                                            wire:focus="$set('showSerialDropdown', true)" />
                                        @if($report_serial_no)
                                            <i class="mdi mdi-close-circle clear-icon" wire:click="clearSerial"></i>
                                        @endif
                                    </div>
                                    @if($showSerialDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredSerialOptions as $serialOption)
                                                <div class="tag-dropdown-item" wire:click="selectSerial('{{ $serialOption }}')">
                                                    {{ $serialOption }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-empty">No serial number found</div>
                                            @endforelse
                                        </div>
                                    @endif
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