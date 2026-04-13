<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#f0f4ff;">
                <i class="mdi mdi-account-multiple text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Client Contacts
                    Directory</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Authorized contacts, communication
                    preferences &amp; portal access</small>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="Search contacts..."
                    wire:model.live.debounce.300ms="search">
            </div>
            <!-- Show Entries -->
            <div class="d-flex align-items-center mb-2 mb-md-0 mr-3 flex-shrink-0">
                <label class="mb-0 mr-2 crm-filter-label text-nowrap">Show</label>
                <select wire:model.live="perPage" wire:key="per-page-select" class="custom-select custom-select-sm no-select2" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="mb-0 ml-2 crm-filter-label text-nowrap">entries</label>
            </div>
            <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-file-excel"></i> Export to Excel
            </button>
            <button class="btn btn-add btn-sm" wire:click="openContactForm">
                <i class="mdi mdi-plus"></i> Add
            </button>
        </div>
    </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i
            class="mdi mdi-loading mdi-spin"></i>
        Loading...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th style="min-width: 100px;">Actions</th>
                <th>No</th>
                <th nowrap>First Name</th>
                <th nowrap>Middle Name</th>
                <th nowrap>Last Name</th>
                <th nowrap>Job Title</th>
                <th nowrap>Unit Name(s)</th>
                <th nowrap>Email</th>
                <th nowrap>Telephone</th>
                <th nowrap>Mobile</th>
                <th nowrap>Other Customers Assigned</th>
                <th nowrap>Receives Price List?</th>
                <th nowrap>Receives Invoice?</th>
                <th nowrap>Receives Report?</th>
                <th nowrap>Receives Feedback?</th>
                <th nowrap>Active?</th>
                <th nowrap>Can Login?</th>
            </tr>
        </x-slot:header>
                    @forelse($contacts as $contact)
                        <tr>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <button class="btn crm-btn crm-btn-edit btn-sm"
                                        wire:click="openContactForm({{ $contact->id }})">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button class="btn crm-btn crm-btn-delete btn-sm"
                                        wire:click="deleteContact({{ $contact->id }})"
                                        wire:confirm="Are you sure you want to delete this contact?">
                                        <i class="mdi mdi-trash-can-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $contact->first_name }}</td>
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
                                    <span class="crm-badge crm-badge-success">Active</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">Inactive</span>
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
                                    message="No contacts found"
                                    help="Add a liaison contact to begin managing communication preferences."
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
</div>