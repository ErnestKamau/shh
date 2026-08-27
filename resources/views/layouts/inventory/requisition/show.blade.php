@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<?php $lab_storage_catgory_id = systemVariables('lab_samples_category_id'); ?>
<title>{{ $request->request_code ?? 'New '.rtrim($stage, 's') }} {{ " - ".$stage }} | Inventory Management</title>
<style>
	.bg-selected {
		color: #fff;
		background-color: #3d5777;
	}

	.hide {
		display: none;
	}

	.item-row td .form-group {
		margin-bottom: unset !important;
	}

	#document-flow-holder {
		white-space: nowrap;
		/* important */
		overflow: auto;
	}

	.flow-doc-holder {
		display: inline-block;
		padding: 10px;
		vertical-align: text-top;
	}

	.flow-doc {
		max-width: 305px;
		width: 245px;
		text-align: center;
		border-radius: 9px;
		border: 1px solid #5f84a2;
		box-shadow: 0px 0px 15px #b5b5b5;
		margin-bottom: 10px;
	}

	.flow-doc.is_current {
		border: 3px solid #5f84a2;
		box-shadow: 0px 0px 15px #a9a9a9;
	}

	.flow-doc.is_deleted {
		border-color: rgb(156, 0, 0);
	}

	input:invalid {
		border-color: red;
	}

	.flow-doc .header {
		font-size: 14px;
		font-weight: 600;
		color: #676767;
		padding: 5px 9px;
		border-bottom: 1px solid #5f84a2;
	}

	.flow-doc .body {
		font-size: 12px;
		padding: 5px;
		border-bottom: 1px solid #d5d5d5;
	}

	.flow-doc .footer {
		font-size: 12px;
		padding: 5px;
		color: #34f;
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
<?php
	$theUser = \App\User::find($request->request_initiator) ?? \Auth::user();
	$availableCurrencies = getCurrencies();
	$allItems = $request->items($ammendment) ?? array();
	$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());
	
	$allItemsGrouped = $request->items($ammendment, false, true) ?? array();
	$normalItemsGrouped = empty($allItemsGrouped) ? array() : ($allItemsGrouped['normal'] ?? array());

	// echo json_encode($normalItemsGrouped, JSON_PRETTY_PRINT);

	$kitPossibleItems = $request->items($ammendment, true) ?? array();
	$kitPossibleItems = empty($kitPossibleItems) ? array() : ($kitPossibleItems['normal'] ?? array());


	$otherItems = empty($allItems) ? array() : ($allItems['issued_received'] ?? array());

	$normalItemIDS = [];
	$normalItemsSuppliers = [];
	foreach ($normalItems as $i) {
		$normalItemIDS[] = $i->inventory_sub_category_id;
	}

	$inventoryItems = [];
	$normalItemsSuppliers = getSuppliers($normalItemIDS);
	// $inventoryItems = getInventoryItems($lab_storage_catgory_id, true);
	// echo json_encode($inventoryItems);
	$myCCs = explode(',', $request->cost_center ?? $theUser->department()->name);

	$allStores = getUserStores();
	$allStoreIds = collect($allStores)->pluck('id')->unique()->filter()->values();
	$allStores = \App\InventoryStore::with('slots')
		->whereIn('id', $allStoreIds)
		->orderBy('name')
		->get();
	if ($allStores->isEmpty()) {
		$locationId = getCurrentUserLocation()->id ?? null;
		$allStores = \App\InventoryStore::with('slots')
			->when($locationId, fn ($q) => $q->where('inventory_location_id', $locationId))
			->where(function ($q) {
				$q->where('is_frozen', false)->orWhere('is_frozen', '0')->orWhereNull('is_frozen');
			})
			->orderBy('name')
			->get();
	}
