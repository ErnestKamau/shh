@extends('layouts.lab.layout.app', [ 'datePicker' => true, 'select2' => true])

@section('title2')
<title>Supporting document | Request #{{ $submissionRequest->id }}</title>
@endsection

@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme workflow-theme">
	@include('layouts.lab.partials.lab-panel-theme-styles')

	<?php
	$items = [
		[
			'link' => route('dashboard-lab'),
			'name' => 'Dashboard',
			'icon' => null,
		],
		[
			'link' => route('sample-submission-requests.index'),
			'name' => 'Submission Requests',
			'icon' => null,
		],
		[
			'link' => route('sample-submission-requests.show', ['request' => $submissionRequest, 'details' => 1]),
			'name' => 'Request #' . $submissionRequest->id,
			'icon' => null,
		],
		[
			'link' => route('sample-submission-requests.supporting-documents.edit', [$submissionRequest, $instance]),
			'name' => 'Supporting document',
			'icon' => null,
		],
	];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	@if(session('error'))
	<div class="alert alert-danger mb-3" style="margin: 0 15px;">{{ session('error') }}</div>
	@endif

	<livewire:sampleworkflow.submission-supporting-document-fill
		:submission-request-id="$submissionRequest->id"
		:instance-id="$instance->id"
		wire:key="sd-fill-{{ $instance->id }}"
	/>
</main>
@endsection
