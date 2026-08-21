@extends('layouts.lab.layout.app', ['dataTable' => false, 'select2' => true])

@section('title2')
<title>Lab — Quotation Overview</title>
@endsection

@section('content2')
@php
    $drafts = $drafts ?? collect();
@endphp

<main class="container-fluid lab-surface-theme ls-admin-page quotation-index-page ls-quotation-shell ls-ui-kit" data-ls-type="plex">
    @include('layouts.lab.partials.lab-surface-theme-styles')
    @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')
    @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')

    <?php
    $items = [
        [
            'link' => '/lab-dashboard',
            'name' => 'Dashboard',
            'icon' => null,
        ],
        [
            'link' => route('quotation-index'),
            'name' => 'Quotation Overview',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle-outline mr-1"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline mr-1"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 ls-quotation-header-card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-edit-outline text-primary"></i>
                                Quotation Overview
                            </h2>
                            <p class="text-muted mb-0">KPIs and quotations by status</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                            @include('livewire.billing.partials.quotation-drafts-dropdown', ['drafts' => $drafts])
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add-quotation">
                                <i class="mdi mdi-plus"></i> Add Quotation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(! empty($metrics))
        @include('layouts.lab.invoice.partials.quotation-metrics', ['metrics' => $metrics, 'kpiPeriod' => $kpiPeriod ?? null])
    @endif

    @livewire('billing.quotation-manager', ['embedded' => true])
</main>

<livewire:billing.create-enquiry-from-quotation-wizard />

@include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers, 'labSections' => $labSections ?? collect()])
@endsection

@section('script2')
@include('layouts.lab.invoice.partials.add-quotation-modal-scripts')
@endsection
