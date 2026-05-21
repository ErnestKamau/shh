@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>{{ $hybridWorksheet->name }} — Blocks | Lab Management</title>
@include('layouts.lab.partials.lab-panel-theme-styles')
@endsection

@section('content2')
<main>
	<?php
	$items = [
		['link' => route('lab-home'), 'name' => 'Lab', 'icon' => null],
		['link' => route('formulars.index'), 'name' => 'Worksheet Engine', 'icon' => null],
		['link' => route('formulars.hybrid-worksheets.manage'), 'name' => 'Hybrid Worksheets', 'icon' => null],
		['link' => null, 'name' => $hybridWorksheet->name.' v'.$version->version_number, 'icon' => null],
	];
	?>
	<div class="px-4 lab-panel-theme">
		<x-bread-crumb :items="$items"></x-bread-crumb>
		@livewire('hybrid-worksheets.hybrid-worksheet-block-editor', ['version' => $version])
	</div>
</main>
@endsection
