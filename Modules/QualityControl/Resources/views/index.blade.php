@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Quality Control | Qc</title>
@endsection
@section('content2')
<main>
	<?php
	$items = array(
		array(
			'link' =>'#',
			'name' => 'Qc',
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => 'Configuration',
			'icon' => null
		)
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h2 class="p-4">
		<i class="mdi mdi-account-group"></i> QC Module
		
	</h2>
	<br>
	
</main>
@endsection

@section('script2')



<script>

</script>
@endsection