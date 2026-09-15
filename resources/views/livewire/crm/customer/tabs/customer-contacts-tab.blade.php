<div>
    @if($flashMessage !== '')
        <div class="alert alert-{{ $flashType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            <i class="mdi mdi-{{ $flashType === 'success' ? 'check-circle' : 'alert-circle' }}"></i> {{ $flashMessage }}
            <button type="button" class="close" wire:click="dismissFlash" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#f0f4ff;">
                <i class="mdi mdi-account-multiple text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.client_contacts_directory') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.client_contacts_directory_subtitle') }}</small>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="{{ __('crm.search_contacts') }}"
                    wire:model.live.debounce.300ms="search">
            </div>
            <!-- Show Entries -->
            <div class="d-flex align-items-center flex-wrap flex-shrink-0 crm-contacts-per-page">
                <label class="mb-0 mr-2 crm-filter-label text-nowrap">{{ __('crm.show') }}</label>
                <div class="tag-select-container crm-units-tag-select" style="min-width: 88px;">
                    <div class="tag-select-input crm-units-tag-select-input">
                        <select wire:model.live="perPage" wire:key="per-page-select"
                            class="tag-select-native no-select2">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <label class="mb-0 ml-2 crm-filter-label text-nowrap">{{ __('crm.entries') }}</label>
            </div>
            <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-file-excel"></i> {{ __('crm.export_to_excel') }}
            </button>
            <x-imara.primary-btn subject="{{ __('crm.contact') }}" wire:click="openContactForm" loading-target="openContactForm" />
        </div>
    </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i
            class="mdi mdi-loading mdi-spin"></i>
        {{ __('crm.loading') }}...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th style="min-width: 100px;">{{ __('crm.actions') }}</th>
                <th>No</th>
                <th nowrap>{{ __('crm.first_name') }}</th>
                <th nowrap>{{ __('crm.middle_name') }}</th>
                <th nowrap>{{ __('crm.last_name') }}</th>
                <th nowrap>{{ __('crm.job_title') }}</th>
                <th nowrap>{{ __('crm.unit_names') }}</th>
                <th nowrap>{{ __('crm.email') }}</th>
                <th nowrap>{{ __('crm.telephone') }}</th>
                <th nowrap>{{ __('crm.mobile') }}</th>
                <th nowrap>{{ __('crm.other_customers_assigned') }}</th>
                <th nowrap>{{ __('crm.receives_price_list') }}</th>
                <th nowrap>{{ __('crm.receives_invoice') }}</th>
                <th nowrap>{{ __('crm.receives_report') }}</th>
                <th nowrap>{{ __('crm.receives_feedback') }}</th>
                <th nowrap>{{ __('crm.active') }}?</th>
                <th nowrap>{{ __('crm.can_login') }}?</th>
            </tr>
        </x-slot:header>
                    @forelse($contacts as $contact)
                        <tr>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <x-imara.row-action-btn variant="edit" wire:click="openContactForm('{{ $contact->id }}')" class="mr-1" :title="__('crm.edit')" />
                                    <x-imara.row-action-btn variant="delete" wire:click="deleteContact('{{ $contact->id }}')" wire:confirm="{{ __('crm.delete_contact_confirm') }}" :title="__('crm.delete')" />
                                </x-crm.action-buttons>
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $contact->first_name }}
                                @if($contact->is_main_customer_contact)
                                    <span class="crm-badge crm-badge-primary ml-1">Main</span>
                                @endif
                            </td>
                            <td>{{ $contact->middle_name }}</td>
                            <td>{{ $contact->last_name }}</td>
                            <td>{{ $contact->job_occupation }}</td>
                            <td>{{ getUnitNamesByID($contact->unit_name ? explode(',', $contact->unit_name) : []) }}</td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ $contact->telephone }}</td>
                            <td>{{ $contact->mobile }}</td>
                            <td>{{ getOtherCustomersByID($contact->other_customers ? explode(',', $contact->other_customers) : []) }}</td>
                            <td class="text-small text-center">
                                @if($contact->receive_price_list == '1')
                                    <span class="crm-badge crm-badge-success">Yes</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">No</span>
                                @endif
                            </td>
                            <td class="text-small text-center">
                                @if($contact->receive_invoice == '1')
                                    <span class="crm-badge crm-badge-success">Yes</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">No</span>
                                @endif
                            </td>
                            <td class="text-small text-center">
                                @if($contact->receive_report == '1')
                                    <span class="crm-badge crm-badge-success">Yes</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">No</span>
                                @endif
                            </td>
                            <td class="text-small text-center">
                                @if($contact->receive_feedback == '1')
                                    <span class="crm-badge crm-badge-success" title="Opted-in for Feedback">Yes</span>
                                @else
                                    <span class="crm-badge crm-badge-danger" title="Not Opted-in">No</span>
                                @endif
                            </td>
                            <td class="text-small text-center">
                                @if($contact->active == '1')
                                    <span class="crm-badge crm-badge-success">{{ ucfirst(__('crm.active')) }}</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">{{ __('crm.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-small text-center">
                                @if($contact->can_login == '1')
                                    <span class="crm-badge crm-badge-success">Yes</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">No</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16">
                                <x-crm.empty-state
                                    icon="mdi-account-multiple"
                                    :message="__('crm.no_contacts_found')"
                                    :help="__('crm.no_contacts_help')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($contacts->firstItem() ?? 0) . ' to ' . ($contacts->lastItem() ?? 0) . ' of ' . $contacts->total() . ' results'">
        {{ $contacts->links() }}
    </x-crm.pagination>

    @if($showForm)
        @livewire(\App\Livewire\Crm\Contact\ContactForm::class, [
            'customerId' => $customer->id,
            'contactId' => $editingContact ? $editingContact->id : null
        ], 'contact-form-' . ($editingContact ? $editingContact->id : 'new'))
    @endif
    <style>
        .crm-units-tag-select-input {
            padding: 0 8px 0 10px;
            min-height: 38px;
            align-items: center;
        }

        .crm-units-tag-select .tag-select-native {
            width: 100%;
            border: none;
            box-shadow: none;
            background: transparent;
            padding: 8px 26px 8px 0;
            min-height: 36px;
            line-height: 1.5;
            font-size: 0.875rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 2px center;
            background-size: 14px 14px;
            cursor: pointer;
        }

        .crm-units-tag-select .tag-select-native:focus {
            outline: none;
            box-shadow: none;
        }
    </style>
</div>