?>
<?php
	$gate_pass_category = getConfigByName('gate_pass_category_id');
	$gate_pass_category_id = count($gate_pass_category) > 0 ? $gate_pass_category[0]->value : 0;

	$inventoryProcurementRoles = filterExistingSpatieRoleNames(['Inventory Procurement Group', 'Procurement', 'Admin']);
	$inventoryDepartmentHeadRoles = filterExistingSpatieRoleNames(['Inventory Department Head Group', 'Department Head', 'Admin']);
	$inventoryManagerRoles = filterExistingSpatieRoleNames(['Inventory Manager Group', 'Manager', 'Admin']);
	$inventoryFinanceRoles = filterExistingSpatieRoleNames(['Inventory Finance Group', 'Financial Accountant', 'Finance', 'Admin']);
	$inventoryStoreManagerRoles = filterExistingSpatieRoleNames(['Inventory Store Manager Group', 'Store Manager', 'Store', 'Admin']);

	$isInventoryProcurement = $inventoryProcurementRoles !== [] && \Auth::user()->hasAnyRole($inventoryProcurementRoles);
	$isInventoryDepartmentHead = $inventoryDepartmentHeadRoles !== [] && \Auth::user()->hasAnyRole($inventoryDepartmentHeadRoles);
	$isInventoryManager = $inventoryManagerRoles !== [] && \Auth::user()->hasAnyRole($inventoryManagerRoles);
	$isInventoryFinance = $inventoryFinanceRoles !== [] && \Auth::user()->hasAnyRole($inventoryFinanceRoles);
	$isInventoryStoreManager = $inventoryStoreManagerRoles !== [] && \Auth::user()->hasAnyRole($inventoryStoreManagerRoles);

	$lab_department_id = getConfigByName('lab_department_id');
	$lab_department_id = count($lab_department_id) > 0 ? $lab_department_id[0]->value : 0;

	$expiring_item_ids = getConfigByName('expiring_item_ids');
	$expiring_item_ids = count($expiring_item_ids) > 0 ? $expiring_item_ids[0]->value : 0;
	$expiring_item_ids = explode(",", $expiring_item_ids);
	$expiring_item_ids = array_map('trim', $expiring_item_ids);
	$expiring_item_ids = array_map('intval', $expiring_item_ids);
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

		$parentIsLoanLend = (!empty($request->parent_request_id) && \Illuminate\Support\Str::isUuid((string) $request->parent_request_id))
			? \App\RequestEntity::find($request->parent_request_id)
			: null;
		$hasExceeded = has_exceeding_quantities($request->id ?? null);

	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<?php $approvals = getStageApprovals('Requisition', $stage); ?>
	<h3 class="p-4">
		<i class="mdi mdi-text-box-plus"></i> {{ !isset($request->status) ? 'Create' : '' }} {{ $stage }} {{
		$request->request_code ?? '' }}
		<span class="btn-group" role="group">
			<button id="btnGroupDrop1" type="button" class="btn-sm btn btn-transparent dropdown-toggle" data-toggle="dropdown"
				aria-haspopup="true" aria-expanded="false">
				v{{ $ammendment }}
			</button>
			<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
				@for ($a = $ammendment_count; $a >=1; $a--)
				<a class="dropdown-item save-details-form" data-type="save-details"
					href="{{ route('view-request-details', ['stage'=>$request->request_type, 'id'=>$request->request_code, 'ammendement'=>$a]) }}">
					<i class="mdi mdi-chevron-double-right"></i> v{{ $a }}
				</a>
				@endfor
			</div>
		</span>
		<small style="cursor: pointer" class="badge badge-pill bg-white my-small-text" {!! in_array($stage, ["Purchase Request", "Request for Quotation" , "Purchase Orders" , "Request to Store" , "Material Issuance" ])
			? 'data-target="#jump-to-status-modal" data-toggle="modal"' : '' !!}>
			<i class="mdi mdi-information-outline"></i> {{ isset($request->status) ? $request->status : 'In Preparation' }}
			@if ($stage == "Purchase Orders" )
			{{-- Zoho Books integration disabled — not in use.
			@if (trim($request->zoho_status) != "")
			<small class="text-muted"><i class="mdi mdi-pan-right"></i>
				ZOHO Status: {{ $request->zoho_status }}
			</small>
			@endif
			--}}
			@else
			<small class="text-muted"><i class="mdi mdi-pan-right"></i>
				{{ in_array($request->status, ["Goods Accepted",
				"Items Issued Out"]) ? (in_array($request->approval_status, ['Fully', 'Partially']) ? $request->approval_status
				: '' ) : '' }}
			</small>
			@endif

		</small>
		@if ((isset($request->status) && $request->status == "In Preparation" || !isset($request->status)))
		<button class="btn btn-default text-primary float-right save-details-form btn-sm" data-type="save-details">
			<i class="mdi mdi-content-save"></i> Save
		</button>
		@else
		<button class="btn btn-default text-primary float-right save-details-form hidden btn-sm" id="save-other-changes"
			data-type="save-details">
			<i class="mdi mdi-content-save"></i> Save
		</button>
		@endif

		@if (isset($request->status) && (in_array($request->request_type, ["Gate Pass"])))
		@if(trim($request->note_bearer!="") && trim($request->time_out!=""))
		<a class="btn btn-transparent text-info btn-sm float-right ml-2 btn-sm" target="_blank"
			href="{{ route('req-report-generate', ['id'=>$request->id, 'supply'=>'Gate Pass']) }}">
			<i class="mdi mdi-printer"></i> Gate Pass
		</a>
		@endif
		@endif

		@if (isset($request->status) && (in_array($request->request_type, ["Goods Return", "Material Issuance"])))
		@if(trim($request->note_bearer!="") && trim($request->time_out!=""))
		<span class="btn btn-transparent text-info btn-sm float-right ml-2 btn-sm" data-target="#Create-Gate-Pass-Modal"
			data-toggle="modal">
			<i class="mdi mdi-plus"></i> Create Gate Pass
		</span>
		@endif
		@endif
		@if(isset($request->status) && $request->in_ammendment > 0)
		<?php $isRequestIniator = $request->status == "Amendment Awaiting Approval" && $request->request_initiator = \Auth::user()->id ?>
		<div class="btn-group float-right" role="group">
			<button id="btnGroupDrop1" type="button" class="btn btn-transparent dropdown-toggle btn-sm" data-toggle="dropdown"
				aria-haspopup="true" aria-expanded="false">
				<i class="mdi mdi-file-edit text-danger"></i> Amendment {!! $isRequestIniator ? '<sup
					class="label label-danger"><i class="mdi mdi-alert-circle text-danger"></i></sup>' :'' !!}
			</button>
			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
				@if($isRequestIniator)
				<a class="dropdown-item text-success" data-toggle="modal" data-target="#approve-ammendments-details-form"
					href="#"><i class="mdi mdi-check-circle"></i> Approve Amendments</a>
				<a class="dropdown-item text-danger" data-toggle="modal" data-target="#reject-ammendment-modal" href="#"><i
						class="mdi mdi-close-circle"></i> Reject Amendment</a>
				@else
				<a class="dropdown-item save-details-form text-success" data-type="save-details" href="#"><i
						class="mdi mdi-content-save-edit"></i> Save Amendments</a>
				<a class="dropdown-item text-danger" data-toggle="modal" data-target="#reject-ammendment-modal" href="#"><i
						class="mdi mdi-close-circle"></i> Cancel Amendment</a>
				@if($request->in_ammendment > 1)
				<a class="dropdown-item save-details-form text-primary" data-type="get-requester-approval" href="#"><i
						class="mdi mdi-account-check"></i> Get Requester Approval</a>
				@endif
				@endif
			</div>
		</div>
		@endif

		@if(in_array($stage, ["Lend"]))
		@if(in_array($request->status, ["Items Issued Out", "Approval Complete", "Goods Accepted"]))
		<button class="btn btn-default text-info float-right btn-sm" data-target="#Create-Material-Issuance-Modal"
			data-toggle="modal">
			<i class="mdi mdi-archive-arrow-up-outline"></i> Issue Items
		</button>
		@endif
		@if(in_array($request->status, ["Items Issued Out", "Goods Accepted"]))
		<button class="btn btn-default text-success float-right btn-sm" data-target="#Create-Goods-Receipt-Modal"
			data-toggle="modal">
			<i class="mdi mdi-file-move"></i> Accept Items
		</button>
		@endif
		@endif

		@if(in_array($stage, ["Loan"]))
		@if(in_array($request->status, ["Items Issued Out", "Goods Accepted", "Approval Complete"]))
		<button class="btn btn-default text-success float-right btn-sm" data-target="#Create-Goods-Receipt-Modal"
			data-toggle="modal">
			<i class="mdi mdi-file-move"></i> Receive Items
		</button>
		@endif
		@if(in_array($request->status, ["Items Issued Out", "Goods Accepted"]))
		<button class="btn btn-default text-info float-right btn-sm" data-target="#Create-Material-Issuance-Modal"
			data-toggle="modal">
			<i class="mdi mdi-archive-arrow-up-outline"></i> Return Items
		</button>
		@endif
		@endif

		@if($stage == "Material Issuance")
		@if (isset($request->status) && in_array($request->status, array("Awaiting User Reception")))
		<button class="btn btn-default text-info float-right btn-sm" data-target="#issue-items-otp-modal"
			data-toggle="modal">
			<i class="mdi mdi-archive-arrow-up-outline"></i> Issue Items
		</button>
		@endif
		@if(isset($request->status) && $request->status == "In Preparation")
		<button class="btn btn-default text-success float-right save-details-form" data-type="notify-user">
			<i class="mdi mdi-bell-ring"></i> Notify User
		</button>
		@endif
		@if($request->status == 'Completed' && $isInventoryStoreManager)
		<button class="btn btn-default text-success float-right btn-sm" data-target="#reverse-entity-action"
			data-toggle="modal">
			<i class="mdi mdi-undo"></i> Reverse Material Issuance
		</button>
		@endif
		@endif
		@if ($request->request_type == "Purchase Orders")
		@if (in_array($request->status, array("Approval Complete", "Purchase Order Sent", "Goods Accepted")))
		@if($isInventoryStoreManager)
		<?php
			$hasMI = issue_received_complete($request->id);
		?>

		@if($hasMI['items'] < $hasMI['all']) <span class="btn btn-default text-dark float-right btn-sm"
			data-target="#Create-Goods-Receipt-Modal" data-toggle="modal">
			<i class="mdi mdi-file-move"></i> Create Goods Receipt
			</span>
			<span class="btn btn-default text-warning float-right btn-sm" data-target="#Create-Goods-Return-Modal"
				data-toggle="modal">
				<i class="mdi mdi-file-undo"></i> Create Return Note
			</span>
			@endif
			@endif

			@if($request->bank_notified == 0 && isETCU())
			<span class="btn btn-default text-primary float-right btn-sm" data-target="#Send-Bank-Notification"
				data-toggle="modal">
				<i class="mdi mdi-bank"></i> Upload Bank Documents
			</span>
			@endif

			@if($request->bank_notified == 1 && $request->supplier_bank_notification == 0 && isETCU())
			<span class="btn btn-default text-primary float-right btn-sm" data-target="#Upload-Bank-Confirmation"
				data-toggle="modal">
				<i class="mdi mdi-bank"></i> Upload Bank Confirmation
			</span>
			@endif

			@if($request->status == "Approval Complete")
			<button class="btn btn-default text-dark float-right save-details-form btn-sm"
				data-type="send-purchase-order">
				<i class="mdi mdi-send"></i> Send Purchase Order
			</button>
			@endif
			@if (isset($request->status) && $isInventoryProcurement)
			<button class="btn btn-default text-success float-right save-details-form btn-sm" data-type="mark-as-completed"
				data-alert="Are you sure you want to proceed?">
				<i class="mdi mdi-content-save"></i> Mark as Complete
			</button>
			@endif
			@endif
			@endif
			@if ($stage == "Request to Store")
			@if($request->status == 'Approval Complete')
			<?php
				$hasMIForComplete = issue_received_complete($request->id);
			?>
			@if($hasMIForComplete['items'] >= $hasMIForComplete['all'] && $hasMIForComplete['all'] > 0)
			<button class="btn btn-default text-success float-right save-details-form btn-sm" data-type="mark-as-completed"
				data-alert="Are you sure you want to proceed?">
				<i class="mdi mdi-content-save"></i> Mark as Complete
			</button>
			@endif
			@endif
			@endif
			@if ($request->request_type == "Goods Receipt")
			@if (in_array($request->status,array("Approval Complete", "Partially Fulfilled")))
			@if($request->requester_confirmation == 1)
			<button class="btn btn-default text-dark float-right btn-sm" data-target="#accept-goods-otp-modal"
				data-toggle="modal">
				<i class="mdi mdi-package-variant-closed"></i> Accept Goods
			</button>
			@else
			@if($request->issue_to == \Auth::user()->id)
			<a id="confirm-requester-approval-link"
				data-link="{{ route('requester_verification_confirmation', ['id'=>$request->id]) }}"
				class="btn btn-default text-success btn-sm"><i class="mdi mdi-check"></i> Confirm Verified Items</a>
			@else
			<button class="btn btn-transparent text-muted float-right btn-sm" disables>
				<i class="mdi mdi-alert"></i> Awaiting Requester Confirmation
			</button>
			@endif
			<button class="btn btn-default text-dark float-right btn-sm" data-target="#accept-goods-otp-modal"
				data-toggle="modal">
				<i class="mdi mdi-package-variant-closed"></i> Accept Goods
			</button>
			@endif
			@endif
			@if($request->status == "Goods Accepted")
			@if(!in_array($parentIsLoanLend->request_type, ['Loan', 'Lend']))
			<button class="btn btn-default text-primary float-right btn-sm" data-target="#send-to-finance-modal"
				data-toggle="modal">
				<i class="mdi mdi-file-account-outline"></i> Send to Finance
			</button>
			@else
			<button class="btn btn-default text-success float-right save-details-form btn-sm" data-type="mark-as-completed"
				data-alert="Are you sure you want to proceed?">
				<i class="mdi mdi-content-save"></i> Mark as Complete
			</button>
			@endif
			@endif
			@if($request->status == "Awaiting Finance Approval" && $isInventoryFinance)
			<button class="btn btn-default text-primary float-right btn-sm" data-target="#finance-department-modal"
				data-toggle="modal">
				<i class="mdi mdi-content-save"></i> Mark as Complete
			</button>
			@endif

			@if(in_array($request->status, ['Completed', 'Goods Accepted', 'Awaiting Finance Approval']) &&
			$isInventoryStoreManager)
			<button class="btn btn-default text-success float-right btn-sm" data-target="#reverse-entity-action"
				data-toggle="modal">
				<i class="mdi mdi-undo"></i> Reverse Goods Receipt
			</button>
			@endif
			@endif

			@if ($request->request_type == "Goods Return")
			@if (in_array($request->status,array("Approval Complete")))
			<button class="btn btn-default text-dark float-right btn-sm" data-target="#return-goods-to-supplier-modal"
				data-toggle="modal">
				<i class="mdi mdi-package-variant-closed"></i> Return Goods
			</button>
			@endif
			@endif
			@if(($approvals->count() ?? 0) > 0 && (count($normalItems) > 0))
			@php
				$pendingEntityApprovalsCount = isset($request->id)
					? \App\EntityApproval::where('model', $stage)->where('model_id', $request->id)->where('status', 'Pending')->count()
					: 0;
				$canSendForApproval = in_array($stage, ["Purchase Request", "Request to Store", "Purchase Orders", "Gate Pass", "Loan", "Lend"])
					&& (
						$request->status == "In Preparation"
						|| (in_array($request->status, ["Awaiting Approval", "Partially Approved"]) && $pendingEntityApprovalsCount === 0)
					);
			@endphp
			@if ($canSendForApproval)
			@if($request->is_lab_kit == 0)
			<button class="btn btn-default text-dark float-right save-details-form btn-sm" data-type="get-approval-details">
				<i class="mdi mdi-account-check"></i> Get Approval
			</button>
			@else
			<button class="btn btn-default text-success float-right save-details-form btn-sm" data-type="mark-as-completed"
				data-alert="Are you sure you want to proceed?">
				<i class="mdi mdi-content-save"></i> Mark Lab Kit as Complete
			</button>
			@endif
			@endif

			@if ($stage == "Request for Quotation")
			@php
				$rfqHasAwardedQuotes = \App\SupplierQuote::where('request_id', $request->id)->where('is_awarded', 1)->exists();
				$rfqHasSuppliers = $request->supplier_rfqs()->count() > 0;
				$rfqHasEmailBody = trim($request->email_body ?? '') !== '';
			@endphp

			@if ($request->status == "Approval Complete" && $isInventoryProcurement && $rfqHasAwardedQuotes)
			<button class="btn btn-default text-success float-right save-details-form btn-sm" data-type="mark-as-completed"
				data-alert="Are you sure you want to proceed?">
				<i class="mdi mdi-content-save"></i> Mark as Complete
			</button>
			<button class="btn btn-default text-dark float-right btn-sm" data-target="#create-po-confirmation-modal"
				data-toggle="modal">
				<i class="mdi mdi-file-move"></i> Create Purchase Order
			</button>
			@endif

			@if ($request->status == "Awarded" && $rfqHasAwardedQuotes && $isInventoryProcurement)
			<button class="btn btn-default text-dark float-right save-details-form btn-sm" data-type="get-approval-details">
				<i class="mdi mdi-account-check"></i> Get Approval
			</button>
			@endif

			@if ($rfqHasSuppliers && $rfqHasEmailBody && $isInventoryProcurement && !in_array($request->status, ["Awarded", "Approval Complete", "Rejected"]))
			<button class="btn btn-default text-dark float-right save-details-form btn-sm" data-type="send-rfq-details">
				<i class="mdi mdi-email-send"></i> Send Out RFQS
			</button>
			@endif

			@if($rfqHasEmailBody)
			<span class="btn btn-default text-primary float-right btn-sm" data-target="#Send-RFQ-modal" data-toggle="modal">
				<i class="fas fa-eye"></i> Preview Email Body
			</span>
			@else
			<span class="btn btn-default text-info float-right btn-sm" data-target="#Send-RFQ-modal" data-toggle="modal">
				<i class="fas fa-plus"></i> Add Email Body
			</span>
			@endif
			@endif

			@if ($stage == "Request for Quotation" && $request->supplier_rfqs()->count() > 0)
			@if (!in_array($request->status, ["Approval Complete", "Rejected"]) &&
			$isInventoryProcurement && trim($request->email_body) != "")
			<button class="btn btn-default text-dark float-right save-details-form btn-sm" data-type="send-rfq-details">
				<i class="mdi mdi-email-send"></i> Send Out RFQS
			</button>
			@endif
			@if (in_array($request->status, ["Awarded", "RFQs sent out"]))
			<button class="btn btn-default text-dark float-right save-details-form btn-sm" data-type="get-approval-details">
				<i class="mdi mdi-account-check"></i> Get Approval
			</button>
			@endif
			@if ($request->status == "Approval Complete" && $isInventoryProcurement)
			<?php $approvalStatus = $request->approvals(); ?>
			<button class="btn btn-default text-dark float-right btn-sm" data-target="#create-po-confirmation-modal"
				data-toggle="modal">
				<i class="mdi mdi-file-move"></i> Create Purchase Order
			</button>
			@if (isset($request->status) && $isInventoryProcurement)
			<button class="btn btn-default text-success float-right save-details-form btn-sm" data-type="mark-as-completed"
				data-alert="Are you sure you want to proceed?">
				<i class="mdi mdi-content-save"></i> Mark as Complete
			</button>
			@endif
			@endif
			@endif
			@if (in_array($request->status, ["Approval Complete", "Items Issued Out", "Completed"]) && $stage === "Request to Store")
			<?php
						$hasMI = issue_received_complete($request->id);
					?>
			@if($hasMI['items'] < $hasMI['all'])
			<button class="btn btn-default text-info float-right btn-sm"
				data-toggle="modal" data-target="#Create-Material-Issuance-Modal">
				<i class="mdi mdi-package-variant-closed"></i> Create Material Issuance
			</button>
			@endif
			@endif
			@if ($request->status == "Approval Complete" && $stage=="Purchase Request" &&
				$isInventoryProcurement)
				<?php
						$hasAnRFQ = \App\RequestEntity::where('request_type', 'Request for Quotation')->where('parent_material_requisition', $request->id)->first();
						$hasAnPO = \App\RequestEntity::where('request_type', 'Purchase Orders')->where('parent_material_requisition', $request->id)->first();
						$rfqIsIncomplete = isset($hasAnRFQ->id)
							&& ! \App\RequestEntityItem::where('request_id', $hasAnRFQ->id)->exists();
					?>
				@if((!isset($hasAnRFQ->id) || $rfqIsIncomplete) && !isset($hasAnPO->id))
				<button class="btn btn-default text-success float-right save-details-form btn-sm"
					data-type="create-rfq-from-material-requisition">
					<i class="mdi mdi-text-box-plus-outline"></i> {{ isETCU() ? 'Create RFQ' : 'Send to Procurement' }}
				</button>
				{{-- <button class="btn btn-default text-primary float-right btn-sm" data-target="#create-lpo-from-mr-modal"
					data-toggle="modal">
					<i class="mdi mdi-text-box-plus-outline"></i> Create LPO
				</button> --}}
				@endif
				@endif
				@endif
	</h3>
	<br>
	@if($hasExceeded && isset($request->status) && in_array($request->status, ['In Preparation', 'Awaiting Approval']))
	<div class="alert alert-danger" style="font-size: 18px; display:flex; align-items: center; justify-content: center">
		<i class="mdi mdi-alert" style="font-size: 24px"></i> &nbsp;&nbsp;Some items in your request exceed the available
		quantity. Please adjust the requested quantities or create a new Purchase Request.
	</div>
	@endif
	{{-- Zoho Books integration disabled — not in use.
	@php($zerrors = json_decode(trim($request->errors ?? "") == "" ? '[]' : $request->errors, true))
	@if(count(empty($request->errors ?? null) ? [] : $zerrors) > 0)
	<div class="alert alert-danger" style="font-size: 12px;">
		<h6><i class="mdi mdi-alert"></i> Integration Error:</h6>
		<br>
		<ul>
			@foreach ($zerrors as $err)
			<li>{{ $err }}</li>
			@endforeach
		</ul>
		<div class="pv-2 pl-0 text-small text-primary">
			<a href="{{ route('recreate-purchase-order', $request->id ?? 0) }}"><i class="fas fa-sync"></i> Retry creating the
				Purchase Order on Zoho...</a>
		</div>
	</div>
	@endif
	--}}
	<form id="details-form" class="bg-light" method="POST" enctype="multipart/form-data"
		action="{{ route('save-request-details', ['stage'=>$stage, 'id'=>$request->id ?? 0]) }}"
		style="clear: both !important">
		@csrf
		<div class="card tab-card">
			<div class="card-header tab-card-header">
				<ul class="nav nav-tabs card-header-tabs" style="font-size: 14px" id="Request-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="General-tab" data-toggle="tab" href="#General" role="tab"
							aria-controls="General" aria-selected="true">General</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Items-tab" data-toggle="tab" href="#Items" role="tab" aria-controls="Items"
							aria-selected="true">
							Items
							<small class="badge badge-pill badge-secondary">{{ count($normalItems) }}</small>
						</a>
					</li>
					@if (in_array($stage, array("Goods Receipt", "Material Issuance")))
					{{-- <li class="nav-item hide-em">
						<a class="nav-link" id="Received-Items-tab" data-toggle="tab" href="#Received-Items" role="tab"
							aria-controls="Received-Items" aria-selected="true">
							{{ $stage == "Goods Receipt" ? 'Received' : 'Issued' }} Items
							<small class="badge badge-pill badge-secondary">{{ count($otherItems) }}</small>
						</a>
					</li> --}}
					@endif
					@if ($stage == "Request for Quotation")
					<li class="nav-item">
						<a class="nav-link" id="Suppliers-tab" data-toggle="tab" href="#Suppliers" role="tab"
							aria-controls="Suppliers" aria-selected="true">Suppliers</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Quotes-tab" data-toggle="tab" href="#Quotes" role="tab" aria-controls="Quotes"
							aria-selected="true">Quotes</a>
					</li>
					@endif
					<li class="nav-item">
						<a class="nav-link" id="Approvals-tab" data-toggle="tab" href="#Approvals" role="tab"
							aria-controls="Approvals" aria-selected="true">
							Approvals
							@if (isset($request->status) && in_array($request->status, ['Awaiting Approval', 'Partially Approved']))
							<small class="badge badge-pill badge-danger my-small-text"><i class="mdi mdi-bell-ring bell"></i></small>
							@endif
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Notes-tab" data-toggle="tab" href="#Notes" role="tab" aria-controls="Notes"
							aria-selected="true">
							Notes
							<?php $notesList = isset($request->status) ? $request->notes() : array() ; ?>
							<small class="badge badge-pill badge-secondary">{{ count($notesList) ?? 0 }}</small>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Attachments-tab" data-toggle="tab" href="#Attachments" role="tab"
							aria-controls="Attachments" aria-selected="true">
							Attachments
							<?php $attachmentList = isset($request->status) ? $request->attachments() : array(); ?>
							<small class="badge badge-pill badge-secondary">{{ count($attachmentList) ?? 0 }}</small>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Requisition-tab" data-toggle="tab" href="#Requisition" role="tab"
							aria-controls="Requisition" aria-selected="true">
							<i class="mdi mdi-sitemap"></i> Requisition Flow
						</a>
					</li>
					@if($stage == "Goods Receipt")
					<li class="nav-item">
						<a class="nav-link" id="Ratings-tab" data-toggle="tab" href="#Ratings" role="tab" aria-controls="Ratings"
							aria-selected="true">
							<i class="mdi mdi-star"></i> Rate Supplier
						</a>
					</li>
					@endif
				</ul>
			</div>
			@if (in_array($request->request_type, ["Purchase Orders", "Goods Receipt", "Goods Return"]))
			<?php $supplier = \App\Supplier::find($request->supplier_id) ?>
			@endif
			<div class="tab-content" id="Request-tabs-content">
				@if($stage == "Goods Receipt")
				<div class="tab-pane fade p-3" id="Ratings" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title">
						Ratings
						@if($isInventoryProcurement)
						<div class="btn btn-sm text-info float-right" data-target="#update-supplier-criteria-rating-modal"
							data-toggle="modal">
							<i class="mdi mdi-star"></i> Rate Goods Receipt
						</div>
						@endif
					</h5>
					<div class="row">
						<div class="col-md-12">
							@foreach (getSupplierRatingCriteria() as $gSRC)
							<?php
								$score = $ratingScores[$gSRC->id] ?? 0;
								$scorePerc = $score/$gSRC->max_score*100;
								$mRatingColor = supplierRatingColorFromScore($scorePerc);

								$guideTitle = '';

								foreach($gSRC->guides as $gd){
									if(floatval($score) >= $gd->lower_value && floatval($score) <= $gd->upper_value){
										$guideTitle = $gd->title;
									}
								}
							?>
							<div class="mt-1 mb-1">
								<div class="pt-1 pb-1" style="clear: both">
									<h6>{{ $gSRC->title }} <small class="badge badge-pill badge-primary">{{ $guideTitle }}</small></h6>
								</div>
								<div class="progress">
									<div class="progress-bar progress-bar-striped {{ $mRatingColor }}" role="progressbar"
										style="width: {{ $scorePerc }}%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<div class="pt-1 pb-1 text-muted">
									<small>{{ number_format($score,1) }} out of {{ number_format($gSRC->max_score,1) }}</small>
								</div>
							</div>
							@endforeach
						</div>
					</div>
				</div>
				@endif
				<div class="tab-pane fade p-3" id="Attachments" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1">Attachments
						<span class="btn btn-default text-primary float-right btn-sm" data-target="#add-attachment-modal"
							data-toggle="modal">
							<i class="mdi mdi-plus"></i> Attachment
						</span>
						<span class="btn btn-default text-danger float-right delete-this-row mr-2 btn-sm"
							data-holder="#attachment-items">
							<i class="mdi mdi-delete"></i> Remove
						</span>
					</h5>
					<div class="row no-gutters">
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
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
								<tbody id="attachment-items" class="notes-and-attachments"
									data-attachments='{{ json_encode($attachmentList) }}'></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Notes" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1">Notes
						<span class="btn btn-default text-primary btn-sm float-right" data-target="#add-note-modal"
							data-toggle="modal">
							<i class="mdi mdi-plus"></i> Note
						</span>
						<span class="btn btn-default text-danger btn-sm float-right delete-this-row mr-2" data-holder="#note-items">
							<i class="mdi mdi-delete"></i> Remove
						</span>
					</h5>
					<div class="row no-gutters">
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
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
					<h5 class="card-title mb-3 mt-1">
						Requisition Document Flow
						@if(isset($request->id))
						<div class="float-right pull-right {{ $stage == " Purchase Request" ? "hidden hide" : "" }}"
							id="downloadable-link">
							@if($request->downloadable_link == "")
							<a class="btn btn-sm btn-transparent text-primary"
								href="{{ route('req-report-generate-pdf', ['id'=>$request->id]) }}">
								<i class="mdi mdi-file-refresh"></i> Generate PDF
							</a>
							@elseif($request->downloadable_link == "pending")
							<span class="btn btn-sm btn-transparent text-warning">
								<i class="fas fa-spin fa-spinner"></i> Creating PDF...
							</span>
							@else
							<a class="btn btn-sm btn-transparent text-success mr-2" target="_blank" download
								href="{{ $request->downloadable_link }}">
								<i class="mdi mdi-file-pdf-outline"></i> Download
							</a>
							<a class="btn btn-sm btn-transparent text-default"
								href="{{ route('req-report-generate-pdf', ['id'=>$request->id]) }}">
								<i class="mdi mdi-sync"></i>
							</a>
							@endif
						</div>
						@endif
					</h5>
					<div id="document-flow-holder">
						<?php $arrays = getDocumentTemplates(); ?>
						{{--
						<pre>{{ json_encode($documentFlow, JSON_PRETTY_PRINT) }}</pre> --}}
						@foreach($documentFlow as $stName=>$docs)
						<span class="flow-doc-holder">
							<h6 style="font-size: 16px">{{ $stName }}</h6>
							<br>
							@foreach($docs as $doc)
							@if($doc->delete == 0)
							<div
								class="flow-doc {{ $stName==$stage && $doc->id == $request->id ? 'is_current '.($doc->delete == 1 ? 'is_deleted' : '') :'' }}">
								<div class="header">
									<header>{{ $doc->request_code }}{{ $doc->ammendment == 1 ? '' : 'v'.$doc->ammendment }} - <small
											class="text-muted">{{ $doc->status }} {{ $doc->delete == 1 ? 'DELETED' : '' }}</small>
										@if(isset($arrays[$stName]))
										<a target="_blank" href="{{ route('req-report-generate', ['id'=>$doc->id]) }}"
											class="pull-right float-right">
											<i class="mdi mdi-eye"></i>
										</a>
										@if ($doc->request_type == "Goods Receipt")
										<a title="Supply Inspection Form" data-toggle="tooltip" target="_blank"
											href="{{ route('req-report-generate', ['id'=>$doc->id, 'supply'=>'Supply Inspection Form']) }}"
											class="pull-right float-right">
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
															$docTypes = ["GR - Invoice", "GR - Delivery Note", "GR - Job Card"];
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
											{!! $attachment ? '<i class="text-success mdi mdi-check-circle"></i>' : '<i
												class="text-muted mdi mdi-close-circle-outline"></i>' !!}
										</div>
										@endforeach
									</div>
								</div>
								@endif
								<div class="footer row no-gutters">
									<div class="col-6">
										<?php $parentReq = \App\RequestEntity::find($doc->parent_request_id) ?>
										@if (isset($parentReq->id))
										<a
											href="{{ route('view-request-details', ['stage'=>$doc->parent_request, 'id'=>$doc->parent_request_id]) }}">
											<i class="mdi mdi-clipboard-arrow-right"></i> Source - {{ $parentReq->request_code }}
										</a>
										@else
										<span class="text-muted"><i class="mdi mdi-clipboard-arrow-right"></i> Source - </span>
										@endif
									</div>
									<div class="col-6">
										<a
											href="{{ route('view-request-details', ['stage'=>$doc->request_type, 'id'=>$doc->id]) }}">View</a>
									</div>
								</div>
							</div>
							@endif
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
						@if(isset($request->id) && \Illuminate\Support\Str::isUuid((string) $request->id))
						<a href="{{ route('download-request-items', ['id'=>$request->id]) }}"
							class="btn btn-transparent btn-sm text-primary"><i class="mdi mdi-download"></i> Download</a>
						<a href="{{ route('download-request-items', ['id'=>$request->id, 'isPDF'=>'pdf']) }}"
							class="btn btn-transparent btn-sm text-danger"><i class="mdi mdi-download"></i> Download PDF</a>
						@endif
						@if (!isset($request->status) || in_array($request->status, ["In Preparation", "RFQs sent out", "Receiving Quotes", "Awaiting Approval", "Partially Approved"]))

						@if(in_array($stage, ["Purchase Request", "Request to Store", "Request for Quotation", "Gate Pass", "Loan",
						"Lend"]) || ($stage == 'Purchase Orders' && intval($request->ammendment) > 1))
						<span class="btn btn-default text-primary btn-sm float-right add-item-row"><i class="mdi mdi-plus"></i>
							Add</span>
						<span class="btn btn-default text-danger btn-sm float-right delete-item-row mr-2"><i
								class="mdi mdi-delete"></i> Remove</span>
						@endif
						@endif
						@if ($request->request_type == "Purchase Orders" && $isInventoryProcurement)
						@if (in_array($request->status, array("Approval Complete", "Purchase Order Sent")))
						<span class="btn btn-default text-dark float-right" data-target="#Make-Amendment-Modal" data-toggle="modal">
							<i class="mdi mdi-file-move"></i> Make Amendment
						</span>
						@endif
						@endif
						@if(in_array($stage, ["Gate Pass"]))
						<span class="btn btn-default text-dark btn-sm" data-target="#add-sub-category-modal" data-toggle="modal">
							<i class="mdi mdi-plus"></i> Add Gate Pass Item
						</span>
						@endif
					</h5>
					@php($storesFrozen = areThereFrozenStores())
					@if (count($storesFrozen) > 0)
					<div style="margin:10px 0px">
						<div class="alert alert-warning">
							<i class="mdi mdi-information"></i> Please note that store{{ count($storesFrozen) > 1 ? 's' : '' }} <em>{{
								implode(',', $storesFrozen) }}</em> {{ count($storesFrozen) > 1 ? 'are' : 'is' }}
							frozen for stock taking.
						</div>
					</div>
					@endif

					<div class="table-responsive">
						<table
							class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
							<thead>
								@if(in_array($stage,["Lend", "Loan"]) && in_array($request->status, ['Items Issued Out', 'Goods
								Accepted', 'Completed']))
								<tr>
									<th colspan="6"></th>
									<th colspan="5" style="background-color: rgb(218, 255, 250); text-align: center; padding: 5px">
										Compensation</th>
								</tr>
								@endif
								<tr>
									<th style="width: 32px"></th>
									<th>#</th>
									<th nowrap>Item</th>
									<th nowrap>Brand</th>
									<th nowrap>Description/Location</th>
									@if(in_array($stage, ['Request to Store', 'Material Issuance']))
									<th nowrap>Issuing UoM</th>
									@else
									<th nowrap>UoM</th>
									@endif
									@if(in_array($stage, array("Request to Store", "Purchase Request", "Request for Quotation", "Loan",
									"Lend")))
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
									@if($stage == "Purchase Request")
									<th nowrap>Delivery Date</th>
									@endif
									@if(in_array($stage,["Request for Quotation", "Purchase Orders"]))
									<th nowrap>Shipping Mode</th>
									@endif
									@if(!in_array($stage,["Purchase Request", "Gate Pass", "Lend", "Loan", "Request to Store"]))
									<th nowrap>Store</th>
									<th nowrap>Slot</th>
									<th nowrap>Currency</th>
									<th nowrap>Net Value</th>
									@endif
									@if(in_array($stage,["Lend", "Loan"]) && in_array($request->status, ['Items Issued Out', 'Goods
									Accepted', 'Completed']))
									<th nowrap>Kind</th>
									<th nowrap>UoM</th>
									<th nowrap>Quantity</th>
									<th nowrap>Value</th>
									<th nowrap>Remark</th>
									@endif
								</tr>
							</thead>
							<tbody id="req-items">
								<?php $itemsToName = array(); ?>
								@foreach ($normalItems as $req_item)
								<?php $pendingQ = $req_item->quantity - $req_item->pending(); ?>
								<tr class="item-row">
									<td class="text-center align-middle">
										<input type="checkbox" class="row-select-checkbox" title="Select row" />
									</td>
									<td class="item-id">
										{{ $loop->iteration }}
										<input type="hidden" name="items[req_item_id][]" value="{{ $req_item->id }}" />
									</td>
									<td>
										<div class="form-group">
											<?php
												$readonly = isset($request->status) && $request->status == "In Preparation" || !isset($request->status) ? false : true;
											?>
											<select name="items[item_id][]" style="min-width: 200px; font-size: 12px"
												class="form-control selected-item" data-selected="{{ $req_item->inventory_sub_category_id }}"
												data-account="{{ $req_item->item_account_id }}" placeholder="Please select inventory item..." {{
												$readonly ? "disabled" : "" }}>
												<option value="{{ $req_item->inventory_sub_category_id }}" selected="selected">{{
													$req_item->item_name }}</option>
											</select>

											@php($hiddenAccount = ! isETCU() || in_array($stage, ['Request to Store', 'Material Issuance', 'Goods Receipt']))

											@if (! $hiddenAccount)
											<div>
												<div style="padding:3px 2px">Account</div>
												<select {{ $readonly ? "disabled" : "" }} name="items[item_account_id][]"
													style="min-width: 200px; font-size: 12px; margin-top: 5px" class="form-control"
													placeholder="Select Account..." required>
													<option value="">Select Account...</option>
													@foreach ($accounts as $acc)
													<option value="{{ $acc->account_id }}" {{ $req_item->item_account_id == $acc->account_id ?
														"selected" : "" }}><small>({{ clear_underscore($acc->type) }}) {{ $acc->name }}</small>
													</option>
													@endforeach
												</select>
											</div>
											@endif
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="text" class="form-control" name="items[brand][]" style="min-width: 110px; max-width: 130px"
												value="{{ $req_item->brand }}" placeholder="Brand..." />
											<div class="d-none" style="display:none">
												<select name="items[item_brand_id][]" class="form-control selected-item-brand d-none"
													data-selected="{{ $req_item->item_brand_id }}" placeholder="Select Item Brand..." {{ $readonly
													&& $request->in_ammendment == 0 ? "disabled" : "" }}></select>
											</div>
										</div>
									</td>
									<td nowrap>
										<div class="form-group" style="position: relative">
											<span class="btn btn-default btn-sm text-primary"
												style="position: absolute; top: 0px; right: 0px; z-index: 1"
												data-target="#req-location-selector-modal" data-toggle="modal">
												<i class="mdi mdi-map-marker"></i>
											</span>
											<textarea class="form-control item-description" name="items[comments][]" value=""
												style="min-width: 140px; max-width: 160px; z-index:0" {{ $readonly ? "readonly" : ""
												}}>{{ $req_item->comments }}</textarea>
										</div>
									</td>
									@if(in_array($stage, ['Request to Store', 'Material Issuance']))
									<td nowrap>
										<div class="form-group">
											{{-- {!! "
											<pre>".json_encode($req_item, JSON_PRETTY_PRINT)."</pre>" !!} --}}
											<?php
															$existsCon = getUoMConverstion(1, $req_item->unit_type, $req_item->secondary_unit_type);
														?>
											<?php
															$itemUoM = array_values(array_filter(array_unique([
																$req_item->uom,
																$req_item->unit_type,
																$req_item->secondary_unit_type,
															])));
															if($existsCon == false){
																$itemUoM = array_values(array_filter(array_unique([
																	$req_item->uom,
																	$req_item->unit_type,
																])));
															}
														?>
											@if($existsCon == false)
											<small class="text-danger"><sup>*UoM conversions not configured</sup></small>
											@endif
											@if($readonly && (int) ($request->in_ammendment ?? 0) === 0)
											<span class="form-control" style="min-width: 140px">{{ $req_item->uom ?: ($req_item->unit_type ?: '-') }}</span>
											<input type="hidden" name="items[uom][]" value="{{ $req_item->uom ?: $req_item->unit_type }}" />
											@else
											<select name="items[uom][]" class="form-control selected-item-uom" style="min-width: 140px"
												data-selected="{{ $req_item->uom }}" placeholder="Select Item UoM...">
												<option value=""></option>
												@foreach ($itemUoM as $uom)
												<option value="{{ $uom }}" {{ $uom==$req_item->uom ? 'selected' : '' }}>{{ $uom }}</option>
												@endforeach
											</select>
											@endif
										</div>
									</td>
									@else
									<td>
										<div class="form-group">
											<?php
												$itemUoM = array_values(array_filter(array_unique([
													$req_item->uom,
													$req_item->unit_type,
												])));
											?>
											@if($readonly && (int) ($request->in_ammendment ?? 0) === 0)
											<span class="form-control" style="min-width: 140px">{{ $req_item->uom ?: ($req_item->unit_type ?: '-') }}</span>
											<input type="hidden" name="items[uom][]" value="{{ $req_item->uom ?: $req_item->unit_type }}" />
											@else
											<select name="items[uom][]" class="form-control selected-item-uom" style="min-width: 140px"
												data-selected="{{ $req_item->uom }}" placeholder="Select Item UoM...">
												<option value=""></option>
												@foreach ($itemUoM as $uom)
												<option value="{{ $uom }}" {{ $uom==$req_item->uom ? 'selected' :'' }}>{{ $uom }}</option>
												@endforeach
											</select>
											@endif
										</div>
									</td>
									@endif
									@if(in_array($stage, array("Request to Store", "Purchase Request", "Request for Quotation", "Loan",
									"Lend")))
									<td nowrap>
										<div class="form-group">
											<span class="form-control open-quantity" style="min-width: 70px; max-width: 80px">0.00</span>
											<input type="hidden" min="0.00" name="items[open_quantity][]" style="min-width: 70px"
												class="form-control open_quantity" placeholder="Open Quantity..." />
										</div>
									</td>
									@endif
									@if($stage == "Goods Receipt")
									<td>
										<div class="form-group">
											<input type="number" min="0.00" name="items[received_quantity][]"
												value="{{ $req_item->quantity }}" style="min-width: 100px" step="any"
												class="form-control received-user-quantity" placeholder="Received Quantity..."
												readonly="true" />
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="date" name="items[expiry][]" value="{{ $req_item->gr_expiry }}"
												style="min-width: 100px" class="form-control expiry-user-quantity" placeholder="Expiry Date..."
												{!! in_array($request->status,array("Approval Complete", "Partially Fulfilled")) ? '' :
											'readonly="true"' !!} />
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="date" name="items[date_of_manufacture][]"
												value="{{ $req_item->date_of_manufacture }}" style="min-width: 100px"
												class="form-control date-of-manufacture" placeholder="Date of Manufacture..." {!!
												in_array($request->status,array("Approval Complete", "Partially Fulfilled")) ? '' :
											'readonly="true"' !!} />
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="text" name="items[lot_no][]" value="{{ $req_item->lot_no }}" style="min-width: 100px"
												class="form-control user-lot-no" placeholder="Lot Number..." {!!
												in_array($request->status,array("Approval Complete", "Partially Fulfilled", "Awaiting User
											Reception")) ? '' : 'readonly="true"' !!} />
										</div>
									</td>
									@else
									<td>
										@php($notifyQuantityChange = in_array($stage,["Request for Quotation", "Purchase Orders"]) ?
										'notify-item-change' : '')
										<div class="form-group">
											<input type="number" min="0.00" data-item="{{ $req_item->sub_category->name }}"
												name="items[quantity][]" data-value="{{ $req_item->quantity }}"
												value="{{ $req_item->quantity }}" step="any" style="min-width: 70px; max-width: 90px"
												class="form-control user-quantity {{ $notifyQuantityChange }}" placeholder="Quantity..." {!!
												isset($request->status) && $request->status == "In Preparation" || !isset($request->status)
											? '' : 'readonly="true"' !!} {!! $stage == "Material Issuance" ? 'readonly="true"' : '' !!}
											required />
										</div>
										<div class="mt-1 item-change-reason-div form-group">
											<textarea class="form-control form-control-sm" name="items[quantity_change_reason][]"
												placeholder="Reason for Quantity Change"></textarea>
										</div>
									</td>
									@endif
									@if(in_array($stage, array("Request to Store", "Purchase Orders")))
									<td>
										<div class="form-group">
											<input type="number" min="0.00" name="items[pending_quantity][]" value="{{ $pendingQ }}"
												style="min-width: 100px" class="form-control pending-user-quantity"
												placeholder="Pending Quantity..." readonly />
										</div>
									</td>
									@if($stage == "Purchase Orders")
									<td>
										<div class="form-group">
											<input type="number" min="0.00" name="items[issued_quantity][]"
												value="{{ $req_item->issued_received_breakdown()['Goods Receipt'] ?? 0 }}"
												style="min-width: 100px" step="any" class="form-control issued-user-quantity"
												placeholder="Issued Quantity..." readonly />
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="number" min="0.00" name="items[issued_quantity][]"
												value="{{ $req_item->issued_received_breakdown()['Goods Return'] ?? 0 }}" step="any"
												style="min-width: 100px" class="form-control issued-user-quantity"
												placeholder="Issued Quantity..." readonly />
										</div>
									</td>
									@else
									<td>
										<div class="form-group">
											<input type="number" min="0.00" name="items[issued_quantity][]" value="{{ $req_item->pending() }}"
												step="any" style="min-width: 100px" class="form-control issued-user-quantity"
												placeholder="Issued Quantity..." readonly />
										</div>
									</td>
									@endif
									@endif
									@if(in_array($stage, array("Request to Store", "Material Issuance")))
									<td>
										<div class="form-group">
											<input type="text" name="items[lot_no][]" value="{{ $req_item->lot_no }}" style="min-width: 100px"
												class="form-control user-lot-no" placeholder="Lot Number..." {!! (isset($request->status) &&
											in_array($request->status, array("In Preparation", "Approval Complete", "Partially Approved",
											"Partially Fulfilled", "Awaiting User Reception"))) || !isset($request->status) ? '' :
											'disabled="true"' !!} />
										</div>
									</td>
									<td>
										<div class="form-group">
											<select name="items[starting_sample][]" class="form-control starting-sample"
												data-selected="{{ $req_item->starting_sample }}" placeholder="Starting Sample..." {{ $readonly
												? "disabled" : "" }}>
												<option value=""></option>
												@foreach (getSamplesByWorkflow("Samples In Lab") as $sample)
												<option value="{{ $sample->id }}" {{ $sample->id == $req_item->starting_sample ? 'selected' :''
													}}>{{ $sample->sample_code }}</option>
												@endforeach
											</select>
										</div>
									</td>
									@endif
									@if($stage == "Purchase Request")
									<td nowrap>
										<div class="form-group">
											<span class="form-control delivery_date" style="min-width: 100px">{{ ($request->created_at ??
												\Carbon\Carbon::now())->addDays(7) }}</span>
											<input type="hidden" class="delivery_date" name="delivery_date"
												value="{{ ($request->created_at ?? \Carbon\Carbon::now())->addDays(7) }}" />
										</div>
										<div class="form-group">
											<input type="hidden" min="0.00" name="items[net_value][]" style="min-width: 100px"
												value="{{ $req_item->net_value }}" class="form-control items-total" {!! isset($request->status)
											&& $request->status == "In Preparation" || !isset($request->status) ? '' : 'disablned="true"' !!}
											readsonly />

											@if(in_array($stage, ['Purchase Orders']) && in_array($request->status, ['In Preparation',
											'Awaiting Approval']))
											<!-- <span class="btn btn-sm btn-transparent text-info">
																<i class="mdi mdi-cached"></i>
															</span> -->
											@endif
										</div>
									</td>
									@endif
									@if(in_array($stage,["Request for Quotation", "Purchase Orders"]))
									<td>
										<div class="form-group">
											<select name="items[mode][]" class="form-control" style="min-width: 140px" placeholder="Select Shipping Mode...">
												@foreach(getShippingMode() as $mode)
												<option value="{{ $mode }}" {{ $mode==$req_item->shipping_mode ? 'selected' : '' }}>{{ $mode }}
												</option>
												@endforeach
											</select>
										</div>
									</td>
									@endif
									@if(!in_array($stage,["Purchase Request", "Gate Pass", "Loan", "Lend", "Request to Store"]))
									<td>
										<div class="form-group">
											<?php
												$storeEditable = !isset($request->status)
													|| in_array($request->status, [
														"In Preparation",
														"Approval Complete",
														"Partially Approved",
														"Partially Fulfilled",
														"Awaiting User Reception",
													]);
												$selectedStoreId = $req_item->store_id ?: ($allStores->first()->id ?? null);
												$storeForSlots = $allStores->firstWhere('id', $selectedStoreId) ?? $allStores->first();
												$selectedSlotId = $req_item->slot_id
													?: ($storeForSlots?->slots->first()->id ?? null);
											?>
											<select name="items[store_id][]" data-selected="{{ $selectedStoreId }}" style="min-width: 180px"
												class="form-control selected-store {{ in_array($stage, ['Request to Store', 'Material Issuance', 'Goods Receipt']) ? 'trigger-save' : '' }}"
												data-placeholder="Select Store..." {!! (isset($request->status) && in_array($request->status,
												array("In Preparation", "Approval Complete", "Partially Approved", "Partially Fulfilled",
												"Awaiting User Reception"))) || !isset($request->status) ? '' : 'disabled="true"' !!}
												{{ in_array($stage, ["Goods Receipt", "Material Issuance"]) && $request->status != "Awaiting Approval" ? "required" : "" }}>
												@foreach ($allStores as $store)
												<option value="{{ $store->id }}" {{ (string) $store->id === (string) $req_item->store_id ? 'selected' : '' }}
													data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
												@endforeach
											</select>
										</div>
									</td>
									<td>
										<div class="form-group">
											<select name="items[slot_id][]" data-slot="{{ $selectedSlotId }}" style="min-width: 160px"
												class="form-control store-slots {{ in_array($stage, ['Request to Store', 'Material Issuance', 'Goods Receipt']) ? 'trigger-save' : '' }}"
												data-placeholder="Select Slot..." {!! (isset($request->status) && in_array($request->status,
												array("In Preparation", "Approval Complete", "Partially Approved", "Partially Fulfilled",
												"Awaiting User Reception"))) || !isset($request->status) ? '' : 'disabled="true"' !!}
												{{ in_array($stage, ["Goods Receipt", "Material Issuance"]) && $request->status != "Awaiting Approval" ? "required" : "" }}></select>
										</div>
									</td>
									<td>
										<div class="form-group">
											<select name="items[currency][]"
												class='form-control  {{ $stage == "Purchase Orders" ? "trigger-save" : "" }}'
												placeholder="Select Currency..." required>
												@foreach ($availableCurrencies as $p)
												<option value="{{ $p->id }}" {{ $req_item->currency == $p->id ? 'selected' : '' }}>{{ $p->name
													}}</option>
												@endforeach
											</select>
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="number" {!! isset($req_item->price) && floatval($req_item->price) > 0 ?
											'data-unit_value="'.floatval($req_item->price).'"' : '' !!} min="0.00" name="items[net_value][]"
											style="min-width: 100px" value="{{ $req_item->net_value }}" class="form-control {{ $stage !=
											"Purchase Orders" ? 'items-total' : '' }}" {!! isset($request->status) && $request->status == "In
											Preparation" || !isset($request->status) ? '' : 'disabled="true"' !!} readsonly />
										</div>
									</td>
									@endif
									@if(in_array($stage,["Lend", "Loan"]) && in_array($request->status, ['Items Issued Out', 'Goods
									Accepted', 'Completed']))
									<td>
										<div class="form-group">
											<input type="text" value="{{ $req_item->compensation_kind }}" style="min-width: 250px"
												class="form-control trigger-save" name="items[compensation_kind][]"
												placeholder="Compensation Kind..." {{ $request->status == 'Completed' ? 'readonly' : '' }} />
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="text" value="{{ $req_item->compensation_uom }}" style="min-width: 250px"
												class="form-control trigger-save" name="items[compensation_uom][]"
												placeholder="Compensation UoM..." {{ $request->status == 'Completed' ? 'readonly' : '' }} />
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="number" value="{{ $req_item->compensation_quantity }}" step="any"
												style="min-width: 250px" class="form-control trigger-save" name="items[compensation_quantity][]"
												placeholder="Compensation Quantity..." {{ $request->status == 'Completed' ? 'readonly' : '' }}
											/>
										</div>
									</td>
									<td>
										<div class="form-group">
											<input type="number" value="{{ $req_item->compensation_value }}" step="any"
												style="min-width: 250px" class="form-control trigger-save" name="items[compensation_value][]"
												placeholder="Compensation Value..." {{ $request->status == 'Completed' ? 'readonly' : '' }} />
										</div>
									</td>
									<td>
										<div class="form-group">
											<textarea style="min-width: 300px" value="{{ $req_item->compensation_remarks }}"
												class="form-control trigger-save" name="items[compensation_remarks][]"
												placeholder="Compensation Remarks..." {{
												$request->status == 'Completed' ? 'readonly' : '' }}></textarea>
										</div>
									</td>
									@endif
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					@if (in_array($stage, array("Purchase Orders")))
					<?php
								$extras = \Illuminate\Support\Facades\Schema::hasTable('request_entity_extra_charges')
									? \App\RequestEntityExtraCharge::join('module_pre_configs as mpc', function ($join) {
										$join->whereRaw('mpc.id::text = request_entity_extra_charges.currency_id::text');
									})
										->selectRaw('request_entity_extra_charges.*, mpc.name as currency')
										->where('request_id', $request->id)
										->get()
									: collect();
							?>
					<br />
					<span class="text-info extras-toggler" style="cursor: pointer"><i class="mdi mdi-chevron-down"></i> Additional
						Charges</span>
					<br>
					<div class="d-none" id="extras-div">
						<h6>
							Purchase Orders Additional Charges
							<span class="btn btn-sm btn-transparent text-primary pull-right" data-target="#extra-charge-modal"
								data-toggle="modal">
								<i class="mdi mdi-plus"></i> Charge
							</span>
						</h6>
						<hr>
						<table class="table table-sm table-striped">
							<thead>
								<th>No</th>
								<th>Name</th>
								<th>Currency</th>
								<th>Cost</th>
								<th></th>
							</thead>
							<tbody>
								@foreach ($extras as $extra)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $extra->title }}</td>
									<td>{{ $extra->currency }}</td>
									<td>{{ number_format($extra->cost ,2) }}</td>
									<td>
										<span class="btn btn-sm btn-transparent text-danger" data-id="{{ $extra->id }}" data-toggle="modal"
											data-target="#delete-extra-charge-modal">
											<i class="mdi mdi-delete"></i>
										</span>
									</td>
								</tr>
								@endforeach

								@if($extras->count() == 0)
								<tr>
									<td colspan="4"><i class="mdi mdi-alert"></i> No extras for this Purchase Order</td>
								</tr>
								@endif
							</tbody>
						</table>
					</div>
					@endif
				</div>
				@if (in_array($stage, array("Goods Receipt", "Material Issuance")))
				<div class="tab-pane fade p-3" id="Received-Items" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3">
						{{ $stage == "Goods Receipt" ? 'Received' : 'Issued' }} Items
					</h5>
					<div class="table-responsive">
						<table
							class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
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
									<td>{{ number_format($ir_item->quantity, 3) }}</td>
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
					<h5 class="card-title mb-3 mt-1">Quotes <span
							class="btn btn-transparent btn-sm text-success download-quotes-csv">
							<i class="mdi mdi-download"></i> <small>Download CSV</small>
						</span>
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
						<span class="btn btn-sm btn-success d-none float-right mr-2" id="multiple-award-btn"
							data-target="#award-rfq-to-user" data-toggle="modal" data-multiple="yes">
							<i class="mdi mdi-account-check"></i>
							Award Suppliers
						</span>
					</h5>
					<div class="row no-gutters">
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
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
									<?php $itemAlreadyExists = []; ?>
									<?php $isMinimumPrice = array(); ?>
									<?php
												$systemCurrency = getCurrencyById($request->currency)->name ?? '';
												$csv = [["Item", "Supplier", "Currency", "Price"]]
											?>
									@foreach ($request->quotes(false, true) as $quote)
									<?php
													$isKitRow = filled($quote->catalog_number)
														&& ! is_numeric($quote->catalog_number)
														&& ! \Illuminate\Support\Str::isUuid((string) $quote->catalog_number);
													$row = [$isKitRow ? $quote->catalog_number.' - '.$quote->kit_item_name : $quote->item_name,
														(isset($quote->supplier) ? $quote->supplier->name : '--'), $quote->currency == '-1' ? $systemCurrency : $quote->currency,
														$quote->quote_amount
													];

													$rowS = [];

													foreach ($row as $r) {
														$rowS[] = '"'.$r.'"';
													}
													$csv[] = $rowS;
												?>
									<tr
										style="{{ !isset($isMinimumPrice[$quote->request_item_id]) ? 'background-color: #addcad; font-weight: 600' :'' }}">
										<td>{{ $loop->iteration }}</td>
										<td><input type="checkbox" class="supplier-checked" value="{{ $quote->quote_amount }}"
												name="{{ $quote->supplier->name ?? '-' }}" /> {{ $quote->supplier->name ?? '-' }}</td>
										<td>
											{{ $isKitRow ? $quote->catalog_number.' - '.$quote->kit_item_name : $quote->item_name }}
										</td>
										<td>{{ $quote->brand }}</td>
										<td>{{ $quote->currency == '-1' ? $systemCurrency : $quote->currency }} {{
											number_format($quote->quote_amount, 2) }} <small>(VAT {{ $quote->vat_perc }}%) {{ $quote->vat_inc
												? 'Inc' : 'Exc' }}</small></td>
										<td nowrap>
											@if(trim($quote->awarded_at) == "")
											@if(!$isKitRow)
											<input type="checkbox" class="award-multiple-quote" value="{{ $quote->id }}"
												name="quotes_to_award[{{ $quote->request_item_id }}]" data-item="{{ $quote }}"
												data-requestid="{{ $quote->request_item_id }}" />
											@endif
											<span class="btn btn-sm btn-success" data-item="{{ $quote }}"
												data-iskit="{{ $isKitRow ? 'Yes' : 'No' }}" data-target="#award-rfq-to-user"
												data-toggle="modal">
												<i class="mdi mdi-account-check"></i>
												Award Supplier
											</span>
											<span class="btn btn-sm btn-primary" data-quote="{{ $quote }}" data-item="{{ $quote->item_name }}"
												data-target="#edit-supplier-quote" data-toggle="modal"
												data-iskit="{{ $isKitRow ? 'Yes' : 'No' }}">
												<i class="mdi mdi-pencil"></i>
												Edit Quote
											</span>
											<span class="btn btn-sm btn-danger" data-quote="{{ $quote }}" data-item="{{ $quote->item_name }}"
												data-target="#delete-supplier-quote" data-toggle="modal"
												data-iskit="{{ $isKitRow ? 'Yes' : 'No' }}">
												<i class="mdi mdi-delete"></i>
												Remove Quote
											</span>
											@else
											@if($quote->is_awarded == 1)
											<i class="mdi mdi-check-bold text-green"></i> {{ $quote->awarded_at }}

											@if (count($request->children) == 0)
											<span class="btn btn-transparent btn-sm" data-quote="{{ $quote }}"
												data-target="#undo-supplier-award" data-toggle="modal">
												<i class="mdi mdi-backup-restore text-danger" data-toggle="tooltip" data-placement="left"
													title="Undo Supplier Award"></i>
											</span>
											@endif
											@else
											<i class="mdi mdi-cancel text-muted"></i>
											@endif
											@endif
										</td>
									</tr>
									<?php
													$isMinimumPrice[$quote->request_item_id] = true;
													$itemAlreadyExists[$quote->item_name] = true;
												?>
									@endforeach
								</tbody>
							</table>
							<div id="quotes-supplier-totals" data-csv="{{ json_encode($csv) }}" class="d-flex"></div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Suppliers" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1">Suppliers
						@if (isset($request->status) && in_array($request->status, ["In Preparation", "RFQs sent out", "Receiving
						Quotes", "Awarded"]) || !isset($request->status))
						@if($isInventoryProcurement)
						<span class="btn btn-default btn-sm text-primary float-right add-supplier-row">
							<i class="mdi mdi-plus"></i> Supplier
						</span>
						<span class="btn btn-default btn-sm text-danger float-right delete-this-row" data-holder="#supplier-list">
							<i class="mdi mdi-delete"></i> Remove
						</span>
						@endif
						@endif
					</h5>
					<div class="row no-gutters">
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th style="width: 32px"></th>
										<th>#</th>
										<th nowrap>Name</th>
										<th nowrap>Rating</th>
										<th nowrap>Email</th>
										<th nowrap>Phone</th>
										<th nowrap>RFQ Sent</th>
										<th nowrap>Quote Received</th>
									</tr>
								</thead>
								<tbody id="supplier-list" data-suppliers='{{ json_encode($request->supplier_rfqs()) }}'>
									<tr class="no-data">
										<td colspan="8">
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
					<h5 class="card-title mb-3 mt-1">
						Required Approvals <small class="badge badge-secondary">{{ $approvals->count() }}</small>
						<a href="{{ route('trigger-pending-approvals-reminder', ['id'=>$request->id]) }}"
							class="float-right btn btn-sm btn-transparent btn-primary">
							<i class="mdi mdi-bell-alert"></i> Send Reminder
						</a>
					</h5>
					<div class="row no-gutters">
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text table-banded table-striped table-hover table-bordered table-sm">
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
											//Add the request creator here tovoid them from approving
											$hasApprovedBefore = [];
										?>
									@foreach ($approvals as $app)
									<?php
												$entityApproval = $app->entity_approval($stage, $request->id);
												$assignedApproverId = filled($entityApproval?->user_id) ? (string) $entityApproval->user_id : null;
												$assignedApproverName = $assignedApproverId
													? (string) (\App\User::find($assignedApproverId)?->name ?? '')
													: '';
												$configuredApprovers = getActiveUsersByRole($app->role_group_name ?? '');
												$configuredApproverNames = $configuredApprovers
													->pluck('name')
													->filter()
													->values()
													->all();

												$enableCurrentForApproval = $isEntityAboveApproved;
												//Set $isEntityAboveApproved for the next approval step
												$isEntityAboveApproved = trim((string) ($entityApproval?->approved_at ?? '')) !== '';
											?>
									<tr>
										<td>{{ $loop->iteration }}</td>
										<td>
											{{ $app->title }}
											@if(!empty($app->role_group_name))
												<br><small class="text-muted">Role: {{ $app->role_group_name }}</small>
											@endif
										</td>
										<td>{{ $entityApproval?->created_at ?? '-' }}</td>
										<td nowrap>
											@if($assignedApproverName !== '')
												{{ $assignedApproverName }}
											@elseif(count($configuredApproverNames) > 0)
												{{ implode(', ', $configuredApproverNames) }}
												@if(!$entityApproval)
													<br><small class="text-muted">Configured (not sent yet)</small>
												@endif
											@else
												<span class="text-muted">-</span>
											@endif
											<?php
														$users = $configuredApprovers->pluck('id', 'name');
														$man_users = getActiveUsersByRole($inventoryManagerRoles)->pluck('id', 'name');
													?>
											@if (isset($request->status) && ($request->status == 'Awaiting Approval' || $request->status ==
											'Partially Approved'))
											@if(isset($app->is_pending($stage, isset($request->id) ? $request->id : 0)->id))
											<span class="ml-2 btn btn-transparent btn-sm text-primary"
												data-approval="{{ $entityApproval->id }}" data-users="{{ json_encode($users) }}"
												data-manusers="{{ json_encode($man_users) }}" data-toggle="modal" data-title="{{ $app->title }}"
												data-target="#change-approver-modal">
												<i class="mdi mdi-sync"></i> Change
											</span>
											@endif
											@endif
										</td>
										<td>{{ $entityApproval?->approved_at ?? '-' }}</td>
										<td nowrap>
											@if (isset($request->status) && ($request->status == 'Awaiting Approval' || $request->status ==
											'Partially Approved'))
											@if(isset($app->is_pending($stage, isset($request->id) ? $request->id : 0)->id))
											{{-- @if(true) --}}
											@if($enableCurrentForApproval && ((isset($entityApproval->created_at)) &&
											((string) $entityApproval->user_id === (string) Auth::user()->id)) && !in_array((string) Auth::user()->id,
											array_map('strval', $hasApprovedBefore), true))
											@if(trim((string) ($entityApproval->approved_at ?? '')) == "")
											<span class="btn btn-sm btn-outline-success"
												data-approval="{{ $entityApproval->id }}" role="button" data-toggle="modal"
												data-target="#confirm-accept-modal">
												<i class="mdi mdi-check-bold"></i> Approve
											</span>
											<span class="btn btn-sm btn-outline-danger" data-approval="{{ $entityApproval->id }}"
												role="button" data-toggle="modal" data-target="#enter-reject-modal">
												<i class="mdi mdi-cancel"></i> Reject
											</span>
											<span class="btn btn-sm btn-outline-info" data-approval="{{ $entityApproval->id }}"
												role="button" data-toggle="modal" data-target="#enter-recheck-modal">
												<i class="mdi mdi-arrow-left-top"></i> Return
											</span>
											@else
											{{ $entityApproval->status ?? '' }}
											@endif
											@else
											<span class="btn btn-sm mr-2 btn-disabled text-muted">
												<i class="mdi mdi-check-bold"></i> Approve
											</span>
											<span class="btn btn-sm btn-disabled text-muted">
												<i class="mdi mdi-cancel"></i> Reject
											</span>
											@endif
											@if(!$assignedApproverId)
											<span class="ml-2 btn btn-transparent btn-sm text-primary trigger-change-approver"
												data-approval="{{ $entityApproval?->id }}" data-users="{{ json_encode($users) }}"
												data-manusers="{{ json_encode($man_users) }}" data-toggle="modal" data-title="{{ $app->title }}"
												data-target="#change-approver-modal"></span>
											@endif
											<small class="text-danger">{{ trim(Auth::user()->electronic_sig) == "" ? '**Please update your
												signature' : '' }}</small>
											@else
											<span class="text-info">{{ $entityApproval?->status ?? '' }}</span>
											@endif
											@else
											{{ $entityApproval?->status ?? 'Not Sent' }}
											@endif
										</td>
									</tr>
									<?php
												if ($assignedApproverId) {
													$hasApprovedBefore[] = $assignedApproverId;
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
							@if(in_array($stage,["Lend", "Loan"]))
							<div class="form-group">
								<label class="control-label">{{ $stage == "Loan" ? 'Loan From' : 'Lend To' }}</label>
								<select name="farm_id" class="form-control" placeholder="Select Farm..."
									data-placeholder="Select Farm...">
									@foreach (getClients() as $c)
									<option value="{{ $c->id }}" {{ $c->id == $request->farm_id ? 'selected' : '' }}>{{ $c->name }}
									</option>
									@endforeach
								</select>
							</div>
							@endif
							<div class="form-group">
								<label class="control-label">Description/Purpose</label>
								<textarea class="form-control" name="description"
									placeholder="{{ $stage }} Description...">{{ $request->description }}</textarea>
							</div>
							<div class="basic-info ">
								<div class="form-group">
									<label class="control-label">Company Unit</label>
									<div class="form-control">
										<span class="">{{ \Auth::user()->location()->name }}</span>
										<input type="hidden" name="company_unit" value="{{ \Auth::user()->location_id }}" />
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">Department</label>
									<div class="form-control">
										<span class="">{{ $theUser->department()->name }}</span>
										<input type="hidden" name="department" value="{{ $theUser->department_id }}" />
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">Cost Center*</label>

									<select required name="cost_center[]" class="form-control trigger-save ls-select2"
										placeholder="Select Cost Center..." data-placeholder="Select Cost Center..." multiple>
										@foreach (getCostCenter() as $cc)
										<option value="{{ $cc }}" {{ in_array($cc, $myCCs) ? 'selected' : '' }}>{{ $cc }}</option>
										@endforeach
									</select>
								</div>
								{{-- <div id="project-number-field" class="form-group {{ $request->nature_of_purchase != " Capex"
									? 'hidden hide' : '' }}">
									<label class="control-label">I/O Number</label>
									<input type="text" class="form-control" name="io_number" value="{{ $request->io_number }}"
										placeholder="IO Number..." />
								</div> --}}
								{{-- <div class="form-group">
									<?php $usage = ['Internal', 'External']; ?>
									<label class="control-label">Usage</label>
									<select name="usage" class="form-control" placeholder="Select Usage..." required>

										@foreach ($usage as $u)
										<option value="{{ $u }}" {{ $u==$request->usage ? 'selected' : '' }}>{{ $u }}</option>
										@endforeach
									</select>
								</div> --}}
							</div>
							<div class="form-group">
								<label class="control-label">Nature of Purchase*</label>
								<select name="nature_of_purchase" class="form-control trigger-save"
									placeholder="Select Nature of Purchase..." {!! isETCU() ? (!in_array($stage, ['Request to Store', 'Loan', 'Lend']) ? 'required' : '') : 'required' !!}>
									<option value="">Select Nature of Purchase...</option>
									@foreach (getNatureOfExpense() as $np)
									@if (in_array($stage, ['Request to Store', 'Material Issuance']))
									@if($np == "Normal")
									<option value="{{ $np }}" selected>{{ $np }}</option>
									@endif
									@else
									<option value="{{ $np }}" {{ $np==($request->nature_of_purchase ?? '') ? 'selected' : '' }}>{{ $np }}
									</option>
									@endif
									@endforeach
								</select>
							</div>
							<div id="project-number-field" class="form-group trigger-save {{ $request->nature_of_purchase != " Capex"
								? 'hidden hide' : '' }}">
								<label class="control-label">CAPEX Project Number</label>
								<input type="text" class="form-control {{ $request->nature_of_purchase == " Capex" ? 'required' : '' }}"
									{{ $request->nature_of_purchase == "Capex" ? 'required="true"' : '' }} name="capex_project_number"
								value="{{ $request->capex_project_number }}" placeholder="Project Number..." />
							</div>
							@if (in_array($request->request_type, ["Purchase Orders", "Goods Receipt", "Goods Return"]))
							<div class="form-group">
								<div class="row">
									<div class="col-3">
										<img src="{{ $supplier->logo ?? '/images/no-logo.png' }}" style="width: 100%" />
									</div>
									<div class="col-9">
										<label class="control-label">Supplier</label>
										<div class="form-control">
											<span class="">{{ $supplier->name ?? '' }}</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Supplier Email</label>
								<div class="form-control">
									<span class="">{{ $supplier->email ?? '' }}</span>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Supplier Phone</label>
								<div class="form-control">
									<span class="">{{ $supplier->phone ?? '' }}</span>
								</div>
							</div>
							@if($stage != "Purchase Orders")
							<div class="form-group">
								<label class="control-label">Due Date</label>
								<input type="date" name="valid_until" value="{{ $request->due_date ?? '' }}"
									class="form-control trigger-save" placeholder="Due Date..." />
							</div>
							@endif
							@endif
							@if(in_array($request->request_type, ["Request for Quotation", "Purchase Orders"]))
							<div class="form-group">
								<label class="control-label">Currency*</label>
								<select name="currency" class='form-control trigger-save' placeholder="Select Currency..." required>
									@foreach ($availableCurrencies as $p)
									<option value="{{ $p->id }}" {{ $p->id == ($request->currency ?? '') ? 'selected' : '' }}>{{ $p->name
										}}</option>
									@endforeach
								</select>
							</div>
							@if($stage == "Request for Quotation")
							<div class="form-group">
								<label class="control-label">Submission Deadline*</label>
								<input type="datetime-local" name="submission_deadline"
									value="{{ $request->submission_deadline ? \Carbon\Carbon::parse($request->submission_deadline)->format('Y-m-d\TH:i') : '' }}"
									class="form-control trigger-save" placeholder="Submission Deadline..." required />
							</div>
							@endif
							@if($stage == "Purchase Orders" && !empty($request->validity_period))
							<div class="form-group">
								<label class="control-label">Valid Until</label>
								<input type="date" name="validity_period" value="{{ $request->validity_period ?? '' }}"
									class="form-control trigger-save" placeholder="Validity Period..." />
							</div>
							@endif
							<div class="form-group">
								<label class="control-label">{{ $stage == "Purchase Orders" ? "Delivery Date" : "Valid Until" }}{{ $stage == "Request for Quotation" ? '*' : '' }}</label>
								<input type="date" name="valid_until" value="{{ $request->due_date ?? '' }}"
									class="form-control trigger-save" placeholder="Validity Period..."
									{{ $stage == "Request for Quotation" ? 'required' : '' }} />
							</div>
							<div class="form-group">
								<label class="control-label">Request Value</label>
								@if($stage == "Purchase Orders")
								<input type="number" step="any" class="form-control trigger-save" name="net_value"
									value="{{ $request->net_value ?? '0.00' }}" />
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
							@if(in_array($request->request_type, ["Goods Receipt", "Goods Return", "Material Issuance", "Gate Pass"]))
							<div class="form-group">
								<label class="control-label">{{ $request->request_type == "Goods Receipt" ? 'CARRIER' : 'Note Bearer
									Name' }}</label>
								<input type="text" name="note_bearer" value="{{ $request->request_type == " Goods Receipt" ?
									($supplier->name ?? '') : ($request->note_bearer ?? '') }}" class="form-control trigger-save"
								placeholder="Note Bearer Name..." />
							</div>
							<div class="form-group">
								<label class="control-label">Gate Pass</label>
								<?php
											$code = $request->request_code;

											if(trim($request->gate_pass) == ""){
												$gatePass = getNamingConventionCode("Gate Pass", false, "GP");
											}
											else{
												$gatePass = $request->gate_pass;
											}

										?>
								<input type="text" name="gate_pass" readonly value="{{ $gatePass }}" class="form-control trigger-save"
									placeholder="Gate Pass..." />
							</div>
							@if($request->request_type == "Goods Receipt")
							<div class="form-group">
								<label class="control-label">Delivery Note No</label>
								<input type="text" name="delivery_note_number" value="{{ $request->delivery_note_number ?? '' }}"
									class="form-control trigger-save" placeholder="Delivery Note No..." />
							</div>
							<div class="form-group">
								<label class="control-label">Supplier Invoice No</label>
								<input type="text" name="supplier_invoice_number" value="{{ $request->supplier_invoice_number ?? '' }}"
									class="form-control trigger-save" placeholder="Supplier Invoice No..." />
							</div>
							<div class="form-group">
								<label class="control-label">Moisture Contents</label>
								<input type="text" name="moisture_contents" value="{{ $request->moisture_contents ?? '' }}"
									class="form-control trigger-save" placeholder="Moisture Contents..." />
							</div>
							<div class="form-group">
								<label class="control-label">Remarks</label>
								<input type="text" name="remarks" value="{{ $request->remarks ?? '' }}"
									class="form-control trigger-save" placeholder="Remarks..." />
							</div>
							@endif
							<div class="form-group">
								<label class="control-label">Time Out</label>
								<input type="time" name="time_out" value="{{ $request->time_out ?? '' }}"
									class="form-control trigger-save" placeholder="Time Out..." />
							</div>
							<div class="form-group">
								<label class="control-label">Vehicle Number</label>
								<input type="text" name="vehicle_no" value="{{ $request->vehicle_no ?? '' }}"
									class="form-control trigger-save" placeholder="Vehicle Number..." />
							</div>
							<div class="form-group">
								<label class="control-label">Destination</label>
								<input type="text" name="destination" value="{{ $request->destination ?? '' }}"
									class="form-control trigger-save" placeholder="Destination..." />
							</div>
							@endif
							<div class="form-group">
								<label class="control-label">Requested By</label>
								<div class="form-control">
									<?php $RequestedBy = \App\User::find($request->request_initiator); ?>
									<span class="">{{ $RequestedBy ? $RequestedBy->name : \Auth::user()->name }}</span>
									<input type="hidden" name="requested_by"
										value="{{ $request->request_initiator ?? \Auth::user()->id }}" />
								</div>
							</div>
							@if($stage == "Material Issuance")
							<div class="form-group">
								<label class="control-label">Issue To</label>
								<select class="form-control trigger-save" name="issue_to" placeholder="Issue To...">
									<?php
										$request->issue_to = trim($request->issue_to) == "" ? ($RequestedBy ? $RequestedBy->id : \Auth::user()->id) : $request->issue_to;
									?>
									@foreach (getUsers() as $user)
									<option value="{{ $user->id }}" {{ $user->id == $request->issue_to ? 'selected' : '' }}>{{ $user->name
										}}</option>
									@endforeach
								</select>
							</div>
							@endif
							@if($stage == "Goods Receipt")
							<div class="form-group">
								<label class="control-label">Items Checked By</label>
								<select class="form-control trigger-save" name="issue_to" data-placeholder="Items Checked By..."
									required="true">
									<option></option>
									@foreach (getUsers() as $user)
									<option value="{{ $user->id }}" {{ $user->id == $request->issue_to ? 'selected' : '' }}>{{ $user->name
										}}</option>
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
									@foreach (getRequestPriority() as $p)
									<option value="{{ $p }}" {{ $p==($request->priority ?? '') ? 'selected' : '' }}>{{ $p }}</option>
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
							@if(isETCU() && in_array($stage, ['Request for Quotation', 'Purchase Orders']))
							<div class="form-group">
								<label class="control-label">Send PO via</label>
								<select name="method_to_send_po" class="form-control" placeholder="Select Send PO via..." required>
									@foreach (getMethodToSendPO() as $p)
									<option value="{{ $p }}" {{ $p==($request->method_to_send_po ?? '') ? 'selected' : '' }}>{{ $p }}
									</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Print Price in PO*</label>
								<select name="print_price_on_po" class="form-control" placeholder="Print Price in PO..." required>
									@foreach (["YES", "NO"] as $p)
									<option value="{{ $p }}" {{ $p==($request->print_price_on_po ?? '') ? 'selected' : '' }}>{{ $p }}
									</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Delivery To*</label>
								<select name="delivery_to" class="form-control" placeholder="Delivery To..." required>
									@foreach (etcuFarms() as $p)
									<option value="{{ $p }}" {{ $p==($request->delivery_to ?? '') ? 'selected' : '' }}>{{ $p }}</option>
									@endforeach
								</select>
							</div>
							@endif
							<div class="form-group">
								@if(in_array($stage, ["Purchase Request", "Request to Store"]))
								<label class="control-label"><input class="trigger-save" type="checkbox" name="is_lab_kit" value="1" {{
										$request->is_lab_kit == 1 ? 'checked' : '' }} /> Is Clonable</label>
								@endif
							</div>
							@if(\Auth::user()->department_id == $lab_department_id && $stage == "Purchase Request")
							<div class="form-group">
								<label class="control-label">Catalog Number <small class="text-danger">*Alphanumeric</small></label>
								<input type="text" name="catalog_number" class="form-control trigger-save"
									value="{{ $request->catalog_number ?? '' }}" />
							</div>
							<div class="form-group">
								<label class="control-label">KIT Price</label>
								<input type="text" name="kit_total_price" class="form-control trigger-save"
									value="{{ $request->kit_total_price ?? 0 }}" />
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="duplicates-for-quotes"></div>
	</form>
</main>
@endsection

@section('script2')
@if($stage == "Goods Receipt")
<div id="update-supplier-criteria-rating-modal" class="modal fade update-supplier-criteria-rating-modal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" action="{{ route('update-rating-criteria-score', ['id'=>$supplier->id]) }}"
			method="POST" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-content-save"></i> Update Supplier Criteria Scored</h5>
			</div>
			<div class="modal-body">
				@foreach (getSupplierRatingCriteria() as $gSRC)
				<?php
					$c_score = $ratingScores[$gSRC->id] ?? 0;
					$score_reason = $ratingReason[$gSRC->id] ?? '';
					$scorePerc = $c_score/$gSRC->max_score*100;
					$guidesOBJ = $gSRC->guides;
				?>
				<div class="form-group">
					<h6 style="width: 100%" for="crit-{{ $gSRC->id }}">{{ $gSRC->title }}
						<span class="badge badge-pill badge-info float-right">0</span>
					</h6>
					<input style="width: 100%" type="range" step="0.1" value="{{ $c_score }}"
						name="rating[{{ $gSRC->id }}][criteria]" class="form-range" min="0" max="{{ $gSRC->max_score }}"
						id="crit-{{ $gSRC->id }}" data-guides="{{ $guidesOBJ }}">
					{{-- <div><i class="fas fa-star" style="font-size:11px"></i> <small
							class="badge badge-default guide-title"></small></div> --}}
					<input type="hidden" name="rating_request_id" value="{{ $request->id ?? 0 }}" />
					<div class="form-group reason-textarea mt-1">
						<label class="control-label"><em>Reason for your rating</em></label>
						<textarea class="form-control form-control-sm" name="rating[{{ $gSRC->id }}][reason]"
							placeholder="Reason..." required>{{ $score_reason }}</textarea>
					</div>
				</div>
				@endforeach
			</div>
			<div class="divider"></div>
			<div class="modal-body">
				<div class="alert alert-callout alert-info text-lg">
					<i class="mdi mdi-information fa-1x"></i> Please provide the confirmation OTP code:
				</div>
				<div class="form-group">
					<label>Requester OTP</label>
					<input type="text" name="requester_otp" class="form-control" placeholder="Requester OTP..." />
					<input type="hidden" name="issue_out_items" value="1" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
@endif
<div id="add-inventory-category" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-inventory-category') }}"
			enctype="multipart/form-data">
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
<div id="Send-Bank-Notification" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('submit-bank-details', ['id'=>$request->id ?? 0]) }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Upload Bank Documents</h4>
			</div>
			<div class="modal-body">
				<?php
				$bankDocuments = [];
				foreach ($attachmentList as $a) {
					if($a->type == "Bank Notification Document"){
						$bankDocuments[] = $a;
					}
				}
			?>
				@if(count($bankDocuments) == 0)
				<div class="alert alert-warning">
					<i class="fas fa-exclamation-triangle pull-left"></i> No Bank Documents added to the PO. Click <span
						id="trigger-add-attachments" class="badge badge-primary" style="cursor: pointer"
						data-dismiss="modal">here</span> to attach bank related documents to the PO.
				</div>
				@endif
				<div class="form-group">
					<label class="control-label">Message to Bank</label>
					<textarea class="form-control" name="message" rows="7" placeholder="Message to Bank..."></textarea>
				</div>
				@if(count($bankDocuments) > 0)
				<h6><i class="fas fa-files"></i> Additional Documents</h6>
				<hr>
				<ol>
					@foreach ($bankDocuments as $doc)
					<input type="hidden" name="docs[link][]" value="{{ $doc->title }}" />
					<input type="hidden" name="docs[file][]" value="{{ $doc->file }}" />
					<li class="p-2 text-primary"><i class="fas fa-file"></i> {{ $doc->title }} <a href="{{ $doc->file }}"
							class="float-right pull-right text-danger" target="_blank"><i class="fas fa-download"></i></a></li>
					@endforeach
				</ol>
				@endif
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-email-send"></i> Send Documents</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="Upload-Bank-Confirmation" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST"
			action="{{ route('upload-bank-confirmation', ['id'=>$request->id ?? 0]) }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-upload"></i> Upload Bank Confirmation Document</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Upload Bank Confirmation Document</label>
					<input type="file" class="form-control" name="file" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-upload"></i> Upload</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-approval-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-approval-to-stage', ['stage'=>$stage]) }}"
			enctype="multipart/form-data">
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
				<button type="submit" class="btn btn-primary modal-add-data trigger-save" data-type="attachment"><i
						class="mdi mdi-plus"></i> Add</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div id="accept-goods-otp-modal" class="modal fade update-supplier-criteria-rating-modal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-numeric"></i> Rate Supplier and Confirm Receipt</h5>
			</div>
			<div class="modal-body">
				@foreach (getSupplierRatingCriteria() as $gSRC)
				<?php
					$c_score = $ratingScores[$gSRC->id] ?? 0;
					$score_reason = $ratingReason[$gSRC->id] ?? '';
					$scorePerc = $c_score/$gSRC->max_score*100;
					$guidesOBJ = $gSRC->guides;
				?>
				<div class="form-group rating-delivery" data-rid="{{ $gSRC->id }}">
					<h6 style="width: 100%" for="crit-{{ $gSRC->id }}">{{ $gSRC->title }}
						<span class="badge badge-pill badge-info float-right">0</span>
					</h6>
					{{-- <div><i class="fas fa-star" style="font-size:11px"></i> <small
							class="badge badge-default guide-title"></small></div> --}}
					<input style="width: 100%" type="range" step="0.1" value="{{ $c_score }}"
						name="delivery_rating[{{ $gSRC->id }}]criteria" class="form-range rating-delivery-criteria"
						data-type="criteria" min="0" max="{{ $gSRC->max_score }}" id="crit-{{ $gSRC->id }}"
						data-guides="{{ $guidesOBJ }}">
					<input type="hidden" name="request_id" value="{{ $request->id ?? 0 }}" required />
					<div class="form-group reason-textarea mt-1">
						<label class="control-label"><em>Reason for your rating</em></label>
						<textarea class="form-control form-control-sm rating-delivery-reason" data-type="reason"
							name="delivery_rating[{{ $gSRC->id }}]reason" placeholder="Reason...">{{ $score_reason }}</textarea>
					</div>
				</div>
				@endforeach
			</div>
			<div class="divider"></div>
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
				<button type="button" class="btn btn-success save-details-form" id="accept-goods-otp-modal-save-btn"
					data-type="accept-goods-receipt">Confirm</button>
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

						@foreach (getNoteTypes() as $item)
						<option value="{{ $item }}">{{ $item }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Title</label>
					<input class="form-control" name="title" placeholder="Note Title ...">
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
				<button type="submit" class="btn btn-primary modal-add-data trigger-save" data-type="note"><i
						class="mdi mdi-plus"></i> Add</button>
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
<div id="enter-recheck-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-arrow-u-left-top"></i> Return Reason</h5>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Enter Reason</label>
					<textarea class="form-control" id="recheck-reason-text" placeholder="Reason..."></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-info save-details-form" data-type="recheck-with-reason">Return</button>
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
						<input type="radio" name="return_action" class="goods-return-action" value="resupply" checked /> The
						supplier will resupply the returned items.
					</label>
				</div>
				<div class="form-group">
					<label class="control-label">
						<input type="radio" name="return_action" class="goods-return-action" value="credit_note" /> The supplier has
						provided a credit note for the items.
					</label>
				</div>
				<div class="form-group uploadable hide">
					<label class="control-label">Attach Credit Note</label>
					<input type="file" name="credit_note" class="form-control goods-return-credit-note-file" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger save-details-form"
					data-type="return-goods-to-supplier">Proceed</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div id="Make-Amendment-Modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form method="post" action="{{ route('make-po-ammendment', ['id'=>$request->id ?? 0, 'stage'=>$stage]) }}"
			class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-file-document-edit"></i> Amend Purchase Order</h5>
			</div>
			<div class="modal-body">
				<div class="list-group">
					<label type="button" class="list-group-item list-group-item-action">
						<input type="radio" name="ammendent_type" value="supplement" checked /> Create a Supplementary Purchase
						Order
					</label>
					<label type="button" class="list-group-item list-group-item-action">
						<input type="radio" name="ammendent_type" value="replace" /> Replace Current Purchase Order
					</label>
				</div>
				<br>
				<div class="alert alert-danger">
					<i class="mdi mdi-information"></i> Proceed with this action?
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger">Yes Proceed</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
@if(in_array($stage, ["Purchase Request", "Request for Quotation", "Purchase Orders", "Request to Store", "Material Issuance"]))
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
					<li class="list-group-item"><label class="control-label"><input name="status" value="In Preparation"
								type="radio" /> In Preparation</label></li>
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
				<div class="form-group d-none" id="awarding-multiple-alert">
					<div class="alert alert-warning text-default">
						<i class="mdi mdi-alert fa-2x float-left mr-2 mb-1"></i> You have selected to award multiple quotes at once.
						Please ensure that you confirm that you have selected
						the correct suppliers for each item and each awarding is due to the reason you have provided.
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success save-details-form" data-type="award-with-reason">Award</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
@if(in_array($stage,["Gate Pass"]))
<div id="add-sub-category-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-inventory-sub-category') }}"
			enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $gate_pass_category_id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Item</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<?php
							$third_party_item_code = getConfigByName('third_party_item_code');
							$third_party_item_code = count($third_party_item_code) > 0 ? $third_party_item_code[0]->value : 'SAP Code';
						?>
					<label class="control-label">{{ $third_party_item_code }}</label>
					<input type="text" class="form-control" name="sap_code" placeholder="{{ $third_party_item_code }}..." />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Image</label>
					<input type="file" class="form-control" name="image" />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Manufacturer</label>
					<input type="text" class="form-control" name="manufacturer" value="" placeholder="Manufacturer..." />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Maximum Order Quantity</label>
					<input type="number" min="0" value="10000000" class="form-control" name="maximum_order_quantity" value=""
						placeholder="Maximum Order Quantity..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Item Classification</label>
					<select class="form-control" name="item_classification">
						<option value="">Select Item Classification...</option>
						@foreach (getInventoryItemClassification() as $g=>$c)
						<option value="{{ $g }}">{{ $c }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Unit of Measure</label>
					<select class="form-control" name="unit_type" required>
						<option value="">Select Unit of Measure...</option>
						@foreach (getReportingUnits() as $g)
						<option value="{{ $g['name'] }}">{{ $g['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Issuing Unit of Measure</label>
					<select class="form-control" name="secondary_unit_type" required>
						<option value="">Select Unit of Measure...</option>
						@foreach (getReportingUnits() as $g)
						<option value="{{ $g['name'] }}">{{ $g['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Cash Price</label>
					<input type="number" min="0" value="100" class="form-control" name="unit_price" value=""
						placeholder="Cash Price..." required />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Credit Price</label>
					<input type="number" min="0" class="form-control" name="unit_price_credit" value=""
						placeholder="Credit Price..." required />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Annual Consumption</label>
					<input type="number" min="0" class="form-control" name="annual_consumption"
						placeholder="Annual Consumption..." required />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Working Days</label>
					<input type="number" min="0" class="form-control" name="working_days" placeholder="Working Days..."
						required />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Estimated variation in demand as a %of average consumption</label>
					<input type="number" class="form-control" name="estimated_variation_in_demand_average_consumption"
						placeholder="Estimated variation in demand as a %of average consumption..." required />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Internal Lead Time</label>
					<input type="number" class="form-control" name="internal_lead_time"
						value="{{ getConfigByName('default_internal_lead_time')[0] }}" placeholder="Internal Lead Time..."
						required />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">External Lead Time</label>
					<input type="number" class="form-control" name="external_lead_time"
						value="{{ getConfigByName('default_external_lead_time')[0] }}" placeholder="External Lead Time..."
						required />
				</div>
				<div class="form-group hidden hide">
					<label class="control-label">Material Type</label>
					<select class="form-control" name="material_type_id" placeholder="Select Material Type...">
						<option value="">Non Specific</option>
						@foreach (getModulePreconfig('Material Type', 'Inventory-Management') as $material_type)
						<option value="{{ $material_type['id'] }}">{{ $material_type['name'] }}</option>
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
@endif
@if(in_array($stage,["Goods Return", "Material Issuance"]))
<div id="Create-Gate-Pass-Modal" class="modal fade" role="dialog">
	<form class="modal-dialog modal-lg" action="" method="POST" enctype="multipart/form-data">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-clipboard-arrow-left"></i> Create Gate Pass</h5>
			</div>
			<div class="modal-body">
				<div class="alert alert-info">
					Are you sure you want to create a Gate-Pass from {{ $request->request_type }}?
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="create_gate_pass" value="1" />
				<button type="submit" class="btn btn-success">Yes, Create</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</form>
</div>
@endif
@if(in_array($stage, ["Purchase Orders", "Lend", "Loan"]))
<div id="Create-Goods-Receipt-Modal" class="modal fade" role="dialog">
	<form class="modal-dialog modal-lg" action="" method="POST" enctype="multipart/form-data">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-plus"></i> {{ in_array($stage, ["Lend", "Loan"]) ? "Receive Items" :
					"Create Goods Receipt" }}</h5>
			</div>
			<div class="modal-body">
				<h6><i class="mdi mdi-package-variant-closed"></i> Receivable Items</h6>
				<div class="table-responsive">
					<table class="table table-condensed table-striped table-sm table-bordered">
						<thead>
							<th>Item</th>
							<th>Requested Quantity</th>
							@if(in_array($stage, ["Loan", "Lend"]))
							<th>{{ $stage == "Loan" ? 'Borrowed' : 'Lended' }} Quantity</th>
							@endif
							<th>Pending Quantity</th>
							<th>Receiving Quantity</th>
							<th>Expiry</th>
							<th>Lot Number</th>
							<th>Date of Manufacture</th>
						</thead>
						<tbody>
							@foreach ($normalItemsGrouped ?? array() as $req_item)
							<?php
								$pendingQ = $req_item->quantity - $req_item->pending();
								$isKitItem = filled($req_item->catalog_number)
									&& ! is_numeric($req_item->catalog_number)
									&& ! \Illuminate\Support\Str::isUuid((string) $req_item->catalog_number);

								if(in_array($stage, ["Loan", "Lend"])){
									$moreRemvs = [];
									foreach($req_item->issued_received_breakdown() as $b=>$z){
										if(!isset($moreRemvs[$b])){
											$moreRemvs[$b] = 0;
										}
										$moreRemvs[$b] += floatval($z);
									}

									$GRBal = isset($moreRemvs["Goods Receipt"]) ? floatval($moreRemvs["Goods Receipt"]) : 0;
									$MIBal = isset($moreRemvs["Material Issuance"]) ? floatval($moreRemvs["Material Issuance"]) : 0;

									$Remover = 0;
									if($stage == "Loan"){
										$Remover = $req_item->quantity - $GRBal;
									}
									else{
										$Remover = $MIBal - $GRBal;
									}

									$pendingQ = $Remover;
								}

								$itemCategory = \App\InventorySubCategories::find($req_item->inventory_sub_category_id);
                $itemCategoryID = $itemCategory->inventory_category_id;
							?>
							<tr class="for-item-{{ $req_item->inventory_sub_category_id }} quote-row">
								<input type="hidden" name="item[]" value="{{ $req_item->id }}" />
								<td nowrap>{{ $isKitItem ? $req_item->catalog_number.' - ' : '' }}{{ $req_item->item_name }}</td>
								<td class="text-right">{{ number_format($req_item->quantity, 3) }}{{ $req_item->uom }}</td>
								@if(in_array($stage, ["Loan", "Lend"]))
								<td class="text-right">{{ number_format(($stage == "Loan" ? $GRBal : $MIBal), 3) }}{{ $req_item->uom }}
								</td>
								@endif
								<td class="text-right">
									{{ number_format($pendingQ, 3) }}{{ $req_item->uom }}
									<input type="hidden" name="pending[{{ $req_item->id }}]" value="{{ $pendingQ }}" />
								</td>
								<td>
									<input type="number" step="any" max="{{ $pendingQ }}" value="{{ $pendingQ }}"
										class="form-control form-control-sm" name="received[{{ $req_item->id }}]" />
								</td>
								<td>
									<input type="date" class="form-control form-control-sm" name="expiry[{{ $req_item->id }}]" {!!
										in_array($itemCategoryID, $expiring_item_ids) ? 'required="true"' : '' !!} />
								</td>
								<td>
									<input type="text" class="form-control form-control-sm" name="lot_no[{{ $req_item->id }}]"
										style="width: 100px" placeholder="Lot Number..." {!! in_array($itemCategoryID, $expiring_item_ids)
										? 'required="true"' : '' !!} />
								</td>
								<td>
									<input type="date" class="form-control form-control-sm"
										name="date_of_manufacture[{{ $req_item->id }}]" />
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<br>
				<hr><br>
				@if(!in_array($stage, ["Lend", "Loan"]))
				<h6><i class="mdi mdi-paperclip"></i>Accompanying Documents</h6>
				<div class="form-group">
					<label>Invoice</label>
					<input type="file" name="invoice" class="form-control" />
				</div>
				<div class="form-group">
					<label class="control-label">Items Checked By</label>
					<select class="form-control trigger-save" name="issue_to" data-placeholder="Items Checked By..."
						required="true">
						<option></option>
						@foreach (getUsers() as $user)
						<option value="{{ $user->id }}" {{ $user->id == $request->issue_to ? 'selected' : '' }}>{{ $user->name }}
						</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label>Delivery Note</label>
					<input type="file" name="delivery_note" class="form-control" />
				</div>
				<div class="form-group">
					<label>Job Card</label>
					<input type="file" name="job_card" class="form-control" />
				</div>
				<br>
				<hr><br>
				@endif
				<h6><i class="mdi mdi-email"></i>{{ $stage == "Lend" ? 'Message to Issuer' : 'Message to Requester' }} *
					Optional</h6>
				<div class="row pb-1">
					<div class="col-2">
						<img src="/images/user.png" style="width: 100%" />
					</div>
					{{--
					<pre>{{ json_encode($request, JSON_PRETTY_PRINT) }}</pre> --}}
					<?php
					$REQUESTER = \App\User::find(!in_array($stage, ["Lend", "Loan"]) ? $request->request_initiator : $request->created_by);
				?>
					<div class="col-10">
						<h5>{{ $REQUESTER->name ?? '-' }}</h5>
						<span style="padding: 0px 4px 2px 0px; font-size:15px"><i class="mdi mdi-email"></i> {{ $REQUESTER->email ??
							'' }}</span><br>
						<span style="padding: 2px 4px 2px 0px; font-size:15px"><i class="mdi mdi-phone"></i> {{ $REQUESTER->phone ??
							'' }}</span><br>
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
				<div class="table-responsive">
					<table class="table table-condensed table-striped table-sm table-bordered">
						<thead>
							<th>Item</th>
							<th>Requested Quantity</th>
							<th>Pending Quantity</th>
							<th>Return Quantity</th>
							<th>Expiry</th>
							<th>Lot Number</th>
							<th>Date of Manufacture</th>
						</thead>
						<tbody>
							@foreach ($normalItems ?? array() as $req_item)
							<?php $pendingQ = $req_item->quantity - $req_item->pending(); ?>
							<tr class="for-item-{{ $req_item->inventory_sub_category_id }} quote-row">
								<input type="hidden" name="item[]" value="{{ $req_item->id }}" />
								<td nowrap>{{ $req_item->item_name }}</td>
								<td class="text-right">{{ number_format($req_item->quantity, 3) }}</td>
								<td class="text-right">{{ number_format($pendingQ, 3) }}</td>
								<td>
									<input type="number" value="0" step="any" min="0" max="{{ $pendingQ }}"
										class="form-control form-control-sm" name="received[{{ $req_item->id }}]" required />
								</td>
								<td>
									<input type="date" class="form-control form-control-sm" name="expiry[{{ $req_item->id }}]" />
								</td>
								<td>
									<input type="text" class="form-control form-control-sm" name="lot_no[{{ $req_item->id }}]"
										style="width: 100px" placeholder="Lot Number..." />
								</td>
								<td>
									<input type="date" class="form-control form-control-sm"
										name="date_of_manufacture[{{ $req_item->id }}]" />
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<br>
				<hr><br>
				<h6><i class="mdi mdi-paperclip"></i>Accompanying Documents</h6>
				<div class="form-group">
					<label>Invoice</label>
					<input type="file" name="invoice" class="form-control" />
				</div>
				<div class="form-group">
					<label>Delivery Note</label>
					<input type="file" name="delivery_note" class="form-control" />
				</div>
				<br>
				<hr><br>
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
@if(in_array($stage, ["Request to Store", "Lend", "Loan"]))
<div id="Create-Material-Issuance-Modal" class="modal fade" role="dialog">
	<form class="modal-dialog modal-lg" action="" method="POST" enctype="multipart/form-data">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-plus"></i> {{ in_array($stage, ["Lend", "Loan"]) ? "Issue Out Items" :
					"Create Material Issuance" }}</h5>
			</div>
			<div class="modal-body">
				<h6><i class="mdi mdi-package-variant-closed"></i> Issuable Items</h6>
				<div class="table-responsive">
					<table class="table table-condensed table-striped table-sm table-bordered">
						<thead>
							<th>Item</th>
							<th>Requested Quantity</th>
							@if(in_array($stage, ["Loan", "Lend"]))
							<th>{{ $stage == "Loan" ? 'Borrowed' : 'Lended' }} Quantity</th>
							@endif
							<th>Pending Quantity</th>
							<th>Issuing Quantity</th>
						</thead>
						<tbody>
							@foreach ($normalItems ?? array() as $req_item)
							<?php
								$pendingQ = $req_item->quantity - $req_item->pending();
								if(in_array($stage, ["Loan", "Lend"])){
									$moreRemvs = [];
									foreach($req_item->issued_received_breakdown() as $b=>$z){
										if(!isset($moreRemvs[$b])){
											$moreRemvs[$b] = 0;
										}
										$moreRemvs[$b] += floatval($z);
									}

									$GRBal = isset($moreRemvs["Goods Receipt"]) ? floatval($moreRemvs["Goods Receipt"]) : 0;
									$MIBal = isset($moreRemvs["Material Issuance"]) ? floatval($moreRemvs["Material Issuance"]) : 0;

									$Remover = 0;
									if($stage == "Loan"){
										$Remover = $GRBal - $MIBal;
									}
									else{
										$Remover = $req_item->quantity - $MIBal;
									}

									$pendingQ = $Remover;
								}
							?>
							<tr class="for-item-{{ $req_item->inventpory_sub_category_id }} quote-row">
								<input type="hidden" name="item[]" value="{{ $req_item->id }}" />
								<td nowrap>{{ $req_item->item_name }}</td>
								<td class="text-right">{{ number_format($req_item->quantity, 3) }}{{ $req_item->uom }}</td>
								@if(in_array($stage, ["Loan", "Lend"]))
								<td class="text-right">{{ number_format(($stage == "Loan" ? $GRBal : $MIBal), 3) }}{{ $req_item->uom }}
								</td>
								@endif
								<td class="text-right">
									{{ number_format($pendingQ, 3) }}{{ $req_item->uom }}
									<input type="hidden" name="pending[{{ $req_item->id }}]" value="{{ $pendingQ }}" />
								</td>
								<td>
									<input type="number" step="any" min="0" value="{{ $pendingQ }}" max="{{ $pendingQ }}"
										class="form-control form-control-sm" name="issued[{{ $req_item->id }}]" />
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
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
						<span style="padding: 0px 4px 2px 0px; font-size:15px"><i class="mdi mdi-email"></i> {{ $REQUESTER->email ??
							'' }}</span><br>
						<span style="padding: 2px 4px 2px 0px; font-size:15px"><i class="mdi mdi-phone"></i> {{ $REQUESTER->phone ??
							'' }}</span><br>
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
				<button type="button" class="btn btn-success save-details-form" data-type="confirm-approval-reason">Yes,
					Approve</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div id="reverse-entity-action" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form action="{{ route('reverse-entity-action', ['id'=>$request->id ?? 0]) }}" method="POST" class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-undo"></i> Reverse {{ $request->request_type }}</h5>
			</div>
			<div class="modal-body">
				<div class="alert alert-callout alert-info text-lg">
					<i class="mdi mdi-information-circle"></i> Proceed with reversing this {{ $request->request_type }} - {{
					$request->request_code }}?
				</div>
				<div class="form-group">
					<label>Reason</label>
					<textarea class="form-control" placeholder="Reason..." name="reason" required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success">Yes, Reverse</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
{{-- Send RFQ email body modal (independent of OTP optional setting) --}}
<div id="Send-RFQ-modal" class="modal fade" role="dialog">
	<form class="modal-dialog" action="{{ route('add-email-body-rfq', $request->id ?? 0) }}" method="POST">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-cog"></i> Configure Email Body</h5>
			</div>
			<div class="modal-body">
				<div class="col-xs-12">
					Dear Supplier,
				</div>
				<?php $hasEmailBody = trim($request->email_body) != "" ?>
				<?php
					$pro_contact = getConfigByName('po_contact_email');
					$pro_contact_email = count($pro_contact) > 0 ? $pro_contact[0]->value : 'contact not set';
					$defaultBody = "<p>Please provide us with a quote for the following items. Feel free to use your preferred quote template for this RFQ. 
					If you don`t have one, no problem - the attached template is available for your convenience. <br>
					Our only request is that you ensure all essential details are included, such as itemized costs, anticipated lead times, etc. 
					Please indicate the validity period for your quotes. <br>
					Send your quotes to <b>" . $pro_contact_email . "</b>.</p>";
				?>
				<div>
					<div class="my-2" id="rfq-body-editor">
						<textarea id="rfq-body" class="form-control editor" rows="8"
							style="border:none !important; outline: none!important" name="body" placeholder="Email Body..."
							required>{{ $hasEmailBody ? $request->email_body : $defaultBody }}</textarea>
					</div>
					<div class="my-1" id="rfq-body-preview">
						{!! $request->email_body !!}
					</div>
				</div>
				<div>
					<div class="my-1" style="cursor:pointer">
						<small class="text-info" id="edit-body-preview">
							<i class="fas fa-edit"></i> Edit
						</small>
					</div>
				</div>

			</div>
			<div class="modal-body">
				<h6>RFQ Items</h6>
				<table style="border-collapse: collapse;">
					<thead>
						<tr style="border: 1px solid #999">
							<th style="border: 1px solid #999; padding:2px">No.</th>
							<th style="border: 1px solid #999; padding:2px">RFQ Code</th>
							<th style="border: 1px solid #999; padding:2px">Item Code</th>
							<th style="border: 1px solid #999; padding:2px">Description</th>
							<th style="border: 1px solid #999; padding:2px">Comments</th>
							<th style="border: 1px solid #999; padding:2px">Quantity</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($normalItems ?? array() as $req_item)
						<tr style="border: 1px solid #999">
							<td style="border: 1px solid #999; padding:2px">{{ $loop->iteration }}</td>
							<td style="border: 1px solid #999; padding:2px">{{ $request->request_code }}</td>
							<td style="border: 1px solid #999; padding:2px">{{ $req_item->sub_category->code }}</td>
							<td style="border: 1px solid #999; padding:2px">{{ $req_item->sub_category->name }}
								<br>{{ $request->comments }}
							</td>
							<td style="border: 1px solid #999; padding:2px">{{ $req_item->comments }}</td>
							<td style="border: 1px solid #999; padding:2px">{{ number_format($req_item->quantity, 3) }} <small>({{
									$req_item->uom }})</small></td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" onclick="tinyMCE.triggerSave()">Update</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</form>
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
<div id="extra-charge-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-extra-charge', ['id' => $request->id ?? 0]) }}">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-file-account"></i> Add Additional Charge</h5>
			</div>
			<?php
					$r_extras = \Illuminate\Support\Facades\Schema::hasTable('request_entity_extra_charges')
						? \App\RequestEntityExtraCharge::selectRaw('title as name')->groupBy('name')->get()
						: collect();
				?>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Title <i class="mdi mdi-swap-vertical text-info swap-extra-titles"
							style="cursor: pointer"></i></label>
					<input type="text" name="title_confirm" placeholder="Title..." class="form-control d-none" />
					<div class="select-holder">
						<select name="title" data-placeholder="Select Title..." class="form-control">
							@foreach ($r_extras as $re)
							<option value="{{ $re->name }}">{{ $re->name }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Currency </label>
					<select name="currency" class='form-control' data-placeholder="Select Currency...">
						@foreach ($availableCurrencies as $p)
						<option value="{{ $p->id }}">{{ $p->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Cost </label>
					<input type="number" name="cost" placeholder="Cost" class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success">Add Charge</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="delete-extra-charge-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-delete"></i> Remove Additional Charge</h5>
			</div>
			<?php
					$r_extras = \Illuminate\Support\Facades\Schema::hasTable('request_entity_extra_charges')
						? \App\RequestEntityExtraCharge::selectRaw('title as name')->groupBy('name')->get()
						: collect();
				?>
			<div class="modal-body">
				<div class="alert alert-danger">
					<i class="mdi mdi-alert"></i> Are you sure you want to remove this additional charge?
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success">Add Charge</button>
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
						<i class="fas fa-info-circle fa-2x"></i> Are you sure you want to create a purchase order straight from
						Purchase Request?
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
<div id="create-po-confirmation-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST"
			action="{{ route('save-request-details', ['stage'=>$stage,'id'=>$request->id ?? 0]) }}">
			@csrf
			<input type="hidden" name="generate_purchase_order" value="1" />
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-file-account"></i> Create Purchase Order Confirmation</h5>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label><input type="checkbox" id="toggle-split-checker" name="split_items" value="YES" /> PO contain items
						that you want to split between 2 or more suppliers?</label>
				</div>
				<div class="d-none" id="po-split-fields">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Items</label>
								<select class="form-control po-split-select" name="supplier_items[]" data-placeholder="Select Items..." multiple>
									@foreach ($normalItems as $req_item)
									<option value="{{ $req_item->id }}">{{ $req_item->item_name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Suppliers</label>
								<select class="form-control po-split-select" name="supplier_ids[]" data-placeholder="Select Suppliers..." multiple>
									@foreach ($normalItemsSuppliers as $sup)
									<option value="{{ $sup->id }}">{{ $sup->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="alert alert-info">
						<i class="fas fa-info-circle fa-2x"></i> Proceed with creating a purchase order?
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success">Yes, Proceed</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
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
@if(isOTPOptional())
<div id="issue-items-otp-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-numeric"></i> Confirm Material Issuance</h5>
			</div>
			<div class="modal-body">
				<div class="alert alert-callout alert-info text-lg">
					<i class="mdi mdi-information fa-1x"></i> Are you sure that you want to issue out these items from the
					inventory?
				</div>
				<div class="form-group">
					<input type="hidden" name="requester_otp" class="form-control" value="123456" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success save-details-form" id="issue-items-otp-modal-save-btn"
					data-type="issue-items">Confirm</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
@else
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
				<button type="button" class="btn btn-success save-details-form" id="issue-items-otp-modal-save-btn"
					data-type="issue-items">Confirm</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
@endif
<div id="quote-accept-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<div class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-cash-usd"></i> Supplier Quote</h5>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success save-details-form" data-type="accept-supplier-quote">Accept,
					Quote</button>
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
					<input class="form-control" type="text" name="amount" value="" required />
				</div>
				<div class="form-group">
					<label><strong>Reason</strong></label>
					<textarea class="form-control" name="amount" placeholder="Reason for Quote Adjustment..." required></textarea>
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
				<button type="button" class="btn btn-danger save-details-form" data-type="cancel-ammendment">Reject</button>
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
				<button type="button" class="btn btn-primary save-details-form" data-type="approve-ammendments-details">Yes,
					Approve Amendment</button>
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
					<label class="control-label">Change approver to</label>
					<select name="user_id" class="form-control" placeholder="Select Approver"></select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">Change</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="req-location-selector-modal" class="modal fade" role="dialog">
	<div method="POST" class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('req-locations-add') }}">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title"><i class="mdi mdi-map-marker"></i> Request Location</h5>
			</div>
			<div class="modal-body">
				<div class="form-group">
					@foreach ($reqlocs as $l)
					<div class="p-1 border-bottom select-location" style="cursor: pointer">{{ $l->name }}</div>
					@endforeach
				</div>
				@if ($isInventoryProcurement || $isInventoryStoreManager)
				<div class="form-group text-center" id="toggle-hidden-add-location">
					<i class="mdi mdi-plus"></i> Add Location
				</div>
				@endif
				<div class="form-group hidden hidden-add-location">
					<label class="control-label">New Location</label>
					<input type="text" name="name" placeholder="Location Name..." class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary hidden hidden-add-location">Add</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="data-attr-holder" class="hidden" data-stores="{{ json_encode($allStores) }}"></div>
@if($stage == "Request for Quotation")
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	tinymce.init({
			selector: 'textarea.editor',
			height: 315
		});
</script>
@endif
<script>
	var EmailsWithIssues = {};
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
						<th>Currency</th>
						<th>Amount</th>
						<th>VAT(?)</th>
					</thead>
					<tbody>
						@foreach ($kitPossibleItems ?? array() as $req_item)
							<?php
								$isKitRow = filled($req_item->catalog_number)
									&& ! is_numeric($req_item->catalog_number)
									&& ! \Illuminate\Support\Str::isUuid((string) $req_item->catalog_number);
							?>
							<tr class="for-item-{{ $req_item->inventory_sub_category_id }} quote-row">
								<td><input type="checkbox" class="duplicatable quote-check" value="{{ $isKitRow ? $req_item->kit_item_ids : $req_item->id }}" name="quote[id][{{$isKitRow ? $req_item->kit_item_ids : $req_item->id}}]"></td>
								<td>{{ $isKitRow ? $req_item->catalog_number." - ".$req_item->kit_item_name : $req_item->item_name }}</td>
								<td>{{ $isKitRow ? 1 : $req_item->quantity }}</td>
								<td>
									<select name="quote[currency][{{$isKitRow ? $req_item->kit_item_ids : $req_item->id}}]" class='form-control duplicatable' data-placeholder="Select Currency...">
										@foreach ($availableCurrencies as $p)
											<option value="{{ $p->id }}" {{ $p->id == $request->currency ? 'selected' : '' }}>{{ $p->name }}</option>
										@endforeach
									</select>
								</td>
								<td>
									<input type="number" style="min-width: 200px" min="0" step="any" value="" readonly class="form-control form-control-sm duplicatable" name="quote[amount][{{$isKitRow ? $req_item->kit_item_ids : $req_item->id}}]" />
								</td>
								<td>
									<select name="quote[vat][{{$isKitRow ? $req_item->kit_item_ids : $req_item->id}}]" class='form-control duplicatable' data-placeholder="Select VAT...">
										@foreach (getTaxes() as $p)
											<option value="{{ $p }}">{{ $p }}%</option>
										@endforeach
									</select>
									<div style="font-size:11px; display: flex; width: 100%; align-items: center">
										<div style="padding:4px">
											<input class="duplicatable" type="radio" checked name="quote[inc][{{$isKitRow ? $req_item->kit_item_ids : $req_item->id}}]" value="0"> Exc
										</div>	
										<div style="padding:4px">
											<input class="duplicatable" type="radio" name="quote[inc][{{$isKitRow ? $req_item->kit_item_ids : $req_item->id}}]" value="1"> Inc
										</div>
									</div>
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
			}, 2000);
			var isAlreadyInPreview = false;
			@if($hasEmailBody)
				isAlreadyInPreview = true;
				$('#rfq-body-editor').slideUp(0);
				$('#rfq-body-preview').slideDown(0);
			@endif
			$('#edit-body-preview').on('click', function(){
				if(!isAlreadyInPreview){
					$('#rfq-body-editor').slideUp(0);
					$('#rfq-body-preview').slideDown(0);
					$('#edit-body-preview').html(`<i class="fas fa-edit"></i> Edit Body`);
				}
				else{
					$('#rfq-body-editor').slideDown(0);
					$('#rfq-body-preview').slideUp(0);
					$('#edit-body-preview').html(`<i class="fas fa-eye"></i> Preview Body`);
				}
				isAlreadyInPreview = !isAlreadyInPreview;
			});

		});

		var getNoteRow = function($data){
			var noteTitle = $data.title || '';
			var noteDescription = $data.description || '';
			var $row = `
				<tr class="note-row new">
					<td class="row-id"></td>
					<td>
						${ noteTitle ? `<em>${ noteTitle }</em>` : '' }
						<select class="form-control" name="notes[type][]" placeholder="Type..." required>
							@foreach (getNoteTypes() as $item)
								<option value="{{ $item }}" ${ $data.type == '{{ $item }}' ? 'selected' : '' } >{{ $item }}</option>
							@endforeach
						</select>
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_user_name || '' }" readonly />
						<input type="hidden" class="note-type-val" name="created_by" value="${ $data.current_user || '' }" />
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_time || '' }" readonly />
					</td>
					<td nowrap>
						<span class="mdi mdi-android-messages btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#view-note-modal"
							data-description="${ noteDescription.replace(/"/g, '&quot;') }"> Description</span>
						<input type="hidden" name="notes[description][]" value="${ noteDescription.replace(/"/g, '&quot;') }" />
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
					<td class="text-center align-middle">
						<input type="checkbox" class="row-select-checkbox" title="Select row" />
					</td>
					<td class="item-id row-id ${ ($data.rfq_sent || 0) == '1' ? 'rfq_sent' : '' }"></td>
					<td>
						<div class="form-group">
							<select name="suppliers[id][]" style="min-width: 200px; font-size: 12px" class="form-control selected-supplier" placeholder="Select Supplier..." required>
								@foreach ($normalItemsSuppliers as $item)
									<option value="{{ $item->id }}"  ${ ($data.supplier_id || 0) == '{{ $item->id }}' ? 'selected' : '' }
										data-phone="{{ $item->phone }}" data-email="{{ $item->email }}" data-invalidemails = "{{ is_valid_email(trim($item->email)) }}"
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
							@if(isETCU())
							<a href="/supplier-rfq-pdf/${$data.supplier_id}/{{ $request->id }}" target="_blank" class="text-primary float-right"><i class="mdi mdi-download"></i></a>
							@endif
							${ ($data.rfq_sent || 0) == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' }
						</div>
					</td>
					<td nowrap>
						<div class="form-group">
							${ ($data.quote_received || 0) == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' }
							<span class="quote-btn"></span>
						</div>
					</td>
				</tr>
			`;

			$row = $($row);

			if($data.supplier_id && ($data.quote_received || 0) == '0'){
				var btn = $(`<span class="btn btn-sm btn-default text-primary ml-2 accept-quote-btn"
						data-toggle="modal" data-target="#quote-accept-modal" data-supplier="`+$data.supplier_id+`">`+(($data.rfq_sent || 0) == '1' ? 'Accept Quote' : 'Enter Price')+`</span>`);
				$row.find('.quote-btn').append(btn);
			}
			if($data.supplier_id && ($data.quote_received || 0) == '1'){
				var btn = $(`<span class="btn btn-sm btn-default text-primary ml-2 accept-quote-btn"
					data-toggle="modal" data-target="#quote-accept-modal" data-supplier="`+$data.supplier_id+`">
					<i class="mdi mdi-pencil"></i>
				</span>`);
				$row.find('.quote-btn').append(btn);
			}

			return $row.clone();
		}

		var getItemRow = function($data){
			var $row = `
				<tr class="item-row new">
					<td class="text-center align-middle">
						<input type="checkbox" class="row-select-checkbox" title="Select row" />
					</td>
					<td class="item-id"></td>
					<td>
						<div class="form-group">
							<select name="items[item_id][]" style="min-width: 200px; font-size: 12px" class="form-control selected-item" placeholder="Select Item..." required><option></option></select>
							@if (isETCU() && ! in_array($stage, ['Request to Store', 'Material Issuance', 'Goods Receipt']))
							<select name="items[item_account_id][]" style="min-width: 200px; font-size: 12px" class="form-control" placeholder="Select Account..." required>
								<option value="">Select Account...</option>
								@foreach ($accounts as $acc)
									<option value="{{ $acc->account_id }}"><small>({{ clear_underscore($acc->type) }}) {{ $acc->name }}</small></option>
								@endforeach
							</select>
							@endif
						</div>
					</td>
					<td>
						<div class="form-group">
							<input type="text" class="form-control" name="items[brand][]" style="min-width: 110px; max-width: 130px" placeholder="Brand..." />
							<div class="d-none" style="display:none">
								<select name="items[item_brand_id][]" class="form-control selected-item-brand d-none" data-selected="" placeholder="Select Item Brand..."></select>
							</div>
						</div>
					</td>
					<td nowrap>
						<div class="form-group" style="position: relative">
							<span class="btn btn-default btn-sm text-primary" style="position: absolute; top: 0px; right: 0px; z-index: 1" data-target="#req-location-selector-modal" data-toggle="modal">
								<i class="mdi mdi-map-marker"></i>
							</span>
							<textarea class="form-control item-description" name="items[comments][]" style="min-width: 140px; max-width: 160px; z-index: 0"></textarea>
						</div>
					</td>
					<td>
						<div class="form-group">
							<select name="items[uom][]" class="form-control selected-item-uom" style="min-width: 140px" placeholder="Select Item UoM..." ><option></option></select>
						</div>
					</td>
					@if(in_array($stage, array("Request to Store", "Purchase Request", "Request for Quotation", "Loan", "Lend")))
					<td nowrap>
						<div class="form-group">
							<span class="form-control open-quantity" style="min-width: 70px; max-width: 80px">0.00</span>
							<input type="hidden" min="0.00" name="items[open_quantity][]" style="min-width: 70px" class="form-control open_quantity" placeholder="Quantity..." />
						</div>
					</td>
					@endif
					<td>
						<div class="form-group">
							<input type="number" min="0" name="items[quantity][]" step="any" style="min-width: 70px; max-width: 90px" class="form-control user-quantity" placeholder="Quantity..." required />
						</div>
					</td>
					@if($stage == "Goods Receipt")
					<td>
						<div class="form-group">
							<input type="number" min="0.00" name="items[received_quantity][]" style="min-width: 100px" class="form-control user-quantity" step="any" placeholder="Quantity..." required />
						</div>
					</td>
					@endif
					@if($stage == "Request to Store")
						<td>
							<div class="form-group">
								<input type="number" min="0.00"  name="items[pending_quantity][]" style="min-width: 100px" class="form-control user-pending-quantity" placeholder="Quantity..." readonly />
							</div>
						</td>
						<td>
							<div class="form-group">
								<input type="number" min="0.00" name="items[issued_quantity][]" style="min-width: 100px" class="form-control issued-user-quantity" placeholder="Quantity..." readonly />
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
					@if($stage == "Purchase Request")
					<td nowrap>
						<div class="form-group">
							<span class="form-control delivery_date" style="min-width: 100px">{{ \Carbon\Carbon::now()->addDays(7) }}</span>
							<input type="hidden" class="delivery_date" name="delivery_date" value="{{ \Carbon\Carbon::now()->addDays(7) }}" />
						</div>
						<div class="form-group">
							<input type="hidden" min="0.00" name="items[net_value][]" style="min-width: 100px" value="" class="form-control items-total" {!! isset($request->status) && $request->status == "In Preparation" || !isset($request->status) ? '' : 'disabled="true"' !!} readonly />
						</div>
					</td>
					@endif
					@if(in_array($stage,["Request for Quotation", "Purchase Orders"]))
						<td>
							<div class="form-group">
								<select name="items[mode][]" class="form-control" style="min-width: 140px" placeholder="Select Shipping Mode...">
									@foreach(getShippingMode() as $mode)
										<option value="{{ $mode }}">{{ $mode }}</option>
									@endforeach
								</select>
							</div>
						</td>
					@endif
					@if(!in_array($stage,["Purchase Request", "Gate Pass", "Loan", "Lend", "Request to Store"]))
					<td>
						<div class="form-group">
							<select name="items[store_id][]" class="form-control selected-store" style="min-width: 180px" data-placeholder="Select Store...">
								@foreach ($allStores as $store)
									<option value="{{ $store->id }}" data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
								@endforeach
							</select>
						</div>
					</td>
					<td>
						<div class="form-group">
							<select name="items[slot_id][]" style="min-width: 160px"  class="form-control store-slots" data-placeholder="Select Slot..."></select>
						</div>
					</td>
					<td>
						<div class="form-group">
							<select name="items[currency][]" class='form-control {{ $stage == "Purchase Orders" ? "trigger-save" : "" }}' data-placeholder="Select Currency..." required>
								@foreach ($availableCurrencies as $p)
									<option value="{{ $p->id }}">{{ $p->name }}</option>
								@endforeach
							</select>
						</div>
					</td>
					<td>
						<div class="form-group">
							<input type="number" min="0.00" name="items[net_value][]" style="min-width: 100px" value="0.00" class="form-control items-total" required />
						</div>
					</td>
					@endif
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
			var recheckReason = $(`<input type="hidden" name="recheck_reason" class="reject-reason-hidden" />`);
			var awardReason = $(`<input type="hidden" name="award_reason" class="award-reason-hidden" />`);
			var approveThis = $(`<input type="hidden" name="approve_this" value="1" />`);
			var approvalDiv = $(`<input type="hidden" name="approval_id" />`);
			var approvalID;
			var supplierID;
			var awardedQuote;
			var isMultipleAward;
			var isKitRow;

			@if($request->downloadable_link == "pending")
				var btn = $(`
					<a class="btn btn-sm btn-transparent text-success mr-2 download-link" target="_blank" download>
						<i class="mdi mdi-file-pdf-outline"></i> Download
					</a>
					<a class="btn btn-sm btn-transparent text-default" href="{{ route('req-report-generate-pdf', ['id'=>$request->id]) }}">
						<i class="mdi mdi-sync"></i>
					</a>
				`);

				var checkPDFFunc;

				var checkPDF = function(){
					$.ajax({
						url: "{{ route('check-pdf-processing-progress', ['id'=>$request->id]) }}",
						dataType: "json",
						beforeSend: function(){

						},
						success: function(js){
							if(js.status!=false && js.status!="pending"){
								var dBtn = btn.clone();
								$('#downloadable-link').html('');
								$('#downloadable-link').append(dBtn);
								$('#downloadable-link').find('.download-link').attr('href', js.status);
								$('#downloadable-link').find('.download-link').prop('href', js.status);

								clearInterval(checkPDFFunc);
							}
						}
					});
				}

				checkPDFFunc = window.setInterval(() => {
					checkPDF();
				}, 5000);

			@endif

			$('#req-location-selector-modal').on('show.bs.modal', function(e){
				var commentbox = $(e.relatedTarget).parents('.form-group').find('textarea');
				var closeBTN = $(this).find('[data-dismiss="modal"]');
				var currentText = commentbox.val();

				$(this).find('.select-location').off('click');
				$(this).find('.select-location').on('click', function(){
					var thisLoc = $(this).text();
					var newText = thisLoc+' '+($.trim(currentText) == "" ? "" : "- "+currentText);
					commentbox.val(newText);
					closeBTN.trigger('click');
				});
			});

			$('#toggle-hidden-add-location').on('click', function(){
				$(this).parents('form').find('.hidden-add-location').toggleClass('hidden');
			});

			$('#confirm-requester-approval-link').on('click', function(){
				if(confirm("Are you sure that you confirm to verifying these items?")){
					var url = $(this).data('link');
					window.location = url;
				}
			});

			$('[name="expiry[]"]').on('change', function(){
				var val = $.trim($(this).val());
				var siblingLN = $(this).parents('tr').find('[name="lot_no[]"]');

				if(val != ''){
					siblingLN.parents('td').css('background-color', '#c38888');
					siblingLN.prop('required', true);
					siblingLN.attr('required', true);
				}
				else{
					siblingLN.parents('td').css('background-color', 'inherit');
					siblingLN.removeProp('required');
					siblingLN.removeAttr('required');
				}
			});

			$('.award-multiple-quote').on('change', function(){
				var val = $(this).data('requestid');

				$('[data-requestid="'+val+'"].award-multiple-quote').not(this).attr('checked', false);
				$('[data-requestid="'+val+'"].award-multiple-quote').not(this).prop('checked', false);
				$('[data-requestid="'+val+'"].award-multiple-quote').not(this).removeAttr('checked');
				$('[data-requestid="'+val+'"].award-multiple-quote').not(this).removeProp('checked');

				var nums = $('.award-multiple-quote:checked').length;

				if(nums > 1){
					$('#multiple-award-btn').removeClass('d-none');
				}
				else{
					$('#multiple-award-btn').addClass('d-none');
				}
			});

			$('input[type="number"]').on('change', function(){
				var $val = parseFloat($(this).val());
				var max = parseFloat($(this).attr('max'));

				console.log($val, $(this).attr('max'), "VAL TO MAX");

				if($val > max){
					$(this).val(max);
				}
			});

			var initPoSplitSelect2 = function(){
				var $modal = $('#create-po-confirmation-modal');
				$modal.find('.po-split-select').each(function(){
					var $select = $(this);
					if ($select.hasClass('select2-hidden-accessible')) {
						$select.select2('destroy');
					}
					$select.select2({
						width: '100%',
						placeholder: $select.data('placeholder') || 'Select...',
						allowClear: true,
						dropdownParent: $modal
					});
				});
			};

			$('#toggle-split-checker').on('change', function(){
				if($(this).is(":checked")){
					$('#po-split-fields').removeClass('d-none');
					initPoSplitSelect2();
				}
				else{
					$('#po-split-fields').addClass('d-none');
				}
			});

			$('#create-po-confirmation-modal').on('shown.bs.modal', function(){
				if ($('#toggle-split-checker').is(':checked')) {
					initPoSplitSelect2();
				}
			});

			$('#create-po-confirmation-modal').on('hidden.bs.modal', function(){
				$(this).find('.po-split-select').each(function(){
					if ($(this).hasClass('select2-hidden-accessible')) {
						$(this).select2('destroy');
					}
				});
			});

			$("#undo-supplier-award").on('show.bs.modal', function(e){
				var quote = $(e.relatedTarget).data('quote');
				$("#undo-supplier-award").find('form').attr('action', '/undo-supplier-award/'+quote.quotes_id);
				$("#undo-supplier-award").find('form').prop('action', '/undo-supplier-award/'+quote.quotes_id);
			});

			$("#delete-extra-charge-modal").on('show.bs.modal', function(e){
				var btn = $(e.relatedTarget);
				var id = btn.data('id');

				var form = $(this).find('form');

				form.attr('action', '/remove-extra-charge/'+id);
				form.prop('action', '/remove-extra-charge/'+id);

			});

			$('.swap-extra-titles').on('click', function(){
				var fGP = $(this).parents('.form-group');
				var select = fGP.find('.select-holder');
				var input = fGP.find('input');

				input.toggleClass('d-none');

				if(input.hasClass('d-none')){
					select.removeClass('d-none');
					input.val('');
				}
				else{
					select.addClass('d-none');
				}
			});

			$('.item-change-reason-div').slideUp(0);

			$('.notify-item-change').on('change', function(){
				let changedVal = $(this).data('value');
				let val = $(this).val();
				let item = $(this).data('item');

				if(val != changedVal){
					$(this).parents('td').find('.item-change-reason-div').slideDown(200);
					$(this).parents('td').find('.item-change-reason-div textarea').prop('required', true);
				}
				else{
					$(this).parents('td').find('.item-change-reason-div textarea').val('');
					$(this).parents('td').find('.item-change-reason-div').slideUp(200);
					$(this).parents('td').find('.item-change-reason-div textarea').prop('required', false).removeProp('required');
				}
			});

			$("#delete-supplier-quote").on('show.bs.modal', function(e){
				var btn = $(e.relatedTarget);
				var item = btn.data('item');
				var quote = btn.data('quote');
				isKitRow = $(e.relatedTarget).data('iskit');

				$(this).find('form').attr('action', '/remove/supplier-quote/'+(isKitRow == "Yes" ? quote.quotes_id : quote.id));

				$(this).find('.modal-title').html(`<i class="mdi mdi-cash-usd"></i> Remove Quote for ${item}`);

				$(this).find('.modal-body').find('.quote-item').html(`${item}`);
			});

			$('.trigger-save').on('change', function(){
				$('#save-other-changes').removeClass('hidden');
			});

			$('[name="nature_of_purchase"]').on('change', function(){
				var val = $(this).children('option:selected').val();

				if(val == "Capex"){
					$('#project-number-field').removeClass('hidden hide');
					$('#project-number-field').addClass('required');
					$('#project-number-field').find('input').attr('required', true);
					$('#project-number-field').find('input').attr('required', true);
				}
				else{
					$('#project-number-field').addClass('hidden hide');
					$('#project-number-field').removeClass('required');
					$('#project-number-field').find('input').removeAttr('required');
					$('#project-number-field').find('input').removeProp('required');
				}
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

			
			$('.form-range').on('change', function(){
				var $rat = parseFloat($(this).val()).toFixed(1);
				var max_score = parseFloat($(this).attr('max'));
				let guides = $(this).data('guides');

				let guideLabel = $(this).parent().find('.guide-title');
				
				guides.forEach(element => {
					if(element.lower_value){
						if($rat >= element.lower_value && $rat <= element.upper_value){
							guideLabel.text(element.title);
						}
					}
				});

				var $rating = $rat/max_score*100;

				var $cls = $rating == 100 ? 'bg-success' : ($rating < 100 && $rating > 60 ?
				'bg-info' : ($rating <= 60 && $rating > 35 ? 'bg-warning' : 'bg-danger'));

				console.log($cls, $rating, $rat, max_score);

				$(this).removeClass('bg-success bg-info bg-warning bg-danger');
				$(this).addClass($cls);

				$(this).parents('.form-group').find('h6').find('.badge').text($rat+"/"+max_score);
			});

			$('.update-supplier-criteria-rating-modal').on('show.bs.modal', function(e){
				$('.form-range').trigger('change');
			});

			$('#trigger-add-attachments').on('click', function(){
				$('#Attachments-tab').trigger('click');

				window.setTimeout(() => {
					$('span[data-target="#add-attachment-modal"]').trigger('click');
				}, 222);
			});

			$("#change-approver-modal").on('show.bs.modal', function(e){
				var rlTarget = $(e.relatedTarget);
				var approvalID = rlTarget.data('approval');
				var title = rlTarget.data('title');
				var users = rlTarget.data('users');
				var managerU = rlTarget.data('manusers');
				var select = $(this).find('select[name="user_id"]');

				$(this).find('.modal-title').html(`<i class="mdi mdi-account-convert"></i> Set Approval for ${title}`);

				select.empty()
				$.each(users, function(u, s){
					var op = $(`<option value="${s}">${u}</option>`);
					select.append(op);
				});

				var manOpt = $(`<optgroup label="Managers"></optgroup>`);

				$.each(managerU, function(u, s){
					var op = $(`<option value="${s}">${u}</option>`);
					manOpt.append(op);
				});

				select.append(manOpt);

				select.trigger('change');

				var form = $(this).find('form');
				form.attr('action', '/change-req-approver/{{ $stage }}/'+approvalID);
				form.prop('action', '/change-req-approver/{{ $stage }}/'+approvalID);
			});

			$("#edit-supplier-quote").on('show.bs.modal', function(e){
				var btn = $(e.relatedTarget);
				var item = btn.data('item');
				var quote = btn.data('quote');
				isKitRow = $(e.relatedTarget).data('iskit');

				$(this).find('form').attr('action', '/edit/supplier-quote/'+(isKitRow == "Yes" ? quote.quotes_id : quote.id));

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
				var maxAllowed = $(this).attr('max');
				@if(in_array($stage,getRequisitionWorkflow()) || in_array($stage, getRequestToStoreWorkflow()))
					@if($request->nature_of_purchase != "Capex")
						if(val > maxAllowed){
							alert("Requested Quantity can not exceed "+maxAllowed);
							$(this).val('');
							$(this).focus();
						}
					@else
						$(this).removeAttr('max');
						$(this).removeProp('max');
					@endif
				@endif

				if(val == 0){
					$(this).val('');
				}

				var price = parseFloat($(this).parents('tr').find('.items-total').data('unit_value'));
				$(this).parents('tr').find('.items-total').val(val*price);
			});

			$('#quote-accept-modal').on('show.bs.modal', function(e){
				var items = $(e.relatedTarget).data('items');

				console.log(items, 'ITEMS')

				var $body = getQuoteAcceptModalBody();

				$('#quote-accept-modal').find('.modal-body').html($body);

				var $attachBTN = $(`
					<div class="form-group">
						<label>Attach Supplier Quote</label>
						<input type="file" name="supplier_quote" class="form-control duplicatable" />
					 </div>
				`);

				$('#quote-accept-modal').find('.modal-body').append($attachBTN);

				$.each(items, function(i, e){
					$('#quote-accept-modal').find('tr.for-item-'+e).addClass('hasThis');
				});

				$('#quote-accept-modal').find('tr.quote-row').not('.hasThis').remove();

				supplierID = $(e.relatedTarget).data('supplier');
			})

			$('#enter-reject-modal').on('show.bs.modal', function(e) {
				approvalID = $(e.relatedTarget).attr('data-approval') || $(e.relatedTarget).data('approval');
			});

			$('#enter-recheck-modal').on('show.bs.modal', function(e) {
				approvalID = $(e.relatedTarget).attr('data-approval') || $(e.relatedTarget).data('approval');
			});

			$('#award-rfq-to-user').on('show.bs.modal', function(e) {
				$(this).find('[name="awarded_quote_is"]').remove();
				var isMultiple = $(e.relatedTarget).data('multiple');
				isMultipleAward = false;

				if(isMultiple == 'yes'){
					$('#awarding-multiple-alert').removeClass('d-none');
					var selectedQuotesId = [];
					var vals = $('input.award-multiple-quote:checked').each(function(){
						selectedQuotesId.push($(this).val());
					});
					isMultipleAward = true;

					awardedQuote = {"id": selectedQuotesId.join(',')};
				}
				else{
					$('#awarding-multiple-alert').addClass('d-none');
					awardedQuote = $(e.relatedTarget).data('item');
					isKitRow = $(e.relatedTarget).data('iskit');
				}

			});

			$('#confirm-accept-modal').on('show.bs.modal', function(e) {
				approvalID = $(e.relatedTarget).attr('data-approval') || $(e.relatedTarget).data('approval');
			});

			$('.save-details-form').on('click', function(){
				var type = $(this).data('type');
				var thisBTN = $(this);
				var inform = $(this).data('alert') || false;
				var skipRequiredValidation = [
					'confirm-approval-reason',
					'reject-with-reason',
					'recheck-with-reason',
					'award-with-reason',
					'get-approval-details',
					'mark-as-completed',
					'accept-goods-receipt',
					'issue-items'
				].indexOf(type) !== -1;

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

					// console.log($reject_message)

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
					var $acceptModal = thisBTN.closest('.modal');
					if($acceptModal.length === 0){
						$acceptModal = $('.update-supplier-criteria-rating-modal:visible').first();
					}
					if($acceptModal.length === 0){
						$acceptModal = $('#accept-goods-otp-modal').first();
					}

					var otp_value = $.trim($acceptModal.find('[name="requester_otp"]').val() || '');
					if(otp_value.length != 6){
						alert("Please provide the OTP Code(6 characters).");
						return false;
					}

					var missingStoreSlot = false;
					$('#req-items tr.item-row').each(function(){
						var $tr = $(this);
						var $store = $tr.find('[name="items[store_id][]"]');
						var $slot = $tr.find('[name="items[slot_id][]"]');

						$store.prop('disabled', false).removeAttr('disabled');
						$slot.prop('disabled', false).removeAttr('disabled');

						if(!$store.val()){
							var firstStore = $store.find('option[value!=""]').first().val();
							if(firstStore){
								$store.val(firstStore).trigger('change');
							}
						}

						if(!$slot.val()){
							var firstSlot = $slot.find('option[value!=""]').first().val();
							if(firstSlot){
								$slot.val(firstSlot).trigger('change');
							}
						}

						if(!$store.val() || !$slot.val()){
							missingStoreSlot = true;
						}
					});

					if(missingStoreSlot){
						alert('Please open the Items tab and select Store and Slot for each line before confirming receipt.');
						return false;
					}

					var delivery_rating = [];
					$acceptModal.find('.rating-delivery').each(function(){
						let gSID = $(this).data('rid');
						let criteria = $(this).find('.rating-delivery-criteria').val();
						let reason = $(this).find('.rating-delivery-reason').val() || '';

						delivery_rating.push({
							"id": gSID,
							"rating": criteria,
							"reason": reason
						});
					});

					$('#details-form').find('input[name="supplier_rating_criteria"], input[name="accept_goods_receipt"], input[name="otp_value"]').remove();
					$('<input>', {
						type: 'hidden',
						name: 'supplier_rating_criteria',
						value: JSON.stringify(delivery_rating)
					}).appendTo('#details-form');
					$('<input>', {
						type: 'hidden',
						name: 'accept_goods_receipt',
						value: '1'
					}).appendTo('#details-form');
					$('<input>', {
						type: 'hidden',
						name: 'otp_value',
						value: otp_value
					}).appendTo('#details-form');
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
						alert("No Item selected")
						return;
					}

					if(!valid){
						alert("Confirm that you have selected an Item and entered the price")
						return;
					}

					var $duplicatable = $('#quote-accept-modal').find('.duplicatable');

					$duplicatable.attr('style', 'width: 1px !important; height:1px !important; overflow: hidden!important');

					$('#details-form').append(`<input type="hidden" name="supplier_id" value="${supplierID}" />`);
					$('#details-form').append(`<input type="hidden" name="is_rfq_quote" value="1" />`);
					$('#details-form').find("#duplicates-for-quotes").html($duplicatable);
				}

				if(type == "send-rfq-details"){
					if(!$.isEmptyObject(EmailsWithIssues)){
						var invalidEmailsMSG = [];
						$.each(EmailsWithIssues, function(e, em){
							invalidEmailsMSG.push(e+' - '+em);
						});

						alert('Missing or wrong email format for '+invalidEmailsMSG.join(', '));
						return;
					}
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

				if(type == 'recheck-with-reason'){
					var reason = $('#recheck-reason-text').val();

					reason = $.trim(reason);

					if(reason == ""){
						alert('Please Provide a reason for return.')
						return;
					}

					recheckReason.val(reason);

					approvalDiv.val(approvalID);
					$('#details-form').append(approvalDiv);
					$('#details-form').append(recheckReason);
				}

				if(type == 'award-with-reason'){
					var reason = $('#award-reason-text').val();

					reason = $.trim(reason);

					if(reason == ""){
						alert('Please provide a comment on why this supplier was chosen.')
						return;
					}

					awardReason.val(reason);


					$('#details-form').append(`<input type="hidden" name="awarded_quote_is" value="${isKitRow == "Yes" ? awardedQuote.quotes_id : awardedQuote.id}" />`);
					$('#details-form').append(awardReason);

					if(isMultipleAward){
						$('#details-form').append(`<input type="hidden" name="is_multiple_row" value="yes" />`);
					}
				}

				if(type == 'confirm-approval-reason'){
					if(!approvalID){
						alert('Approval step was not selected. Close this dialog and click Approve again.');
						return false;
					}

					approvalDiv.val(approvalID);
					$('#details-form').append(approveThis);
					$('#details-form').append(approvalDiv);
				}

				if(type == 'save-details'){
					$('#details-form').find('[name="get_approval"]').remove();
					$('#req-items').find('select, input').removeAttr('disabled');
					$('#req-items').find('select, input').removeProp('disabled');
				}

				if(type == 'issue-items' || type == 'accept-goods-receipt'){
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

				if(!skipRequiredValidation){
				$('#details-form input:required, #details-form textarea:required').map(function() {
					isValidIn &= this.validity['valid'] ;

					theField = this;

					if(!this.validity['valid']){
						allInvalidHolder.push([theField, 'input']);
					}
					else{
						$(theSelect).parent().css('border', 'inherit');
					}

				}) ;

				if (isValidIn) {
					console.log('valid input!');
				} else{
					subMit = false;
					console.log('invalid select!');
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
				} else{
					console.log('invalid select!');
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
				}

				if(subMit){
					thisBTN.prop('disabled', true);
					// Native submit() skips HTML5 constraint validation on hidden-tab required fields.
					// jQuery .submit() can cancel silently then leave the button dead if we unbind click.
					var detailsForm = document.getElementById('details-form');
					if(detailsForm){
						detailsForm.submit();
					}
				}
				else{
					alert("Confirm that you have provided all the values for ("+theFieldsInval.join(',')+")");
					$('#details-form').find('input[name="accept_goods_receipt"]').remove();
					$('#details-form').find('input[name="issue_out_items"]').remove();
					$('#details-form').find('input[name="supplier_rating_criteria"]').remove();
					$('#details-form').find('input[name="otp_value"]').remove();
					thisBTN.prop('disabled', false);
				}
			});

			$('.add-supplier-row').on('click', function(){
				$('#supplier-list').find('tr.no-data').remove();
				generateSupplierRow();

				$('.trigger-save').trigger('change');
			});

			$('#supplier-list').on('change', 'tr .selected-supplier', function(){
				var selected = $(this).children('option:selected');
				var phone = selected.data('phone');
				var rating = selected.data('rating') || 0;
				var email = selected.data('email');
				var invalidEmails = selected.data('invalidemails');
				var parentTr = $(this).parents('tr');

				$.ajax({
					url: '/fetch-supplier-items/'+selected.val(),
					dataType: 'json',
					beforeSend: function(){

					},
					success: function(ls){
						// console.log(ls);
						selected.data('items', ls);
						var items = selected.data('items');
						if(parentTr.find('.accept-quote-btn').length > 0){
							parentTr.find('.accept-quote-btn').data('items', items);
						}
					}
				});

				// console.log(items);

				if(invalidEmails != ""){
					EmailsWithIssues[selected.text()] = invalidEmails;
				}

				parentTr.find('.supplier-email').text(email);
				parentTr.find('.supplier-phone').text(phone);
				parentTr.find('.supplier-rating').html(rating+` <i class="mdi mdi-star text-success"></input>`);
				var arr = [];
			});

			$('#req-items').on('change', 'tr .selected-store', function(){
				var selected = $(this).children('option:selected');
				var slots = selected.data('slots') || [];
				var slots = selected.data('slots') || [];

				if(typeof slots === 'string'){
					try { slots = JSON.parse(slots); } catch (e) { slots = []; }
				}

				var slotDiv = $(this).parents('tr').find('[name="items[slot_id][]"]');
				var selectedVal = slotDiv.data('slot');

				slotDiv.html('');

				if (!Array.isArray(slots)) {
					slots = [];
				}

				$.each(slots, function(i, s){
					if (!s || typeof s !== 'object') {
						return;
					}
					var newOption = new Option(s.name, s.id, false, false);
					slotDiv.append(newOption);
					slotDiv.append(newOption);
				});

				if (selectedVal) {
					slotDiv.val(String(selectedVal));
				}
				slotDiv.trigger('change');
			});

			var fetchAvailableStock = function($parentTr, brand_id=0, item_id){
				$.ajax({
					url: '/api-get-available-items/'+item_id+'/'+brand_id+'/{{ $request->id ?? 0 }}',
					data: {
						"created_at":"{{ ($request->created_at ?? \Carbon\Carbon::now()) }}"
					},
					dataType: 'json',
					beforeSend: function(){
						$parentTr.find('.open_quantity').val('Fetching...')
					},
					success: function(js){
						$parentTr.find('.open-quantity').text(js.formatted);
						$parentTr.find('.open_quantity').val(js.value);

						if(parseFloat(js.value) > 0){
							$parentTr.find('.open-quantity').css('border-color', '#ff6c00').css('border-width', '3px');
						}

						$parentTr.find('.delivery_date').text(js.delivery_date);
						$parentTr.find('.delivery_date').val(js.delivery_date);
						var available = js.value;
						@if(in_array($stage,getRequestToStoreWorkflow()))
							$parentTr.find('input.user-quantity').attr('max', available);
							$parentTr.find('input.user-quantity').prop('max', available);
							console.log("AVAILABLE", available);
						@endif
					}
				});
			}

			var defaultStores = $(`<option value=""></option>
				@foreach ($allStores as $store)
					<option value="{{ $store->id }}" data-slots='@json($store->slots->map->only(["id", "name"]))'>{{ $store->name }}</option>
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

				// console.log(unit_val);

				@if(in_array($stage, ['Request to Store', 'Material Issuance']))
					var $uoms = [js.uom, js.uom2];
				@else
					var $uoms = [js.uom];
				@endif

				var siblingBrands = $this.parents('tr').find('select.selected-item-brand').html(`<option value="0" selected>Non Specific</option>`);
				var selectedBrand = $this.parents('tr').find('select.selected-item-brand').data('selected');
				var quantityField = $this.parents('tr').find('input.user-quantity');
				var $UoMSelect = $this.parents('tr').find('select.selected-item-uom');
				var selectedUoM = $UoMSelect.data('selected') || js.uom;

				var addedAcc = $this.data('account');
				var itemAccount = $this.parents('td').find('select').not('.selected-item');
				itemAccount.val($.trim(addedAcc) == '' ? js.account_id : addedAcc).trigger('change');

				$.each(brands, function(j,s){
					var $op = $(`<option value="${s.id}">${s.name}</option>`);
					siblingBrands.append($op);
				});

				// After approval the UOM control is readonly text (no select). Do not wipe it.
				if ($UoMSelect.length && !$UoMSelect.is(':disabled')) {
					$UoMSelect.empty();
					$.each($uoms, function(u, m){
						if (!m) {
							return;
						}
						var $op = $(`<option value="${m}">${m}</option>`);
						$UoMSelect.append($op);
					});
					$UoMSelect.val(selectedUoM).trigger('change');
				}
				siblingBrands.val(selectedBrand).trigger('change');

				@if(in_array($stage,getRequisitionWorkflow()))
					var order_quantity_max = js.maximum_order_quantity;
					console.log(order_quantity_max, "MAX ORDER");
					quantityField.attr('max', order_quantity_max);
					quantityField.prop('max', order_quantity_max);
				@endif

				// fetchAvailableStock(parentTr, parentTr.find('select.selected-item-brand').val(), selected.val());

				if(text){
					$.ajax({
						url: '/get-store-slots-by-item/'+$itemID,
						dataType: 'json',
						success: function(js){
							var selectedVal = parentTr.find('[name="items[store_id][]"]').data('selected');

							if(js.length > 0){
								parentTr.find('[name="items[store_id][]"]').html('').trigger('change');
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
					url: '{{ $request->request_type == "Gate Pass" ? route("get_items_via_ajax", ["cat_id" => $gate_pass_category_id ]) : route("get_items_via_ajax") }}',
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

			$('#req-items').find('select[name="items[mode][]"]').each(function(){
				var placeholder = $(this).attr('placeholder') || 'Select Shipping Mode...';
				$(this).select2({
					placeholder: placeholder,
					width: 'style'
				});
			});

			$('#req-items').on('change', 'tr .selected-item', function(){
				var itemID = $(this).val();
				var $this = $(this);
				$.ajax({
					url: '/get_item_details/'+itemID+'/{{ $request->id }}',
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
						url: '{{ $request->request_type == "Gate Pass" ? route("get_items_via_ajax", ["cat_id" => $gate_pass_category_id ]) : route("get_items_via_ajax") }}',
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
				$(this).closest('tr').find('.row-select-checkbox').prop('checked', $(this).hasClass('bg-selected'));
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

			$('#supplier-list').on('dblclick', 'tr.item-row .item-id, tr.item-row .row-id', function(){
				$(this).toggleClass('bg-selected');
				$(this).closest('tr').find('.row-select-checkbox').prop('checked', $(this).hasClass('bg-selected'));
			});

			$('.delete-item-row').on('click', function(){
				if($('#req-items').find('.item-id.bg-selected').length && confirm("Are you sure you want to remove this row?")){
					$('#req-items').find('.item-id.bg-selected').parents('tr').remove();
					$('.trigger-save').trigger("change");
				}
				else{
					alert("No row selected. Use the checkbox to select a row");
				}
			});

			$('.delete-this-row').on('click', function(){
				var holder = $(this).data('holder');
				if($(holder).find('.row-id.bg-selected').length > 0 && confirm("Are you sure you want to remove this row?")){
					$(holder).find('.row-id.bg-selected').parents('tr').remove();
					$('.trigger-save').trigger("change");
				}
				else{
					alert("No row selected. Use the checkbox to select a row");
				}
			});

			$('#req-items, #supplier-list').on('change', '.row-select-checkbox', function(){
				var $idCell = $(this).closest('tr').find('.item-id, .row-id').first();
				if ($idCell.hasClass('rfq_sent')) {
					$(this).prop('checked', false);
					return;
				}
				if ($(this).is(':checked')) {
					$idCell.addClass('bg-selected');
				} else {
					$idCell.removeClass('bg-selected');
				}
			});

			$('.modal-add-data').on('click', function(){
				var type = $(this).data('type');
				var modal = $(this).parents('.modal');

				if(type == "note"){
					if (generateNoteRow(modal) === false) {
						return;
					}
				}
				else{
					generateAttachmentRow(modal);
				}

				modal.find('[data-dismiss="modal"]').trigger('click');
			});

			var generateNoteRow = function($modal = false, $dt = false){
				if($modal){
					var noteType = $modal.find('[name="type"]').val();
					var noteTitle = $modal.find('[name="title"]').val();
					var noteDescription = $.trim($modal.find('[name="description"]').val() || '');

					if (!noteDescription) {
						alert('Please enter a note description.');
						return false;
					}

					var data = {
						"type": noteType,
						"title": noteTitle,
						"description": noteDescription,
						"current_user": $modal.find('[name="current_user"]').val(),
						"current_user_name": $modal.find('[name="current_user_name"]').val(),
						"current_user_email": $modal.find('[name="current_user_email"]').val(),
						"current_time": $modal.find('[name="current_time"]').val()
					}
				}

				if($dt){
					var data = {
						"type": $dt.type,
						"title": $dt.title,
						"description": $dt.description,
						"current_user": $dt.user_id,
						"current_user_name": $dt.user_name,
						"current_user_email": $dt.user_email,
						"current_time": $dt.created_at
					}
				}

				if (!data) {
					return false;
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

				return true;
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

				sRow.find('.item-id').on('dblclick', function(){
					if(!$(this).hasClass('rfq_sent')){
						$(this).toggleClass('bg-selected');
						$(this).closest('tr').find('.row-select-checkbox').prop('checked', $(this).hasClass('bg-selected'));
					}
				});

				sRow.find('.row-select-checkbox').on('change', function(){
					var $idCell = $(this).closest('tr').find('.item-id');
					if ($idCell.hasClass('rfq_sent')) {
						$(this).prop('checked', false);
						return;
					}
					if ($(this).is(':checked')) {
						$idCell.addClass('bg-selected');
					} else {
						$idCell.removeClass('bg-selected');
					}
				});

				sRow.find('.item-id').append(`
					<input type="hidden" name="suppliers[supplier_rfq_id][]" value="${ n }" />
				`);

				$('#supplier-list').append(sRow);

				sRow.find('.form-control').trigger('change');

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

			$('.extras-toggler').on('click', function(){
				$(this).find('i').toggleClass('chevron-down chevron-up');

				if($(this).find('i').hasClass('chevron-up')){
					$('#extras-div').removeClass('d-none');
				}
				else{
					$('#extras-div').addClass('d-none');
				}
			});

			$('.download-quotes-csv').on('click', function(){
				var csv = $('#quotes-supplier-totals').data('csv');
				let csvContent = "data:text/csv;charset=utf-8,"
					+ csv.map(csv => csv.join("\t")).join("\n");

				var encodedUri = encodeURI(csvContent);
				var link = document.createElement("a");
				link.setAttribute("href", encodedUri);
				link.setAttribute("download", "{{ $request->request_code }}.csv");
				document.body.appendChild(link); // Required for FF

				link.click();
			});

			$('.supplier-checked').on('change', function(){
				var supplier = $(this).attr('name');
				var supplierTotal = 0;
				$('[name="'+supplier+'"]:checked').each(function(){
					supplierTotal+= parseFloat($(this).val());
				});

				var $supplierTotalDIV = $('#quotes-supplier-totals').find('[type="'+supplier+'"]');

				if(supplierTotal > 0){
					$supplierTotalDIV.removeClass('d-none');
					if($supplierTotalDIV.length == 0){
						$supplierTotalDIV = $(`<div class="m-3" type="${supplier}"></div>`)
						$('#quotes-supplier-totals').append($supplierTotalDIV);
					}
					$supplierTotalDIV.html(`<strong class="text-muted" style="font-size: 14px">${supplier.replace('[]', '')}</strong> - ${supplierTotal.toLocaleString()}`)
				}
				else{
					$supplierTotalDIV.addClass('d-none');
				}
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