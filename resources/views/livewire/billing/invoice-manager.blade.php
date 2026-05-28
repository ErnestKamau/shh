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

    <!-- Payment status tabs -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 invoice-list-tabs-card">
                <div class="invoice-list-tabs px-3 px-md-4 py-3">
                    <button type="button"
                            wire:click="setPaymentStatusTab('all')"
                            class="invoice-list-tab {{ $paymentStatusTab === 'all' ? 'active' : '' }}">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span>All</span>
                        <span class="invoice-list-tab__count">{{ $paymentTabCounts['all'] }}</span>
                    </button>
                    <button type="button"
                            wire:click="setPaymentStatusTab('unpaid')"
                            class="invoice-list-tab invoice-list-tab--unpaid {{ $paymentStatusTab === 'unpaid' ? 'active' : '' }}">
                        <i class="mdi mdi-clock-alert-outline"></i>
                        <span>Unpaid</span>
                        <span class="invoice-list-tab__count">{{ $paymentTabCounts['unpaid'] }}</span>
                    </button>
                    <button type="button"
                            wire:click="setPaymentStatusTab('partially_paid')"
                            class="invoice-list-tab invoice-list-tab--partial {{ $paymentStatusTab === 'partially_paid' ? 'active' : '' }}">
                        <i class="mdi mdi-progress-clock"></i>
                        <span>Partially Paid</span>
                        <span class="invoice-list-tab__count">{{ $paymentTabCounts['partially_paid'] }}</span>
                    </button>
                    <button type="button"
                            wire:click="setPaymentStatusTab('completely_paid')"
                            class="invoice-list-tab invoice-list-tab--paid {{ $paymentStatusTab === 'completely_paid' ? 'active' : '' }}">
                        <i class="mdi mdi-check-circle-outline"></i>
                        <span>Completely Paid</span>
                        <span class="invoice-list-tab__count">{{ $paymentTabCounts['completely_paid'] }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 draft-invoices-card">
                <div class="card-header d-flex justify-content-between align-items-center border-0">
                    <h5 class="card-title mb-0">
                        @if($paymentStatusTab === 'unpaid')
                            Unpaid Invoices
                        @elseif($paymentStatusTab === 'partially_paid')
                            Partially Paid Invoices
                        @elseif($paymentStatusTab === 'completely_paid')
                            Completely Paid Invoices
                        @else
                            Draft Invoices
                        @endif
                    </h5>
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
                            <table id="draft-invoices-table" class="table table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>Actions</th>
                                        <th>Draft Invoice #</th>
                                        <th>Customer</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Currency</th>
                                        <th>Total</th>
                                        <th>Tax</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->invoices as $invoice)
                                        @php
                                            $invoiceTotal = (float) ($invoice->line_items_total ?? $invoice->invoicetotal);
                                            $invoiceTax = (float) ($invoice->line_items_tax ?? $invoice->total_tax);
                                            $paidTotal = (float) ($invoice->paid_total ?? 0);
                                            if ($paidTotal <= 0) {
                                                $paymentLabel = 'Unpaid';
                                                $paymentChipClass = 'payment-chip--unpaid';
                                            } elseif ($paidTotal >= $invoiceTotal && $invoiceTotal > 0) {
                                                $paymentLabel = 'Completely Paid';
                                                $paymentChipClass = 'payment-chip--paid';
                                            } elseif ($paidTotal > 0) {
                                                $paymentLabel = 'Partially Paid';
                                                $paymentChipClass = 'payment-chip--partial';
                                            } else {
                                                $paymentLabel = 'Unpaid';
                                                $paymentChipClass = 'payment-chip--unpaid';
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a href="{{ route('billing.invoices.show', $invoice->id) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                       title="View">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('print-invoice', ['id' => $invoice->id]) }}"
                                                       class="btn btn-sm rm-act-btn rm-act-btn--muted"
                                                       title="Print"
                                                       target="_blank">
                                                        <i class="mdi mdi-printer"></i>
                                                    </a>
                                                </div>
                                            </td>
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
                                                {{ $invoice->currency_label }}
                                            </td>
                                            <td>
                                                <strong>{{ number_format($invoiceTotal, 2) }}</strong>
                                            </td>
                                            <td>
                                                {{ number_format($invoiceTax, 2) }}
                                            </td>
                                            <td>
                                                <span class="payment-chip {{ $paymentChipClass }}">{{ $paymentLabel }}</span>
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

    <style>
    .invoice-list-tabs-card {
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .invoice-list-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .invoice-list-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .invoice-list-tab i {
        font-size: 18px;
        opacity: 0.85;
    }

    .invoice-list-tab:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #334155;
    }

    .invoice-list-tab.active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #1d4ed8;
        color: #fff;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.28);
    }

    .invoice-list-tab.active i {
        opacity: 1;
    }

    .invoice-list-tab--unpaid.active {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-color: #d97706;
        box-shadow: 0 8px 20px rgba(217, 119, 6, 0.28);
    }

    .invoice-list-tab--partial.active {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border-color: #0284c7;
        box-shadow: 0 8px 20px rgba(2, 132, 199, 0.28);
    }

    .invoice-list-tab--paid.active {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        border-color: #16a34a;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.28);
    }

    .invoice-list-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 7px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.08);
        font-size: 11px;
        font-weight: 700;
    }

    .invoice-list-tab.active .invoice-list-tab__count {
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
    }

    .payment-chip {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .payment-chip--unpaid {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .payment-chip--partial {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    .payment-chip--paid {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .draft-invoices-card {
        border-radius: 8px;
        overflow: hidden;
    }

    #draft-invoices-table .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        font-size: 12px;
    }

    #draft-invoices-table .rm-act-btn--view {
        border: 1px solid #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    #draft-invoices-table .rm-act-btn--view:hover {
        background: #dcfce7;
        border-color: #86efac;
    }

    #draft-invoices-table .rm-act-btn--muted {
        border: 1px solid #e2e8f0;
        color: #475569;
        background: #f8fafc;
    }

    #draft-invoices-table .rm-act-btn--muted:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
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

    @media (max-width: 767.98px) {
        .invoice-list-tabs {
            flex-direction: column;
        }

        .invoice-list-tab {
            width: 100%;
            justify-content: flex-start;
        }
    }
    </style>
</div>
