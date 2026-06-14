@php
    $companyHeader = $companyHeader ?? [];
@endphp
<table class="trf-header-table">
    <tr>
        <td style="width: 12%;">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" class="trf-logo" alt="Company Logo">
            @endif
        </td>
        <td style="width: 50%;">
            <div class="trf-company-name">{{ $companyHeader['name'] ?? ($company->name ?? '') }}</div>
            <div class="trf-company-meta">
                Tel: {{ $companyHeader['telephone'] ?? '' }}<br>
                Email: {{ $companyHeader['email'] ?? '' }}<br>
                Address: {{ $companyHeader['address'] ?? '' }}
            </div>
        </td>
        <td style="width: 38%;" class="trf-right">
            <div class="trf-company-meta trf-right">
                PO Box: {{ $companyHeader['po_box'] ?? '' }}<br>
                Fax: {{ $companyHeader['fax'] ?? '' }}<br>
                Website: {{ $companyHeader['website'] ?? '' }}
            </div>
            <div class="trf-serial trf-accent">S. No. {{ $serialNumber }}</div>
        </td>
    </tr>
    <tr class="trf-title-row">
        <td colspan="3" class="trf-title">{{ $formTitle }}</td>
    </tr>
</table>
