<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $reportHeader->quote_number ?? '' }}</title>
    @include('billing.quotations.amspec.partials.fonts')
    @include('billing.quotations.amspec.partials.styles')
</head>
<body class="amspec-preview-body">
    @php
        $shellMode = 'public';
        $forPdf = false;
        $stylesLoaded = true;
    @endphp
    @include('billing.quotations.amspec.shell')
</body>
</html>
