@extends('layouts.configuration.layout.app')

@section('title2')
<title>System Configuration</title>
@endsection

@section('content2')
<div class="container-fluid px-2">
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <h4 class="mb-1"><i class="mdi mdi-cogs text-primary"></i> System Settings</h4>
            <p class="mb-0 text-muted">Manage global system behavior and module access from one place.</p>
        </div>
    </div>

    @if(auth()->user()->is_support_staff)
        @livewire('system.module-visibility-manager')
    @else
        <div class="alert alert-warning mb-0">
            <i class="mdi mdi-alert"></i> Only support staff can manage module visibility.
        </div>
    @endif
</div>
@endsection
