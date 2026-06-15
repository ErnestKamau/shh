@php
    $companyHeader = $companyHeader ?? [];
@endphp
<table class="trf-header-table trf-ww-header-table">
    <tr class="trf-title-row">
        <td colspan="3" class="trf-title">{{ $formTitle }}</td>
    </tr>
    <tr>
        <td class="trf-header-logo">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" class="trf-logo" alt="Company Logo">
            @endif
        </td>
        <td class="trf-header-center">
            <table class="trf-header-company-grid trf-ww-header-company" cellpadding="0" cellspacing="0">
                <colgroup>
                    <col class="trf-company-col-left">
                    <col class="trf-company-col-right">
                </colgroup>
                <tr>
                    <td class="trf-company-col-left trf-company-name-cell"><nobr>{{ $companyHeader['name'] ?? ($company->name ?? '') }}</nobr></td>
                    <td class="trf-company-col-right"><nobr><span class="trf-field-label">PO Box:</span> <span class="trf-field-value">{{ $companyHeader['po_box'] ?? '' }}</span></nobr></td>
                </tr>
                <tr>
                    <td class="trf-company-col-left"><nobr><span class="trf-field-label">Tel:</span> <span class="trf-field-value">{{ $companyHeader['telephone'] ?? '' }}</span></nobr></td>
                    <td class="trf-company-col-right"><nobr><span class="trf-field-label">Fax:</span> <span class="trf-field-value">{{ $companyHeader['fax'] ?? '' }}</span></nobr></td>
                </tr>
                <tr>
                    <td class="trf-company-col-left"><nobr><span class="trf-field-label">Email:</span> <span class="trf-field-value">{{ $companyHeader['email'] ?? '' }}</span></nobr></td>
                    <td class="trf-company-col-right"><nobr><span class="trf-field-label">Website:</span> <span class="trf-field-value">{{ $companyHeader['website'] ?? '' }}</span></nobr></td>
                </tr>
                <tr>
                    <td colspan="2" class="trf-company-address-cell"><nobr><span class="trf-field-label">Address:</span> <span class="trf-field-value">{{ $companyHeader['address'] ?? '' }}</span></nobr></td>
                </tr>
            </table>
        </td>
        <td class="trf-header-serial">
            <span class="trf-serial">S. No. {{ $serialNumber }}</span>
        </td>
    </tr>
</table>
