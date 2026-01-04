@extends('layouts.lab.layout.app')

@section('title2')
<title>Preview Template - Template Engine</title>
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
            'name' => 'Form Templates',
            'icon' => null
        ],
        [
            'link' => route('templates.builder', $template->id),
            'name' => $template->name,
            'icon' => null
        ],
        [
            'link' => '#',
            'name' => 'Preview',
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
                                <i class="fas fa-eye text-primary"></i>
                                Preview Application
                            </h2>
                            <p class="text-muted mb-0">Previewing: {{ $template->name }}</p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('templates.builder', $template->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-tools"></i> Back to Builder
                            </a>
                            <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-list"></i> Templates List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h4 class="text-center">{{ $template->name }}</h4>
                    @if($template->description)
                        <p class="text-muted text-center">{{ $template->description }}</p>
                    @endif
                    <hr>
                </div>
                <div class="card-body p-4">
                    @livewire('template-engine::render-form', ['template' => $template])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
