@php
    $isBrazilQuotation = (bool) ($isBrazilQuotation ?? false);
    $showPrices = array_key_exists('show_prices', $termsOfSale ?? [])
        ? (bool) $termsOfSale['show_prices']
        : ! $isBrazilQuotation;
    $deliveryOfResults = trim((string) ($termsOfSale['delivery_of_results'] ?? $termsOfSale['service_delivery'] ?? ''));
    $confidentiality = trim((string) ($termsOfSale['confidentiality'] ?? ''));
@endphp
<div class="amspec-terms-of-sale" style="margin-top: 18px; font-size: 11px;">
    <p class="amspec-terms-title" style="font-weight: 700; margin-bottom: 8px;">Terms of Sale</p>
    <p style="margin-bottom: 8px;">
        @if($showPrices && ! empty($termsOfSale['prices']))
            <strong>Prices:</strong> {{ $termsOfSale['prices'] }} {{ $currencyCode }}<br>
        @endif
        <strong>Delivery of results:</strong> {{ $termsOfSale['service_delivery'] ?? $deliveryOfResults }}<br>
        <strong>Payments:</strong> {{ $termsOfSale['payments'] }}<br>
        <strong>Proposal Acceptance:</strong> {{ $termsOfSale['quote_specification'] }}
    </p>

    <p class="amspec-terms-title" style="font-weight: 700; margin-bottom: 8px;">Additional Information</p>
    <p style="margin-bottom: 8px;">
        @if($isBrazilQuotation)
            {!! nl2br(e(trim((string) ($termsOfSale['payment_info'] ?? '')))) !!}
            @if(filled(trim((string) ($termsOfSale['additional_info'] ?? ''))))
                <br><br>
                <strong>Technical questions / Inquiries / Complaints:</strong><br>
                {!! nl2br(e($termsOfSale['additional_info'])) !!}
            @endif
        @else
            <strong>Technical questions / Inquiries / Complaints:</strong><br>
            {!! nl2br(e($termsOfSale['additional_info'] ?? '')) !!}<br>
            <strong>Information for Purchase Order / Sample Shipment:</strong><br>
            {!! nl2br(e($termsOfSale['payment_info'] ?? '')) !!}
        @endif
    </p>

    @if($isBrazilQuotation)
        <p style="margin-bottom: 8px;">
            <strong>Delivery of results:</strong> {{ $deliveryOfResults !== '' ? $deliveryOfResults : 'Results are made available by e-mail/portal to the contact provided by the applicant in the registration form.' }}
        </p>
        <p style="margin-bottom: 8px;">
            <strong>Confidentiality:</strong> {{ $confidentiality !== '' ? $confidentiality : 'All customer information obtained is treated by Amspec Lab as confidential and is not shared.' }}
        </p>
    @endif

    @if(! empty(array_filter($bankDetails ?? [])))
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
