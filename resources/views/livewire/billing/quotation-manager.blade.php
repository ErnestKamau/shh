<div class="quotations-manager-page">
    <style>
        .quotations-manager-page .quotation-stage-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .quotations-manager-page .quotation-stage-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.5rem 0.9rem;
            border-radius: 8px;
            border: 1px solid var(--ls-color-border, #e2e8f0);
            background: #fff;
            color: var(--ls-color-muted, #64748b);
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .quotations-manager-page .quotation-stage-tab:hover {
            border-color: var(--color-primary-border-soft, #e2b4b4);
            color: var(--color-primary, #6D0A0E);
        }

        .quotations-manager-page .quotation-stage-tab.is-active {
            border-color: var(--color-primary, #6D0A0E);
            color: var(--color-primary, #6D0A0E);
            background: var(--color-primary-soft, #f8ecec);
        }

        .quotations-manager-page .quotation-stage-tab__count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.35rem;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #475569;
        }

        .quotations-manager-page .quotation-stage-tab.is-active .quotation-stage-tab__count {
            background: rgba(109, 10, 14, 0.12);
            color: var(--color-primary, #6D0A0E);
        }

        .quotations-manager-page .quotation-type-chip,
        .quotations-manager-page .quotation-status-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .quotations-manager-page .quotation-type-chip--analysis {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .quotations-manager-page .quotation-type-chip--general {
            background: #ecfeff;
            color: #0e7490;
            border-color: #a5f3fc;
        }

        .quotations-manager-page .quotation-status-chip--prep {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }

        .quotations-manager-page .quotation-status-chip--complete {
            background: #ecfdf5;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .quotations-manager-page .quotation-actions-cell {
            gap: 0.45rem;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .quotations-manager-page .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 0;
            font-size: 12px;
            border: 1px solid transparent;
            background: #fff;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            box-shadow: none;
            cursor: pointer;
        }

        .quotations-manager-page .rm-act-btn--view {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .quotations-manager-page .rm-act-btn--view:hover {
            background: #dcfce7;
            border-color: #86efac;
            color: #166534;
            text-decoration: none;
        }

        .quotations-manager-page .rm-act-btn--edit {
            border-color: #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .quotations-manager-page .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e40af;
            text-decoration: none;
        }

        .quotations-manager-page .rm-act-btn--clone {
            border-color: #c4b5fd;
            color: #6d28d9;
            background: #f5f3ff;
        }

        .quotations-manager-page .rm-act-btn--clone:hover {
            background: #ede9fe;
            border-color: #a78bfa;
            color: #5b21b6;
            text-decoration: none;
        }

        .quotations-manager-page .rm-act-btn--enquiry {
            border-color: #fed7aa;
            color: #c2410c;
            background: #fff7ed;
        }

        .quotations-manager-page .rm-act-btn--enquiry:hover {
            background: #ffedd5;
            border-color: #fdba74;
            color: #9a3412;
        }
    </style>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-3" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-edit-outline text-primary"></i>
                                Quotations Management
                            </h2>
                            <p class="text-muted mb-0">View and manage customer quotations</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add-quotation">
                            <i class="mdi mdi-plus-circle-outline"></i> Create Quotation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="quotation-stage-tabs px-3 px-md-4 py-3">
                    <button type="button"
                            class="quotation-stage-tab {{ $stageFilter === '' ? 'is-active' : '' }}"
                            wire:click="$set('stageFilter', '')">
                        <i class="mdi mdi-view-list-outline"></i>
                        <span>All</span>
                        <span class="quotation-stage-tab__count">{{ $this->stageCounts['all'] }}</span>
                    </button>
                    <button type="button"
                            class="quotation-stage-tab {{ $stageFilter === 'Quote In Preparation' ? 'is-active' : '' }}"
                            wire:click="$set('stageFilter', 'Quote In Preparation')">
                        <i class="mdi mdi-clock-outline"></i>
                        <span>In Preparation</span>
                        <span class="quotation-stage-tab__count">{{ $this->stageCounts['Quote In Preparation'] }}</span>
                    </button>
                    <button type="button"
                            class="quotation-stage-tab {{ $stageFilter === 'Quote Complete' ? 'is-active' : '' }}"
                            wire:click="$set('stageFilter', 'Quote Complete')">
                        <i class="mdi mdi-check-circle-outline"></i>
                        <span>Complete</span>
                        <span class="quotation-stage-tab__count">{{ $this->stageCounts['Quote Complete'] }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                    <button type="button" wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                        <i class="mdi mdi-refresh"></i> Clear
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Quote number...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label">Customer</label>
                                <select wire:model.live="customerFilter" class="form-control">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label">Type</label>
                                <select wire:model.live="quotationTypeFilter" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="Analysis">Analysis</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" wire:model.live="startDate" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" wire:model.live="endDate" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($drafts && $drafts->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-color: #fcd34d;">
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

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="mdi mdi-table"></i> Quotations</h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="mb-0 mr-2 text-muted small">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body @if($this->quotations->count() > 0) pt-0 @endif">
                    @if($this->quotations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover ls-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 1%;">Actions</th>
                                        <th>Quote #</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Expiring</th>
                                        <th>Status</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->quotations as $quotation)
                                        <tr class="quotation-preview-hover-parent">
                                            <td>
                                                <div class="d-flex quotation-actions-cell">
                                                    <button type="button"
                                                            wire:click="viewQuotation({{ $quotation->id }})"
                                                            class="rm-act-btn rm-act-btn--view"
                                                            title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                    <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}"
                                                       class="rm-act-btn rm-act-btn--edit"
                                                       title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('quotation.preview', ['id' => $quotation->id]) }}"
                                                       class="rm-act-btn rm-act-btn--view quotation-preview-quote-btn"
                                                       title="Preview quotation document"
                                                       target="_blank">
                                                        <i class="mdi mdi-file-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                            wire:click="cloneQuotation({{ $quotation->id }})"
                                                            class="rm-act-btn rm-act-btn--clone"
                                                            title="Clone">
                                                        <i class="mdi mdi-content-duplicate"></i>
                                                    </button>
                                                    @can('laboratory.components.quotation.add')
                                                        @if($quotation->status === 'Quote Complete'
                                                            && $quotation->quotation_type === 'Analysis'
                                                            && $quotation->expiring_date
                                                            && \Carbon\Carbon::parse($quotation->expiring_date)->startOfDay()->gte(now()->startOfDay()))
                                                            <button type="button"
                                                                    wire:click="openCreateEnquiryModal(@js((string) $quotation->id))"
                                                                    wire:loading.attr="disabled"
                                                                    class="rm-act-btn rm-act-btn--enquiry"
                                                                    title="Create enquiry from quotation">
                                                                <i class="mdi mdi-flask-plus-outline"></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                            <td><strong>{{ $quotation->quote_number }}</strong></td>
                                            <td>{{ $quotation->customer }}</td>
                                            <td>
                                                <span class="quotation-type-chip {{ $quotation->quotation_type === 'Analysis' ? 'quotation-type-chip--analysis' : 'quotation-type-chip--general' }}">
                                                    {{ $quotation->quotation_type }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($quotation->quote_date)->format('Y-m-d') }}</td>
                                            <td>
                                                @if($quotation->expiring_date)
                                                    {{ \Carbon\Carbon::parse($quotation->expiring_date)->format('Y-m-d') }}
                                                    @if(\Carbon\Carbon::parse($quotation->expiring_date)->isPast())
                                                        <span class="badge badge-danger ml-1">Expired</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                <span class="quotation-status-chip {{ $quotation->status === 'Quote Complete' ? 'quotation-status-chip--complete' : 'quotation-status-chip--prep' }}">
                                                    {{ $quotation->status }}
                                                </span>
                                            </td>
                                            <td class="text-right"><strong>{{ number_format($quotation->total_amount ?? 0, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">
                                Showing {{ $this->quotations->firstItem() ?? 0 }} to {{ $this->quotations->lastItem() ?? 0 }} of {{ $this->quotations->total() }} entries
                            </span>
                            <div>{{ $this->quotations->links('pagination::bootstrap-4') }}</div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-file-document-edit-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No quotations found</h5>
                            <p class="text-muted mb-0">Create your first quotation to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showQuotationDetails && $this->selectedQuotation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-document-edit-outline"></i>
                            Quotation Details - {{ $this->selectedQuotation->quote_number }}
                        </h5>
                        <button type="button" class="btn-close ls-modal-close" wire:click="closeQuotationDetails" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Customer Information</h6>
                                <p class="mb-1"><strong>Customer:</strong> {{ $this->selectedQuotation->customer->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Contact:</strong>
                                    @if($this->selectedQuotation->contact)
                                        {{ $this->selectedQuotation->contact->first_name }} {{ $this->selectedQuotation->contact->last_name }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Quotation Information</h6>
                                <p class="mb-1"><strong>Quote Date:</strong> {{ $this->selectedQuotation->quote_date }}</p>
                                <p class="mb-1"><strong>Expiring Date:</strong> {{ $this->selectedQuotation->expiring_date ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Type:</strong> {{ $this->selectedQuotation->quotation_type }}</p>
                                <p class="mb-1"><strong>Status:</strong>
                                    <span class="badge bg-{{ $this->selectedQuotation->status === 'Quote Complete' ? 'success' : 'warning' }}" style="color: white;">
                                        {{ $this->selectedQuotation->status }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <h6 class="text-muted mb-3">Line Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered ls-table">
                                <thead style="background-color: rgba(0, 0, 0, .05);">
                                    <tr>
                                        <th>Item/Analysis</th>
                                        <th>Invoicable Item</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->selectedQuotation->details as $detail)
                                        <tr>
                                            <td>
                                                @if($this->selectedQuotation->quotation_type === 'Analysis')
                                                    @if($detail->sampletype)
                                                        <strong>{{ $detail->sampletype->name }}</strong>
                                                    @endif
                                                @else
                                                    <strong>{{ $detail->item_name }}</strong>
                                                    @if($detail->description)
                                                        <br><small class="text-muted">{{ $detail->description }}</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($detail->invoicableItem)
                                                    {{ $detail->invoicableItem->item_code }} - {{ $detail->invoicableItem->item_name }}
                                                @else
                                                    <span class="text-muted">Not mapped</span>
                                                @endif
                                            </td>
                                            <td>{{ $detail->quantity }}</td>
                                            <td>{{ number_format($detail->unit_price, 2) }}</td>
                                            <td>{{ $detail->tax }}%</td>
                                            <td><strong>{{ number_format($detail->quantity * $detail->unit_price * (1 + $detail->tax/100), 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th colspan="5" class="text-end">Subtotal:</th>
                                        <th>{{ number_format($this->selectedQuotation->sub_total ?? 0, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Tax:</th>
                                        <th>{{ number_format($this->selectedQuotation->tax ?? 0, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Total:</th>
                                        <th>{{ number_format($this->selectedQuotation->total_amount ?? 0, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer" style="gap: 8px;">
                        <button type="button" class="btn btn-secondary" wire:click="closeQuotationDetails">Close</button>
                        <a href="{{ route('quotation.preview', ['id' => $this->selectedQuotation->id]) }}"
                           class="btn quotation-preview-quote-btn"
                           target="_blank">
                            <i class="mdi mdi-file-eye"></i> Preview Quote
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showCreateEnquiryModal && $this->enquirySourceQuotation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">
                                <i class="mdi mdi-flask-plus-outline text-primary"></i>
                                Create Enquiry from {{ $this->enquirySourceQuotation->quote_number }}
                            </h5>
                            <small class="text-muted">
                                {{ $this->enquirySourceQuotation->customer?->name ?? 'Customer' }}
                                · {{ $this->enquirySourceQuotation->details->count() }} quotation line(s)
                            </small>
                        </div>
                        <button type="button" class="btn-close ls-modal-close" wire:click="closeCreateEnquiryModal" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <form wire:submit="createEnquiryFromQuotation">
                        <div class="modal-body">
                            <div class="alert alert-info d-flex align-items-start" style="gap: 10px;">
                                <i class="mdi mdi-auto-fix mt-1"></i>
                                <div>
                                    Tests, parameters, pricing and a Test Request Form will be prefilled.
                                    Confirm the physical sample count and add any customer reference available.
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="lw-enquiry-creation-intent">Workflow start</label>
                                <select id="lw-enquiry-creation-intent" wire:model.live="enquiryForm.creation_intent" class="form-control">
                                    <option value="prepare">Prepare for sending</option>
                                    <option value="already_sent">Quotation already sent</option>
                                    <option value="accepted">Customer already accepted</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="enquiry-number-of-samples">Physical samples <span class="text-danger">*</span></label>
                                        <input id="enquiry-number-of-samples"
                                               type="number"
                                               min="1"
                                               max="10000"
                                               wire:model="enquiryForm.number_of_samples"
                                               class="form-control @error('enquiryForm.number_of_samples') is-invalid @enderror">
                                        @error('enquiryForm.number_of_samples')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="enquiry-reference-number">Customer reference</label>
                                        <input id="enquiry-reference-number"
                                               type="text"
                                               wire:model="enquiryForm.reference_number"
                                               class="form-control @error('enquiryForm.reference_number') is-invalid @enderror"
                                               placeholder="Optional">
                                        @error('enquiryForm.reference_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="enquiry-date-expected">Expected sample date</label>
                                        <input id="enquiry-date-expected"
                                               type="date"
                                               wire:model="enquiryForm.date_expected"
                                               class="form-control @error('enquiryForm.date_expected') is-invalid @enderror">
                                        @error('enquiryForm.date_expected')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if(($enquiryForm['creation_intent'] ?? '') === 'accepted')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Customer PO</label>
                                            <input type="text" wire:model="enquiryForm.client_po_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mt-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="lw-enquiry-po-skipped" wire:model="enquiryForm.po_skipped">
                                                <label class="custom-control-label" for="lw-enquiry-po-skipped">Skip PO</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="enquiry-sample-description">Sample description</label>
                                <textarea id="enquiry-sample-description"
                                          rows="2"
                                          wire:model="enquiryForm.sample_description"
                                          class="form-control @error('enquiryForm.sample_description') is-invalid @enderror"
                                          placeholder="Optional description shared by the quoted samples"></textarea>
                                @error('enquiryForm.sample_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-0">
                                <label for="enquiry-notes">Internal enquiry notes</label>
                                <textarea id="enquiry-notes"
                                          rows="2"
                                          wire:model="enquiryForm.enquiry_notes"
                                          class="form-control @error('enquiryForm.enquiry_notes') is-invalid @enderror"
                                          placeholder="Optional"></textarea>
                                @error('enquiryForm.enquiry_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer" style="gap: 8px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeCreateEnquiryModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="createEnquiryFromQuotation">
                                <span wire:loading.remove wire:target="createEnquiryFromQuotation">
                                    <i class="mdi mdi-auto-fix"></i> Create &amp; Prefill Enquiry
                                </span>
                                <span wire:loading wire:target="createEnquiryFromQuotation">
                                    <span class="spinner-border spinner-border-sm mr-1"></span> Creating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')

    <style>
    .modal.show {
        display: block !important;
    }
    </style>
</div>
