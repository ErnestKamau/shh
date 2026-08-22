@extends('layouts.lab.layout.app', ['dataTable' => false, 'select2' => true])

@section('title2')
<title>Lab — Quotations</title>
@endsection

@section('content2')
@php
    $drafts = $drafts ?? collect();
    $requestedPageTab = request()->query('page_tab');
    $hasStageFilter = filled(request()->query('stage_filter'));
    $pageTab = $requestedPageTab === 'quotations' || $hasStageFilter
        ? 'quotations'
        : 'overview';
    $initialStage = request()->query('stage_filter');
    $initialStage = is_string($initialStage) ? $initialStage : null;
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
            'name' => 'Quotations',
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
                                Quotations
                            </h2>
                            <p class="text-muted mb-0">KPIs, workflow stages, and quotation management</p>
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

    <div class="card shadow-sm border-0 ls-quotation-panel ls-quotation-page-tabs-card mb-4">
        <div class="card-header border-0 ls-quotation-page-tabs-header">
            <ul class="nav nav-tabs ls-quotation-page-tabs" id="quotation-page-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $pageTab === 'overview' ? 'active' : '' }}"
                       id="quotation-page-overview-tab"
                       data-toggle="tab"
                       href="#quotation-page-overview"
                       role="tab"
                       aria-controls="quotation-page-overview"
                       aria-selected="{{ $pageTab === 'overview' ? 'true' : 'false' }}">
                        <i class="mdi mdi-chart-box-outline"></i>
                        Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $pageTab === 'quotations' ? 'active' : '' }}"
                       id="quotation-page-quotations-tab"
                       data-toggle="tab"
                       href="#quotation-page-quotations"
                       role="tab"
                       aria-controls="quotation-page-quotations"
                       aria-selected="{{ $pageTab === 'quotations' ? 'true' : 'false' }}">
                        <i class="mdi mdi-file-document-multiple-outline"></i>
                        Quotations
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="quotation-page-tabs-content">
                <div class="tab-pane fade {{ $pageTab === 'overview' ? 'show active' : '' }} p-3 p-md-4"
                     id="quotation-page-overview"
                     role="tabpanel"
                     aria-labelledby="quotation-page-overview-tab">
                    @if(! empty($metrics))
                        @include('layouts.lab.invoice.partials.quotation-metrics', [
                            'metrics' => $metrics,
                            'kpiPeriod' => $kpiPeriod ?? null,
                        ])
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-chart-box-outline" style="font-size: 2.5rem;"></i>
                            <p class="mb-0 mt-2">No KPI data available yet.</p>
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade {{ $pageTab === 'quotations' ? 'show active' : '' }} p-3 p-md-4"
                     id="quotation-page-quotations"
                     role="tabpanel"
                     aria-labelledby="quotation-page-quotations-tab">
                    @livewire('billing.quotation-manager', [
                        'embedded' => true,
                        'initialStage' => $initialStage,
                    ])
                </div>
            </div>
        </div>
    </div>
</main>

<livewire:billing.create-enquiry-from-quotation-wizard />

@include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers, 'labSections' => $labSections ?? collect()])
@endsection

@section('script2')
@include('layouts.lab.invoice.partials.add-quotation-modal-scripts')
<script>
    window.showQuotationStageTab = function (stage) {
        var quotationsTab = document.getElementById('quotation-page-quotations-tab');
        if (quotationsTab && typeof jQuery !== 'undefined') {
            jQuery(quotationsTab).tab('show');
        }

        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('set-quotation-stage', { stage: stage || '' });
        }

        window.setTimeout(function () {
            document.getElementById('quotation-stage-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
    };
</script>
@endsection
