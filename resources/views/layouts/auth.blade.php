<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.favicon')

    <title>{{ $title ?? config('app.name', 'Polucon') }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="/assets/css/font-awesome/all.min.css">
    <link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap/bootstrap4.4.1.min.css">
    <link type="text/css" rel="stylesheet" href="/select2/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('css/w3.css') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @livewireStyles

    @stack('head')
</head>
<body class="bg-light">
    {{ $slot }}

    @livewireScripts
    <script src="/assets/js/libs/jquery/jquery-3.5.1.min.js"></script>
    <script src="/assets/js/libs/jquery/popper.min.js"></script>
    <script src="/assets/js/libs/bootstrap/bootstrap-4.4.1.min.js"></script>
    <script src="/select2/select2.min.js"></script>

    @stack('scripts')
</body>
</html>
