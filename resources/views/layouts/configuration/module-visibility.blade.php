@extends('layouts.configuration.layout.app')

@section('title2')
<title>{{ __('system.module_visibility') }}</title>
@endsection

@section('content2')
<div class="container-fluid px-2">
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <h4 class="mb-1"><i class="mdi mdi-swap-horizontal text-primary"></i> {{ __('system.module_switching') }}</h4>
            <p class="mb-0 text-muted">{{ __('system.control_visible_modules') }}</p>
        </div>
    </div>

    @can('system.module-switching.view')
        @livewire('system.module-visibility-manager')
    @else
        <div class="alert alert-warning mb-0">
            <i class="mdi mdi-alert"></i> {{ __('system.no_permission') }}
        </div>
    @endcan
</div>
@endsection
