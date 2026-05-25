@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>{{ $worksheet->name }} — Log Entry | Lab Management</title>
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
		['link' => route('formulars.log-entry-worksheets.manage'), 'name' => 'Log Entry Worksheets', 'icon' => null],
		['link' => null, 'name' => $worksheet->name, 'icon' => null],
	];
	?>
	<div class="px-4 lab-panel-theme">
		<x-bread-crumb :items="$items"></x-bread-crumb>
		@livewire('log-entry-worksheets.log-entry-worksheet-editor', ['worksheet' => $worksheet])
	</div>
</main>
@endsection
