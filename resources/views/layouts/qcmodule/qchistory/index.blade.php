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
		<form action="{{route('generateQCReport')}}" method="post">
			@csrf
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
							<select name="analyte_id" id="" class="form-control">
							<option value="">Analyte</option>
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
	
				</div>
			</div>
			<div class="card-footer">
				<button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
			</div>
		</form>
	</div>
	<div class="card mt-3">
		<div class="spn-header text-bold pl-3 pt-3">Report Parameters :</div>
		<div class="card-body row">
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Max Value : {{isset($data['max']) ? $data['max'] : '-'}}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Min Value : {{isset($data['min']) ? $data['min'] : '-'}}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Median : {{isset($data['median']) ? $data['median'] : '-'}}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Average : {{isset($data['average']) ? $data['average'] : '-'}}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> STD Deviations : {{isset($data['std']) ? $data['std'] : '-' }}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Cv : {{isset($data['cv']) ? $data['cv'] : '-'}}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Statistical Population Size : {{isset($data['population']) ? $data['population'] : '-'}} </span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Z-Score : {{isset($data['z_score'])}}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Std Star : {{isset($data['std_star']) ? $data['std_star'] : '-'}}</span>
			</div>
			<div class="col-md-3">
				<span><i class="mdi mdi-chevron-right"></i> Cv Star : {{isset($data['cv_star']) ? $data['cv_star'] : '-'}}</span>
			</div>
		</div>
	</div>
	<div class="card mt-4" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
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
			$('#standard_id').select2();
		})
		$('#sample_type_id').on('change',(e)=>{
			$('#analysis_type_id').empty();
			var sample_type_id = $('#sample_type_id').val()
			$.ajax({
				url:`/qualitycontrol/get/Qc-Analysis-Types/${sample_type_id}/Ajax`,
				method:`GET`,
				success:(data)=>{
					$.each(data,(i,obj)=>{
						var body = `<option value="${obj.id}">${obj.name}</option>`;
						$('#analysis_type_id').append(body);
					})
				},
				error:(data)=>{
					console.log(data);
				}
			})
			$('#analysis_type_id').select2();
		})
	})
</script>

@endsection