@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> Sample Report</title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
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

    .my-small-text {
        font-size: 13px !important;
    }

    .removeThis {
        z-index: 12;
        position: absolute;
        cursor: pointer;
        top: 0px;
        right: 2px;
        padding: 1px 4px;
        font-size: 12px;
        background-color: red;
        border-radius: 50%;
        color: #fff;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
    }
</style>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null
        ),
        array(
            'link' => route('lab-reports-home'),
            'name' => 'Reports',
            'icon' => null
        ),
        array(
            'link' => '/lab/reports-home',
            'name' => ucwords(str_replace('_', ' ', $filter['report_name'])),
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
        <i class="mdi mdi-clipboard-text-multiple-outline"></i> Laboratory Reports | {{ucwords(str_replace('_', ' ', $filter['report_name']))}}
        <span class="btn-btn-default btn-sm float-right"><i class="mdi mdi-printer"></i> Print Report</span>
    </h3>
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <img src="{{$company->logo}}" style="width: 200px;height:50px;" alt="">
                <span class="float-right">
                    <b>Date: </b>{{getTodayDate()}} <br>
                    <b>Email: </b>{{$company->email}}
                </span>
            </span>
        </div>
        <div class="card-body">
            <b><u>Report Filters</u></b>
            <div class="row p-2">
                @foreach($filter as $k=>$v)
                @if(!in_array($k,$filter_remove))
                
                <div class="col-md-3 col-lg-3 col-sm-6 mt-2">
                    <?php
                    $key = ucwords(str_replace('_', ' ', $k));
                    $value = ucwords(str_replace('_', ' ', $v));
                    ?>
                     <i class="mdi mdi-chevron-double-right"></i> {{$key}}: {{$value}}

                </div>
                @endif
                @endforeach
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table table-condensed table-hover table-bordered table-sm table-stripped" style="width: 140%;">
                    <thead class="bg-light">
                        <tr>
                            @foreach($theads as $thead)
                            <th>{{$thead}}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $key=>$data)
                            @if($filter['group_by'] == 'none' && $filter['report_name'] != 'profit_report')
                                @if($filter['report_name'] == 'batch_report')
                                <tr>
                                    <td>{{$data->receipt_date}}</td>
                                    <td>{{$data->batch_code}}</td>
                                    <td>{{$data->crm_name}}</td>
                                    <td>{{$data->submit_by}}</td>
                                    <td>{{$data->date_collected}}</td>
                                    <td>{{$data->approval_date}}</td>
                                    <td>{{$data->batch_scope}}</td>
                                    <td>{{$data->customer_survey}}</td>
                                    <td>{{$data->invoice_number}}</td>
                                    <td>{{$data->sample_type_name}}</td>
                                    <td>{{$data->workflow_stage}}</td>
                                    <td>{{$data->priority}}</td>
                                    
                                </tr>
                                @endif
                                @if($filter['report_name'] == 'sample_report')
                                <tr>
                                    <td>{{$data->receipt_date}}</td>
                                    <td>{{$data->sample_code}}</td>
                                    <td>{{$data->crm_name}}</td>
                                    <td>{{$data->submit_by}}</td>
                                    <td>{{$data->sample_type_name}}</td>
                                    <td>{{$data->analysis_name}}</td>
                                    <td>{{$data->analyte_name}}</td>
                                    <td>{{$data->date_collected}}</td>
                                    <td>{{$data->invoice_number}}</td>
                                    <td>{{$data->batch_scope}}</td>
                                    <td>{{$data->workflow_stage}}</td>
                                    <td>{{$data->priority}}</td>
                                </tr>
                                @endif
                            @else
                                @if($filter['report_name'] =='profit_report')
                                    <tr class="bg-dark" style="color:white;font-size:10px">
                                        <td>Group By</td>
                                        <td>{{$key}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style="text-align: right !important;">Cost Price : {{array_sum($data['cost_price'])}}</td>
                                        <td style="text-align: right !important;">Total Tax : {{array_sum($data['tax'])}}</td>
                                        <td style="text-align: right !important;">total Selling Price : {{array_sum($data['selling_price'])}}</td>
                                        <td style="text-align: right !important;">Total Profit : {{array_sum($data['profit'])}} </td>
                                    </tr>
                                    @foreach($data['samples'] as $d)
                                    <tr>
                                        <td>{{$d->sample_code}}</td>
                                        <td style="width: 20%;">{{getSampleTypeByID($d->sample_type_id)->name}}</td>
                                        <td style="width:10%">{{getCrmCustomerByID($d->crm_customer_id)->name}}</td>
                                        <td>{{$d->submit_by}}</td>
                                        <td style="width: 15%;">{{getAnalysisTypeID($d->analysis_type)->name}}</td>
                                        <td>{{getInvoiceById($d->invoice_id)->invoice_number ?? '-'}}</td>
                                        <td style="width: 15%;text-align: right !important;">{{$d->cost_price}}</td>
                                        <td style="width: 8%;text-align: right !important;">{{$d->tax_amount}}</td>
                                        <td style="width: 10%;text-align: right !important;">{{$d->selling_price}}</td>
                                        <td style="width: 15%;text-align: right !important;">{{$d->selling_price - $d->cost_price}}</td>
                                    </tr>
                                    @endforeach

                                @endif
                                @if($filter['report_name'] == 'batch_report')
                                    <tr class="bg-dark" style="color:white;font-weight:600">
                                        <td>Group By</td>
                                        <td>{{$key}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    @foreach($data as $d)
                                    <tr>
                                        <td>{{$d->batch_code}}</td>
                                        <td>{{$d->crm_name}}</td>
                                        <td>{{$d->sample_type_name}}</td>
                                        <td>{{$d->no_of_samples}}</td>
                                        <td>{{$d->workflow_stage}}</td>
                                        <td>{{$d->priority}}</td>
                                    </tr>
                                    @endforeach
                                @endif
                                @if($filter['report_name'] == 'sample_report')
                                    <tr class="bg-dark" style="color:white;font-weight:600">
                                        <td>Group By</td>
                                        <td>{{$key}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    @foreach($data as $d)
                                    <tr>
                                        <td>{{$d->sample_code}}</td>
                                        <td>{{$d->crm_name}}</td>
                                        <td>{{$d->sample_type_name}}</td>
                                        <td>{{$d->analysis_name}}</td>
                                        <td>{{$d->analyte_name}}</td>
                                        <td>{{$d->workflow_stage}}</td>
                                        <td>{{$d->priority}}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            @endif
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