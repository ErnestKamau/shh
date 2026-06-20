@php
    $primaryColor = $branding['primary'] ?? '#6D0A0E';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);
@endphp

<div class="amspec-quotation" style="--quotation-primary: {{ $primaryColor }}; --quotation-accent: {{ $accentColor }};">
    @if(!$isPdf && !empty($branding['watermarkSrc']))
        <img src="{{ $branding['watermarkSrc'] }}" alt="" class="amspec-watermark">
    @endif

    <div @class(['amspec-page-sheet', 'amspec-page-one', 'amspec-pdf-page' => $isPdf])>
        <div class="amspec-page-body">
            @include('billing.quotations.amspec.partials.header')
            @include('billing.quotations.amspec.partials.meta')
            @include('billing.quotations.amspec.partials.intro')
            @include('billing.quotations.amspec.partials.test-table')
            @include('billing.quotations.amspec.partials.terms-of-sale')
            @include('billing.quotations.amspec.partials.terms')
        </div>
        @include('billing.quotations.amspec.partials.footer')
    </div>

    <div @class(['amspec-page-sheet', 'amspec-page-two', 'amspec-pdf-page' => $isPdf])>
        @include('billing.quotations.amspec.partials.page-logo')
        <div class="amspec-page-body">
            @include('billing.quotations.amspec.partials.signature')
        </div>
        @include('billing.quotations.amspec.partials.footer')
    </div>
</div>
