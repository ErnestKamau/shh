@extends('layouts.workorder.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Dashboard | Work Order Management</title>
@endsection
@section('content2')
<main>
	<?php
	$items = array(
		array(
			'link' => route('workorder-home'),
			'name' => 'Workorder Management',
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => 'Dashboard',
			'icon' => null
		)
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h2 class="p-2">
		<i class="mdi mdi-clipboard-plus-outline"></i> Work Order
		@if(isset($account_settings->id))
		<button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-customer"><i class="mdi mdi-plus"></i> Add</button>
		@else
		<a href="{{ route('add-config-customer') }}" class="btn btn-sm btn-outline-primary float-right"><i class="mdi mdi-plus"></i> Add</a>
		@endif
	</h2>
	<hr>

@endsection
@section('scripts2')
	<script src="/assets/js/libs/raphael/raphael-min.js"></script>
	<script src="{{ asset('assets/js/libs/morris.js/morris.min.js') }}"></script>

@endsection