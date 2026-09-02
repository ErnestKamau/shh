@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> {{ $title }} - Reports | {{ config('app.name', 'Laravel') }}</title>
	<link type="text/css" rel="stylesheet" href="/assets/css/theme-default/libs/select2/select2.css?1424887856" />
  	<link type="text/css" rel="stylesheet" href="/assets/css/theme-default/libs/morris/morris.core.css?1420463396" />
	<style type="text/css">
		.table-row{
			margin-bottom: 15px;
			border-bottom: 3px solid #efefef;
		}

		.table-row:last-child{
			border-bottom: none !important;
		}

		.table-row:nth-child(even){
			background-color: rgba(0,0,0,0.03) !important;
		}

		#unsaved-query{
			position: fixed;
			bottom: 0px;
			right: 0px;
			z-index: 99;
		}

		span.select2.select2-container{
			width: 100% !important;
			min-width: unset !important;
		}

		.select2-container--default .select2-selection--single .select2-selection__rendered {
			font-size: 12px;
		}
	</style>
	@endsection
@section('content2')
<section>
	<?php
		$items = array(
			array(
				'link' => route('inventory-home'),
				'name' => 'Inventory Management',
				'icon' => null
			),
			array(
				'link' => route('inventory-reports'),
				'name' => 'Reports',
				'icon' => null
			),
			array(
				'link' => '#',
				'name' => $title." - Report",
				'icon' => null
			)
		);

		$startYear = 2021;
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="batch-header-bar mb-3">
		<div class="batch-header-top">
			<div class="batch-title-group">
				<span class="batch-code-label">{{ $title }} Reports</span>
				<span class="batch-stage-pill">
					<i class="mdi mdi-chart-bell-curve-cumulative"></i>
					{{ inventoryLabel('consumption_analytics', 'Consumption Analytics') }}
				</span>
			</div>
		</div>
	</div>

	<form class="card p-3 mb-3">
		<h5><i class="mdi mdi-filter"></i> Filters</h5>
		<div class="row no-gutters">
			<div class="col-md-4">
				<div class="form-group">
					<select class="form-control" name="category" date-placeholder="Select Category...">
						<option value="">Select Item Category...</option>
						@foreach ($categories as $c)
							<option value="{{ $c->name }}" {{ $c->name == Request::get('category') ? 'selected' : '' }}>{{ $c->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-md-3">
				<div class="form-group">
					<select class="form-control" name="filter_item" date-placeholder="Select Item...">
						<?php  $selectedItem = Request::get('filter_item') ?? ""; ?>
						@foreach ($sub_cats as $sc=>$vv)
							<option value="{{ $vv }}" {{ $selectedItem == $sc ? 'selected' : '' }}>{{ $sc }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group">
					<select class="form-control" name="month" date-placeholder="Select Month...">
						@for ($m=1; $m<=12; $m++)
							<?php $cmonth = date('F', mktime(0,0,0,$m, 1, date('Y'))); ?>
							<option value="{{ $cmonth }}" {{ $cmonth == $month ? 'selected' : '' }}>{{ $cmonth }}</option>
						@endfor
					</select>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group">
					<select class="form-control" name="year" date-placeholder="Select Year...">
						@for ($m=$startYear; $m<=date('Y'); $m++)
							<option value="{{ $m }}" {{ $m == $year ? 'selected' : '' }}>{{ $m }}</option>
						@endfor
					</select>
				</div>
			</div>
			<div class="col-md-1">
				<div class="form-group">
					<button class="btn btn-sm btn-success btn-block">
						<i class="mdi mdi-filter-outline"></i> Filter
					</button>
				</div>
			</div>
		</div>
	</form>
	<br>
	<div class="table-responsive">
		@if(count($theData) > 0)
			<div class="table-responsive">
				<table class="table table-condensed table-striped table-sm table-bordered">
					<thead>
						<tr class="text-danger">
							<th>No</th>
							@foreach ($columns['main'] as $c)
								<th>{{ $c }}</th>
							@endforeach
							@foreach (array_keys($topColumns) as $c)
								<th colspan="{{ count($topColumns[$c]) }}" class="text-center">{{ $c }}</th>
							@endforeach
						</tr>
						<tr class="text-primary">
							<th colspan="{{ count($columns['main'])+1 }}"></th>
							@foreach ($topColumns as $c=>$cls)
								@foreach ($cls as $l)
									<th><span style="font-size:0px">{{ $c }} :</span> {{ $l}}</th>
								@endforeach
							@endforeach
						</tr>
					</thead>
					<tbody>
						<?php
							$footerTotals = [];
						?>
						@foreach ($theData as $tD)
							<tr>
								<td>{{ $loop->iteration }}</td>
								@foreach ($columns['main'] as $c)
									<td nowrap>{{ $tD[$c] }}</td>
								@endforeach
								@foreach ($topColumns as $c=>$cls)
									<?php
										if(!isset($footerTotals[$c])){
											$footerTotals[$c] = [];
										}
									?>
									@foreach ($cls as $l)
										<?php
											if(!isset($footerTotals[$c][$l])){
												$footerTotals[$c][$l] = ['quantity'=>0, 'price'=>0];
											}
											$footerTotals[$c][$l]['quantity'] += ($tD[$c][$l]['quantity'] ?? 0);
											$footerTotals[$c][$l]['price'] += ($tD[$c][$l]['price'] ?? 0);
										?>
										<td class="text-right">{{ $tD[$c][$l]['quantity'] ?? 0 }}</td>
									@endforeach
								@endforeach
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr style="font-weight: 600; text-align: right">
							<td colspan="4">TOTALS</td>
							@foreach ($topColumns as $c=>$cls)
								@foreach ($cls as $l)
									<td class="text-right">{{ number_format($footerTotals[$c][$l]['quantity'] ?? 0, 2) }}</td>
								@endforeach
							@endforeach
						</tr>
						<tr style="margin-top:20px; font-weight: 600; text-align: right">
							<td colspan="4" style="background-color: #fbead0">Unit value in Ksh</td>
							@foreach ($topColumns as $c=>$cls)
								@foreach ($cls as $l)
									<td class="text-right" style="background-color: #f0fbd0">{{ number_format(floatval($footerTotals[$c][$l]['price']) == 0 ? "0" : (floatval($footerTotals[$c][$l]['price'])/floatval($footerTotals[$c][$l]['quantity'])), 2) }}</td>
								@endforeach
							@endforeach
						</tr>
						<tr style="text-align: right">
							<td colspan="4" style="background-color: #fbead0"><strong>Total value in Ksh</strong></td>
							@foreach ($topColumns as $c=>$cls)
								@foreach ($cls as $l)
									<td class="text-right" style="background-color: #f0fbd0">{{ number_format($footerTotals[$c][$l]['price'] ?? 0, 2) }}</td>
								@endforeach
							@endforeach
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="table-responsive">
				<hr style="margin: 40px 0px">
				<table class="table table-condensed table-striped table-sm table-bordered">
					<thead>
						<tr>
							<th>Week</th>
							@foreach (array_keys($topColumns) as $c)
								<th colspan="{{ count($topColumns[$c]) }}" class="text-center">{{ $c }}</th>
							@endforeach
						</tr>
						<tr class="text-primary">
							<th colspan="1"></th>
							@foreach ($topColumns as $c=>$cls)
								@foreach ($cls as $l)
									<th><span style="font-size:0px">{{ $c }} :</span> {{ $l}}</th>
								@endforeach
							@endforeach
						</tr>
					</thead>
					<tbody>
						<?php
							$wfooterTotals = [];
							$ccTotals = [];
						?>
						@foreach ($theWeekData as $week => $data)
							<?php
								if(!isset($wfooterTotals[$c])){
									$wfooterTotals[$c] = [];
								}
							?>
							<tr>
								<td class="text-right">{{ $week }}</td>
								@foreach ($topColumns as $c=>$cls)
									@foreach ($cls as $l)
										<?php
											if(!isset($wfooterTotals[$c][$l])){
												$wfooterTotals[$c][$l] = ['quantity'=>0, 'price'=>0];
											}

											if(!isset($ccTotals[$c])){
												$ccTotals[$c] = ["total"=>0, "items"=>[]];
											}

											if(!isset($ccTotals[$c]['items'][$l])){
												$ccTotals[$c]['items'][$l] = 0;
											}

											$ccTotals[$c]['total'] += ($data[$c][$l]['quantity'] ?? 0);

											$ccTotals[$c]['items'][$l] += ($data[$c][$l]['quantity'] ?? 0);

											$wfooterTotals[$c][$l]['quantity'] += ($data[$c][$l]['quantity'] ?? 0);
											$wfooterTotals[$c][$l]['price'] += ($data[$c][$l]['price'] ?? 0);
										?>
										<td class="text-right">{{ number_format($data[$c][$l]['quantity'] ?? 0, 2) }}</td>
									@endforeach
								@endforeach
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr style="font-weight: 600; text-align: right">
							<td class="text-danger">TOTALS</td>
							@foreach ($topColumns as $c=>$cls)
								@foreach ($cls as $l)
									<td class="text-right">{{ number_format($wfooterTotals[$c][$l]['quantity'] ?? 0, 2) }}</td>
								@endforeach
							@endforeach
						</tr>
						<tr style="font-weight: 600; text-align: right">
							<td class="text-danger">DEVIATION</td>
							@foreach ($topColumns as $c=>$cls)
								@foreach ($cls as $l)
									<td class="text-right">{{ number_format(floatval($wfooterTotals[$c][$l]['quantity'])-floatval($footerTotals[$c][$l]['quantity']) ?? 0, 2) }}</td>
								@endforeach
							@endforeach
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="table-responsive">
				<hr style="margin: 40px 0px">
				<table class="table table-condensed table-striped table-sm table-bordered w-auto" style="float: left !important; width: 30% !important">
					<?php $totals = 0; ?>
					<tbody>
						@foreach ($ccTotals as $c =>$v)
							<tr>
								<th colspan="2">{{ ucwords($c) }}</th>
							</tr>
							@foreach ($v['items'] as $i=>$t)
								<tr>
									<th style="color:#454545; font-size: 11px">{{ ucwords($i) }}</th>
									<td class="text-right">{{ number_format($t, 2) }}</td>
								</tr>
							@endforeach
							<tr>
								<th class="text-danger">TOTALS</th>
								<td class="text-right">{{ number_format($v['total'], 2) }}</td>
							</tr>
							<tr>
								<td colspan="2" style="padding: 2px; border: none!important">&nbsp;</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@else
			<div class="alert alert-info text-center"><i class="mdi mdi-alert"></i> No Data available. Ensure that you have selected the correct Category, Item, Month and Year.</div>
		@endif
	</div>
</section>
@endsection
@section('script2')
<script>
	$(function(){
		var defCat = '';
		var url = '{{ route("get_items_via_ajax") }}';
		$('select[name="category"]').on('change', function(){
			$('select[name="filter_item"]').val('').trigger('change');
			defCat = $(this).val();
			$('select[name="filter_item"]').select2({
				ajax: {
					url: defCat == '' ? url : url+'/'+defCat+'/name',
					data: function (params) {
						var query = {
							search: params.term,
							page: params.page || 1
						}
						return query;
					}
				},
				placeholder: 'Please Select Inventory Item...'
			});
		});
	});
</script>
@endsection