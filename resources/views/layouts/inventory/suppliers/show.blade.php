@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> {{ $supplier->name }} | Supplier </title>
	<style type="text/css">
		.tab-card {
			border:1px solid #eee;
		}

		.tab-card-header {
			background:none;
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
			font-size: 13px !important;
		}

		.removeThis {
			z-index: 12;
			position: absolute;
			cursor: pointer;
			top: 0px;
			right: 2px;
			padding: 1px 4px;
			font-size: 12px;
			background-color: red;
			border-radius: 50%;
			color: #fff;
			box-shadow: 0px 0px 5px rgba(0,0,0,0.08);
		}

		input[type="range"] {
			-webkit-appearance: none;
			-moz-appearance: none;
			width: 300px;
			height: 5px;
			padding: 0;
			border-radius: 2px;
			outline: none;
			cursor: pointer;
		}


		/*Chrome thumb*/

		input[type="range"]::-webkit-slider-thumb {
			-webkit-appearance: none;
			-moz-appearance: none;
			-webkit-border-radius: 5px;
			/*16x16px adjusted to be same as 14x14px on moz*/
			height: 16px;
			width: 16px;
			border-radius: 5px;
			background: #e7e7e7;
			border: 1px solid #c5c5c5;
		}


		/*Mozilla thumb*/

		input[type="range"]::-moz-range-thumb {
			-webkit-appearance: none;
			-moz-appearance: none;
			-moz-border-radius: 5px;
			height: 14px;
			width: 14px;
			border-radius: 5px;
			background: #e7e7e7;
			border: 1px solid #c5c5c5;
		}


		/*IE & Edge input*/

		input[type=range]::-ms-track {
			width: 300px;
			height: 6px;
			/*remove bg colour from the track, we'll use ms-fill-lower and ms-fill-upper instead */
			background: transparent;
			/*leave room for the larger thumb to overflow with a transparent border */
			border-color: transparent;
			border-width: 2px 0;
			/*remove default tick marks*/
			color: transparent;
		}


		/*IE & Edge thumb*/

		input[type=range]::-ms-thumb {
			height: 14px;
			width: 14px;
			border-radius: 5px;
			background: #e7e7e7;
			border: 1px solid #c5c5c5;
		}


		/*IE & Edge left side*/

		input[type=range]::-ms-fill-lower {
			background: #919e4b;
			border-radius: 2px;
		}


		/*IE & Edge right side*/

		input[type=range]::-ms-fill-upper {
			background: #c5c5c5;
			border-radius: 2px;
		}


		/*IE disable tooltip*/

		input[type=range]::-ms-tooltip {
			display: none;
		}

	</style>
