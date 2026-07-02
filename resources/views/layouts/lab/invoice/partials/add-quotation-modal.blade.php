@php
    $customers = $customers ?? collect();
    $currencyById = \App\Models\Currency::query()->get()->keyBy('id');
@endphp
@include('layouts.lab.invoice.partials.add-quotation-modal-styles')
<div class="modal fade" id="add-quotation" role="dialog" aria-labelledby="add-quotation-title">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('add-quotation-header') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header quotation-modal-header">
                <div>
                    <h4 class="modal-title" id="add-quotation-title">
                        <i class="mdi mdi-file-document-plus-outline"></i> New Quotation
                    </h4>
                    <p class="modal-subtitle">Select client, contact, and quotation type to continue.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body quotation-modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group mb-3">
                            <label class="soft-label" for="select-client">Client <span class="text-danger">*</span></label>
                            <select name="client" class="form-control modern-select no-select2" id="select-client" required>
                                <option value="" disabled>Choose Client...</option>
                                @foreach ($customers as $customer)
                                    @php
                                        $zohoId = $customer->zoho_customer_id;
                                        if (is_array($zohoId)) {
                                            $zohoId = $zohoId[0] ?? '';
                                        } elseif (is_string($zohoId) && str_starts_with(trim($zohoId), '[')) {
                                            $decoded = json_decode($zohoId, true);
                                            $zohoId = is_array($decoded) ? ($decoded[0] ?? '') : $zohoId;
                                        }
                                        $custCurrency = $customer->currency_id ? ($currencyById[$customer->currency_id] ?? null) : null;
                                    @endphp
                                    <option value="{{ $customer->id }}"
                                        data-zoho-customer-id="{{ $zohoId }}"
                                        data-currency-id="{{ $customer->currency_id ?? '' }}"
                                        data-currency-code="{{ $custCurrency?->code ?? '' }}"
                                        data-currency-description="{{ $custCurrency?->description ?? '' }}">
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-3">
                            <label class="soft-label" for="select-quotation-type">Quotation Type <span class="text-danger">*</span></label>
                            <select name="quotation_type" id="select-quotation-type" class="form-control modern-select no-select2" required>
                                <option value="Analysis" selected>Analysis Quotation</option>
                                <option value="General">General Quotation</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3 contacts">
                    <label class="soft-label" for="select-client-contact">Client Contact <span class="text-danger">*</span></label>
                    <select name="client_contact" id="select-client-contact" class="form-control modern-select no-select2" required>
                        <option value="" disabled selected>Select client first...</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="soft-label" for="quotation-date">Quotation Date <span class="text-danger">*</span></label>
                            <input type="date" name="quotation_date" id="quotation-date" class="form-control modern-input" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="soft-label" for="expire-date">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="expire_date" id="expire-date" class="form-control modern-input" value="{{ now()->addDays(30)->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="soft-label" for="select-zoho-customer">Dynamics Customer <small class="text-muted">(Optional)</small></label>
                    <select name="zoho_customer_id" id="select-zoho-customer" class="form-control modern-select no-select2">
                        <option value="">Select Dynamics Customer...</option>
                        @foreach (\App\ZohoCustomers::where('status', 'Active')->orderBy('name')->get() as $zc)
                            <option value="{{ $zc->id }}" data-currency-code="{{ $zc->currency_code }}">{{ $zc->name }} ({{ $zc->customer_no }})</option>
                        @endforeach
                    </select>
                    <small class="form-text text-info mt-1" id="zoho-customer-info" style="display: none;">
                        <i class="mdi mdi-information-outline"></i> Links Dynamics customer and may override currency.
                    </small>
                </div>

                <div class="form-group mb-0">
                    <label class="soft-label" for="display-currency">Currency</label>
                    <input type="text" id="display-currency" class="form-control modern-input" readonly placeholder="Auto-populated from client or Dynamics customer...">
                    <input type="hidden" name="currency_id" id="currency-id" required>
                    <small class="form-text text-muted">Set from the CRM client by default, or from the Dynamics customer when selected.</small>
                </div>
            </div>

            <div class="modal-footer quotation-modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-quotation-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-quotation-primary">
                    <i class="mdi mdi-arrow-right"></i> Continue
                </button>
            </div>
        </form>
    </div>
</div>
