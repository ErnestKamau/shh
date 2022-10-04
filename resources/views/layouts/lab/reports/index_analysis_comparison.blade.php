@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Analysis Types Comparison Reports </title>
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
        <i class="fas fa-money-bill-alt"></i> Lab Reports | Analysis Comparison Reports

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


            $analysis_types = getAnalysisTypes();
            ?>

            <form action="{{ route('analysis-type-comparison-report') }}" method="get" enctype="multipart/form-data">
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
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="height: 40px;">

                {!! $check[0]->value == "true" ? '<img src="/images/imara-sys.png" style="height:100%" class="float-right">' : '' !!}
                <center style="font-weight: 900;">Analysis Comparison Reports </center>
                {!! $check_company_logo[0]->show_on_reports == 1 ? '<img src='.$check_company_logo[0]->logo.' style="height: 4.5%;position:absolute;top:0%" class="float-left" />':'' !!}
            </h4>
            <hr>
            <h5 class="mb-3 mt-4" style="margin-right: 10%; font-weight:600;font-size:15px">Report Filters: </h5>
            <div class="row no-gutters mb-5">
                <div class="col-xl-1 col-sm-2"></div>
                <div class="col-xl-11 col-sm-2">
                    <div class="row no-gutters">
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Client: </b>{{$filter_data['client']}}</p>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <p><b>Start Date: </b> {{$filter_data['start_date']}}</p>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <p><b>End Date: </b>{{$filter_data['end_date']}}</p>
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
                                <h5 class="card-title">Analysis Type Comparison Graph</h5>

                            </div>
                            <div class="card-body">
                                <canvas id="analysis-comparison" style="height:70% !important"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-5">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                    <h5>Analysis Type Comparion</h5>
                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Analysis Name</th>
                            <th>Total Samples</th>
                            <th>In Reception</th>
                            <th>In Lab</th>
                            <th>In Verification</th>
                            <th>In Approval</th>
                        </tr>
                    </thead>
                    <?php
                    $reception = 'Sample Reception';
                    $lab = 'Samples In Lab';
                    $verification = 'Sample Verification';
                    $approval = 'Sample Approval';
                    ?>
                    <tbody>
                        @foreach($samples_analysis as $sample)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$sample['name']}}</td>
                            <td>{{$sample['samples']}}</td>
                            <td>{{$sample['Sample Reception'] ?? 0}}</td>
                            <td>{{$sample['Samples In Lab'] ?? 0}}</td>
                            <td>{{$sample['Sample Verification'] ?? 0}}</td>
                            <td>{{$sample['Sample Approval'] ?? 0}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>



</main>
<?php
echo '
<script type="text/javascript">
var analysis_namearr =' . json_encode($samples_analysis_details) . ';
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

    let mychart = document.getElementById('analysis-comparison').getContext('2d');
    var analysis_names = Object.keys(analysis_namearr);
    var analysis_values = Object.values(analysis_namearr);


    let massPopChart = new Chart(mychart, {
        type: 'bar',
        data: {
            labels: analysis_names,
            datasets: [{
                label: 'Samples',
                data: analysis_values,
                backgroundColor: poolColors(analysis_values.length),
                borderColor: poolColors(analysis_values.length),
                hoverBorderWidth: 1,
                hoverBorderColor: '#000',
            }],
        },
        options: {
            title: {
                display: true,
                text: 'Analysis Types',
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



@endsection