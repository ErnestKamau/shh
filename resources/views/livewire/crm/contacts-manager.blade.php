<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-account-box-outline text-primary"></i>
                                Contacts Management
                            </h2>
                            <p class="text-muted mb-0">Manage contacts for: <strong>{{ $customer->name }}</strong></p>
                        </div>
                        <button wire:click="showCreateContactModal" class="btn btn-primary" wire:loading.attr="disabled" wire:target="showCreateContactModal">
                            <span wire:loading.remove wire:target="showCreateContactModal">
                                <i class="mdi mdi-plus"></i> Add Contact
                            </span>
                            <span wire:loading wire:target="showCreateContactModal">
                                <i class="mdi mdi-loading mdi-spin"></i> Opening form...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Contacts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->contacts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                        <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Units</th>
                                        <th>Can Login</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->contacts as $contact)
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $contact->first_name }} {{ $contact->middle_name }} {{ $contact->last_name }}</div>
                                                    <small class="text-muted">{{ $contact->job_occupation ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $contact->email }}</td>
                                            <td>{{ $contact->telephone }}</td>
                                            <td>
                                                <small class="text-muted">{{ $contact->unit_name ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @if($contact->can_login == 1)
                                                            <span class="badge bg-success p-2" style="color: white;">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary p-2" style="color: white;">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($contact->active == 1)
                                                    <span class="badge bg-success p-2" style="color: white;">Active</span>
                                                @else
                                                    <span class="badge bg-danger p-2" style="color: white;">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditContactModal({{ $contact->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-1" 
                                                            title="Edit"
                                                            wire:loading.attr="disabled"
                                                            wire:target="showEditContactModal({{ $contact->id }})">
                                                        <span wire:loading.remove wire:target="showEditContactModal({{ $contact->id }})">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </span>
                                                        <span wire:loading wire:target="showEditContactModal({{ $contact->id }})">
                                                            <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                                        </span>
                                                    </button>
                                                    <button wire:click="deleteContact({{ $contact->id }})" 
                                                            class="btn btn-sm btn-outline-danger mr-1" 
                                                            title="Delete"
                                                            wire:loading.attr="disabled"
                                                            wire:target="deleteContact({{ $contact->id }})"
                                                            onclick="return confirm('Are you sure you want to delete this contact?')">
                                                        <span wire:loading.remove wire:target="deleteContact({{ $contact->id }})">
                                                            <i class="mdi mdi-delete"></i>
                                                        </span>
                                                        <span wire:loading wire:target="deleteContact({{ $contact->id }})">
                                                            <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                                        </span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-account-box-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No contacts found</h5>
                            <p class="text-muted">Start by adding your first contact.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    @if($showContactModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header" style="border-bottom: 2px solid #e9ecef;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingContact ? 'pencil' : 'plus' }} text-primary"></i>
                        {{ $editingContact ? 'Edit' : 'Create' }} Contact
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeContactModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveContact">
                        <!-- Personal Information Section -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-account-circle text-primary"></i> Personal Information
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-account-star text-info"></i> Title <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model="contactForm.title_id" class="form-select modern-select">
                                                <option value="">Select Title</option>
                                                @foreach($titles as $title)
                                                    <option value="{{ $title->id }}">{{ $title->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('contactForm.title_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-account text-primary"></i> First Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" wire:model="contactForm.first_name" class="form-control" placeholder="First name...">
                                            @error('contactForm.first_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-account-outline text-primary"></i> Middle Name
                                            </label>
                                            <input type="text" wire:model="contactForm.middle_name" class="form-control" placeholder="Middle name...">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-account text-primary"></i> Last Name
                                            </label>
                                            <input type="text" wire:model="contactForm.last_name" class="form-control" placeholder="Last name...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-briefcase text-warning"></i> Job Occupation
                                            </label>
                                            <input type="text" wire:model="contactForm.job_occupation" class="form-control" placeholder="Job title...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-card-account-phone text-success"></i> Contact Information
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-email text-info"></i> Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" wire:model="contactForm.email" class="form-control" placeholder="Email address...">
                                            @error('contactForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-phone text-success"></i> Telephone <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" wire:model="contactForm.telephone" class="form-control" placeholder="Telephone...">
                                            @error('contactForm.telephone') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-cellphone text-success"></i> Mobile
                                            </label>
                                            <input type="text" wire:model="contactForm.mobile" class="form-control" placeholder="Mobile number...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-office-building text-primary"></i> Company Units <span class="text-danger">*</span>
                                            </label>
                                            <div x-data="{
                                                open: false,
                                                search: '',
                                                selectedUnits: @entangle('contactForm.unit_name').live,
                                                allUnits: {{ json_encode($units->pluck('name')->toArray()) }},
                                                get filteredUnits() {
                                                    if (!this.search) return this.allUnits;
                                                    return this.allUnits.filter(unit => 
                                                        unit.toLowerCase().includes(this.search.toLowerCase())
                                                    );
                                                },
                                                toggleUnit(unit) {
                                                    if (!Array.isArray(this.selectedUnits)) {
                                                        this.selectedUnits = [];
                                                    }
                                                    const index = this.selectedUnits.indexOf(unit);
                                                    if (index === -1) {
                                                        this.selectedUnits.push(unit);
                                                    } else {
                                                        this.selectedUnits.splice(index, 1);
                                                    }
                                                },
                                                removeUnit(unit) {
                                                    if (!Array.isArray(this.selectedUnits)) return;
                                                    const index = this.selectedUnits.indexOf(unit);
                                                    if (index !== -1) {
                                                        this.selectedUnits.splice(index, 1);
                                                    }
                                                },
                                                isSelected(unit) {
                                                    return Array.isArray(this.selectedUnits) && this.selectedUnits.includes(unit);
                                                }
                                            }" class="searchable-dropdown-wrapper">
                                                <!-- Selected Units Tags -->
                                                <div class="selected-tags-container" @click="open = true">
                                                    <template x-if="Array.isArray(selectedUnits) && selectedUnits.length > 0">
                                                        <div class="tags-wrapper">
                                                            <template x-for="unit in selectedUnits" :key="unit">
                                                                <span class="unit-tag">
                                                                    <span x-text="unit"></span>
                                                                    <button type="button" @click.stop="removeUnit(unit)" class="remove-tag-btn">
                                                                        <i class="mdi mdi-close"></i>
                                                                    </button>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <input 
                                                        type="text" 
                                                        x-model="search"
                                                        @focus="open = true"
                                                        @click="open = true"
                                                        placeholder="Search or select units..."
                                                        class="form-control searchable-input"
                                                    >
                                                    <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                                </div>

                                                <!-- Dropdown List -->
                                                <div x-show="open" 
                                                     @click.away="open = false"
                                                     x-transition
                                                     class="dropdown-list">
                                                    <template x-if="filteredUnits.length > 0">
                                                        <div class="units-list">
                                                            <template x-for="unit in filteredUnits" :key="unit">
                                                                <div @click="toggleUnit(unit)" 
                                                                     class="unit-item"
                                                                     :class="{ 'selected': isSelected(unit) }">
                                                                    <i class="mdi" 
                                                                       :class="isSelected(unit) ? 'mdi-checkbox-marked text-primary' : 'mdi-checkbox-blank-outline'"></i>
                                                                    <span x-text="unit"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <template x-if="filteredUnits.length === 0">
                                                        <div class="no-results">
                                                            <i class="mdi mdi-alert-circle-outline"></i>
                                                            <span>No units found</span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            @error('contactForm.unit_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preferences & Settings Section -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-cog text-info"></i> Preferences & Settings
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input type="checkbox" wire:model="contactForm.receive_price_list" class="form-check-input" id="priceList" role="switch">
                                            <label class="form-check-label" for="priceList">
                                                <i class="mdi mdi-currency-usd text-success"></i> Receives Price List
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input type="checkbox" wire:model="contactForm.receive_invoice" class="form-check-input" id="invoice" role="switch">
                                            <label class="form-check-label" for="invoice">
                                                <i class="mdi mdi-receipt text-warning"></i> Receives Invoice
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input type="checkbox" wire:model="contactForm.receive_report" class="form-check-input" id="report" role="switch">
                                            <label class="form-check-label" for="report">
                                                <i class="mdi mdi-file-document text-primary"></i> Receives Report
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" wire:model="contactForm.can_login" class="form-check-input" id="canLogin" role="switch">
                                            <label class="form-check-label" for="canLogin">
                                                <i class="mdi mdi-account-key text-danger"></i> Can Login (Create/Update Passwords)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" wire:model="contactForm.active" class="form-check-input" id="contactActive" role="switch">
                                            <label class="form-check-label" for="contactActive">
                                                <i class="mdi mdi-check-circle text-success"></i> Is Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Section -->
                        @if($contactForm['can_login'])
                            <div class="form-section mb-4">
                                <div class="section-header mb-3">
                                    <h6 class="mb-0 text-muted">
                                        <i class="mdi mdi-lock text-danger"></i> Password Configuration
                                    </h6>
                                </div>
                                <div class="section-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="mdi mdi-lock text-danger"></i> Password <span class="text-danger">*</span>
                                                </label>
                                                <input type="password" wire:model="contactForm.main_password" class="form-control" placeholder="Password...">
                                                @error('contactForm.main_password') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="mdi mdi-lock-check text-danger"></i> Confirm Password <span class="text-danger">*</span>
                                                </label>
                                                <input type="password" wire:model="contactForm.confirm_password" class="form-control" placeholder="Confirm password...">
                                                @error('contactForm.confirm_password') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 2px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" wire:click="closeContactModal" wire:loading.attr="disabled" wire:target="saveContact">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="saveContact" wire:loading.attr="disabled" wire:target="saveContact">
                        <span wire:loading.remove wire:target="saveContact">
                            <i class="mdi mdi-content-save"></i> {{ $editingContact ? 'Update' : 'Create' }} Contact
                        </span>
                        <span wire:loading wire:target="saveContact">
                            <span class="spinner-border spinner-border-sm" role="status"></span> Saving data...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modern Styling -->
    <style>
    /* Modern Select Field Styling */
    .modern-select {
        border: 1px solid #ced4da;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background: #ffffff;
        outline: none;
    }

    .modern-select:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
    }

    .modern-select option {
        padding: 10px 16px;
        font-weight: 500;
        color: #495057;
    }

    .modern-select option:hover {
        background-color: #f8f9fa;
    }

    /* Custom dropdown arrow */
    .modern-select:not([multiple]) {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 40px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Invalid state styling */
    .modern-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .modern-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    /* Make modal body scrollable */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Custom scrollbar for better UX */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .modal.show {
        display: block !important;
    }
    
    /* Form section styling with border bottoms */
    .form-section {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 20px;
    }
    
    .form-section:last-of-type {
        border-bottom: none;
    }
    
    .section-header h6 {
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
        display: inline-block;
        min-width: 100%;
    }
    
    .section-body {
        padding-top: 10px;
    }
    
    /* Form switch styling */
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .form-check-input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Modal animations */
    .modal-content {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    /* Input field styling improvements */
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Label icon spacing */
    .form-label i {
        margin-right: 4px;
    }
    
    /* Button styling improvements */
    .modal-footer .btn {
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .modal-footer .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* Searchable Dropdown Styling */
    .searchable-dropdown-wrapper {
        position: relative;
    }
    
    .selected-tags-container {
        position: relative;
        min-height: 45px;
        border: 1px solid #ced4da;
        border-radius: 12px;
        padding: 8px 40px 8px 12px;
        background: white;
        cursor: text;
        transition: all 0.3s ease;
    }
    
    .selected-tags-container:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
    }
    
    .selected-tags-container:has(.searchable-input:focus) {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .tags-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 6px;
    }
    
    .unit-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
    }
    
    .remove-tag-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
        color: white;
    }
    
    .remove-tag-btn:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: scale(1.1);
    }
    
    .remove-tag-btn i {
        font-size: 12px;
    }
    
    .searchable-input {
        border: none;
        outline: none;
        box-shadow: none !important;
        padding: 4px 0;
        min-width: 200px;
        flex: 1;
    }
    
    .searchable-input:focus {
        border: none !important;
        box-shadow: none !important;
    }
    
    .dropdown-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: #6c757d;
        transition: transform 0.3s ease;
        pointer-events: none;
    }
    
    .dropdown-arrow.rotated {
        transform: translateY(-50%) rotate(180deg);
    }
    
    .dropdown-list {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
    }
    
    .units-list {
        padding: 8px;
    }
    
    .unit-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    
    .unit-item:hover {
        background: #f8f9fa;
    }
    
    .unit-item.selected {
        background: rgba(0, 123, 255, 0.08);
    }
    
    .unit-item i {
        font-size: 20px;
    }
    
    .no-results {
        padding: 20px;
        text-align: center;
        color: #6c757d;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .no-results i {
        font-size: 32px;
        opacity: 0.5;
    }
    
    /* Custom scrollbar for dropdown */
    .dropdown-list::-webkit-scrollbar {
        width: 8px;
    }
    
    .dropdown-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .dropdown-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .dropdown-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    </style>
</div>

