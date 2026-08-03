@php
    $primaryColor = $branding['primary'] ?? 'var(--color-primary)';
    $accentColor = $branding['accent'] ?? '#4CAF50';
    $isPdf = (bool) ($forPdf ?? false);
@endphp

<div class="amspec-quotation" style="--quotation-primary: {{ $primaryColor }}; --quotation-accent: {{ $accentColor }};">
    @if(!empty($branding['watermarkSrc']))
        <img src="{{ $branding['watermarkSrc'] }}" alt="" class="amspec-watermark" aria-hidden="true">
    @endif


    <div @class(['amspec-page-sheet', 'amspec-page-one', 'amspec-pdf-page' => $isPdf])>
        <div class="amspec-page-body">
            @include('billing.quotations.amspec.partials.header')
            @include('billing.quotations.amspec.partials.meta')
            @include('billing.quotations.amspec.partials.intro')
            @include('billing.quotations.amspec.partials.test-table')
        </div>
        @if(!$isPdf)
            @include('billing.quotations.amspec.partials.footer')
        @endif
    </div>

    <div @class(['amspec-page-sheet', 'amspec-page-two', 'amspec-pdf-page' => $isPdf])>
        @include('billing.quotations.amspec.partials.page-logo')
        <div class="amspec-page-body">
            @include('billing.quotations.amspec.partials.terms')
            @include('billing.quotations.amspec.partials.signature')
        </div>
        @if(!$isPdf)
            @include('billing.quotations.amspec.partials.footer')
        @endif
    </div>
</div>
