@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Worksheet Executor | Lab Management</title>
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
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header > .nav-tabs > li > a:hover {
        color: #007bff;
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

    .worksheet-step {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8f9fa;
    }

    .worksheet-step.step-input {
        border-left: 4px solid #007bff;
    }

    .worksheet-step.step-derived {
        border-left: 4px solid #28a745;
    }

    .worksheet-step.step-lookup {
        border-left: 4px solid #ffc107;
    }

    .result-display {
        background: #e8f5e8;
        border: 1px solid #28a745;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
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
            'link' => route('formulars.manage'),
            'name' => 'Formula Management',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Worksheet Executor',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid">
        @livewire('formulars.worksheet-executor', [
            'formulaVersion' => $formulaVersion,
            'executionMode' => $executionMode,
            'sampleId' => $sampleId,
            'batchId' => $batchId
        ])
    </div>
</main>
@endsection
