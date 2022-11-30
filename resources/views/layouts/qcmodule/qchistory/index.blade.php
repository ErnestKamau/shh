@extends('layouts.lab.layout.app', ['dataTable'=>true, 'datePicker'=>true, 'select2'=>true])

@section('title2')
<title>{{ $status }} | Sample WorkFlow</title>

<style>
	

	.hidden {
		display: none;
	}


	.btn-white{
		background-color: white !important;
	}
	.text-bold{
		font-weight: 550 ;
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
			'link' => route('sample-workflow', ['status' => 'All Samples']),
			'name' => 'QC History',
			'icon' => null
		),
		
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-2">
		<span><i class="mdi mdi-file-document-edit"></i> Qc History</span>
	</h4>
	<div class="filter-form bordered-top card">
		<u><small class="p-2 text-bold">Apply Filter ?</small></u>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Start Date</label>
						<input type="date" name="start_date" id="" value="" class="form-control">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">End Date</label>
						<input type="date" name="end_date" id="" value="" class="form-control">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Qc Types</label>
						<select name="qc_type_id" id="" class="form-control"></select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Qc Schemes</label>
						<select name="qc_scheme_id" id="" class="form-control"></select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Standard</label>
						<select name="standard_id" id="" class="form-control"></select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Sample Type</label>
						<select name="sample_type_id" id="" class="form-control"></select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Analysis Type</label>
						<select name="analysis_type_id" id="" class="form-control"></select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Analyte</label>
						<select name="analyte_id" id="" class="form-control"></select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Remark</label>
						<select name="remark" id="" class="form-control">
							<option value="">Select Remark...</option>
							<option value="pass">Pass</option>
							<option value="fail">Fail</option>
						</select>
					</div>
				</div>

			</div>
		</div>
	</div>
	<div class="card mt-3">
		<div class="spn-header text-bold pl-3 pt-3">Report Parameters :</div>
		<div class="card-body row">
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Max Value : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Min Value : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Median : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Average : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> STD Deviations : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Cv : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Statistical Population Size</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Z-Score : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Std Star : -</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Cv Star : -</span>
			</div>
		</div>
	</div>
	<div class="card mt-4" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-bordered table-sm">
					<thead>
						<th></th>
						<th nowrap>Receipt Date</th>
						<th>Batch Code</th>
						<th>Sample Code</th>
						<th nowrap>Sample Type</th>
						<th>Qc Type</th>
						<th>Qc Scheme</th>
						<th>Standard</th>
						<th>Results</th>
						<th>Standard Value</th>
						<th>Expected Count</th>
						<th>Remark</th>
						<th>Analyst</th>
						<th>RFT Form No</th>
						<th nowrap>Date Collected</th>
						<th>Lab</th>
					</thead>
					<tbody>
						
					</tbody>
				</table>
				
			</div>
		</div>
	</div>
</main>
@endsection

@section('script2')

@endsection