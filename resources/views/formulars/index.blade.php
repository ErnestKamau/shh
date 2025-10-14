@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Worksheet Engine | Lab Management</title>
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

    .formula-card {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .formula-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .formula-card .card-body {
        padding: 1.5rem;
    }

    .formula-card .card-title {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .formula-card .card-text {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .formula-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 1.2rem;
        font-weight: 600;
        color: #007bff;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #6c757d;
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
            'link' => null,
            'name' => 'Worksheet Engine',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0">
                                    <i class="mdi mdi-calculator text-primary"></i>
                                    Worksheet Engine
                                </h2>
                                <p class="text-muted mb-0">Create and manage formula workflows for laboratory calculations</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $stats['activeFormulas'] }}</h4>
                                <p class="mb-0">Active Formulas</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-purple text-white" style="background-color: #6f42c1 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $stats['activeMethodSequences'] }}</h4>
                                <p class="mb-0">Active Sequences</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-chart-timeline fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $stats['executions'] }}</h4>
                                <p class="mb-0">Executions</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-play-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $stats['lookupTables'] }}</h4>
                                <p class="mb-0">Lookup Tables</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-table fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('formulars.manage') }}" class="formula-card card text-decoration-none">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <i class="mdi mdi-calculator fa-3x text-primary mb-3"></i>
                                            <h5 class="card-title">Manage Formulas</h5>
                                            <p class="card-text">Create, edit, and version control formulas</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('formulars.global-variables') }}" class="formula-card card text-decoration-none">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <i class="mdi mdi-variable fa-3x text-success mb-3"></i>
                                            <h5 class="card-title">Global Variables</h5>
                                            <p class="card-text">Manage constants and global variables</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('formulars.lookup-tables') }}" class="formula-card card text-decoration-none">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <i class="mdi mdi-table fa-3x text-info mb-3"></i>
                                            <h5 class="card-title">Lookup Tables</h5>
                                            <p class="card-text">Manage reference data and lookup tables</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('method-sequences.manage') }}" class="formula-card card text-decoration-none">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <i class="mdi mdi-chart-timeline fa-3x text-purple mb-3" style="color: #6f42c1 !important;"></i>
                                            <h5 class="card-title">Method Sequences</h5>
                                            <p class="card-text">Manage method sequence workflows and stages</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('formulars.history') }}" class="formula-card card text-decoration-none">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <i class="mdi mdi-history fa-3x text-warning mb-3"></i>
                                            <h5 class="card-title">Execution History</h5>
                                            <p class="card-text">View and manage worksheet executions</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
