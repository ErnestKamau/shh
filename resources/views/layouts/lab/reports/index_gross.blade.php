@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Gross-Profit-Margin </title>
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

    .dt-buttons {
        display: none;
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
            'link' => null,
            'name' => 'Reports',
            'icon' => null
        ),

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="fas fa-money-bill-alt"></i> Lab Reports | Gross Profit Margin Report

    </h2>

    <div class="card mb-4 p-3">
        <div class="row">
            <div class="col-xl-6 col-sm-6">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-toggle="dropdown">
                        Choose Laboratory Report
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item " href="{{ route('lab-reports-home') }}">Batch Reports</a>
                        <a class="dropdown-item " href="{{ route('samples-reports-lab') }}">Samples Reports</a>
                        <a class="dropdown-item " href="{{ route('analysis-type-comparison-report') }}">Analysis Comparison Reports</a>
                        <a class="dropdown-item " href="{{ route('get-customer-comparison') }}">Customer Comparison Report</a>
                        <a class="dropdown-item" href="{{ route('gross-profit-home') }}">Gross Profit Margin Report</a>
                        <a href="{{ route('gross-index-analysis') }}" class="dropdown-item">Gross Profit Analysis Report</a>

                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-sm-6">
                <span class="btn btn-outline-warning float-right" id="add-filter"><i class="mdi mdi-plus"></i> Filter</span>
            </div>
        </div>
        <div class="card bg-light p-3 mt-5" style="font-size: 12px;" id="Filter-Form">
            <h5 style="font-size: 12px;" class="card-title">
                <small class="text-danger">*Choose filters to apply on your report.</small>
                <hr>
            </h5>
            <?php
            $clients = getClients();
            $sample_workflow = getSampleWorflowStages();
            $tracking_stages = getSampleTrackingStages();
            $sample_types = getSampleTypes();


            $analysis_types = getAnalysisTypes();
            ?>

            <form action="{{ route('filter-gross-index-data') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    <div class="row no-gutter">
                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Choose Client</label>
                                <select style="background-color: white;" name="client" id="client-ids" aria-placeholder="Choose Client...">
                                    <option value="all">All</option>
                                    @foreach($clients as $client)
                                    <option value="{{$client->id}}">{{$client->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Analysis Type</label>
                                <select name="analysis_type" style="background-color: white;" id="sample-types" aria-placeholder="Choose Sample Type...">
                                    <option value="all">All</option>
                                    @foreach($analysis_types as $type)
                                    <option value="{{$type->id}}">{{$type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Currency</label>
                                <select name="currency" style="background-color: white;" id="sample-workflow" aria-placeholder="Choose Sample Workflow...">
                                    
                                    @foreach($currencies as $currencys)
                                    <option value="{{$currencys->id}}">{{$currencys->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row no-gutter">


                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Sample Types</label>
                                <select name="sample_types" style="background-color: white;" id="sample-types" aria-placeholder="Choose Sample Type...">
                                    <option value="all">All</option>
                                    @foreach($sample_types as $type)
                                    <option value="{{$type->id}}">{{$type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Start Date <small class="text-danger">*takes a span of one year by default </small> </label>
                                <input type="date" style="background-color: white;" name="start_date" class="form-control">
                            </div>

                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">End Date</label>
                                <input type="date" style="background-color: white;" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-outline-success">Apply</button>
                </div>
            </form>
        </div>
    </div>
    <a href="#" class="btn btn-outline-dark float-right mr-4"><i class="mdi mdi-printer-check"></i> Print</a> <br><br><br>
    <?php
    $check = getSystemConfiguration('display_system_logo');
    $check_company_logo = getDefaultCompany()
    ?>
    @if(isset($samples))
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="height: 40px;">

                {!! $check[0]->value == "true" ? '<img src="/images/imara-sys.png" style="height:100%" class="float-right">' : '' !!}
                <center style="font-weight: 900;">Gross Profit Margin Reports </center>
                {!! $check_company_logo[0]->show_on_reports == 1 ? '<img src='.$check_company_logo[0]->logo.' style="height: 5.5%;position:absolute;top:0.2%" class="float-left" />':'' !!}
            </h4>

            <hr>
            <h5 class="mb-3 mt-4" style="margin-right: 10%; font-weight:600;font-size:15px">Report Filters: </h5>
            <div class="row no-gutters mb-5">
                <div class="col-xl-1 col-sm-2">

                </div>
                <div class="col-xl-11 col-sm-10">
                    <div class="row no-gutters">

                        <div class="col-xl-4 col-sm-6">
                            <p><b>Client: </b>{{$filter_data['client']}}</p>
                            <p><b>Analysis Type: </b>{{$filter_data['analysis_type']}}</p>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Currency: </b> {{$filter_data['currency']}}</p>
                            <p><b>Start Date: </b>{{$filter_data['start_date']}}</p>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Sample Type: </b>{{$filter_data['sample_type']}}</p>
                            <p><b>End Date: </b>{{$filter_data['end_date']}}</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">


            <div class="table-responsive">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                    <h5>Gross Profit Margin Reports</h5>
                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Batch Code</th>
                            <th>Invoice Number</th>
                            <th>Currency</th>
                            <th>Analysis Type</th>

                            <th>Samples Types</th>
                            <th>Customer</th>
                            <th>Quantity</th>
                            
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Profit</th>


                        </tr>
                    </thead>
                    <tbody style="">

                        @foreach($samples as $sample)
                        <tr>

                            <td>{{$loop->iteration}}</td>
                            <td>{{$sample->batch_code}}</td>
                            <td>{{$sample->invoice_number}}</td>
                            <td>{{$sample->currency}}</td>
                            <td>{{$sample->analysis_type_name}}</td>
                            <td>{{$sample->sample_type}}</td>
                            <td>{{$sample->customer}}</td>
                            <td>{{$sample->quantity}}</td>
                           
                            <td class="text-right"> {{number_format( $sample->cost_price, 2)}}</td>
                            <td class="text-right">{{number_format( $sample->selling_price,2)}}</td>
                            <td class="text-right">{{number_format( $sample->sp_bp, 2)}}</td>

                        </tr>

                        @endforeach

                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-lg-5 col-sm-6 ml-auto">
                    <table class="table table-clear">
                        <tbody>

                            <tr>

                                <td style="font-size: 1rem;font-weight:600;">Total Selling Price : <small><b>({{$filter_data['currency']}})</b></small> : </td>
                                <td>
                                    <p class="float-right">{{number_format($selling_sum) }}</p>
                                </td>

                            </tr>
                            <tr>

                                <td style="font-size: 1rem;font-weight:600;">Total Cost Price : <small><b>({{$filter_data['currency']}})</b></small>: </td>
                                <td>
                                    <p class="float-right">{{number_format($cost_sum) }} </p>
                                </td>
                            </tr>
                            <tr>

                                <td class="mb-0"><b class="mb-0">Gross Profit Margin : </b></td>
                                <td class="mb-0">
                                    <p class="float-right mb-0">{{number_format($gpm) }}% </p>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    @endif



</main>
@endsection
@section('script2')
<script>
    $(function() {
        $('#add-filter').click(function(event) {
            $('#Filter-Form').toggle();
        });
    });
</script>



@endsection