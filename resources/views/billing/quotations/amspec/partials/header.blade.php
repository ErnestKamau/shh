@php
    $isPdf = (bool) ($forPdf ?? false);
    $companyLogoSrc = $isPdf
        ? ($branding['logoDataUri'] ?? '')
        : ($branding['logoUrl'] ?? $branding['logoDataUri'] ?? '');
    if ($companyLogoSrc === '') {
        $companyLogoSrc = $branding['wordmarkDataUri'] ?? '';
    }
    $hexClusterSrc = $branding['hexClusterDataUri'] ?? '';
    $companyName = $company->name ?? 'Company';
    $hexWidth = $isPdf ? 108 : 100;
@endphp

<table class="amspec-header-block" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: none; margin: 0 0 10px 0;">
    <tr class="amspec-header-logos">
        <td class="amspec-header-logo-cell" width="58%" valign="middle" style="width: 58%; vertical-align: middle; padding: 0 12px 10px 0; border: none;">
            @if($companyLogoSrc !== '')
                <img src="{{ $companyLogoSrc }}"
                     alt="{{ $companyName }}"
                     class="amspec-logo-wordmark"
                     @if($isPdf) width="220" height="58" style="max-width: 240px; max-height: 62px; width: auto; height: auto; border: none; display: block;" @else style="display: block;" @endif>
            @endif
        </td>
        <td class="amspec-header-hex-cell" width="42%" valign="top" align="right" style="width: 42%; vertical-align: top; text-align: right; padding: 0 0 10px 0; border: none;">
            @if($hexClusterSrc !== '')
                <div class="amspec-header-hex-wrap">
                    <img src="{{ $hexClusterSrc }}"
                         alt=""
                         class="amspec-hex-cluster"
                         width="{{ $hexWidth }}"
                         height="{{ (int) round($hexWidth * 0.75) }}"
                         style="width: {{ $hexWidth }}px; height: auto; max-width: {{ $hexWidth }}px; border: 0; outline: none; display: inline-block; vertical-align: top;">
                </div>
            @endif
        </td>
    </tr>
    <tr class="amspec-header-details">
        <td class="amspec-header-company" width="58%" valign="top" style="width: 58%; vertical-align: top; padding: 0 12px 0 0; border: none; word-wrap: break-word;">
            <div class="amspec-company-name">{{ $company->name ?? '' }}</div>
            <div class="amspec-contact-line amspec-company-address">
                @if(!empty($company->address)){{ $company->address }}<br>@endif
                @if(!empty($company->location)){{ $company->location }}<br>@endif
                @if(!empty($company->street)){{ $company->street }}@endif
            </div>
        </td>
        <td class="amspec-header-contact" width="42%" valign="top" align="right" style="width: 42%; vertical-align: top; text-align: right; padding: 0; border: none; word-wrap: break-word;">
            <div class="amspec-contact-line amspec-header-contact-lines">
                <div>Tel: {{ $company->telephone ?? '' }}</div>
                <div>Fax: {{ $company->fax ?? '' }}</div>
                <div>Mobile: {{ $company->cell_phone ?? '' }}</div>
                <div>Email: {{ $company->email ?? '' }}</div>
            </div>
        </td>
    </tr>
</table>
