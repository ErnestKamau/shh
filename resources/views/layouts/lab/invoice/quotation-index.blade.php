@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Lab-Quotations </title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    .tab-card-header>.nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header>.nav-tabs>li {
        margin-right: 2px;
    }

    .tab-card-header>.nav-tabs>li>a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 15px;
    }

    .tab-card-header>.nav-tabs>li>a.show {
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header>.nav-tabs>li>a:hover {
        color: #007bff;
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header>.tab-content {
        padding-bottom: 0;
    }

    .quotations-index-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 1rem;
    }

    .quotations-index-toolbar h4 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 600;
    }

    .quotations-index-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quotations-index-table-wrap {
        overflow-x: auto;
    }

    .quotations-index-table {
        width: 100% !important;
        margin-bottom: 0;
    }

    .quotations-index-table thead th,
    table.dataTable.quotations-index-table thead th,
    table.dataTable.quotations-index-table thead td {
        vertical-align: middle !important;
        white-space: nowrap;
        padding: 0.55rem 0.7rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .quotations-index-table tbody td,
    table.dataTable.quotations-index-table tbody td {
        vertical-align: middle !important;
        padding: 0.55rem 0.7rem !important;
    }

    .quotations-index-table .quote-no-link {
        font-weight: 600;
        color: inherit;
        text-decoration: none;
    }

    .quotations-index-table .quote-no-link:hover {
        color: var(--color-primary, #7a1f2b);
        text-decoration: underline;
    }

    .quotations-index-table .quote-total {
        font-weight: 600;
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .quotations-index-table .quote-actions {
        display: inline-flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 4px;
        white-space: nowrap;
    }

    .quotations-index-table .quote-actions .btn {
        padding: 0.2rem 0.45rem;
        line-height: 1.2;
    }

    .quotations-index-table .workflow-status-chip {
        border-color: color-mix(in srgb, var(--chip-accent, #e2e8f0) 35%, #e2e8f0);
        color: var(--chip-accent, #475569);
        background: color-mix(in srgb, var(--chip-accent, #f1f5f9) 12%, #f8fafc);
    }
</style>
@endsection
@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme workflow-theme">
    @include('layouts.lab.partials.lab-panel-theme-styles')
    <?php
    $items = array(
        array(
            'link' => '/lab-dashboard',
            'name' => 'Dashboard',
            'icon' => null
        ),


        array(
            'link' => '/billing-quotation',
            'name' => 'Quotations',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $stage,
            'icon' => null
        ),

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')
    <div class="quotations-index-toolbar">
        <h4>
            <i class="mdi mdi-file-cad"></i> Billing | Quotations — {{ $stage }}
        </h4>
        <div class="quotations-index-actions">
            <div class="dropleft">
                <button type="button" class="btn btn-sm btn-light border dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Drafts
                    <span class="badge badge-pill badge-primary ml-1">{{ $drafts->count() }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right p-2" style="max-height: 70vh; min-width: 280px; overflow: auto;">
                    @forelse($drafts as $draft)
                        <a class="dropdown-item rounded mb-1" href="{{ route('add-qoute-details-view', ['id' => $draft->id, 'stage' => $draft->status]) }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <span>{{ $draft->quote_number }}</span>
                                <small class="text-muted ml-2">{{ optional($draft->created_at)->format('Y-m-d') ?? $draft->created_at }}</small>
                            </div>
                        </a>
                    @empty
                        <span class="dropdown-item text-muted disabled">No drafts</span>
                    @endforelse
                </div>
            </div>
            @if($stage == 'Quote In Preparation')
                <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#add-quotation">
                    <i class="mdi mdi-plus"></i> Add
                </button>
            @endif
        </div>
    </div>
    @if($stage == 'All Quotations' && !empty($metrics))
        @include('layouts.lab.invoice.partials.quotation-metrics', ['metrics' => $metrics, 'kpiPeriod' => $kpiPeriod ?? null])
    @endif
    @if($stage == 'All Quotations' )
    <div class="filter">
        <div class="p-3"> <u><b>Apply Filter ?</b></u</div>
        <div class="card mb-5">
            <form action="{{route('filterQuotations')}}" method="POST">
                <div class="card-body">
                    @csrf
                    <div class="row">
    
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Quote Type</label>
                                <select name="quote_type" id="quote_type" class="form-control">
                                    <option value="">Choose Quotation Type</option>
                                    <option value="General">General Quotation</option>
                                    <option value="Analysis">Analysis Quotation</option>
                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 sample_type_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Sample Type</label>
                                <select name="sample_type_id" id="sample_type_id" class="form-control">
                                    @foreach($sample_types ?? [] as $st)
                                    <option value="{{$st->id}}">{{$st->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 analysis_type_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Analysis Type</label>
                                <select name="analysis_type_id" id="analysis_type_id" class="form-control"></select>
                            </div>
                        </div>
                      
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">End Date</label>
                                <input type="date" name="end_date" id="" class="end_date form-control">
                            </div>
                        </div>
                        <div class="col-md-12 bg-light p-2 item_description_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Item Description</label>
                                <textarea class=" form-control" value="" name="item_description" rows="1" placeholder=""> </textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-filter-variant-plus"></i> Apply Filter</button>
                </div>
            </form>

        </div>
    </div>
    @endif

    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-file-cad"></i> Quotations</h5>
        </div>
        <div class="workflow-board-panel-body p-0">
            <div class="quotations-index-table-wrap table-responsive">
                <table id="quotations-index-table" class="table workflow-table table-hover quotations-index-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 48px;">#</th>
                            <th>Quote No</th>
                            <th>Type</th>
                            @if($stage == 'All Quotations')
                                <th>Status</th>
                            @endif
                            <th>Quote Date</th>
                            <th>Expiry</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Prepared By</th>
                            <th class="text-right">Total</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            @php
                                $quoteDate = $quotation->quote_date ? \Carbon\Carbon::parse($quotation->quote_date)->format('Y-m-d') : '—';
                                $expiryDate = $quotation->expiring_date ? \Carbon\Carbon::parse($quotation->expiring_date) : null;
                                $typeAccent = $quotation->quotation_type === 'Analysis' ? '#2563eb' : '#0891b2';
                                $statusAccent = $quotation->status === 'Quote Complete' ? '#15803d' : '#b45309';
                            @endphp
                            <tr class="quotation-preview-hover-parent">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a class="quote-no-link" href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}">
                                        {{ $quotation->quote_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="workflow-status-chip" style="--chip-accent: {{ $typeAccent }};">
                                        {{ $quotation->quotation_type ?: '—' }}
                                    </span>
                                </td>
                                @if($stage == 'All Quotations')
                                    <td>
                                        <span class="workflow-status-chip" style="--chip-accent: {{ $statusAccent }};">
                                            {{ $quotation->status }}
                                        </span>
                                    </td>
                                @endif
                                <td>{{ $quoteDate }}</td>
                                <td>
                                    @if($expiryDate)
                                        {{ $expiryDate->format('Y-m-d') }}
                                        @if($expiryDate->isPast())
                                            <span class="badge badge-danger ml-1">Expired</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $quotation->customer ?: '—' }}</td>
                                <td>{{ $quotation->contact }}</td>
                                <td>{{ $quotation->prepared_by_name }}</td>
                                <td class="quote-total">{{ number_format((float) ($quotation->total_amount ?? 0), 2) }}</td>
                                <td>
                                    <div class="quote-actions">
                                        <a href="{{ route('add-qoute-details-view', ['id' => $quotation->id]) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           data-toggle="tooltip"
                                           title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <a href="{{ route('quotation.preview', ['id' => $quotation->id]) }}"
                                           class="btn btn-sm quotation-preview-quote-btn"
                                           data-toggle="tooltip"
                                           title="Preview Quote"
                                           target="_blank">
                                            <i class="mdi mdi-file-eye"></i>
                                        </a>
                                        <a href="{{ route('clone_quotation', ['id' => $quotation->id]) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           data-toggle="tooltip"
                                           title="Clone">
                                            <i class="mdi mdi-content-duplicate"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $stage == 'All Quotations' ? 11 : 10 }}" class="text-center text-muted py-4">
                                    No quotations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>
@include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers])
@endsection
@section('script2')
<script>
    $(function() {
        $('#quote_type').on('change',(e)=>{
            $quote_type = $('#quote_type').val();
            if($quote_type == 'General'){
                $('.sample_type_field').addClass('hidden');
                $('.analysis_type_field').addClass('hidden');
                $('.item_description_field').removeClass('hidden');
            }else if($quote_type == 'Analysis'){
                
                $('.sample_type_field').removeClass('hidden');
                $('.analysis_type_field').removeClass('hidden');
                $('.item_description_field').addClass('hidden');
            }
            
            
        });
        $('#sample_type_id').on('change',(e)=>{
            $value  = $('#sample_type_id').val()
        })

        if ($.fn.DataTable && $('#quotations-index-table').length && $('#quotations-index-table tbody tr td[colspan]').length === 0) {
            var $table = $('#quotations-index-table');
            if (!$.fn.DataTable.isDataTable($table)) {
                $table.DataTable({
                    autoWidth: false,
                    order: [[0, 'asc']],
                    pageLength: 25,
                    columnDefs: [
                        { orderable: false, targets: -1 },
                        { className: 'text-right', targets: -2 },
                    ],
                    language: {
                        emptyTable: 'No quotations found.',
                    },
                });
            }
        }
    });
</script>
@include('layouts.lab.invoice.partials.add-quotation-modal-scripts')


@endsection
