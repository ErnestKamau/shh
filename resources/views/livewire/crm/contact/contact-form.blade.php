
<div x-data="{
    initSelect2() {
        setTimeout(() => {
            // Initialize Department/Unit Select2
            let unitSelect = $('#unit_selector');
            if (unitSelect.hasClass('select2-hidden-accessible')) {
                unitSelect.select2('destroy');
            }
            unitSelect.select2({
                placeholder: 'Select Department',
                allowClear: true,
                width: '100%',
                multiple: true,
                dropdownParent: unitSelect.closest('.modal'),
                closeOnSelect: true
            }).on('change', function (e) {
                var data = $(this).val();
                $wire.set('unit_name', data);
            });

            // Initialize Other Customers Select2
            let customerSelect = $('#other_customers_selector');
            if (customerSelect.hasClass('select2-hidden-accessible')) {
                customerSelect.select2('destroy');
            }
            customerSelect.select2({
                placeholder: 'Select Customers',
                allowClear: true,
                width: '100%',
                multiple: true,
                dropdownParent: customerSelect.closest('.modal'),
                closeOnSelect: true
            }).on('change', function (e) {
                var data = $(this).val();
                $wire.set('other_customers', data);
            });

            // Initial load for Units
            let initialUnits = $wire.get('unit_name');
            if (initialUnits) {
                unitSelect.val(initialUnits).trigger('change');
            }

            // Initial load for Other Customers
            let initialCustomers = $wire.get('other_customers');
            if (initialCustomers) {
                customerSelect.val(initialCustomers).trigger('change');
            }
        }, 100);
    }
}" x-init="initSelect2()">
    <style>
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
        }

        .form-section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
    </style>
    <template x-teleport="body">
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" wire:click.self="close"
            tabindex="-1" role="dialog" wire:ignore.self>
            <div class="modal-dialog modal-xl" role="document"> <!-- Changed to modal-xl for better layout -->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $contactId ? 'pencil' : 'plus' }}"></i>
                            {{ $contactId ? 'Edit' : 'Add' }} Company Contact
                        </h4>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <!-- Personal Information -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">First Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('first_name') is-invalid @enderror"
                                            wire:model="first_name" placeholder="First Name..." required />
                                        @error('first_name') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Middle Name</label>
                                        <input type="text" class="form-control" wire:model="second_name"
                                            placeholder="Middle Name..." />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Last Name</label>
                                        <input type="text" class="form-control" wire:model="third_name"
                                            placeholder="Last Name..." />
                                    </div>
                                </div>
                            </div>

                            <!-- Job & Department -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Occupation <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model="job_occupation"
                                            placeholder="Occupation..." />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" wire:ignore>
                                        <label class="control-label">Department <span
                                                class="text-danger">*</span></label>
                                        <select id="unit_selector" class="form-control select2" multiple required>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('unit_name') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            wire:model="email" placeholder="Email Address..." required />
                                        @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Details -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Telephone <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                            wire:model.blur="telephone" placeholder="Telephone..." />
                                        @error('telephone') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Mobile</label>
                                        <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                            wire:model="mobile" placeholder="Mobile..." />
                                        @error('mobile') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" wire:ignore>
                                        <label class="control-label">Other Customers Assigned</label>
                                        <select id="other_customers_selector" class="form-control select2" multiple>
                                            @foreach($customers as $cust)
                                                <option value="{{ $cust->id }}">{{ $cust->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Permissions & Settings -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="receiveReport"
                                            wire:model="receive_report">
                                        <label class="custom-control-label" for="receiveReport">Receives Report?</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="receivePriceList"
                                            wire:model="receive_price_list">
                                        <label class="custom-control-label" for="receivePriceList">Receives
                                            Pricelist?</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="receiveInvoice"
                                            wire:model="receive_invoice">
                                        <label class="custom-control-label" for="receiveInvoice">Receives
                                            Invoice?</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="receiveFeedback"
                                            wire:model="receive_feedback">
                                        <label class="custom-control-label" for="receiveFeedback">Opt-in for Feedback
                                            Emails</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="isActive"
                                            wire:model="active">
                                        <label class="custom-control-label" for="isActive">Is Active?</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="createPassword"
                                            wire:model.live="can_login">
                                        <label class="custom-control-label" for="createPassword">Create/Update User
                                            Passwords</label>
                                    </div>
                                </div>
                            </div>

                            @if($can_login)
                                <div class="row bg-light p-3 rounded mx-1">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Password</label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                wire:model="password" placeholder="Password..." />
                                            @error('password') <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Confirm Password</label>
                                            <input type="password"
                                                class="form-control @error('confirm_password') is-invalid @enderror"
                                                wire:model="confirm_password" placeholder="Confirm Password..." />
                                            @error('confirm_password') <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="modal-footer bg-light">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save"><i class="mdi mdi-content-save"></i> Save</span>
                                <span wire:loading wire:target="save"><i class="mdi mdi-loading mdi-spin"></i> Saving...</span>
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="close" wire:loading.attr="disabled">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>