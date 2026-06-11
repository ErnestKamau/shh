@php
    $isPdf = (bool) ($forPdf ?? false);
@endphp
<table @class(['amspec-meta-table', 'amspec-meta-table-pdf' => $isPdf]) width="{{ $isPdf ? 'auto' : '100%' }}" cellpadding="0" cellspacing="0" style="margin-top: {{ $isPdf ? '8px' : '14px' }}; border-collapse: collapse;{{ $isPdf ? ' width: auto; table-layout: auto;' : ' width: 100%; table-layout: fixed;' }}">
    <tr>
        <td class="amspec-meta-label" @if($isPdf) width="1%" @endif>Laboratory Ref</td>
        <td class="amspec-meta-value">: <strong>{{ $reportHeader->laboratory_ref_display ?? $reportHeader->laboratory_ref ?? $reportHeader->quote_number }}</strong></td>
    </tr>
    <tr>
        <td class="amspec-meta-label">Date</td>
        <td class="amspec-meta-value">: <strong>{{ $reportHeader->quote_date_formatted ?? $reportHeader->quote_date }}</strong></td>
    </tr>
    <tr>
        <td class="amspec-meta-label">Attention</td>
        <td class="amspec-meta-value">: <strong>{{ $reportHeader->attention ?? '' }}</strong></td>
    </tr>
    <tr>
        <td class="amspec-meta-label">Subject</td>
        <td class="amspec-meta-value">: <strong>{{ $reportHeader->subject ?? 'Quotation' }}</strong></td>
    </tr>
    <tr>
        <td class="amspec-meta-label">Sampling Location</td>
        <td class="amspec-meta-value">: <strong>{{ $reportHeader->sampling_location_display ?? '-' }}</strong></td>
    </tr>
</table>
