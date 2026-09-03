@extends('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Equipment Report</title>
<style type="text/css">
    .eq-report-card {
        background: #fff !important;
        border: 1px solid #e6e8eb;
        box-shadow: none;
    }

    .eq-report-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eef0f2;
    }

    .eq-report-logo {
        max-width: 180px;
        max-height: 56px;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .eq-report-meta {
        text-align: right;
        font-size: 12px;
        color: #4b5563;
        line-height: 1.6;
        white-space: nowrap;
    }

    .eq-report-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 0.75rem;
        padding: 0.25rem 0 0.75rem;
    }

    .eq-report-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        border-radius: 4px;
        font-size: 12px;
        color: #374151;
    }

    .eq-report-filter strong {
        font-weight: 600;
        color: #111827;
    }

    .eq-report-table {
        width: 100% !important;
        font-size: 12px;
        margin-bottom: 0;
    }

    .eq-report-table th {
        white-space: nowrap;
        vertical-align: middle;
        background: #f3f4f6;
        font-weight: 600;
    }

    .eq-report-table td {
        vertical-align: middle;
    }

    .eq-report-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 600;
    }

    .eq-report-status.is-active {
        color: #15803d;
    }

    .eq-report-status.is-disposed {
        color: #b91c1c;
    }

    .eq-report-due {
        white-space: nowrap;
    }

    .eq-report-due .badge {
        font-weight: 500;
    }

    .eq-report-group-row td {
        background: #1f2937 !important;
        color: #fff !important;
        font-weight: 600;
    }

    .eq-report-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: #6b7280;
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = [
        [
            'link' => '/equipment-home',
            'name' => 'Equipment',
            'icon' => null,
        ],
        [
            'link' => route('equipment-report-generate'),
            'name' => 'Reports',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => ucwords(str_replace('_', ' ', $filter['report_name'])),
            'icon' => null,
        ],
    ];

    $hiddenFilters = ['_token', 'equipment_id'];
    $isGrouped = ($filter['group_by'] ?? 'none') !== 'none';
    $hasRows = $isGrouped ? collect($equipment_data)->flatten(1)->isNotEmpty() : collect($equipment_data)->isNotEmpty();
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="d-flex align-items-center justify-content-between px-4 pt-3 pb-2">
        <h5 class="mb-0">
            <i class="mdi mdi-clipboard-text-multiple-outline"></i>
            Equipment Reports | {{ ucwords(str_replace('_', ' ', $filter['report_name'])) }}
        </h5>
        <a href="{{ route('equipment-report-generate') }}" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-filter-variant"></i> Change Filters
        </a>
    </div>

    <div class="card eq-report-card mx-3 mb-4">
        <div class="eq-report-header">
            <img src="{{ $company->logo }}" alt="{{ $company->name ?? 'Company' }}" class="eq-report-logo">
            <div class="eq-report-meta">
                <div><b>Date:</b> {{ getTodayDate() }}</div>
                <div><b>Email:</b> {{ $company->email }}</div>
            </div>
        </div>

        <div class="card-body">
            <div class="mb-2"><b>Report Filters</b></div>
            <div class="eq-report-filters">
                @foreach ($filter as $k => $v)
                    @if (! in_array($k, $hiddenFilters, true) && $v !== null && $v !== '')
                        <span class="eq-report-filter">
                            <strong>{{ ucwords(str_replace('_', ' ', $k)) }}:</strong>
                            {{ ucwords(str_replace('_', ' ', (string) $v)) }}
                        </span>
                    @endif
                @endforeach
            </div>

            <hr class="mt-2 mb-3">

            @if (! $hasRows)
                <div class="eq-report-empty">
                    No equipment matched these filters. Adjust the filters and generate the report again.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-sm table-bordered eq-report-table">
                        <thead>
                            <tr>
                                @foreach ($theads as $thead)
                                    <th>{{ $thead }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($equipment_data as $key => $value)
                                @if ($isGrouped)
                                    <tr class="eq-report-group-row">
                                        <td>Group</td>
                                        <td colspan="{{ max(count($theads) - 1, 1) }}">{{ $key }}</td>
                                    </tr>
                                    @foreach ($value as $data)
                                        @include('layouts.equipment.reports.partials.row', [
                                            'row' => $data,
                                            'reportName' => $filter['report_name'],
                                        ])
                                    @endforeach
                                @else
                                    @include('layouts.equipment.reports.partials.row', [
                                        'row' => $value,
                                        'reportName' => $filter['report_name'],
                                    ])
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</main>
@endsection

@section('script2')
@endsection
