@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Procedure Capture Worksheets | Lab Management</title>
@include('layouts.lab.partials.lab-panel-theme-styles')
<style>
	.breadcrumb-container {
		margin-left: 0 !important;
		margin-right: 0 !important;
		margin-top: 12px;
		margin-bottom: 12px;
	}
	.lab-panel-theme .modal-content {
		border-radius: 10px;
		border: 1px solid var(--workflow-border);
		box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
	}
	.lab-panel-theme .modal-header {
		border-bottom: 1px solid #f1f5f9;
		background: var(--workflow-surface);
		border-radius: 10px 10px 0 0;
	}
</style>
@endsection

@section('content2')
<main>
	<?php
	$items = [
		[
			'link' => route('lab-home'),
			'name' => 'Lab',
			'icon' => null,
		],
		[
			'link' => route('formulars.index'),
			'name' => 'Worksheet Engine',
			'icon' => null,
		],
		[
			'link' => null,
			'name' => 'Procedure Worksheets',
			'icon' => null,
		],
	];
	?>
	<div class="px-4 lab-panel-theme">
		<x-bread-crumb :items="$items"></x-bread-crumb>

		@livewire('procedures.procedure-worksheet-manager')
	</div>
</main>
@endsection
