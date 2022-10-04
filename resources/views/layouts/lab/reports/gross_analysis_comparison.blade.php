@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Gross Profit Analysis Comparison Report </title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>
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
        <i class="fas fa-money-bill-alt"></i> Lab Reports | Gross Profit Analysis Reports

    </h2>

    <div class="card mb-5 p-3">
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


            $analysis_type = getAnalysisTypes();
            ?>

            <form action="{{ route('gross-analysis-comparison') }}" method="get" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    <div class="row no-gutter">


                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Analysis Type</label>
                                <select name="analysis_type" style="background-color: white;" id="sample-types" aria-placeholder="Choose Sample Type...">
                                    <option value="all">All</option>
                                    @foreach($analysis_type as $type)
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
                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Start Date <small class="text-danger">*takes a span of one year by default </small> </label>
                                <input type="date" style="background-color: white;" name="start_date" class="form-control">
                            </div>

                        </div>
                    </div>
                    <div class="row no-gutter">

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


    <span data-toggle="modal" data-target="#print_report" class="btn btn-outline-dark float-right mr-4"><i class="mdi mdi-printer-check"></i> Print</span> <br><br><br>
    <?php
    $check = getSystemConfiguration('display_system_logo');
    $check_company_logo = getDefaultCompany()
    ?>
    @if($status == 'show')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="height: 40px;">

                {!! $check[0]->value == "true" ? '<img src="/images/imara-sys.png" style="height:80%" class="float-right">' : '' !!}
                <center class="ml-5" style="font-weight: 900;">Gross Profit Analysis Comparison Reports </center>
                {!! $check_company_logo[0]->show_on_reports == 1 ? '<img src='.$check_company_logo[0]->logo.' style="height: 2.5%;position:absolute;top:0%" class="float-left mt-2" />':'' !!}
            </h4>
            <hr>
            <h5 class="mb-3 mt-4" style="margin-right: 10%; font-weight:600;font-size:15px">Report Filters: </h5>
            <div class="row no-gutters mb-5">
                <div class="col-xl-1 col-sm-2"></div>
                <div class="col-xl-11 col-sm-2">
                    <div class="row no-gutters">
                        <div class="col-xl-4 col-sm-6">

                            <p><b>Currency: </b> {{$filter_data['currency']}}</p>
                            <p><b>End Date: </b>{{$filter_data['end_date']}}</p>

                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Analysis Type: </b>{{$filter_data['analysis_type']}}</p>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Start Date: </b>{{$filter_data['start_date']}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card tab-card">
                <div class="card-header tab-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="analysis-tabs" role="tablist">
                        <li class="nav-item">
                            <a href="#analysis-type-graph-comparison" class="nav-link active" id="analysis_type_graph_link" data-toggle="tab" role="tab" aria-controls="analysis-type-graph-comparison" aria-selected="true">Analysis Type Comparison</a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="analysis-type-tabs-contents">
                    <div class="tab-pane fade show active p-3" id="analysis-type-graph-comparison" role="tabpanel" aria-labelledby="one-tab">
                        <div class=" bg-default no-overflow">
                            <div class="card-head-sm p-3 border-bottom">
                                <h5 class="card-title">Analysis Types Comparison Graph</h5>

                            </div>
                            <div class="card-body">
                                <canvas id="gross-analysis-comparison" style="height:70% !important"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-5">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                    <h5>Gross Profit Analysis Types Report</h5>

                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Analysis Type</th>
                            <th>Samples Submited</th>

                            <th>Currency</th>
                            <th>Total Cost Price</th>
                            <th>Total Selling Price</th>
                            <th>Total Profit</th>
                            <th>Profit Margin</th>

                        </tr>
                    </thead>
                    <tbody>


                        @foreach($analysis_types as $analysis)
                        <tr>

                            <td>{{$loop->iteration}}</td>
                            <td>{{$analysis->name}}</td>
                            <td>{{$analysis->samples}}</td>
                            <td>{{$analysis->currency}}</td>
                            <td class="text-right">{{number_format($analysis->total_costing ,2) }}</td>
                            <td class="text-right">{{number_format($analysis->total_selling,2) }}</td>
                            <td class="text-right">{{number_format($analysis->profits ,2)}}</td>
                            <td>{{number_format($analysis->profit_margin,3)}}%</td>

                        </tr>
                        @endforeach




                    </tbody>

                </table>
            </div>
        </div>
    </div>
    @endif


</main>

@if($status =='show')
<div class="modal fade" id="print_report" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('print_gross_analysis_comparison') }}" method="post">
                @csrf  
                <div class="modal-header ">
                    <h4 class="modal-title">
                        <i class="mdi mdi-printer-check text-primary"></i> Print Gross Profit Analysis Analysis Comparison Report
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="card p-2 text-center" style="background-color: turquoise;">
                        Confirm you want to print out <b> Gross Profit Analysis Analysis Comparison Report</b>.
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Currency</label>
                        <input type="number" name="currency" value="{{$filter_data['currency_id']}}" id="" class="form-control">
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Analysis Type</label>
                        <input type="text" name="analysis_type" value="{{$filter_data['analysis_type']}}" id="" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-printer-check"></i> Print</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
echo '
<script type="text/javascript">
var analysis_data =' . json_encode($analysis_data) . ';
</script>
';
?>

@endsection
@section('script2')
<script>
    function dynamicColors() {
        var r = Math.floor(Math.random() * 255);
        var g = Math.floor(Math.random() * 255);
        var b = Math.floor(Math.random() * 255);
        return "rgba(" + r + "," + g + "," + b + ")";
    }

    function poolColors(a) {
        var pool = [];
        for (i = 0; i < a; i++) {
            pool.push(dynamicColors());
        }
        return pool;
    }

    let mychart = document.getElementById('gross-analysis-comparison').getContext('2d');
    var analysis_names = Object.keys(analysis_data);
    var analysis_profit = Object.values(analysis_data);


    let massPopChart = new Chart(mychart, {
        type: 'bar',
        data: {
            labels: analysis_names,
            datasets: [{
                label: 'Profit',
                data: analysis_profit,
                backgroundColor: poolColors(analysis_profit.length),
                borderColor: poolColors(analysis_profit.length),
                hoverBorderWidth: 1,
                hoverBorderColor: '#000',
            }],
        },
        options: {
            title: {
                display: true,
                text: 'Gross Analysis Comparison',
                fontSize: 15,
                fontColor: '#000',
            },
            legend: {
                display: false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    })

    $(function() {
        $('#add-filter').click(function(event) {
            $('#Filter-Form').toggle();
        });
    });
</script>
@endif


@endsection