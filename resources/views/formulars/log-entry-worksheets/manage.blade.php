@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Log Entry Worksheets | Lab Management</title>
@include('layouts.lab.partials.lab-panel-theme-styles')
<style>
	.breadcrumb-container { margin-left: 0 !important; margin-right: 0 !important; margin-top: 12px; margin-bottom: 12px; }
</style>
@endsection

@section('content2')
<main>
	<?php
	$items = [
		['link' => route('lab-home'), 'name' => 'Lab', 'icon' => null],
		['link' => route('formulars.index'), 'name' => 'Worksheet Engine', 'icon' => null],
		['link' => null, 'name' => 'Log Entry Worksheets', 'icon' => null],
	];
	?>
	<div class="px-4 lab-panel-theme">
		<x-bread-crumb :items="$items"></x-bread-crumb>
		@livewire('log-entry-worksheets.log-entry-worksheet-manager')
	</div>
</main>
@endsection
