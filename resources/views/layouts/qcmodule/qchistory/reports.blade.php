@extends('layouts.lab.layout.app', ['dataTable'=>true, 'datePicker'=>true, 'select2'=>true])

@section('title2')
<title>{{ $status ?? 'QC Reports' }} | QC WorkFlow</title>

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
			'link' => route('qc-reports'),
			'name' => 'QC Unprocessed',
			'icon' => null
		),

	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-2">
		<span><i class="mdi mdi-file-document-edit"></i> Qc Reports</span>
        
	</h4>

	<div class="card mt-4" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
		<div class="card-header" style="font-size:20px; font-weight:580">
			Data
			<!-- <span class="btn btm-sm btn-default float-right  bg-white" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;" data-target="#release_data" data-toggle="modal"><i class="mdi mdi-cogs"></i> Release Report</span> -->
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-bordered table-sm">
					<thead>
                        <th>Graph</th>
						<th>Sample Type</th>
                        <th>Analysis Type</th>
                        <th>Method</th>
                        <th>Analyte</th>
                        <th>No of Results</th>
                        <th>Mean</th>
                        <th>Median</th>
                        <th>SD</th>
                        <th>CV</th>
                        <th>% CV</th>

					</thead>
					<tbody>
						@foreach($results as $r)
						<tr>
							<td><i class="mdi mdi-eye"></i></td>
                            <td>{{ $r->sampletype->name }}</td>
                            <td>{{ $r->analysistype->name }}</td>
                            <td>{{ $r->method->name }}</td>
                            <td>{{ $r->analyte->code }}</td>
                            <td>{{ $r->results->count() }}</td>
                            <td>{{ $r->robust_mean }}</td>
                            <td>{{ $r->robust_median }}</td>
                            <td>{{ $r->robust_standard_deviation }}</td>
                            <td>{{ $r->robust_cv }}</td>
                            <td>{{ $r->robust_cv_percentage }}</td>
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