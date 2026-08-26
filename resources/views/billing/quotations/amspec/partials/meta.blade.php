@php
    $isPdf = (bool) ($forPdf ?? false);
    $isBrazilQuotation = (bool) ($isBrazilQuotation ?? false);
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
    @unless($isBrazilQuotation)
        <tr>
            <td class="amspec-meta-label">Subject</td>
            <td class="amspec-meta-value">: <strong>{{ $reportHeader->subject ?? 'Quotation' }}</strong></td>
        </tr>
    @endunless
    @if(($reportHeader->revision_number ?? 1) > 1 || ! empty($reportHeader->revision_of_quote_number))
        <tr>
            <td class="amspec-meta-label">Revision</td>
            <td class="amspec-meta-value">: <strong>Rev. {{ $reportHeader->revision_number ?? 1 }}</strong>@if(! empty($reportHeader->revision_of_quote_number)) <span style="font-weight: 400;">(supersedes {{ $reportHeader->revision_of_quote_number }})</span>@endif</td>
        </tr>
    @endif
    <tr>
        <td class="amspec-meta-label">Company Unit</td>
        <td class="amspec-meta-value">: <strong>{{ $reportHeader->company_unit_display ?? '-' }}</strong></td>
    </tr>
    @unless($isBrazilQuotation)
        <tr>
            <td class="amspec-meta-label">Sampling Location</td>
            <td class="amspec-meta-value">: <strong>{{ $reportHeader->sampling_location_display ?? '-' }}</strong></td>
        </tr>
    @endunless
</table>
