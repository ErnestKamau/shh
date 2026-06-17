<div class="amspec-signature">
    <p>{{ $copy['closing'] ?? '' }}</p>
    <p style="margin-top: 16px;">Thanking You,<br>Yours faithfully,</p>
    <p class="amspec-signature-entity">
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
