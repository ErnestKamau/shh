{{--
    Imara AI — master layout
    Partials live in layouts/ImaraAi/partials/
      _styles.blade.php  — CSS
      _sidebar.blade.php — sidebar HTML
      _main.blade.php    — main chat area HTML
      _script.blade.php  — JavaScript
--}}
@extends('layouts.app')

@section('module-name')
<li class="nav-item">
    <a class="nav-link module-name" href="{{ route('imara-ai') }}">
        <i class="mdi mdi-chip"></i> ImaraChat AI
    </a>
</li>
@endsection

@section('title')
<style>
    /* Standalone layout resets */
    html, body {
        height: 100%;
        overflow: hidden;
    }
    body > nav + * {
        margin: 0 !important;
        padding: 0 !important;
    }

    @include('layouts.ImaraAi.partials._styles')
</style>
@endsection

@section('content')
<div id="imara-ai-root">
    @include('layouts.ImaraAi.partials._header')
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="ai-body">
        @include('layouts.ImaraAi.partials._sidebar')
        @include('layouts.ImaraAi.partials._main')
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.2.4/dist/purify.min.js"></script>
<script>
    @include('layouts.ImaraAi.partials._script')
</script>
@endsection
