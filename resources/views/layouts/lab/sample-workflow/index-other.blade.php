@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title>Contact Split</title>

<style>
	.form-part-toggler {
		margin: 0px 0px 5px 0px !important;
		padding: 6px 6px 6px 6px;
		border-bottom: 1px solid rgba(0, 0, 0, 0.09);
		cursor: pointer;
	}

	.form-part-toggler:hover {
		background-color: rgba(0, 0, 0, 0.08);
	}

	#sample-detail-rows .form-group {
		display: none;
	}

	#sample-detail-rows tr.selected-row {
		background-color: rgb(253, 220, 220);
	}

	#sample-detail-rows .text {
		display: unset;
	}

	#sample-detail-rows tr.editable .form-group {
		display: unset;
	}

	#sample-detail-rows tr.editable .text {
		display: none;
	}

	#sample-detail-rows tr {
		cursor: pointer;
	}

	.hidden {
		display: none;
	}

	.overdue-bg-color {
		background-color: rgba(240, 185, 83, 0.972) !important;
	}

	.upfront-bg-color {
		background-color: skyblue !important;
	}

	.ammend-bg-color {
		background-color: #fef764 !important;
	}

	.btn-white {
		background-color: white !important;
	}

	.badge-active {
		background-color: white !important;
		color: black;
	}
</style>
@endsection
@section('content2')
<main>
	<?php
$items = array(
	array(
		'link' => route('dashboard-lab'),
		'name' => 'Dashboard',
		'icon' => null
	),
	array(
		'link' => route('sample-workflow', ['status' => 'All Samples']),
		'name' => 'Sample Workflow',
		'icon' => null
	),
);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-4">
		<span class="float-left"><i class="mdi mdi-file-document-edit"></i> Contact Split</span>
	</h4>

	<div class="table-responsive bg-light mt-3 p-4">
		<table class="table table-condensed my-small-text table-bordered table-sm">
            <thead>
                <th>Reference</th>
                <th>Name</th>
                <th>Class</th>
                <th>Address</th>
                <th>Relation</th>
            </thead>
            <tbody>
                @foreach ($contacts as $contact)
                    @if(sizeof($contact->refine_address) >= 1)
                        @foreach($contact->refine_address as $r_address)
                            <tr>
                                <td>{{$contact->reference}}</td>
                                <td>{{$contact->name}}</td>
                                <td>{{$contact->class}}</td>
                                <td>{{$r_address['address']}}</td>
                                <td>{{$r_address['relation']}}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{$contact->reference}}</td>
                            <td>{{$contact->name}}</td>
                            <td>{{$contact->class}}</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
			
		</table>
		
	</div>
</main>
@endsection

@section('script2')

@endsection