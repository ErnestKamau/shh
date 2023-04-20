@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>{{ $id!=false ? $grequest->code : 'New' }} - General Requisition | Inventory Management</title>
	<style>
		.d-none{
			display: none;
		}

		select{
			min-width: 150px;
		}

		input:not('[type="checkbox"]'){
			min-width: 100px;
		}

		.np .form-group{
			padding-bottom: 0px !important;
			margin-bottom: 0px !important;
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
					"link" => route('general-requisition-list'),
          'name' => "General Requisition",
          'icon' => null
        ),
        array(
					"link" => route('general-requisition-view', $id ? $grequest->id : '0'),
          'name' => "General Requisition",
          'icon' => null
        )
			);

			$assistant_sup_roles = getConfigByName('assistant_supervisor_role_id');
			$assistant_sup_role_id = count($assistant_sup_roles) > 0 ? $assistant_sup_roles[0]->value : 0;

			$labs_array = getConfigByName('laboratories_list');
			$labs_array = count($labs_array) > 0 ? $labs_array[0]->value : 0;

			$laboratories = explode(';', $labs_array);

			$request_approval_show = false;


			$normalItemsSuppliers = getSuppliers();
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<div class="d-flex p-3 justify-content-between">
			<h3 class="pull-left">
				<i class="mdi mdi-text-box-plus"></i> {{ $id ? '' : 'New' }} {{ $stage }} {{ $id ? ' - '.$grequest->code : '' }} 
				<span class="badge badge-default"><small>{{ $grequest->status ?? 'In Preparation' }}</small></span>
			</h3>
			<div class="pull-right">
				@if($request_approval_show)
					<span class="btn btn-default btn-sm" id="save-details-btn mh-1"><i class="mdi mdi-signature"></i> Request Approvals</span>
				@endif
			</div>
		</div>
		<form class="p-3" method="POST" action="{{ route('general-requisition-update', $id) }}">
			@csrf
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs float-left">
						<li class="nav-item active">
							<a data-toggle="tab" class="nav-link active" href="#details-form">Details</a>
						</li>
						<li class="nav-item "><a data-toggle="tab"  class="nav-link" href="#quotes">Quotes</a></li>
					</ul>
					<div class="float-right">
						<a class="btn btn-info btn-sm mr-2" target="_blank" href="{{ route('general-requisition-document', $grequest->id ?? 0) }}"><i class="mdi mdi-printer"></i> Print</a>	
						<button class="btn btn-success btn-sm"><i class="mdi mdi-content-save"></i> Save</button>						
					</div>
				</div>
				<div class="card-body tab-content">
					<div id="details-form" class="tab-pane show active pt-4">
						<h6><i class="mdi mdi-information"></i> Details</h6>
						<br>
						<div class="row">
							<div class="col-md-4 col-sm-6">
								<div class="form-group">
									<label class="control-label">Requester</label>
									<input type="text" class="form-control" name="requester_name" value="{{ $grequest->requester->name ??  \Auth::user()->name }}" readonly />
									<input type="hidden" class="form-control" name="created_by" value="{{ $grequest->created_by ?? \Auth::user()->id }}" />
								</div>
							</div>
							<div class="col-md-4 col-sm-6">
								<div class="form-group">
									<label class="control-label">Department</label>
									<input type="text" class="form-control" name="requesting_department" value="{{ $grequest->requesting_department ?? \Auth::user()->department()->name }}" readonly />
								</div>
							</div>
							<div class="col-md-4 col-sm-6">
								<div class="form-group">
									<label class="control-label">Laboratory</label>
									<select name="laboratory" class="form-control" placeholder="Select Laboratory...">
										@foreach ($laboratories as $lab)
											<option value="{{ $lab }}" {{ ($grequest->laboratory ?? '') == $lab ? 'selected' : '' }}>{{ $lab }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-md-4 col-sm-6">
								<div class="form-group">
									<label class="control-label">Date Required</label>
									<input type="date" min="{{ date('Y-m-d') }}" class="form-control" name="date_required" value="{{ $grequest->date_required ?? date('Y-m-d') }}" />
								</div>
							</div>
							<div class="col-md-8 col-sm-12">
								<div class="form-group">
									<label class="control-label">Description</label>
									<textarea class="form-control" name="description" placeholder="Purchase Description...">{{ $grequest->description ?? '' }}</textarea>
								</div>
							</div>
						</div>
						<hr>
						<div class="d-flex justify-content-between pv-2">
							<div class="pull-left"><h6><i class="mdi mdi-format-list-bulleted"></i> Requisition Items</h6></div>
							<div class="pull-right">
								@if($grequest->show_pr_approval == 1)
									<span class="btn btn-transparent text-success mh-1" data-url="{{ route('send-notification-gr', [$grequest->id, 'pr']) }}" data-target="#show-approvals-settings-modal" data-toggle="modal"><small><i class="mdi mdi-file-check"></i> Get PR Approvals</small></span>
								@endif
								<span class="btn btn-transparent text-primary mh-1" id="add-item-row"><small><i class="mdi mdi-plus"></i> Add Item</small></span>
								<span class="btn btn-transparent text-danger mh-1" id="remove-rows-btn"><small><i class="mdi mdi-delete"></i> Remove</small></span>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th><input type="checkbox" id="item-checkbox" /></th>
										<th>Item Description</th>
										<th>Product No</th>
										<th>Purpose</th>
										<th>Quantity</th>
										<th>Last Price</th>
										<th>Supplier</th>
										<th>Price</th>
										<th>Quote Date</th>
									</tr>
								</thead>
								<tbody id="item-rows" class="has-toggler-fields">
									@if(count($grequest->items) == 0)
										<tr class="missing-data">
											<td colspan="100%" class="p-2 pt-2">
												<div class="text-align-center alert alert-info mt-2">
													<i class="mdi mdi-information"></i> No Items added yet.
												</div>
											</td>
										</tr>
									@endif
									@foreach ($grequest->items as $item)
										<tr class="existing_row np" data-id="{{ $item->id }}">
											<td>
												<div class="form-group">
													<input type="checkbox" class="item-checkbox" value="{{ $item->id }}" name="item[id][]" />
												</div>
											</td>
											<td>
												<div class="form-group">
													<div class="d-flex" style="width:100%">
														<div class="field flex-grow-1">
															<div class="supplier-field">
																<select class="form-control" name="item[item_id][{{ $item->id }}]" placeholder="Select Item...">
																	<option>Select Item...</option>
																	@foreach ($listedItems as $itm)
																		<option value="{{ $itm->id }}" {{ $item->item_id == $itm->id ? 'selected' : '' }}>{{ $itm->name }}</option>
																	@endforeach
																</select>
															</div>
															<input type="text" style="width: 175px" name="item[text_item_description][{{ $item->id }}]" class="form-control form-control-sm supplier-field d-none" placeholder="Select Item..." />
														</div>
														<div class="icon toggle-field">
															<span class="btn btn-transparent text-info btn-sm"><i class="mdi mdi-swap-vertical"></i></span>
														</div>
													</div>
												</div>
											</td>
											<td>
												<div class="form-group">
													<input type="text" class="form-control form-control-sm" name="item[product_no][{{ $item->id }}]" placeholder="Product Number..." value="{{ $item->product_no }}" />
												</div>
											</td>
											<td>
												<div class="form-group">
													<input type="text" class="form-control form-control-sm" name="item[purpose][{{ $item->id }}]" placeholder="Purpose..." value="{{ $item->purpose }}" />
												</div>
											</td>
											<td>
												<div class="form-group">
													<input type="text" class="form-control form-control-sm" name="item[qty][{{ $item->id }}]" placeholder="Quantity..." value="{{ $item->qty }}" />
												</div>
											</td>
											<td>
												<div class="form-group">
													<input type="text" readonly class="form-control form-control-sm" name="item[last_unit_price][{{ $item->id }}]" placeholder="Last Price..." value="{{ $item->last_unit_price }}" />
												</div>
											</td>
											<td>
												<div class="form-group">
													<select class="form-control" name="item[supplier_id][{{ $item->id }}]" placeholder="Select Supplier...">
														<option value="">Select Supplier...</option>
														@foreach ($filteredSuppliers as $sup)
															<option value="{{ $sup->id }}" {{ $sup->id == $item->supplier_id ? 'selected' : '' }}>{{ $sup->name }}</option>
														@endforeach
													</select>
												</div>
											</td>
											<td>
												<div class="form-group">
													<input readonly type="text" class="form-control form-control-sm" name="item[ext_cost][{{ $item->id }}]" placeholder="Price..." value="{{ $item->ext_cost }}" />
												</div>
											</td>
											<td>
												<div class="form-group">
													<input readonly type="date" class="form-control form-control-sm" name="item[supplier_assignment_date][{{ $item->id }}]" value="{{ $item->supplier_assignment_date }}" placeholder="Quote Date..." />
												</div>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div id="quotes" class="tab-pane fade">
						<div class="d-flex justify-content-between pv-2">
							<div class="pull-left"><h6><i class="mdi mdi-file-document-outline"></i> Quotes</h6></div>
							<div class="pull-right">
								@if($grequest->show_quote_approval == 1)
									<span class="btn btn-transparent text-success mh-1" data-url="{{ route('send-notification-gr', [$grequest->id, 'quote']) }}" data-target="#show-approvals-settings-modal" data-toggle="modal"><small><i class="mdi mdi-file-check"></i> Get Quote Approvals</small></span>
								@endif
								<span class="btn btn-tranparent text-primary mh-1 btn-sm" data-target="#add-quote-details" data-toggle="modal">
									<i class="mdi mdi-plus"></i> Add Quote
								</span>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th><input type="checkbox" id="quote-checkbox"></th>
										<th>Item</th>
										<th>Supplier</th>
										<th>Quantity</th>
										<th>Amount</th>
										<th>Date</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									@if(count($grequest->quotes() ?? []) == 0)
										<tr>
											<td colspan="100%">
												<div class="alert alert-info text-align-center">
													<i class="mdi mdi-information"></i> No attachments found.
												</div>
											</td>
										</tr>
									@endif
									@foreach ($grequest->quotes() ?? [] as $quote)
										<tr class="existing-row quote-checkbox">
											<td><input type="checkbox" class="quote-checkbox" value="{{ $quote->id }}"></td>
											<td>{{ $quote->item_description }}</td>
											<td>{{ $quote->supplier }}</td>
											<td>{{ number_format($quote->qty, 2) }}</td>
											<td>{{ number_format($quote->amount, 2) }}</td>
											<td>{{ $quote->created_at }}</td>
											<td>
												<span class="btn btn-transparent btn-sm text-info mr-2" data-quote="{{ json_encode($quote) }}" data-target="#update-quote-details" data-toggle="modal">
													<i class="mdi mdi-pencil"></i>
												</span>
												<span class="btn btn-transparent btn-sm text-danger mr-2" data-quote="{{ json_encode($quote) }}" data-target="#delete-quote-details" data-toggle="modal">
													<i class="mdi mdi-delete"></i>
												</span>
											</td>
										</tr>	
									@endforeach
								</tbody>
							</table>
						</div>
						<hr>
						<h6><i class="mdi mdi-paperclip"></i> Attachments</h6>
						<div class="pb-2">
							<div class="row p-2 m-1" style="font-weight: 700; background-color: rgba(0,0,0,0.08)">
								<div class="col-sm-6">Document</div>
								<div class="col-sm-4">Type</div>
								<div class="col-sm-2"></div>
							</div>
							@if($grequest->documents->count() == 0)
								<div class="row p-2 m-1">
									<div class="col-sm-12">
										<div class="alert alert-info text-align-center">
											<i class="mdi mdi-information"></i> No attachments found.
										</div>
									</div>
								</div>
							@endif
							@foreach ($grequest->documents as $doc)
								<div class="row p-2 m-1">
									<div class="col-sm-6">{{ $doc->title }}</div>
									<div class="col-sm-4">{{ $doc->type }}</div>
									<div class="col-sm-2">
										<a class="btn text-success btn-sm mr-2" href="{{ $doc->file }}">
											<i class="mdi mdi-download"></i>
										</a>
										<a class="btn text-danger btn-sm mr-2 remove-attachment" download data-attachment="{{ $doc->id }}">
											<i class="mdi mdi-trash"></i>
										</a>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
			<hr title="Approvals">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs float-left">
						<li class="nav-item active">
							<a data-toggle="tab"  class="nav-link active" href="#requisition-approvals">Requisition Approvals</a>
						</li>
						<li class="nav-item">
							<a data-toggle="tab" class="nav-link" href="#quotes-app-approvals">Quote Approvals</a>
						</li>
					</ul>
				</div>
				<div class="card-body tab-content">
					<div id="quotes-app-approvals" class="tab-pane fade">
						<h6>Quote Approvals</h6>
						<hr>
						<div class="row">
							<div class="col-sm-4">
								<p><strong>Requested By</strong></p>
								@if (trim($grequest->requested_by_2)!="")
									@php($dataJ = json_decode($grequest->requested_by_2))
									@if($dataJ->user)
										<p>{{ $dataJ->user->name }}</p>
									@endif
									@if($dataJ->status == "Completed")
										<p><img src="{{ $dataJ->user->signature }}" style="max-width: 100px" /></p>
										<p><i class="mdi mdi-information"></i> {{ $dataJ->status }} <i class="mdi mdi-calendar"></i> {{ $dataJ->time }}</p>
									@else
										<p>
											@if($dataJ->user->id != \Auth::user()->id)
												<span class="btn btn-sn btn-disabled" disabled>
													<i class="mdi mdi-file-check"></i> Approve
												</span>
											@else
												<div class="dropdown show">
													<a class="btn btn-outline-primary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														Approval Action
													</a>
													<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
														<a class="dropdown-item text-success" href="#" data-action="approve" data-type="pr-requested_by_2"  data-target="#request-approval-modal" data-toggle="modal">Approve</a>
														<a class="dropdown-item text-primary" href="#" data-action="return" data-type="pr-requested_by_2"  data-target="#request-approval-modal" data-toggle="modal">Return</a>
														<a class="dropdown-item text-danger" href="#" data-action="reject" data-type="pr-requested_by_2"  data-target="#request-approval-modal" data-toggle="modal">Reject</a>
													</div>
												</div>
											@endif
										</p>
									@endif
								@else
									<p><i class="mdi mdi-information"></span> PENDING</i>
								@endif
							</div>
							<div class="col-sm-4">
								<p><strong>Checked By</strong></p>
								@if (trim($grequest->checked_by_2)!="")
									@php($dataJ = json_decode($grequest->checked_by_2))
									@php($isCompleteC = $dataJ->status == "Completed")
									@if($dataJ->user)
										@if($dataJ->status != "Completed")
											<p>{{ $dataJ->user->name }} 
												<small class="btn btn-transparent text-danger" data-toggle="modal" data-target="#modal-change-current-approver" data-type="checked_by_2">
													<i class="mdi mdi-refresh"></i>
												</small>
											</p>
										@endif
									@endif
									@if($dataJ->status == "Completed")
										<p><img src="{{ $dataJ->user->signature }}" style="max-width: 100px" /></p>
										<p><i class="mdi mdi-information"></i> {{ $dataJ->status }} <i class="mdi mdi-calendar"></i> {{ $dataJ->time }}</p>
									@else
										<p>
											@if($dataJ->user->id != \Auth::user()->id)
												<span class="btn btn-sn btn-disabled" disabled>
													<i class="mdi mdi-file-check"></i> Approve
												</span>
											@else
												<div class="dropdown show">
													<a class="btn btn-outline-primary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														Approval Action
													</a>
													<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
														<a class="dropdown-item text-success" href="#" data-action="approve" data-type="pr-checked_by_2"  data-target="#request-approval-modal" data-toggle="modal">Approve</a>
														<a class="dropdown-item text-primary" href="#" data-action="return" data-type="pr-checked_by_2"  data-target="#request-approval-modal" data-toggle="modal">Return</a>
														<a class="dropdown-item text-danger" href="#" data-action="reject" data-type="pr-checked_by_2"  data-target="#request-approval-modal" data-toggle="modal">Reject</a>
													</div>
												</div>
											@endif
										</p>
									@endif
								@else
									<p><i class="mdi mdi-information"></span> PENDING</i>
								@endif
							</div>
							<div class="col-sm-4">
								<p><strong>Approved By</strong></p>
								@if (trim($grequest->approved_by_2)!="")
									@php($dataJ = json_decode($grequest->approved_by_2))
									@if($dataJ->user)
										<p>{{ $dataJ->user->name }}
											@if($dataJ->status != "Completed")
												<small class="btn btn-transparent text-danger" data-toggle="modal" data-target="#modal-change-current-approver" data-type="approved_by_2">
													<i class="mdi mdi-refresh"></i>
												</small>
											@endif
										</p>
									@endif
									@if($dataJ->status == "Completed")
										<p><img src="{{ $dataJ->user->signature }}" style="max-width: 100px" /></p>
										<p><i class="mdi mdi-information"></i> {{ $dataJ->status }} <i class="mdi mdi-calendar"></i> {{ $dataJ->time }}</p>
									@else
										<p>
											@if($dataJ->user->id != \Auth::user()->id || $isCompleteC == false)
												<span class="btn btn-sn btn-disabled" disabled>
													<i class="mdi mdi-file-check"></i> Approve
												</span>
											@else
												<div class="dropdown show">
													<a class="btn btn-outline-primary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														Approval Action
													</a>
													<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
														<a class="dropdown-item text-success" href="#" data-reason="true" data-action="approve" data-type="pr-approved_by_2"  data-target="#request-approval-modal" data-toggle="modal">Approve</a>
														<a class="dropdown-item text-primary" href="#" data-action="return" data-type="pr-approved_by_2"  data-target="#request-approval-modal" data-toggle="modal">Return</a>
														<a class="dropdown-item text-danger" href="#" data-action="reject" data-type="pr-approved_by_2"  data-target="#request-approval-modal" data-toggle="modal">Reject</a>
													</div>
												</div>
											@endif
										</p>
									@endif
								@else
									<p><i class="mdi mdi-information"></span> PENDING</i>
								@endif
							</div>
						</div>
					</div>
					<div id="requisition-approvals" class="tab-pane active show fade">
						<h6>Requisition Approvals</h6><hr>
						<div class="row">
							<div class="col-sm-4">
								<p><strong>Requested By</strong></p>
								@if (trim($grequest->requested_by_1)!="")
									@php($dataJ = json_decode($grequest->requested_by_1))
									@if($dataJ->user)
										<p>{{ $dataJ->user->name }}</p>
									@endif
									@if($dataJ->status == "Completed")
										<p><img src="{{ $dataJ->user->signature }}" style="max-width: 100px" /></p>
										<p><i class="mdi mdi-information"></i> {{ $dataJ->status }} <i class="mdi mdi-calendar"></i> {{ $dataJ->time }}</p>
									@else
										<p>
											@if($dataJ->user->id != \Auth::user()->id)
												<span class="btn btn-sn btn-disabled" disabled>
													<i class="mdi mdi-file-check"></i> Approve
												</span>
											@else
												<div class="dropdown show">
													<a class="btn btn-outline-primary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														Approval Action
													</a>
													<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
														<a class="dropdown-item text-success" href="#" data-action="approve" data-type="pr-requested_by_1"  data-target="#request-approval-modal" data-toggle="modal">Approve</a>
														<a class="dropdown-item text-primary" href="#" data-action="return" data-type="pr-requested_by_1"  data-target="#request-approval-modal" data-toggle="modal">Return</a>
														<a class="dropdown-item text-danger" href="#" data-action="reject" data-type="pr-requested_by_1"  data-target="#request-approval-modal" data-toggle="modal">Reject</a>
													</div>
												</div>
											@endif
										</p>
									@endif
								@else
									<p><i class="mdi mdi-information"></span> PENDING</i>
								@endif
							</div>
							<div class="col-sm-4">
								<p><strong>Checked By</strong></p>
								@if (trim($grequest->checked_by_1)!="")
									@php($dataJ = json_decode($grequest->checked_by_1))
									@php($isCompleteC = $dataJ->status == "Completed")
									@if($dataJ->user)
										<p>{{ $dataJ->user->name }}
											@if($dataJ->status != "Completed")
												<small class="btn btn-transparent text-danger" data-toggle="modal" data-target="#modal-change-current-approver" data-type="checked_by_1">
													<i class="mdi mdi-refresh"></i>
												</small>
											@endif
										</p>
									@endif
									@if($dataJ->status == "Completed")
										<p><img src="{{ $dataJ->user->signature }}" style="max-width: 100px" /></p>
										<p><i class="mdi mdi-information"></i> {{ $dataJ->status }} <i class="mdi mdi-calendar"></i> {{ $dataJ->time }}</p>
									@else
										<p>
											@if($dataJ->user->id != \Auth::user()->id)
												<span class="btn btn-sn btn-disabled" disabled>
													<i class="mdi mdi-file-check"></i> Approve
												</span>
											@else
												<div class="dropdown show">
													<a class="btn btn-outline-primary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														Approval Action
													</a>
													<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
														<a class="dropdown-item text-success" href="#" data-action="approve" data-type="pr-checked_by_1"  data-target="#request-approval-modal" data-toggle="modal">Approve</a>
														<a class="dropdown-item text-primary" href="#" data-action="return" data-type="pr-checked_by_1"  data-target="#request-approval-modal" data-toggle="modal">Return</a>
														<a class="dropdown-item text-danger" href="#" data-action="reject" data-type="pr-checked_by_1"  data-target="#request-approval-modal" data-toggle="modal">Reject</a>
													</div>
												</div>
											@endif
										</p>
									@endif
								@else
									<p><i class="mdi mdi-information"></span> PENDING</i>
								@endif
							</div>
							<div class="col-sm-4">
								<p><strong>Approved By</strong></p>
								@if (trim($grequest->approved_by_1)!="")
									@php($dataJ = json_decode($grequest->approved_by_1))
									@if($dataJ->user)
										<p>{{ $dataJ->user->name }}
											@if($dataJ->status != "Completed")
												<small class="btn btn-transparent text-danger" data-toggle="modal" data-target="#modal-change-current-approver" data-type="approved_by_1">
													<i class="mdi mdi-refresh"></i>
												</small>
											@endif
										</p>
									@endif
									@if($dataJ->status == "Completed")
										<p><img src="{{ $dataJ->user->signature }}" style="max-width: 100px" /></p>
										<p><i class="mdi mdi-information"></i> {{ $dataJ->status }} <i class="mdi mdi-calendar"></i> {{ $dataJ->time }}</p>
									@else
										<p>
											@if($dataJ->user->id != \Auth::user()->id || $isCompleteC == false)
												<span class="btn btn-sn btn-disabled" disabled>
													<i class="mdi mdi-file-check"></i> Approve
												</span>
											@else
												<div class="dropdown show">
													<a class="btn btn-outline-primary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														Approval Action
													</a>
													<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
														<a class="dropdown-item text-success" href="#" data-action="approve" data-type="pr-approved_by_1"  data-target="#request-approval-modal" data-toggle="modal">Approve</a>
														<a class="dropdown-item text-primary" href="#" data-action="return" data-type="pr-approved_by_1"  data-target="#request-approval-modal" data-toggle="modal">Return</a>
														<a class="dropdown-item text-danger" href="#" data-action="reject" data-type="pr-approved_by_1"  data-target="#request-approval-modal" data-toggle="modal">Reject</a>
													</div>
												</div>
											@endif
										</p>
									@endif
								@else
									<p><i class="mdi mdi-information"></span> PENDING</i>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</main>
@endsection
@section('script2')
<div class="modal" id="add-quote-details">
	<div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('add-gr-quote', $grequest->id) }}" enctype="multipart/form-data">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Supplier Quote</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
				@csrf
				<div class="form-group">
					<label class="">Supplier</label>
					<div class="d-flex has-toggler-fields" style="width:100%">
						<div class="field flex-grow-1">
							<div class=" supplier-field">
								<select class="form-control" name="supplier_id" placeholder="Select Supplier...">
									@foreach ($normalItemsSuppliers as $sup)
										<option value="{{ $sup->id }}">{{ $sup->name }}</option>
									@endforeach
								</select>
							</div>
							<input type="text" name="new_supplier" class="form-control form-control-sm supplier-field d-none" placeholder="Supplier Name..." />
						</div>
						<div class="icon toggle-field">
							<span class="btn btn-transparent text-info btn-sm"><i class="mdi mdi-swap-vertical"></i></span>
						</div>
					</div>
				</div>
				<div class="row p-2" style="font-weight: 700; background-color: rgba(0,0,0,0.08)">
					<div class="col-sm-1"></div>
					<div class="col-sm-4">Item</div>
					<div class="col-sm-4">Quantity</div>
					<div class="col-sm-3">Price</div>
				</div>
				@if($grequest->items->count())
					<div class="row">
						<div class="col-sm-12">
							<div class="alert alert-info">
								<i class="mdi mdi-information"></i> No items added to the requisition yet.
							</div>
						</div>
					</div>
				@endif
				<br><br>
				@foreach ($grequest->items as $it)
					<div class="row">
						<div class="col-sm-1"><input type="checkbox" name="quote[request_item_id][{{ $it->id }}]" class="toggle-quote-checkbox" value="{{ $it->id }}" /></div>
						<div class="col-sm-4">
							<div class="form-group">
								<input type="text" class="form-control form-control-sm" value="{{ $it->item_description }}" readonly />
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<input type="text" class="form-control form-control-sm" value="{{ $it->qty }}" readonly />
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<input type="number" min="0" step="any" name="quote[amount][{{ $it->id }}]" class="form-control form-control-sm" value="" />
							</div>
						</div>
					</div>
				@endforeach
        <div class="form-group mt-2">
					<label class="control-label">Upload Quote</label>
					<input type="file" class="form-control" name="file"/>
				</div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-transparent text-danger" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<div class="modal" id="modal-change-current-approver">
	<div class="modal-dialog">
    <form class="modal-content" method="POST">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Change Approver</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
				@csrf
        <div class="form-group">
					<label class="control-label">Change Approver</label>
					<select class="form-control" name="user" placeholder="Select Approver..." required>
						<option value="">Select Approver...</option>
						@foreach ($getUsers as $us)
							<option value="{{ $us->id }}">{{ $us->name }}</option>
						@endforeach
					</select>
				</div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Change Approver</button>
        <button type="button" class="btn btn-transparent text-danger" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<div class="modal" id="show-approvals-settings-modal">
	<div class="modal-dialog">
    <form class="modal-content" method="POST">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Set Approvals</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
				@csrf
        <div class="form-group">
					<label class="control-label">Checked By</label>
					<select class="form-control" name="checked_by" placeholder="Select Checked By..." required>
						<option value="">Select Checked By...</option>
						@foreach ($getUsers as $us)
							<option value="{{ $us->id }}">{{ $us->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Approved By</label>
					<select class="form-control" name="approved_by" placeholder="Select Checked By..." required>
						<option value="">Select Checked By...</option>
						@foreach ($getUsers as $us)
							<option value="{{ $us->id }}">{{ $us->name }}</option>
						@endforeach
					</select>
				</div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Send Requests</button>
        <button type="button" class="btn btn-transparent text-danger" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<div class="modal" id="delete-quote-details">
	<div class="modal-dialog">
    <form class="modal-content" method="POST">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Delete Quote</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
				@csrf
        <div class="alert alert-danger">
					<i class="mdi mdi-delete"></i> Are you sure you want to delete this quote?
				</div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Danger</button>
        <button type="button" class="btn btn-transparent text-danger" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<div class="modal" id="request-approval-modal">
	<div class="modal-dialog">
    <form class="modal-content" method="POST">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Approve Request</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
				@csrf
        <div class="alert alert-info">
					<i class="mdi mdi-information"></i> Are you sure you want to <span class="action-type-text"></span> this?
				</div>
				<input type="hidden" name="action" id="approval-action-type" />
				<div class="form-group d-none approval-reason">
					<label class="control-label">Reason for Approval</label>
					<textarea name="reason_for_approval" class="form-control" placeholder="Reason for Approval..."></textarea>
				</div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Approve</button>
        <button type="button" class="btn btn-transparent text-danger" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<div class="modal" id="update-quote-details">
	<div class="modal-dialog">
    <form class="modal-content" method="POST">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Edit Quote Amount</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
				@csrf
        <div class="form-group">
					<label class="control-label">Item Description</label>
					<input type="text" class="form-control item-name" value="" readonly/>
				</div>
        <div class="form-group">
					<label class="control-label">Supplier</label>
					<input type="text" class="form-control item-supplier" value="" readonly/>
				</div>
        <div class="form-group">
					<label class="control-label">Quantity</label>
					<input type="text" class="form-control item-quantity" value="" readonly/>
				</div>
        <div class="form-group">
					<label class="control-label">Price</label>
					<input type="number" min="0" step="any" name="amount" class="form-control item-amount" value=""/>
				</div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-transparent text-danger" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.min.css"
	integrity="sha512-bkB9w//jjNUnYbUpATZQCJu2khobZXvLP5GZ8jhltg7P/dghIrTaSJ7B/zdlBUT0W/LXGZ7FfCIqNvXjWKqCYA=="
	crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet"
	href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.bootstrap4.min.css"
	integrity="sha512-MMojOrCQrqLg4Iarid2YMYyZ7pzjPeXKRvhW9nZqLo6kPBBTuvNET9DBVWptAo/Q20Fy11EIHM5ig4WlIrJfQw=="
	crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js"
	integrity="sha512-pF+DNRwavWMukUv/LyzDyDMn8U2uvqYQdJN0Zvilr6DDo/56xPDZdDoyPDYZRSL4aOKO/FGKXTpzDyQJ8je8Qw=="
	crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
	var selectizerNow = function(tis){
		var $url = $(tis).data('url');
		if($url){
			$(tis).selectize({
				valueField: "id_name",
				labelField: "name",
				searchField: "name",
				create: false,
				onInitialize: function() {
					this.trigger('change', this.getValue(), true);
				},
				render: {
					option: function (item, escape) {
						return (
							`<div class="p-2"><i class="far fa-address-card"></i> ${item.name} <small class="text-muted">${item.reference}</small></div>`
						);
					},
				},
				load: function (query, callback) {
					if (!query.length) return callback();
					$.ajax({
						url: $url + encodeURIComponent(query),
						type: "GET",
						error: function () {
							callback();
						},
						success: function (res) {
							callback(res);
						},
					});
				},
			});
		}
		else{
			$(tis).selectize();
		}
	}
	window.csrfToken = '{{ csrf_token() }}';
	var loadRow = function(){
		var tr = $(`
			<tr class="new_row np">
				<td>
					<div class="form-group">
						<input type="checkbox" class="item-checkbox"  name="item[id][]" />
					</div>
				</td>
				<td>
					<div class="form-group">
						<div class="d-flex" style="width:100%">
							<div class="field flex-grow-1">
								<div class="supplier-field">
									<select class="form-control" name="new_item[item_id][]" placeholder="Select Item...">
										<option>Select Item...</option>
										@foreach ($listedItems as $itm)
											<option value="{{ $itm->id }}">{{ $itm->name }}</option>
										@endforeach
									</select>
								</div>
								<input type="text" style="width: 175px" name="new_item[text_item_description][]" class="form-control form-control-sm supplier-field d-none" placeholder="Select Item..." />
							</div>
							<div class="icon toggle-field">
								<span class="btn btn-transparent text-info btn-sm"><i class="mdi mdi-swap-vertical"></i></span>
							</div>
						</div>
					</div>
				</td>
				<td>
					<div class="form-group">
						<input type="text" class="form-control form-control-sm" name="new_item[product_no][]" placeholder="Product Number..." />
					</div>
				</td>
				<td>
					<div class="form-group">
						<input type="text" class="form-control form-control-sm" name="new_item[purpose][]" placeholder="Purpose..." />
					</div>
				</td>
				<td>
					<div class="form-group">
						<input type="text" class="form-control form-control-sm" name="new_item[qty][]" placeholder="Quantity..." required />
					</div>
				</td>
				<td>
					<div class="form-group">
						<input type="text" class="form-control form-control-sm" name="new_item[last_unit_price][]" placeholder="Last Price..." />
					</div>
				</td>
				<td>
					<div class="form-group">
						<select style="width: 175px" data-url="{{ route('get-supplier-list') }}/" placeholder="Select Supplier..." class="form-control form-control-sm" name="new_item[supplier_id][]">
							<option value="">Select Supplier...</option>
						</select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<input type="number" step="any" min="0" class="form-control form-control-sm" name="new_item[ext_cost][]" placeholder="Price..." />
					</div>
				</td>
				<td>
					<div class="form-group">
						<input type="date" class="form-control form-control-sm" name="new_item[supplier_assignment_date][]" placeholder="Quote Date..." />
					</div>
				</td>
			</tr>
		`);

		var trow = tr.clone();

		return trow;
	}

	$(function(){
		$(this).find('.selectize-field').each(function(){
			if($(this)[0].selectize){
				$(this)[0].selectize.destroy();
			}
			selectizerNow(this);
		});
		$('#item-checkbox').on('change', function(){
			var checked = $(this).is(':checked');
			$('#item-rows').find('.item-checkbox[type="checkbox"]').removeClass('selected');

			$('#item-rows').find('.item-checkbox[type="checkbox"]').each(function(){
				var $parentRow = $(this).parents('tr');
				if(checked){
					$(this).attr('checked', true);
					$(this).prop('checked', true);

					$parentRow.addClass('selected');
				}
				else{
					$(this).prop('checked', false);
					$(this).removeAttr('checked');

					$parentRow.removeClass('selected');
				}
			});
		});

		$('#add-item-row').on('click', function(){
			$('#item-rows').find('.missing-data').remove();
			var rRow = loadRow();

			$('#item-rows').append(rRow);
			
			rRow.find('.selectize-field').each(function(){
				if($(this)[0].selectize){
					$(this)[0].selectize.destroy();
				}
				selectizerNow(this);
			});
		});

		$('.has-toggler-fields').on('click', '.toggle-field', function(){
			console.log("Heeeey")
			var $selected = $(this).parents('.form-group').find('.supplier-field.d-none');
			$(this).parents('.form-group').find('.supplier-field').addClass('d-none');
			$selected.removeClass('d-none');
			$(this).parents('.form-group').find('input.supplier-field').val('');
		});

		$('#item-rows').on('click', '.item-checkbox', function(){
			$(this).parents('tr').addClass('selected');
		});

		$('#update-quote-details').on('show.bs.modal', function(e){
			var target = $(e.relatedTarget);
			var quote = target.data('quote');

			$(this).find('.item-name').val(quote.item_description);
			$(this).find('.item-quantity').val(quote.qty);
			$(this).find('.item-supplier').val(quote.supplier);
			$(this).find('.item-amount').val(quote.amount);

			var action = "{{ route('update-gr-quotes') }}/"+quote.id;

			$(this).find('form').prop('action', action);
			$(this).find('form').attr('action', action);
		});

		$('#request-approval-modal').on('show.bs.modal', function(e){
			var target = $(e.relatedTarget);
			var hasReason = target.data('reason');
			var dType = target.data('type');
			var dTypeParts = dType.split('-');
			var action = target.data('action');

			if(hasReason || $.inArray(action, ['return', 'reject']) > -1){
				$(this).find('.approval-reason').removeClass('d-none');
			}
			else{
				$(this).find('.approval-reason').addClass('d-none');
			}

			$('#approval-action-type').val(action);

			var action = "{{ route('approve-this-gr') }}/{{ $grequest->id }}/"+dType;


			$(this).find('form').prop('action', action);
			$(this).find('form').attr('action', action);
		});

		$('#modal-change-current-approver').on('show.bs.modal', function(e){
			var target = $(e.relatedTarget);
			var type = target.data('type');
			
			
			var action = "{{ route('change-approver-this-gr') }}/{{ $grequest->id }}/"+type;

			$(this).find('form').prop('action', action);
			$(this).find('form').attr('action', action);
		});

		$('#delete-quote-details').on('show.bs.modal', function(e){
			var target = $(e.relatedTarget);
			var quote = target.data('quote');

			var action = "{{ route('delete-gr-quotes') }}/"+quote.id;

			$(this).find('form').prop('action', action);
			$(this).find('form').attr('action', action);
		});
		
		$('#show-approvals-settings-modal').on('show.bs.modal', function(e){
			var target = $(e.relatedTarget);
			var action = target.data('url');

			$(this).find('form').prop('action', action);
			$(this).find('form').attr('action', action);
		});

		$('#remove-rows-btn').on('click', function(){
			if(confirm("Are you sure you want to remove this items?")){
				var ids = [];
				var existingOnes = [];
				$('#item-rows').find('tr.selected').each(function(){
					var id = $.trim($(this).data('id'));
					if(parseInt(id) > 0){
						ids.push(id);
					}
					var trMain = $(this); 
					if(trMain.hasClass('new_row')){
						trMain.slideUp(function(){
							trMain.remove();
						});
					}
					else{
						existingOnes.push(trMain);
					}
				});

				if(ids.length > 0){
					$.ajax({
						url: '{{ route("remove-general-requisition-rows", $grequest->id ?? 0) }}',
						type: 'POST',
						dataType: 'json',
						data: {
							'_token': window.csrfToken,
							'items': ids,
							'type': 'item' 
						},
						beforeSend: function(){
						},
						success: function(js){
							$.each(existingOnes, function(k,v){
								v.slideUp(function(){
									v.remove();
								});
							});
						}
					});
				}
			}
		});
	});
</script>
@endsection