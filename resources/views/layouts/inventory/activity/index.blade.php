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
	<br>
	<div class="table-responsive bg-light p-4">
		<table id="inventory-movement" data-url="{{ route('get-stock-movement') }}" class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
			<thead class="bg-light p-2">
				<tr>
					<th>No</th>
					<th>Category</th>
					<th>Code</th>
					<th>Item</th>
					<th>Brand</th>
					<th>Store</th>
					<th>Slot</th>
					<th>Department</th>
					<th>{{ __('Stock In') }}</th>
					<th>{{ __('Stock Out') }}</th>
					<th>Type</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($items as $item)
					<tr>
						<td>{{ $loop->iteration }}</td>
						<td nowrap>{{ $item->category }}</td>
						<td nowrap>{{ $item->code }}</td>
						<td nowrap>{{ $item->sub_category }}</td>
						<td nowrap>{{ $item->brand }}</td>
						<td nowrap>{{ $item->store }}</td>
						<td nowrap>{{ $item->slot }}</td>
						<td nowrap>{{ $item->department }}</td>
						<td nowrap>{{ number_format($item->stock_in ?? 0) }} <small class="text-muted">{{ $item->unit_type }}</small> {{ $item->status }} {!! $item->status == 'pending' ? '<small class="text-muted">Pending</small>' : '' !!}</td>
						<td nowrap>{{ number_format($item->stock_out ?? 0) }} <small class="text-muted">{{ $item->unit_type }}</small></td>
						<td nowrap>{{ $item->entity_code }}</td>
						<td nowrap>{{ $item->created_at }}</td>
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