<div class="container-fluid contacts-manager-page">
    <!-- Header -->
    <div class="row mb-4 customer-tab-filters">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-account-box-outline text-primary"></i>
                                {{ __('crm.contacts') }} {{ __('crm.management') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('crm.manage_contacts_for', ['customer' => $customer->name]) }}</p>
                        </div>
                        <button wire:click="showCreateContactModal" class="btn btn-sm btn-outline-primary pricelist-action-btn" wire:loading.attr="disabled" wire:target="showCreateContactModal">
                            <span wire:loading.remove wire:target="showCreateContactModal">
                                <i class="mdi mdi-plus"></i> {{ __('crm.add_contact') }}
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

    <!-- Filters -->
    <div class="row mb-4 align-items-end contacts-manager-filters">
        <div class="col-md-6 mb-2 mb-md-0">
            <input type="text" wire:model.live="search" class="form-control"
                placeholder="{{ __('crm.search_contacts') }}">
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <div class="tag-select-container contacts-manager-filter-select">
                <div class="tag-select-input contacts-manager-filter-select-input">
                    <select wire:model.live="statusFilter" class="tag-select-native no-select2" wire:key="contacts-status-filter">
                        <option value="">{{ __('crm.all_status') }}</option>
                        <option value="1">{{ __('crm.active') }}</option>
                        <option value="0">{{ __('crm.inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <div class="tag-select-container contacts-manager-filter-select">
                <div class="tag-select-input contacts-manager-filter-select-input">
                    <select wire:model.live="perPage" class="tag-select-native no-select2" wire:key="contacts-per-page">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <button type="button" wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                {{ __('crm.clear') }}
            </button>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->contacts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>{{ __('crm.name') }}</th>
                                        <th>{{ __('crm.email') }}</th>
                                        <th>{{ __('crm.phone_1') }}</th>
                                        <th>{{ __('crm.units') }}</th>
                                        <th>{{ __('crm.can_login') }}</th>
                                        <th>{{ __('crm.status') }}</th>
                                        <th>{{ __('crm.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->contacts as $contact)
                                        <tr class="{{ (int) $contact->active === 1 ? '' : 'table-secondary' }}">
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $contact->first_name }} {{ $contact->middle_name }} {{ $contact->last_name }}</div>
                                                    <small class="text-muted">{{ $contact->job_occupation ?? __('crm.not_available') }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $contact->email }}</td>
                                            <td>{{ $contact->telephone }}</td>
                                            <td>
                                                <small class="text-muted">{{ $contact->unit_name ?? __('crm.not_available') }}</small>
                                            </td>
                                            <td>
                                                @if($contact->can_login == 1)
                                                            <span class="badge bg-success p-2" style="color: white;">{{ __('crm.yes') }}</span>
                                                @else
                                                    <span class="badge bg-secondary p-2" style="color: white;">{{ __('crm.no') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($contact->active == 1)
                                                    <span class="badge bg-success p-2" style="color: white;">{{ __('crm.active') }}</span>
                                                @else
                                                    <span class="badge bg-danger p-2" style="color: white;">{{ __('crm.deactivated') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button wire:click="showEditContactModal('{{ $contact->id }}')" 
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit" 
                                                            title="{{ __('crm.edit') }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="showEditContactModal('{{ $contact->id }}')">
                                                        <span wire:loading.remove wire:target="showEditContactModal('{{ $contact->id }}')">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </span>
                                                        <span wire:loading wire:target="showEditContactModal('{{ $contact->id }}')">
                                                            <span class="spinner-border spinner-border-sm" role="status"></span> Opening form...
                                                        </span>
                                                    </button>
                                                    <button wire:click="deleteContact('{{ $contact->id }}')" 
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete" 
                                                            title="{{ __('crm.delete') }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="deleteContact({{ $contact->id }})"
                                                            onclick="return confirm(@js(__('crm.delete_contact_confirm')))">
                                                        <span wire:loading.remove wire:target="deleteContact({{ $contact->id }})">
                                                            <i class="mdi mdi-delete"></i>
                                                        </span>
                                                        <span wire:loading wire:target="deleteContact('{{ $contact->id }}')">
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
                            <h5 class="text-muted mt-3">{{ __('crm.no_contacts_found') }}</h5>
                            <p class="text-muted">{{ __('crm.add_first_contact_hint') }}</p>
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
                        {{ $editingContact ? __('crm.edit') : __('crm.create') }} {{ __('crm.contact') }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeContactModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveContact">
                        <!-- Personal Information Section -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-account-circle text-primary"></i> {{ __('crm.personal_information') }}
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-account-star text-info"></i> {{ __('crm.title') }} <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model="contactForm.title_id" class="form-select modern-select">
                                                <option value="">{{ __('crm.select_title') }}</option>
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
                                                <i class="mdi mdi-account text-primary"></i> {{ __('crm.first_name') }} <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" wire:model="contactForm.first_name" class="form-control" placeholder="{{ __('crm.first_name_placeholder') }}">
                                            @error('contactForm.first_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-account-outline text-primary"></i> {{ __('crm.middle_name') }}
                                            </label>
                                            <input type="text" wire:model="contactForm.middle_name" class="form-control" placeholder="{{ __('crm.middle_name_placeholder') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-account text-primary"></i> {{ __('crm.last_name') }}
                                            </label>
                                            <input type="text" wire:model="contactForm.last_name" class="form-control" placeholder="{{ __('crm.last_name_placeholder') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-briefcase text-warning"></i> {{ __('crm.job_occupation') }}
                                            </label>
                                            <input type="text" wire:model="contactForm.job_occupation" class="form-control" placeholder="{{ __('crm.job_title_placeholder') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-draw-pen text-primary"></i> Signature
                                            </label>
                                            <input type="file" wire:model="signatureFile" accept="image/*" class="form-control">
                                            @if($contactForm['signature'])
                                                <div class="mt-2">
                                                    <small class="text-muted">{{ __('crm.current_signature') }}:</small>
                                                    <div class="mt-1">
                                                        <img src="{{ asset('storage/' . $contactForm['signature']) }}" alt="{{ __('crm.signature') }}" style="max-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 4px;">
                                                    </div>
                                                </div>
                                            @endif
                                            @error('signatureFile') <span class="text-danger">{{ $message }}</span> @enderror
                                            <small class="form-text text-muted">{{ __('crm.signature_upload_hint') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-card-account-phone text-success"></i> {{ __('crm.contact_information') }}
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-email text-info"></i> {{ __('crm.email') }} <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" wire:model="contactForm.email" class="form-control" placeholder="{{ __('crm.email_address_placeholder') }}">
                                            @error('contactForm.email') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-phone text-success"></i> {{ __('crm.telephone') }} <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" wire:model="contactForm.telephone" class="form-control" placeholder="{{ __('crm.telephone_placeholder') }}">
                                            @error('contactForm.telephone') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-cellphone text-success"></i> {{ __('crm.mobile') }}
                                            </label>
                                            <input type="text" wire:model="contactForm.mobile" class="form-control" placeholder="{{ __('crm.mobile_number_placeholder') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="mdi mdi-office-building text-primary"></i> {{ __('crm.company_units') }} <span class="text-danger">*</span>
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
                                            }"
                                                class="tag-select-container modal-contact-units-select @error('contactForm.unit_name') is-invalid @enderror">
                                                <div class="tag-select-input" @click="open = true">
                                                    <template x-for="unit in (Array.isArray(selectedUnits) ? selectedUnits : [])" :key="unit">
                                                        <span class="tag-badge">
                                                            <span class="tag-badge-label" x-text="unit"></span>
                                                            <i class="mdi mdi-close" @click.stop="removeUnit(unit)" role="button" title="{{ __('crm.delete') }}"></i>
                                                        </span>
                                                    </template>
                                                    <input
                                                        type="text"
                                                        x-model="search"
                                                        @focus="open = true"
                                                        @click="open = true"
                                                        placeholder="{{ __('crm.search_or_select_units') }}"
                                                        class="tag-input"
                                                        autocomplete="off"
                                                    >
                                                    <i class="mdi mdi-chevron-down flex-shrink-0 ml-auto" :class="{ 'rotated': open }"></i>
                                                </div>
                                                <div x-show="open"
                                                    @click.away="open = false"
                                                    x-transition
                                                    class="tag-dropdown">
                                                    <template x-for="unit in filteredUnits" :key="unit">
                                                        <div @click="toggleUnit(unit)"
                                                            class="tag-dropdown-item d-flex align-items-center"
                                                            :class="{ 'bg-light': isSelected(unit) }">
                                                            <i class="mdi mr-2"
                                                                :class="isSelected(unit) ? 'mdi-checkbox-marked text-primary' : 'mdi-checkbox-blank-outline'"></i>
                                                            <span x-text="unit"></span>
                                                        </div>
                                                    </template>
                                                    <div x-show="filteredUnits.length === 0" class="no-results">
                                                        <i class="mdi mdi-alert-circle-outline"></i>
                                                        <span>{{ __('crm.no_units_found_static') }}</span>
                                                    </div>
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
                            <div class="section-header mb-4">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-cog text-info"></i> {{ __('crm.preferences_settings') }}
                                </h6>
                            </div>
                            <div class="section-body" style="padding-bottom: 20px;">
                           
                                
                                <div class="row g-3 gy-4">
                                    <!-- Reports -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.receive_report')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.receive_report') }">
                                                <div class="preference-icon bg-primary bg-opacity-10 text-primary" style="background-color: rgba(13, 110, 253, 0.1) !important;">
                                                    <i class="mdi mdi-file-document"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.analysis_reports') }}</span>
                                                    <span class="preference-desc">{{ __('crm.receive_pdf_analysis_reports') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.receive_report" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Schedule of Analysis -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.can_receive_schedule_of_analysis')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.can_receive_schedule_of_analysis') }">
                                                <div class="preference-icon bg-info bg-opacity-10 text-info" style="background-color: rgba(13, 202, 240, 0.1) !important;">
                                                    <i class="mdi mdi-calendar-clock"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.analysis_schedule') }}</span>
                                                    <span class="preference-desc">{{ __('crm.analysis_progress_updates') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.can_receive_schedule_of_analysis" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Price List -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.receive_price_list')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.receive_price_list') }">
                                                <div class="preference-icon bg-success bg-opacity-10 text-success" style="background-color: rgba(25, 135, 84, 0.1) !important;">
                                                    <i class="mdi mdi-currency-usd"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.price_lists') }}</span>
                                                    <span class="preference-desc">{{ __('crm.receive_updated_product_prices') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.receive_price_list" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Invoices -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.receive_invoice')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.receive_invoice') }">
                                                <div class="preference-icon bg-warning bg-opacity-10 text-warning" style="background-color: rgba(255, 193, 7, 0.1) !important;">
                                                    <i class="mdi mdi-receipt"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.invoices') }}</span>
                                                    <span class="preference-desc">{{ __('crm.billing_invoice_notifications') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.receive_invoice" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Reminders -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.can_receive_payment_reminders')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.can_receive_payment_reminders') }">
                                                <div class="preference-icon bg-danger bg-opacity-10 text-danger" style="background-color: rgba(220, 53, 69, 0.1) !important;">
                                                    <i class="mdi mdi-bell-ring"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.payment_alerts') }}</span>
                                                    <span class="preference-desc">{{ __('crm.payment_reminders_pending') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.can_receive_payment_reminders" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Login Access -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.can_login')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.can_login') }">
                                                <div class="preference-icon bg-dark bg-opacity-10 text-dark" style="background-color: rgba(33, 37, 41, 0.1) !important;">
                                                    <i class="mdi mdi-login"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.portal_access') }}</span>
                                                    <span class="preference-desc">{{ __('crm.allow_customer_portal_login') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.can_login" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Sample -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.can_submit_sample')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.can_submit_sample') }">
                                                <div class="preference-icon bg-secondary bg-opacity-10 text-secondary" style="background-color: rgba(108, 117, 125, 0.1) !important;">
                                                    <i class="mdi mdi-flask"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.submit_samples') }}</span>
                                                    <span class="preference-desc">{{ __('crm.permission_register_samples') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.can_submit_sample" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Active Status -->
                                    <div class="col-md-6 col-xl-4">
                                        <div class="h-100" wire:click="$toggle('contactForm.active')">
                                            <div class="preference-card" :class="{ 'active': @entangle('contactForm.active') }">
                                                <div class="preference-icon bg-success bg-opacity-10 text-success" style="background-color: rgba(25, 135, 84, 0.1) !important;">
                                                    <i class="mdi mdi-check-circle"></i>
                                                </div>
                                                <div class="preference-info">
                                                    <span class="preference-title">{{ __('crm.active_status') }}</span>
                                                    <span class="preference-desc">{{ __('crm.contact_currently_active') }}</span>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" wire:model="contactForm.active" role="switch" @click.stop>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Section -->
                        @if($contactForm['can_login'])
                            @php
                                $hasExistingUser = $editingContact && \App\User::where('email', $editingContact->email)->exists();
                            @endphp
                            <div class="form-section mb-4">
                                <div class="section-header mb-3">
                                    <h6 class="mb-0 text-muted">
                                        <i class="mdi mdi-lock text-danger"></i> {{ __('crm.password_configuration') }}
                                        @if($hasExistingUser)
                                            <small class="text-secondary ms-2">({{ __('crm.leave_blank_keep_password') }})</small>
                                        @endif
                                    </h6>
                                </div>
                                <div class="section-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="mdi mdi-lock text-danger"></i> {{ __('crm.password') }}
                                                    @if(!$hasExistingUser)<span class="text-danger">*</span>@endif
                                                </label>
                                                <input type="password" wire:model="contactForm.main_password" class="form-control" placeholder="{{ $hasExistingUser ? __('crm.leave_blank_keep_current_short') : __('crm.password_placeholder') }}">
                                                @error('contactForm.main_password') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="mdi mdi-lock-check text-danger"></i> {{ __('crm.confirm_password') }}
                                                    @if(!$hasExistingUser)<span class="text-danger">*</span>@endif
                                                </label>
                                                <input type="password" wire:model="contactForm.confirm_password" class="form-control" placeholder="{{ $hasExistingUser ? __('crm.leave_blank_keep_current_short') : __('crm.confirm_password_placeholder') }}">
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
                        <i class="mdi mdi-close"></i> {{ __('crm.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="saveContact" wire:loading.attr="disabled" wire:target="saveContact">
                        <span wire:loading.remove wire:target="saveContact">
                            <i class="mdi mdi-content-save"></i> {{ $editingContact ? __('crm.update') : __('crm.create') }} {{ __('crm.contact') }}
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
    .preference-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 8px;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        margin: 10px !important;
    }
    .preference-card:hover {
        border-color: #6D0A0E;
        box-shadow: 0 4px 12px rgba(109, 10, 14, 0.1);
        transform: translateY(-2px);
    }
    .preference-card.active {
        border-color: #6D0A0E;
        background: rgba(109, 10, 14, 0.02);
    }
    .preference-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .preference-info {
        flex: 1;
    }
    .preference-title {
        display: block;
        font-weight: 600;
        color: #495057;
        font-size: 14px;
        margin-bottom: 2px;
    }
    .preference-desc {
        display: block;
        font-size: 11px;
        color: #6c757d;
        line-height: 1.2;
    }
    /* Custom Switch Positioning */
    .preference-card .form-check-input {
        width: 2.5em; 
        height: 1.25em;
        cursor: pointer;
    }
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
        border-color: #6D0A0E;
        box-shadow: 0 0 0 0.2rem rgba(109, 10, 14, 0.25);
        background: #ffffff;
        outline: none;
    }

    .modern-select:hover {
        border-color: #6D0A0E;
        box-shadow: 0 4px 8px rgba(109, 10, 14, 0.15);
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

    .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 3px;
        font-size: 12px;
    }

    .rm-act-btn:last-child {
        margin-right: 0;
    }

    .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .rm-act-btn--delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }

    .pricelist-action-btn {
        border-radius: 10px;
        min-height: 42px;
        font-weight: 600;
        padding-left: 16px;
        padding-right: 16px;
    }

    .contacts-manager-page .contacts-manager-filter-select-input {
        padding: 0 8px 0 12px;
        min-height: 42px;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 8px;
        background: #fff;
        display: flex;
    }

    .contacts-manager-page .contacts-manager-filter-select .tag-select-native {
        width: 100%;
        display: block;
        border: none;
        box-shadow: none;
        background-color: transparent;
        padding: 10px 28px 10px 0;
        min-height: 40px;
        line-height: 1.5;
        font-size: 0.9375rem;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 4px center;
        background-size: 16px 16px;
        cursor: pointer;
    }

    .contacts-manager-page .contacts-manager-filter-select .tag-select-native:focus {
        border: none;
        box-shadow: none;
        outline: none;
        background-color: transparent;
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
        padding-bottom: 24px;
        margin-bottom: 24px !important;
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
        padding-top: 20px;
        padding-bottom: 10px;
    }
    
    /* Form switch styling */
    .form-check-input:checked {
        background-color: #6D0A0E;
        border-color: #6D0A0E;
    }
    
    .form-check-input:focus {
        border-color: #6D0A0E;
        box-shadow: 0 0 0 0.2rem rgba(109, 10, 14, 0.25);
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
        border-color: #6D0A0E;
        box-shadow: 0 0 0 0.2rem rgba(109, 10, 14, 0.25);
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

    /* Contact modal: company units multi-select uses global tag-select-*; chevron + empty state only */
    .modal-contact-units-select .tag-select-input .mdi-chevron-down {
        transition: transform 0.2s ease;
        color: #6c757d;
        font-size: 1.25rem;
        line-height: 1;
    }

    .modal-contact-units-select .tag-select-input .mdi-chevron-down.rotated {
        transform: rotate(180deg);
    }

    .modal-contact-units-select .tag-dropdown {
        margin-top: 4px;
        border-top: 1px solid #6D0A0E;
        border-radius: 8px;
    }

    .modal-contact-units-select .no-results {
        padding: 20px;
        text-align: center;
        color: #6c757d;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .modal-contact-units-select .no-results i {
        font-size: 32px;
        opacity: 0.5;
    }
    </style>
</div>
