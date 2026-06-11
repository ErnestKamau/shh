@php
    $primaryColor = $branding['primary'] ?? '#6D0A0E';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);
@endphp

<div class="amspec-quotation" style="--quotation-primary: {{ $primaryColor }}; --quotation-accent: {{ $accentColor }};">
    @if(!$isPdf && !empty($branding['watermarkSrc']))
        <img src="{{ $branding['watermarkSrc'] }}" alt="" class="amspec-watermark">
    @endif

    @if($isPdf)
        {{-- PDF: exactly two pages — footer anchored at bottom of each page --}}
        <div class="amspec-page-sheet amspec-page-one amspec-pdf-page">
            <table class="amspec-pdf-page-layout" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="amspec-pdf-page-main" valign="top">
                        @include('billing.quotations.amspec.partials.header')
                        @include('billing.quotations.amspec.partials.meta')
                        @include('billing.quotations.amspec.partials.intro')
                        @include('billing.quotations.amspec.partials.test-table')
                    </td>
                </tr>
                <tr>
                    <td class="amspec-pdf-page-footer-cell" valign="bottom">
                        @include('billing.quotations.amspec.partials.footer')
                    </td>
                </tr>
            </table>
        </div>

        <div class="amspec-page-sheet amspec-page-two amspec-pdf-page">
            <table class="amspec-pdf-page-layout" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="amspec-pdf-page-main" valign="top">
                        @include('billing.quotations.amspec.partials.page-logo')
                        @include('billing.quotations.amspec.partials.terms')
                        @include('billing.quotations.amspec.partials.signature')
                    </td>
                </tr>
                <tr>
                    <td class="amspec-pdf-page-footer-cell" valign="bottom">
                        @include('billing.quotations.amspec.partials.footer')
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div class="amspec-page-sheet amspec-page-one">
            <div class="amspec-page-body">
                @include('billing.quotations.amspec.partials.header')
                @include('billing.quotations.amspec.partials.meta')
                @include('billing.quotations.amspec.partials.intro')
                @include('billing.quotations.amspec.partials.test-table')
            </div>
            @include('billing.quotations.amspec.partials.footer')
        </div>

        <div class="amspec-page-sheet amspec-page-two">
            @include('billing.quotations.amspec.partials.page-logo')
            <div class="amspec-page-body">
                @include('billing.quotations.amspec.partials.terms')
                @include('billing.quotations.amspec.partials.signature')
            </div>
            @include('billing.quotations.amspec.partials.footer')
        </div>
    @endif
</div>
