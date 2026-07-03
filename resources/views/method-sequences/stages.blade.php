@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Method Sequence Stages Editor | Lab Management</title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
    .tab-card-header > .nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header > .nav-tabs > li {
        margin-right: 2px;
    }

    .tab-card-header > .nav-tabs > li > a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 15px;
    }

    .tab-card-header > .nav-tabs > li > a.show {
        border-bottom: 2px solid var(--color-primary);
        color: var(--color-primary);
    }

    .tab-card-header > .nav-tabs > li > a:hover {
        color: var(--color-primary);
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header > .tab-content {
        padding-bottom: 0;
    }

    .my-small-text {
        font-size: 13px !important;
    }

    .stage-item {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8f9fa;
    }

    .stage-item.result-stage {
        border-left: 4px solid #28a745;
    }

    .stage-item.non-result-stage {
        border-left: 4px solid var(--color-primary);
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = [
        [
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null,
        ],
        [
            'link' => route('formulars.index'),
            'name' => 'Worksheet Engine',
            'icon' => null,
        ],
        [
            'link' => route('method-sequences.manage'),
            'name' => 'Method Sequence Management',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Stages Editor',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid">
        <!-- Sequence Header Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3">
                            @if($methodSequenceVersion->methodSequence)
                                {{ $methodSequenceVersion->methodSequence->name ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        </h4>
                        <p class="text-muted">
                            @if($methodSequenceVersion->methodSequence)
                                {{ $methodSequenceVersion->methodSequence->description ?? 'No description available' }}
                            @else
                                No description available
                            @endif
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Analyte:</strong></p>
                        <p class="text-muted">
                            @if($methodSequenceVersion->methodSequence && $methodSequenceVersion->methodSequence->analyte)
                                {{ $methodSequenceVersion->methodSequence->analyte->name }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Method:</strong></p>
                        <p class="text-muted">
                            @if($methodSequenceVersion->methodSequence && $methodSequenceVersion->methodSequence->method)
                                {{ $methodSequenceVersion->methodSequence->method->name }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <span class="badge badge-info">Version {{ $methodSequenceVersion->version_number ?? 'N/A' }}</span>
                        @if(isset($methodSequenceVersion->is_active) && $methodSequenceVersion->is_active)
                            <span class="badge badge-success">Active</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @livewire('method-sequences.method-sequence-stage-editor', ['version' => $methodSequenceVersion])
    </div>
</main>
@endsection

