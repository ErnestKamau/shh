<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <link rel="stylesheet" href="/assets/css/font-awesome/all.min.css">
    <link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap/bootstrap4.4.1.min.css">
    <link type="text/css" rel="stylesheet" href="/select2/select2.min.css" />
    @yield('head')
    <style>
        body { background: #fff; margin: 0; padding: 0; }
        select, .select2.select2-container.select2-container--default { width: 100% !important; }
    </style>
</head>
<body>
    @yield('content')

    <script src="/assets/js/libs/jquery/jquery-3.5.1.min.js"></script>
    <script src="/assets/js/libs/jquery/popper.min.js"></script>
    <script src="/assets/js/libs/bootstrap/bootstrap-4.4.1.min.js"></script>
    <script src="/select2/select2.min.js"></script>
    @yield('script')
</body>
</html>
