<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $formTitle }}</title>
    @include('workflow.forms.test-request.partials.styles')
</head>
<body class="trf-layout-centered trf-orientation-{{ $orientation ?? 'landscape' }}">
    <div class="trf-page">
        @include('workflow.forms.test-request.partials.header')
        @include('workflow.forms.test-request.partials.sample-data-food')
        @include('workflow.forms.test-request.partials.footer')
    </div>
</body>
</html>
