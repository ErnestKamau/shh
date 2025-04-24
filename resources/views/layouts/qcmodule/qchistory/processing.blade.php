@extends('layouts.lab.layout.app', ['dataTable'=>true, 'datePicker'=>true, 'select2'=>true])

@section('title2')
<title>{{ $status ?? 'QC Processing' }} | QC WorkFlow</title>

<style>
	.hidden {
		display: none;
	}


	.btn-white {
		background-color: white !important;
	}

	.text-bold {
		font-weight: 550;
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
			'link' => route('showUnProcessed'),
			'name' => 'QC Unprocessed',
			'icon' => null
		),

	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-2">
		<span><i class="mdi mdi-file-document-edit"></i> Qc Awaiting Processing</span>
        <span class="btn btn-sm btn-default bg-white float-right" data-target="#process_data" data-toggle="modal"><i class="mdi mdi-cog"></i> Process Results</span>
	</h4>

	<div class="card mt-4" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
		<div class="card-header" style="font-size:20px; font-weight:580">
			Unprocessed Data
			<!-- <span class="btn btm-sm btn-default float-right  bg-white" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;" data-target="#release_data" data-toggle="modal"><i class="mdi mdi-cogs"></i> Release Report</span> -->
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-bordered table-sm">
					<thead>

						<th nowrap>Receipt Date</th>
						<th>Batch Code</th>
						<th>Sample Code</th>
						
						<th>Analyte</th>
			
						<th nowrap>Sample Type</th>
						<th>Results</th>
						<th>Previous Result</th>
						<th>+- %</th>
						<th>Standard Value</th>
						<th>Remark</th>
						<th>Analyst</th>

					</thead>
					<tbody>
						@foreach($results as $r)
						<tr>
							<td>{{$r->receipt_date}}</td>
							<td>{{$r->batch_code}}</td>
							<td>{{$r->sample_detail_code}}</td>
							
							<td>{{ $r->analyte_code }}</td>
							
							<td>{{$r->sample_type_name}}</td>
							<td>{{$r->result}}</td>
							
							<td>{{ $r->previous_result }}</td>
							
							<td>{{ $r->config_percentage }}</td>
							
							<td>{{$r->main_value}}</td>
							
							<td>{{$r->remarks}}</td>
							<td>{{$r->analyst_name}}</td>

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
<div class="modal fade" id="process_data" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{ route('process-qc-results') }}" method="post">
				@csrf 
				<div class="modal-body">
					<div class="alert alert-primary p-2">
						<div class="d-flex">
							<i class="mdi mdi-alert-decagram mt-4" style="font-size:55px"></i>
							<div class="p-2 mt-5">
								Confirm you want to process all the un processed QC Results!
							</div>
						</div>
					</div>
					
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline-primary btn-sm" type="submit"><i class="mdi mdi-thumb-up"></i> Yes, Process</button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	$(() => {
		
	})
</script>

@endsection