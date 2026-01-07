@extends('layouts.lab.layout.app')

@section('title2')
<title>Template Builder - {{ $template->name }}</title>
@endsection

@section('content2')
<div class="container-fluid">
    <?php
    $breadcrumbItems = [
        [
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ],
        [
            'link' => route('templates.index'),
            'name' => 'Report Templates',
            'icon' => null
        ],
        [
            'link' => '#',
            'name' => 'Report Builder',
            'icon' => null
        ]
    ];
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    <!-- Page Title Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-tools text-primary"></i>
                                Report Builder
                            </h2>
                            <p class="text-muted mb-0">Building: {{ $template->name }}</p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('templates.preview', $template->id) }}" class="btn btn-outline-success">
                                <i class="fas fa-eye"></i> Preview Report
                            </a>
                            <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-arrow-left"></i> Back to Templates
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewire('template-engine::builder', ['template' => $template])
</div>
@endsection

@push('scripts')
    {{-- Scripts moved to Livewire component to support @this --}}
    <script src="{{ asset('assets/js/libs/jquery-ui/jquery-ui.min.js') }}"></script>
@endpush
