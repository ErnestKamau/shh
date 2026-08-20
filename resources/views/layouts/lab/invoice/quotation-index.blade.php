@extends('layouts.lab.layout.app', ['dataTable' => false, 'select2' => true])

@section('title2')
<title>Lab — Quotations</title>
@endsection

@section('content2')
@php
    $stageCounts = $stageCounts ?? [
        'all' => $quotations->count(),
        'Quote In Preparation' => 0,
        'Quote In Approval' => 0,
        'Quote Complete' => 0,
    ];
    $isAllStage = $stage === 'All Quotations';
    $isPrepStage = $stage === 'Quote In Preparation';
    $isApprovalStage = $stage === 'Quote In Approval';
    $isCompleteStage = $stage === 'Quote Complete';
@endphp

<main class="container-fluid lab-surface-theme ls-admin-page quotation-index-page" data-ls-type="plex">
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
        [
            'link' => null,
            'name' => $stage,
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')

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
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-file-document-edit-outline text-primary"></i>
                                Quotations
                            </h2>
                            <p class="text-muted mb-0">View and manage customer quotations — {{ $stage }}</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                            <div class="dropdown">
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                        data-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">
                                    <i class="mdi mdi-file-edit-outline"></i>
                                    Drafts
                                    <span class="badge badge-pill badge-primary ml-1">{{ $drafts->count() }}</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right quotation-drafts-menu p-2">
                                    @forelse($drafts as $draft)
                                        <a class="dropdown-item quotation-draft-item"
                                           href="{{ route('add-qoute-details-view', ['id' => $draft->id]) }}">
                                            <strong>{{ $draft->quote_number }}</strong>
                                            <small class="text-muted d-block">{{ $draft->created_at }}</small>
                                        </a>
                                    @empty
                                        <span class="dropdown-item text-muted small">No drafts</span>
                                    @endforelse
                                </div>
                            </div>
                            @if($isPrepStage || $isAllStage)
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add-quotation">
                                    <i class="mdi mdi-plus"></i> Add Quotation
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isAllStage && !empty($metrics))
        @include('layouts.lab.invoice.partials.quotation-metrics', ['metrics' => $metrics, 'kpiPeriod' => $kpiPeriod ?? null])
    @endif

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 quotation-stage-tabs-card">
                <div class="quotation-stage-tabs px-3 px-md-4 py-3">
                    <a href="{{ route('quotation-index') }}"
                       class="quotation-stage-tab {{ $isAllStage ? 'is-active' : '' }}">
                        <i class="mdi mdi-view-list-outline"></i>
                        <span>All</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['all'] }}</span>
                    </a>
                    <a href="{{ route('quotation-index', ['stage' => 'Quote In Preparation']) }}"
                       class="quotation-stage-tab {{ $isPrepStage ? 'is-active' : '' }}">
                        <i class="mdi mdi-clock-outline"></i>
                        <span>In Preparation</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['Quote In Preparation'] }}</span>
                    </a>
                    <a href="{{ route('quotation-index', ['stage' => 'Quote In Approval']) }}"
                       class="quotation-stage-tab {{ $isApprovalStage ? 'is-active' : '' }}">
                        <i class="mdi mdi-account-check-outline"></i>
                        <span>In Approval</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['Quote In Approval'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('quotation-index', ['stage' => 'Quote Complete']) }}"
                       class="quotation-stage-tab quotation-stage-tab--complete {{ $isCompleteStage ? 'is-active' : '' }}">
                        <i class="mdi mdi-check-circle-outline"></i>
                        <span>Complete</span>
                        <span class="quotation-stage-tab__count">{{ $stageCounts['Quote Complete'] }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($isAllStage)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-muted">
                            <i class="mdi mdi-filter-variant"></i> Filter Options
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('filterQuotations') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Quote Type</label>
                                        <select name="quote_type" id="quote_type" class="form-control">
                                            <option value="">All quotation types</option>
                                            <option value="General">General Quotation</option>
                                            <option value="Analysis">Analysis Quotation</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Lab Section</label>
                                        <select name="lab_section_id" id="lab_section_id" class="form-control">
                                            <option value="">All lab sections</option>
                                            @foreach($labSections ?? [] as $section)
                                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 sample_type_field d-none">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Sample Type</label>
                                        <select name="sample_type_id" id="sample_type_id" class="form-control">
                                            <option value="">Select sample type…</option>
                                            @foreach($sample_types ?? [] as $st)
                                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 analysis_type_field d-none">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Analysis Type</label>
                                        <select name="analysis_type_id" id="analysis_type_id" class="form-control"></select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="end_date form-control">
                                    </div>
                                </div>
                                <div class="col-md-12 item_description_field d-none">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Item Description</label>
                                        <textarea class="form-control" name="item_description" rows="1" placeholder="Search line item description…"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi mdi-filter-variant-plus"></i> Apply Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-table"></i>
                        {{ $stage }}
                    </h5>
                    <span class="text-muted small">{{ $quotations->count() }} {{ \Illuminate\Support\Str::plural('quotation', $quotations->count()) }}</span>
                </div>
                <div class="card-body @if($quotations->count() > 0) pt-0 @endif">
                    @if($quotations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover ls-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 1%;">Actions</th>
                                        <th>Quote #</th>
                                        <th>Type</th>
                                        <th>Lab Section(s)</th>
                                        @if($isAllStage)
                                            <th>Status</th>
                                        @endif
                                        <th>Quote Date</th>
                                        <th>Expiry</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Prepared By</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quotations as $quotation)
                                        <tr class="quotation-preview-hover-parent">
                                            <td>
                                                <div class="d-flex quotation-actions-cell">
                                                    <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}"
                                                       class="rm-act-btn rm-act-btn--open"
                                                       title="Open quotation">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    @if($quotation->status === 'Quote In Preparation')
                                                        <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}"
                                                           class="rm-act-btn rm-act-btn--edit"
                                                           title="Edit in preparation">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('quotation.preview', ['id' => $quotation->id]) }}"
                                                       class="rm-act-btn rm-act-btn--preview quotation-preview-quote-btn"
                                                       title="Preview quotation PDF"
                                                       target="_blank">
                                                        <i class="mdi mdi-file-eye"></i>
                                                    </a>
                                                    <a href="{{ route('clone_quotation', ['id' => $quotation->id]) }}"
                                                       class="rm-act-btn rm-act-btn--clone"
                                                       title="Clone">
                                                        <i class="mdi mdi-content-duplicate"></i>
                                                    </a>
                                                    @can('laboratory.components.quotation.add')
                                                        @if($quotation->status === 'Quote Complete'
                                                            && $quotation->quotation_type === 'Analysis'
                                                            && $quotation->expiring_date
                                                            && \Carbon\Carbon::parse($quotation->expiring_date)->startOfDay()->gte(now()->startOfDay()))
                                                            <button type="button"
                                                                    class="rm-act-btn rm-act-btn--enquiry create-enquiry-button"
                                                                    title="Create enquiry from quotation"
                                                                    onclick="Livewire.dispatch('open-create-enquiry-from-quotation', { quotationId: @js((string) $quotation->id) })">
                                                                <i class="mdi mdi-flask-outline"></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}" class="quotation-quote-number">
                                                    {{ $quotation->quote_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="quotation-type-chip {{ $quotation->quotation_type === 'Analysis' ? 'quotation-type-chip--analysis' : 'quotation-type-chip--general' }}">
                                                    {{ $quotation->quotation_type }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $sectionNames = $quotation->relationLoaded('labSections')
                                                        ? $quotation->labSections->pluck('name')->filter()->values()
                                                        : collect();
                                                @endphp
                                                @if($sectionNames->isEmpty())
                                                    <span class="text-muted">—</span>
                                                @else
                                                    {{ $sectionNames->implode(', ') }}
                                                @endif
                                            </td>
                                            @if($isAllStage)
                                                <td>
                                                    <span @class([
                                                        'quotation-status-chip',
                                                        'quotation-status-chip--complete' => $quotation->status === 'Quote Complete',
                                                        'quotation-status-chip--approval' => $quotation->status === 'Quote In Approval',
                                                        'quotation-status-chip--prep' => ! in_array($quotation->status, ['Quote Complete', 'Quote In Approval'], true),
                                                    ])>
                                                        {{ $quotation->status }}
                                                    </span>
                                                </td>
                                            @endif
                                            <td>{{ $quotation->quote_date }}</td>
                                            <td>
                                                {{ $quotation->expiring_date ?: '—' }}
                                                @if(!empty($quotation->expiring_date) && \Carbon\Carbon::parse($quotation->expiring_date)->isPast())
                                                    <span class="badge badge-danger ml-1">Expired</span>
                                                @endif
                                            </td>
                                            <td>{{ $quotation->customer }}</td>
                                            <td>{{ $quotation->contact }}</td>
                                            <td>{{ $quotation->prepared_by_name }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format((float) $quotation->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-file-document-edit-outline text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No quotations found</h5>
                            <p class="text-muted mb-0">
                                @if($isPrepStage)
                                    Create a quotation to get started.
                                @else
                                    Nothing matches this stage yet.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

<livewire:billing.create-enquiry-from-quotation-wizard />

@include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers, 'labSections' => $labSections ?? collect()])

<style>
    .quotation-index-page .quotation-stage-tabs-card {
        border-radius: 12px;
        overflow: hidden;
    }

    .quotation-index-page .quotation-stage-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .quotation-index-page .quotation-stage-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.5rem 0.9rem;
        border-radius: 8px;
        border: 1px solid var(--ls-color-border, #e2e8f0);
        background: #fff;
        color: var(--ls-color-muted, #64748b);
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .quotation-index-page .quotation-stage-tab:hover {
        border-color: var(--color-primary-border-soft, #e2b4b4);
        color: var(--color-primary, #6D0A0E);
        text-decoration: none;
    }

    .quotation-index-page .quotation-stage-tab.is-active {
        border-color: var(--color-primary, #6D0A0E);
        color: var(--color-primary, #6D0A0E);
        background: var(--color-primary-soft, #f8ecec);
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
    }

    .quotation-index-page .quotation-stage-tab--complete.is-active {
        border-color: #15803d;
        color: #15803d;
        background: #ecfdf5;
    }

    .quotation-index-page .quotation-stage-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.35rem;
        padding: 2px 6px;
        border-radius: 999px;
        font-size: 0.6875rem;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
    }

    .quotation-index-page .quotation-stage-tab.is-active .quotation-stage-tab__count {
        background: rgba(109, 10, 14, 0.12);
        color: var(--color-primary, #6D0A0E);
    }

    .quotation-index-page .quotation-stage-tab--complete.is-active .quotation-stage-tab__count {
        background: #dcfce7;
        color: #166534;
    }

    .quotation-index-page .quotation-type-chip,
    .quotation-index-page .quotation-status-chip {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .quotation-index-page .quotation-type-chip--analysis {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .quotation-index-page .quotation-type-chip--general {
        background: #ecfeff;
        color: #0e7490;
        border-color: #a5f3fc;
    }

    .quotation-index-page .quotation-status-chip--prep {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }

    .quotation-index-page .quotation-status-chip--approval {
        background: #eef2ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }

    .quotation-index-page .quotation-status-chip--complete {
        background: #ecfdf5;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .quotation-index-page .quotation-drafts-menu {
        max-height: 70vh;
        width: 280px;
        overflow: auto;
    }

    .quotation-index-page .quotation-draft-item {
        border-radius: 8px;
        white-space: normal;
    }

    .quotation-index-page .quotation-draft-item + .quotation-draft-item {
        margin-top: 4px;
    }

    .quotation-index-page .quotation-quote-number {
        color: inherit;
        font-weight: 700;
        text-decoration: none;
    }

    .quotation-index-page .quotation-quote-number:hover {
        color: var(--color-primary, #6D0A0E);
        text-decoration: none;
    }

    .quotation-index-page .quotation-actions-cell {
        gap: 0.45rem;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .quotation-index-page .rm-act-btn {
        border-radius: 7px;
        padding: 4px 8px;
        margin-right: 0;
        font-size: 12px;
        border: 1px solid transparent;
        background: #fff;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: none;
    }

    .quotation-index-page .rm-act-btn--open {
        border-color: #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    .quotation-index-page .rm-act-btn--open:hover {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
        text-decoration: none;
    }

    .quotation-index-page .rm-act-btn--preview {
        border-color: #c4b5fd;
        color: #6d28d9;
        background: #f5f3ff;
    }

    .quotation-index-page .rm-act-btn--preview:hover {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #5b21b6;
        text-decoration: none;
    }

    .quotation-index-page .rm-act-btn--view {
        border-color: #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    .quotation-index-page .rm-act-btn--view:hover {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
        text-decoration: none;
    }

    .quotation-index-page .rm-act-btn--edit {
        border-color: #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .quotation-index-page .rm-act-btn--edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
        color: #1e40af;
        text-decoration: none;
    }

    .quotation-index-page .rm-act-btn--clone {
        border-color: #c4b5fd;
        color: #6d28d9;
        background: #f5f3ff;
    }

    .quotation-index-page .rm-act-btn--clone:hover {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #5b21b6;
        text-decoration: none;
    }

    .quotation-index-page .rm-act-btn--enquiry {
        border-color: #fed7aa;
        color: #c2410c;
        background: #fff7ed;
        cursor: pointer;
    }

    .quotation-index-page .rm-act-btn--enquiry:hover {
        background: #ffedd5;
        border-color: #fdba74;
        color: #9a3412;
    }
</style>
@endsection

@section('script2')
<script>
    $(function () {
        $('#quote_type').on('change', function () {
            var quoteType = $(this).val();
            if (quoteType === 'General') {
                $('.sample_type_field').addClass('d-none');
                $('.analysis_type_field').addClass('d-none');
                $('.item_description_field').removeClass('d-none');
            } else if (quoteType === 'Analysis') {
                $('.sample_type_field').removeClass('d-none');
                $('.analysis_type_field').removeClass('d-none');
                $('.item_description_field').addClass('d-none');
            } else {
                $('.sample_type_field, .analysis_type_field, .item_description_field').addClass('d-none');
            }
        });

    });
</script>
@include('layouts.lab.invoice.partials.add-quotation-modal-scripts')
@endsection
