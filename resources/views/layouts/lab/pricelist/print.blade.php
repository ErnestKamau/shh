<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <?php $companyDetails = getCompanyDetails(); ?>
	<title>
		@if ($pricelist->is_master == 1)
			{{ $pricelist->currency()->name }} MASTER PRICELIST
		@else
			STANDARD {{ $pricelist->currency()->name }} PRICELIST
		@endif
	</title>
  <!-- Scripts -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css"/>
	
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<style type="text/css">
		.my-small-text{
			font-size: 12px;
		}
	</style>
</head>
<body onload="window.print()">
	<div class="container">
		<div class="row">
			<div class="col-sm-4 col-md-3">
				<img src="{{ $companyDetails['logo'] }}" style="width: 100%" />
			</div>
			<div class="col-sm-4 col-md-6 text-center">
				<h5 class="pt-3">
					@if ($pricelist->is_master == 1)
						{{ $pricelist->currency()->name }} MASTER PRICELIST
					@else
						STANDARD {{ $pricelist->currency()->name }} PRICELIST
					@endif
					<br>
					<small class="text-muted">Valid Until : {{ $pricelist->valid_till }}</small>
				</h5>
			</div>
			<div class="col-sm-4 col-md-3 align-right">
				<small class="float-right text-muted">{{ $pricelist->document_no }}</small><br>
				<small class="float-right text-muted">Rev.{{ str_pad($pricelist->revision_number, 2,"0", STR_PAD_LEFT) }}</small><br>
				<span class="float-right">{{ $companyDetails['name'] }}</span><br>
			</div>
		</div>
		<div class="table-responsive mt-4">
			<p class="p-2 my-small-text well-sm border rounded" style="color:#676767"><b>Description:</b> {{ $pricelist->description }}</p>
			<table class="table table-condensed my-small-text table-bordered">
				<thead>
					<tr>
						<th>Code</th>
						<th>Analysis</th>
						<th>Details</th>
						<th>Price</th>
						<th>VAT?</th>
						<th>Duration <small class="text-muted">(Days)</small></th>
					</tr>
				</thead>
				<tbody>
					<?php $data = $pricelist->items_by_sample_type() ?>
					@foreach ($data['analysis'] as $sample_type => $items)
						<tr><th colspan="6">{{ $sample_type }}</th></tr>
						@foreach ($items as $type=>$item)
							@if($item->external_view == 1)
								<tr>
									<td>{{ $item->analysis_type_code }}</td>
									<td>{{ $item->analysis_type_name }}</td>
									<td>{{ implode(",", $data['analytes'][$sample_type][$type]) }}</td>
									<td>{{ $pricelist->currency()->name }} {{ number_format($item->selling_price,2) }}</td>
									<td>{!! $item->vat == 1 ? '<i class="mdi mdi-check-circle text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
									<td>{{ $item->reporting_time }}</td>
								</tr>
							@endif
						@endforeach
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</body>
</html>