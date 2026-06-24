<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $reportHeader->quote_number ?? '' }}</title>
    @include('billing.quotations.amspec.partials.styles')
    @include('billing.quotations.amspec.partials.download-styles')
</head>
<body class="amspec-download-body">
    @php
        $shellMode = 'download';
        $stylesLoaded = true;
    @endphp
    @include('billing.quotations.amspec.partials.footer')
    @include('billing.quotations.amspec.shell')
</body>
</html>
