<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $formTitle }}</title>
    @include('workflow.forms.test-request.partials.styles')
</head>
<body class="trf-layout-centered">
    <div class="trf-page">
        @include('workflow.forms.test-request.partials.header')
        @include('workflow.forms.test-request.partials.sample-data-water')
        @include('workflow.forms.test-request.partials.footer')
    </div>
</body>
</html>
