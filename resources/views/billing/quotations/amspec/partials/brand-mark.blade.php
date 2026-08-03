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
<table class="amspec-brand-row" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: none; margin: 0 0 2px 0;">
    <tr>
        <td class="amspec-brand-left" width="55%" valign="middle" style="width: 55%; vertical-align: middle; padding: 0; border: none;">
            @if($companyLogoSrc !== '')
                <img src="{{ $companyLogoSrc }}"
                     alt="{{ $companyName }}"
                     class="amspec-logo-wordmark"
                     @if($isPdf) width="220" height="58" style="max-width: 240px; max-height: 62px; width: auto; height: auto; border: none;" @endif>
            @endif
        </td>
        <td class="amspec-brand-right" width="45%" align="right" valign="top" style="width: 45%; text-align: right; vertical-align: top; padding: 0; border: none;">
            @if($hexClusterSrc !== '')
                <img src="{{ $hexClusterSrc }}"
                     alt=""
                     class="amspec-hex-cluster"
                     width="{{ $hexWidth }}"
                     height="{{ (int) round($hexWidth * 0.75) }}"
                     style="width: {{ $hexWidth }}px; height: auto; max-width: {{ $hexWidth }}px; border: 0; outline: none; display: inline-block;">
            @endif
        </td>
    </tr>
</table>