@endsection
@section('content2')
	<main>
		<?php
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('inventory-suppliers'),
          'name' => 'Suppliers',
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $supplier->name,
          'icon' => null
        )
      );


			$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
			$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<h2 class="p-4">
			<i class="mdi mdi-mdi-user"></i> {{ $supplier->name }} <small class="badge {{ $supplier->average_rating() < 60 ? 'badge-warning' : 'badge-success' }}">{{ $supplier->average_rating() }}%<i class="mdi mdi-star"></i> </small> <small class="text-muted"> | Suppliers</small>

		</h2>
		<div class="row no-gutters">
			<div class="col-md-4 p-2">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title"><i class="mdi mdi-pencil-outline"></i> Edit Supplier</h5>
						<form method="POST" action="{{ route('edit-inventory-supplier', ['id'=>$supplier->id]) }}" enctype="multipart/form-data">
							@csrf
							<div class="form-group">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="{{ $supplier->name }}" placeholder="Name..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Logo</label>
								<div class="p-4">
									<img src="{{ $supplier->logo }}" style="width: 100%" />
								</div>
								<input type="file" class="form-control" name="logo"  />
							</div>
							<div class="form-group">
								<label class="control-label">Email</label>
								<input type="email" class="form-control" name="email" value="{{ $supplier->email }}" placeholder="Email..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Phone</label>
								<input type="text" class="form-control" name="phone" value="{{ $supplier->phone }}" placeholder="Phone..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Building</label>
								<input type="text" class="form-control" name="building" value="{{ $supplier->building }}" placeholder="Building..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Street</label>
								<input type="text" class="form-control" name="street" value="{{ $supplier->street }}" placeholder="Street..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Town</label>
								<input type="text" class="form-control" name="town" value="{{ $supplier->town }}" placeholder="Town..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Address</label>
								<input type="text" class="form-control" name="address" value="{{ $supplier->address }}" placeholder="Address..." required />
							</div>
							<div class="form-group">
								<label class="control-label">PIN Number</label>
								<input type="text" class="form-control" name="pin_number" value="{{ $supplier->pin_number }}" placeholder="PIN Number..." required />
							</div>
							<div class="form-group">
								<label class="control-label">VAT Number</label>
								<input type="text" class="form-control" name="vat_number" value="{{ $supplier->vat_number }}" placeholder="VAT Number..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Payment Terms</label>
								<select type="text" class="form-control" name="payment_terms"  placeholder="Payment Terms..." required>
								@foreach ($paymentTerms as $term)
									<option value="{{ $term->name }}" {{ $supplier->payment_terms == $term->name ? "selected" : "" }}>{{ $term->name }}</option>
								@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Payment Methods</label>
								<input type="text" class="form-control" name="payment_method" value="{{ $supplier->payment_method }}" placeholder="Payment Methods..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Supplier Currency</label>
								<select name="default_currency" class='form-control trigger-save' data-placeholder="Select Currency...">
									<option></option>
									@foreach (getCurrencies() as $p)
										<option value="{{ $p->id }}" {{ $p->id == ($supplier->default_currency ?? '') ? 'selected' : '' }}>{{ $p->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label"><input type="checkbox" name="active" value="1" {{ $supplier->active == 1 ? 'checked' : '' }} /> Is Active?</label>
							</div>
							<div class="p-0">
								<button type="submit" class="btn btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-md-8 p-2">
				<div class="card tab-card">
					<div class="card-header tab-card-header">
						<ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
							{{-- <li class="nav-item">
								<a class="nav-link active" id="Activity-tab" data-toggle="tab" href="#Activity" role="tab" aria-controls="Activity" aria-selected="true">Activity</a>
							</li> --}}
							<li class="nav-item">
								<a class="nav-link active" id="Orders-tab" data-toggle="tab" href="#Orders" role="tab" aria-controls="Orders" aria-selected="true">Orders</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Goods-Receipt-tab" data-toggle="tab" href="#Goods-Receipt" role="tab" aria-controls="Orders" aria-selected="true">Goods Receipt</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Goods-Return-tab" data-toggle="tab" href="#Goods-Return" role="tab" aria-controls="Orders" aria-selected="true">Goods Return</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Main-Categories-tab" data-toggle="tab" href="#Main-Categories" role="tab" aria-controls="Supplier Categories" aria-selected="true">Supplier Categories</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Categories-tab" data-toggle="tab" href="#Categories" role="tab" aria-controls="Supplier Items" aria-selected="true">Supplier Items</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Ratings-tab" data-toggle="tab" href="#Ratings" role="tab" aria-controls="Ratings" aria-selected="true">Ratings</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Contacts-tab" data-toggle="tab" href="#Contacts" role="tab" aria-controls="Contacts" aria-selected="true">Contacts</a>
							</li>
							{{-- <li class="nav-item">
								<a class="nav-link" id="Contracts-tab" data-toggle="tab" href="#Contracts" role="tab" aria-controls="Contracts" aria-selected="true">Contracts</a>
							</li> --}}
						</ul>
					</div>
					<div class="tab-content" id="Orders-tabs-content">
						{{-- <div class="tab-pane fade p-3" id="Contracts" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">
								Contracts
								<div class="btn btn-sm btn-info float-right" data-target="#add-a-contract" data-toggle="modal"><i class="mdi mdi-plus"></i> Contract</div>
							</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped server-side table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Description</th>
											<th>Item(s)</th>
											<th>Start Date</th>
											<th>End Date</th>
											<th>Status</th>
											<th>Download</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@foreach ($supplier->contracts() as $item)
											<?php
												// if(\Carbon\Carbon::parse($item['end']) < \Carbon\Carbon::today() && $item['status'] > 0){
												// 	updateContractStatus($item['id'], 0);
												// 	$item['status'] = 0;
												// }
											?>
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td nowrap>{{ $item['description'] }}</td>
												<td nowrap>{{ implode(",", array_values($item['items'] ))}}</td>
												<td>{{ $item['start'] }}</td>
												<td>{{ $item['end'] }}</td>
												<td>{!! $item['status'] == 0 ? '<i class="mdi mdi-check-circle text-muted"></i>' : '<i class="mdi mdi-check-circle text-success"></i>' !!}</td>
												<td nowrap><a class="btn btn-xs btn-transparent text-info btn-sm" download href="{{ $item['file'] }}"><i class="mdi mdi-download"></i></a></td>
												<td nowrap>
													<span class="btn btn-transparent btn text-primary btn-sm" data-toggle="modal" data-target="#edit-contract-modal" data-item='{{ json_encode($item) }}'>
														<i class="mdi mdi-pencil"></i>
													</span>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div> --}}
						<div class="tab-pane fade p-3" id="Ratings" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">
								Ratings
								@if(\Auth::user()->hasRole($procurement_officer_role_id, true))
									<div class="btn btn-sm btn-transparent text-info" data-target="#add-rating-criteria-modal" data-toggle="modal">
										<i class="mdi mdi-plus"></i> Criteria
									</div>
									<div class="btn btn-sm text-info text-danger float-right" data-target="#update-supplier-criteria-rating-modal" data-toggle="modal">
										<i class="mdi mdi-update"></i> Update Rating
									</div>
								@endif
							</h5>
							<div class="row">
								<div class="col-md-4 col-sm-5">
									<div class="p-3 text-center">
										<h1 style="font-size: 5.2em">{{ $supplier->average_rating() }}</h1>
										<div class="progress">
											<div class="progress-bar progress-bar-striped" role="progressbar" style="width: {{ $supplier->average_rating() }}%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
										<div class="pt-1 pb-1">
											<small>A score of {{ $supplier->average_rating() }} out of 100%</small>
										</div>
									</div>
								</div>
								<div class="col-md-8 col-sm-7">
									@foreach (getSupplierRatingCriteria() as $gSRC)
										<?php
											$score = $ratingScores[$gSRC->id] ?? 0;
											$scorePerc = $score/$gSRC->max_score*100;
											$mRatingColor = supplierRatingColorFromScore($scorePerc);
										?>
										<div class="mt-1 mb-1">
											<div class="pt-1 pb-1" style="clear: both">
												<h6>{{ $gSRC->title }}</h6>
												@if(\Auth::user()->hasRole($procurement_officer_role_id, true))
												<div class="mtools float-right pull-right">
													<div class="btn-group" role="group">
														<button type="button" class="btn btn-transparent text-info btn-sm"
															data-criteria="{{ json_encode($gSRC) }}" data-target="#edit-rating-criteria-modal" data-toggle="modal">
															<i class="mdi mdi-pencil"></i>
														</button>
													</div>
												</div>
												@endif
											</div>
											<div class="progress">
												<div class="progress-bar progress-bar-striped {{ $mRatingColor }}" role="progressbar" style="width: {{ $scorePerc }}%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
											<div class="pt-1 pb-1 text-muted">
												<small>{{ number_format($score,1) }} out of {{ number_format($gSRC->max_score,1) }}</small>
											</div>
										</div>
									@endforeach
								</div>
							</div>
						</div>
						<div class="tab-pane fade show active p-3" id="Orders" role="tabpanel" aria-labelledby="one-tab">
							{{-- <h5 class="card-title">Orders <div class="btn btn-sm btn-info float-right" data-target="#create-an-order" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Order</div></h5> --}}
							<div class="table-responsive">
								<table id="server-side-orders" data-parent="Request for Quotation" data-type="Purchase Orders"
									data-url="{{ route('server-side-purchase-orders', ['field'=>'supplier_id', 'fieldID'=>$supplier->id, 'type'=>'Purchase Orders']) }}"
									class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th></th>
											<th>GR Number </th>
											<th>Description</th>
											<th>Due Date</th>
											<th>Source</th>
											<th>Created by</th>
											<th>Date Created</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
								@if(count($supplier->purchase_orders) == 0)
									<div class="alert alert-info">
										<i class="mdi mdi-alert"></i> No Orders added yet.
									</div>
								@endif
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Goods-Receipt" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Goods Receipt</h5>
							<div class="table-responsive">
								<table id="server-side-goods-receipt" data-parent="Purchase Orders" data-type="Goods Receipt"
									data-url="{{ route('server-side-purchase-orders', ['field'=>'supplier_id', 'fieldID'=>$supplier->id, 'type'=>'Goods Receipt']) }}"
									class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>GRN Number </th>
											<th>Description</th>
											<th>Due Date</th>
											<th>Source</th>
											<th>Created by</th>
											<th>Date Created</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
								@if(count($supplier->purchase_orders) == 0)
									<div class="alert alert-info">
										<i class="mdi mdi-alert"></i> No Goods Receipt added yet.
									</div>
								@endif
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Goods-Return" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Goods Return</h5>
							<div class="table-responsive">
								<table id="server-side-goods-return" data-parent="Purchase Orders" data-type="Goods Return"
									data-url="{{ route('server-side-purchase-orders', ['field'=>'supplier_id', 'fieldID'=>$supplier->id, 'type'=>'Goods Return']) }}"
									class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Order No </th>
											<th>Description</th>
											<th>Due Date</th>
											<th>Source</th>
											<th>Created by</th>
											<th>Date Created</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
								@if(count($supplier->purchase_orders) == 0)
									<div class="alert alert-info">
										<i class="mdi mdi-alert"></i> No Goods Return added yet.
									</div>
								@endif
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Main-Categories" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Supplier Category <div class="btn btn-sm btn-info float-right" data-target="#add-supplier-category" data-toggle="modal"><i class="mdi mdi-plus"></i> Category</div></h5>
							<hr>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Category </th>
											<th nowrap>Items</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@foreach ($supplier_categories as $item)
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td>{{ $item->name }}</td>
												<td>{{ $item->items }}</td>
												<td>
													<div class="btn-sm btn-block">
														<button class="btn btn-default text-danger btn-sm" data-toggle="modal" data-target="#delete-supplier-main-category" data-item="{{ $item->row_id }}"><i class="mdi mdi-delete"></i></button>
													</div>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Categories" role="tabpanel" aria-labelledby="one-tab">
							{{-- <h5 class="card-title">Supplier Items <div class="btn btn-sm btn-info float-right" data-target="#add-supplier-items" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Item</div></h5> --}}
							<hr>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Image </th>
											<th nowrap>Item</th>
											<th nowrap>Code</th>
											<th nowrap>Brand</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@foreach ($supplier->categories() as $item)
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td data-toggle="modal" style="cursor: pointer;"
													data-target="#view-image-large" data-img="{{ $item->image }}">
													<img src="{{ url($item->image) }}" style="max-height: 50px" />
												</td>
												<td>{{ $item->item }}</td>
												<td>{{ $item->code }}</td>
												<td>{{ $item->brand }}</td>
												<td>
													<div class="btn-sm btn-block">
														<button class="btn btn-default text-danger btn-sm" data-toggle="modal" data-target="#delete-supplier-category" data-item="{{ $item->id }}"><i class="mdi mdi-delete"></i></button>
													</div>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Contacts" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Contacts <div class="btn btn-sm btn-transparent text-info float-right" data-target="#add-supplier-contact" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Contact</div></h5>
							<hr>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Name </th>
											<th nowrap>ID</th>
											<th nowrap>Phone</th>
											<th nowrap>Email</th>
											<th nowrap>PIN</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@foreach ($supplier->contacts as $contact)
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td>{{ $contact->name }}</td>
												<td>{{ $contact->id_number }}</td>
												<td>{{ $contact->phone }}</td>
												<td>{{ $contact->email }}</td>
												<td>{{ $contact->pin }}</td>
												<td nowrap>
													<div class="btn-sm btn-block">
														<button class="btn btn-transparent text-primary btn-sm" data-toggle="modal" data-target="#add-supplier-contact" data-action="Edit" data-contact="{{ $contact }}"><i class="mdi mdi-pencil"></i></button>
														<button class="btn btn-transparent text-danger btn-sm" data-toggle="modal" data-target="#delete-supplier-contact" data-contact="{{ $contact->id }}"><i class="mdi mdi-delete"></i></button>
													</div>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						{{-- <div class="tab-pane fade show active p-3" id="Activity" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Overview </h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Category</th>
											<th>Sub Category</th>
											<th>Manufacturer</th>
											<th>{{ __('Stock In') }}</th>
											<th>Status</th>
											<th>Overseen By</th>
											<th>Date</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($supplier->inventory_items as $item)
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td>{{ $item->category->name ?? '-' }}</td>
												<td>{{ $item->sub_category->name ?? '-' }}</td>
												<td>{{ $item->sub_category->manufacturer ?? '-' }}</td>
												<td>{{ number_format($item->stock_in ?? 0) }}{{ $item->category->unit_type ?? '-' }} </td>
												<td>{{ $item->status }}</td>
												<td>{{ $item->creator->name ?? '-' }}<small>< {{ $item->creator->email ?? '-' }} ></small></td>
												<td>{{ $item->created_at }}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div> --}}
					</div>
				</div>
			</div>
		</div>
	</main>
@endsection
@section('script2')
<div id="add-supplier-category" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-supplier-main-category', ['supplier_id'=>$supplier->id]) }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Supplier Category</h4>
			</div>
			<div class="modal-body">
				{{-- <pre> {{ json_encode(getSubCategoriesByBrand(0, true), JSON_PRETTY_PRINT) }}</pre>; --}}
				<div class="form-group">
					<label class="control-label">Category</label>
					<select class="form-control" name="category_id[]" required multiple placeholder="Select Category...">
						<option></option>
						@foreach ($all_categories as $ac)
							<option value="{{ $ac->id }}">{{ $ac->name }}</option>
						@endforeach
					</select>

				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-supplier-contact" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('update-supplier-contact', ['supplier_id'=>$supplier->id]) }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><span class="action"><i class="mdi mdi-plus"></i> Add </span>Supplier Contact</h4>
			</div>
			<div class="modal-body">
				{{-- <pre> {{ json_encode(getSubCategoriesByBrand(0, true), JSON_PRETTY_PRINT) }}</pre>; --}}
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" placeholder="Name..." />
				</div>
				<div class="form-group">
					<label class="control-label">Contact Type</label>
					<input type="text" class="form-control" name="type" placeholder="Contact Type e.g Director..." />
				</div>
				<div class="form-group">
					<label class="control-label">Phone</label>
					<input type="tel" class="form-control" name="phone" placeholder="Phone..." />
				</div>
				<div class="form-group">
					<label class="control-label">Email</label>
					<input type="email" class="form-control" name="email" placeholder="Email..." />
				</div>
				<div class="form-group">
					<label class="control-label">ID Number</label>
					<input type="text" class="form-control" name="id_number" placeholder="ID Number..." />
				</div>
				<div class="form-group">
					<label class="control-label">PIN Number</label>
					<input type="text" class="form-control" name="pin" placeholder="PIN Number..." />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
{{-- <div id="add-supplier-items" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-supplier-category', ['supplier'=>$supplier->id]) }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Supplier Item</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Item</label>
					<select class="form-control" name="brands[]" required multiple placeholder="Select Item...">
						<option></option>
						@foreach (getSubCategoriesByBrand(0, true) as $cat=>$items)
							<optgroup label="{{ $cat }}">
								@foreach ($items as $sub)
									<option value="{{ $sub['sub_id'] }}-{{ $sub['brand'] }}">{{ $sub['name'] }}</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div> --}}
<div id="delete-supplier-main-category" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Remove Supplier Category</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-callout alert-danger">
						<i class="fas fa-exclamation-triangle"></i> Are you sure that you want to remove this Category?

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

<div id="update-supplier-criteria-rating-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" action="{{ route('update-rating-criteria-score', ['id'=>$supplier->id]) }}" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-content-save"></i> Update Supplier Criteria Scored</h4>
			</div>
			<div class="modal-body">
				@foreach (getSupplierRatingCriteria() as $gSRC)
					<?php
						$c_score = $ratingScores[$gSRC->id] ?? 0;
						$scorePerc = $c_score/$gSRC->max_score*100;
					?>
					<div class="form-group">
						<h6 style="width: 100%" for="crit-{{ $gSRC->id }}">{{ $gSRC->title }} <span class="badge badge-pill badge-info float-right">0</span></h6>
						<input style="width: 100%" type="range" step="0.1" value="{{ $c_score }}" name="criteria[{{ $gSRC->id }}]" class="form-range" min="0" max="{{ $gSRC->max_score }}" id="crit-{{ $gSRC->id }}">
					</div>
				@endforeach
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-rating-criteria-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" action="{{ route('rating-criteria') }}" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add a new rating Criteria</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-callout alert-danger">
						<i class="fas fa-exclamation-triangle"></i> Please note that this action will add a new criteria to all the supplier on the system?
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Title</label>
					<input type="text" class="form-control" name="title" placeholder="Title" />
				</div>
				<div class="form-group">
					<label class="control-label">Max Score</label>
					<input type="number" min="0" class="form-control" name="max_score" placeholder="Max Score..." />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger"><i class="mdi mdi-plus"></i> Criteria</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-rating-criteria-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Criteria</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-callout alert-warning">
						<i class="fas fa-exclamation-triangle"></i> Please note that this edit will affect all suppliers?
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Title</label>
					<input type="text" class="form-control" name="title" placeholder="Title" />
				</div>
				<div class="form-group">
					<label class="control-label">Max Score</label>
					<input type="number" min="0" class="form-control" name="max_score" placeholder="Max Score..." />
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" name="active" checked="true" /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="delete-supplier-category" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Supplier Item</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-callout alert-danger">
						<i class="fas fa-exclamation-triangle"></i> Are you sure that you want to remove this Item?

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
<div id="delete-supplier-contact" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Supplier Contact</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-callout alert-danger">
						<i class="fas fa-exclamation-triangle"></i> Are you sure that you want to remove this Contact?
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
<div id="view-image-large" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-file-image"></i> Image Preview</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
{{-- <div id="add-a-contract" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" action="{{ route("create-supplier-contract", ['supplier'=>$supplier->id]) }}" id="add-a-contract-form" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="mdi mdi-pencil"></i> Enter Contract Details
				</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Description</label>
					<input type="text" class="form-control" name="description" placeholder="Description..." />
				</div>
				<div class="form-group">
					<label class="control-label">Items </label>
					<select class="form-control" name="item[]" placeholder="Select Items" multiple>
						<option value="">Select Item...</option>
						@foreach ($cats as $item)
							<optgroup label="{{ $item->name }}">
								@foreach ($item->subcategories as $it)
									<option value="{{ $it->id }}"><em>{{ $item->name }}</em> > {{ $it->name }}</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Start Date</label>
					<input type="date" class="form-control" name="start" placeholder="Contract Start Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">End Date</label>
					<input type="date" class="form-control" name="end" placeholder="Contract End Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">File</label>
					<input type="file" class="form-control" name="file" required />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-contract-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" id="edit-contract-form" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="mdi mdi-pencil"></i> Edit Contract Details
				</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Description</label>
					<input type="text" class="form-control" name="description" placeholder="Description..." />
				</div>
				<div class="form-group">
					<label class="control-label">Items</label>
					<select class="form-control" name="item[]" placeholder="Select Items" multiple>
						<option value="">Select Item...</option>
						@foreach ($cats as $item)
							<optgroup label="{{ $item->name }}">
								@foreach ($item->subcategories as $it)
									<option value="{{ $it->id }}"><em>{{ $item->name }}</em> > {{ $it->name }}</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Start Date</label>
					<input type="date" class="form-control" name="start" placeholder="Contract Start Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">End Date</label>
					<input type="date" class="form-control" name="end" placeholder="Contract End Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">File <small class="text-muted">* Leave blank if you do not want to update document</small></label>
					<input type="file" class="form-control" name="file" />
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" name="status" value="1" /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div> --}}
<script>


	$(function(){
		//triggered when edit-an-order modal is about to be shown
		$('#view-image-large').on('show.bs.modal', function(e){
			var img = $(e.relatedTarget).data('img');

			$(this).find('.modal-body').html(`<img src="${img}" style="width:100%" />`);
		});

		$('.form-range').on('change', function(){
			var $rat = parseFloat($(this).val()).toFixed(1);
			var max_score = parseFloat($(this).attr('max'));

			var $rating = $rat/max_score*100;

			var $cls = $rating == 100 ? 'bg-success' : ($rating < 100 && $rating > 60 ?
			'bg-info' : ($rating <= 60 && $rating > 35 ? 'bg-warning' : 'bg-danger'));

			$(this).removeClass('bg-success bg-info bg-warning bg-danger');
			$(this).addClass($cls);

			$(this).parents('.form-group').find('h6').find('.badge').text($rat+"/"+max_score);
		});

		$('#update-supplier-criteria-rating-modal').on('show.bs.modal', function(e){
			$('.form-range').trigger('change');
		});

		$('#add-supplier-contact').on('show.bs.modal', function(e){
			var action = $(e.relatedTarget).data('action');
			var contact = $(e.relatedTarget).data('contact');
			var cid = action != "Edit" ? contact : contact.id;

			if(action != "Edit"){
				$(this).find('input').not('[type="hidden"]').val('');
				$(this).find('span.action').html(`<i class="mdi mdi-plus"></i> Add `);
			}
			else{
				var $form = $(this).find('form');
				$.each(contact, function(k,v){
					console.log(k,v);
					$form.find('input[name="'+k+'"]').not('[type="hidden"]').val(v);
				});
				$(this).find('span.action').html(`<i class="mdi mdi-pencil"></i> Edit `);
			}

			$(this).find('form').attr('action', '{{ route("update-supplier-contact", ["supplier_id"=>$supplier->id]) }}'+(action == "Edit" ? '/'+cid : ''))
			$(this).find('form').prop('action', '{{ route("update-supplier-contact", ["supplier_id"=>$supplier->id]) }}'+(action == "Edit" ? '/'+cid : ''))

		});

		$('#edit-rating-criteria-modal').on('show.bs.modal', function(e){
			var criteria = $(e.relatedTarget).data('criteria');

			var form = $(this).find('form');

			form.find('[name="title"]').val(criteria.title);
			form.find('[name="max_score"]').val(criteria.max_score);

			form.prop('action', '/rating-criteria/'+criteria.id);
			form.attr('action', '/rating-criteria/'+criteria.id);
		});

		$('#delete-supplier-main-category').on('show.bs.modal', function(e){
			var $item = $(e.relatedTarget).data('item');

			var form = $(this).find('form');

			form.prop('action', '/delete-supplier-main-category/'+$item);
			form.attr('action', '/delete-supplier-main-category/'+$item);
		});

		$('#delete-supplier-category').on('show.bs.modal', function(e){
			var $item = $(e.relatedTarget).data('item');

			var form = $(this).find('form');

			form.prop('action', '/delete-supplier-category/'+$item);
			form.attr('action', '/delete-supplier-category/'+$item);
		});

		$('#delete-supplier-contact').on('show.bs.modal', function(e){
			var $item = $(e.relatedTarget).data('contact');

			var form = $(this).find('form');

			form.prop('action', '/remove-supplier-contact/'+$item);
			form.attr('action', '/remove-supplier-contact/'+$item);
		});

		$('#edit-contract-modal').on('show.bs.modal', function(e) {
			var $item = $(e.relatedTarget).data('item');

			var editForm = $('#edit-contract-form');

			$('#edit-contract-form').prop('action', '/edit-supplier-contract/{{ $supplier->id }}/'+$item.id);

			editForm.find('[name="description"]').val($item.description);
			editForm.find('[name="start"]').val($item.start);
			editForm.find('[name="end"]').val($item.end);
			editForm.find('[name="status"]').prop('checked', ($item.status==1));

			var items = Object.keys($item.items);

			$('#edit-contract-form').find('[name="item[]"]').val(items).trigger('change');
		});

		var tables = ['#server-side-goods-receipt', '#server-side-goods-return', '#server-side-orders'];

		$.each(tables, function(t, tb){
			var $url = $(tb).data('url');
			var parent = $(tb).data('parent');
			var ctype = $(tb).data('type');
			var serverTable = $(tb).DataTable({
				lengthMenu: [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
				dom: 'Blfrtip',
				buttons: [
					'copy', 'csv', 'excel', 'pdf', 'print'
				],
				columns: [
					{ data: "loop", "searchable": false },
					{ data: "request_code" },
					{ data: "description" },
					{ data: "due_date" },
					{
						data: null,
						className: "center",
						render: function ( data, type, row ) {
							$(row).find('td:eq(4)').attr('nowrap');
							$(row).find('td:eq(4)').prop('nowrap');
							return `<a class="btn btn-xs text-info btn-transparent" href='/req/${parent}/${data.parent_id}'>${data.parent_request_code}</a>
							`;
						}
					},
					{ data: "created_by" },
					{ data: "created_at" },
					{ data: "status" },
					{
						data: null,
						className: "center",
						render: function ( data, type, row ) {
							$(row).find('td:eq(8)').attr('nowrap');
							$(row).find('td:eq(8)').prop('nowrap');
							return `<a class="btn btn-xs text-primary btn-transparent" href='/req/${ctype}/${data.id}'><i class="mdi mdi-eye"></i></a>
							`;
						}
					}
				],
				destroy: true,
				processing: true,
				serverSide: true,
				ajax: $url
			});
		});
	});
</script>
@endsection
