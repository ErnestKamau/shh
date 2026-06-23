<div class="amspec-terms-of-sale" style="margin-top: 18px; font-size: 11px;">
    <p class="amspec-terms-title" style="font-weight: 700; margin-bottom: 8px;">Terms of Sale</p>
    <p style="margin-bottom: 8px;">
        @if(! empty($termsOfSale['prices']))
            <strong>Prices:</strong> {{ $termsOfSale['prices'] }} {{ $currencyCode }}<br>
        @endif
        <strong>Service Delivery:</strong> {{ $termsOfSale['service_delivery'] }}<br>
        <strong>Payments:</strong> {{ $termsOfSale['payments'] }}<br>
        <strong>Quote Specification:</strong> {{ $termsOfSale['quote_specification'] }}
    </p>

    <p class="amspec-terms-title" style="font-weight: 700; margin-bottom: 8px;">Additional Information</p>
    <p style="margin-bottom: 8px;">
        {{ $termsOfSale['additional_info'] }}<br>
        <strong>{{ $termsOfSale['payment_info'] }}</strong>
    </p>

    @if(! empty(array_filter($bankDetails)))
        <div class="amspec-bank-details text-center" style="margin-top: 12px;">
            <p style="margin-bottom: 4px;">
                Cheques made payable to <strong>{{ $company->name ?? '' }}</strong>
            </p>
            <p style="margin-bottom: 4px;">
                <strong>Bank Details:</strong>
                Bank Name: <strong>{{ $bankDetails['bank_name'] ?? '' }}</strong>
                Account No: <strong>{{ $bankDetails['account_no'] ?? '' }}</strong>
                Swift Code: <strong>{{ $bankDetails['swift_code'] ?? '' }}</strong>
            </p>
            <p style="margin-bottom: 4px;">
                Bank Code: <strong>{{ $bankDetails['bank_code'] ?? '' }}</strong>
                Branch Code: <strong>{{ $bankDetails['branch_code'] ?? '' }}</strong>
            </p>
            <p>
                <strong>Mobile Remittance:</strong>
                Mpesa Paybill: <strong>{{ $bankDetails['paybill'] ?? '' }}</strong>
                Account Name: <strong>{{ $bankDetails['account'] ?? '' }}</strong>
            </p>
        </div>
    @endif
</div>
