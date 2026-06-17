<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $formTitle }}</title>
    @include('workflow.forms.test-request.partials.styles')
    <style>
        @page { size: A4 portrait; margin: 2mm 3mm; }
    </style>
</head>
<body class="trf-layout-centered trf-waste-water">
    <div class="trf-page">
        @include('workflow.forms.test-request.partials.header-waste-water')
        @include('workflow.forms.test-request.partials.sample-data-waste-water')
        @include('workflow.forms.test-request.partials.footer')
    </div>
</body>
</html>
