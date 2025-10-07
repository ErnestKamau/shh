@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Formula Steps Editor | Lab Management</title>
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

    .step-item {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8f9fa;
    }

    .step-item.step-input {
        border-left: 4px solid #007bff;
    }

    .step-item.step-derived {
        border-left: 4px solid #28a745;
    }

    .step-item.step-lookup {
        border-left: 4px solid #ffc107;
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
            'name' => 'Formula Workflow Engine',
            'icon' => null,
        ],
        [
            'link' => route('formulars.manage'),
            'name' => 'Formula Management',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Formula Steps Editor',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid">
        @livewire('formulars.formula-step-editor', ['formulaVersion' => $formulaVersion])
    </div>
</main>
@endsection
