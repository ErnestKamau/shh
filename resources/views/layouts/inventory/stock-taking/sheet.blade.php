<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- CSRF Token -->
		<meta name="csrf-token" content="{{ csrf_token() }}">

		@yield('title')

		<!-- Scripts -->
		<!-- <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.2.45/css/materialdesignicons.min.css"> -->

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	</head>
	<body onload="window.print()" style="background-color: white">
		<main>
			<h3 class="p-4">
				<i class="mdi mdi-format-list-bulleted-type"></i>Stock Taking - {{ $taking->code }} <small class="text-muted"><i class="mdi mdi-warehouse"></i> {{ $taking->store_names }} </small>
				{{-- <button class="btn btn-outline-info text-primary float-right"><i class="mdi mdi-printer"></i> Print Stock Taking Sheet</button> --}}
			</h3>
			<h6 class="pl-4 pr-4">Stock Taking Instructions</h6>
			<div class="p-4">
				<p>{!! $taking->description !!}</p>
				<br>
				@foreach ($dataByStore as $key=>$items)
					<hr>
					<h6><i class="mdi mdi-warehouse mt-2"></i> {{ $items[0]->store }}</h6>
					<div class="table-responsive mb-2">
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="font-size: 12px">
								<thead>
									<th>No.</th>
									<th>Code</th>
									<th>Item</th>
									<th>UoM</th>
									<th>Store</th>
									<th>Slot</th>
									<th>Lot No</th>
									<th>Expiry</th>
									<th>Recorded Quantity</th>
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
											<td></td>
											<td></td>
											<td></td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div style="page-break-before: always;"></div>
				@endforeach
			</div>
		</main>
	</body>
</html>