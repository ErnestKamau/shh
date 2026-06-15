@php
    $customers = $customers ?? collect();
    $currencyById = \App\Models\Currency::query()->get()->keyBy('id');
@endphp
<div class="modal fade" id="add-quotation" role="dialog" aria-labelledby="add-quotation-title">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('add-quotation-header') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title" id="add-quotation-title">
                    <i class="mdi mdi-plus"></i> Quotation
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <div class="form-group">
                        <label class="control-label" for="select-client">Client</label>
                        <select name="client" class="form-control quotation-modal-select no-select2" id="select-client" required>
                            <option value="" disabled selected>Choose Client...</option>
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

                    <div class="form-group contacts">
                        <label class="control-label" for="select-client-contact">Client Contact</label>
                        <select name="client_contact" id="select-client-contact" class="form-control quotation-modal-select no-select2" required>
                            <option value="" disabled selected>Select Client Contact</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="select-zoho-customer">Dynamics Customer <small class="text-muted">(Optional)</small></label>
                        <select name="zoho_customer_id" id="select-zoho-customer" class="form-control quotation-modal-select no-select2">
                            <option value="">Select Dynamics Customer...</option>
                            @foreach (\App\ZohoCustomers::where('status', 'Active')->orderBy('name')->get() as $zc)
                                <option value="{{ $zc->id }}" data-currency-code="{{ $zc->currency_code }}">{{ $zc->name }} ({{ $zc->customer_no }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-info" id="zoho-customer-info" style="display: none;">
                            <i class="mdi mdi-information"></i> This will link the Dynamics customer to the CRM customer and auto-populate the currency.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="display-currency">Currency</label>
                        <input type="text" id="display-currency" class="form-control" readonly placeholder="Auto-populated from client or Dynamics customer..." style="background-color: #f5f5f5;">
                        <input type="hidden" name="currency_id" id="currency-id" required>
                        <small class="form-text text-muted">Set from the CRM client by default, or from the Dynamics customer when selected.</small>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="selecy-quotation-type">Quotation Type</label>
                        <select name="quotation_type" id="selecy-quotation-type" class="form-control quotation-modal-select no-select2" required>
                            <option value="">Choose Quotation Type</option>
                            <option value="General">General Quotation</option>
                            <option value="Analysis" selected>Analysis Quotation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="quotation-date">Quotation Date</label>
                        <input type="date" name="quotation_date" id="quotation-date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="expire-date">Expiry Date</label>
                        <input type="date" name="expire_date" id="expire-date" class="form-control" value="{{ now()->addDays(30)->format('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            <div class="footers pt-3 p-2 bg-light" style="height:70px">
                <button type="submit" class="btn btn-outline-primary float-right"><i class="mdi mdi-content-save"></i> Next</button>
                <button type="button" class="btn btn-outline-danger float-left" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
