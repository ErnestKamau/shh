@php
    $shellMode = $shellMode ?? 'embedded';
    $forPdf = $forPdf ?? false;
    $stylesLoaded = $stylesLoaded ?? false;
@endphp

@if($shellMode === 'preview')
    <div class="preview-toolbar">
        <a href="{{ route('quotation.preview.pdf', ['id' => $reportHeader->id]) }}" target="_blank" class="amspec-btn amspec-btn-primary">
            Download PDF
        </a>
        <button type="button" onclick="window.print()" class="amspec-btn amspec-btn-secondary">
            Print
        </button>
    </div>
@endif

<div @class([
    'amspec-document-shell',
    'amspec-shell-' . $shellMode => true,
])>
    @include('billing.quotations.amspec.document')
</div>
