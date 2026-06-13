<table class="trf-header-table">
    <tr>
        <td style="width: 18%;">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" class="trf-logo" alt="Company Logo">
            @endif
        </td>
        <td style="width: 52%;">
            <div class="trf-company-name">{{ $company->name ?? '' }}</div>
            <div class="trf-company-meta">
                @if(!empty($company->telephone))Tel: {{ $company->telephone }}<br>@endif
                @if(!empty($company->email))Email: {{ $company->email }}<br>@endif
                @if(!empty($company->address))Address: {{ $company->address }}@if(!empty($company->street)), {{ $company->street }}@endif<br>@endif
                @if(!empty($company->location)){{ $company->location }}<br>@endif
            </div>
        </td>
        <td style="width: 15%; text-align: right;">
            @if(!empty($hexClusterSrc))
                <img src="{{ $hexClusterSrc }}" class="trf-hex" alt="">
            @endif
        </td>
        <td style="width: 15%;">
            <div class="trf-company-meta trf-right">
                PO Box: {{ $company->client_number ?? '' }}<br>
                Fax: {{ $company->fax ?? '' }}<br>
                Website: {{ $company->website ?? '' }}
            </div>
        </td>
    </tr>
</table>

<div class="trf-title">{{ $formTitle }}</div>
<div class="trf-serial">S. No. {{ $serialNumber }}</div>
