@extends('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Equipment-Report </title>
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
            'name' => 'Equipments',
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
        <i class="fas fa-money-bill-alt"></i> Equipment Reports | Disposed Equipment Report

    </h2>
    <div class="card mb-5 p-3">
        <div class="row">
            <div class="col-xl-6 col-sm-6">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-toggle="dropdown">
                        Choose Equipment Report
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item " href="{{ route('disposed-equipment-generate') }}">Disposed Equipment</a>
                        <a class="dropdown-item " href="#">Maintainance Logs Report</a>
                        <a class="dropdown-item " href="#">Calibration Logs Report</a>
                        <a class="dropdown-item " href="#">Repair Logs Report</a>


                    </div>

                </div>
            </div>
            <div class="col-xl-6 col-sm-6">
                <span class="btn btn-outline-warning float-right" id="add-filter"><i class="mdi mdi-plus"></i> Filter</span>
            </div>
        </div>
        <div class="card mb-3 bg-light p-3" style="font-size: 12px;" id="Filter-Form">
            <h5 style="font-size: 12px;" class="card-title">
                <small class="text-danger">*Choose filters to apply on your report.</small>
                <hr>
            </h5>
            <?php
            $equipments = getEquipment();
            $locations = getAssetLocation();
            $types = getAssetTypes();

            ?>

            <form action="/equipment/reports-home/generate" method="get" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    <div class="row no-gutter">
                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Choose Equipments</label>
                                <select style="background-color: white;" name="equipment" id="equipment-ids" aria-placeholder="Choose Client...">
                                    <option value="all">All</option>
                                    @foreach($equipments as $equipment)
                                    <option value="{{$equipment->id}}">{{$equipment->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Choose Asset Location</label>
                                <select name="location_id" style="background-color: white;" id="sample_priority" aria-placeholder="Choose Sample priority...">
                                    <option value="all">All</option>
                                    @foreach($locations as $location)
                                    <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">Choose Asset Types</label>
                                <select name="asset_type" style="background-color: white;" id="sample-workflow" aria-placeholder="Choose Sample Workflow...">
                                    <option value="all">All</option>
                                    @foreach($types as $type)
                                    <option value="{{$type->id}}">{{$type->asset_code}} ({{$type->descripton}})</option>
                                    @endforeach
                                </select>
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
    <a href="#" class="btn btn-outline-dark btn-sm float-right mr-4 "><i class="mdi mdi-printer-check"></i> Print</a> <br><br><br>
    <?php
    $check = getSystemConfiguration('display_system_logo');
    $check_company_logo = getDefaultCompany()
    ?>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="height:40px">
                {!! $check[0]->value == "true" ? '<img src="/images/imara-sys.png" style="height:100%" class="float-right">' : '' !!}
                <center style="font-weight: 900;">Disposed Equipments Reports </center>
                {!! $check_company_logo[0]->show_on_reports == 1 ? '<img src='.$check_company_logo[0]->logo.' style="height: 4.5%;position:absolute;top:0%" class="float-left" />':'' !!}
            </h4>
            <hr>
            <h5 class="mb-3 mt-4">Report Filter</h5>
            <div class="row no-gutters mb-5">
                <div class="col-xl-1 col-sm-2"></div>
                <div class="col-xl-11 col-sm-9">
                    <div class="row no-gutters">
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Equipment: </b>{{$filter_data['equipment']}}</p>
                            <p><b>No of Equipments: </b>{{$filter_data['no_equipments']}}</p>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Asset Location: </b>{{$filter_data['asset_location']}}</p>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <p><b>Asset Type: </b>{{$filter_data['asset_type']}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 col-sm-12">
                    
                        
                        
                            <div class="table-responsive mt-3">
                                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">

                                    <thead class="bg-light p-2">
                                        <tr>
                                            <th>No</th>

                                            <th>Equipment</th>
                                            <th>Certificate</th>
                                            <th>Overseen By</th>
                                            <th>Maintainance Notification</th>
                                            <th>Reference Number</th>
                                            <th>Operator</th>
                                            <th>maintainance Type</th>
                                            <th>Service Provider </th>
                                            <th>Created Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equipments_data as $equipment)

                                        <tr>
                                            <td>{{$loop->iteration}}</td>

                                            <td>{{$equipment->name}}</td>
                                            <td>{{$equipment->make}}</td>
                                            <td>{{$equipment->model}}</td>
                                            <td>{{$equipment->date_purchased}}</td>
                                            <td>{{$equipment->asset_type}}</td>
                                            <td>{{$equipment->assign_employee_name}}</td>
                                            <td>{{$equipment->assigned_department}}</td>
                                            <td>{{$equipment->dispose_employee}}</td>
                                            <td>{{$equipment->dispose_date}}</td>

                                           
                                        </tr>
                                        <!-- <tr>
                                        <div class="panel panel-default mt-3">
                                                <h5>Dispose Reason: </h5>
                                                <div class="row">
                                                    <div class="col-xl-1 col-sm-1"></div>
                                                    <div class="col-xl-11 col-sm-11">
                                                        {{$equipment->comment}}
                                                    </div>
                                                </div>
                                            </div>
                                        </tr> -->

                                        @endforeach
                                    </tbody>
                                </table>
                                
                            </div>
                            <div class="card p-3 mt-3" style="height: 70vh; overflow:auto">
                                @foreach($equipments_data as $equipment)
                                <div class="card mt-3">
                                    <div class="card-header">

                                        <h5 class="card-title"> 
                                            <img src="{{$equipment->picture}}"  style="height: 50px;" class="float-left" alt=""> 
                                            <center  style="font-weight: 700;">Dispose Reason for {{$equipment->name}}</center>
                                            <span  style="font-size: 13px;" class="float-right">Dispose Date: {{$equipment->dispose_date}}</span>
                                        </h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-1 col-sm-1"></div>
                                        <div class="col-xl-11 col-sm-11">
                                            {{$equipment->comment}}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                        
                    
                </div>

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