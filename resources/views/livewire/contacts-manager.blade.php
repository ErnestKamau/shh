<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="mdi mdi-account-box-outline"></i> Contacts
            </h6>
            <button wire:click="showCreateContactModal" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus"></i> Add Contact
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Message Alert -->
        @if($message)
            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show m-3" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" wire:click="dismissMessage"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Units</th>
                        <th>Can Login</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
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
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if($contact->active == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="showEditContactModal({{ $contact->id }})" 
                                            class="btn btn-outline-warning btn-sm" 
                                            title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button wire:click="deleteContact({{ $contact->id }})" 
                                            class="btn btn-outline-danger btn-sm" 
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this contact?')">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="mdi mdi-information-outline fs-1"></i>
                                    <p class="mt-2">No contacts found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Contact Modal -->
@if($showContactModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-{{ $editingContact ? 'pencil' : 'plus' }}"></i>
                        {{ $editingContact ? 'Edit' : 'Create' }} Contact
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeContactModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveContact">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                    <select wire:model="contactForm.title_id" class="form-select">
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
                                    <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="contactForm.first_name" class="form-control" placeholder="First name...">
                                    @error('contactForm.first_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Middle Name</label>
                                    <input type="text" wire:model="contactForm.middle_name" class="form-control" placeholder="Middle name...">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Last Name</label>
                                    <input type="text" wire:model="contactForm.last_name" class="form-control" placeholder="Last name...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Job Occupation</label>
                                    <input type="text" wire:model="contactForm.job_occupation" class="form-control" placeholder="Job title...">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" wire:model="contactForm.email" class="form-control" placeholder="Email address...">
                                    @error('contactForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Telephone <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="contactForm.telephone" class="form-control" placeholder="Telephone...">
                                    @error('contactForm.telephone') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Mobile</label>
                                    <input type="text" wire:model="contactForm.mobile" class="form-control" placeholder="Mobile number...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Company Units <span class="text-danger">*</span></label>
                                    <select wire:model="contactForm.unit_name" class="form-select" multiple>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('contactForm.unit_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="contactForm.receive_price_list" class="form-check-input" id="priceList">
                                    <label class="form-check-label" for="priceList">
                                        Receives Price List?
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="contactForm.receive_invoice" class="form-check-input" id="invoice">
                                    <label class="form-check-label" for="invoice">
                                        Receives Invoice?
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="contactForm.receive_report" class="form-check-input" id="report">
                                    <label class="form-check-label" for="report">
                                        Receives Report?
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="contactForm.can_login" class="form-check-input" id="canLogin">
                                    <label class="form-check-label" for="canLogin">
                                        Create/Update User Passwords
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="contactForm.active" class="form-check-input" id="contactActive">
                                    <label class="form-check-label" for="contactActive">
                                        Is Active?
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if($contactForm['can_login'])
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                        <input type="password" wire:model="contactForm.main_password" class="form-control" placeholder="Password...">
                                        @error('contactForm.main_password') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" wire:model="contactForm.confirm_password" class="form-control" placeholder="Confirm password...">
                                        @error('contactForm.confirm_password') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeContactModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveContact">
                        <i class="mdi mdi-content-save"></i> {{ $editingContact ? 'Update' : 'Create' }} Contact
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

