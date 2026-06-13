<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $formTitle }}</title>
    @include('workflow.forms.test-request.partials.styles')
</head>
<body>
    @include('workflow.forms.test-request.partials.header')
    @include('workflow.forms.test-request.partials.customer-details')
    @include('workflow.forms.test-request.partials.sample-collection-water')
    @include('workflow.forms.test-request.partials.sample-details-water')
    @include('workflow.forms.test-request.partials.footer-sections')
    @include('workflow.forms.test-request.partials.footer')
</body>
</html>
