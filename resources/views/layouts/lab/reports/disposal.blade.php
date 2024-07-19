@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title> Disposal | Lab Reports</title>

<style>
    .form-part-toggler {
        margin: 0px 0px 5px 0px !important;
        padding: 6px 6px 6px 6px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.09);
        cursor: pointer;
    }

    .form-part-toggler:hover {
        background-color: rgba(0, 0, 0, 0.08);
    }

    #sample-detail-rows .form-group {
        display: none;
    }

    #sample-detail-rows tr.selected-row {
        background-color: rgb(253, 220, 220);
    }

    #sample-detail-rows .text {
        display: unset;
    }

    #sample-detail-rows tr.editable .form-group {
        display: unset;
    }

    #sample-detail-rows tr.editable .text {
        display: none;
    }

    #sample-detail-rows tr {
        cursor: pointer;
    }

    .hidden {
        display: none;
    }

    .overdue-bg-color {
        background-color: rgba(240, 185, 83, 0.972) !important;
    }

    .upfront-bg-color {
        background-color: skyblue !important;
    }

    .ammend-bg-color {
        background-color: #fef764 !important;
    }

    .btn-white {
        background-color: white !important;
    }
</style>
@endsection
@section('content2')
<main>
    <?php
$items = array(
    array(
        'link' => route('dashboard-lab'),
        'name' => 'Dashboard',
        'icon' => null
    ),
    array(
        'link' => route('lab-report-disposal'),
        'name' => 'Disposal Reports',
        'icon' => null
    ),

);
	?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h4 class="p-4">
        <span class="float-left"><i class="mdi mdi-file-document-edit"></i> Disposal Lab Reports</span>
    </h4>
    <div class="table-responsive bg-light p-4">

        <b><u>Apply Filters?</u></b>
        <form action="/sample-workflow/Finished Sample" class="mb-4" method="get">
            <div class="row mt-2 p-2 bg-white">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Disposal Date From</label>
                        <input type="date" name="date_from" id="" value="{{$filter['date_from'] ?? ''}}"
                            class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Disposal Date To</label>
                        <input type="date" name="date_to" id="" value="{{$filter['date_to'] ?? ''}}"
                            class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Customer</label>
                        <select name="customer_id" id="" class="form-control">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{$customer->id}}" {{isset($filter['customer_id']) && $customer->id == $filter['customer_id'] ? 'selected' : ''}}>{{$customer->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Sample Types</label>
                        <select name="sample_type_id" id="" class="form-control">
                            <option value="">Select Sample Type</option>
                            @foreach ($sampletypes as $s_type)
                                <option value="{{$s_type->id}}" {{isset($filter['sample_type_id']) && $filter['sample_type_id'] == $s_type->id ? 'selected' : '' }}>{{$s_type->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Store</label>
                        <select name="store_id" id="" class="form-control">
                            <option value="">Select Stores</option>
                            @foreach ($stores as $key => $store)
                                <option value="{{$store['id']}}" {{isset($filter['store_id']) && $filter['store_id'] == $store['id'] ? 'selected' : ''}}>{{$store['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="has_filter" value="1">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-outline-primary float-right"><i
                            class="mdi mdi-filter"></i> Apply</button>
                </div>
            </div>

        </form>

        <table class="table table-condensed my-small-text table-bordered table-sm"
            data-fixedcls="{{json_encode(["left" => 3])}}">
            <thead>
                <th></th>
                <th>Sample Code</th>
                <th>Disposal Date</th>
                <th>Status Days</th>
                <th>Store</th>
                <th>Slot</th>
                <th>Sample Types</th>
                <th>Analysis Types</th>
            </thead>
            <tbody>
                @foreach ($data as $sample)
                    <tr class="{{$sample->disposal_date < date('Y-m-d') ? 'bg-danger' : ''}}">
                        <td></td>
                        <td>{{$sample->sample_code}}</td>
                        <td>{{$sample->disposal_date}}</td>
                        <td>{{$sample->disposal_date > date('Y-m-d') ? '+' . getDiffBtnDates($disposal_date, date('Y-m-d')) : '-' . getDiffBtnDates($disposal_date, date('Y-m-d')) }}
                        </td>
                        <td>{{$sample->store_name}}</td>
                        <td>{{$sample->store_slot_name}}</td>
                        <td>{{$sample->sample_type_name}}</td>
                        <td>{{$sample->analysisTypeNames}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection

@section('script2')

@endsection