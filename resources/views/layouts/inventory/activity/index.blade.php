@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Inventory Movement</title>
@endsection
@section('content2')

<main>
	<?php
		$iitems = array(
			array(
				'link' => route('inventory-home'),
				'name' => 'Inventory Management',
				'icon' => null
			),
			array(
				'link' => route('inventory-activity'),
				'name' => 'Inventory Movement',
				'icon' => null
			)
		);
	?>
	<x-bread-crumb :items="$iitems"></x-bread-crumb>
	<h2 class="p-4">
		<i class="mdi mdi-format-list-bulleted-type"></i>Inventory Movement
	</h2>
	<div class="p-4">
		<div class="row">
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label">Cost Centers</label>
					<select class="form-control" id="change-department">
						<option value="">All Cost Centers</option>
						@foreach (getCostCenter() as $dp)
							<option value="{{ $dp }}" {{ $term['department']==$dp ? 'selected' : '' }}>{{ $dp }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label">Classification</label>
					<select class="form-control" id="change-classification" style="width: 100%">
						<option value="">All Classification</option>
						@foreach (getInventoryItemClassification() as $in=>$css)
							<option value="{{ $in }}" {{ $term['classification']==$in ? 'selected' : '' }}>{{ $css }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group" style="margin-right: 10px;">
					<label class="control-label">Categories</label>
					<select class="form-control" id="change-category" style="width: 100%">
						<option value="">All Categories</option>
						@foreach (getInventoryCategories() as $cat)
							<option value="{{ $cat->id }}" {{ $cat->id==$term['category'] ? 'selected' : '' }}>{{ ucfirst($cat->name) }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group" style="margin-right: 10px">
					<label class="control-label">Start Date</label>
					<input class="form-control" value="{{ $term['range'][0] ?? '' }}" type="date" id="change-start-date" />
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group" style="margin-right: 10px">
					<label class="control-label">End Date</label>
					<input class="form-control" value="{{ $term['range'][1] ?? '' }}" type="date" id="change-end-date" />
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group" style="margin-right: 10px">
					<label class="control-label">&nbsp;&nbsp;</label>
					<span class="btn btn-primary btn-sm" id="filter-movement" style="width:100%">
						<i class="mdi mdi-magnify"></i> Filter
					</span>
				</div>
			</div>
		</div>
	</div>
	<br>
	<div class="table-responsive bg-light p-4">
		<table id="inventory-movement" data-url="{{ route('get-stock-movement') }}" class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
			<thead class="bg-light p-2">
				<tr>
					<th>No</th>
					<th>Date</th>
					<th>SAP Code</th>
					<th>Item</th>
					<th>Code</th>
					<th>{{ __('inventory.stock_in') }}</th>
					<th>{{ __('inventory.stock_out') }}</th>
					<th>UoM</th>
					<th>Cost Center</th>
					<th>Store</th>
					<th>Slot</th>
					<th>Category</th>
					<th>Request Code</th>
					<th>Brand</th>
					<th>Purpose</th>
					<th>Line Comment</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($items as $item)
					<tr>
						<td>{{ $loop->iteration }}</td>
						<td nowrap>{{ $item->req_date ?? $item->created_at }}</td>
						<td nowrap>{{ $item->sap_code }}</td>
						<td nowrap>{{ $item->sub_category }}</td>
						<td nowrap>{{ $item->code }}</td>
						<td nowrap>{{ number_format($item->stock_in ?? 0, 3) }}</td>
						<td nowrap>{{ number_format($item->stock_out ?? 0, 3) }}</td>
						<td nowrap>{{ $item->unit_type }}</td>
						<td nowrap>{{ $item->cost_center }}</td>
						<td nowrap>{{ $item->store }}</td>
						<td nowrap>{{ $item->slot }}</td>
						<td nowrap>{{ $item->category }}</td>
						<td nowrap>{{ $item->entity_code }}</td>
						<td nowrap>{{ $item->brand }}</td>
						<td nowrap>{{ $item->description ?? '-' }}</td>
						<td nowrap>{{ $item->comments ?? '-' }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</main>
@endsection
@section('script2')
<script>
	$(function(){
		// $('#change-classification').on('change', function(){
		// 	var $url = '{{ route("inventory-activity") }}';
		// 	var $val = $(this).children('option:selected').val();
		// 	if($val != ""){
		// 		$url += '/'+$(this).children('option:selected').text()+'/'+$val;
		// 	}
		// 	window.location = $url;
		// });

		$('#filter-movement').on('click', function(){
			var $url = '{{ route("inventory-activity") }}';
			var filters = {};
			var $department = $('#change-department').val();
			var $classification = $('#change-classification').val();
			var $category = $('#change-category').val();
			var $startDate = $('#change-start-date').val();
			var $endDate = $('#change-end-date').val();

			if($department != ''){
				filters['department'] = $department;
			}
			if($classification != ''){
				filters['classification'] = $classification;
			}
			if($category != ''){
				filters['category'] = $category;
			}
			if($startDate != ''){
				filters['range'] = $startDate+'_'+$endDate;
			}

			var keys = Object.keys(filters);
			var values = Object.values(filters);

			if(keys.length > 0){
				$url += '/'+keys.join(',')+'/'+values.join(',');
			}

			window.location = $url;
		});

		// var tables = ["#inventory-movement"];
		// $.each(tables, function(t, tb){
		// 	var $url = $(tb).data('url');
		// 	var serverTable = $(tb).DataTable({
		// 		lengthMenu: [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
		// 		dom: 'Blfrtip',
		// 		buttons: [
		// 			'copy', 'csv', 'excel', 'pdf', 'print'
		// 		],
		// 		columns: [
		// 			{ data: "loop", "searchable": false },
		// 			{ data: "request_code" },
		// 			{ data: "description" },
		// 			{ data: "due_date" },
		// 			{
		// 				data: null,
		// 				className: "center",
		// 				render: function ( data, type, row ) {
		// 					$(row).find('td:eq(4)').attr('nowrap');
		// 					$(row).find('td:eq(4)').prop('nowrap');
		// 					return `<a class="btn btn-xs text-info btn-transparent" href='/req/${parent}/${data.parent_id}'>${data.parent_request_code}</a>
		// 					`;
		// 				}
		// 			},
		// 			{ data: "created_by" },
		// 			{ data: "created_at" },
		// 			{ data: "status" },
		// 			{
		// 				data: null,
		// 				className: "center",
		// 				render: function ( data, type, row ) {
		// 					$(row).find('td:eq(8)').attr('nowrap');
		// 					$(row).find('td:eq(8)').prop('nowrap');
		// 					return `<a class="btn btn-xs text-primary btn-transparent" href='/req/${ctype}/${data.id}'><i class="mdi mdi-eye"></i></a>
		// 					`;
		// 				}
		// 			}
		// 		],
		// 		destroy: true,
		// 		processing: true,
		// 		serverSide: true,
		// 		ajax: $url
		// 	});
		// });
	});

</script>
@endsection