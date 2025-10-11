@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Manage Lookup Table Entries | Lab Management</title>
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
            'link' => route('formulars.lookup-tables'),
            'name' => 'Lookup Tables',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Manage Entries',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid">
        @livewire('formulars.lookup-table-entry-manager', ['lookupTable' => $lookupTable])
    </div>
</main>
@endsection

