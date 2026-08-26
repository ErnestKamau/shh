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
    $hexWidth = $isPdf ? 109 : 101;
    $logoHeight = $isPdf ? 57 : 53;
    $hexHeight = $isPdf ? 59 : 55;

    $customerName = trim((string) ($reportHeader->customer_name ?? ''));
    $companyAddress = trim((string) ($reportHeader->physical_address ?? ''));
    $cityCountry = trim((string) ($reportHeader->customer_city_country ?? ''));
    if ($cityCountry === '') {
        $cityCountry = trim(implode(', ', array_filter([
            trim((string) ($reportHeader->customer_city ?? '')),
            trim((string) ($reportHeader->customer_country ?? '')),
        ])));
    }
    $customerTel = trim((string) ($reportHeader->customer_tel ?? ''));
    $customerFax = trim((string) ($reportHeader->customer_fax ?? ''));
    $customerMobile = trim((string) ($reportHeader->customer_mobile ?? ''));
    $customerEmail = trim((string) ($reportHeader->customer_email ?? ''));
@endphp

<table class="amspec-header-block" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: none; margin: 0 0 10px 0;">
    <tr class="amspec-header-logos">
        <td class="amspec-header-logo-cell" width="58%" valign="middle" style="width: 58%; vertical-align: middle; padding: 0 12px 16px 0; border: none;">
            @if($companyLogoSrc !== '')
                <img src="{{ $companyLogoSrc }}"
                     alt="{{ $companyName }}"
                     class="amspec-logo-wordmark"
                     @if($isPdf) width="219" height="{{ $logoHeight }}" style="max-width: 239px; max-height: {{ $logoHeight }}px; width: auto; height: auto; border: none; display: inline-block; vertical-align: middle;" @else style="display: inline-block; vertical-align: middle;" @endif>
            @endif
        </td>
        <td class="amspec-header-hex-cell" width="42%" valign="middle" align="right" style="width: 42%; vertical-align: middle; text-align: right; padding: 0 0 16px 0; border: none;">
            @if($hexClusterSrc !== '')
                <div class="amspec-header-hex-wrap" style="margin-top: -4px;">
                    <img src="{{ $hexClusterSrc }}"
                         alt=""
                         class="amspec-hex-cluster"
                         width="{{ $hexWidth }}"
                         height="{{ $hexHeight }}"
                         style="width: auto; height: {{ $hexHeight }}px; max-height: {{ $hexHeight }}px; max-width: {{ $hexWidth }}px; border: 0; outline: none; display: inline-block; vertical-align: middle;">
                </div>
            @endif
        </td>
    </tr>
    <tr class="amspec-header-details">
        <td class="amspec-header-company" width="58%" valign="top" style="width: 58%; vertical-align: top; padding: 0 12px 0 0; border: none; word-wrap: break-word;">
            @if($customerName !== '')
                <div class="amspec-customer-name">{{ $customerName }}</div>
            @endif
            @if($companyAddress !== '')
                <div class="amspec-contact-line amspec-customer-address">{{ $companyAddress }}</div>
            @endif
            @if($cityCountry !== '')
                <div class="amspec-contact-line amspec-customer-address">{{ $cityCountry }}</div>
            @endif
        </td>
        <td class="amspec-header-customer" width="42%" valign="top" align="right" style="width: 42%; vertical-align: top; text-align: right; padding: 0; border: none; word-wrap: break-word;">
            <div class="amspec-contact-line amspec-header-customer-contact">
                @if(($isBrazilQuotation ?? false))
                    @php
                        $brazilPhone = $customerTel !== '' ? $customerTel : $customerMobile;
                    @endphp
                    @if($brazilPhone !== '')
                        <div>Tel: {{ $brazilPhone }}</div>
                    @endif
                    @if($customerEmail !== '')
                        <div>Email: {{ $customerEmail }}</div>
                    @endif
                @else
                    <div>Tel: {{ $customerTel }}</div>
                    <div>Fax: {{ $customerFax }}</div>
                    <div>Mobile: {{ $customerMobile }}</div>
                    <div>Email: {{ $customerEmail }}</div>
                @endif
            </div>
        </td>
    </tr>
</table>
