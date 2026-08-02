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
<table class="amspec-brand-row" width="100%" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 2px;">
    <tr>
        <td class="amspec-brand-left" width="55%" valign="middle" style="width: 55%; vertical-align: middle; padding: 0;">
            @if($companyLogoSrc !== '')
                <img src="{{ $companyLogoSrc }}"
                     alt="{{ $companyName }}"
                     class="amspec-logo-wordmark"
                     @if($isPdf) width="220" height="58" style="max-width: 240px; max-height: 62px; width: auto; height: auto;" @endif>
            @endif
        </td>
        <td class="amspec-brand-right" width="45%" align="right" valign="top" style="width: 45%; text-align: right; vertical-align: top; padding: 0;">
            @if($hexClusterSrc !== '')
                <img src="{{ $hexClusterSrc }}" alt="" class="amspec-hex-cluster" width="118" height="88">
            @endif
        </td>
    </tr>
</table>
