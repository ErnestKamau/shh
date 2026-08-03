@php
    $isPdf = (bool) ($forPdf ?? false);
    $hexClusterSrc = $branding['hexClusterDataUri'] ?? '';
    $hexWidth = $isPdf ? 108 : 100;
@endphp

<table class="amspec-header-top" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: none; margin: 0;">
    <tr>
        <td class="amspec-header-company" width="58%" valign="top" style="width: 58%; vertical-align: top; padding: 0; border: none; word-wrap: break-word;">
            <div class="amspec-company-name">{{ $company->name ?? '' }}</div>
            <div class="amspec-contact-line amspec-company-address">
                @if(!empty($company->address)){{ $company->address }}<br>@endif
                @if(!empty($company->location)){{ $company->location }}<br>@endif
                @if(!empty($company->street)){{ $company->street }}@endif
            </div>
        </td>
        <td class="amspec-header-contact" width="42%" valign="top" align="right" style="width: 42%; vertical-align: top; text-align: right; padding: 0; border: none; word-wrap: break-word;">
            @if($hexClusterSrc !== '')
                <div class="amspec-header-hex" style="text-align: right; margin: 0 0 6px 0; padding: 0; border: none; line-height: 0;">
                    <img src="{{ $hexClusterSrc }}"
                         alt=""
                         class="amspec-hex-cluster"
                         width="{{ $hexWidth }}"
                         height="{{ (int) round($hexWidth * 0.75) }}"
                         style="width: {{ $hexWidth }}px; height: auto; max-width: {{ $hexWidth }}px; border: 0; outline: none; display: inline-block; vertical-align: top;">
                </div>
            @endif
            <div class="amspec-contact-line">
                <div>Tel: {{ $company->telephone ?? '' }}</div>
                <div>Fax: {{ $company->fax ?? '' }}</div>
                <div>Mobile: {{ $company->cell_phone ?? '' }}</div>
                <div>Email: {{ $company->email ?? '' }}</div>
            </div>
        </td>
    </tr>
</table>
