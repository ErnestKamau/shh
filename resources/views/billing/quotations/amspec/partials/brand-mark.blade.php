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
@endphp
<table class="amspec-brand-row" width="100%" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse;">
    <tr>
        <td class="amspec-brand-left" width="55%" valign="middle" style="width: 55%; vertical-align: middle;">
            @if($companyLogoSrc !== '')
                <img src="{{ $companyLogoSrc }}" alt="{{ $companyName }}" class="amspec-logo-wordmark">
            @endif
        </td>
        <td class="amspec-brand-right" width="45%" align="right" valign="top" style="width: 45%; text-align: right; vertical-align: top;">
            @if($hexClusterSrc !== '')
                <img src="{{ $hexClusterSrc }}" alt="" class="amspec-hex-cluster" width="110" height="82">
            @endif
        </td>
    </tr>
</table>
