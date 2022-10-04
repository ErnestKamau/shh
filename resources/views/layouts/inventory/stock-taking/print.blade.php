<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- CSRF Token -->
	<title>{{ $taking->code }} - Stock Taking</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
	<div class="text-center pb-3">
		<img src="/images/sygenta.png" style="max-height: 150px" />
	</div>
	<hr>
	<div class="pv-2 text-center mb-2">
		<h4><span style="border-bottom:2px solid #000">{{ $taking->code }}</span> <small>Stores - {{ $taking->store_names }}</small></h4>
	</div>
	<br>
	<div class="main-table mt-2">
		@foreach ($dataByStore as $key=>$items)
			<hr>
			<h6><i class="mdi mdi-warehouse mt-2"></i> {{ $items[0]->store }}</h6>
			<div class="table-responsive mb-2">
				<div class="table-responsive">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-s">
						<thead>
							<th>No.</th>
							<th>Code</th>
							<th>Item</th>
							<th>Uom</th>
							<th>Store</th>
							<th>Slot</th>
							@if ($taking->reviewed)
								<th>System Quantity</th>
							@endif
							<th>Recorded Quantity</th>
							@if ($taking->reviewed)
								<th>Dev. Quantity</th>
								<th>Dev. Value</th>
							@endif
							<th>Comments</th>
						</thead>
						<tbody>
							@foreach($items as $item)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>
										{{ $item->code }}
										<input type="hidden" name="code[]" value="{{ $item->code }}" />
									</td>
									<td>
										{{ $item->name }}
										<input type="hidden" name="inventory_sub_category_id[]" value="{{ $item->item_id }}" />
									</td>
									<td>
										{{ $item->unit_type }}
									</td>
									<td>
										{{ $item->store }}
										<input type="hidden" name="store_name[]" value="{{ $item->store }}" />
										<input type="hidden" name="store_id[]" value="{{ $item->store_id }}" />
									</td>
									<td>
										{{ $item->slot }}
										<input type="hidden" name="slot_id[]" value="{{ $item->slot_id }}" />
										<input type="hidden" name="slot_name[]" value="{{ $item->slot }}" />
									</td>
									@if ($taking->reviewed)
									<td>{{ $item->quantity }}</td>
									@endif
									<td>
										<input type="hidden" name="system_quantity[]" value="{{ $item->quantity }}" />
										{{ number_format($item->available_quantity,2) }}
									</td>
									@if ($taking->reviewed)
										<?php
											$variation = floatval($item->quantity) - floatval($item->available_quantity);
											$var_direction = $variation > 0 ? 'below' : 'above';
											$var_perc = number_format(floatval($item->quantity) > 0 ? abs($variation)/floatval($item->quantity)*100 : 100, 2);
										?>
										<td nowrap>
											@if($item->available_quantity !==null)
												<span class="{{ $var_direction == 'below' ? 'text-danger' : 'text-success' }}">
													<i class="mdi mdi-{{ $var_direction == 'below' ? 'menu-down' : 'menu-up' }}"></i> {{ $var_perc }}%
													<small>({{ abs($variation) }})</small>
												</span>
											@else
												-
											@endif
										</td>
										<td nowrap>
											@if($item->available_quantity !==null)
												<span class="{{ $var_direction == 'below' ? 'text-danger' : 'text-success' }}">
													<i class="mdi mdi-{{ $var_direction == 'below' ? 'menu-down' : 'menu-up' }}"></i> {{ number_format($item->unit_price*abs($variation), 2) }}/=
												</span>
											@else
												-
											@endif
										</td>
									@endif
									<td>{{ $item->comments }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		@endforeach
	</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,600,700&display=swap" rel="stylesheet" type="text/css">
<script>
	window.print();
</script>
</html>