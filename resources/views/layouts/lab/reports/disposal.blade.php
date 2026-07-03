@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title>Disposal | Lab Reports</title>
<style>
    .hidden {
        display: none;
    }
</style>
@endsection

@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme workflow-theme">
    @include('layouts.lab.partials.lab-panel-theme-styles')
    <?php
    $items = [
        ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
        ['link' => route('lab-report-disposal'), 'name' => 'Disposal Reports', 'icon' => null],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="row workflow-board-header mb-3">
        <div class="col-12">
            <div class="batch-header-bar">
                <div class="batch-header-top">
                    <div class="batch-title-group">
                        <i class="mdi mdi-delete-sweep" style="font-size:1.2rem;"></i>
                        <span class="batch-code-label">Disposal Lab Reports</span>
                    </div>
                </div>
                <p class="text-muted small mb-0" style="color: rgba(255,255,255,0.82) !important;">Track sample disposal dates, storage locations, and overdue items.</p>
            </div>
        </div>
    </div>

    <div class="workflow-board-panel mb-3">
        <div class="workflow-board-panel-header">
            <h6><i class="mdi mdi-filter-variant"></i> Filters</h6>
            <button type="submit" form="disposal-report-filters" class="btn btn-sm btn-primary">
                <i class="mdi mdi-filter"></i> Apply
            </button>
        </div>
        <div class="workflow-board-panel-body">
            <form action="/sample-workflow/Finished Sample" id="disposal-report-filters" method="get">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="control-label">Disposal Date From</label>
                            <input type="date" name="date_from" value="{{ $filter['date_from'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="control-label">Disposal Date To</label>
                            <input type="date" name="date_to" value="{{ $filter['date_to'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="control-label">Customer</label>
                            <select name="customer_id" class="form-control">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ isset($filter['customer_id']) && $customer->id == $filter['customer_id'] ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="control-label">Sample Types</label>
                            <select name="sample_type_id" class="form-control">
                                <option value="">Select Sample Type</option>
                                @foreach ($sampletypes as $s_type)
                                    <option value="{{ $s_type->id }}" {{ isset($filter['sample_type_id']) && $filter['sample_type_id'] == $s_type->id ? 'selected' : '' }}>{{ $s_type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="control-label">Store</label>
                            <select name="store_id" class="form-control">
                                <option value="">Select Stores</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store['id'] }}" {{ isset($filter['store_id']) && $filter['store_id'] == $store['id'] ? 'selected' : '' }}>{{ $store['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="has_filter" value="1">
            </form>
        </div>
    </div>

    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h6><i class="mdi mdi-table"></i> Disposal Report</h6>
            <span class="text-muted small">{{ count($data) }} sample(s)</span>
        </div>
        <div class="workflow-board-panel-body p-0">
            <div class="table-responsive">
                <table class="table workflow-table table-hover mb-0" data-fixedcls="{{ json_encode(['left' => 3]) }}">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Sample Code</th>
                            <th>Disposal Date</th>
                            <th>Status Days</th>
                            <th>Store</th>
                            <th>Slot</th>
                            <th>Sample Types</th>
                            <th>Analysis Types</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $sample)
                            <tr class="{{ $sample->disposal_date < date('Y-m-d') ? 'table-danger' : '' }}">
                                <td></td>
                                <td class="font-weight-bold">{{ $sample->sample_code }}</td>
                                <td>{{ $sample->disposal_date }}</td>
                                <td>{{ $sample->disposal_date > date('Y-m-d') ? '+' . getDiffBtnDates($disposal_date, date('Y-m-d')) : '-' . getDiffBtnDates($disposal_date, date('Y-m-d')) }}</td>
                                <td>{{ $sample->store_name }}</td>
                                <td>{{ $sample->store_slot_name }}</td>
                                <td>{{ $sample->sample_type_name }}</td>
                                <td>{{ $sample->analysisTypeNames }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script2')
@endsection
