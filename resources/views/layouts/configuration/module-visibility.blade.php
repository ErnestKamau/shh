@extends('layouts.configuration.layout.app')

@section('title2')
<title>Module Visibility</title>
@endsection

@section('content2')
<div class="container-fluid px-2">
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <h4 class="mb-1"><i class="mdi mdi-swap-horizontal text-primary"></i> Module Switching</h4>
            <p class="mb-0 text-muted">Control which modules appear on the home dashboard.</p>
        </div>
    </div>

    @if(auth()->user()->is_support_staff && auth()->user()->can('System.components.System Settings.View'))
        @livewire('system.module-visibility-manager')
    @else
        <div class="alert alert-warning mb-0">
            <i class="mdi mdi-alert"></i> Only support staff can manage module switching.
        </div>
    @endif
</div>
@endsection
