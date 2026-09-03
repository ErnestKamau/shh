@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Inventory Movement</title>
@endsection
@section('content2')

<main>
	@php
		$iitems = [
			['link' => route('inventory-home'), 'name' => inventoryLabel('module_name', 'Inventory Management'), 'icon' => null],
			['link' => route('inventory-activity'), 'name' => inventoryLabel('inventory_movement', 'Inventory Movement'), 'icon' => null],
		];
	@endphp
	<x-bread-crumb :items="$iitems"></x-bread-crumb>

	<div class="batch-header-bar mb-3">
		<div class="batch-header-top">
			<div class="batch-title-group">
				<span class="batch-code-label">{{ inventoryLabel('inventory_movement', 'Inventory Movement') }}</span>
				<span class="batch-stage-pill">
					<i class="mdi mdi-chart-areaspline"></i>
					{{ inventoryLabel('movement_logs', 'Movement Logs') }}
				</span>
			</div>
		</div>
	</div>

	<div class="card mb-4">
		<div class="card-header bg-light d-flex align-items-center justify-content-between py-2 px-3">
			<span class="font-weight-bold text-sm text-dark"><i class="mdi mdi-filter-variant mr-1 text-primary"></i> {{ inventoryLabel('filters', 'Filter Movement Records') }}</span>
		</div>
		<div class="card-body p-3">
			<div class="row align-items-end" style="row-gap: 0.75rem;">
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="form-group mb-0">
						<label class="control-label text-xs font-weight-bold">{{ inventoryLabel('cost_center', 'Cost Center') }}</label>
						<select class="form-control form-control-sm" id="change-department">
							<option value="">{{ inventoryLabel('all_cost_centers', 'All Cost Centers') }}</option>
							@foreach (getCostCenter() as $dp)
								<option value="{{ $dp }}" {{ ($term['department'] ?? '') == $dp ? 'selected' : '' }}>{{ $dp }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="form-group mb-0">
						<label class="control-label text-xs font-weight-bold">{{ inventoryLabel('classification', 'Classification') }}</label>
						<select class="form-control form-control-sm" id="change-classification" style="width: 100%">
							<option value="">{{ inventoryLabel('all_classifications', 'All Classifications') }}</option>
							@foreach (getInventoryItemClassification() as $in=>$css)
								<option value="{{ $in }}" {{ ($term['classification'] ?? '') == (string)$in ? 'selected' : '' }}>{{ $css }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="form-group mb-0">
						<label class="control-label text-xs font-weight-bold">{{ inventoryLabel('categories', 'Categories') }}</label>
						<select class="form-control form-control-sm" id="change-category" style="width: 100%">
							<option value="">{{ inventoryLabel('all_categories', 'All Categories') }}</option>
							@foreach (getInventoryCategories() as $cat)
								<option value="{{ $cat->id }}" {{ ($cat->id == ($term['category'] ?? '')) ? 'selected' : '' }}>{{ ucfirst($cat->name) }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="form-group mb-0">
						<label class="control-label text-xs font-weight-bold">{{ inventoryLabel('start_date', 'Start Date') }}</label>
						<input class="form-control form-control-sm" value="{{ $term['range'][0] ?? '' }}" type="date" id="change-start-date" />
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="form-group mb-0">
						<label class="control-label text-xs font-weight-bold">{{ inventoryLabel('end_date', 'End Date') }}</label>
						<input class="form-control form-control-sm" value="{{ $term['range'][1] ?? '' }}" type="date" id="change-end-date" />
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<button type="button" class="btn btn-primary btn-sm btn-block" id="filter-movement">
						<i class="mdi mdi-magnify"></i> {{ inventoryLabel('filter', 'Filter') }}
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="workflow-board-panel">
		<div class="table-responsive p-0">
			<table id="inventory-movement" data-url="{{ route('get-stock-movement') }}" class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
				<thead>
					<tr>
						<th>#</th>
						<th nowrap>{{ inventoryLabel('date', 'Date') }}</th>
						<th nowrap>SAP Code</th>
						<th nowrap>{{ inventoryLabel('item', 'Item') }}</th>
						<th nowrap>{{ inventoryLabel('code', 'Code') }}</th>
						<th nowrap>{{ __('inventory.stock_in') }}</th>
						<th nowrap>{{ __('inventory.stock_out') }}</th>
						<th nowrap>{{ inventoryLabel('uom', 'UoM') }}</th>
						<th nowrap>{{ inventoryLabel('cost_center', 'Cost Center') }}</th>
						<th nowrap>{{ inventoryLabel('store', 'Store') }}</th>
						<th nowrap>{{ inventoryLabel('slot', 'Slot') }}</th>
						<th nowrap>{{ inventoryLabel('category', 'Category') }}</th>
						<th nowrap>{{ inventoryLabel('request_code', 'Request Code') }}</th>
						<th nowrap>{{ inventoryLabel('brand', 'Brand') }}</th>
						<th nowrap>{{ inventoryLabel('purpose', 'Purpose') }}</th>
						<th nowrap>{{ inventoryLabel('line_comment', 'Line Comment') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($items as $item)
						<tr>
							<td>{{ $loop->iteration }}</td>
							<td nowrap>{{ $item->req_date ?? $item->created_at }}</td>
							<td nowrap><code>{{ $item->sap_code }}</code></td>
							<td nowrap><strong>{{ $item->sub_category }}</strong></td>
							<td nowrap><code>{{ $item->code }}</code></td>
							<td nowrap class="text-success font-weight-bold">{{ number_format($item->stock_in ?? 0, 3) }}</td>
							<td nowrap class="text-danger font-weight-bold">{{ number_format($item->stock_out ?? 0, 3) }}</td>
							<td nowrap>{{ $item->unit_type }}</td>
							<td nowrap>{{ $item->cost_center }}</td>
							<td nowrap>{{ $item->store }}</td>
							<td nowrap>{{ $item->slot }}</td>
							<td nowrap>{{ $item->category }}</td>
							<td nowrap><code>{{ $item->entity_code }}</code></td>
							<td nowrap>{{ $item->brand }}</td>
							<td nowrap>{{ $item->description ?? '-' }}</td>
							<td nowrap>{{ $item->comments ?? '-' }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
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