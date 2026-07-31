@php
    $hasCustomerAcceptanceSignature = ! empty($reportHeader->customer_acceptance_signature);
    $customerSignedAt = $reportHeader->customer_acceptance_signed_at ?? null;
    $customerSignedAtLabel = $customerSignedAt
        ? (\Illuminate\Support\Carbon::parse($customerSignedAt)->format('d-m-Y H:i'))
        : null;
@endphp

<div class="amspec-signature-closing">
    <p>{{ $copy['closing'] ?? '' }}</p>
</div>

<div class="amspec-signature-intro">
    <p>Thanking You,<br>Yours faithfully,</p>
</div>

<div class="amspec-signature-row">
    <div class="amspec-signature amspec-signature-lab">
        <p class="amspec-signature-entity" style="margin-top: 0;">
            For {{ strtoupper($copy['legal_entity'] ?: ($company->name ?? '')) }}
        </p>
        @if(!empty($reportHeader->prepared_by_signature))
            <p style="margin-top: 16px;">
                <img src="{{ $reportHeader->prepared_by_signature }}" alt="Signature" style="max-height: 60px; max-width: 180px;">
            </p>
        @else
            <p @class(['amspec-sig-space' => ($forPdf ?? false)]) style="margin-top: {{ ($forPdf ?? false) ? '18px' : '40px' }};">Authorized Signature</p>
        @endif
        @if(!empty($reportHeader->prepared_by_name))
            <p style="margin-top: 8px; font-weight: 600;">{{ $reportHeader->prepared_by_name }}</p>
        @endif
        @php
            $preparedByPosition = trim((string) ($reportHeader->prepared_by_position ?? ''));
            $showPreparedByPosition = $preparedByPosition !== '' && strcasecmp($preparedByPosition, 'admin') !== 0;
        @endphp
        @if($showPreparedByPosition)
            <p style="margin: 0;">{{ $preparedByPosition }}</p>
        @endif
        <p style="margin-top: 8px;">Phone: {{ $reportHeader->prepared_by_phone ?? $company->telephone ?? '' }}</p>
        <p>Email: {{ $reportHeader->prepared_by_email ?? $company->email ?? '' }}</p>
    </div>

    <div class="amspec-signature amspec-signature-customer">
        <p class="amspec-signature-entity" style="margin-top: 0;">Customer Acceptance</p>
        <p style="margin-top: 8px;">I accept this quotation and the terms stated herein.</p>
        @if($hasCustomerAcceptanceSignature)
            <p style="margin-top: 16px;">
                <img src="{{ $reportHeader->customer_acceptance_signature }}" alt="Customer signature" style="max-height: 60px; max-width: 180px;">
            </p>
        @else
            <p @class(['amspec-sig-space' => ($forPdf ?? false)]) style="margin-top: {{ ($forPdf ?? false) ? '18px' : '40px' }};">Authorized Signature</p>
        @endif
        @if(!empty($reportHeader->customer_acceptance_signer_name))
            <p style="margin-top: 8px; font-weight: 600;">{{ $reportHeader->customer_acceptance_signer_name }}</p>
        @endif
        @if($customerSignedAtLabel)
            <p style="margin-top: 4px;">Signed: {{ $customerSignedAtLabel }}</p>
        @endif
    </div>
</div>
