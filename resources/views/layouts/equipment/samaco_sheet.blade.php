@extends('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> Samaco Sheet</title>

<style type="text/css">
	.no-header th {
		color: #454545;
	}

	.hidden {
		display: none;
	}

	.btn-default {
		background-color: white !important;
		margin: 3px;
		padding: 3px !important;
		font-size: 13px !important;
	}
</style>
@endsection
@section('content2')
<main>
    <h4 class="p-3"><i class="mdi mdi-file-cog"></i> Samaco Sheet</h4>
    <div class="table-responsive">
        <table class="table table-condensed table-bordered table-sm table-striped table-hover"></table>
    </div>
</main>
@endsection
@section('script2')
@endsection