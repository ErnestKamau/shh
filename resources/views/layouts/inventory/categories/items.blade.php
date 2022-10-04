@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> {{ $category->name }} | Inventory Item</title>
	<style type="text/css">
		.tab-card {
			border:1px solid #eee;
		}

		.tab-card-header {
			background:none;
		}

		.rating-star{
			padding: 3px;
			font-size:15px;
			color: #bbb;
		}

		.rating-star:hover, .rating-star.selected{
			cursor: pointer;
			color: rgb(123, 165, 24);
		}

		/* Default mode */
		.tab-card-header > .nav-tabs {
			border: none;
			margin: 0px;
		}
		.tab-card-header > .nav-tabs > li {
			margin-right: 2px;
		}
		.tab-card-header > .nav-tabs > li > a {
			border: 0;
			border-bottom:2px solid transparent;
			margin-right: 0;
			color: #737373;
			padding: 2px 15px;
		}

		#item-status-bar .text{
			overflow-x: hidden;
			text-overflow: ellipsis;
			white-space: nowrap
		}

		.tab-card-header > .nav-tabs > li > a.show {
			border-bottom:2px solid #007bff;
			color: #007bff;
		}
		.tab-card-header > .nav-tabs > li > a:hover {
			color: #007bff;
		}

		.tab-card .nav-link.active{
			background-color: #dadccd !important;
			border: 1px solid #cccebf !important;
		}

		.tab-card-header > .tab-content {
			padding-bottom: 0;
		}

		.my-small-text{
			font-size: 12px !important;
		}

	</style>
