<div class="quotations-manager-page ls-quotation-shell {{ $embedded ? 'is-embedded' : '' }}">
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-3" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    @unless($embedded)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 ls-quotation-header-card">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                            <div>
                                <h2 class="mb-0">
                                    <i class="mdi mdi-file-document-edit-outline text-primary"></i>
                                    Quotations Management
                                </h2>
                                <p class="text-muted mb-0">View and manage customer quotations</p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                @include('livewire.billing.partials.quotation-drafts-dropdown', ['drafts' => $drafts])
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add-quotation">
                                    <i class="mdi mdi-plus-circle-outline"></i> Add Quotation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endunless

    <div class="row mb-3" id="quotation-stage-tabs">
        <div class="col-12">
            <div class="card shadow-sm border-0 quotation-stage-tabs-card">
                <div class="quotation-stage-tabs px-3 px-md-4 py-3">
                    <button type="button"
                            class="quotation-stage-tab {{ $stageFilter === '' ? 'is-active' : '' }}"
                            wire:click="setStageFilter('')">
                        <i class="mdi mdi-view-list-outline"></i>
                        <span>All</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['all'] }}</span>
                    </button>
                    <button type="button"
                            class="quotation-stage-tab {{ $stageFilter === 'Quote In Preparation' ? 'is-active' : '' }}"
                            wire:click="setStageFilter('Quote In Preparation')">
                        <i class="mdi mdi-clock-outline"></i>
                        <span>In Preparation</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['Quote In Preparation'] }}</span>
                    </button>
                    <button type="button"
                            class="quotation-stage-tab {{ $stageFilter === 'Quote In Approval' ? 'is-active' : '' }}"
                            wire:click="setStageFilter('Quote In Approval')">
                        <i class="mdi mdi-account-check-outline"></i>
                        <span>In Approval</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['Quote In Approval'] }}</span>
                    </button>
                    <button type="button"
                            class="quotation-stage-tab quotation-stage-tab--complete {{ $stageFilter === 'Quote Complete' ? 'is-active' : '' }}"
                            wire:click="setStageFilter('Quote Complete')">
                        <i class="mdi mdi-check-circle-outline"></i>
                        <span>Complete</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['Quote Complete'] }}</span>
                    </button>
                    <button type="button"
                            class="quotation-stage-tab {{ $stageFilter === 'approval-settings' ? 'is-active' : '' }}"
                            wire:click="setStageFilter('approval-settings')">
                        <i class="mdi mdi-cog-outline"></i>
                        <span>Approval configuration</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($stageFilter === 'approval-settings')
        <div class="row mb-4" wire:key="approval-settings-panel">
            <div class="col-12">
                @livewire('billing.quotation-approval-settings', key('quotation-approval-settings'))
            </div>
        </div>
    @else
        @if(! $embedded && $drafts && $drafts->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 ls-quotation-panel" style="border-color: #fcd34d;">
                        <div class="card-header border-0" style="background: #fffbeb;">
                            <h6 class="text-warning mb-0">
                                <i class="mdi mdi-file-edit"></i> Draft Quotations ({{ $drafts->count() }})
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover ls-table mb-0">
                                    <tbody>
                                        @foreach($drafts as $draft)
                                            <tr>
                                                <td><strong>{{ $draft->quote_number }}</strong></td>
                                                <td>{{ $draft->customer }}</td>
                                                <td>{{ $draft->quotation_type }}</td>
                                                <td>{{ \Carbon\Carbon::parse($draft->created_at)->format('Y-m-d') }}</td>
                                                <td class="text-right">
                                                    <a href="{{ route('add-qoute-details-view', ['id' => $draft->id]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="mdi mdi-pencil"></i> Continue
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row" wire:key="quotation-list-{{ $stageFilter }}-{{ $sortField }}-{{ $sortDirection }}">
            <div class="col-12">
                <div class="card shadow-sm border-0 ls-quotation-panel">
                    <div class="card-header border-0 ls-quotation-list-header">
                        <div class="ls-quotation-list-header__top">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-table"></i>
                                @if($stageFilter === '')
                                    All Quotations
                                @else
                                    {{ $stageFilter }}
                                @endif
                            </h5>
                            <div class="ls-quotation-list-header__search">
                                @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-search-toolbar')
                            </div>
                        </div>
                    </div>
                    <div class="card-body quotation-list-panel @if($quotations->count() > 0) pt-0 @endif"
                         wire:loading.class="is-loading"
                         wire:target="search,stageFilter,sortField,sortDirection,customerFilter,quotationTypeFilter,labSectionFilter,startDate,endDate,perPage,setStageFilter,sortBy,gotoPage,previousPage,nextPage,clearFilters">
                        @if($quotations->count() > 0)
                            <div class="ls-quotation-table-scroll">
                                <table class="table table-hover ls-table ls-table--dense mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 1%;">Actions</th>
                                            <th>
                                                <button type="button" class="ls-quotation-sort-btn {{ $sortField === 'quote_number' ? 'is-active' : '' }}" wire:click="sortBy('quote_number')">
                                                    Quote #
                                                    <i class="mdi {{ $sortField === 'quote_number' && $sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></i>
                                                </button>
                                            </th>
                                            <th>Type</th>
                                            <th>Lab Section(s)</th>
                                            <th>
                                                <button type="button" class="ls-quotation-sort-btn {{ $sortField === 'status' ? 'is-active' : '' }}" wire:click="sortBy('status')">
                                                    Status
                                                    <i class="mdi {{ $sortField === 'status' && $sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></i>
                                                </button>
                                            </th>
                                            <th>
                                                <button type="button" class="ls-quotation-sort-btn {{ $sortField === 'quote_date' ? 'is-active' : '' }}" wire:click="sortBy('quote_date')">
                                                    Quote Date
                                                    <i class="mdi {{ $sortField === 'quote_date' && $sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></i>
                                                </button>
                                            </th>
                                            <th>
                                                <button type="button" class="ls-quotation-sort-btn {{ $sortField === 'expiring_date' ? 'is-active' : '' }}" wire:click="sortBy('expiring_date')">
                                                    Expiry
                                                    <i class="mdi {{ $sortField === 'expiring_date' && $sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></i>
                                                </button>
                                            </th>
                                            <th>
                                                <button type="button" class="ls-quotation-sort-btn {{ $sortField === 'customer' ? 'is-active' : '' }}" wire:click="sortBy('customer')">
                                                    Customer
                                                    <i class="mdi {{ $sortField === 'customer' && $sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></i>
                                                </button>
                                            </th>
                                            <th>Prepared By</th>
                                            <th class="text-right">
                                                <button type="button" class="ls-quotation-sort-btn {{ $sortField === 'total_amount' ? 'is-active' : '' }}" wire:click="sortBy('total_amount')">
                                                    Total
                                                    <i class="mdi {{ $sortField === 'total_amount' && $sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></i>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quotations as $quotation)
                                            <tr class="quotation-preview-hover-parent">
                                                <td>
                                                    <div class="d-flex quotation-actions-cell">
                                                        <a href="{{ $quotation->status === 'Quote In Preparation'
                                                                ? route('add-qoute-details-view', ['id' => $quotation->id])
                                                                : route('view_quotation_final', ['id' => $quotation->id]) }}"
                                                           class="rm-act-btn rm-act-btn--open"
                                                           title="Open quotation">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                        @if($quotation->status === 'Quote In Preparation')
                                                            <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}"
                                                               class="rm-act-btn rm-act-btn--edit"
                                                               title="Edit in preparation">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </a>
                                                            <button type="button"
                                                                    wire:click="openImportModal(@js((string) $quotation->id))"
                                                                    class="rm-act-btn rm-act-btn--import"
                                                                    title="Import lines from Excel or the Amspec quotation-preparation PDF into this quote">
                                                                <i class="mdi mdi-file-upload-outline"></i>
                                                            </button>
                                                        @endif
                                                        @if($quotation->status === 'Quote In Approval' && in_array((string) $quotation->id, $approvableQuotationIds ?? [], true))
                                                            <div class="bq-approve-menu" x-data="{ open: false }" @click.outside="open = false">
                                                                <button type="button"
                                                                    class="rm-act-btn bq-approve-btn is-awaiting"
                                                                    title="Approve or reject quotation"
                                                                    @click.stop="open = !open"
                                                                    :aria-expanded="open">
                                                                    <i class="mdi mdi-check-circle"></i>
                                                                </button>
                                                                <div class="bq-approve-menu__panel" x-show="open" x-cloak @click.stop>
                                                                    <button type="button"
                                                                        class="bq-approve-menu__item"
                                                                        wire:click="openApproveAndSendModal(@js((string) $quotation->id))"
                                                                        @click="open = false">
                                                                        <i class="mdi mdi-check-decagram text-success"></i>
                                                                        Approve and send
                                                                    </button>
                                                                    <button type="button"
                                                                        class="bq-approve-menu__item bq-approve-menu__item--danger"
                                                                        wire:click="openRejectModal(@js((string) $quotation->id))"
                                                                        @click="open = false">
                                                                        <i class="mdi mdi-close-circle-outline"></i>
                                                                        Reject
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <a href="{{ route('clone_quotation', ['id' => $quotation->id]) }}"
                                                           class="rm-act-btn rm-act-btn--clone"
                                                           title="Clone">
                                                            <i class="mdi mdi-content-duplicate"></i>
                                                        </a>
                                                        @can('laboratory.components.quotation.add')
                                                            @if($quotation->status === 'Quote Complete'
                                                                && $quotation->quotation_type === 'Analysis'
                                                                && $quotation->expiring_date
                                                                && \Carbon\Carbon::parse($quotation->expiring_date)->startOfDay()->gte(now()->startOfDay()))
                                                                <button type="button"
                                                                        wire:click="openCreateEnquiryWizard(@js((string) $quotation->id))"
                                                                        wire:loading.attr="disabled"
                                                                        class="rm-act-btn rm-act-btn--enquiry"
                                                                        title="Create enquiry from quotation">
                                                                    <i class="mdi mdi-flask-outline"></i>
                                                                </button>
                                                            @endif
                                                        @endcan
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ $quotation->status === 'Quote In Preparation'
                                                            ? route('add-qoute-details-view', ['id' => $quotation->id])
                                                            : route('view_quotation_final', ['id' => $quotation->id]) }}" class="quotation-quote-number">
                                                        {{ $quotation->quote_number }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="quotation-type-chip {{ $quotation->quotation_type === 'Analysis' ? 'quotation-type-chip--analysis' : 'quotation-type-chip--general' }}">
                                                        {{ $quotation->quotation_type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $sectionNames = $quotation->labSections->pluck('name')->filter()->values();
                                                    @endphp
                                                    @if($sectionNames->isEmpty())
                                                        <span class="text-muted">—</span>
                                                    @else
                                                        {{ $sectionNames->implode(', ') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <span @class([
                                                        'quotation-status-chip',
                                                        'quotation-status-chip--complete' => $quotation->status === 'Quote Complete',
                                                        'quotation-status-chip--approval' => $quotation->status === 'Quote In Approval',
                                                        'quotation-status-chip--prep' => ! in_array($quotation->status, ['Quote Complete', 'Quote In Approval'], true),
                                                    ])>
                                                        {{ $quotation->status }}
                                                    </span>
                                                </td>
                                                <td>{{ $quotation->quote_date ?: '—' }}</td>
                                                <td>
                                                    {{ $quotation->expiring_date ?: '—' }}
                                                    @if(! empty($quotation->expiring_date) && \Carbon\Carbon::parse($quotation->expiring_date)->isPast())
                                                        <span class="badge badge-danger ml-1">Expired</span>
                                                    @endif
                                                </td>
                                                <td>{{ $quotation->customer }}</td>
                                                <td>{{ $quotation->prepared_by_name }}</td>
                                                <td class="text-right font-weight-bold">{{ number_format((float) $quotation->total_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-file-document-edit-outline text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No quotations found</h5>
                                <p class="text-muted mb-0">
                                    @if($stageFilter === 'Quote In Preparation' || $stageFilter === '')
                                        Create a quotation to get started, or adjust filters.
                                    @else
                                        Nothing matches this stage or filter yet.
                                    @endif
                                </p>
                            </div>
                        @endif

                        <div class="ls-quotation-list-footer">
                            <div class="ls-quotation-list-footer__meta">
                                <span class="text-muted small" wire:loading.remove wire:target="search,stageFilter,sortField,sortDirection,customerFilter,quotationTypeFilter,labSectionFilter,startDate,endDate,perPage,setStageFilter,sortBy,gotoPage,previousPage,nextPage,clearFilters">
                                    @if($quotations->total() > 0)
                                        Showing {{ $quotations->firstItem() ?? 0 }}–{{ $quotations->lastItem() ?? 0 }} of {{ $quotations->total() }}
                                        {{ \Illuminate\Support\Str::plural('quotation', $quotations->total()) }}
                                    @else
                                        {{ $quotations->total() }} {{ \Illuminate\Support\Str::plural('quotation', $quotations->total()) }}
                                    @endif
                                </span>
                                <span class="text-muted small" wire:loading wire:target="search,stageFilter,sortField,sortDirection,customerFilter,quotationTypeFilter,labSectionFilter,startDate,endDate,perPage,setStageFilter,sortBy,gotoPage,previousPage,nextPage,clearFilters">
                                    Updating…
                                </span>
                                <div class="ls-quotation-list-footer__per-page">
                                    <label for="perPage" class="mb-0 text-muted small">Show</label>
                                    <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm">
                                        @foreach($perPageOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if($quotations->total() > 0)
                                <div class="ls-quotation-list-footer__pages">
                                    {{ $quotations->links('pagination::bootstrap-4') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showQuotationDetails && $selectedQuotation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-document-edit-outline"></i>
                            Quotation Details - {{ $selectedQuotation->quote_number }}
                        </h5>
                        <button type="button" class="btn-close ls-modal-close" wire:click="closeQuotationDetails" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Customer Information</h6>
                                <p class="mb-1"><strong>Customer:</strong> {{ $selectedQuotation->customer->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Contact:</strong>
                                    @if($selectedQuotation->contact)
                                        {{ $selectedQuotation->contact->first_name }} {{ $selectedQuotation->contact->last_name }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Quotation Information</h6>
                                <p class="mb-1"><strong>Quote Date:</strong> {{ $selectedQuotation->quote_date }}</p>
                                <p class="mb-1"><strong>Expiring Date:</strong> {{ $selectedQuotation->expiring_date ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Type:</strong> {{ $selectedQuotation->quotation_type }}</p>
                                <p class="mb-1"><strong>Status:</strong> {{ $selectedQuotation->status }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="gap: 8px;">
                        <button type="button" class="btn btn-secondary" wire:click="closeQuotationDetails">Close</button>
                        <a href="{{ route('quotation.preview', ['id' => $selectedQuotation->id]) }}"
                           class="btn quotation-preview-quote-btn"
                           target="_blank">
                            <i class="mdi mdi-file-eye"></i> Preview Quote
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('layouts.lab.partials.billing.amspec-import-modal', [
        'context' => 'quotation',
        'driver' => 'livewire',
        'isOpen' => $showImportModal,
        'importFormat' => $importFormat,
        'importPricingMode' => $importPricingMode,
        'closeMethod' => 'closeImportModal',
        'submitMethod' => 'submitImport',
        'wireModel' => 'importFile',
        'errorBag' => 'importFile',
        'inputId' => 'ls-quote-list-import-file',
    ])

    @unless($embedded)
        <livewire:billing.create-enquiry-from-quotation-wizard />
        @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')
    @endunless

    <style>
        .rm-act-btn--import {
            border: 1px solid #fde68a;
            color: #b45309;
            background: #fffbeb;
        }
        .rm-act-btn--import:hover {
            background: #fef3c7;
            border-color: #fcd34d;
        }
    </style>

    @if($showApproveAndSendModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); overflow-y: auto; z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve and send quotation</h5>
                        <button type="button" class="close" wire:click="closeApproveAndSendModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3" style="color:#334155;">
                            Approving marks this quotation as fit to send and delivers it to the selected CRM contacts.
                            Portal delivery is automatic when eligible; email is optional (PDF attachment only).
                        </p>
                        <h6 class="font-weight-bold mb-2" style="color:#1e3a8a;">Who will receive this quotation</h6>
                        <p class="small text-muted mb-2">
                            Contacts with “Receive quotations” are pre-selected. Company unit comes from CRM.
                        </p>
                        @if(count($approveRecipientOptions) === 0)
                            <div class="alert alert-warning py-2 small mb-3">
                                No customer contacts found for this quotation’s customer.
                            </div>
                        @else
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 2.5rem;"></th>
                                            <th>Contact</th>
                                            <th>Company unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($approveRecipientOptions as $recipient)
                                            @php $checked = in_array((string) $recipient['id'], $approveRecipientContactIds, true); @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        @checked($checked)
                                                        wire:click="toggleApproveRecipient('{{ $recipient['id'] }}')">
                                                </td>
                                                <td>
                                                    <strong>{{ $recipient['name'] }}</strong>
                                                    @if(!empty($recipient['receive_quotations']))
                                                        <span class="badge badge-light border small">Receives quotations</span>
                                                    @endif
                                                </td>
                                                <td>{{ $recipient['company_unit'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="bq-approve-send-email" wire:model="approveSendEmail">
                            <label class="form-check-label" for="bq-approve-send-email">
                                Email quotation PDF to selected contacts (optional)
                            </label>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold" for="bq-approve-comments">Comments (optional)</label>
                            <textarea id="bq-approve-comments"
                                class="form-control form-control-sm"
                                rows="2"
                                wire:model="approvalDecisionComments"
                                placeholder="Optional note for the requester"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeApproveAndSendModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="confirmApproveAndSend" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmApproveAndSend">
                                <i class="mdi mdi-check-decagram"></i> Approve and send
                            </span>
                            <span wire:loading wire:target="confirmApproveAndSend">Approving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showRejectModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject quotation</h5>
                        <button type="button" class="close" wire:click="closeRejectModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Provide a reason for returning this quotation to preparation.</p>
                        <textarea class="form-control"
                            rows="4"
                            wire:model="approvalDecisionComments"
                            placeholder="Rejection reason (required)"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeRejectModal">Cancel</button>
                        <button type="button" class="btn btn-outline-danger" wire:click="confirmReject" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmReject">Confirm reject</span>
                            <span wire:loading wire:target="confirmReject">Rejecting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .bq-approve-menu { position: relative; display: inline-flex; }
        .bq-approve-btn {
            background: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            color: #047857 !important;
        }
        .bq-approve-btn .mdi {
            color: #047857 !important;
            font-size: 1.15rem;
            animation: bq-approve-shake 1.35s ease-in-out infinite;
            transform-origin: 50% 55%;
        }
        .bq-approve-menu__panel {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0.35rem;
            min-width: 15rem;
            padding: 0.4rem 0;
            background: #fff;
            border: 1px solid #dbe5f0;
            border-radius: 10px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            z-index: 30;
        }
        .bq-approve-menu__item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.55rem 0.9rem;
            border: 0;
            background: transparent;
            text-align: left;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #0369a1;
            cursor: pointer;
        }
        .bq-approve-menu__item:hover { background: #f8fafc; }
        .bq-approve-menu__item--danger { color: #b91c1c; }
        .bq-approve-menu__reject { padding: 0.5rem 0.9rem 0.75rem; }
        @keyframes bq-approve-shake {
            0%, 100% { transform: rotate(0deg) scale(1); }
            12% { transform: rotate(12deg) scale(1.05); }
            24% { transform: rotate(-10deg) scale(1.05); }
            36% { transform: rotate(8deg) scale(1.04); }
            48% { transform: rotate(-6deg) scale(1.03); }
            60% { transform: rotate(3deg) scale(1.02); }
            72% { transform: rotate(0deg) scale(1); }
        }
    </style>

</div>
