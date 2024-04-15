@extends('layouts.lab.layout.app', ['dataTable'=>true, 'datePicker'=>true, 'select2'=>true])

@section('title2')
<title>{{ $status }} | Sample WorkFlow</title>

<style>
	.form-part-toggler {
		margin: 0px 0px 5px 0px !important;
		padding: 6px 6px 6px 6px;
		border-bottom: 1px solid rgba(0, 0, 0, 0.09);
		cursor: pointer;
	}

	.form-part-toggler:hover {
		background-color: rgba(0, 0, 0, 0.08);
	}

	#sample-detail-rows .form-group {
		display: none;
	}

	#sample-detail-rows tr.selected-row {
		background-color: rgb(253, 220, 220);
	}

	#sample-detail-rows .text {
		display: unset;
	}

	#sample-detail-rows tr.editable .form-group {
		display: unset;
	}

	#sample-detail-rows tr.editable .text {
		display: none;
	}

	#sample-detail-rows tr {
		cursor: pointer;
	}

	.hidden {
		display: none;
	}

	.overdue-bg-color {
		background-color: rgba(240, 185, 83, 0.972) !important;
	}

	.upfront-bg-color {
		background-color: skyblue !important;
	}

	.ammend-bg-color {
		background-color: #fef764 !important;
	}
	.btn-white{
		background-color: white !important;
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
	<h4 class="p-4">
		<span><i class="mdi mdi-file-document-edit"></i> Qc History</span>
	</h4>
	<div class="filter-form bordered-top card">
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
						<select name="qc_type_id" id="qc_type_id" class="form-control">\
							<option value="">Choose Qc Type</option>
							@foreach($qc_types as $q_type)
							<option value="{{$q_type->id}}">{{$q_type->name}}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Qc Schemes</label>
						<select name="qc_scheme_id" id="" class="form-control">
							<option value="">Choose QC Scheme</option>
							@foreach($qc_schemes as $scheme)
							<option value="{{$scheme->id}}">{{$scheme->name}}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Analyte</label>
						<select name="analyte_id" id="" class="form-control">
							<option value="">Choose Analyte</option>
							@foreach($analytes as $analyte)
							<option value="{{$analyte->id}}">{{$analyte->code}}</option>
							@endforeach
						</select>
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
				<div class="col-md-4">
					<div class="form-group">
						<label for="" class="control-label">Standard</label>
						<select name="standard_id" id="standard_id" class="form-control"></select>
					</div>
				</div>

			</div>
		</div>
	</div>
	<div class="table-responsive bg-light p-4">
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
</main>
@endsection

@section('script2')
<script>
	$(()=>{
		$('#qc_type_id').on('change',(e)=>{
			$('#standard_id').empty();
			var qc_type_id = $('#qc_type_id').val()
			$.ajax({
				url:`/qualitycontrol/get/Qc-Standards/${qc_type_id}/Ajax`,
				method:`GET`,
				success:(data)=>{
					$.each(data,(i,obj)=>{
						var body = `<option value="${obj.id}">${obj.name}</option>`;
						$('#standard_id').append(body);
					})
				},
				error:(data)=>{
					console.log(data);
				}
			})
		})
	})
</script>

@endsection