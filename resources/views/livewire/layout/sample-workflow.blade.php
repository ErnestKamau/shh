@extends('layouts.lab.layout.app', [ 'datePicker' => true, 'select2' => true])

@section('title2')
<title>{{ $status }} | Sample WorkFlow</title>
@endsection

@section('content2')
<main>
	<?php
$items = [
	[
		'link' => route('dashboard-lab'),
		'name' => 'Dashboard',
		'icon' => null,
	],
	[
		'link' => route('sample-workflow', ['status' => 'All Samples']),
		'name' => 'Sample Workflow',
		'icon' => null,
	],
	[
		'link' => route('sample-workflow', ['status' => $status]),
		'name' => $status,
		'icon' => null,
	],
];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	@livewire('sampleworkflow.workflow-board', [
		'status' => $status,
		'initialFilters' => $initialFilters ?? [],
	])
</main>
@endsection
@section('script2')
@stack('script2')
@endsection



