<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="mdi mdi-account-cash-outline"></i> Payment Details
            </h5>
            <div class="d-flex align-items-center">
                <button type="button" 
                        class="btn btn-primary btn-sm text-nowrap" 
                        data-action="add" 
                        data-target="#add-payment-details" 
                        data-toggle="modal"
                        style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px; margin-right: 15px;">
                    <i class="mdi mdi-plus"></i> Add Payment
                </button>
                <div class="d-flex align-items-center ml-2 border-left pl-3">
                    <label for="perPage" class="form-label mb-0 mr-2 text-muted small text-nowrap">Show:</label>
                    <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm d-inline-block" style="width: 70px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live="search" 
                       class="form-control" 
                       placeholder="Search by payment method, reference number, contact person, or receiver...">
            </div>

            @if($paymentDetails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" style="width: 100%;">
                        <thead style="background-color: rgba(0, 0, 0, .03);" class="p-2">
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
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
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
                                <td colspan="8" class="text-center py-5">
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
