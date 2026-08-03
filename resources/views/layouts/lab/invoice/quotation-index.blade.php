@extends('layouts.lab.layout.app', ['dataTable' => false, 'select2' => true])

@section('title2')
<title>Lab — Quotations</title>
@endsection

@section('content2')
@php
    $stageCounts = $stageCounts ?? [
        'all' => $quotations->count(),
        'Quote In Preparation' => 0,
        'Quote Complete' => 0,
    ];
    $isAllStage = $stage === 'All Quotations';
    $isPrepStage = $stage === 'Quote In Preparation';
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
                                           href="{{ route('add-qoute-details-view', ['id' => $draft->id, 'stage' => $draft->status]) }}">
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
                                                    <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id, 'stage' => 'Quote In Reception']) }}"
                                                       class="rm-act-btn rm-act-btn--edit"
                                                       title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('quotation.preview', ['id' => $quotation->id]) }}"
                                                       class="rm-act-btn rm-act-btn--view quotation-preview-quote-btn"
                                                       title="Preview quotation"
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
                                                                    data-toggle="modal"
                                                                    data-target="#create-enquiry-from-quotation"
                                                                    data-quote-id="{{ $quotation->id }}"
                                                                    data-quote-number="{{ $quotation->quote_number }}"
                                                                    data-customer="{{ $quotation->customer }}"
                                                                    data-sample-count="{{ max(1, (int) ($quotationSampleCounts[$quotation->id] ?? 1)) }}"
                                                                    data-creation-token="{{ \Illuminate\Support\Str::uuid() }}">
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
                                            @if($isAllStage)
                                                <td>
                                                    <span class="quotation-status-chip {{ $quotation->status === 'Quote Complete' ? 'quotation-status-chip--complete' : 'quotation-status-chip--prep' }}">
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

