<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5>
                <i class="mdi mdi-account-cash-outline"></i> Payment details
            </h5>
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <button type="button"
                        class="btn btn-primary btn-sm btn-action-sm text-nowrap"
                        data-action="add"
                        data-target="#add-payment-details"
                        data-toggle="modal">
                    <i class="mdi mdi-plus"></i> Add payment
                </button>
                @if($batch->status == 'Samples In Lab' && ($batch->invoice_id == 0 || $batch->invoice_id == null))
                <a href="{{ route('billing.sales-order.create', ['batches' => [$batch->batch_code]]) }}" class="btn btn-success btn-sm btn-action-sm text-nowrap">
                    <i class="mdi mdi-check-decagram mr-1"></i> Generate Sales Order
                </a>
                @endif
                <div class="d-flex align-items-center border-left pl-3 ml-1">
                    <label for="perPage" class="form-label mb-0 mr-2 text-muted small text-nowrap">Show</label>
                    <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm d-inline-block" style="width: 70px; border-radius: 6px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            @if($batch->invoice_id > 0 && $batch->invoice)
            <!-- Invoice Info -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="alert alert-light border d-flex justify-content-between align-items-center p-3" style="background-color: #f8fafc; border-radius: 12px; border-left: 4px solid #4CAF50 !important;">
                        <div>
                            <h6 class="mb-1 text-success">
                                <i class="mdi mdi-receipt"></i> Sales Order Generated
                            </h6>
                            <p class="mb-0 text-muted">
                                <strong>#{{ $batch->invoice->invoice_number }}</strong> | 
                                Total: <strong>{{ number_format($batch->invoice->invoice_total, 2) }} {{ $batch->invoice->currencyinfo->code ?? '' }}</strong> |
                                Date: {{ $batch->invoice->created_at->format('d M Y') }}
                            </p>
                        </div>
                        <a href="{{ route('invoice-sample-header', ['id' => $batch->invoice_id]) }}" class="btn btn-outline-success btn-sm">
                            <i class="mdi mdi-eye"></i> View Sales Order
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live="search" 
                       class="form-control" 
                       placeholder="Search by payment method, reference number, contact person, or receiver...">
            </div>

            @if($paymentDetails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover workflow-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th nowrap>Payment Method</th>
                                <th>Amount</th>
                                <th>Reference No</th>
                                <th>VAT</th>
                                <th>Balance</th>
                                <th>Contact Person</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentDetails as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payment->payment_method }}</td>
                                <td>{{ number_format($payment->amount ?? 0, 2) }}</td>
                                <td>{{ $payment->ref_no }}</td>
                                <td>{{ number_format($payment->vat_amount ?? 0, 2) }}</td>
                                <td>{{ number_format($payment->balance ?? 0, 2) }}</td>
                                <td>{{ $payment->contact_person_name }}</td>
                                <td>{{ $payment->receivername }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="text-muted">
                            Showing {{ $paymentDetails->firstItem() ?? 0 }} to {{ $paymentDetails->lastItem() ?? 0 }} of {{ $paymentDetails->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $paymentDetails->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th nowrap>Payment Method</th>
                                <th>Amount</th>
                                <th>Reference No</th>
                                <th>VAT</th>
                                <th>Balance</th>
                                <th>Contact Person</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center py-5 workflow-empty-state">
                                    <i class="mdi mdi-cash-multiple text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Payment Details Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No payment details match your search criteria
                                        @else
                                            There are no payment details to display
                                        @endif
                                    </small></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
