@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<?php $lab_storage_catgory_id = systemVariables('lab_samples_category_id'); ?>
	<title>{{ $request->request_code ?? 'New '.rtrim($stage, 's') }} {{ " - ".$stage  }} | Inventory Management</title>
	<style>
		.bg-selected{
			color: #fff;
			background-color: #3d5777;
		}

		.hide{
			display: none;
		}

		.item-row td .form-group {
			margin-bottom: unset !important;
		}

		#document-flow-holder{
			white-space: nowrap; /* important */
    	overflow: auto;
		}

		.flow-doc-holder{
			display: inline-block;
			padding: 10px;
			vertical-align: text-top;
		}
		.flow-doc{
			max-width: 305px;
			width: 245px;
			text-align: center;
			border-radius: 9px;
			border: 1px solid #5f84a2;
			box-shadow: 0px 0px 15px #b5b5b5;
			margin-bottom: 10px;
		}

		.flow-doc.is_current{
			border: 3px solid #5f84a2;
			box-shadow: 0px 0px 15px #a9a9a9;
		}

		.flow-doc .header{
			font-size: 14px;
			font-weight: 600;
			color: #676767;
			padding: 5px 9px;
			border-bottom:1px solid #5f84a2;
		}

		.flow-doc .body{
			font-size: 12px;
			padding: 5px;
			border-bottom:1px solid #d5d5d5;
		}

		.flow-doc .footer{
			font-size: 12px;
			padding: 5px;
			color: #34f;
		}
	</style>
@endsection
@section('content2')
<?php
	$allItems = $request->items($ammendment) ?? array();

	$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

	// echo json_encode($normalItems, JSON_PRETTY_PRINT);

	$otherItems = empty($allItems) ? array() : ($allItems['issued_received'] ?? array());

	$normalItemIDS = [];

	foreach ($normalItems as $i) {
		$normalItemIDS[] = $i->inventory_sub_category_id;
	}

	$normalItemsSuppliers = getSuppliers($normalItemIDS);
	$inventoryItems = [];
	// $inventoryItems = getInventoryItems($lab_storage_catgory_id, true);
	// echo json_encode($inventoryItems);
?>
<?php
	$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
	$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;

	$departmental_head_roles = getConfigByName('departmental_head_role_id');
	// echo ">>>>>>>>>>>>>>>>".json_encode($departmental_head_roles);
	$departmental_head_role_id = count($departmental_head_roles) > 0 ? $departmental_head_roles[0]->value : 0;

	$finance_department_roles = getConfigByName('finance_department_role_id');
	$finance_department_role_id = count($finance_department_roles) > 0 ? $finance_department_roles[0]->value : 0;

	$store_manager_roles = getConfigByName('store_manager_role_id');
	// echo "This is it ".json_encode($store_manager_roles);
	$store_manager_role_id = count($store_manager_roles) > 0 ? $store_manager_roles[0]->value : 0;