<div class="modal fade" id="create-enquiry-from-quotation" tabindex="-1" role="dialog" aria-labelledby="create-enquiry-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="create-enquiry-title">
                        <i class="mdi mdi-flask-outline text-primary"></i>
                        Create Enquiry from <span data-enquiry-quote-number></span>
                    </h5>
                    <small class="text-muted" data-enquiry-customer></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('quotation.create-enquiry') }}">
                @csrf
                <input type="hidden" name="quotation_id" value="{{ old('quotation_id') }}">
                <input type="hidden" name="creation_token" value="{{ old('creation_token') }}">

                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-start" style="gap: 10px;">
                        <i class="mdi mdi-auto-fix mt-1"></i>
                        <div>
                            Tests, parameters, pricing and the Test Request Form will be prefilled.
                            Confirm the physical sample count and add the available request information.
                        </div>
                    </div>

                    @if(old('quotation_id') && $errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="enquiry-creation-intent">Workflow start <span class="text-danger">*</span></label>
                        <select id="enquiry-creation-intent"
                                name="creation_intent"
                                class="form-control @error('creation_intent') is-invalid @enderror">
                            <option value="prepare" @selected(old('creation_intent', 'prepare') === 'prepare')>
                                Prepare for sending
                            </option>
                            <option value="already_sent" @selected(old('creation_intent') === 'already_sent')>
                                Quotation already sent
                            </option>
                            <option value="accepted" @selected(old('creation_intent') === 'accepted')>
                                Customer already accepted
                            </option>
                        </select>
                        @error('creation_intent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Accepted quotations move directly to Ready for Reception after TRF completion.
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="enquiry-number-of-samples">Physical samples <span class="text-danger">*</span></label>
                                <input id="enquiry-number-of-samples"
                                       type="number"
                                       name="number_of_samples"
                                       min="1"
                                       max="10000"
                                       value="{{ old('number_of_samples', 1) }}"
                                       class="form-control @error('number_of_samples') is-invalid @enderror"
                                       required>
                                @error('number_of_samples')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    For multi-sample-type quotes, each type keeps its quotation quantity.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="enquiry-reference-number">Customer reference</label>
                                <input id="enquiry-reference-number"
                                       type="text"
                                       name="reference_number"
                                       value="{{ old('reference_number') }}"
                                       class="form-control @error('reference_number') is-invalid @enderror"
                                       placeholder="Optional">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="enquiry-date-expected">Expected sample date</label>
                                <input id="enquiry-date-expected"
                                       type="date"
                                       name="date_expected"
                                       value="{{ old('date_expected') }}"
                                       class="form-control @error('date_expected') is-invalid @enderror">
                                @error('date_expected')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row enquiry-accepted-fields d-none">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="enquiry-client-po-number">Customer PO</label>
                                <input id="enquiry-client-po-number"
                                       type="text"
                                       name="client_po_number"
                                       value="{{ old('client_po_number') }}"
                                       class="form-control @error('client_po_number') is-invalid @enderror"
                                       placeholder="Optional if PO can be skipped">
                                @error('client_po_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block">&nbsp;</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="enquiry-po-skipped"
                                           name="po_skipped"
                                           value="1"
                                           @checked(old('po_skipped'))>
                                    <label class="custom-control-label" for="enquiry-po-skipped">
                                        Skip PO for this enquiry
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="enquiry-sample-description">Sample description</label>
                        <textarea id="enquiry-sample-description"
                                  name="sample_description"
                                  rows="2"
                                  class="form-control @error('sample_description') is-invalid @enderror"
                                  placeholder="Optional description shared by the quoted samples">{{ old('sample_description') }}</textarea>
                        @error('sample_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <label for="enquiry-notes">Internal enquiry notes</label>
                        <textarea id="enquiry-notes"
                                  name="enquiry_notes"
                                  rows="2"
                                  class="form-control @error('enquiry_notes') is-invalid @enderror"
                                  placeholder="Optional">{{ old('enquiry_notes') }}</textarea>
                        @error('enquiry_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-auto-fix"></i>
                        Create &amp; Prefill Enquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers])

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

        var enquiryModal = $('#create-enquiry-from-quotation');
        var enquiryForm = enquiryModal.find('form');
        var validationQuotationId = @json((string) old('quotation_id', ''));

        function populateEnquiryModal(button, preserveValues) {
            var quoteId = String(button.data('quote-id') || '');

            enquiryModal.find('[data-enquiry-quote-number]').text(button.data('quote-number') || '');
            enquiryModal.find('[data-enquiry-customer]').text(button.data('customer') || '');
            enquiryForm.find('[name="quotation_id"]').val(quoteId);
            if (!preserveValues || !enquiryForm.find('[name="creation_token"]').val()) {
                enquiryForm.find('[name="creation_token"]').val(button.data('creation-token') || '');
            }

            if (!preserveValues) {
                enquiryForm.find('[name="number_of_samples"]').val(button.data('sample-count') || 1);
                enquiryForm.find('[name="reference_number"]').val('');
                enquiryForm.find('[name="date_expected"]').val('');
                enquiryForm.find('[name="sample_description"]').val('');
                enquiryForm.find('[name="enquiry_notes"]').val('');
                enquiryForm.find('[name="creation_intent"]').val('prepare');
                enquiryForm.find('[name="client_po_number"]').val('');
                enquiryForm.find('[name="po_skipped"]').prop('checked', false);
            }

            toggleAcceptedFields();
        }

        function toggleAcceptedFields() {
            var intent = enquiryForm.find('[name="creation_intent"]').val();
            enquiryForm.find('.enquiry-accepted-fields').toggleClass('d-none', intent !== 'accepted');
        }

        enquiryForm.find('[name="creation_intent"]').on('change', toggleAcceptedFields);

        enquiryModal.on('show.bs.modal', function (event) {
            var trigger = $(event.relatedTarget);
            if (!trigger.length) {
                return;
            }

            var preserveValues = validationQuotationId !== ''
                && String(trigger.data('quote-id')) === validationQuotationId;
            populateEnquiryModal(trigger, preserveValues);
        });

        if (validationQuotationId !== '') {
            var validationButton = $('.create-enquiry-button').filter(function () {
                return String($(this).data('quote-id')) === validationQuotationId;
            }).first();

            if (validationButton.length) {
                populateEnquiryModal(validationButton, true);
                enquiryModal.modal('show');
            }
        }
    });
</script>
@include('layouts.lab.invoice.partials.add-quotation-modal-scripts')
@endsection
