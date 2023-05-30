@extends('layouts.lab.layout.app', ['dataTable'=>true, 'datePicker'=>true, 'select2'=>true])

@section('title2')
<title>{{ $status }} | Sample WorkFlow</title>

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
	<div class="filter-form bordered-top card p-2">
		<form action="{{route('generateQCReport')}}" method="post">
			@csrf
			<u><small class="p-3 text-bold">Apply Filter ?</small></u>
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
							<select name="qc_type_id" id="qc_type_id" class="form-control">
								<option value="">Choose QC Type</option>
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
							<label for="" class="control-label">Standard</label>
							<select name="standard_id" id="standard_id" class="form-control"></select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="" class="control-label">Sample Type</label>
							<select name="sample_type_id" id="sample_type_id" class="form-control">
								<option value="">Choose Sample Type</option>
								@foreach($sample_types as $st)
								<option value="{{$st->id}}">{{$st->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="" class="control-label">Analysis Type</label>
							<select name="analysis_type_id" id="analysis_type_id" class="form-control">

							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="" class="control-label">Analyte</label>
							<select name="analyte_id" id="analyte_id" class="form-control">
								
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

				</div>
			</div>
			<div class="card-footer-1 border-top m-2 p-3">
				<button type="submit" class="btn btn-sm btn-outline-primary float-right">Apply</button>
			</div>
		</form>
	</div>

	<div class="card mt-4" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
		<div class="card-header" style="font-size:20px; font-weight:580">
			Report Data
			<span class="btn btm-sm btn-default float-right  bg-white" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;" data-target="#release_data" data-toggle="modal"><i class="mdi mdi-cogs"></i> Release Report</span>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-bordered table-sm">
					<thead>

						<th nowrap>Receipt Date</th>
						<th>Batch Code</th>
						<th>Sample Code</th>
						<th nowrap>Sample Type</th>
						<th>Results</th>
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
							<td>{{$r->sample_type_name}}</td>
							<td>{{$r->result}}</td>
							<td>{{$r->guide}}</td>
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
<div class="modal fade" id="release_data" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="" method="post" enctype="multi
			">
				@csrf 
				<div class="modal-body">
					<div class="alert alert-primary p-2">
						<div class="d-flex">
							<i class="mdi mdi-alert-decagram mt-4" style="font-size:55px"></i>
							<div class="p-2">
								<p><b>Confirm you want to release the report</b></p>
								By Releasing the report the following will happen:
								<ul>
									<li>System will schedule the report for statistical Analysis.</li>
									<li>Will move the report to Awaiting Approval Section </li>
									<li>Respective QC Approvers will be notified of the new qc report released</li>
								</ul>
								@if(sizeof(explode(',',$release_ids)) == 0)
								<div class="alert alert-danger p-2">
									The report has no data. You can release an empty report
								</div>
								@endif
								<input type="hidden" name="released_ids" value="{{$release_ids}}">
							</div>
						</div>
					</div>
					
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline-primary btn-sm" type="submit"><i class="mdi mdi-thumb-up"></i> Yes, Release</button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	$(() => {
		$('#qc_type_id').on('change', (e) => {
			$('#standard_id').empty();
			var qc_type_id = $('#qc_type_id').val()
			$.ajax({
				url: `/qualitycontrol/get/Qc-Standards/${qc_type_id}/Ajax`,
				method: `GET`,
				success: (data) => {
					$.each(data, (i, obj) => {
						var body = `<option value="${obj.id}">${obj.name}</option>`;
						$('#standard_id').append(body);
					})
				},
				error: (data) => {
					console.log(data);
				}
			})
			$('#standard_id').select2();
		})
		$('#sample_type_id').on('change', (e) => {
			$('#analysis_type_id').empty();
			$('#analysis_type_id').append(`<option>Loading...</option>`);
			var sample_type_id = $('#sample_type_id').val()
			$.ajax({
				url: `/qualitycontrol/get/Qc-Analysis-Types/${sample_type_id}/Ajax`,
				method: `GET`,
				success: (data) => {
					$('#analysis_type_id').empty();
					$.each(data, (i, obj) => {
						var body = `<option value="${obj.id}">${obj.name}</option>`;
						$('#analysis_type_id').append(body);
					})
				},
				error: (data) => {
					console.log(data);
				}
			})
			$('#analysis_type_id').select2();
		});
		$('#analysis_type_id').on('show.bs.modal',(e)=>{
			$('#analyte_id').empty();
			$('#analyte_id').append(`<option>Loading...</option>`);
			var analysis_type_id = $('#analysis_type_id').val();
			$.ajax({
				url:`/qualitycontrol/get/Analysis-Elements/By-Type-Id/${analysis_type_id}`,
				method:'GET',
				success:(data)=>{
					$('#analyte_id').empty();
					$.each(data,(i,obj)=>{
						var option = `<option value="${obj.analyte_id}">${obj.parametername}</option>`
						$('#analyte_id').append(option);
					});
				},
				error: (data) => {
					console.log(data);
				}
				
			})
		})
	})
</script>

@endsection