@endsection
@section('content2')
	<main>
		<?php $systemUnitsofMeasure = getReportingUnits(); ?>
		<?php
			$itemBarcodeNo = pad_str($category->id, 2)."".pad_str($subcategory->id, 4);
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('inventory-categories'),
          'name' => 'Categories',
          'icon' => null
        ),
        array(
          'link' => route('show-inventory-category', ['id'=>$category->id]),
          'name' => $category->name,
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $subcategory->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<h3 class="p-4">
			<i class="mdi mdi-package-variant"></i> {{ $category->name }}
			<small class="text-muted"> | Item
				@if($subcategory->requires_reorder==1)
					<span class="badge badge-pill badge-danger">
						<i class="mdi mdi-information-triangle"></i> Restock Required
					</span>
				@endif
			</small>
			{{-- <div class="btn btn-sm btn-transparent text-primary float-right m-2" data-target="#transfer-inventory-items" data-toggle="modal"><i class="mdi mdi-share"></i> Issuing</div>
			<div class="btn btn-sm btn-transparent text-success float-right m-2" data-target="#add-inventory-items" data-toggle="modal"><i class="mdi mdi-plus"></i> Receiving</div>
			<div class="btn btn-sm btn-transparent text-info float-right m-2" data-target="#stock-keeping-modal" data-toggle="modal"><i class="mdi mdi-clipboard-arrow-left"></i> Stock Taking</div> --}}
			{{-- <div class="btn btn-sm btn-transparent text-danger float-right m-2" data-target="#item-disposal-modal" data-toggle="modal"><i class="mdi mdi-trash-can"></i> Item Disposal</div> --}}
			{{-- <div class="btn btn-sm btn-transparent text-primary float-right m-2" data-target="#item-return-modal" data-toggle="modal"><i class="mdi mdi-keyboard-return"></i> Item Return</div> --}}
			<br>
			<div class="p-2"><span class="barcode">{!! DNS1D::getBarcodeSVG($itemBarcodeNo, 'C128B') !!}</span> <span class="btn btn-sm btn-transparent print-barcode"><i class="mdi mdi-printer text-info"></i></span></div>
		</h3>

		{{-- <pre>{{ json_encode($subcategory->sorted_items(), JSON_PRETTY_PRINT) }}</pre> --}}
		<?php $sorteddata = $subcategory->sorted_items(); ?>
		<?php $excludedKeys = array("Returned 2 Store", "Item Disposal", "Stock Taking", "items"); ?>
		<div class="row no-gutters">
			<div class="col-sm-4 p-2">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title"><i class="mdi mdi-pencil-outline"></i> Edit Inventory Item</h5>
						<form method="POST" action="{{ route('edit-inventory-sub-category', ['id'=>$subcategory->id]) }}" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="category_id" value="{{ $category->id }}">
							<div class="form-group">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="{{ $subcategory->name }}" placeholder="Name..." required />
							</div>
							<div class="form-group">
								<label class="control-label">CAT/Lot No</label>
								<input type="text" class="form-control" name="sap_code" value="{{ $subcategory->sap_code }}" placeholder="CAT/Lot No..." />
							</div>
							<div class="form-group">
								<label class="control-label">Description</label>
								<textarea class="form-control" name="description" placeholder="Description..." required>{{ $subcategory->description }}</textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Sub Categories</label>
								<select name="sub_category_id" id="" class="form-control">
									<option value=""> Select Sub Category</option>
									@foreach(getInventorySubs() as $sub)
									<option value="{{$sub->id}}" {{$subcategory->sub_category_id == $sub->id ? 'selected' : ''}}>{{$sub->value}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-sm-4">
										<img src="{{ $subcategory->image }}" style="width: 100%" />
									</div>
									<div class="col-sm-8">
										<label class="control-label">Image</label>
										<input type="file" class="form-control" name="image"  />
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Manufacturer</label>
								<input type="text" class="form-control" name="manufacturer" value="{{ $subcategory->manufacturer }}" placeholder="Manufacturer..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Maximum Order Quantity</label>
								<input type="number" min="0" class="form-control" name="maximum_order_quantity" value="{{ $subcategory->maximum_order_quantity }}" placeholder="Maximum Order Quantity..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Item Classification</label>
								<select class="form-control" name="item_classification">
									<option value="">Select Item Classification...</option>
									@foreach (getInventoryItemClassification() as $g=>$c)
										<option value="{{ $g }}"  {{ $subcategory->item_classification == $g ? 'selected' : '' }}>{{ $c }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Unit of Measure</label>
								<select class="form-control" name="unit_type">
									<option value="">Select Unit of Measure...</option>
									@foreach ($systemUnitsofMeasure as $g)
										<option value="{{ $g['name'] }}" {{ $subcategory->unit_type == $g['name'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Issuing Unit of Measure</label>
								<select class="form-control" name="secondary_unit_type">
									<option value="">Select Unit of Measure...</option>
									@foreach (getReportingUnits() as $g)
										<option value="{{ $g['name'] }}" {{ $subcategory->secondary_unit_type == $g['name'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
									@endforeach
								</select>
							</div>
							<?php
								$conversionExists = getUoMConverstion(1, $subcategory->unit_type, $subcategory->secondary_unit_type);
							?>
							@if (!$conversionExists)
							<div class="form-group">
								<div class="alert alert-danger">
									<i class="mdi mdi-information"></i> No conversion exists for the selected Units of Measure. Click <a href="{{ route('view-uom-conversions') }}"><b>here</b></a> to configure conversion
								</div>
							</div>
							@endif
							<div class="form-group">
								<label class="control-label">Cash Price</label>
								<input type="number" min="0" class="form-control" name="unit_price" value="{{ $subcategory->unit_price }}" placeholder="Unit Price..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Credit Price</label>
								<input type="number" min="0" class="form-control" name="unit_price_credit" value="{{ $subcategory->unit_price_credit }}" placeholder="Credit Price..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Annual Consumption</label>
								<input type="number" min="0" class="form-control" name="annual_consumption" value="{{ $subcategory->annual_consumption }}" placeholder="Annual Consumption..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Working Days</label>
								<input type="number" min="0" class="form-control" value="{{ $subcategory->working_days }}" name="working_days" placeholder="Working Days..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Estimated variation in demand as a %of average consumption</label>
								<input type="number" class="form-control" value="{{ $subcategory->estimated_variation_in_demand_average_consumption }}" name="estimated_variation_in_demand_average_consumption" placeholder="Estimated variation in demand as a %of average consumption..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Internal Lead Time</label>
								<input type="number" class="form-control" value="{{ $subcategory->internal_lead_time }}" name="internal_lead_time" placeholder="Internal Lead Time..." required />
							</div>
							<div class="form-group">
								<label class="control-label">External Lead Time</label>
								<input type="number" class="form-control" value="{{ $subcategory->external_lead_time }}" name="external_lead_time" placeholder="External Lead Time..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Material Type</label>
								<select class="form-control" name="material_type_id" placeholder="Select Material Type...">
									<option value="0" {{ $subcategory->material_type_id == '' ? 'selected' : '' }}>Non Specific</option>
									@foreach (getModulePreconfig('Material Type', 'Inventory-Management') as $material_type)
										<option value="{{ $material_type['id'] }}" {{ $subcategory->material_type_id == $material_type['id'] ? 'selected' : '' }}>{{ $material_type['name'] }}</option>
									@endforeach
								</select>
							</div>
							<div class="p-0">
								<button type="submit" class="btn btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-sm-8 p-2">
				<div class="card mb-2" id="item-status-bar">
					<div class="card-body">
						<div class="row">
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-package-variant-closed text-success fa-2x"></i></div>
								<div class="value">{{ number_format($subcategory->available_stock, 2) }}{{ $subcategory->unit_type }}</div>
								<div class="text p-1">Available</div>
							</div>
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-package-variant text-info fa-2x"></i></div>
								<div class="value">~{{ number_format($subcategory->daily_demand(), 2) }}{{ $subcategory->unit_type }}</div>
								<div class="text p-1">Daily Demand</div>
							</div>
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-calendar-clock text-default fa-2x"></i></div>
								<div class="value">{{ number_format($subcategory->total_lead_time()) }} <small>Days</small></div>
								<div class="text p-1">Total Lead Time</div>
							</div>
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-file-clock text-warning fa-2x"></i></div>
								<div class="value">{{ number_format($subcategory->lead_time_consumption(),2) }}{{ $subcategory->unit_type }}</div>
								<div class="text p-1" title="Lead Time Consumption" data-toggle="tooltip" style="">Lead Time Consumption</div>
							</div>
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-package-variant-closed text-danger fa-2x"></i></div>
								<div class="value">{{ number_format($subcategory->safety_stock(),2) }}{{ $subcategory->unit_type }}</div>
								<div class="text p-1">Safety Stock</div>
							</div>
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-cart-arrow-down text-info fa-2x"></i></div>
								<div class="value">{{ number_format($subcategory->reaorder_level,2) }}{{ $subcategory->unit_type }}</div>
								<div class="text p-1">Reorder Level</div>
							</div>
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-package-variant-closed text-default fa-2x"></i></div>
								<div class="value">{{ number_format($subcategory->standard_order_quantity(),2) }}{{ $subcategory->unit_type }}</div>
								<div class="text p-1" title="Standard Order Quantity" data-toggle="tooltip">Standard Order Quantity</div>
							</div>
							<div class="col-sm-3 text-center">
								<div class="icon"><i class="mdi mdi-package-variant-closed text-success fa-2x"></i></div>
								<div class="value">{{ number_format($subcategory->max_standard_inventory(),2) }}{{ $subcategory->unit_type }}</div>
								<div class="text p-1" title="Max Standard Inventory" data-toggle="tooltip">Max Standard Inventory</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card tab-card">
					<div class="card-header tab-card-header">
						<ul class="nav nav-tabs card-header-tabs" id="Elements-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="Overview-tab" data-toggle="tab" href="#Overview" role="tab" aria-controls="Elements" aria-selected="true">Overview</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="item-brands-tab" data-toggle="tab" href="#item-brands" role="tab" aria-controls="Elements" aria-selected="true">Brands</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="item-suppliers-tab" data-toggle="tab" href="#item-suppliers" role="tab" aria-controls="Elements" aria-selected="true">Suppliers</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Elements-tab" data-toggle="tab" href="#Elements" role="tab" aria-controls="Elements" aria-selected="true">Received</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="transfer-tab" data-toggle="tab" href="#Transfer" role="tab" aria-controls="Transfer" aria-selected="false">Transfer</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="return-tab" data-toggle="tab" href="#Return" role="tab" aria-controls="Return" aria-selected="false">Return</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="stock-keeping-tab" data-toggle="tab" href="#Stock-Keeping" role="tab" aria-controls="Return" aria-selected="false">Stock Taking</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="disposal-tab" data-toggle="tab" href="#Disposal" role="tab" aria-controls="Disposal" aria-selected="false">Disposal</a>
							</li>
						</ul>
					</div>
					<div class="tab-content" id="Elements-tabs-content">
						<div class="tab-pane fade p-3" id="Elements" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Received Items</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Batch Code</th>
											<th nowrap>Barcode</th>
											<th nowrap>P.O. Number</th>
											<th nowrap>Quantity</th>
											<th nowrap>Est Price</th>
											<th nowrap>Actual Price</th>
											<th nowrap>Supplier</th>
											<th nowrap>Date</th>
											<th nowrap>Received_by</th>
											<th nowrap>Expiry</th>
											<th nowrap>Status</th>
											<th nowrap>Slot</th>
											<th nowrap>Rating</th>
											<th nowrap>Documentation</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@foreach ($sorteddata['items'] as $item)
											<tr>
												<td nowrap>{{ $loop->iteration }}</td>
												<td nowrap>{{ $item->batch_code }}</td>
												<td nowrap>{{ $item->barcode ?? 'n/a' }}</td>
												<td nowrap>{{ $item->po_number ?? 'n/a' }}</td>
												<td nowrap>{{ number_format($item->stock_in, $item->sub_category->reporting_decimal_places) }} {{ $item->sub_category->unit_type }}</td>
												<td nowrap>Ksh. {{ number_format($item->stock_in * $item->sub_category->unit_price, 2) }}</td>
												<td nowrap>Ksh. {{ number_format($item->price,2) }}</td>
												<td nowrap>{{ $item->supplier->name }}</td>
												<td nowrap>{{ $item->created_at }}</td>
												<td nowrap>{{ $item->creator->name }}</td>
												<td nowrap>{{ $item->expiry == '2099-12-31' ? '-' : $item->expiry }}</td>
												<td>{{ $item->status }}</td>
												<td>{{ $item->slot->slot->name ?? 'n/a' }}</td>
												<td nowrap>
													@if (!isset($item->rating))
														<span class="btn btn-transparent btn-sm text-primary"
															data-data='{{ json_encode(array("supplier"=>$item->supplier->id, "item"=>$item->id)) }}'
															data-target="#supplier-rating-modal" data-toggle="modal">
															<i class="mdi mdi-star"></i> Rate
														</span>
													@else
														<small title="{{ $item->rating->comments }}">{{ $item->rating->rating }}<i class="mdi mdi-star text-success"></i></small>
													@endif
												</td>
												<td>
													@if (isset($item->note))
														<span class="btn btn-transparent btn-sm text-info" data-toggle="modal" data-action="Item Reception"
															data-target='#inventory-notes-comments' data-note='{{ json_encode($item->note) }}'>
															<i class="mdi mdi-note-text-outline"></i> View
														</span>
													@else
														None
													@endif
												</td>
												<td></td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="item-brands" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">
								Brands
								<span class="btn btn-transparent text-primary btn-sm float-right" data-target="#add-item-brand-modal" data-toggle="modal">
									<i class="mdi mdi-plus"></i> Add
								</span>
							</h5>
							<div class="row no-gutters">
								@if ($subcategory->getBrands()->count() == 0)
									<div class="alert alert-info align-center text-center" style="width: 100%">
										<i class="fas fa-info-circle"></i> No brands configured for this item.
									</div>
								@endif
								@foreach ($subcategory->getBrands() as $brand)
									<div class="float-left m-1" style="border: 1px solid #ccc; border-top-right-radius: 9px; border-top-left-radius: 9px">
										<img src="{{ url($brand->image) }}" style="max-width: 200px; margin: 15px" />
										<hr>
										<span class="pl-3">{{ $brand->name }}</span>
										<hr>
										<div class="border-top border-light">
											<div class="btn-sm btn-block">
												<button class="btn btn-default text-info btn-sm" data-toggle="modal" data-target="#change-brand-image-{{ $loop->iteration }}"><i class="mdi mdi-image"></i></button>
												<button class="btn btn-default text-danger btn-sm" data-toggle="modal" data-target="#delete-brand-image-{{ $loop->iteration }}"><i class="mdi mdi-delete"></i></button>
											</div>
										</div>
									</div>
									<div id="change-brand-image-{{ $loop->iteration }}" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<!-- Modal content-->
											<form class="modal-content" method="POST" action="{{ route('change-brand-image', ['id'=>$brand->id]) }}" enctype="multipart/form-data">
												@csrf
												<div class="modal-header">
													<h4 class="modal-title"><i class="mdi mdi-plus"></i> Update Brand Details</h4>
												</div>
												<div class="modal-body">
													<div class="form-group">
														<label class="control-label">Name</label>
														<input type="text" class="form-control" name="name" value="{{ $brand->name }}" placeholder="Brand Name..." required />
													</div>
													<div class="form-group">
														<label class="control-label">Item Image <small class="text-muted">*Optional. Leave blank to maintain current image</small></label>
														<input type="file" name="image" class="form-control" />
													</div>
												</div>
												<div class="modal-footer">
													<button type="submit" class="btn btn-danger"><i class="mdi mdi-update"></i> Update</button>
													<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
												</div>
											</form>
										</div>
									</div>
									<div id="delete-brand-image-{{ $loop->iteration }}" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<!-- Modal content-->
											<form class="modal-content" method="POST" action="{{ route('delete-item-brand', ['id'=>$brand->id]) }}" enctype="multipart/form-data">
												@csrf
												<div class="modal-header">
													<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Item Brand</h4>
												</div>
												<div class="modal-body">
													<div class="form-group">
														<div class="alert alert-callout alert-danger">
															<i class="fas fa-exclamation-triangle"></i> Are you sure that you want to remove this brand?
														</div>
													</div>
												</div>
												<div class="modal-footer">
													<button type="submit" class="btn btn-danger"><i class="mdi mdi-trash"></i> Remove</button>
													<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
												</div>
											</form>
										</div>
									</div>
								@endforeach
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Transfer" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Items Issued Out</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Quantity</th>
											<th nowrap>Department</th>
											<th nowrap>Issued By</th>
											<th nowrap>Received By</th>
											<th nowrap>Date</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $looper = 0; ?>
										@foreach ($sorteddata as $key=>$items)
											@if(!in_array($key, $excludedKeys))
												@foreach ($items as $item)
													<?php $looper++; ?>
													<tr>
														<td>{{ $looper }}</td>
														<td>{{ number_format($item->stock_out, $item->sub_category->reporting_decimal_places) }} {{ $item->sub_category->unit_type }}</td>
														<td>{{ $item->department->name ?? '' }}</td>
														<td>{{ $item->creator->name }}</td>
														<td>{{ $item->receiver->name ?? 'n/a' }}</td>
														<td>{{ $item->created_at }}</td>
														<td>
															<span class="btn btn-transparent btn-sm text-primary" data-item='{{ json_encode($item) }}'
																data-target="#item-return-modal" data-toggle="modal">
																<i class="mdi mdi-keyboard-return"></i> Return
															</span>
														</td>
													</tr>
												@endforeach
											@endif
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="item-suppliers" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">
								Suppliers
								<span class="btn btn-primary btn-sm float-right pull-right" data-target="#add-this-supplier" data-toggle="modal">
									<i class="mdi mdi-plus"></i> Add Supplier
								</span>
							</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Name</th>
											<th nowrap>Phone</th>
											<th nowrap>Email</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@foreach ($subcategory->suppliers() as $sup)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td>{{ $sup->name }}</td>
											<td>{{ $sup->phone }}</td>
											<td>{{ $sup->email }}</td>
											<td>
												<span class="btn btn-sm btn-transparent text-danger" data-toggle="modal"
													data-target="#remove-this-supplier" data-supplier="{{ $sup->id }}">
													<i class="mdi mdi-delete"></i>
												</span>
											</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Return" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Items Returned</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Code</th>
											<th nowrap>Quantity</th>
											<th nowrap>Returned By</th>
											<th nowrap>Received By</th>
											<th nowrap>Date</th>
											<th nowrap>Expiry</th>
											<th nowrap>Slot</th>
											<th nowrap>Notes</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $looper = 0; ?>
										@foreach ($sorteddata['Returned 2 Store'] ?? array() as $item)
											<?php $looper++; ?>
											<tr>
												<td>{{ $looper }}</td>
												<td>{{ $item->batch_code }}</td>
												<td>{{ number_format($item->stock_in, $item->sub_category->reporting_decimal_places) }} {{ $item->sub_category->unit_type }}</td>
												<td>{{ $item->receiver->name ?? 'n/a' }}</td>
												<td>{{ $item->creator->name }}</td>
												<td>{{ $item->created_at }}</td>
												<td>{{ $item->expiry == '2099-12-31' ? '-' : $item->expiry }}</td>
												<td>{{ $item->slot->slot->name ?? 'n/a' }}</td>
												<td>
													@if (isset($item->note))
														<span class="btn btn-transparent btn-sm text-info" data-toggle="modal" data-action="Return"
															data-target='#inventory-notes-comments' data-note='{{ json_encode($item->note) }}'>
															<i class="mdi mdi-note-text-outline"></i> View
														</span>
													@else
														No Notes
													@endif
												</td>
												<td></td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Stock-Keeping" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Stock Taking</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Batch Code</th>
											<th nowrap>Adjustment</th>
											<th nowrap>Stock Taker</th>
											<th nowrap>Date</th>
											<th nowrap>Slot</th>
											<th nowrap>Notes</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $looper = 0; ?>
										@foreach ($sorteddata['Stock Taking'] ?? array() as $item)
											<?php $looper++; ?>
											<tr>
												<td>{{ $looper }}</td>
												<td>{{ $item->batch_code }}</td>
												<td>
													@if (intval($item->stock_out) > 0)
														<span class="text-danger"><i class="mdi mdi-menu-down"></i>{{ number_format(intval($item->stock_out)) }} {{ $item->sub_category->unit_type }}</span>
													@endif
													@if (intval($item->stock_in) > 0)
														<span class="text-success"><i class="mdi mdi-menu-up"></i>{{ number_format(intval($item->stock_in)) }} {{ $item->sub_category->unit_type }}</span>
													@endif
													@if (intval($item->stock_out) == 0 && intval($item->stock_in) == 0)
														<span class="text-default">{{ number_format(intval($item->stock_in)) }} {{ $item->sub_category->unit_type }}</span>
													@endif
												</td>
												<td>{{ $item->creator->name }}</td>
												<td>{{ $item->created_at }}</td>
												<td>{{ $item->slot->name ?? 'n/a' }}</td>
												<td>
													@if (isset($item->note))
														<span class="btn btn-transparent btn-sm text-info" data-toggle="modal" data-action="Stock Taking"
														data-target='#inventory-notes-comments' data-note='{{ json_encode($item->note) }}'>
															<i class="mdi mdi-note-text-outline"></i> View
														</span>
													@else
														No Notes
													@endif
												</td>
												<td></td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Disposal" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Item Disposal</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Batch Code</th>
											<th nowrap>Quantity</th>
											<th nowrap>Overseer</th>
											<th nowrap>Date</th>
											<th nowrap>Notes</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $looper = 0; ?>
										@foreach ($sorteddata['Item Disposal'] ?? array() as $item)
											<?php $looper++; ?>
											<tr>
												<td>{{ $looper }}</td>
												<td>{{ $item->batch_code }}</td>
												<td>{{ number_format(intval($item->stock_out)) }} {{ $item->sub_category->unit_type }}</td>
												<td>{{ $item->creator->name ?? 'n/a' }}</td>
												<td>{{ $item->created_at }}</td>
												<td>
													@if (isset($item->note))
														<span class="btn btn-transparent btn-sm text-info" data-toggle="modal" data-action="Item Disposal"
														data-target='#inventory-notes-comments' data-note='{{ json_encode($item->note) }}'>
															<i class="mdi mdi-note-text-outline"></i> View
														</span>
													@else
														No Notes
													@endif
												</td>
												<td></td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane show active fade p-3" id="Overview" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title ml-2 mr-2 mt-4">
								<span class="">Inventory Items</span>
							</h5>
							<hr>
							<div class="table-responsive mt-2">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm"
									data-url="">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Name</th>
											<th nowrap>Stock In</th>
											<th nowrap>Stock Out</th>
											<th nowrap>Department</th>
											<th nowrap>Created By</th>
											<th nowrap>Date</th>
											<th nowrap>Status</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($subcategory->inventory_items as $item)
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td nowrap><img src="{{ $item->sub_category->image }}" style="width: 30px; margin-right: 4px"/> {{ $item->sub_category->name }}</td>
												<td>{{ number_format($item->stock_in, 2) }}</td>
												<td>{{ number_format($item->stock_out, 2) }}</td>
												<td>{{ $item->department->name ?? '-' }}</td>
												<td nowrap>{{ $item->creator->name }} <small class="text-default-light"><i class="mdi mdi-email-variant"></i> {{ $item->creator->email }}</small></td>
												<td nowrap>{{ $item->created_at }}</td>
												<td>{{ $item->status }}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
@endsection
@section('script2')
<div id="add-sub-category" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-inventory-sub-category') }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $category->id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Item</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Image</label>
					<input type="file" class="form-control" name="image"  />
				</div>
				<div class="form-group">
					<label class="control-label">Manufacturer</label>
					<input type="text" class="form-control" name="manufacturer" value="" placeholder="Manufacturer..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Minimum Level</label>
					<input type="number" min="0" class="form-control" name="minimum_level" value="" placeholder="Minimum Level..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Unit of Measure</label>
					<select class="form-control" name="unit_type">
						<option value="">Select Unit of Measure...</option>
						@foreach ($systemUnitsofMeasure as $g)
							<option value="{{ $g['name'] }}">{{ $g['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Unit Price</label>
					<input type="number" min="0" class="form-control" name="unit_price" value="" placeholder="Unit Price..." required />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="add-inventory-items" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-inventory-items') }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $category->id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Item Reception</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="sub_category_id" value="{{ $subcategory->id }}">
				<div class="form-group">
					<label class="control-label">Supplier</label>
					<select class="form-control" name="supplier_id" required>
						<option value="Select Sub Category...">Select Supplier...</option>
						@foreach ($subcategory->suppliers() as $sup)
							<option value="{{ $sup->id }}">{{ $sup->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Quantity</label>
					<input type="text" class="form-control" name="quantity" value="" placeholder="Quantity..." required />
				</div>
				<div class="form-group">
					<label class="control-label">P.O. Number</label>
					<input type="text" class="form-control" name="po_number" value="" placeholder="Purchase Order Number..." required>
				</div>
				<div class="form-group">
					<label class="control-label">Price</label>
					<input type="number" class="form-control" name="price" value="" placeholder="Price...">
				</div>
				<div class="form-group">
					<label class="control-label">Expiry</label>
					<input type="date" class="form-control" name="expiry" value="" placeholder="Expiry...">
				</div>
				<div class="form-group">
					<label class="control-label">Store</label>
					<select class="form-control store-selector" name="store" required placeholder="Select Store">
						<option></option>
						@foreach ($stores as $item)
							<option value="{{ $item->id }}" data-slots='{{ json_encode($item->slots) }}'>{{ $item->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Slot</label>
					<select class="form-control slot-selector" name="slot" required placeholder="Select Slot...">
						<option></option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Comments</label>
					<textarea class="form-control" name="comments" value="" placeholder="Comments..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Documentation <small class="text-muted">Image or document file</small></label>
					<input type="file" class="form-control" name="file" value="" />
				</div>
				<div class="form-group">
					<label class="control-label">
						<input type="checkbox" name="requires_qc" /> &nbsp; Requires Quality Control
					</label>
				</div>
				<div class="form-group">
					<label class="control-label">Barcode</label>
					<input type="text" class="form-control" name="barcode" value="" placeholder="Scan Barcode...">
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="stock-keeping-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('stock-keeping') }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $category->id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-left"></i> Stock Taking</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="sub_category_id" value="{{ $subcategory->id }}">
				<input type="hidden" value="4" name="transfer_to" required>
				<div class="form-group">
					<label class="control-label">Quantity</label>
					<input type="text" class="form-control" name="quantity" value="" placeholder="Quantity..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Comments</label>
					<textarea class="form-control" name="comments" value="" placeholder="Comments..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">File <small class="text-muted">Image or document file</small></label>
					<input type="file" class="form-control" name="file" value="" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="supplier-rating-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('supplier-rating') }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="supplier_id" />
				<input type="hidden" name="inventory_item_id" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-left"></i> Supplier Rating</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Rating</label>
					<div class="rating-holder">
						<?php $d = 1; ?>
						@while ($d <= 10)
							<span class="rating-star" data-value="{{ $d }}"><i class="mdi mdi-star"></i></span>
							<?php $d++; ?>
						@endwhile
						<input type="hidden" name="rating" value="0">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Title</label>
					<input type="text" class="form-control" name="title" value="" placeholder="Title..." />
				</div>
				<div class="form-group">
					<label class="control-label">Comments</label>
					<textarea class="form-control" name="comments" value="" placeholder="Comments..." required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="item-return-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('return-item-to-store') }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $category->id }}" />
			<input type="hidden" name="sub_category_id" value="{{ $subcategory->id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-keyboard-return"></i> Return 2 Store</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="item_to_return" value="">
				<input type="hidden" value="6" name="transfer_to" required>
				<div class="form-group">
					<label class="control-label">Quantity</label>
					<input type="text" class="form-control" name="quantity" value="" placeholder="Quantity..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Expiry</label>
					<input type="date" class="form-control" name="expiry" value="" placeholder="Expiry..." />
				</div>
				<div class="form-group">
					<label class="control-label">Store</label>
					<select class="form-control store-selector" name="store" required placeholder="Select Store">
						<option></option>
						@foreach ($stores as $item)
							<option value="{{ $item->id }}" data-slots='{{ json_encode($item->slots) }}'>{{ $item->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Slot</label>
					<select class="form-control slot-selector" name="slot" required placeholder="Select Slot...">
						<option></option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Return Comments</label>
					<textarea class="form-control" name="comments" value="" placeholder="Return Comments..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Return File <small class="text-muted">Image or document file</small></label>
					<input type="file" class="form-control" name="file" value="" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

{{-- <div id="item-disposal-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('item-disposal') }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $category->id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-trash-can"></i> Item Disposal</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="sub_category_id" value="{{ $subcategory->id }}">
				<input type="hidden" value="5" name="transfer_to" required>
				<div class="form-group">
					<label class="control-label">Store</label>
					<select style="min-width: 150px" name="store_id" class="form-control selected-store" placeholder="Select Store..." required>
						<option selected></option>
						@foreach (getUserStores() as $store)
							<option value="{{ $store->id }}" data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Slot</label>
					<select style="min-width: 150px" name="store_slot_id" class="form-control store-slots" placeholder="Select Store First..." required><option selected></option></select>
				</div>
				<div class="form-group">
					<label class="control-label">Quantity</label>
					<input type="number" step="any" class="form-control" name="quantity" value="" placeholder="Quantity..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Reason</label>
					<select class="form-control" name="reason" id="disposal-reason-field" placeholder="Reason for disposal..." required>
						<option></option>
						@foreach (getDisposalReason() as $item)
							<option value="{{ $item->description }}">{{ $item->description }}</option>
						@endforeach
						<option value="Other">Other</option>
					</select>
				</div>
				<div class="form-group hidden" id="other-disposal-reason">
					<label class="control-label">Other Reason</label>
					<textarea class="form-control" name="other_reason" placeholder="Other Reason..."></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Disposal Comments</label>
					<textarea class="form-control" name="comments" value="" placeholder="Return Comments..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Disposal File <small class="text-muted">Image or document file</small></label>
					<input type="file" class="form-control" name="file" value="" />
				</div>
				<div class="form-group hidden" id="disposal-confirmation-message">
					<div class="alert alert-danger">
						<i class="mdi mdi-alert fa-2x pull-left"></i> Are you sure you want to proceed with item disposal?
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<span class="btn btn-danger" id="item-disposal-button"><i class="mdi mdi-delete"></i> Dispose</span>
				<button class="btn btn-danger hidden" id="item-disposal-submit-button"><i class="mdi mdi-check-bold"></i> Yes, Proceed</button>
				<button type="button" class="btn btn-default" id="item-disposal-cancel-button" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div> --}}

<div id="inventory-notes-comments" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content" method="POST" action="{{ route('stock-keeping') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-information"></i> <span class="inventory-action"></span> Notes</h4>
			</div>
			<div class="modal-body">
				<h6><span class="inventory-action-title"></span></h6>
				<div class="inventory-action-comments p-1"></div>
				<hr>
				<h6><i class="mdi mdi-file-document"></i> Accompanying Document</h6>
				<div class="inventory-action-document"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div id="transfer-inventory-items" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('transfer-inventory-items') }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $category->id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-share"></i> Item Issuing</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="sub_category_id" value="{{ $subcategory->id }}">
				<div class="form-group">
					<label class="control-label">Transfer To</label>
					<select class="form-control" name="transfer_to" required>
						<option value="">Select Transfer To...</option>
						@foreach ($departments as $dep)
							<option value="{{ $dep->id }}">{{ $dep->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Quantity</label>
					<input type="text" class="form-control" name="quantity" value="" placeholder="Quantity..." required />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="create-an-order" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('create-order') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Create an Order</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Supplier</label>
					<select name="supplier_id" class="form-control select2" placeholder="Select Supplier" required>
						<option value="">Select Supplier...</option>
						@foreach ($subcategory->suppliers() as $sup)
							<option value="{{ $sup->id }}">{{ $sup->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Order Comments</label>
					<textarea class="form-control" name="order_comments" placeholder="Info for the supplier..."></textarea>
				</div>
				<fieldset class="form-group">
					<legend>Order Items</legend>
					<div class="row">
						<div class="col-sm-12">
							<label class="control-label">Quantity</label>
							<input type="hidden" name="items[sub_category_id][]" />
							<input type="number" min="0" class="form-control" name="items[quantity][]" placeholder="Enter Quantity" required />
						</div>
					</div>
				</fieldset>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-item-brand-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-item-brand', ['subcategory'=>$subcategory->id]) }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Item Brand</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name*</label>
					<input type="text" class="form-control" name="name" placeholder="Brand Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Image*</label>
					<input type="file" class="form-control" name="image" placeholder="Brand Image..." required />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="remove-this-supplier" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Supplier</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-danger">
						<i class="fas fa-exclamation-triangle fa-2x"></i> Are you sure that you want to remove this supplier?
					</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Remove</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-this-supplier" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-supplier-to-inventory', ['itemID'=> $subcategory->id]) }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Supplier</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Select Supplier</label>
					<select class="form-control" id="select-supplier" name="supplier" placeholder="Select Supplier..."></select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-plus"></i> Add Supplier</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('.store-selector').on('change', function(){
			var slots = $(this).children('option:selected').data('slots');

			var slotSelect = $(this).parents('form').find('select.slot-selector');
			slotSelect.html(`<option></option>`);

			$.each(slots, function(s, slot){
				slotSelect.append(`<option value="${slot.id}">${slot.name}</option>`);
			})

		});

		$('#select-supplier').select2({
			ajax: {
				url: '{{ route("get_suppliers_via_ajax") }}',
				data: function (params) {
					var query = {
						search: params.term,
						page: params.page || 1
					}
					return query;
				}
			},
			placeholder: 'Please Select Supplier...'
		});

		$('.print-barcode').on('click', function(){
			var restorepage = $('body').html();
			var printcontent = $(this).parent().find('.barcode').clone();

			$('body').empty().html(printcontent);

			window.print();

			location.reload();
		});

		$('.rating-star').on('click', function(){
			$('.rating-star').removeClass('selected');
			var i = $(this).data('value');
			$('.rating-star').slice(0, i).addClass('selected');

			$('#supplier-rating-modal').find('[name="rating"]').val(i);
		});

		$('#remove-this-supplier').on('show.bs.modal', function(e) {
			var supplier = $(e.relatedTarget).data('supplier');
			var form = $(this).find('form');

			form.attr('action', '/remove-this-supplier/'+supplier+'/{{ $subcategory->id }}');
			form.prop('action', '/remove-this-supplier/'+supplier+'/{{ $subcategory->id }}');
		});

		$('#create-an-order').on('show.bs.modal', function(e) {
			var subCat = {{ $category->id }}+" "+$(e.relatedTarget).data('subid');

			$('#create-an-order').find('[name="items[sub_category_id][]"]').val(subCat);
		});

		$('#supplier-rating-modal').on('show.bs.modal', function(e) {
			var data = $(e.relatedTarget).data('data');

			$('#supplier-rating-modal').find('[name="supplier_id"]').val(data.supplier);
			$('#supplier-rating-modal').find('[name="inventory_item_id"]').val(data.item);
		});

		$('#inventory-notes-comments').on('show.bs.modal', function(e) {
			var note = $(e.relatedTarget).data('note');
			var action = $(e.relatedTarget).data('action');

			$('#inventory-notes-comments').find('.inventory-action').html(action);
			$('#inventory-notes-comments').find('.inventory-action').html(action);
			$('#inventory-notes-comments').find('.inventory-action-title').html(note.title && note.title !="" ? `<i class="mdi mdi-information"></i> ${note.title}` : `<i class="mdi mdi-comment-text"></i> Comments`);
			$('#inventory-notes-comments').find('.inventory-action-comments').html(note.comments);
			if($.trim(note.document) !== ""){
				$('#inventory-notes-comments').find('.inventory-action-document').html(`
					<a href="${note.document}" class="btn btn-transparent text-primary" target="_blank">
						<i class="mdi mdi-download"></i> View Document
					</a>
				`);
			}
			else{
				$('#inventory-notes-comments').find('.inventory-action-document').html(`
					<span href="${note.document}" class="btn btn-transparent text-info" target="_blank">
						<i class="fas fa-exclamation-triangle"></i> No Document Provided
					</span>
				`);
			}

		});

		$('#item-disposal-button').on('click', function(e){
			$(this).addClass('hidden');
			$("#item-disposal-submit-button").removeClass("hidden");
			$('#disposal-confirmation-message').removeClass("hidden");
			$(this).attr('type', 'submit');
			$('#item-disposal-cancel-button').html('<i class="mdi mdi-ban"></i> No, Cancel')
		});

		$('#item-disposal-cancel-button').on('click', function(){
			$(this).parents('form').trigger('reset');
			$(this).parents('form').find('.form-control').trigger('change');
			$(this).text('Close');
			$('#disposal-confirmation-message').addClass("hidden");
			$('#item-disposal-button').attr('type', 'button');

			$("#item-disposal-submit-button").addClass('hidden');
			$('#item-disposal-button').removeClass("hidden");
		});

		$('#item-return-modal').on('show.bs.modal', function(e) {
			var item = $(e.relatedTarget).data('item');

			$('#item-return-modal').find('[name="item_to_return"]').val(JSON.stringify(item));
		});

		$('#disposal-reason-field').on('change', function(){
			var text = $(this).children('option:selected').text();
			if(text == "Other"){
				$('#other-disposal-reason').val('');
				$('#other-disposal-reason').removeClass('hidden');
			}
			else{
				$('#other-disposal-reason').val('');
				$('#other-disposal-reason').addClass('hidden');
			}
		});

		$('select.selected-store').on('change', function(){
			var selected = $(this).children('option:selected');
			var slots = selected.data('slots');
			var location = $(this).data('location');

			var slotDiv = $(this).parents('.modal-body').find('select.store-slots');
			slotDiv.attr('placeholder', 'Select Slot...')
			slotDiv.html(`<option></option>`);
			var selectedSlot = slotDiv.data('selected')
			$.each(slots, function(i, s){
				var newOption = new Option(s.name, s.id, false, false);
				slotDiv.append(newOption).trigger('change');
			});
			slotDiv.val(selectedSlot).trigger('change');
		});
	});
</script>
@endsection
