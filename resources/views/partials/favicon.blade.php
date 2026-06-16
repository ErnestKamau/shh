@php
    $systemFavicon = function_exists('getSystemFavicon') ? getSystemFavicon() : null;
@endphp
@if($systemFavicon)
    <link rel="icon" href="{{ $systemFavicon }}" sizes="any">
    <link rel="shortcut icon" href="{{ $systemFavicon }}">
    <link rel="apple-touch-icon" href="{{ $systemFavicon }}">
@endif
