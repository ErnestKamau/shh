@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Hybrid Worksheets | Lab Management</title>
@include('layouts.lab.partials.lab-panel-theme-styles')
@endsection

@section('content2')
<main>
	<?php
	$items = [
		['link' => route('lab-home'), 'name' => 'Lab', 'icon' => null],
		['link' => route('formulars.index'), 'name' => 'Worksheet Engine', 'icon' => null],
		['link' => null, 'name' => 'Hybrid Worksheets', 'icon' => null],
	];
	?>
	<div class="px-4 lab-panel-theme">
		<x-bread-crumb :items="$items"></x-bread-crumb>
		@livewire('hybrid-worksheets.hybrid-worksheet-manager')
	</div>
</main>
@endsection