?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('go_to_stage', ['stage'=>$stage]),
          'name' => $stage,
          'icon' => null
	),
        array(
          'link' => null,
          'name' => $request->request_code ?? 'New '.rtrim($stage, 's'),
          'icon' => null
        )
      );
    ?>
		<x-bread-crumb :items="$items"></x-bread-crumb>
		<?php $approvals = getStageApprovals('Requisition', $stage); ?>
    <h3 class="p-4">
			<i class="mdi mdi-text-box-plus"></i> {{ !isset($request->status) ? 'Create' : '' }} {{ $stage }} {{ $request->request_code ?? '' }}
			@if ($request->ammendment > 1)
				<span class="btn-group" role="group">
					<button id="btnGroupDrop1" type="button" class="btn-sm btn btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						v{{ $ammendment }}
					</button>
					<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
						@for ($a = $request->ammendment; $a >=1; $a--)
							<a class="dropdown-item save-details-form" data-type="save-details" href="{{ route('view-request-details', ['stage'=>$request->request_type, 'id'=>$request->id, 'ammendement'=>$a]) }}">
								<i class="mdi mdi-chevron-double-right"></i> v{{ $a }}
							</a>
						@endfor
					</div>
				</span>
			@endif
			<small class="badge badge-pill bg-white my-small-text"  {!! in_array($stage, ["Material Requisition", "Request for Quotation", "Purchase Orders", "Request to Store", "Material Issuance"]) ? 'data-target="#jump-to-status-modal" data-toggle="modal"' : '' !!}>
				<i class="mdi mdi-information-outline"></i> {{ isset($request->status) ? $request->status : 'In Preparation' }}</small>
			@if ((isset($request->status) && $request->status == "In Preparation" || !isset($request->status)))
				<button class="btn btn-default text-primary float-right save-details-form" data-type="save-details">
					<i class="mdi mdi-content-save"></i> Save
				</button>
			@else
				<button class="btn btn-default text-primary float-right save-details-form hidden" id="save-other-changes" data-type="save-details">
					<i class="mdi mdi-content-save"></i> Save
				</button>
			@endif

			@if(isset($request->status) && $request->in_ammendment > 0)
				<?php $isRequestIniator = $request->status == "Amendment Awaiting Approval" && $request->request_initiator = \Auth::user()->id ?>
				<div class="btn-group float-right" role="group">
					<button id="btnGroupDrop1" type="button" class="btn btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="mdi mdi-file-edit text-danger"></i> Amendment {!! $isRequestIniator ? '<sup class="label label-danger"><i class="mdi mdi-alert-circle text-danger"></i></sup>' :'' !!}
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
						@if($isRequestIniator)
							<a class="dropdown-item text-success" data-toggle="modal" data-target="#approve-ammendments-details-form" href="#"><i class="mdi mdi-check-circle"></i> Approve Amendments</a>
							<a class="dropdown-item text-danger" data-toggle="modal" data-target="#reject-ammendment-modal" href="#"><i class="mdi mdi-close-circle"></i> Reject Amendment</a>
						@else
							<a class="dropdown-item save-details-form text-success" data-type="save-details" href="#"><i class="mdi mdi-content-save-edit"></i> Save Amendments</a>
							<a class="dropdown-item text-danger" data-toggle="modal" data-target="#reject-ammendment-modal" href="#"><i class="mdi mdi-close-circle"></i> Cancel Amendment</a>
							@if($request->in_ammendment > 1)
								<a class="dropdown-item save-details-form text-primary" data-type="get-requester-approval" href="#"><i class="mdi mdi-account-check"></i> Get Requester Approval</a>
							@endif
						@endif
					</div>
				</div>
			@endif

			@if($stage == "Material Issuance")
				@if (isset($request->status) && in_array($request->status, array("Awaiting User Reception")))
					<button class="btn btn-default text-info float-right" data-target="#issue-items-otp-modal" data-toggle="modal">
						<i class="mdi mdi-archive-arrow-up-outline"></i> Issue Items
					</button>
				@endif
				@if(isset($request->status) && $request->status == "In Preparation")
					<button class="btn btn-default text-success float-right save-details-form" data-type="notify-user">
						<i class="mdi mdi-bell-ring"></i> Notify User
					</button>
				@endif
			@endif
			@if ($request->request_type == "Purchase Orders")
				@if (in_array($request->status, array("Approval Complete", "Purchase Order Sent")))
					@if(\Auth::user()->hasRole($store_manager_role_id, true))
						<span class="btn btn-default text-dark float-right" data-target="#Create-Goods-Receipt-Modal"
							data-toggle="modal">
							<i class="mdi mdi-file-move"></i> Create Goods Receipt
						</span>
						<span class="btn btn-default text-warning float-right" data-target="#Create-Goods-Return-Modal"
							data-toggle="modal">
							<i class="mdi mdi-file-undo"></i> Create Return Note
						</span>
					@endif
					@if($request->status == "Approval Complete")
						<button class="btn btn-default text-dark float-right save-details-form" data-type="send-purchase-order">
							<i class="mdi mdi-send"></i> Send Purchase Order
						</button>
					@endif
					@if (isset($request->status) && \Auth::user()->hasRole($procurement_officer_role_id, true))
						<button class="btn btn-default text-success float-right save-details-form" data-type="mark-as-completed" data-alert="Are you sure you want to proceed?">
							<i class="mdi mdi-content-save"></i> Mark as Complete
						</button>
					@endif
				@endif
			@endif
			@if ($request->request_type == "Goods Receipt")
				@if (in_array($request->status,array("Approval Complete", "Partially Fulfilled")))
					<button class="btn btn-default text-dark float-right" data-target="#accept-goods-otp-modal" data-toggle="modal">
						<i class="mdi mdi-package-variant-closed"></i> Accept Goods
					</button>
				@endif

				@if($request->status == "Goods Accepted")
					<button class="btn btn-default text-primary float-right" data-target="#send-to-finance-modal" data-toggle="modal">
						<i class="mdi mdi-file-account-outline"></i> Send to Finance
					</button>
				@endif
				@if($request->status == "Awaiting Finance Approval" && \Auth::user()->hasRole($finance_department_role_id, true))
					<button class="btn btn-default text-primary float-right" data-target="#finance-department-modal" data-toggle="modal">
						<i class="mdi mdi-content-save"></i> Mark as Complete
					</button>
				@endif
			@endif

			@if ($request->request_type == "Goods Return")
				@if (in_array($request->status,array("Approval Complete")))
					<button class="btn btn-default text-dark float-right" data-target="#return-goods-to-supplier-modal" data-toggle="modal">
						<i class="mdi mdi-package-variant-closed"></i> Return Goods
					</button>
				@endif
			@endif
			@if(($approvals->count() ?? 0) > 0 && (count($normalItems) > 0))
				@if ($request->status == "In Preparation" && $stage=="Material Requisition")
					<button class="btn btn-default text-dark float-right save-details-form" data-type="get-approval-details">
						<i class="mdi mdi-account-check"></i> Get Approval
					</button>
				@endif
				@if ($request->status == "In Preparation" && $stage=="Request to Store")
					<button class="btn btn-default text-dark float-right save-details-form" data-type="get-approval-details">
						<i class="mdi mdi-account-check"></i> Get Approval
					</button>
				@endif
				@if ($stage == "Request for Quotation" && $request->supplier_rfqs()->count() > 0)
					@if ($request->status != "Approval Complete" && count($request->quotes()) == 0 && \Auth::user()->hasRole($procurement_officer_role_id, true))
						<button class="btn btn-default text-dark float-right save-details-form" data-type="send-rfq-details">
							<i class="mdi mdi-email-send"></i> Send Out RFQS
						</button>
					@endif
					@if ($request->status == "Awarded")
						<button class="btn btn-default text-dark float-right save-details-form" data-type="get-approval-details">
							<i class="mdi mdi-account-check"></i> Get Approval
						</button>
					@endif
					@if ($request->status == "Approval Complete" && Auth::user()->hasRole($procurement_officer_role_id, true))
						<?php $approvalStatus = $request->approvals(); ?>
						<button class="btn btn-default text-dark float-right save-details-form" data-type="generate-purchase-order">
							<i class="mdi mdi-file-move"></i> Create Purchase Order
						</button>
					@endif
				@endif
				@if ($request->status == "Approval Complete" && $stage=="Request to Store")
					<button class="btn btn-default text-info float-right" data-toggle="modal" data-target="#Create-Material-Issuance-Modal">
						<i class="mdi mdi-package-variant-closed"></i> Create Material Issuance
					</button>
				@endif
				@if ($request->status == "Approval Complete" && $stage=="Material Requisition" && Auth::user()->hasRole($departmental_head_role_id, true))
					<button class="btn btn-default text-success float-right save-details-form" data-type="create-rfq-from-material-requisition">
						<i class="mdi mdi-text-box-plus-outline"></i> Create RFQ
					</button>
					{{-- <button class="btn btn-default text-primary float-right" data-target="#create-lpo-from-mr-modal" data-toggle="modal">
						<i class="mdi mdi-text-box-plus-outline"></i> Create LPO
					</button> --}}
				@endif
			@endif
		</h3>
		<br>
		<form id="details-form" class="bg-light" method="POST" enctype="multipart/form-data" action="" style="clear: both !important">
			@csrf
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" style="font-size: 14px" id="Request-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="General-tab" data-toggle="tab" href="#General" role="tab" aria-controls="General" aria-selected="true">General</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Items-tab" data-toggle="tab" href="#Items" role="tab" aria-controls="Items" aria-selected="true">
								Items
								<small class="badge badge-pill badge-secondary">{{ count($normalItems) }}</small>
							</a>
						</li>
						@if (in_array($stage, array("Goods Receipt", "Material Issuance")))
						{{-- <li class="nav-item hide-em">
							<a class="nav-link" id="Received-Items-tab" data-toggle="tab" href="#Received-Items" role="tab" aria-controls="Received-Items" aria-selected="true">
								{{ $stage == "Goods Receipt" ? 'Received' : 'Issued' }} Items
								<small class="badge badge-pill badge-secondary">{{ count($otherItems) }}</small>
							</a>
						</li> --}}
						@endif
						@if ($stage == "Request for Quotation")
							<li class="nav-item">
								<a class="nav-link" id="Suppliers-tab" data-toggle="tab" href="#Suppliers" role="tab" aria-controls="Suppliers" aria-selected="true">Suppliers</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Quotes-tab" data-toggle="tab" href="#Quotes" role="tab" aria-controls="Quotes" aria-selected="true">Quotes</a>
							</li>
						@endif
						<li class="nav-item">
							<a class="nav-link" id="Approvals-tab" data-toggle="tab" href="#Approvals" role="tab" aria-controls="Approvals" aria-selected="true">
								Approvals
								@if (isset($request->status) && $request->status == 'Awaiting Approval')
									<small class="badge badge-pill badge-danger my-small-text"><i class="mdi mdi-bell-ring bell"></i></small>
								@endif
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Notes-tab" data-toggle="tab" href="#Notes" role="tab" aria-controls="Notes" aria-selected="true">
								Notes
								<?php $notesList = isset($request->status) ? $request->notes() : array() ; ?>
								<small class="badge badge-pill badge-secondary">{{ count($notesList) ?? 0 }}</small>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Attachments-tab" data-toggle="tab" href="#Attachments" role="tab" aria-controls="Attachments" aria-selected="true">
								Attachments
								<?php $attachmentList = isset($request->status) ? $request->attachments() : array(); ?>
								<small class="badge badge-pill badge-secondary">{{ count($attachmentList) ?? 0 }}</small>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Requisition-tab" data-toggle="tab" href="#Requisition" role="tab" aria-controls="Requisition" aria-selected="true">
								<i class="mdi mdi-sitemap"></i> Requisition Flow
							</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="Request-tabs-content">
					<div class="tab-pane fade p-3" id="Attachments" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3 mt-1">Attachments
							<span class="btn btn-default text-primary float-right" data-target="#add-attachment-modal" data-toggle="modal">
								<i class="mdi mdi-plus"></i> Attachment
							</span>
							<span class="btn btn-default text-danger float-right delete-this-row mr-2" data-holder="#attachment-items">
								<i class="mdi mdi-delete"></i> Remove
							</span>
						</h5>
						<div class="row no-gutters">
							<div class="table-responsive">
								<table class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
									<thead>
										<tr>
											<th>#</th>
											<th nowrap>File</th>
											<th nowrap>Title</th>
											<th nowrap>Type</th>
											<th nowrap>Created By</th>
											<th nowrap>Created On</th>
											<th nowrap></th>
										</tr>
									</thead>
									<tbody id="attachment-items" class="notes-and-attachments" data-attachments='{{ json_encode($attachmentList) }}'></tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Notes" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3 mt-1">Notes
							<span class="btn btn-default text-primary float-right" data-target="#add-note-modal" data-toggle="modal">
								<i class="mdi mdi-plus"></i> Note
							</span>
							<span class="btn btn-default text-danger float-right delete-this-row mr-2" data-holder="#note-items">
								<i class="mdi mdi-delete"></i> Remove
							</span>
						</h5>
						<div class="row no-gutters">
							<div class="table-responsive">
								<table class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
									<thead>
										<tr>
											<th>#</th>
											<th nowrap>Note Type</th>
											<th nowrap>Created By</th>
											<th nowrap>Created On</th>
											<th nowrap></th>
										</tr>
									</thead>
									<tbody id="note-items" class="notes-and-attachments" data-notes='{{ json_encode($notesList) }}'></tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Requisition" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3 mt-1">Requisition Document Flow</h5>
						<div id="document-flow-holder">
							<?php $arrays = getDocumentTemplates(); ?>
							@foreach($documentFlow as $stName=>$docs)
								<span class="flow-doc-holder">
									<h6 style="font-size: 16px">{{ $stName }}</h6>
									<br>
									@foreach($docs as $doc)
										<div class="flow-doc {{ $stName==$stage && $doc->id == $request->id ? 'is_current' :'' }}">
											<div class="header">
												<header>{{ $doc->request_code }} - <small class="text-muted">{{ $doc->status }}</small>
													@if(isset($arrays[$stName]))
														<a target="_blank" href="{{ route('req-report-generate', ['id'=>$doc->id]) }}" class="pull-right float-right">
															<i class="mdi mdi-printer"></i>
														</a>
														@if ($doc->request_type == "Goods Receipt")
															<a title="Supply Inspection Form" data-toggle="tooltip" target="_blank" href="{{ route('req-report-generate', ['id'=>$doc->id, 'supply'=>true]) }}" class="pull-right float-right">
																<i class="mdi mdi-account-search text-danger"></i>
															</a>
														@endif
													@endif
												</header>
											</div>
											<div class="body" style="white-space: normal !important">
												{{ $doc->description ?? 'No description.' }}
											</div>
											@if(in_array($doc->request_type, array("Goods Receipt", "Goods Return")))
												<div class="body" style="white-space: normal !important">
													<?php
														$docTypes = ["GR - Invoice", "GR - Delivery Note"];
														if($doc->request_type == "Goods Return"){
															$docTypes[] = "GR - Credit Note";
														}
													?>
													<div class="row">
														@foreach($docTypes as $d)
															<div class="col-9 text-left">
																<i class="mdi mdi-menu-right-outline"></i> {{ $d }}
															</div>
															<div class="col-3">
																<?php
																	$attachment = \App\EntityAttachment::where('model', $doc->request_type)->where('type', $d)
																		->where('model_id', $doc->id)->first();
																?>
																{!! $attachment ? '<i class="text-success mdi mdi-check-circle"></i>' : '<i class="text-muted mdi mdi-close-circle-outline"></i>' !!}
															</div>
														@endforeach
													</div>
												</div>
											@endif
											<div class="footer row no-gutters">
												<div class="col-6">
													<?php $parentReq = \App\RequestEntity::find($doc->parent_request_id) ?>
													@if (isset($parentReq->id))
														<a href="{{ route('view-request-details', ['stage'=>$doc->parent_request, 'id'=>$doc->parent_request_id]) }}">
															<i class="mdi mdi-clipboard-arrow-right"></i> Source - {{ $parentReq->request_code }}
														</a>
													@else
														<span class="text-muted"><i class="mdi mdi-clipboard-arrow-right"></i> Source - </span>
													@endif
												</div>
												<div class="col-6">
													<a href="{{ route('view-request-details', ['stage'=>$stName, 'id'=>$doc->id]) }}">View</a>
												</div>
											</div>
										</div>
									@endforeach
									@if(count($docs) == 0)
										<span class="text-center text-muted" style="text-align:center">
											<i class="fas fa-info-circle"></i> Not yet at this stage.
										</span>
									@endif
									</span>
							@endforeach
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Items" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Items
							@if (isset($request->status) && ($request->status == "In Preparation" || !isset($request->status) ))
								@if(in_array($stage, ["Material Requisition", "Request to Store", "Request for Quotation"]))
									<span class="btn btn-default text-primary btn-sm float-right add-item-row"><i class="mdi mdi-plus"></i> Add</span>
									<span class="btn btn-default text-danger btn-sm float-right delete-item-row mr-2"><i class="mdi mdi-delete"></i> Remove</span>
								@endif
							@endif
							@if ($request->request_type == "Purchase Orders" && Auth::user()->hasRole($procurement_officer_role_id, true))
								@if (in_array($request->status, array("Approval Complete", "Purchase Order Sent")))
									<span class="btn btn-default text-dark float-right" data-target="#Make-Amendment-Modal"
										data-toggle="modal">
										<i class="mdi mdi-file-move"></i> Make Amendment
									</span>
								@endif
							@endif
						</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th>Cat/Lot No</th>
										<th nowrap>Item</th>
										<th nowrap>Brand</th>
										<th nowrap>Comments</th>
										@if(in_array($stage, ['Request to Store', 'Material Issuance']))
											<th nowrap>Issuing UoM</th>
										@else
											<th nowrap>UoM</th>
										@endif
										@if(in_array($stage, array("Request to Store", "Material Requisition", "Request for Quotation")))
											<th nowrap>Open Quantity</th>
										@endif

										@if($stage == "Goods Receipt")
											<th nowrap>Received Quantity</th>
											<th nowrap>Expiry</th>
											<th nowrap>Date of Manufacture</th>
											<th nowrap>Lot No</th>
										@else
											@if($stage == "Goods Return")
												<th nowrap>Quantity</th>
											@else
												<th nowrap>Requested Quantity</th>
											@endif
										@endif
										@if(in_array($stage,["Request to Store", "Purchase Orders"]))
											<th nowrap>Pending Quantity</th>
											<th nowrap>{{ $stage == "Request to Store" ? "Issued" : "Received " }} Quantity</th>
											@if($stage == "Purchase Orders")
												<th nowrap>Returned Qty</th>
											@endif
										@endif
										@if(in_array($stage, array("Request to Store", "Material Issuance")))
											<th nowrap>Lot No</th>
											<th nowrap>Starting Sample</th>
										@endif
										@if($stage == "Material Requisition")
											<th nowrap>Delivery Date</th>
										@endif
										<th nowrap>Store</th>
										<th nowrap>Slot</th>
										<th nowrap>Net Value</th>
										@if($stage == "Goods Receipt")
											<th nowrap>Inspection Test</th>
											<th nowrap>Inspection Remarks</th>
										@endif
									</tr>
								</thead>
								<tbody id="req-items">
									<?php $itemsToName = array(); ?>
									@foreach ($normalItems as $req_item)
										<?php $pendingQ = $req_item->quantity - $req_item->pending(); ?>
										<tr class="item-row">
											<td class="item-id">
												{{ $loop->iteration }}
												<input type="hidden" name="items[req_item_id][]" value="{{ $req_item->id }}" />
											</td>
											<td>{{$req_item->sap_code}}</td>
											<td>
												<div class="form-group">
													<?php
														$readonly = isset($request->status) && $request->status == "In Preparation" || !isset($request->status) ? false : true;
													?>
													<select name="items[item_id][]" style="min-width: 200px; font-size: 12px" class="form-control selected-item" data-selected="{{ $req_item->inventory_sub_category_id }}" placeholder="Please select inventory item..." {{ $readonly ? "disabled" : "" }} >
														<option value="{{ $req_item->inventory_sub_category_id }}" selected="selected">{{ $req_item->item_name }}</option>
													</select>
												</div>
											</td>
											<td>
												<div class="form-group">
													<select name="items[item_brand_id][]" class="form-control selected-item-brand" data-selected="{{ $req_item->item_brand_id }}" placeholder="Select Item Brand..."  {{ $readonly && $request->in_ammendment == 0  ? "disabled" : "" }}></select>
												</div>
											</td>
											<td nowrap>
												<div class="form-group">
													<textarea class="form-control item-description" name="items[comments][]" value="" style="min-width: 250px"  {{ $readonly ? "readonly" : "" }}>{{ $req_item->comments }}</textarea>
												</div>
											</td>
											@if(in_array($stage, ['Request to Store', 'Material Issuance']))
												<td nowrap>
													<div class="form-group">
														{{-- {!! "<pre>".json_encode($req_item, JSON_PRETTY_PRINT)."</pre>" !!} --}}
														<?php
															$existsCon = getUoMConverstion(1, $req_item->unit_type, $req_item->secondary_unit_type);
														?>
														<?php
															$itemUoM = [$req_item->unit_type, $req_item->secondary_unit_type];
															if($existsCon == false){
																$itemUoM = [$req_item->unit_type];
															}
														?>
														@if($existsCon == false)
															<small class="text-danger"><sup>*UoM conversions not configured</sup></small>
														@endif
														<select name="items[uom][]" class="form-control selected-item-uom" data-selected="{{ $req_item->uom }}" placeholder="Select Item UoM..."  {{ $readonly && $request->in_ammendment == 0  ? "disabled" : "" }}>
															<option value=""></option>
															@foreach ($itemUoM as $uom)
																<option value="{{ $uom }}" {{ $uom == $req_item->uom ? 'selected' : '' }}>{{ $uom }}</option>
															@endforeach
														</select>
													</div>
												</td>
											@else
											<td>
												<div class="form-group">
													<?php $itemUoM = [$req_item->unit_type]; ?>
													<select name="items[uom][]" class="form-control selected-item-uom" data-selected="{{ $req_item->uom }}" placeholder="Select Item UoM..."  {{ $readonly && $request->in_ammendment == 0  ? "disabled" : "" }}>
														<option value=""></option>
														@foreach ($itemUoM as $uom)
															<option value="{{ $uom }}" {{ $uom == $req_item->uom ? 'selected' :'' }}>{{ $uom }}</option>
														@endforeach
													</select>
												</div>
											</td>
											@endif
											@if(in_array($stage, array("Request to Store", "Material Requisition", "Request for Quotation")))
											<td nowrap>
												<div class="form-group">
													<span class="form-control open-quantity" style="min-width: 150px">0.00</span>
													<input type="hidden" min="0.00" name="items[open_quantity][]" style="min-width: 100px" class="form-control open_quantity" placeholder="Open Quantity..." />
												</div>
											</td>
											@endif

											@if($stage == "Goods Receipt")
												<td>
													<div class="form-group">
														<input type="number" min="0.00" name="items[received_quantity][]" value="{{ $req_item->quantity }}" style="min-width: 100px" class="form-control received-user-quantity" placeholder="Received Quantity..." readonly="true" />
													</div>
												</td>
												<td>
													<div class="form-group">
														<input type="date" name="items[expiry][]" value="{{ $req_item->gr_expiry }}"  style="min-width: 100px" class="form-control expiry-user-quantity" placeholder="Expiry Date..." {!! in_array($request->status,array("Approval Complete", "Partially Fulfilled")) ? '' : 'readonly="true"'  !!} />
													</div>
												</td>
												<td>
													<div class="form-group">
														<input type="date" name="items[date_of_manufacture][]" value="{{ $req_item->date_of_manufacture }}" style="min-width: 100px" class="form-control date-of-manufacture" placeholder="Date of Manufacture..." {!! in_array($request->status,array("Approval Complete", "Partially Fulfilled")) ? '' : 'readonly="true"'  !!} />
													</div>
												</td>
												<td>
													<div class="form-group">
														<input type="text" name="items[lot_no][]" value="{{ $req_item->lot_no }}" style="min-width: 100px" class="form-control user-lot-no" placeholder="Lot Number..." {!! in_array($request->status,array("Approval Complete", "Partially Fulfilled")) ? '' : 'readonly="true"'  !!} />
													</div>
												</td>
											@else
												<td>
													<div class="form-group">
														<input type="number" min="0.00" name="items[quantity][]" value="{{ $req_item->quantity }}" style="min-width: 100px" class="form-control user-quantity" placeholder="Quantity..." {!! isset($request->status) && in_array($request->status,array("In Preparation", "Awaiting Approval", "Partially Fulfilled")) || !isset($request->status) ? '' : 'readonly="true"' !!} {!! $stage == "Material Issuance" ? 'readonly="true"' : '' !!} />
													</div>
												</td>
											@endif
											@if(in_array($stage, array("Request to Store", "Purchase Orders")))
												<td>
													<div class="form-group">
														<input type="number" min="0.00" name="items[pending_quantity][]" value="{{ $pendingQ }}" style="min-width: 100px" class="form-control pending-user-quantity" placeholder="Pending Quantity..." readonly />
													</div>
												</td>
												@if($stage == "Purchase Orders")
													<td>
														<div class="form-group">
															<input type="number" min="0.00" name="items[issued_quantity][]" value="{{ $req_item->issued_received_breakdown()['Goods Receipt'] ?? 0 }}" style="min-width: 100px" class="form-control issued-user-quantity" placeholder="Issued Quantity..." readonly />
														</div>
													</td>
													<td>
														<div class="form-group">
															<input type="number" min="0.00" name="items[issued_quantity][]" value="{{ $req_item->issued_received_breakdown()['Goods Return'] ?? 0 }}" style="min-width: 100px" class="form-control issued-user-quantity" placeholder="Issued Quantity..." readonly />
														</div>
													</td>
												@else
													<td>
														<div class="form-group">
															<input type="number" min="0.00" name="items[issued_quantity][]" value="{{ $req_item->pending() }}" style="min-width: 100px" class="form-control issued-user-quantity" placeholder="Issued Quantity..." readonly />
														</div>
													</td>
												@endif
											@endif
											@if(in_array($stage, array("Request to Store", "Material Issuance")))
												<td>
													<div class="form-group">
														<input type="text"  name="items[lot_no][]" value="{{ $req_item->lot_no }}" style="min-width: 100px" class="form-control user-lot-no" placeholder="Lot Number..." {{ $readonly ? "disabled" : "" }} />
													</div>
												</td>
												<td>
													<div class="form-group">
														<select name="items[starting_sample][]" class="form-control starting-sample" data-selected="{{ $req_item->starting_sample }}" placeholder="Starting Sample..."  {{ $readonly ? "disabled" : "" }}>
															<option value=""></option>
															@foreach (getSamplesByWorkflow("Samples In Lab") as $sample)
																<option value="{{ $sample->id }}" {{ $sample->id == $req_item->starting_sample ? 'selected' :'' }}>{{ $sample->sample_code }}</option>
															@endforeach
														</select>
													</div>
												</td>
											@endif
											@if($stage == "Material Requisition")
												<td nowrap>
													<div class="form-group">
														<span class="form-control" style="min-width: 100px">{{ ($request->created_at ?? \Carbon\Carbon::now())->addDays(7) }}</span>
														<input type="hidden" name="delivery_date" value="{{ ($request->created_at ?? \Carbon\Carbon::now())->addDays(7) }}" />
													</div>
												</td>
											@endif
											<td>
												<div style="min-width: 150px" class="form-group">
													<select name="items[store_id][]" data-selected="{{ $req_item->store_id }}" class="form-control selected-store" placeholder="Select Store..." {!! (isset($request->status) && in_array($request->status, array("In Preparation", "Awaiting Approval", "Approval Complete", "Partially Fulfilled", "Awaiting User Reception"))) || !isset($request->status)  ? '' : 'disabled="true"' !!} {{ $stage == "Goods Receipt" && $request->status != "Awaiting Approval" ? "required" : "" }}>
														<option></option>
														@foreach (getUserStores() as $store)
															<option value="{{ $store->id }}"
																{{ $store->id == $req_item->store_id ? 'selected' : '' }}
																data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
														@endforeach
													</select>
												</div>
											</td>
											<td>
												<div style="min-width: 150px" class="form-group">
													<select style="min-width: 150px" name="items[slot_id][]" data-slot="{{ $req_item->slot_id }}" style="min-width: 100px" class="form-control store-slots" placeholder="Select Slot..." {!! (isset($request->status) && in_array($request->status, array("In Preparation", "Awaiting Approval", "Approval Complete", "Partially Fulfilled", "Awaiting User Reception"))) || !isset($request->status)  ? '' : 'disabled="true"' !!} {{ $stage == "Goods Receipt" && $request->status != "Awaiting Approval" ? "required" : "" }}></select>
												</div>
											</td>
											<td>
												<div class="form-group">
													<input type="number" min="0.00" name="items[net_value][]" style="min-width: 100px" value="{{ $req_item->net_value }}" class="form-control items-total" {!! isset($request->status) && $request->status == "In Preparation" || !isset($request->status) ? '' : 'disabled="true"' !!} readonly />
												</div>
											</td>
											@if($stage == "Goods Receipt")
												<td>
													<div class="form-group">
														<input type="text" name="items[test][]" style="min-width: 100px" value="{{ $req_item->test ?? '' }}" class="form-control trigger-save" {!! isset($request->status) && in_array($request->status, ["In Preparation", "Awaiting Approval"])|| !isset($request->status) ? '' : 'disabled="true"' !!} />
													</div>
												</td>
												<td>
													<div class="form-group">
														<input type="text" name="items[remarks][]" style="min-width: 100px" value="{{ $req_item->remarks ?? '' }}" class="form-control trigger-save" {!! isset($request->status) && in_array($request->status, ["In Preparation", "Awaiting Approval"]) || !isset($request->status) ? '' : 'disabled="true"' !!} />
													</div>
												</td>
											@endif
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					@if (in_array($stage, array("Goods Receipt", "Material Issuance")))
					<div class="tab-pane fade p-3" id="Received-Items" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">
							{{ $stage == "Goods Receipt" ? 'Received' : 'Issued' }} Items
						</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Item Code</th>
										<th nowrap>Description</th>
										<th nowrap>Store</th>
										<th nowrap>Slot</th>
										<th nowrap>Quantity</th>
										<th nowrap>Issued By</th>
										<th nowrap>Date</th>
									</tr>
								</thead>
								<tbody id="received-items">
									@foreach ($request->issued_received() as $ir_item)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td>{{ $ir_item->code }}</td>
											<td>{{ $ir_item->item }}</td>
											<td>{{ $ir_item->store }}</td>
											<td>{{ $ir_item->slot }}</td>
											<td>{{ number_format($ir_item->quantity) }}</td>
											<td>{{ $ir_item->issuer }}</td>
											<td>{{ $ir_item->created_at }}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					@endif
					@if ($stage == "Request for Quotation")
						<div class="tab-pane fade p-3" id="Quotes" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3 mt-1">Quotes
								{{-- @if (isset($request->status) && $request->status == "In Preparation" || !isset($request->status))
									<span class="btn btn-default text-primary float-right" data-target="#add-note-modal" data-toggle="modal">
										<i class="mdi mdi-plus"></i> Note
									</span>
									<span class="btn btn-default text-danger float-right delete-this-row mr-2" data-holder="#note-items">
										<i class="mdi mdi-delete"></i> Remove
									</span>
								@endif --}}
								<a class="btn btn-default text-danger float-right mr-2"
									href="{{ route('trigger-system-reminders', ['send_reminder'=>1, 'type'=>$request->request_type, 'days'=>3, 'entity_id'=>$request->id]) }}">
									<i class="mdi mdi-bell-ring"></i> Send Reminders
								</a>
							</h5>
							<div class="row no-gutters">
								<div class="table-responsive">
									<table class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
										<thead>
											<tr>
												<th>#</th>
												<th nowrap>Supplier</th>
												<th nowrap>Item</th>
												<th nowrap>Brand</th>
												<th nowrap>Price</th>
												<th nowrap>Awarded?</th>
											</tr>
										</thead>
										<tbody id="quote-items">
											{{-- <tr>
												<td colspan="6">
													<pre>{{ json_encode($request->quotes(), JSON_PRETTY_PRINT) }}</pre>
												</td>
											</tr> --}}
											<?php $isMinimumPrice = array(); ?>
											@foreach ($request->quotes() as $quote)
												<tr style="{{ !isset($isMinimumPrice[$quote->request_item_id]) ? 'background-color: #addcad; font-weight: 600' :'' }}">
													<td>{{ $loop->iteration }}</td>
													<td>{{ $quote->supplier->name ?? 'missing-supplier-details'}}</td>
													<td>{{ $quote->item_name  }}</td>
													<td>{{ $quote->brand  }}</td>
													<td><span class="default-currency"></span> {{ number_format($quote->quote_amount, 2) }}</td>
													<td>
														@if(trim($quote->awarded_at) == "")
															<span class="btn btn-sm btn-success"
																data-item="{{ $quote }}"
																data-target="#award-rfq-to-user" data-toggle="modal">
																<i class="mdi mdi-account-check"></i>
																Award Supplier
															</span>
															<span class="btn btn-sm btn-primary"
																data-quote="{{ $quote }}" data-item="{{ $quote->item_name }}"
																data-target="#edit-supplier-quote" data-toggle="modal">
																<i class="mdi mdi-pencil"></i>
																Edit Quote
															</span>
															<span class="btn btn-sm btn-danger"
																data-quote="{{ $quote }}" data-item="{{ $quote->item_name }}"
																data-target="#delete-supplier-quote" data-toggle="modal">
																<i class="mdi mdi-delete"></i>
																Remove Quote
															</span>
														@else
															@if($quote->is_awarded == 1)
																<i class="mdi mdi-check-bold text-green"></i> {{ $quote->awarded_at }}
																<span class="btn btn-transparent btn-sm" data-quote="{{ $quote }}" data-target="#undo-supplier-award" data-toggle="modal">
																	<i class="mdi mdi-backup-restore text-danger" data-toggle="tooltip"  data-placement="left" title="Undo Supplier Award"></i>
																</span>
															@else
																<i class="mdi mdi-cancel text-muted"></i>
															@endif
														@endif
													</td>
												</tr>
												<?php $isMinimumPrice[$quote->request_item_id] = true; ?>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Suppliers" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3 mt-1">Suppliers
								@if (isset($request->status) && $request->status == "In Preparation" || !isset($request->status))
									@if(\Auth::user()->hasRole($procurement_officer_role_id, true))
										<span class="btn btn-default text-primary float-right add-supplier-row">
											<i class="mdi mdi-plus"></i> Supplier
										</span>
									@endif
								@endif
							</h5>
							<div class="row no-gutters">
								<div class="table-responsive">
									<table class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
										<thead>
											<tr>
												<th>#</th>
												<th nowrap>Name</th>
												<th nowrap>Rating</th>
												<th nowrap>Email</th>
												<th nowrap>Phone</th>
												<th nowrap>RFQ Sent</th>
												<th nowrap>Quote Received</th>
											</tr>
										</thead>
										<tbody id="supplier-list"  data-suppliers='{{ json_encode($request->supplier_rfqs()) }}'>
											<tr class="no-data">
												<td colspan="7">
													<div class="alert alert-info">
														<i class="fas fa-exclamation-triangle"></i> No suppliers selected yet.
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					@endif
					<div class="tab-pane fade p-3" id="Approvals" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3 mt-1">Required Approvals <small class="badge badge-secondary">{{ $approvals->count() }}</small></h5>
						<div class="row no-gutters">
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-banded table-striped table-hover table-bordered table-sm">
									<thead>
										<tr>
											<th>#</th>
											<th nowrap>Approval</th>
											<th nowrap>Created At</th>
											<th nowrap>Approved By</th>
											<th nowrap>Approved At</th>
											<th nowrap>Status</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$isEntityAboveApproved = true;
											$hasApprovedBefore = [];
										?>
										@foreach ($approvals as $app)
											<?php
												$entityApproval =  $app->entity_approval($stage, $request->id);

												// echo json_encode($entityApproval);

												$enableCurrentForApproval = $isEntityAboveApproved;
												//Set $isEntityAboveApproved for the next approval step
												$isEntityAboveApproved = trim($entityApproval->approved_at ?? "") != "";
											?>
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td>{{ $app->title }}</td>
												<td>{{ $entityApproval->created_at ?? '-' }}</td>
												<td nowrap>
													@if($entityApproval && intval($entityApproval->user_id) > 0)
														{{ $entityApproval->user()->name }}
													@else
														<span class="text-muted">-</span>
													@endif
													<?php
														$users = \App\Role::find($app->role_id)->getUsersByRole()->pluck('id', 'name');
													?>
													@if (isset($request->status) && ($request->status == 'Awaiting Approval' || $request->status == 'Partially Approved'))
														@if(isset($app->is_pending($stage, isset($request->id) ? $request->id : 0)->id))
															<span class="ml-2 btn btn-transparent btn-sm text-primary"
																data-approval="{{ json_encode($entityApproval->id) }}" data-users="{{ json_encode($users) }}"
																data-toggle="modal" data-title="{{ $app->title }}" data-target="#change-approver-modal">
																<i class="mdi mdi-sync"></i> Change
															</span>
														@endif
													@endif
												</td>
												<td>{{ $entityApproval->approved_at ?? '-' }}</td>
												<td nowrap>
													@if (isset($request->status) && ($request->status == 'Awaiting Approval' || $request->status == 'Partially Approved'))
														@if(isset($app->is_pending($stage, isset($request->id) ? $request->id : 0)->id))
															{{-- @if(true) --}}
															@if($enableCurrentForApproval && ((isset($entityApproval->created_at)) && ($entityApproval->user_id == Auth::user()->id)) && !in_array(Auth::user()->id, $hasApprovedBefore))
																<span class="btn btn-sm btn-outline-success mr-2"
																	data-approval="{{ json_encode($entityApproval->id) }}" role="button"
																	data-toggle="modal" data-target="#confirm-accept-modal">
																	<i class="mdi mdi-check-bold"></i> Approve
																</span>
																<span class="btn btn-sm btn-outline-danger"
																	data-approval="{{ json_encode($entityApproval->id) }}" role="button"
																	data-toggle="modal" data-target="#enter-reject-modal">
																	<i class="mdi mdi-cancel"></i> Reject
																</span>
															@else
																<span class="btn btn-sm mr-2 btn-disabled text-muted">
																	<i class="mdi mdi-check-bold"></i> Approve
																</span>
																<span class="btn btn-sm btn-disabled text-muted">
																	<i class="mdi mdi-cancel"></i> Reject
																</span>
															@endif
															@if(!($entityApproval && intval($entityApproval->user_id) > 0))
																<?php
																	$users = \App\Role::find($app->role_id)->getUsersByRole()->pluck('id', 'name');
																?>
																<span class="ml-2 btn btn-transparent btn-sm text-primary trigger-change-approver"
																	data-approval="{{ json_encode($entityApproval->id) }}" data-users="{{ json_encode($users) }}"
																	data-toggle="modal" data-title="{{ $app->title }}" data-target="#change-approver-modal"></span>
															@endif
															<small class="text-danger">{{ trim(Auth::user()->electronic_sig) == "" ? '**Please update your signature' : '' }}</small>
														@else
															<span class="text-info">{{ $app->entity_approval($stage, $request->id ?? 0)->status ?? '' }}</span>
														@endif
													@else
														{{ $app->entity_approval($stage, $request->id ?? 0)->status ?? 'Not Sent' }}
													@endif
												</td>
											</tr>
											<?php
												if($entityApproval && intval($entityApproval->user_id) > 0){
													$hasApprovedBefore[] = $entityApproval->user_id;
												}
											?>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="tab-pane fade show active p-3" id="General" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3 mt-1">General Information</h5>
						<div class="row no-gutters">
							<div class="col-sm-6 col-md-4">
								<div class="form-group">
									<label class="control-label">Description</label>
									<textarea class="form-control" name="description" placeholder="{{ $stage }} Description...">{{ $request->description }}</textarea>
								</div>
								<div class="basic-info {{ $request->request_type == 'Purchase Orders' ? 'hidden hide' : '' }}">
									<div class="form-group">
										<label class="control-label">Company Unit</label>
										<div class="form-control">
											<span class="">{{ \Auth::user()->location()->name }}</span>
											<input type="hidden" name="company_unit" value="{{ \Auth::user()->location_id }}" />
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Department/Cost Center</label>
										<div class="form-control">
											<?php $theUser = \App\User::find($request->request_initiator) ?? \Auth::user(); ?>
											<span class="">{{ $theUser->department()->name }}</span>
											<input type="hidden" name="department" value="{{ $theUser->department_id }}" />
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Nature of Purchase*</label>
										<select name="nature_of_purchase" class="form-control" placeholder="Select Nature of Purchase..." required>
											<option></option>
											@foreach (getNatureOfExpense() as $np)
												<option value="{{ $np }}" {{ $np == ($request->nature_of_purchase ?? '') ? 'selected' : '' }}>{{ $np }}</option>
											@endforeach
										</select>
									</div>
								</div>
								@if (in_array($request->request_type, ["Purchase Orders", "Goods Receipt", "Goods Return"]))
									<?php $supplier = \App\Supplier::find($request->supplier_id) ?>
									<div class="form-group">
										<div class="row">
											<div class="col-3">
												<img src="{{ $supplier->logo }}" style="width: 100%" />
											</div>
											<div class="col-9">
												<label class="control-label">Supplier</label>
												<div class="form-control">
													<span class="">{{ $supplier->name }}</span>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Supplier Email</label>
										<div class="form-control">
											<span class="">{{ $supplier->email }}</span>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Supplier Phone</label>
										<div class="form-control">
											<span class="">{{ $supplier->phone }}</span>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Due Date</label>
										<input type="date" name="valid_until" value="{{ $request->due_date ?? '' }}" class="form-control trigger-save" placeholder="Due Date..." />
									</div>

								@endif
								@if(in_array($request->request_type, ["Request for Quotation", "Purchase Orders"]))
									<div class="form-group">
										<label class="control-label">Currency*</label>
										<select name="currency" class="form-control {{ $stage == "Purchase Orders" ? "trigger-save" : "" }}" placeholder="Select Currency..." required>
											<option></option>
											@foreach (getCurrencies() as $p)
												<option value="{{ $p->id }}" {{ $p->id == ($request->currency ?? '') ? 'selected' : '' }}>{{ $p->name }}</option>
											@endforeach
										</select>
									</div>
									@if($stage == "Request for Quotation")
									<div class="form-group">
										<label class="control-label">Submission Deadline</label>
										<input type="datetime-local" name="submission_deadline" value="{{ $request->submission_deadline ?? '' }}" class="form-control trigger-save" placeholder="Submission Deadline..." />
									</div>
									@endif
									<div class="form-group">
										<label class="control-label">{{ $stage == "Purchase Orders" ? "Delivery Date" : "Valid Until" }}</label>
										<input type="date" name="valid_until trigger-save" value="{{ $request->due_date ?? '' }}" class="form-control" placeholder="Validity Period..." />
									</div>
									<div class="form-group">
										<label class="control-label">Request Value</label>
										@if($stage == "Purchase Orders")
											<input type="number" step="any" class="form-control trigger-save" name="net_value" value="{{ $request->net_value ?? '0.00' }}" />
										@else
											<div class="form-control">
												<span class="">{{ $request->net_value ?? '0.00' }}</span>
												<input type="hidden" name="net_value" value="{{ $request->net_value ?? '0.00' }}" />
											</div>
										@endif
									</div>
								@endif
							</div>
							<div class="col-md-4"></div>
							<div class="col-sm-6 col-md-4">
								@if($request->request_type == "Goods Return")
									<div class="form-group">
										<label class="control-label">Gate Pass</label>
										<input type="text" name="gate_pass" value="{{ $request->gate_pass ?? '' }}" class="form-control trigger-save" placeholder="Gate Pass..." />
									</div>
									<div class="form-group">
										<label class="control-label">Time Out</label>
										<input type="time" name="time_out" value="{{ $request->time_out ?? '' }}" class="form-control trigger-save" placeholder="Time Out..." />
									</div>
									<div class="form-group">
										<label class="control-label">Vehicle Number</label>
										<input type="text" name="vehicle_no" value="{{ $request->vehicle_no ?? '' }}" class="form-control trigger-save" placeholder="Vehicle Number..." />
									</div>
								@endif
								<div class="form-group">
									<label class="control-label">Requested By</label>
									<div class="form-control">
										<?php $RequestedBy = \App\User::find($request->request_initiator); ?>
										<span class="">{{ $RequestedBy ? $RequestedBy->name : \Auth::user()->name }}</span>
										<input type="hidden" name="requested_by" value="{{ $request->request_initiator ?? \Auth::user()->id }}" />
									</div>
								</div>
								@if($stage == "Material Issuance")
									<div class="form-group">
										<label class="control-label">Issue To</label>
										<select class="form-control trigger-save" name="issue_to" placeholder="Issue To...">
											<option></option>
											@foreach (getUsers() as $user)
												<option value="{{ $user->id }}" {{ $user->id == $request->issue_to ? 'selected' : '' }}>{{ $user->name }}</option>
											@endforeach
										</select>
									</div>
								@endif
								<div class="form-group">
									<label class="control-label">Created On</label>
									<div class="form-control">
										<span class="">{{ $request->created_at ?? '' }}</span>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">Created By</label>
									<div class="form-control">
										<?php $CreatedBy = \App\User::find($request->created_by); ?>
										<span class="">{{ $CreatedBy ? $CreatedBy->name : \Auth::user()->name }}</span>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">Updated On</label>
									<div class="form-control">
										<span class="">{{ $request->updated_at ?? '' }}</span>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">Priority*</label>
									<select name="priority" class="form-control" placeholder="Select Priority..." required>
										<option></option>
										@foreach (getRequestPriority() as $p)
											<option value="{{ $p }}" {{ $p == ($request->priority ?? '') ? 'selected' : '' }}>{{ $p }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">Status</label>
									<div class="form-control">
										<span class="">{{ $request->status ?? 'In Preparation' }}</span>
										<input type="hidden" name="status" value="{{ $request->status ?? 'In Preparation' }}" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
  </main>
@endsection

@section('script2')
  <div id="add-inventory-category" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-category') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Inventory Category</h4>
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
            <input type="file" class="form-control" name="image" required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="add-approval-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-approval-to-stage', ['stage'=>$stage]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add New Approval</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Approval Title</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
						<input type="hidden" name="for" value="Requisition" />
          </div>
          <div class="form-group">
						<label class="control-label">Select Role</label>
						<select name="role_id" class="form-control" placeholder="Select Approval User..." required>
							<option></option>
							@foreach (getRoles() as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
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
  <div id="add-attachment-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Attachment</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Title</label>
						<input type="text" class="form-control" name="title" value="" placeholder="Title..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Type</label>
						<select class="form-control" name="type" placeholder="Type..." required>
							<option></option>
							@foreach (getAttachmentTypes() as $item)
								<option value="{{ $item }}">{{ $item }}</option>
							@endforeach
						</select>
          </div>
          <div class="form-group">
						<label class="control-label">Description</label>
						<textarea class="form-control" name="description" placeholder="File Description..."></textarea>
					</div>
          <div class="form-group">
            <label class="control-label">File</label>
						<input type="file" class="form-control" name="attachments[file][]" style="overflow: hidden" required />
          </div>
					<input type="hidden" name="current_user" value="{{ \Auth::user()->id }}" />
					<input type="hidden" name="current_user_email" value="{{ \Auth::user()->email }}" />
					<input type="hidden" name="current_user_name" value="{{ \Auth::user()->name }}" />
					<input type="hidden" name="current_time" value="{{ \Carbon\Carbon::now() }}" />
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary modal-add-data trigger-save" data-type="attachment"><i class="mdi mdi-plus"></i> Add</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="add-note-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Note</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Type</label>
						<select class="form-control" name="type" placeholder="Type..." required>
							<option></option>
							@foreach (getNoteTypes() as $item)
								<option value="{{ $item }}">{{ $item }}</option>
							@endforeach
						</select>
          </div>
          <div class="form-group">
						<label class="control-label">Description</label>
						<textarea class="form-control" name="description" placeholder="File Description..."></textarea>
					</div>
					<input type="hidden" name="current_user" value="{{ \Auth::user()->id }}" />
					<input type="hidden" name="current_user_email" value="{{ \Auth::user()->email }}" />
					<input type="hidden" name="current_user_name" value="{{ \Auth::user()->name }}" />
					<input type="hidden" name="current_time" value="{{ \Carbon\Carbon::now() }}" />
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary modal-add-data trigger-save" data-type="note"><i class="mdi mdi-plus"></i> Add</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="view-note-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-card-text"></i> Description</h5>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <div id="view-note-description"></div>
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="enter-reject-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-cancel"></i> Rejection Reason</h5>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Enter Reason</label>
						<textarea class="form-control" id="reject-reason-text" placeholder="Reason..."></textarea>
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger save-details-form" data-type="reject-with-reason">Reject</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="return-goods-to-supplier-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Goods Return Actions</h5>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">
							<input type="radio" name="return_action" class="goods-return-action" value="resupply" checked /> The supplier will resupply the returned items.
						</label>
					</div>
          <div class="form-group">
						<label class="control-label">
							<input type="radio" name="return_action" class="goods-return-action" value="credit_note" /> The supplier has provided a credit note for the items.
						</label>
					</div>
          <div class="form-group uploadable hide">
						<label class="control-label">Attach Credit Note</label>
						<input type="file" name="credit_note"  class="form-control goods-return-credit-note-file" />
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger save-details-form" data-type="return-goods-to-supplier">Proceed</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="Make-Amendment-Modal"" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-cancel"></i> Confirm RFQ Amendment</h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning">
						<i class="mdi mdi-information"></i>
						Are you sure that you want to make an ammendment to the items? Please note that proceeding with this step will require update of the parent RFQ. Proceed?
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger save-details-form" data-type="make-an-ammendment">Yes, Proceed</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
	<div id="undo-supplier-award" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-backup-restore"></i> Undo Supplier Awarding</h5>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-danger">
						<i class="fas fa-info-circle fa-2x"></i> Are you sure you want to undo supplier awarding?
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger">Yes, Undo</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
			</form>
		</div>
	</div>
  <div id="award-rfq-to-user" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-account-check"></i> Award Supplier</h5>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Enter Reason</label>
						<textarea class="form-control" id="award-reason-text" placeholder="Reason..."></textarea>
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success save-details-form" data-type="award-with-reason">Award</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
	@if($stage == "Purchase Orders")
  <div id="Create-Goods-Receipt-Modal" class="modal fade" role="dialog">
    <form class="modal-dialog modal-lg" action="" method="POST" enctype="multipart/form-data">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-plus"></i> Create Goods Receipt</h5>
        </div>
        <div class="modal-body">
					<h6><i class="mdi mdi-package-variant-closed"></i> Receivable Items</h6>
					<div class="table-responsive">
						<table class="table table-condensed table-striped table-sm table-bordered">
							<thead>
								<th>Item</th>
								<th>Requested Quantity</th>
								<th>Pending Quantity</th>
								<th>Receiving Quantity</th>
								<th>Expiry</th>
								<th>Test</th>
								<th>Remarks</th>
							</thead>
							<tbody>
								@foreach ($normalItems ?? array() as $req_item)
									<?php $pendingQ = $req_item->quantity - $req_item->pending(); ?>
									<tr class="for-item-{{ $req_item->inventory_sub_category_id }} quote-row">
										<input type="hidden" name="item[]" value="{{ $req_item->id }}" />
										<td nowrap>{{ $req_item->item_name }}</td>
										<td class="text-right">{{ number_format($req_item->quantity) }}</td>
										<td class="text-right">{{ number_format($pendingQ) }}</td>
										<td>
											<input type="number" value="0" step="any" max="{{ $pendingQ }}" class="form-control form-control-sm" name="received[]" />
										</td>
										<td>
											<input type="date" class="form-control form-control-sm" name="expiry[]" />
										</td>
										<td>
											<input type="text" class="form-control form-control-sm" name="text[]" style="min-width: 200px"/>
										</td>
										<td>
											<input type="text" class="form-control form-control-sm" name="remarks[]" style="min-width: 200px"/>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<br><hr><br>
					<h6><i class="mdi mdi-paperclip"></i>Accompanying Documents</h6>
					<div class="form-group">
						<label>Invoice</label>
						<input type="file" name="invoice" class="form-control" />
					</div>
					<div class="form-group">
						<label>Delivery Note</label>
						<input type="file" name="delivery_note" class="form-control" />
					</div>
					<br><hr><br>
					<h6><i class="mdi mdi-email"></i>Message to Requester * Optional</h6>
					<div class="row pb-1">
						<div class="col-2">
							<img src="/images/user.png" style="width: 100%" />
						</div>
						<?php
							$REQUESTER = \App\User::find($request->request_initiator);
						?>
						<div class="col-10">
							<h5>{{ $REQUESTER->name ?? '-' }}</h5>
							<span style="padding: 0px 4px 2px 0px; font-size:15px"><i class="mdi mdi-email"></i> {{ $REQUESTER->email ?? '' }}</span><br>
							<span style="padding: 2px 4px 2px 0px; font-size:15px"><i class="mdi mdi-phone"></i> {{ $REQUESTER->phone ?? '' }}</span><br>
						</div>
					</div>
					<div class="form-group">
						<textarea placeholder="Message..." rows="8" name="message" class="form-control"></textarea>
					</div>
        </div>
        <div class="modal-footer">
					<input type="hidden" name="generate_goods_receipt" value="1" />
          <button type="submit" class="btn btn-success">Create</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
	</div>
  <div id="Create-Goods-Return-Modal" class="modal fade" role="dialog">
    <form class="modal-dialog modal-lg" action="" method="POST" enctype="multipart/form-data">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-clipboard-arrow-left"></i> Create Goods Return</h5>
        </div>
        <div class="modal-body">
					<h6><i class="mdi mdi-package-variant-closed"></i> Returnable Items</h6>
          <table class="table table-condensed table-striped table-sm table-bordered">
						<thead>
							<th>Item</th>
							<th>Requested Quantity</th>
							<th>Pending Quantity</th>
							<th>Receiving Quantity</th>
							<th>Expiry</th>
						</thead>
						<tbody>
							@foreach ($normalItems ?? array() as $req_item)
								<?php $pendingQ = $req_item->quantity - $req_item->pending(); ?>
								<tr class="for-item-{{ $req_item->inventory_sub_category_id }} quote-row">
									<input type="hidden" name="item[]" value="{{ $req_item->id }}" />
									<td nowrap>{{ $req_item->item_name }}</td>
									<td class="text-right">{{ number_format($req_item->quantity) }}</td>
									<td class="text-right">{{ number_format($pendingQ) }}</td>
									<td>
										<input type="number" value="0" step="any" max="{{ $pendingQ }}" class="form-control form-control-sm" name="received[]" required />
									</td>
									<td>
										<input type="date"  class="form-control form-control-sm" name="expiry[]" />
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					<br><hr><br>
					<h6><i class="mdi mdi-paperclip"></i>Accompanying Documents</h6>
					<div class="form-group">
						<label>Invoice</label>
						<input type="file" name="invoice" class="form-control" />
					</div>
					<div class="form-group">
						<label>Delivery Note</label>
						<input type="file" name="delivery_note" class="form-control" />
					</div>
					<br><hr><br>
					<h6><i class="mdi mdi-email"></i>Return Comments</h6>
					<div class="form-group">
						<textarea placeholder="Remarks..." rows="8" name="message" class="form-control"></textarea>
					</div>
        </div>
        <div class="modal-footer">
					<input type="hidden" name="create_goods_return" value="1" />
          <button type="submit" class="btn btn-success">Create</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
	</div>
	@endif
	@if($stage == "Request to Store")
  <div id="Create-Material-Issuance-Modal" class="modal fade" role="dialog">
    <form class="modal-dialog modal-lg" action="" method="POST" enctype="multipart/form-data">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-plus"></i> Create Material Issuance</h5>
        </div>
        <div class="modal-body">
					<h6><i class="mdi mdi-package-variant-closed"></i> Issuable Items</h6>
          <table class="table table-condensed table-striped table-sm table-bordered">
						<thead>
							<th>Item</th>
							<th>Requested Quantity</th>
							<th>Pending Quantity</th>
							<th>Issuing Quantity</th>
						</thead>
						<tbody>
							@foreach ($normalItems ?? array() as $req_item)
								<?php $pendingQ = $req_item->quantity - $req_item->pending(); ?>
								<tr class="for-item-{{ $req_item->inventory_sub_category_id }} quote-row">
									<input type="hidden" name="item[]" value="{{ $req_item->id }}" />
									<td nowrap>{{ $req_item->item_name }}</td>
									<td class="text-right">{{ number_format($req_item->quantity) }}{{ $req_item->uom }}</td>
									<td class="text-right">{{ number_format($pendingQ) }}{{ $req_item->uom }}</td>
									<td>
										<input type="number" value="0" step="any" max="{{ $pendingQ }}" class="form-control form-control-sm" name="issued[]" />
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					<hr>
					<h6><i class="mdi mdi-email"></i>Message to Requester * Optional</h6>
					<div class="row pb-1">
						<div class="col-2">
							<img src="/images/user.png" style="width: 100%" />
						</div>
						<?php
							$REQUESTER = \App\User::find($request->created_by);
						?>
						<div class="col-10">
							<h5>{{ $REQUESTER->name ?? '-' }}</h5>
							<span style="padding: 0px 4px 2px 0px; font-size:15px"><i class="mdi mdi-email"></i> {{ $REQUESTER->email ?? '' }}</span><br>
							<span style="padding: 2px 4px 2px 0px; font-size:15px"><i class="mdi mdi-phone"></i> {{ $REQUESTER->phone ?? '' }}</span><br>
						</div>
					</div>
					<div class="form-group">
						<textarea placeholder="Message..." rows="8" name="message" class="form-control"></textarea>
					</div>
        </div>
        <div class="modal-footer">
					<input type="hidden" name="generate_material_issuance" value="1" />
          <button type="submit" class="btn btn-success">Create</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
	</div>
	@endif
  <div id="confirm-accept-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-check-bold"></i> Confirm Approval</h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-callout alert-info text-lg">
						<i class="mdi mdi-information-circle"></i> Proceed with approval?
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success save-details-form" data-type="confirm-approval-reason">Yes, Approve</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="accept-goods-otp-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-numeric"></i> Confirmation OTP</h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-callout alert-info text-lg">
						<i class="mdi mdi-information fa-1x"></i> Please provide the confirmation OTP code:
					</div>
					<div class="form-group">
						<label>Requester OTP</label>
						<input type="text" name="requester_otp" class="form-control" placeholder="Requester OTP..." />
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success save-details-form" id="accept-goods-otp-modal-save-btn" data-type="accept-goods-receipt">Confirm</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="send-to-finance-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-file-account"></i> Send to Finance</h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-callout alert-info text-lg">
						<i class="mdi mdi-information fa-1x"></i> Send GRN to finance to process the invoice.
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success save-details-form" data-type="send-to-finance">Confirm</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="finance-department-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="/mark-gr-as-complete/{{$request->id}}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-file-account"></i> Mark GR as Complete</h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-callout alert-info text-lg">
						<i class="mdi mdi-help-circle fa-1x"></i> Are you sure that you want to continue with this action?.
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Yes, Continue</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
  <div id="create-lpo-from-mr-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="/create-lpo-from-mr/{{$request->id}}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-file-account"></i> Create Purchase Order</h5>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<div class="alert alert-info">
							<i class="fas fa-info-circle fa-2x"></i> Are you sure you want to create a purchase order straight from material requisition?
						</div>
					</div>
          <div class="form-group">
						<label class="control-label">Select Supplier</label>
						<select class="form-control" name="supplier_id" placeholder="Select Supplier...">
							@foreach ($normalItemsSuppliers as $sup)
								<option value="{{ $sup->id }}">{{ $sup->name }}</option>
							@endforeach
						</select>
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Yes, Create</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
  <div id="issue-items-otp-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-numeric"></i> Confirmation OTP</h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-callout alert-info text-lg">
						<i class="mdi mdi-information fa-1x"></i> Please provide the confirmation OTP code:
					</div>
					<div class="form-group">
						<label>Requester OTP</label>
						<input type="text" name="requester_otp" class="form-control" placeholder="Requester OTP..." />
					</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success save-details-form" id="issue-items-otp-modal-save-btn" data-type="issue-items">Confirm</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="quote-accept-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-cash-usd"></i> Supplier Quote</h5>
        </div>
        <div class="modal-body"> </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success save-details-form" data-type="accept-supplier-quote">Accept, Quote</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="edit-supplier-quote" class="modal fade" role="dialog">
    <form method="POST" class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-cash-usd"></i> </h5>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<label><strong>Quote Amount</strong></label>
						<input class="form-control" type="text" name="amount" value="" />
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update, Quote</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
	</div>
  <div id="delete-supplier-quote" class="modal fade" role="dialog">
    <form method="POST" class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-cash-usd"></i> </h5>
        </div>
        <div class="modal-body">
					<div class="alert alert-danger fa-2x">
						<i class="mdi mdi-delete"></i> Are you sure you want to remove this quote for <span class="quote-item"></span>
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Remove, Quote</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
	</div>
  <div id="reject-ammendment-modal" class="modal fade" role="dialog">
    <div method="POST" class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-cancel"></i> Reject/Cancel Amendments</h5>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<textarea name="rejection_message" class="form-control" placeholder="Please provide reason"></textarea>
					</div>
        </div>
        <div class="modal-footer">
          <button type="buttom" class="btn btn-danger save-details-form" data-type="cancel-ammendment">Reject</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="approve-ammendments-details-form" class="modal fade" role="dialog">
    <div method="POST" class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-check"></i> Approve Amendments</h5>
        </div>
        <div class="modal-body">
					<div class="alert alert-warning">
						<i class="mdi mdi-information"></i> Are you sure that you want to approve the amendments to the RFQ?
					</div>
        </div>
        <div class="modal-footer">
          <button type="buttom" class="btn btn-primary save-details-form" data-type="approve-ammendments-details">Yes, Approve Amendment</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
  <div id="change-approver-modal" class="modal fade" role="dialog">
    <div method="POST" class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-account-convert"></i> Change Approval</h5>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<label>Change approver to</label>
						<select name="user_id" class="form-control" placeholder="Select Approver"></select>
					</div>
        </div>
        <div class="modal-footer">
          <button type="buttom" class="btn btn-primary">Change</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
	@if(in_array($stage, ["Material Requisition", "Request for Quotation", "Purchase Orders", "Request to Store", "Material Issuance"]))
		<div id="jump-to-status-modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form method="POST" action="{{ route('jump-request-to-status', ['id'=>$request->id ?? 0]) }}" class="modal-content">
					@csrf
					<div class="modal-header">
						<h5 class="modal-title"><i class="mdi mdi-account-check"></i> Change status for {{ $stage }}</h5>
					</div>
					<div class="modal-body">
						<ul class="list-group list-group-flush">
							<li class="list-group-item"><label class="control-label"><input name="status" value="In Preparation" type="radio" /> In Preparation</label></li>
							@if(($request->done_approvals()->count() == $request->defined_approvals()->count()) || in_array($stage,["Request to Store", "Material Issuance"]))
								<li class="list-group-item"><label class="control-label"><input name="status" value="Approval Complete" type="radio" /> Approval Complete</label></li>
							@endif
							@if($stage=="Request for Quotation")
								<li class="list-group-item"><label class="control-label"><input name="status" value="Awarded" type="radio" /> Awarded</label></li>
								<li class="list-group-item"><label class="control-label"><input name="status" value="RFQs sent out" type="radio" /> RFQs sent out</label></li>
							@endif
						</ul>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">Change Status</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	@endif
	<div id="data-attr-holder" class="hidden" data-stores = "{{ json_encode(getUserStores()) }}" ></div>
	<script>
		var markCompleted = $(`<input type="hidden" name="get_approval" value="1" />`);
		var getQuoteAcceptModalBody = function(){
			var $body = $(`
				<div class="alert alert-info">
					<i class="fas fa-info-circle"></i> Check <i class="mdi mdi-checkbox-blank-outline text-muted"></i> <i class="mdi mdi-arrow-right"></i> <i class="mdi mdi-checkbox-marked text-primary"></i> each of the items that the supplier has quoted for and then enter the amount for each item.
				</div>
				<table class="table table-condensed table-striped table-sm table">
					<thead>
						<th></th>
						<th>Item</th>
						<th>Quantity</th>
						<th>Amount</th>
					</thead>
					<tbody>
						@foreach ($normalItems ?? array() as $req_item)
							<tr class="for-item-{{ $req_item->inventory_sub_category_id }} quote-row">
								<td><input type="checkbox" class="duplicatable quote-check" value="{{ $req_item->id }}" name="quote[id][{{$req_item->id}}]"></td>
								<td nowrap>{{ $req_item->item_name }}</td>
								<td>{{ $req_item->quantity }}</td>
								<td>
									<input type="number" step="any" min="1" value="" class="form-control form-control-sm duplicatable" name="quote[amount][{{$req_item->id}}]" required />
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			`);

			return $body.clone();
		}

		$(document).ready(function(){
			setTimeout(function(){
				$('.trigger-change-approver:first').each(function(i, e){
					e.click();
				});
			}, 2000)
		});

		var getNoteRow = function($data){
			var $row = `
				<tr class="note-row new">
					<td class="row-id"></td>
					<td>
						<select class="form-control" name="notes[type][]" placeholder="Type..." required>
							<option></option>
							@foreach (getNoteTypes() as $item)
								<option value="{{ $item }}" ${ $data.type == '{{ $item }}' ? 'selected' : '' } >{{ $item }}</option>
							@endforeach
						</select>
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_user_name }" readonly />
						<input type="hidden" class="note-type-val" name="created_by" value="${ $data.current_user }" />
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_time }" readonly />
					</td>
					<td nowrap>
						<span class="mdi mdi-android-messages btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#view-note-modal"
							data-description='${ $data.description }'> Description</span>
						<input type="hidden" name="notes[description][]" value="${ $data.description }" />
					</td>
				</tr>
			`;

			$row =  $($row).clone();

			return $row;
		}

		var getAttachmentRow = function($data){
			var $row = `
				<tr class="note-row new">
					<td class="row-id"></td>
					<td class="note-file-div" nowrap></td>
					<td>
						<input type="text" class="note-type-val form-control" name="attachments[title][]" value="${ $data.title }" />
					</td>
					<td>
						<select class="form-control" name="attachments[type][]" placeholder="Type..." required>
							<option></option>
							@foreach (getAttachmentTypes() as $item)
								<option value="{{ $item }}" ${ $data.type == '{{ $item }}' ? 'selected' : '' }>{{ $item }}</option>
							@endforeach
						</select>
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_user_name }" readonly />
						<input type="hidden" class="note-type-val" name="created_by" value="${ $data.current_user }" />
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_time }" readonly />
					</td>
					<td>
						<span class="mdi mdi-android-messages btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#view-note-modal"
							data-description='${ $data.description }'> Description</span>
						<input type="hidden" name="attachments[description][]" value="${ $data.description }" />
					</td>
				</tr>
			`;

			$row =  $($row).clone();

			$row.find('.note-file-div').append($data.file);

			$row.find('[name="attachments[file][]"]').on('change', function(){
				alert($(this).val())
			});

			return $row;
		}

		var getSupplierRow = function($data={}){
			var $row = `
				<tr class="item-row new">
					<td class="item-id"></td>
					<td>
						<div class="form-group">
							<select name="suppliers[id][]" style="min-width: 200px; font-size: 12px" class="form-control selected-supplier" placeholder="Select Supplier..." required>
								<option></option>
								@foreach ($normalItemsSuppliers as $item)
									<option value="{{ $item->id }}"  ${ ($data.supplier_id || 0) == {{ $item->id }} ? 'selected' : '' }
										data-phone="{{ $item->phone }}" data-email="{{ $item->email }}" data-items="{{ json_encode($item->itemIDs()) }}"
										data-rating="{{ number_format($item->average_rating(), 2) }}">{{ $item->name }}</option>
								@endforeach
							</select>
						</div>
					</td>
					<td nowrap>
						<div class="form-group">
							<span class="supplier-rating"></span>
						</div>
					<td nowrap>
						<div class="form-group">
							<span class="supplier-email" style="min-width: 150px"></span>
						</div>
					</td>
					<td nowrap>
						<div class="form-group">
							<span class="supplier-phone" style="min-width: 150px"></span>
						</div>
					</td>
					</td>
					<td nowrap>
						<div class="form-group">
							${ ($data.rfq_sent || 0) == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' }
						</div
					</td>
					<td nowrap>
						<div class="form-group">
							${ ($data.quote_received || 0) == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' }
							<span class="quote-btn"></span>
						</div
					</td>
				</tr>
			`;

			$row = $($row);

			// if(($data.rfq_sent || 0) == '1' && ($data.quote_received || 0) == '0'){
				var btn = $(`<span class="btn btn-sm btn-default text-primary ml-2 accept-quote-btn"
						data-toggle="modal" data-target="#quote-accept-modal" data-supplier="`+$data.supplier_id+`">Accept Quote</span>`);

				$row.find('.quote-btn').append(btn);
			// }

			return $row.clone();
		}

		var getItemRow = function($data){
			var $row = `
				<tr class="item-row new">
					<td class="item-id"></td>
					<td>-</td>
					<td>
						<div class="form-group">
							<select name="items[item_id][]" style="min-width: 200px; font-size: 12px" class="form-control selected-item" placeholder="Select Item..." required><option></option></select>
						</div>
					</td>
					<td>
						<div class="form-group">
							<select name="items[item_brand_id][]" style="min-width: 200px;" class="form-control selected-item-brand" placeholder="Select Item Brand..."><option></option></select>
						</div>
					</td>
					<td nowrap>
						<div class="form-group">
							<textarea class="form-control item-description" name="items[comments][]" style="min-width: 250px"></textarea>
						</div>
					</td>
					<td>
						<div class="form-group">
							<select name="items[uom][]" class="form-control selected-item-uom" placeholder="Select Item UoM..." ><option></option></select>
						</div>
					</td>
					@if(in_array($stage, array("Request to Store", "Material Requisition", "Request for Quotation")))
					<td nowrap>
						<div class="form-group">
							<span class="form-control open-quantity" style="min-width: 150px">0.00</span>
							<input type="hidden" min="0.00" name="items[open_quantity][]" style="min-width: 100px" class="form-control open_quantity" placeholder="Quantity..." />
						</div>
					</td>
					@endif
					<td>
						<div class="form-group">
							<input type="number" min="0" name="items[quantity][]" style="min-width: 100px" class="form-control user-quantity" placeholder="Quantity..." required />
						</div>
					</td>
					@if($stage == "Goods Receipt")
					<td>
						<div class="form-group">
							<input type="number" min="0.00" name="items[received_quantity][]" style="min-width: 100px" class="form-control user-quantity" placeholder="Quantity..." required />
						</div>
					</td>
					@endif
					@if($stage == "Request to Store")
						<td>
							<div class="form-group">
								<input type="number" min="0.00" name="items[pending_quantity][]" style="min-width: 100px" class="form-control user-pending-quantity" placeholder="Quantity..." readonly />
							</div>
						</td>
						<td>
							<div class="form-group">
								<input type="number" min="0.00" name="items[issued_quantity][]" style="min-width: 100px" class="form-control user-quantity" placeholder="Quantity..." readonly />
							</div>
						</td>
					@endif
					@if($stage == "Request to Store")
						<td>
							<div class="form-group">
								<input type="text" min="0.00" name="items[lot_no][]" style="min-width: 100px" class="form-control user-lot-no" placeholder="Lot Number..." />
							</div>
						</td>
						<td>
							<div class="form-group">
								<select name="items[starting_sample][]" class="form-control starting-sample" placeholder="Starting Sample...">
									<option value=""></option>
									@foreach (getSamplesByWorkflow("Samples In Lab") as $sample)
										<option value="{{ $sample->id }}">{{ $sample->sample_code }}</option>
									@endforeach
								</select>
							</div>
						</td>
					@endif
					@if($stage == "Material Requisition")
					<td nowrap>
						<div class="form-group">
							<span class="form-control" style="min-width: 100px">{{ \Carbon\Carbon::now()->addDays(7) }}</span>
							<input type="hidden" name="delivery_date" value="{{ \Carbon\Carbon::now()->addDays(7) }}" />
						</div>
					</td>
					@endif
					<td>
						<div class="form-group">
							<select name="items[store_id][]" class="form-control selected-store" placeholder="Select Store...">
								<option></option>
								@foreach (getUserStores() as $store)
									<option value="{{ $store->id }}" data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
								@endforeach
							</select>
						</div>
					</td>
					<td>
						<div class="form-group">
							<select name="items[slot_id][]" style="min-width: 100px"  class="form-control store-slots" placeholder="Select Slot..."><option></option></select>
						</div>
					</td>
					<td>
						<div class="form-group">
							<input type="number" min="0.00" name="items[net_value][]" style="min-width: 100px" value="0.00" class="form-control items-total" required />
						</div>
					</td>
				</tr>`;
			return $($row).clone();
		}

		var slotchangeEvent = function(parentTr=false){
			if(parentTr){
				var val = parentTr.find('[name="items[slot_id][]"]').data('slot');
				parentTr.find('[name="items[slot_id][]"]').val(val);
			}
			else{
				$('#req-items').find('[name="items[slot_id][]"]').each(function(e){
					var slot = $(this).data('slot');
					$(this).val(slot).trigger('change');
				});
			}

		}

		var clean_placeholder = function($placeholder){
			if($placeholder == undefined){
				return $placeholder;
			}

			var parts = $placeholder.split('Select');
			var ln = parts.length;
			var last = parts[ln-1];


			var parts = last.split('Enter');
			var ln = parts.length;

			return parts[ln-1];
		}

		$(function(){
			var rejectReason = $(`<input type="hidden" name="reject_reason" class="reject-reason-hidden" />`);
			var awardReason = $(`<input type="hidden" name="award_reason" class="award-reason-hidden" />`);
			var approveThis = $(`<input type="hidden" name="approve_this" value="1" />`);
			var approvalDiv = $(`<input type="hidden" name="approval_id" />`);
			var approvalID;
			var supplierID;
			var awardedQuote;

			$("#delete-supplier-quote").on('show.bs.modal', function(e){
				var btn = $(e.relatedTarget);
				var item = btn.data('item');
				var quote = btn.data('quote');

				$(this).find('form').attr('action', '/remove/supplier-quote/'+quote.id);

				$(this).find('.modal-title').html(`<i class="mdi mdi-cash-usd"></i> Remove Quote for ${item}`);

				$(this).find('.modal-body').find('.quote-item').html(`${item}`);
			});

			$("#undo-supplier-award").on('show.bs.modal', function(e){
				var quote = $(e.relatedTarget).data('quote');
				$("#undo-supplier-award").find('form').attr('action', '{{ url("/") }}/undo-supplier-award/'+quote.id);
				$("#undo-supplier-award").find('form').prop('action', '{{ url("/") }}/undo-supplier-award/'+quote.id);
			});

			$('.trigger-save').on('change', function(){
				$('#save-other-changes').removeClass('hidden');
			});

			$('.trigger-save').on('click', function(){
				$('#save-other-changes').removeClass('hidden');
			});

			$('#return-goods-to-supplier-modal').find('[name="return_action"]').on('click', function() {
				var action = $(this).val();
				var fileField = $(this).parents('.modal-body').find('.uploadable');
				if(action == "credit_note"){
					fileField.removeClass('hide');
				}
				else{
					fileField.addClass('hide');
				}
			});

			$("#change-approver-modal").on('show.bs.modal', function(e){
				var rlTarget = $(e.relatedTarget);
				var approvalID = rlTarget.data('approval');
				var title = rlTarget.data('title');
				var users = rlTarget.data('users');
				var select = $(this).find('select[name="user_id"]');

				$(this).find('.modal-title').html(`<i class="mdi mdi-account-convert"></i> Set Approval for ${title}`);

				select.empty()
				$.each(users, function(u, s){
					var op = $(`<option value="${s}">${u}</option>`);
					select.append(op);
				});
				select.trigger('change');

				var form = $(this).find('form');
				form.attr('action', '/change-req-approver/'+approvalID);
				form.prop('action', '/change-req-approver/'+approvalID);
			});
			$("#edit-supplier-quote").on('show.bs.modal', function(e){
				var btn = $(e.relatedTarget);
				var item = btn.data('item');
				var quote = btn.data('quote');

				$(this).find('form').attr('action', '/edit/supplier-quote/'+quote.id);

				$(this).find('.modal-title').html(`<i class="mdi mdi-cash-usd"></i> Edit ${item} Quote Amount`);

				$(this).find('.modal-body').find('[name="amount"]').val(quote.quote_amount);
			});

			$('#quote-accept-modal').on('change', '.quote-check', function(){
				if($(this).is(":checked")){
					$(this).parents('tr').find('[type="number"]').val(0);
					$(this).parents('tr').find('[type="number"]').prop('required', true);
					$(this).parents('tr').find('[type="number"]').attr('required', true);
					$(this).parents('tr').find('[type="number"]').removeProp('readonly');
					$(this).parents('tr').find('[type="number"]').removeAttr('readonly');
				}
				else{
					$(this).parents('tr').find('[type="number"]').val('');
					$(this).parents('tr').find('[type="number"]').removeProp('required');
					$(this).parents('tr').find('[type="number"]').removeAttr('required');
					$(this).parents('tr').find('[type="number"]').prop('readonly', true);
					$(this).parents('tr').find('[type="number"]').attr('readonly', true);
				}
			});

			$('.received-user-quantity, .issued-user-quantity').on('change', function(){
				var max = parseFloat($(this).attr('max'));
				var val = parseFloat($(this).val());

				if(val > max){
					alert("Received Quantity can not exceed pending quantity!");

					$(this).val('0');
				}
			});

			$('#req-items').on('change', 'tr .user-quantity', function(){
				var val = parseFloat($(this).val());
				var price = parseFloat($(this).parents('tr').find('.items-total').data('unit_value'));

				$(this).parents('tr').find('.items-total').val(val*price);

			});

			$('#quote-accept-modal').on('show.bs.modal', function(e){
				var items = $(e.relatedTarget).data('items');

				var $body = getQuoteAcceptModalBody();

				$('#quote-accept-modal').find('.modal-body').html($body);

				$.each(items, function(i, e){
					$('#quote-accept-modal').find('tr.for-item-'+e).addClass('hasThis');
				});

				$('#quote-accept-modal').find('tr.quote-row').not('.hasThis').remove();

				supplierID = $(e.relatedTarget).data('supplier');
			})

			$('#enter-reject-modal').on('show.bs.modal', function(e) {
				approvalID = $(e.relatedTarget).data('approval');
			});

			$('#award-rfq-to-user').on('show.bs.modal', function(e) {
				awardedQuote = $(e.relatedTarget).data('item');
			});

			$('#confirm-accept-modal').on('show.bs.modal', function(e) {
				approvalID = $(e.relatedTarget).data('approval');
			});

			$('.save-details-form').on('click', function(){
				var type = $(this).data('type');
				var inform = $(this).data('alert') || false;

				if(inform){
					var proceed = true;
					if(confirm(inform)){
						proceed = true;
					}
					else{
						proceed = false;
					}

					if(!proceed){
						return false;
					}
				}

				if(type == 'make-an-ammendment'){
					$('#details-form').append(`<input type="hidden" name="make_an_ammendment" value="1" />`);
				}

				if(type == 'approve-ammendments-details'){
					$('#details-form').append(`<input type="hidden" name="approve_ammendments_details" value="1" />`);
				}

				if(type == 'send-to-finance'){
					$('#details-form').append(`<input type="hidden" name="send_to_finance" value="1" />`);
				}

				if(type == 'cancel-ammendment'){
					$('#details-form').append(`<input type="hidden" name="cancel_ammendment" value="1" />`);
					var $reject_message = $('#reject-ammendment-modal').find('[name="rejection_message"]').val();

					console.log($reject_message)

					if($.trim($reject_message) == ""){
						alert("Please provide a reason for rejecting the ammendments");

						return false;
					}
					// return false;

					$('#details-form').append(`<input type="hidden" name="rejection_message" value="${$reject_message}" />`)
				}

				if(type == 'get-requester-approval'){
					$('#details-form').append(`<input type="hidden" name="get_requester_approval" value="1" />`);
				}

				if(type == 'generate-purchase-order'){
					$('#details-form').append(`<input type="hidden" name="generate_purchase_order" value="1" />`);
				}

				if(type== "mark-as-completed"){
					$('#details-form').append(`<input type="hidden" name="mark_as_complete" value="1" />`);
				}

				if(type== "notify-user"){
					$('#details-form').append(`<input type="hidden" name="notify_the_user" value="1" />`);
				}

				if(type== "issue-items-details"){
					$('#details-form').append(`<input type="hidden" name="issue_out_items" value="1" />`);
				}

				if(type == 'generate-goods-receipt'){
					$('#details-form').append(`<input type="hidden" name="generate_goods_receipt" value="1" />`);
				}

				if(type == 'return-goods-to-supplier'){
					$('#details-form').append(`<input type="hidden" name="return_goods_to_supplier" value="1" />`);
					$('#details-form').append($('.goods-return-credit-note-file'));
					$('#details-form').append($('.goods-return-action:checked'));
				}

				if(type == 'accept-goods-receipt'){
					var otp_value = $('#accept-goods-otp-modal').find('[name="requester_otp"]').val();
					if(otp_value.length != 6){
						alert("Please provide the OTP Code(6 characters).");
						return false;
					}
					$('#details-form').append(`<input type="hidden" name="accept_goods_receipt" value="1" />`);
					$('#details-form').append(`<input type="hidden" name="otp_value" value="${otp_value}" />`);
				}

				if(type == 'issue-items'){
					var otp_value = $('#issue-items-otp-modal').find('[name="requester_otp"]').val();
					if(otp_value.length != 6){
						alert("Please provide the OTP Code(6 characters).");
						return false;
					}
					$('#details-form').append(`<input type="hidden" name="issue_out_items" value="1" />`);
					$('#details-form').append(`<input type="hidden" name="otp_value" value="${otp_value}" />`);
				}

				if(type == 'issue-items-from-material-requisition'){
					$('#details-form').append(`<input type="hidden" name="create_material_issuance" value="1" />`);
				}

				if(type == 'create-rfq-from-material-requisition'){
					$('#details-form').append(`<input type="hidden" name="create_rfq_from_material_requisition" value="1" />`);
				}

				if(type == 'send-purchase-order'){
					$('#details-form').append(`<input type="hidden" name="send_purchase_order" value="1" />`);
				}

				if(type == 'get-approval-details'){
					$('#details-form').append(markCompleted);
				}

				if(type == 'accept-supplier-quote'){
					var valid = true;
					var isChecked = true;
					$('.form-control.duplicatable').each(function(e){
						var val = $.trim($(this).val());
						var checkBox = $(this).parents('tr').find('.quote-check.duplicatable')
						if(checkBox.is(":checked") && val == ""){
							valid = false;
						}
					});

					if($('.quote-check.duplicatable:checked').length == 0){
						alert("No Item selected");
						return;
					}

					if(!valid){
						alert("Confirm that you have selected an Item and entered the price");
						return;
					}

					var $duplicatable = $('#quote-accept-modal').find('.duplicatable');

					$duplicatable.attr('style', 'width: 1px !important; height:1px !important; overflow: hidden!important');

					$('#details-form').append(`<input type="hidden" name="supplier_id" value="${supplierID}" />`);
					$('#details-form').append(`<input type="hidden" name="is_rfq_quote" value="1" />`);
					$('#details-form').append($duplicatable);
				}

				if(type == "send-rfq-details"){
					$('#details-form').append(`<input type="hidden" name="is_send_rfq" value="1" />`);
				}

				if(type == 'reject-with-reason'){
					var reason = $('#reject-reason-text').val();

					reason = $.trim(reason);

					if(reason == ""){
						alert('Please Provide a reason for the rejection.')
						return;
					}

					rejectReason.val(reason);

					approvalDiv.val(approvalID);
					$('#details-form').append(approvalDiv);
					$('#details-form').append(rejectReason);
				}

				if(type == 'award-with-reason'){
					var reason = $('#award-reason-text').val();

					reason = $.trim(reason);

					if(reason == ""){
						alert('Please provide a comment on why this supplier was chosen.')
						return;
					}

					awardReason.val(reason);


					$('#details-form').append(`<input type="hidden" name="awarded_quote_is" value="${awardedQuote.id}" />`);
					$('#details-form').append(awardReason);
				}

				if(type == 'confirm-approval-reason'){
					var approval = $(this).data('approval');

					approvalDiv.val(approvalID);
					$('#details-form').append(approveThis);
					$('#details-form').append(approvalDiv);
				}

				if(type == 'save-details'){
					$('#details-form').find('[name="get_approval"]').remove();
					$('#req-items').find('select, input').removeAttr('disabled');
					$('#req-items').find('select, input').removeProp('disabled');
				}

				var subMit = true;
				var isValidIn = true;
				var isValidSel = true;
				var theField;
				var theSelect;
				var allInvalidHolder = [];
				var theFieldsInval = [];

				$('#details-form input:required').map(function() {
					isValidIn &= this.validity['valid'] ;

					theField = this;

					if(!this.validity['valid']){
						allInvalidHolder.push([theField, 'input']);
					}
					else{
						$(theField).parent().css('border', 'inherit');
					}
				}) ;

				if (isValidIn) {
					console.log('valid input!');
				} else{
					subMit = false;
					console.log('invalid input!');
					console.log(allInvalidHolder);
				}

				$('#details-form select:required').map(function() {
					isValidSel &= this.validity['valid'] ;
					theSelect = this;
					if(!this.validity['valid']){
						allInvalidHolder.push([theSelect, 'select']);
					}
					else{
						$(theSelect).parent().css('border', 'inherit');
						$(theSelect).parent().css('background-color', 'inherit');
					}
				}) ;

				if (isValidSel) {
					console.log('valid select!');
					// post something..
				}
				else{
					console.log('invalid select!');
					console.log(isValidSel);
					subMit = false;
				}

				if(allInvalidHolder.length > 0){
					$.each(allInvalidHolder, function(j,s){
						if(s[1] != 'select'){
							$(s[0]).css('border', '2px solid red');
						}
						else{
							$(s[0]).parent().css('border', '1px solid red');
							$(s[0]).parent().css('background-color', '#ffd8bc');
						}
						var $val = $.trim($(s[0]).attr('placeholder')) != '' ? $(s[0]).attr('placeholder') : $(s[0]).data('placeholder');
						theFieldsInval.push(clean_placeholder($val));
					});
				}

				if(subMit){
					$('#details-form').submit();
					thisBTN.off('click');
				}
				else{
					alert("Confirm that you have provided all the values for ("+theFieldsInval.join(',')+")");
					$('#details-form').find('input[name="accept_goods_receipt"]').remove();
					$('#details-form').find('input[name="issue_out_items"]').remove();
				}
			});

			$('.add-supplier-row').on('click', function(){
				$('#supplier-list').find('tr.no-data').remove();
				generateSupplierRow()
			});

			$('#supplier-list').on('change', 'tr .selected-supplier', function(){
				var selected = $(this).children('option:selected');
				var phone = selected.data('phone')
				var rating = selected.data('rating') || 0;
				var email = selected.data('email')
				var items = selected.data('items')
				var parentTr = $(this).parents('tr');

				// console.log(items);

				parentTr.find('.supplier-email').text(email);
				parentTr.find('.supplier-phone').text(phone);
				parentTr.find('.supplier-rating').html(rating+` <i class="mdi mdi-star text-success"></input>`);
				var arr = [];

				if(parentTr.find('.accept-quote-btn').length > 0){
					parentTr.find('.accept-quote-btn').data('items', items);
				}
			});

			$('#req-items').on('change', 'tr .selected-store', function(){
				var selected = $(this).children('option:selected');
				var slots = selected.data('slots');

				var slotDiv = $(this).parents('tr').find('[name="items[slot_id][]"]');
				var selectedVal = slotDiv.data('slot')

				slotDiv.html(`<option></option>`);

				$.each(slots, function(i, s){
					var newOption = new Option(s.name, s.id, false, false);
					slotDiv.append(newOption).trigger('change');
				});

				slotDiv.val(selectedVal).trigger('change');
			});

			var fetchAvailableStock = function($parentTr, brand_id, item_id){
				$.ajax({
					url: '/api-get-available-items/'+item_id+'/'+brand_id,
					dataType: 'json',
					beforeSend: function(){
						$parentTr.find('.open_quantity').val('Fetching...')
					},
					success: function(js){
						$parentTr.find('.open-quantity').text(js.formatted);
						$parentTr.find('.open_quantity').val(js.value);
					}
				});
			}

			var defaultStores = $(`<option></option>
				@foreach (getUserStores() as $store)
					<option value="{{ $store->id }}" data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
				@endforeach`);

			$('#req-items').on('change', 'tr select.selected-item-brand', function(){
				var parentTr = $(this).parents('tr');
				var brand_id = $(this).children('option:selected').val();
				var item_id = parentTr.find('select.selected-item').children('option:selected').val();

				fetchAvailableStock(parentTr, brand_id, item_id);
			});

			var sortOutSelectedItem = function(js, $this, $itemID){
				var available_formated = js.available_formated;
				var available = js.available;
				var brands = js.brands;
				var text = $.trim(js.text);
				var comments = js.comments;
				var unit_val = js.unit_val;
				var parentTr = $this.parents('tr');

				console.log(unit_val);

				@if(in_array($stage, ['Request to Store', 'Material Issuance']))
					var $uoms = [js.uom, js.uom2];
				@else
					var $uoms = [js.uom];
				@endif

				var siblingBrands = $this.parents('tr').find('select.selected-item-brand').html(`<option value="0" selected>Non Specific</option>`);
				var selectedBrand = $this.parents('tr').find('select.selected-item-brand').data('selected');
				var $UoMSelect = $this.parents('tr').find('select.selected-item-uom');
				var selectedUoM = $UoMSelect.data('selected') || js.uom;
				$.each(brands, function(j,s){
					var $op = $(`<option value="${s.id}">${s.name}</option>`);
					siblingBrands.append($op);
				});

				$UoMSelect.empty();
				$.each($uoms, function(u, m){
					var $op = $(`<option value="${m}">${m}</option>`);
					$UoMSelect.append($op);
				});
				$UoMSelect.val(selectedUoM).trigger('change');
				siblingBrands.val(selectedBrand).trigger('change');

				// fetchAvailableStock(parentTr, parentTr.find('select.selected-item-brand').val(), selected.val());

				if(text){
					$.ajax({
						url: '/get-store-slots-by-item/'+$itemID,
						dataType: 'json',
						success: function(js){
							var selectedVal = parentTr.find('[name="items[store_id][]"]').data('selected');

							if(js.length > 0){
								parentTr.find('[name="items[store_id][]"]').html(`<option></option>`).trigger('change');
								$.each(js, function(j,s){
									var $op = $(`<option value="${s.id}">${s.name}</option>`);
									$op.data('slots', s.slots);
									parentTr.find('[name="items[store_id][]"]').append($op);
								});
							}
							else{
								parentTr.find('[name="items[store_id][]"]').html(defaultStores.clone());
							}
							parentTr.find('[name="items[store_id][]"]').val(selectedVal).trigger('change');
							window.setTimeout(function(){
								slotchangeEvent(parentTr);
							}, 1000);
						}
					})
					var qnty = parentTr.find('input.user-quantity').val();
					parentTr.find('.items-total').data('unit_value', unit_val);
					parentTr.find('.items-total').val(qnty*unit_val);
				}

			}

			$('#req-items').find('.selected-item').select2({
				ajax: {
					url: '{{ route("get_items_via_ajax") }}',
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

			$('#req-items').on('change', 'tr .selected-item', function(){
				var itemID = $(this).val();
				var $this = $(this);
				$.ajax({
					url: '/get_item_details/'+itemID,
					beforeSend: function(){

					},
					success: function(js){
						sortOutSelectedItem(js, $this, itemID);
					}
				});
			});

			$('.add-item-row').on('click', function(){
				var itemRow = getItemRow();

				itemRow.find('select').not('.selected-item').each(function(e){
					var placeholder = $(this).attr('placeholder');
					$(this).select2({
						placeholder: placeholder
					});
				});

				itemRow.find('.selected-item').select2({
					ajax: {
						url: '{{ route("get_items_via_ajax") }}',
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

				var no = $('#req-items').find('tr').length +1;

				itemRow.find('.item-id').text(no);

				var d = new Date();
  			var n = d.getTime();

				itemRow.find('.item-id').append(`
					<input type="hidden" name="items[req_item_id][]" value="${ n }" />
				`);


				$('#req-items').append(itemRow);

			});

			$('#req-items').on('dblclick', 'tr.item-row .item-id', function(){
				$(this).toggleClass('bg-selected');
			});

			$('#note-items').on('dblclick', 'tr.item-row .item-id', function(){
				$(this).toggleClass('bg-selected');
			});

			$('.notes-and-attachments').on('dblclick', 'tr.note-row .row-id', function(){
				$(this).toggleClass('bg-selected');
			});

			$('#attachment-items').on('dblclick', 'tr.item-row .item-id', function(){
				$(this).toggleClass('bg-selected');
			});

			$('.delete-item-row').on('click', function(){
				if($('#req-items').find('.item-id.bg-selected').length && confirm("Are you sure you want to remove this row?")){
					$('#req-items').find('.item-id.bg-selected').parents('tr').remove();
				}
				else{
					alert("No row selected. Double-click on a row number to select");
				}
			});

			$('.delete-this-row').on('click', function(){
				var holder = $(this).data('holder');
				if($(holder).find('.row-id.bg-selected').length > 0 && confirm("Are you sure you want to remove this row?")){
					$(holder).find('.row-id.bg-selected').parents('tr').remove();
				}
				else{
					alert("No row selected. Double-click on a row number to select");
				}
			});

			$('.modal-add-data').on('click', function(){
				var type = $(this).data('type');
				var modal = $(this).parents('.modal');

				if(type == "note"){
					generateNoteRow(modal);
				}
				else{
					generateAttachmentRow(modal);
				}

				modal.find('[data-dismiss="modal"]').trigger('click');
			});

			var generateNoteRow = function($modal = false, $dt = false){
				if($modal){
					var data = {
						"type": $modal.find('[name="type"]').val(),
						"description": $modal.find('[name="description"]').val(),
						"current_user": $modal.find('[name="current_user"]').val(),
						"current_user_name": $modal.find('[name="current_user_name"]').val(),
						"current_user_email": $modal.find('[name="current_user_email"]').val(),
						"current_time": $modal.find('[name="current_time"]').val()
					}
				}

				if($dt){
					var data = {
						"type": $dt.type,
						"description": $dt.description,
						"current_user": $dt.user_id,
						"current_user_name": $dt.user_name,
						"current_user_email": $dt.user_email,
						"current_time": $dt.created_at
					}
				}

				var noteRow = getNoteRow(data);

				var d = new Date();
				var n = $dt ? $dt.id : d.getTime();
				var trLen = $('#note-items').find('tr').length;

				noteRow.find('.row-id').text(trLen+1);

				noteRow.find('.row-id').append(`
					<input type="hidden" name="notes[note_id][]" value="${ n }" />
				`);

				$('#note-items').append(noteRow);
				if($modal){
					$modal.find('.form-control').val('');
				}

			}

			var all_notes = $('#note-items').data('notes');

			$.each(all_notes, function(i, e){
				generateNoteRow(false, e);
			});

			var generateSupplierRow = function($dt = false){
				var sRow = getSupplierRow($dt);
				var trLen = $('#supplier-list').find('tr').length;

				sRow.find('.item-id').text(trLen+1);

				var d = new Date();
				var n = $dt ? $dt.id : d.getTime();

				sRow.find('.item-id').append(`
					<input type="hidden" name="suppliers[supplier_rfq_id][]" value="${ n }" />
				`);

				$('#supplier-list').append(sRow);

				$('#supplier-list').find('.form-control').trigger('change');

				sRow.find('select').select2();
			}

			var generateAttachmentRow = function($modal = false, $dt = false){
				if($modal){
					var data = {
						"file": $modal.find('[name="attachments[file][]"]').clone(),
						"type": $modal.find('[name="type"]').val(),
						"title": $modal.find('[name="title"]').val(),
						"description": $modal.find('[name="description"]').val(),
						"current_user": $modal.find('[name="current_user"]').val(),
						"current_user_name": $modal.find('[name="current_user_name"]').val(),
						"current_user_email": $modal.find('[name="current_user_email"]').val(),
						"current_time": $modal.find('[name="current_time"]').val()
					}
				}

				if($dt){
					var data = {
						"file": `<span class="file-link"><a target="_blank" href="${ $dt.file }" class="btn btn-default text-primary"><i class="mdi mdi-download"></i> Download</a></span>
							<span class="hide file-type"><input type="file" class="form-control" name="attachments[file][]" /></span>
							<span class="hide file-type"><input type="hidden" class="form-control" value="${ $dt.file }" name="prev_file[${ $dt.id }]" /></span>
							<span class="btn btn-default btn-sm text-muted change-image" ml-2><i class="mdi mdi-sync"></i></span>
						`,
						"type": $dt.type,
						"title": $dt.title,
						"description": $dt.description,
						"current_user": $dt.user_id,
						"current_user_name": $dt.user_name,
						"current_user_email": $dt.user_email,
						"current_time": $dt.created_at
					}
				}

				var attachmentRow = getAttachmentRow(data);

				var d = new Date();
				var n = $dt ? $dt.id : d.getTime();
				var trLen = $('#attachment-items').find('tr').length;

				attachmentRow.find('.row-id').text(trLen+1);

				attachmentRow.find('.row-id').append(`
					<input type="hidden" name="attachments[attachment_id][]" value="${ n }" />
				`);

				$('#attachment-items').append(attachmentRow);
				var attachmentRow2 = attachmentRow.clone();
				$('#attachment-items').append(attachmentRow2);
				attachmentRow2.remove();

				if($dt){
					attachmentRow.find('.change-image').on('click', function(){
						var td = $(this).parents('td');
						td.find('.file-type').toggleClass('hide');
						td.find('.file-link').toggleClass('hide');
					});
				}

				if($modal){
					$modal.find('.form-control').val('');
				}
			}

			var all_suppliers = $('#supplier-list').data('suppliers') || [];

			if(all_suppliers.length > 0){
				$('#supplier-list').find('tr.no-data').remove();
			}

			$.each(all_suppliers, function(i, e){
				generateSupplierRow(e);
			});

			var all_attachments = $('#attachment-items').data('attachments');

			$.each(all_attachments, function(i, e){
				generateAttachmentRow(false, e);
			});

			$('#view-note-modal').on('show.bs.modal', function(e) {
				var description = $(e.relatedTarget).data('description');
				$('#view-note-description').html(description);
			});

			@if(count($normalItems) == 0)
				$('.add-item-row').trigger('click');
			@else
				$('#req-items').find('.form-control').trigger('change');

				window.setTimeout(function(){
					slotchangeEvent();
					$("#details-form").on('change', '.form-control', function(){
						$('.save-details-form[data-type="save-details"]').toggleClass('btn-default btn-outline-primary');
					});
				}, 200);

			@endif
			$('.default-currency').text($('[name="currency"]').children('option:selected').text());
		});
	</script>
@endsection
