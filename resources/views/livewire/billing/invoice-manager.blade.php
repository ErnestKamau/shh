<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-outline text-primary"></i>
                                Draft Invoices Management
                            </h2>
                            <p class="text-muted mb-0">View and manage customer draft invoices</p>
                        </div>
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Invoice number or reference...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Customer</label>
                                <select wire:model.live="customerFilter" class="form-select modern-select">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Currency</label>
                                <select wire:model.live="currencyFilter" class="form-select modern-select">
                                    <option value="">All</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Date Filter</label>
                                <select wire:model.live="dateFilter" class="form-select modern-select">
                                    <option value="created_at">Invoice Date</option>
                                    <option value="due_date">Due Date</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" wire:model.live="startDate" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" wire:model.live="endDate" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Draft Invoices</h5>
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($this->invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Draft Invoice #</th>
                                        <th>Customer</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Currency</th>
                                        <th>Total</th>
                                        <th>Tax</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->invoices as $invoice)
                                        <tr>
                                            <td>
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                            </td>
                                            <td>
                                                {{ $invoice->crmCustomer->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                {{ $invoice->reference_number ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $invoice->created_at->format('Y-m-d') }}
                                            </td>
                                            <td>
                                                @if($invoice->due_date)
                                                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}
                                                    @if(\Carbon\Carbon::parse($invoice->due_date)->isPast())
                                                        <span class="badge bg-danger ms-1" style="color: white;">Overdue</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ $invoice->currencyinfo->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <strong>{{ number_format($invoice->total, 2) }}</strong>
                                            </td>
                                            <td>
                                                {{ number_format($invoice->total_tax, 2) }}
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button wire:click="viewInvoice({{ $invoice->id }})" 
                                                            class="btn btn-sm btn-outline-primary mr-1" 
                                                            title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </button>
                                                    <a href="{{ route('print-invoice', ['id' => $invoice->id]) }}" 
                                                       class="btn btn-sm btn-outline-secondary mr-1" 
                                                       title="Print"
                                                       target="_blank">
                                                        <i class="mdi mdi-printer"></i>
                                                    </a>
                                                    <a href="{{ route('invoice-sample-header', ['id' => $invoice->id]) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="View Full Details">
                                                        <i class="mdi mdi-open-in-new"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $this->invoices->firstItem() ?? 0 }} to {{ $this->invoices->lastItem() ?? 0 }} of {{ $this->invoices->total() }} entries
                                </span>
                            </div>
                            <div>
                                {{ $this->invoices->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-file-document-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Draft Invoice Found</h5>
                            <p class="text-muted">Adjust your filters or generate draft invoice from sample batches.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Details Modal -->
    @if($showInvoiceDetails && $this->selectedInvoice)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-document-outline"></i>
                            Invoice Details - {{ $this->selectedInvoice->invoice_number }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeInvoiceDetails"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Invoice Header Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Customer Information</h6>
                                <p class="mb-1"><strong>Customer:</strong> {{ $this->selectedInvoice->crmCustomer->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Reference:</strong> {{ $this->selectedInvoice->reference_number ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Invoice Information</h6>
                                <p class="mb-1"><strong>Invoice Date:</strong> {{ $this->selectedInvoice->created_at->format('Y-m-d') }}</p>
                                <p class="mb-1"><strong>Due Date:</strong> {{ $this->selectedInvoice->due_date ? \Carbon\Carbon::parse($this->selectedInvoice->due_date)->format('Y-m-d') : 'N/A' }}</p>
                                <p class="mb-1"><strong>Currency:</strong> {{ $this->selectedInvoice->currencyinfo->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Invoice Line Items -->
                        <h6 class="text-muted mb-3">Line Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead style="background-color: rgba(0, 0, 0, .05);">
                                    <tr>
                                        <th>Analysis Type</th>
                                        <th>Invoicable Item</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Tax Rate</th>
                                        <th>Tax Amount</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->selectedInvoice->details as $detail)
                                        <tr>
                                            <td>
                                                <strong>{{ $detail->analysis_type_name }}</strong>
                                                @if($detail->analysisType)
                                                    <br><small class="text-muted">{{ $detail->analysisType->code }}</small>
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
                                            <td>{{ number_format($detail->selling_price, 2) }}</td>
                                            <td>{{ $detail->tax_rate }}%</td>
                                            <td>{{ number_format($detail->tax_amount, 2) }}</td>
                                            <td><strong>{{ number_format($detail->total, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th colspan="6" class="text-end">Subtotal:</th>
                                        <th>{{ number_format($this->selectedInvoice->total - $this->selectedInvoice->total_tax, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="6" class="text-end">Tax:</th>
                                        <th>{{ number_format($this->selectedInvoice->total_tax, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="6" class="text-end">Total:</th>
                                        <th>{{ number_format($this->selectedInvoice->total, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeInvoiceDetails">Close</button>
                        <a href="{{ route('print-invoice', ['id' => $this->selectedInvoice->id]) }}" 
                           class="btn btn-primary" 
                           target="_blank">
                            <i class="mdi mdi-printer"></i> Print Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .modal.show {
        display: block !important;
    }
    
    /* Modern Select Styling */
    .modern-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .modern-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        background-color: #ffffff;
        outline: none;
    }
    </style>
</div>
