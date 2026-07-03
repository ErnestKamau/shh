<div class="quotations-manager-page">
    <style>
        .quotations-manager-page .workflow-status-chip {
            border-color: color-mix(in srgb, var(--chip-accent, #64748b) 30%, #e2e8f0);
            background: color-mix(in srgb, var(--chip-accent, #64748b) 10%, #ffffff);
            color: var(--chip-accent, #475569);
        }
    </style>
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-3" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row workflow-board-header mb-3">
        <div class="col-12">
            <div class="batch-header-bar">
                <div class="batch-header-top">
                    <div class="batch-title-group">
                        <i class="mdi mdi-file-document-edit-outline" style="font-size:1.2rem;"></i>
                        <span class="batch-code-label">Quotations Management</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary btn-action-sm" data-toggle="modal" data-target="#add-quotation">
                        <i class="mdi mdi-plus-circle-outline"></i> Create Quotation
                    </button>
                </div>
                <p class="text-muted small mb-0" style="color: rgba(255,255,255,0.82) !important;">View and manage customer quotations</p>
            </div>
        </div>
    </div>

    <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header">
            <h6><i class="mdi mdi-filter-variant"></i> Filter Options</h6>
            <button type="button" wire:click="clearFilters" class="btn btn-sm btn-light">
                <i class="mdi mdi-refresh"></i> Clear
            </button>
        </div>
        <div class="workflow-board-panel-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="control-label">Search</label>
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Quote number...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="control-label">Customer</label>
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
                        <label class="control-label">Stage</label>
                        <select wire:model.live="stageFilter" class="form-control">
                            <option value="">All Stages</option>
                            @foreach($quotationStages as $stage)
                                <option value="{{ $stage }}">{{ $stage }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="control-label">Type</label>
                        <select wire:model.live="quotationTypeFilter" class="form-control">
                            <option value="">All Types</option>
                            <option value="Analysis">Analysis</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="control-label">Start Date</label>
                        <input type="date" wire:model.live="startDate" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="control-label">End Date</label>
                        <input type="date" wire:model.live="endDate" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($drafts && $drafts->count() > 0)
        <div class="workflow-board-panel mb-3" style="border-color: #fcd34d;">
            <div class="workflow-board-panel-header" style="background: #fffbeb;">
                <h6 class="text-warning mb-0">
                    <i class="mdi mdi-file-edit"></i> Draft Quotations ({{ $drafts->count() }})
                </h6>
            </div>
            <div class="workflow-board-panel-body p-0">
                <div class="table-responsive">
                    <table class="table workflow-table table-hover mb-0">
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
    @endif

    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h6><i class="mdi mdi-table"></i> Quotations</h6>
            <div class="d-flex align-items-center">
                <label for="perPage" class="mb-0 mr-2 text-muted small">Show:</label>
                <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width: auto;">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="workflow-board-panel-body @if($this->quotations->count() > 0) p-0 @endif">
            @if($this->quotations->count() > 0)
                <div class="table-responsive">
                    <table class="table workflow-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Quote #</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Expiring</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->quotations as $quotation)
                                <tr class="quotation-preview-hover-parent">
                                    <td><strong>{{ $quotation->quote_number }}</strong></td>
                                    <td>{{ $quotation->customer }}</td>
                                    <td>
                                        <span class="workflow-status-chip" style="--chip-accent: {{ $quotation->quotation_type === 'Analysis' ? '#2563eb' : '#0891b2' }};">
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
                                        <span class="workflow-status-chip" style="--chip-accent: {{ $quotation->status === 'Quote Complete' ? '#15803d' : '#b45309' }};">
                                            {{ $quotation->status }}
                                        </span>
                                    </td>
                                    <td><strong>{{ number_format($quotation->total_amount ?? 0, 2) }}</strong></td>
                                    <td>
                                        <div class="d-flex flex-wrap" style="gap: 4px;">
                                            <button wire:click="viewQuotation({{ $quotation->id }})"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="View Details">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                            <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <a href="{{ route('quotation.preview', ['id' => $quotation->id]) }}"
                                               class="btn btn-sm btn-outline-secondary quotation-preview-quote-btn"
                                               title="Preview quotation document"
                                               target="_blank">
                                                <i class="mdi mdi-file-eye"></i>
                                            </a>
                                            <button wire:click="cloneQuotation({{ $quotation->id }})"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="Clone">
                                                <i class="mdi mdi-content-duplicate"></i>
                                            </button>
                                        </div>
                                    </td>
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

    <!-- Quotation Details Modal -->
    @if($showQuotationDetails && $this->selectedQuotation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-document-edit-outline"></i>
                            Quotation Details - {{ $this->selectedQuotation->quote_number }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeQuotationDetails"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Quotation Header Info -->
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

                        <!-- Quotation Line Items -->
                        <h6 class="text-muted mb-3">Line Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
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
                    <div class="modal-footer">
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

    @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')

    <style>
    .modal.show {
        display: block !important;
    }
    </style>
</div>
