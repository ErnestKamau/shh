@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> Sample Report</title>
	<style type="text/css">
		.tab-card {
			border:1px solid #eee;
		}

		.tab-card-header {
			background:none;
		}
		/* Default mode */
		.tab-card-header > .nav-tabs {
			border: none;
			margin: 0px;
		}
		.tab-card-header > .nav-tabs > li {
			margin-right: 2px;
		}
		.tab-card-header > .nav-tabs > li > a {
			border: 0;
			border-bottom:2px solid transparent;
			margin-right: 0;
			color: #737373;
			padding: 2px 15px;
		}

		.tab-card-header > .nav-tabs > li > a.show {
			border-bottom:2px solid #007bff;
			color: #007bff;
		}
		.tab-card-header > .nav-tabs > li > a:hover {
			color: #007bff;
		}

		.tab-card .nav-link.active{
			background-color: #dadccd !important;
			border: 1px solid #cccebf !important;
		}

		.tab-card-header > .tab-content {
			padding-bottom: 0;
		}

		.my-small-text{
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
			box-shadow: 0px 0px 5px rgba(0,0,0,0.08);
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
          'link' => route('show-sample-report'),
          'name' => 'Batch-'.$sample->batch_code,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<h2 class="p-4">
        <i class="mdi mdi-star-box-multiple-outline" ></i> Batch|{{$sample->batch_code}} Report <i class="mdi mdi-star"></i> 
        <a href="#" class="btn btn-outline-success float-right"><i class="mdi mdi-printer"></i> Print</a>
		</h2>
		
		
        <div class="card mt-4">
            <div class="card-title bg-light p-2 pt-4" style="height: 100px;">
            <img src="/images/imara-sys.png" style="height: 60px" />  
            <span class="float-right" style="font-size: 25px; font-weight:700">Batch | {{$sample->batch_code}}</span>
            </div>
            <div class="card-body p-3">
                <div class="row no-gutter ">
                    <div class="col-xl-6 col-sm-12 ">
                        <h5 style="font-weight: 600;">Customer Information</h5>
                        <hr>
                        <div class="content">

                            <p><b>Name: </b>{{$customer->name}}</p>
                            <p><b>Email: </b>{{$customer->email}}</p>
                            <p><b>Address: </b>{{$customer->address}}</p>
                            <p><b>Website: </b>{{$customer->website}}</p>                         
                        </div>
                    </div>

                    <div class="col-xl-6 col-sm-12 ">
                        <h5 style="font-weight: 600;">Batch Information</h5>
                        <hr>
                        <div class="content-sample">
                            <p><b>Priority: </b>{!! $sample->priority == 'High' ? '<span class="mdi mdi-star text-danger">High</span>':$sample->priority !!}</p>
                            <p><b>Batch Code: </b>{{$sample->batch_code}}</p>
                            <p><b>Tracking Stage: </b>{{$sample->sample_tracking_stage}}</p>
                            <p><b>Reference Number: </b>{{$sample->reference_number}}</p>
                            <p><b>Sample Type: </b>{{$sample->sample_type_name}}</p>
                            <p><b>Number of Samples: </b>{{$sample->no_of_samples}}</p>
                            <p><b>Receipt Date: </b>{{$sample->receipt_date}}</p>
                            <p><b>Date Collected: </b>{{$sample->date_collected}}</p>
                            
                        </div>
                    </div>
                </div>
                @foreach($sample_details as $detail)
                <div class="table-responsive bg-light mb-5">
                    <table
                        class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <h5 class="pl-3 pt-3" style="font-weight:600">Sample {{$detail->sample_code}}</h5>
                        <hr>
                        <div class="row no-gutter p-3">
                            <div class="col-xl-4 col-sm-6">
                                <p><b>Sample Code: </b> {{$detail->sample_code}}</p>
                                <p><b>Created At: </b> {{$detail->created_at}}</p>
                                
                            </div>
                            <div class="col-xl-4 col-sm-6">
                                <?php 
                                    $analysises = array();
                                    if(strlen($detail->analysis_type_id > 1)){
                                        $analysis_str = explode(',',$detail->analysis_type_id);
                                        foreach($analysis_str as $id){
                                            $analysis = getAnalysisTypeID(strval($id));
                                            array_push($analysises,$analysis->name);
                                        }
                                        $analysis_name = implode(',',$analysises);
                                    }else{
                                        $analysis = getAnalysisTypeID(strval($id));
                                        
                                       
                                    }
                                    $condition  = getSampleConditionByID($detail->sample_condition_id);
                                    $results = getSampleDetailResultByID($detail->id)
                                ?>
                                <p><b>Analysis Type: </b> {{$analysis_name ?? '-'}}</p>
                                <p><b>Sample Point: </b></p>
                            </div>
                            <div class="col-xl-4 col-sm-6">
                                <p><b>Sample Condition: </b>{{$condition->name}}</p>
                            </div>
                        </div>  
                        <hr>  
                        <thead class=" p-2">
                            <tr>
                                <th>No</th>
                                <th>Analysis Type</th>
                                <th>Analyte Name</th>
                                <th>Report Display</th>
                                <th>Result</th>
                                <th>Unit</th>
                                <th>Reporting Symbol</th>
                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                            <?php
                                $analyte = getAnalyteByID($result->analyte_id);
                                $analysis_analyte = getAnalysisTypeID($result->analysis_type_id);
                            ?>
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$analysis_analyte->name}}</td>
                                <td>{{$analyte->name}}</td>
                                <td>{{$result->analyte_code}}</td>
                                <td>{!! $result->result == '' ? 'N/a' : formatReportResults($result->result, $result->analyte_id) !!}</td>
                                <td>{!! $result->unit_code == '' ? 'N/a' : $result->unit_code !!}</td>
                                <td>{!! $result->reporting_symbol == '' ? 'N/a' : $result->reporting_symbol !!}</td>
                            </tr>
                            @endforeach
                            
                            
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>

        </div>
        
	
@endsection
@section('script2')



@endsection
