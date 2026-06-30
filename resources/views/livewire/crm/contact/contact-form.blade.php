
<div>
    <style>
        .form-section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }

        .tag-select-container {
            position: relative;
            width: 100%;
        }

        .tag-select-input {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            background-color: #fff;
        }

        .tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 120px;
            font-size: 0.9rem;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f2f5;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.85rem;
        }

        .tag-badge i {
            cursor: pointer;
        }

        .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-height: 220px;
            overflow-y: auto;
            z-index: 1100;
        }

        .tag-dropdown-item {
            padding: 8px 10px;
            cursor: pointer;
        }

        .tag-dropdown-item:hover {
            background: #f8f9fa;
        }

        .tag-select-container.is-invalid .tag-select-input {
            border-color: #dc3545;
        }

        .contact-modal-overlay {
            z-index: 1060;
            padding: 1rem 0;
        }

        .contact-modal-dialog {
            margin: 1rem auto;
        }

        .contact-modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .contact-modal-header {
            flex-shrink: 0;
        }

        .contact-modal-form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .contact-modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
            min-height: 0;
        }

        .contact-modal-footer {
            flex-shrink: 0;
            position: sticky;
            bottom: 0;
            z-index: 2;
        }
    </style>
    <template x-teleport="body">
        <div class="modal fade show contact-modal-overlay" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" wire:click.self="close"
            tabindex="-1" role="dialog" wire:ignore.self>
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable contact-modal-dialog" role="document">
                <div class="modal-content contact-modal-content">
                    <div class="modal-header contact-modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $contactId ? 'pencil' : 'plus' }}"></i>
                            {{ $contactId ? __('crm.edit') : __('crm.add') }} {{ __('crm.company_contact') }}
                        </h4>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="save" class="contact-modal-form">
                        <div class="modal-body contact-modal-body">
                            <!-- Personal Information -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.first_name') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('first_name') is-invalid @enderror"
                                            wire:model="first_name" placeholder="{{ __('crm.first_name_placeholder') }}" required />
                                        @error('first_name') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.middle_name') }}</label>
                                        <input type="text" class="form-control" wire:model="second_name"
                                            placeholder="{{ __('crm.middle_name_placeholder') }}" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.last_name') }}</label>
                                        <input type="text" class="form-control" wire:model="third_name"
                                            placeholder="{{ __('crm.last_name_placeholder') }}" />
                                    </div>
                                </div>
                            </div>

                            <!-- Job & Department -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.occupation') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model="job_occupation"
                                            placeholder="{{ __('crm.occupation_placeholder') }}" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.department') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="tag-select-container @error('unit_name') is-invalid @enderror"
                                            wire:click="$set('showUnitDropdown', true)"
                                            wire:click.outside="$set('showUnitDropdown', false)">
                                            <div class="tag-select-input">
                                                @foreach($this->selectedUnits as $unit)
                                                    <span class="tag-badge">
                                                        {{ $unit->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="removeUnitSelection('{{ $unit->id }}')"></i>
                                                    </span>
                                                @endforeach

                                                <input type="text"
                                                    wire:model.live="unitSearch"
                                                    class="tag-input"
                                                    placeholder="{{ __('crm.select_department') }}"
                                                    autocomplete="off">
                                            </div>

                                            @if($showUnitDropdown)
                                                <div class="tag-dropdown">
                                                    @if(count($this->filteredUnits) > 0)
                                                        @foreach($this->filteredUnits as $unit)
                                                            <div class="tag-dropdown-item d-flex justify-content-between align-items-center"
                                                                wire:click.stop="toggleUnitSelection('{{ $unit->id }}')">
                                                                <span>{{ $unit->name }}</span>
                                                                @if($this->isUnitSelected($unit->id))
                                                                    <i class="mdi mdi-check text-success"></i>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="tag-dropdown-item text-muted">No departments found</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        @error('unit_name') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.email') }} <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            wire:model="email" placeholder="{{ __('crm.email_address_placeholder') }}" required />
                                        @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Details -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.telephone') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                            wire:model.blur="telephone" placeholder="{{ __('crm.telephone_placeholder') }}" />
                                        @error('telephone') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.mobile') }}</label>
                                        <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                            wire:model="mobile" placeholder="{{ __('crm.mobile_placeholder') }}" />
                                        @error('mobile') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('crm.other_customers_assigned') }}</label>
                                        <div class="tag-select-container"
                                            wire:click="$set('showOtherCustomersDropdown', true)"
                                            wire:click.outside="$set('showOtherCustomersDropdown', false)">
                                            <div class="tag-select-input">
                                                @foreach($this->selectedOtherCustomers as $cust)
                                                    <span class="tag-badge">
                                                        {{ $cust->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="removeOtherCustomerSelection('{{ $cust->id }}')"></i>
                                                    </span>
                                                @endforeach

                                                <input type="text"
                                                    wire:model.live="otherCustomerSearch"
                                                    class="tag-input"
                                                    placeholder="{{ __('crm.select_customers') }}"
                                                    autocomplete="off">
                                            </div>

                                            @if($showOtherCustomersDropdown)
                                                <div class="tag-dropdown">
                                                    @if(count($this->filteredCustomers) > 0)
                                                        @foreach($this->filteredCustomers as $cust)
                                                            <div class="tag-dropdown-item d-flex justify-content-between align-items-center"
                                                                wire:click.stop="toggleOtherCustomerSelection('{{ $cust->id }}')">
                                                                <span>{{ $cust->name }}</span>
                                                                @if($this->isOtherCustomerSelected($cust->id))
                                                                    <i class="mdi mdi-check text-success"></i>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="tag-dropdown-item text-muted">No customers found</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
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
                                        <label class="custom-control-label" for="receiveReport">{{ __('crm.receives_report') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="receivePriceList"
                                            wire:model="receive_price_list">
                                        <label class="custom-control-label" for="receivePriceList">{{ __('crm.receives_price_list') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="receiveInvoice"
                                            wire:model="receive_invoice">
                                        <label class="custom-control-label" for="receiveInvoice">{{ __('crm.receives_invoice') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="receiveFeedback"
                                            wire:model="receive_feedback">
                                        <label class="custom-control-label" for="receiveFeedback">{{ __('crm.opt_in_feedback_emails') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="isActive"
                                            wire:model="active">
                                        <label class="custom-control-label" for="isActive">{{ __('crm.is_active') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="createPassword"
                                            wire:model.live="can_login">
                                        <label class="custom-control-label" for="createPassword">{{ __('crm.portal_access') }}</label>
                                    </div>
                                </div>
                            </div>

                            @if($can_login)
                                <div class="row bg-light p-3 rounded mx-1">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('crm.password') }}</label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                wire:model="password" placeholder="{{ __('crm.password_placeholder') }}" />
                                            @error('password') <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('crm.confirm_password') }}</label>
                                            <input type="password"
                                                class="form-control @error('confirm_password') is-invalid @enderror"
                                                wire:model="confirm_password" placeholder="{{ __('crm.confirm_password_placeholder') }}" />
                                            @error('confirm_password') <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="modal-footer bg-light contact-modal-footer">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save"><i class="mdi mdi-content-save"></i> {{ __('crm.save') }}</span>
                                <span wire:loading wire:target="save"><i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.saving') }}...</span>
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="close" wire:loading.attr="disabled">{{ __('crm.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>