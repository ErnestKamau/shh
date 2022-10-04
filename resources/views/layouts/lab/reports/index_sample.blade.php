@extends('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true])



@section('title2')
<title> Sample Reports </title>
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
    .dt-buttons{
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
        <i class="fas fa-money-bill-alt"></i> Lab Reports | Sample Reports

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
            $sample_conditions = getSamplelAllConditions();
            $products = getAllCompanyproduct();
            $sample_points = getAllSamplePoints();
            $analysis_types = getAnalysisTypes();
            ?>

            <form action="{{ route('gnrt') }}" method="post" enctype="multipart/form-data">
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
                                <label class="control-label">Sample Conditions</label>
                                <select name="conditions" style="background-color: white;" id="sample_priority" aria-placeholder="Choose Sample priority...">
                                    <option value="all">All</option>
                                    @foreach($sample_conditions as $sample_condition)
                                    <option value={{$sample_condition->id}}>{{$sample_condition->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Sample Workflows</label>
                                <select name="workflows" style="background-color: white;" id="sample-workflow" aria-placeholder="Choose Sample Workflow...">
                                    <option value="all">All</option>
                                    @foreach($sample_workflow as $workflow)
                                    <option value="{{$workflow}}">{{$workflow}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row no-gutter">
                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Sample points</label>
                                <select name="points" style="background-color: white;" id="tracking-stage" aria-placeholder="Sample Tracking Stages...">
                                    <option value="all">All</option>
                                    @foreach($sample_points as $sample_point)
                                    <option value="{{$sample_point->id}}">{{$sample_point->name}}</option>
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
                                <label class="control-label">Start Date <small class="text-danger">*takes a span of one year by default </small> </label>
                                <input type="date" style="background-color: white;" name="start_date" class="form-control">
                            </div>
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
   $check= getSystemConfiguration('display_system_logo');
   $check_company_logo = getDefaultCompany()
   ?>
    <div class="card">
        <div class="card-header">
        <h4 class="card-title" style="height: 40px;">
            
               {!! $check[0]->value == "true" ? '<img src="/images/imara-sys.png" style="height:100%" class="float-right">' : '' !!} 
               <center style="font-weight: 900;">Samples Reports </center>
               {!! $check_company_logo[0]->show_on_reports == 1 ? '<img src='.$check_company_logo[0]->logo.' style="height: 6%;position:absolute;top:1%" class="float-left"  />':'' !!}
            </h4>
            
            <hr>
            <h5 class="mb-3 mt-4" style="margin-right: 10%; font-weight:600;font-size:15px">Report Filters: </h5>
            <div class="row no-gutters mb-5">
                <div class="col-xl-1 col-sm-2">

                </div>
                <div class="col-xl-11 col-sm-10">
                    <div class="row no-gutters">

                        <div class="col-xl-3 col-sm-6">
                            <p><b>Client: </b>{{$filter_data['client']}}</p>
                            <p><b>Analysis Type: </b>{{$filter_data['analysis_type']}}</p>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <p><b>Workflow Stage: </b> {{$filter_data['sample_workflow']}}</p>
                            <p><b>Start Date: </b>{{$filter_data['start_date']}}</p>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <p><b>Sample Condition: </b>{{$filter_data['condition']}}</p>
                            <p><b>End Date: </b>{{$filter_data['end_date']}}</p>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <p><b>Sample Point: </b>{{$filter_data['sample_point']}}</p>
                            <p><b>Product: </b>{{$filter_data['product']}}</p>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">

           
        <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <h5>Samples Reports</h5>
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Priority</th>
                                <th>Sample Code</th>
                                <th>Batch Code</th>
                                <th>Analysis Type</th>
                                <th>Customer</th>
                                <th>Sample Condition</th>
                                <th>Sample Point</th>
                                <th>Product</th>
                                <th>Workflow Stage</th>
                                

                            </tr>
                        </thead>
                        <tbody style="">

                            @foreach($sample_details as $detail)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <?php
                                $sample = getSampleHeaderByID($detail->sample_header_id);
                                $name = array();
                                $ids = explode(',',$detail->analysis_type_id);
                                if(sizeof($ids)>1){
                                    foreach($ids as $id){
                                        $analysis = getAnalysisTypeID((int)$id);
                                        array_push($name,$analysis->name);
                                    }
                                }else{
                                    $analysis = getAnalysisTypeID((int)$ids[0]);
                                    array_push($name,$analysis->name);
                                }
                                $namestr = implode(',',$name);
                                $customer = getCrmCustomerByID($sample->crm_customer_id);
                                $condition = getSampleConditionByID($detail->sample_condition_id);
                                $point = getSamplePointByid($detail->sample_point_id);
                                $product = getCompanyProdut($detail->company_product_id);
                                ?>
                                <td>{!! $sample->priority == 'High' ? '<span class="mdi mdi-star text-danger">High</span>' : $sample->priority !!}</td>
                                <td>{{$detail->sample_code}}</td>
                                <td>{{$sample}}</td>
                                <td>{{$namestr}}</td>
                               
                                <td>{{$customer->name}}</td>
                                <td>{{$condition->name ?? ''}}</td>
                                <td>{{$point->name ?? ''}}</td>
                                <td>{{$product->name ?? ''}}</td>
                                <td>{{$sample->status ?? ''}}</td>
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
<script>
    $(function() {
        $('#add-filter').click(function(event) {
            $('#Filter-Form').toggle();
        });
    });
</script>



@endsection