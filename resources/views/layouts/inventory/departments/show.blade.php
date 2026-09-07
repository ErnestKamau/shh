@extends('layouts.inventory.layout.app', ['dataTable'=>true])

@section('title2')
<title>{{ $department->name }} | Inventory Departments</title>
@endsection
@section('content2')

<main>
	<?php
		$items = array(
			array(
				'link' => route('inventory-home'),
				'name' => 'Inventory Management',
				'icon' => null
			),
			array(
				'link' => route('show-inventory-departments'),
				'name' => 'Departments',
				'icon' => null
			),
			array(
				'link' => '#',
				'name' => $department->name,
				'icon' => null
			)
		);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="batch-header-bar mb-3">
		<div class="batch-header-top">
			<div class="batch-title-group">
				<span class="batch-code-label">{{ $department->name }}</span>
				<span class="batch-stage-pill">
					<i class="mdi mdi-domain"></i>
					{{ inventoryLabel('department_activity', 'Department Activity') }}
				</span>
			</div>
		</div>
	</div>

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-body p-0">
		<div class="table-responsive">
			<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
			<thead class="bg-light p-2">
				<tr>
					<th>No</th>
					<th>Category</th>
					<th>Sub Category</th>
					<th>Manufacturer</th>
					<th>Stock In</th>
					<th>Stock Out</th>
					<th>Checked By</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($department->inventory_items as $item)
					<tr>
						<td>{{ $loop->iteration }}</td>
						<td>{{ $item->category->name }}</td>
						<td>{{ $item->sub_category->name }}</td>
						<td>{{ $item->sub_category->manufacturer }}</td>
						<td>{{ number_format($item->stock_in, $item->sub_category->reporting_decimal_places) }} {{ $item->sub_category->unit_type }}</td>
						<td>{{ number_format($item->stock_out, $item->sub_category->reporting_decimal_places) }} {{ $item->sub_category->unit_type }}</td>
						<td>{{ $item->creator->name }}<small>< {{ $item->creator->email }} ></small></td>
						<td>{{ $item->created_at }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</main>
@endsection
