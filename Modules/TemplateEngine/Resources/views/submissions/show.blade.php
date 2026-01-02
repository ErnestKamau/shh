@extends('layouts.lab.layout.app')

@section('title2')
<title>View Submission - {{ $template->name }}</title>
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
            'link' => '#',
            'name' => $template->name,
            'icon' => null
        ],
        [
            'link' => '#',
            'name' => 'Submission #' . $submission->id,
            'icon' => null
        ]
    ];
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-file-alt text-primary"></i>
                                {{ $template->name }}
                            </h2>
                            <p class="text-muted mb-0">
                                Submitted by {{ $submission->user->name ?? 'N/A' }} on {{ $submission->submitted_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('templates.preview', $template->id) }}" class="btn btn-outline-secondary mr-2">
                                <i class="fas fa-arrow-left"></i> Back to Template
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-primary">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Content -->
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-4">
            @foreach($template->sections as $section)
                @if($section->fields->count() > 0)
                    <div class="mb-4">
                        <h4 class="border-bottom pb-2 mb-3">{{ $section->title }}</h4>
                        
                        @foreach($section->fields as $field)
                            @include('template-engine::submissions.partials.field-display', ['field' => $field, 'data' => $submission->data])
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection

