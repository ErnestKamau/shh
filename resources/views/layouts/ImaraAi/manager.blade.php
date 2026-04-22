{{--
    Imara AI Manager Layout
    Used for KB Manager and other manager pages that integrate with Imara AI
    Includes sidebar navigation and Imara AI styling
--}}
@extends('layouts.app')

@section('module-name')
<li class="nav-item">
    <a class="nav-link module-name" href="{{ route('imara-ai') }}">
        <i class="mdi mdi-chip"></i> Imara AI
    </a>
</li>
@endsection

@section('title')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
<style>
    /* Standalone manager resets */
    html, body {
        height: 100%;
        overflow: hidden;
    }
    body > nav + * {
        margin: 0 !important;
        padding: 0 !important;
    }

    @include('layouts.ImaraAi.partials._styles')
    @include('layouts.ImaraAi.partials._manager-styles')
</style>
@endsection

@section('content')
<div id="imara-ai-root">
    <div class="ai-body">
        @php
            $settingsRoutes = [
                'ai.settings.index', 
                'ai.knowledge.manager', 
                'ai.knowledge.dashboard', 
                'ai.knowledge.show',
                'ai.knowledge.editor'
            ];
            $isSettings = in_array(Route::currentRouteName(), $settingsRoutes);
        @endphp

        @if($isSettings)
            @include('imara-ai.settings.partials._sidebar')
        @else
            @include('layouts.ImaraAi.partials._sidebar')
        @endif
        
        <main class="manager-main">
            @yield('kb-content')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Minimal sidebar initialization for KB manager
    const sidebar = document.getElementById('aiSidebar');
    const toggle = document.getElementById('toggle-main-sidebar');
    
    if (toggle) {
        toggle.addEventListener('click', () => {
            sidebar?.classList.toggle('collapsed');
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
<script>
    @include('layouts.ImaraAi.partials._script')
</script>

@endpush
