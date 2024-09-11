<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Approvals;
use App\ChartOfAccount;
use App\EntityNote;
use App\RequestEntity;
use App\EntityAttachment;
use App\RequestEntityItem;
use App\RequestEntityExtraCharge;
use App\Http\Controllers\MailController as Mailer;
use App\InventorySubCategories;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RequisitionController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function remove_extra_charge(Request $request, $id)
	{
		// return json_encode($request->all());
		$extra = RequestEntityExtraCharge::find($id);

		$extra->delete();

		return redirect()->back()->with('success', 'Extra charge removed.');
	}

	public function jump_request_to_status(Request $request, $id)
	{
		$entity = RequestEntity::find($id);

		$entity->status = $request->status;
		$entity->save();

		return redirect()->back()->with('success', 'Status changed.');
	}

	public function requester_verification_confirmation(Request $request, $id)
	{
		$entity = \App\RequestEntity::find($id);

		if (!isset($entity->id) || \Auth::user()->id != $entity->issue_to) {
			return redirect()->back()->with('error', 'Action not allowed.');
		}

		$entity->requester_confirmation = 1;
		$entity->save();

		return redirect()->back()->with('success', 'Requester Confirmation has been completed.');
	}

	public function add_extra_charge(Request $request, $id)
	{
		// return json_encode($request->all());
		$extra = new RequestEntityExtraCharge;

		$extra->title = trim($request->title_confirm) == "" ? $request->title : $request->title_confirm;
		$extra->request_id = $id;
		$extra->currency_id = $request->currency;
		$extra->cost = $request->cost;
		$extra->save();

		return redirect()->back()->with('success', 'An extra charge was added.');
	}

	public function open_stage(Request $request, $stage)
	{
		$isSomeBody = isUserSomebody(\Auth::user());

		// $list = \App\ViewRequestEntity::where('request_type', $stage)
		// 	->where('inventory_location_id', getCurrentUserLocation()->id)
		// 	->where('is_lab_kit', 0)->where('delete', 0)->where('status', '!=', 'Completed')
		// 	->where('is_supplement', 0)->orderBy('id', 'desc');

		// $completed_list = \App\ViewRequestEntity::where('request_type', $stage)
		// 	->where('inventory_location_id', getCurrentUserLocation()->id)
		// 	->where('is_lab_kit', 0)->where('delete', 0)->where('status', '=', 'Completed')
		// 	->where('is_supplement', 0)->orderBy('id', 'desc');

		// if($isSomeBody === false){
		// 	$departmentID = \Auth::user()->department_id;
		// 	$completed_list = $completed_list->where('department_id', $departmentID);
		// 	$list = $list->where('department_id', $departmentID);
		// }

		// $completed_list = $completed_list->get();
		// $list = $list->get();

		$kit_list = [];
		$lab_department_id = getConfigByName('lab_department_id');
		$lab_department_id = count($lab_department_id) > 0 ? $lab_department_id[0]->value : 0;

		if (\Auth::user()->department_id == $lab_department_id && $stage == "Purchase Request") {
			$kit_list =  \App\ViewRequestEntity::where('request_type', $stage)
				->where('inventory_location_id', getCurrentUserLocation()->id)
				->where('is_lab_kit', 1)->where('delete', 0)->orderBy('id', 'desc')->get();
		}
		// return response()->json($kit_list, 200);

		$approvals = Approvals::where('stage', $stage)->orderBy('level', 'asc')->get();
		// return view('layouts.inventory.requisition.index', compact('stage', 'approvals', 'list', 'kit_list', 'completed_list'));

		return view('layouts.inventory.requisition.index-server-side', compact('stage', 'approvals', 'kit_list'));
	}

	public function show($stage, $id, $ammendment = false)
	{
		// $hasAccess = restrictRequesterAccess(\Auth::user());
		// $restricted = ["Purchase Orders", "Goods Receipt", "Goods Return", "Gate Pass"];
		// $request = RequestEntity::where('id', $id)->where('request_type', $stage)->first();

		// if(in_array($stage, $restricted)){
		// 	if(isset($request->id)){
		// 		if(!$hasAccess){
		// 			return redirect('/inventory-home')->with('error', 'You have no access to '.$stage);
		// 		}
		// 	}
		// }
		if ($ammendment === false) {
			$request = RequestEntity::where('id', $id)->where('request_type', $stage)->first() ?? new RequestEntity;
		} else {
			$request = RequestEntity::where('request_code', $id)->where('request_type', $stage)->where('ammendment', $ammendment)->first();

			if (!isset($request->id)) {
				return redirect()->back()->with('error', 'That version of ' . $stage . ' was not found');
			}
		}

		$documentFlow = [];
		$isLL = false;

		if (isset($request->request_type)) {
			$parentRequisition = RequestEntity::find($request->parent_material_requisition) ?? $request;

			if (in_array($parentRequisition->request_type, ['Loan', 'Lend'])) {
				$isLL = $parentRequisition->request_type;
			}

			if (in_array($stage, getRequisitionWorkflow($isLL))) {
				if ($isLL) {
					$documentFlow[$isLL] = [RequestEntity::find($request->parent_material_requisition) ?? $request];
				} else {
					$documentFlow["Purchase Request"] = [RequestEntity::find($request->parent_material_requisition) ?? $request];
				}
				$stages = getRequisitionWorkflow($isLL);
			} else {
				$documentFlow[in_array($request->request_type, ['Lend', 'Loan']) ? $request->request_type : "Request to Store"] = [RequestEntity::find($request->parent_material_requisition) ?? $request];
				$stages = getRequestToStoreWorkflow();
			}

			array_shift($stages);

			// return json_encode($documentFlow);

			foreach ($stages as $item) {
				if (in_array($stage, getRequisitionWorkflow($isLL))) {
					$documentFlow[$item] = RequestEntity::where('request_type', $item)->where('parent_material_requisition', $documentFlow[$isLL ? $isLL : 'Purchase Request'][0]->id)->get();
				} else {
					$startStage = in_array($request->request_type, ['Lend', 'Loan']) ? $request->request_type : "Request to Store";
					if (!in_array($item, ['Lend', 'Loan'])) {
						$documentFlow[$item] = count($documentFlow[$startStage]) == 0 ? [] : RequestEntity::where('request_type', $item)->where('parent_material_requisition', $documentFlow[$startStage][0]->id)->get();
					}
				}
			}

			if (isset($documentFlow['Goods Return']) && count($documentFlow['Goods Return']) == 0) {
				unset($documentFlow['Goods Return']);
			}
		}

		if ($ammendment === false) {
			$ammendment = $request->ammendment;
		}

		$ammendment_count = RequestEntity::where('request_code', $request->request_code)->where('request_type', $stage)->count();

		// return "<pre>".json_encode($documentFlow)."</pre>";
		// Log::info($item." <><><><><><><> ".json_encode($documentFlow, JSON_PRETTY_PRINT));

		$reqlocs = \App\RequisitionLocation::orderBy('name')->get();

		$itempIDs = RequestEntityItem::where('request_id', $id)->select('inventory_sub_category_id')->pluck('inventory_sub_category_id')->toArray();

		$similarItems = RequestEntityItem::join('request_entities as re', 're.id', 'request_id')
			->select([
				'request_entity_items.inventory_sub_category_id',
				'request_entity_items.quantity',
				'request_entity_items.uom',
				'request_code',
				'request_id',
				DB::raw('DATEDIFF(NOW(), re.created_at) AS days_ago')
			])->with('sub_category')
			->whereIn('inventory_sub_category_id', $itempIDs)
			->where('re.id', '<', $id)
			->where('request_type', 'Purchase Request')
			->where('request_entity_items.created_at', '>=', Carbon::now()->subDays(4))
			->get();

		// return response()->json($similarItems);

		$criteria = \App\SuppliersRatingCriteria::where('request_id', $id)->where('is_current', 1)->get();

		$ratingScores = [];
		$ratingReason = [];
		foreach($criteria as $c){
			$ratingScores[$c->criteria_id] = $c->score;
			$ratingReason[$c->criteria_id] = $c->reason;
		}

		$accounts = ChartOfAccount::whereIn('type', ['accounts_payable','cost_of_goods_sold','expense','fixed_asset','other_current_asset','other_expense', 'stock'])
		->orderBy('name')->get();

		return view('layouts.inventory.requisition.show', compact('ratingReason', 'accounts', 'ratingScores', 'similarItems', 'reqlocs', 'stage', 'request', 'documentFlow', 'ammendment', 'ammendment_count', 'isLL'));
	}

	public function create_goods_receipt($request, $entity)
	{
		// return json_encode($request->all());

		$files = ["invoice", "delivery_note", "job_card"];
		$hasAnInvoice = false;
		$hasAttachments = 0;
		foreach ($files as $file) {
			if ($request->hasFile($file)) {
				$hasAttachments++;
			}

			if($file == "invoice"){
				$hasAnInvoice = true;
			}
		}

		if (isGRNDocumentsOptional() == false) {
			if (!$hasAnInvoice || $hasAttachments < 2) {
				return redirect()->back()->with('error', 'Please upload an invoice and a delivery note or job card.');
			}
		}

		if (!$request->has('issue_to') || $request->issue_to == '') {
			return redirect()->back()->with('error', 'Please provide the items verifier details.');
		}

		$itemsIDs = $request->item;
		$itemsReceived = $request->received;

		$createGoodsReceipt = false;

		$beyondPending = false;

		foreach ($itemsIDs as $it) {
			if (floatval($itemsReceived[$it]) > 0) {
				$createGoodsReceipt = true;
			}

			if (floatval($itemsReceived[$it]) > floatval($request->pending[$it])) {
				$beyondPending = true;
			}
		}

		if ($createGoodsReceipt == false) {
			return redirect()->back()->with('error', 'Goods Receipt not created. All items submitted had zero quantity values.');
		}

		if ($beyondPending) {
			return redirect()->back()->with('error', 'Goods Receipt not created. Some items have quantities exceeding the pending quantity.');
		}

		$itemsExpiry = $request->expiry;
		$lotNumber = $request->lot_no;
		$dateOfManufacture = $request->date_of_manufacture;

		$purchaseOrder = $entity->replicate();
		$purchaseOrder->parent_request = $entity->request_type;
		$purchaseOrder->request_code = getNamingConventionCode("Goods Receipt", false, "GR");
		$purchaseOrder->parent_request_id = $entity->id;
		$purchaseOrder->request_type = "Goods Receipt";
		$purchaseOrder->status = "Awaiting Approval";
		$purchaseOrder->approval_status = "";
		$purchaseOrder->issue_to = $request->issue_to;
		$purchaseOrder->created_by = \Auth::user()->id;
		$purchaseOrder->net_value = 0;
		$purchaseOrder->save();

		if (in_array($entity->request_type, ['Lend', 'Loan'])) {
			$purchaseOrder->request_initiator = $entity->created_by;
			$purchaseOrder->parent_material_requisition = $entity->id;
		}

		$approvals = getStageApprovals('Requisition', 'Goods Receipt');

		$firstApprover = false;

		$previousApprovers = [];
		foreach ($approvals as $app) {
			$approverID = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
				->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id')->first()->id;

			$entity_approval =  \App\EntityApproval::where('model_id', $purchaseOrder->id)->where('approval_id', $app->id)
				->where('model', 'Goods Receipt')->first() ?? new \App\EntityApproval;

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Goods Receipt';
			$entity_approval->model_id = $purchaseOrder->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;

			if (!$firstApprover) {
				$firstApprover = true;
				$entity_approval->is_current = 1;
				$entity_approval->save();
			}
		}

		$itemNames = [];

		foreach ($itemsIDs as $it) {
			if (floatval($itemsReceived[$it]) > 0) {
				$i = RequestEntityItem::find($it);
				$iO = $i->replicate();
				$iO->request_id = $purchaseOrder->id;
				$iO->gr_expiry = $itemsExpiry[$it] ?? '2099-12-31';
				$iO->quantity = $itemsReceived[$it];
				$iO->date_of_manufacture = $dateOfManufacture[$it];
				$iO->lot_no = $lotNumber[$it];
				$iO->save();

				$itemEntity = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

				$netValue = floatval($itemEntity->unit_price) * floatval($iO->quantity);

				$purchaseOrder->net_value += floatval($netValue);
				$purchaseOrder->save();

				$itemNames[] = $itemEntity->name . "(" . $iO->quantity . ")";
			}
		}

		$purchaseOrder->description = implode(", ", $itemNames);
		$purchaseOrder->save();

		foreach ($files as $file) {
			if ($request->hasFile($file)) {
				$path = $request->$file->path();
				$attachment =  new EntityAttachment;
				$attachment->type = $file == "invoice" ? "GR - Invoice" : ($file == "job_card" ? "GR - Job Card" : "GR - Delivery Note");
				$attachment->title = $file;
				$attachment->description = $file;
				$attachment->model = $purchaseOrder->request_type;
				$attachment->model_id = $purchaseOrder->id;
				$attachment->created_by = \Auth::user()->id;

				$file = Storage::putFile('goods-receipt', new File($path));
				$file = explode('/', $file);

				$fName = '/storage/goods-receipt/' . urlencode(end($file));

				$attachment->file = (string) $fName;

				$attachment->save();
			}
		}

		$APPR_USER = \App\User::find($purchaseOrder->request_initiator);
		$MATERIAL_REQUISITION = \App\RequestEntity::find($purchaseOrder->parent_material_requisition ?? $entity->id);

		$companyDetails = getCompanyDetails();
		$OTPController = new OTPController;
		$OTP = $OTPController->create(
			(object) [
				'user_id' => $APPR_USER->id,
				'model' => "Goods Receipt",
				'model_id' => $purchaseOrder->id
			]
		);

		$body = 'Hi ' . $APPR_USER->name . ',<br><br>
			Items from your ' . $MATERIAL_REQUISITION->request_type . ' <a href="' . route("view-request-details", ["stage" => $MATERIAL_REQUISITION->request_type, "id" => $MATERIAL_REQUISITION->id]) . '">' . $MATERIAL_REQUISITION->request_code . '</a> have been delivered to the store. <br>
			Please use the code <b>' . $OTP->code . '</b> ' . (in_array($MATERIAL_REQUISITION->request_type, ["Loan", "Lend"]) ? "." : "when verifying the items delivered by the Supplier.") . '
			<h5>Message</h5>
			<small><em>' . $request->message . '</em></small>
			<br>Regards,<br>
			' . $companyDetails['name'];

		$mailData = array(
			'contacts' => array($APPR_USER->email),
			'body' => $body,
			'subject' => '[' . $MATERIAL_REQUISITION->request_code . '] Goods from your Requisition have arrived at store.'
		);

		if (isKECU()) {
			$this->send_creation_email($purchaseOrder, [], true, false, false);
		} else {
			$this->send_creation_email($purchaseOrder, [], true, true, true);
		}

		$mailer = new Mailer;

		sendTextMessage($APPR_USER->phone, in_array($MATERIAL_REQUISITION->request_type, ["Loan", "Lend"]) ? " Use the code " . $OTP->code . ", when receiving items from " . $MATERIAL_REQUISITION->request_type . " - " . $MATERIAL_REQUISITION->request_code : "You are required at the store to inspect the goods from " . $MATERIAL_REQUISITION->request_code . ". Your OTP is " . $OTP->code . ".");
		$sendMail = $mailer->html_email($mailData, 'default');

		$checkingUser = \App\User::find($request->issue_to);

		if (isset($checkingUser->email)) {
			$body = 'Hi ' . $checkingUser->name . ',<br>
				Goods Receipt ' . $purchaseOrder->request_code . ' has been created and from the items that you verified from Purchase Order ' . $entity->request_code . '. Click this link <a href="' . route("view-request-details", ["stage" => $purchaseOrder->request_type, "id" => $purchaseOrder->id]) . '"> to view the Goods Receipt and confirm your verification of the items.
				<br>Regards,<br>
				' . $companyDetails['name'];

			$mailData = array(
				'contacts' => array($checkingUser->email),
				'body' => $body,
				'subject' => '[' . $purchaseOrder->request_code . '] Your confirmation of items in this Goods Receipt is required.'
			);

			$mailer = new Mailer;
			$sendMail = $mailer->html_email($mailData, 'default');
		}

		return redirect()->route('view-request-details', ['stage' => $purchaseOrder->request_type, 'id' => $purchaseOrder->id])->with('success', 'Goods Receipt successfully created');
	}

	public function goods_return_has_invoice($gr_id, $message)
	{
		$grn = \App\RequestEntity::find($gr_id);
		$po = \App\RequestEntity::find($grn->parent_request_id);

		$companyDetails = getCompanyDetails();

		$supplier = \App\Supplier::find($grn->supplier_id);

		$tbody = '';

		$contacts = split_emails(";", $supplier->email);

		$items = \App\RequestEntityItem::join('inventory_sub_categories as isc', 'isc.id', '=', 'request_entity_items.inventory_sub_category_id')
			->where('request_entity_items.request_id', $gr_id)->where('request_entity_items.action', 'normal')
			->selectRaw('name, unit_type, quantity')->get();

		foreach ($items as $j => $i) {
			$tbody .= "
			<tr>
				<td style='border: 1px solid #333'>" . ($j + 1) . "</td>
				<td style='border: 1px solid #333'>" . $i->name . "</td>
				<td style='border: 1px solid #333'>" . $i->quantity . "" . $i->unit_type . "</td>
			</tr>";
		}

		$body = 'Hi ' . $supplier->name . ',<br><br>
			Some items from the Purchase Order ' . $po->request_code . ' have been returned. <br>
			<table style="border-collapse:collapse; width: 100%; border: 1px solid #333">
				<thead>
					<tr>
						<th style="border: 1px solid #333">No</th>
						<th style="border: 1px solid #333">Item</th>
						<th style="border: 1px solid #333">Quantity</th>
					</tr>
				</thead>
				<tbody>
					' . $tbody . '
				</tbody>
			</table>
			Reason given for return:
			<p>' . $grn->description . '</p>
				Please provide a credit note if you can not resupply the items.
			<br>Regards,<br>
			' . $companyDetails['name'];

		$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
		$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;

		$users = getUsersByRole($procurement_officer_role_id, true);

		foreach ($users as $s) {
			$contacts[] = $s->email;
		}

		$contacts = array_unique($contacts);

		$mailData = array(
			'contacts' => array_filter($contacts),
			'body' => $body,
			'subject' => '[' . $companyDetails["name"] . '] Some items from Purchase Order ' . $po->request_code . ' were returned'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return true;
	}

	public function create_gate_pass($request, $entity)
	{
		$gatePass = $entity->replicate();
		$gatePass->parent_request = $entity->request_type;
		$gatePass->request_code = getNamingConventionCode("Gate Pass", false, "GP");
		$gatePass->parent_request_id = $entity->id;
		$gatePass->request_type = "Gate Pass";
		$gatePass->status = "In Preparation";
		$gatePass->approval_status = "";
		$gatePass->created_by = \Auth::user()->id;
		$gatePass->net_value = 0;
		$gatePass->save();

		$entityItems = RequestEntityItem::where('request_id', $entity->id)->get();

		foreach ($entityItems as $i) {
			$iO = $i->replicate();
			$iO->request_id = $gatePass->id;
			$iO->save();
		}

		$store_manager_role_id = getConfigByName('store_manager_role_id')[0]->value ?? 0;
		$store_managers = \App\Role::find($store_manager_role_id)->getUsersByRole();
		$store_manager_emails = $store_managers->pluck('email')->toArray();

		if (isKECU()) {
			$this->send_creation_email($gatePass, $store_manager_emails, true, false, false);
		} else {
			$this->send_creation_email($gatePass, $store_manager_emails, true);
		}

		//kecu no nots for this

		return redirect()->route('view-request-details', ['stage' => $gatePass->request_type, 'id' => $gatePass->id])->with('success', 'Gate Pass created.');
	}

	public function create_goods_return($request, $entity)
	{
		// return "We here";
		// return response()->json($request->all(), 200);
		$files = ["invoice", "delivery_note"];
		$hasAttachments = false;
		foreach ($files as $file) {
			if ($request->hasFile($file)) {
				$hasAttachments = true;
			}
		}

		if (isGRNDocumentsOptional() == false) {
			if ($hasAttachments == false) {
				return redirect()->back()->with('error', 'Please upload either an invoice or delivery note.');
			}
		}

		$itemsIDs = $request->item;
		$itemsReceived = $request->received;

		$createGoodsReceipt = false;

		foreach ($itemsIDs as $it) {
			if (floatval($itemsReceived[$it]) > 0) {
				$createGoodsReceipt = true;
			}
		}

		if ($createGoodsReceipt == false) {
			return redirect()->back()->with('error', 'Goods Return not created. All items submitted had zero quantity values.');
		}

		$itemsExpiry = $request->expiry;
		$lotNumber = $request->lot_no;
		$dateOfManufacture = $request->date_of_manufacture;

		$purchaseOrder = $entity->replicate();
		$purchaseOrder->parent_request = $entity->request_type;
		$purchaseOrder->request_code = getNamingConventionCode("Goods Return", false, "GRN");
		$purchaseOrder->parent_request_id = $entity->id;
		$purchaseOrder->request_type = "Goods Return";
		$purchaseOrder->status = "Awaiting Approval";
		$purchaseOrder->approval_status = "";
		$purchaseOrder->created_by = \Auth::user()->id;
		$purchaseOrder->net_value = 0;
		$purchaseOrder->save();

		$approvals = getStageApprovals('Requisition', 'Goods Return') ?? [];

		$MATERIAL_REQUISITION = \App\RequestEntity::find($purchaseOrder->parent_material_requisition);

		$req_user = \App\User::find($MATERIAL_REQUISITION->request_initiator);

		$store_manager_role_id = getConfigByName('store_manager_role_id')[0]->value ?? 0;
		$store_managers = \App\Role::find($store_manager_role_id)->getUsersByRole();
		$store_manager_emails = $store_managers->pluck('email')->toArray();

		$procurement_user = \App\User::find($purchaseOrder->request_initiator);

		$contacts = array_merge([], [$req_user->email, $procurement_user->email]);
		$contacts = array_merge($contacts, $store_manager_emails);

		// return response()->json($contacts, 200);
		$previousApprovers = [];
		$firstApprover = false;
		foreach ($approvals as $app) {
			$approverID = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
				->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id')->first()->id;

			$entity_approval = \App\EntityApproval::where('model_id', $purchaseOrder->id)->where('approval_id', $app->id)
				->where('model', 'Goods Return')->first() ?? new \App\EntityApproval;

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Goods Return';
			$entity_approval->model_id = $purchaseOrder->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;

			if (!$firstApprover) {
				$firstApprover = true;
				$entity_approval->is_current = 1;
				$entity_approval->save();
			}
		}

		$itemNames = [];

		foreach ($itemsIDs as $it) {
			if (floatval($itemsReceived[$it]) > 0) {
				$i = RequestEntityItem::find($it);
				$iO = $i->replicate();
				$iO->request_id = $purchaseOrder->id;
				$iO->gr_expiry = $itemsExpiry[$it] ?? '2099-12-31';
				$iO->quantity = $itemsReceived[$it];
				$iO->date_of_manufacture = $dateOfManufacture[$it];
				$iO->lot_no = $lotNumber[$it];
				$iO->save();

				$itemEntity = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

				$netValue = floatval($itemEntity->unit_price) * floatval($iO->quantity);

				$purchaseOrder->net_value += floatval($netValue);
				$purchaseOrder->save();

				$itemNames[] = $itemEntity->name . "(" . $iO->quantity . ")";
			}
		}

		$purchaseOrder->description = implode(", ", $itemNames);
		$purchaseOrder->save();

		$hasInvoice = false;

		foreach ($files as $file) {
			if ($request->hasFile($file)) {
				if ($file == "invoice") {
					$hasInvoice = true;
				}
				$path = $request->$file->path();
				$attachment =  new EntityAttachment;
				$attachment->type = $file == "invoice" ? "GR - Invoice" : "GR - Delivery Note";
				$attachment->title = $file;
				$attachment->description = $file;
				$attachment->model = $purchaseOrder->request_type;
				$attachment->model_id = $purchaseOrder->id;
				$attachment->created_by = \Auth::user()->id;

				$nfile = Storage::putFile('goods-return', new File($path));
				$nfile = explode('/', $nfile);

				$fName = '/storage/goods-return/' . urlencode(end($nfile));

				$attachment->file = (string) $fName;

				$attachment->save();
			}
		}

		$companyDetails = getCompanyDetails();

		$body = 'Hi,<br><br>
			Some items from ' . $entity->request_code . ' have been returned. Reason given for return:
			<p><em>' . $request->message . '</em></p>
			Click <a href="' . route("view-request-details", ["stage" => $purchaseOrder->request_type, "id" => $purchaseOrder->id]) . '">here</a> to view the Goods return note.
			<br>Regards,<br>
			' . $companyDetails['name'];

		$mailData = array(
			'contacts' => $contacts,
			'body' => $body,
			'subject' => '[' . $entity->request_code . '] Some Items returned to supplier'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');



		$purchaseOrder->description = $request->message;
		$purchaseOrder->save();

		if ($hasInvoice) {
			$this->goods_return_has_invoice($purchaseOrder->id, $request->message);
		}

		return redirect()->route('view-request-details', ['stage' => $purchaseOrder->request_type, 'id' => $purchaseOrder->id])->with('success', 'Goods Return Created and notifications sent out.');
	}

	public function send_creation_email($entity, $contacts, $requiresProcurement = false, $requiresSiteManager = false, $requiresDepartmentHead = false)
	{
		return true;
		$companyDetails = getCompanyDetails();

		$emailList = [];

		$subject = $entity->request_type . " [" . $entity->request_code . "] has been created.";

		$initiator = \App\User::find($entity->request_initiator);

		if (isset($initiator->email)) {
			$contacts[] = $initiator->email;
		}

		$creator = \App\User::find($entity->created_by);

		if (isset($creator->email)) {
			$contacts[] = $creator->email;
		}

		if ($requiresProcurement) {
			$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
			$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;

			$procurementOfficers = getUsersByRole($procurement_officer_role_id, true);

			$emailList = [];

			foreach ($procurementOfficers as $au) {
				$emailList[] = $au->email;
			}

			$contacts = array_merge($contacts, $emailList);
		}

		if ($requiresSiteManager) {
			$site_manager_roles = getConfigByName('site_manager_role_id');
			$site_manager_role_id = count($site_manager_roles) > 0 ? $site_manager_roles[0]->value : 0;

			$site_managers = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')->where('ur.role_id', $site_manager_role_id)
				->where('users.department_id', $initiator->department_id)->selectRaw('users.id, users.name, users.email')->get();

			foreach ($site_managers as $au) {
				$emailList[] = $au->email;
			}

			$contacts = array_merge($contacts, $emailList);
		}

		if ($requiresDepartmentHead) {
			$departmental_head_roles = getConfigByName('departmental_head_role_id');
			$departmental_head_role_id = count($departmental_head_roles) > 0 ? $departmental_head_roles[0]->value : 0;

			$dHeads = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')->where('ur.role_id', $departmental_head_role_id)
				->where('users.department_id', $initiator->department_id)->selectRaw('users.id, users.name, users.email')->get();


			foreach ($dHeads as $au) {
				$emailList[] = $au->email;
			}

			$contacts = array_merge($contacts, $emailList);
		}

		$contacts = array_filter($contacts);
		$contacts = array_unique($contacts);

		$parentEntity = \App\RequestEntity::find($entity->parent_request_id);

		if (count($contacts) > 0) {
			$allItems = isset($entity) ? $entity->items($entity->ammendment) : array();

			$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

			$itemsBody = '';

			foreach ($normalItems as $i => $item) {
				$itemsBody .= '<tr style="border: 1px solid #ccc;">
					<td style="border: 1px solid #ccc;">' . ($i + 1) . '</td>
					<td style="border: 1px solid #ccc;">' . $item['item_name'] . '</td>
					<td style="border: 1px solid #ccc;">' . number_format($item['quantity'], 1) . '' . $item['unit_type'] . '</td>
				</tr>';
			}

			$theTable = '
				<table style="border-collapse: collapse; margin: 0px; width: 100%">
					<tr style="border: 1px solid #ccc;">
						<th style="border: 1px solid #ccc;">No.</th>
						<th style="border: 1px solid #ccc;">Item</th>
						<th style="border: 1px solid #ccc;">Quantity</th>
					</tr>
					' . $itemsBody . '
				</table>';

			$body = '
				Hi,<br><br>
				The following items were forwarded from your <b>' . $parentEntity->request_type . ' ' . $parentEntity->request_code . '</b> to <b>' . $entity->request_type . ' ' . $entity->request_code . '</b> on the system.<br><br>
				' . $theTable . '<br>
				Click <b><a href="' . route('view-request-details', ['stage' => $entity->request_type, 'id' => $entity->id]) . '">here</a></b> to view.
				<br>
				Regards,<br>
				' . $companyDetails['name'] . '
			';

			$mailData = array(
				'contacts' => $contacts,
				'body' => $body,
				'subject' => $subject
			);

			$mailer = new Mailer;

			$sendMail = $mailer->html_email($mailData, 'default');
		}

		return true;
	}

	public function create_purchase_order($entity, $request)
	{
		$po_items = RequestEntity::join('request_entity_items as rei', 'rei.request_id', 'request_entities.id')
			->where('request_entities.request_type', 'Purchase Orders')
			->where('request_entities.parent_request_id', $entity->id)
			->whereNotIn('request_entities.status', ['In Preparation', 'Awaiting Approval'])
			->selectRaw('rei.inventory_sub_category_id as sub_id')->get()->pluck('sub_id');

		if (count($po_items) > 0) {
			$po_sub_items =	RequestEntityItem::where('request_id', $entity->id)
				->whereIn('inventory_sub_category_id', $po_items)->selectRaw('id')->pluck('id');

			$quotes = \App\SupplierQuote::where('request_id', $entity->id)->where('is_awarded', 1)
				->whereNotIn('request_item_id', $po_sub_items);
		} else {
			$quotes = \App\SupplierQuote::where('request_id', $entity->id)->where('is_awarded', 1);
		}


		if ($quotes->count() == 0) {
			return redirect()->back()->with('error', 'No awarded items were found.');
		}

		$split_suppliers = $request->supplier_ids ?? [];
		$split_items = $request->supplier_items ?? [];

		if ($request->has('split_items') && count($split_suppliers) > 0) {
			$quotes = $quotes->orWhere(function ($query) use ($request) {
				$query->whereIn('request_item_id', $request->supplier_items);
				$query->whereIn('supplier_id', $request->supplier_ids);
			});
		}

		$quotes = $quotes->get();

		// return json_encode($quotes);

		$firstTime = array();

		$itemsCatNames = array();

		$sourceMaterialRequisition = false;

		foreach ($quotes as $quote) {
			$entityItems = RequestEntityItem::where('request_id', $entity->id)
				->where('ammendment', $entity->ammendment)->where('id', $quote->request_item_id)->get();

			$purchaseOrder = RequestEntity::where('supplier_id', $quote->supplier_id)
				->where('request_type', "Purchase Orders")
				->where('delete', 0)
				->where('parent_request', $entity->request_type)
				->whereIn('status', ['In Preparation', 'Awaiting Approval'])
				->where('parent_request_id', $entity->id)->first();

			$firstApprover = false;
			$firstApproval = false;

			$firstApproverNotified = [];

			if (!isset($purchaseOrder->id)) {
				$sendEmail = true;
				$purchaseOrder = $entity->replicate();
				$purchaseOrder->parent_request = $entity->request_type;
				$purchaseOrder->request_code = getNamingConventionCode("Purchase Orders", false, "PO");
				$purchaseOrder->parent_request_id = $entity->id;
				$purchaseOrder->request_type = "Purchase Orders";
				$purchaseOrder->status = "In Preparation";
				$purchaseOrder->approval_status = "";
				$purchaseOrder->supplier_id = $quote->supplier_id;
				$purchaseOrder->created_by = \Auth::user()->id;
				$purchaseOrder->net_value = 0;
				$purchaseOrder->save();

				$CREATOR = \App\User::find($purchaseOrder->request_initiator);

				$approvals = getStageApprovals('Requisition', 'Purchase Orders');
				$previousApprovers = [];

				foreach ($approvals as $app) {
					$hasDepartmentalApprovals = \App\UserDepartmentalApproval::where('role_id', $app->role_id)
						->where('department_id', $CREATOR->department_id)->first();

					if (!isset($hasDepartmentalApprovals->user_id)) {
						$approver = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
							->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id);

						if (getDepartmentalHeadID() == $app->role_id) {
							$requestInitiator = \App\User::find(isset($purchaseOrder->request_initiator) ? $purchaseOrder->request_initiator : $purchaseOrder->created_by);
							$approver = $approver->where('users.department_id', $requestInitiator->department_id);
						}

						$approver = $approver->selectRaw('users.id, users.email')->first();
					} else {
						$approver = \App\User::find($hasDepartmentalApprovals->user_id);
					}

					$approverID = $approver->id;

					$entity_approval = \App\EntityApproval::where('model_id', $purchaseOrder->id)->where('approval_id', $app->id)
						->where('model', 'Purchase Orders')->first() ?? new \App\EntityApproval;

					if (!$firstApprover) {
						$firstApprover = $approver;
						$entity_approval->is_current = 1;
						$entity_approval->save();
					}

					$entity_approval->approval_id = $app->id;
					$entity_approval->model = 'Purchase Orders';
					$entity_approval->model_id = $purchaseOrder->id;
					$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
					$entity_approval->user_id = $approverID;
					$entity_approval->save();

					$previousApprovers[] = $approverID;

					if (!$firstApproval) {
						$firstApproval = $entity_approval;
					}
				}
			}

			if (!isset($firstTime[$purchaseOrder->id])) {
				auditableDelete(RequestEntityItem::where('request_id', $purchaseOrder->id)->get());
				$firstTime[$purchaseOrder->id] = true;
				$itemsCatNames[$purchaseOrder->id] = [];
			}

			$purchaseOrder->net_value = 0;
			$purchaseOrder->save();

			foreach ($entityItems as $i) {
				$iO = $i->replicate();
				$iO->request_id = $purchaseOrder->id;
				$iO->net_value = $quote->quote_amount;
				$iO->currency = $quote->currency_id;
				$iO->vat_inc = $quote->vat_inc;
				$iO->vat_perc = $quote->vat_perc;
				if ($request->has('split_items') && in_array($i->id, $split_items) && in_array($quote->supplier_id, $split_suppliers)) {
					$iO->net_value = 0;
					$iO->quantity = 0;
				}
				$iO->save();

				// echo json_encode($iO)."<br/>";

				$purchaseOrder->net_value += floatval($iO->net_value);
				$purchaseOrder->save();


				$subCat = \App\InventorySubCategories::find($iO->inventory_sub_category_id);
				$itemsCatNames[$purchaseOrder->id][] = $subCat->name . "(" . $iO->quantity . ")";

				$inventoryItem = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

				$due_date = \Carbon\Carbon::now()->addDays($inventoryItem->external_lead_time);

				$purchaseOrder->due_date = $due_date > $purchaseOrder->due_date ? $due_date : $purchaseOrder->due_date;
			}

			$purchaseOrder->description = implode(", ", $itemsCatNames[$purchaseOrder->id]);
			$purchaseOrder->save();

			// return json_encode($entityItems);

			if (!isset($firstApproverNotified[$purchaseOrder->id]) && $firstApproval) {
				$companyDetails = getCompanyDetails();
				$approvalMoreInfo = $this->approvalMoreInfo($purchaseOrder, $firstApproval);

				$body = '
					Hi,<br>
					There is a ' . $purchaseOrder->request_type . ' Approval Request. Please find the details below<br>
					' . $approvalMoreInfo . '<br>
					Click this link
					<a href="' . route("view-request-details", ["stage" => $purchaseOrder->request_type, "id" => $purchaseOrder->id]) . '">' . route("view-request-details", ["stage" => $purchaseOrder->request_type, "id" => $purchaseOrder->id]) . '</a> to view the request.
					<br>Regards,<br>
					' . $companyDetails['name'];

				$mailData = array(
					'contacts' => [$firstApprover->email],
					'body' => $body,
					'subject' => '[Approval Request] Approval Request for Purchase Order - ' . $purchaseOrder->request_code
				);

				$mailer = new Mailer;

				$sendMail = $mailer->html_email($mailData, 'default');

				$firstApproverNotified[$purchaseOrder->id] = true;
			}

			$parentRequest = RequestEntity::find($purchaseOrder->parent_request_id);

			$pID = $purchaseOrder->parent_material_requisition;

			if ($parentRequest->request_type == "Purchase Request") {
				$pID = $parentRequest->id;
			}

			$sourceMaterialRequisition = RequestEntity::find($pID);
		}

		if ($sourceMaterialRequisition && isset($sourceMaterialRequisition->id)) {
			$sourceMaterialRequisition->status = "Completed";
			$sourceMaterialRequisition->save();
		}

		if (isset($purchaseOrder)) {
			if (isKECU()) {
				$this->send_creation_email($purchaseOrder, [], true, false, false);
			} else {
				$this->send_creation_email($purchaseOrder, [], true, true, true);
			}
		}

		// foreach($firstTime as $fT){
		// 	$REQ = new Request;
		// 	$REQ->request->add(['get_approval'=>true]);
		// 	$sent = $this->update($REQ, $purchaseOrder->request_type, $purchaseOrder->id, true);
		// }

		return redirect()->route('view-request-details', ['stage' => 'Purchase Orders', 'id' => $purchaseOrder->id])->with('success', 'Purchase Order successfuly created');
	}

	public function create_rfq_from_material_requisition($entity, $internal = false)
	{
		$existsRFQ = \App\RequestEntity::where('parent_request_id', $entity->id)->where('request_type', 'Request for Quotation')->get();

		if ($existsRFQ->count() > 0) {
			return redirect()->back()->with('error', 'RFQ already created!');
		}

		$rfq = $entity->replicate();
		$rfq->parent_request = $entity->request_type;
		$rfq->request_code = getNamingConventionCode("Request for Quotation", false, "RFQ");
		$rfq->parent_request_id = $entity->id;
		$rfq->request_type = "Request for Quotation";
		$rfq->status = "In Preparation";
		$rfq->approval_status = "";
		$rfq->created_by = \Auth::user()->id;
		$rfq->net_value = 0;
		$rfq->parent_material_requisition = $entity->id;
		$rfq->save();

		$approvals = getStageApprovals('Requisition', 'Request for Quotation');

		$previousApprovers = [];
		$firstApprover = false;
		foreach ($approvals as $app) {
			$approverID = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
				->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id')->first()->id;

			$entity_approval = \App\EntityApproval::where('model_id', $rfq->id)->where('approval_id', $app->id)
				->where('model', 'Request for Quotation')->first() ?? new \App\EntityApproval;

			if (!$firstApprover) {
				$firstApprover = \App\User::find($approverID);
				$entity_approval->is_current = 1;
				$entity_approval->save();
			}

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Request for Quotation';
			$entity_approval->model_id = $rfq->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;
		}

		$entityItems = RequestEntityItem::where('request_id', $entity->id)->where('ammendment', $entity->ammendment)->get();

		foreach ($entityItems as $i) {
			$iO = $i->replicate();
			$iO->request_id = $rfq->id;
			$iO->save();

			$rfq->net_value += floatval($iO->net_value);
			$rfq->save();
		}

		$hasSupplierForwardable = EntityAttachment::where('type', 'Supplier Item Specification File')
			->where('model', $entity->request_type)->where('model_id', $entity->id)->get();

		foreach ($hasSupplierForwardable as $forwardable) {
			$newFile = $forwardable->replicate();
			$newFile->model = $rfq->request_type;
			$newFile->model_id = $rfq->id;
			$newFile->created_by = \Auth::user()->id;
			$newFile->save();
		}

		if (isKECU()) {
			$this->send_creation_email($rfq, [], true, false, false);
		} else {
			$this->send_creation_email($rfq, [], true);
		}

		if ($internal) {
			return $rfq;
		}

		return redirect()->route('view-request-details', ['stage' => $rfq->request_type, 'id' => $rfq->id])->with('success', 'Request for Quotation successfully created.');
	}

	public function create_material_issuance($request, $entity)
	{
		// return response()->json($request->all(), 200);

		$entityItems = RequestEntityItem::where('request_id', $entity->id)->whereIn('id', $request->item)->get();
		$createMaterialIssuance = false;

		$beyondPending = false;

		foreach ($request->item as $i) {
			if (floatval($request->issued[$i]) > 0) {
				$createMaterialIssuance = true;
			}

			if (floatval($request->issued[$i]) > floatval($request->pending[$i])) {
				$beyondPending = true;
			}
		}

		// return "er";

		if ($createMaterialIssuance == false) {
			return redirect()->back()->with('error', 'Material Issuance not created. All items submitted had zero quantity values.');
		}

		if ($beyondPending) {
			return redirect()->back()->with('error', 'Material Issuance not created. Some items have quantities exceeding the pending quantity.');
		}

		$rfq = $entity->replicate();
		$rfq->parent_request = $entity->request_type;
		$rfq->request_code = getNamingConventionCode("Material Issuance", false, "MI");
		$rfq->parent_request_id = $entity->id;
		$rfq->request_type = "Material Issuance";
		$rfq->parent_material_requisition = $entity->id;
		$rfq->request_initiator = $entity->request_initiator ?? $entity->created_by;

		$rfq->created_by = \Auth::user()->id;
		$rfq->net_value = 0;
		$rfq->status = "Awaiting User Reception";
		$rfq->approval_status = "";
		$rfq->save();

		if (in_array($entity->request_type, ['Lend', 'Loan'])) {
			$rfq->parent_material_requisition = $entity->id;
		}

		$itemNames = [];
		foreach ($request->item as $i) {
			if (floatval($request->issued[$i]) > 0) {
				$item = RequestEntityItem::find($i);
				$iO = $item->replicate();
				$iO->request_id = $rfq->id;
				$iO->quantity = $request->issued[$i];
				$iO->save();

				$rfq->net_value += floatval($iO->net_value);
				$rfq->save();

				$subCat = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

				$itemNames[] = $subCat->name . "(" . $iO->quantity . ")";
			}
		}

		$rfq->description = implode(", ", $itemNames);
		$rfq->save();

		$companyDetails = getCompanyDetails();
		$theUser = \App\User::find($entity->created_by) ?? \Auth::user();

		$OTPController = new OTPController;
		$OTP = $OTPController->create(
			(object) [
				'user_id' => $theUser->id,
				'model' => "Material Issuance",
				'model_id' => $rfq->id
			]
		);

		$body = 'Hi ' . $theUser->name . ',<br><br>
			Items from your Request to Store <a href="' . route("view-request-details", ["stage" => "Request to Store", "id" => $entity->id]) . '">' . $entity->request_code . '</a> are available for pickup at the store. <br>
			Please use the code <b>' . $OTP->code . '</b> when picking up your items.
			<br>Regards,<br>
			' . $companyDetails['name'];

		$subject = '[' . $rfq->request_code . '] Items from your Request to Store are available for pick-up.';

		$store_manager_roles = getConfigByName('store_manager_role_id');
		$store_manager_role_id = count($store_manager_roles) > 0 ? $store_manager_roles[0]->value : 0;

		$storeManagers = getUsersByRole($store_manager_role_id, true);

		$emailList = [];

		foreach ($storeManagers as $sm) {
			$emailList[] = $sm->email;
		}

		if (isKECU()) {
			$this->send_creation_email($rfq, $emailList, true, false, false);
		} else {
			$this->send_creation_email($rfq, $emailList, false, true, true);
		}

		$this->notify_user($body, $theUser->email, $subject);
		sendTextMessage($theUser->phone, "Items from your Request to Store " . $entity->request_code . " are available for pickup. Your OTP is " . $OTP->code . ".");

		return redirect()->route('view-request-details', ['stage' => $rfq->request_type, 'id' => $rfq->id])->with('success', 'Purchase Request successfuly created.');
	}

	public function affect_inventory($action, $item, $entity)
	{
		$inventoryC = new \App\Http\Controllers\InventoryItemController;

		$theUser = \App\User::find($entity->request_initiator) ?? \Auth::user();
		if ($action == "issue") {
			$req = new Request;
			$req->category_id = $item->category()->id;
			$req->sub_category_id = $item->inventory_sub_category_id;
			$req->quantity = $item->quantity;
			$req->po_number = $entity->request_code ?? 'n/a';
			$req->lot_no = $item->lot_no;
			$req->slot = $item->slot_id;
			$req->store = $item->store_id;
			$req->transfer_to = $theUser->department_id;
			$req->issued_to = $theUser->id;
			$req->item_brand_id = $item->item_brand_id;

			$issueOut = $inventoryC->transfer($req, true);

			$item->inventory_item_id = $issueOut->id;

			$item->save();

			return $issueOut;
		} else {
			$myRequest = new Request;
			$myRequest->category_id = $item->category()->id;
			$myRequest->sub_category_id = $item->inventory_sub_category_id;
			$myRequest->supplier_id = $entity->supplier_id;
			$myRequest->price = $item->net_value;
			$myRequest->po_number = $entity->request_code;
			$myRequest->quantity = $item->quantity;
			$myRequest->slot = $item->slot_id;
			$myRequest->item_brand_id = $item->item_brand_id;
			$myRequest->lot_no = $item->lot_no;
			$myRequest->store = $item->store_id;
			$myRequest->expiry = $item->gr_expiry;
			$myRequest->date_of_manufacture = $item->date_of_manufacture;
			$myRequest->inventory_department_id = systemVariables('procurement_department');

			$receivedItem = $inventoryC->add($myRequest, true);

			// return print_r($receivedItem);

			$item->inventory_item_id = $receivedItem->id;

			$item->save();

			return $receivedItem;
		}
	}

	public function issue_out_items($request, $entity)
	{
		// return response()->json($request->all(), 200);

		$otp_value = $request->otp_value;

		$OTPController = new OTPController;

		$otp = $OTPController->close(
			(object)[
				"code" => $otp_value,
				"model" => "Material Issuance",
				"model_id" => $entity->id,
				"approved_by" => \Auth::user()->id
			]
		);

		if (!isset($otp->code)) {
			return redirect()->back()->with('error', 'No matching OTP code found for this Material Issuance.');
		}

		$hasZeroStoreIDs = [];
		$hasNoStock = [];
		foreach ($request->items['req_item_id'] as $i => $id) {
			$entityItem = RequestEntityItem::find($id);
			if ($entityItem->store_id == 0 || $entityItem->slot_id == 0) {
				$hasZeroStoreIDs[] = $entityItem->store_id;
			}

			$invSubCat = InventorySubCategories::find($entityItem->inventory_sub_category_id);
			$quantity = $invSubCat->available()['available'];

			if ($quantity < $request->items['quantity'][$i]) {
				$hasNoStock[] = $entityItem->sub_category->name . ' : ' . $request->items['quantity'][$i] . ' available : ' . $quantity;
			}
		}

		if (count($hasNoStock) > 0) {
			return redirect()->back()->with('error', 'Quantities for the following items : ' . implode(', ', $hasNoStock)) . ' exceed the available quantities.';
		}

		if (count($hasZeroStoreIDs) > 0) {
			return redirect()->back()->with('error', 'Some items are missing store and slot information.');
		}

		foreach ($request->items['req_item_id'] as $i => $id) {
			$entityItem = RequestEntityItem::find($id);

			$lot_no = $request->items['lot_no'][$i] ?? $request->request_code;

			$entityItem->lot_no = $lot_no;

			$quantity = $request->items['quantity'][$i];
			$issuedItem = $entityItem->replicate();

			$itemItself = \App\InventorySubCategories::find($entityItem->inventory_sub_category_id);

			// if($entityItem->uom != $itemItself->unit_type){
			// 	$quantity = getUoMConverstion($quantity, $entityItem->uom, $itemItself->unit_type);
			// }

			$issuedItem->quantity = $quantity;
			$issuedItem->action = 'issued_received';
			$issuedItem->net_value = floatval($issuedItem->quantity) / floatval($entityItem->quantity) * $entityItem->net_value;
			$issuedItem->status = 'completed';
			$issuedItem->issued_by = \Auth::user()->id;
			$issuedItem->save();

			$this->affect_inventory('issue', $issuedItem, $entity);

			$entityItem->status = "completed";

			$entityItem->save();
		}

		$status = "Completed";

		$parentReq = \App\RequestEntity::find($entity->parent_request_id);

		if (in_array($parentReq->request_type, ["Lend", "Loan"])) {
			$parentReq->status = "Items Issued Out";
			$parentReq->save();
		}

		$hasMI = issue_received_complete($entity->id);

		if ($hasMI['items'] == $hasMI['all']) {
			$parentReq->status = "Completed";
			$parentReq->save();
		}

		$entity->status = $status;
		$entity->save();

		$itemsFromParent = issue_received_complete($parentReq->id);

		if ($itemsFromParent['all'] == $itemsFromParent['completed']) {
			$parentReq->status = 'Completed';
			$parentReq->save();
		}

		return redirect()->back()->with('success', ' Items Issued.');
	}

	public function notify_user($body, $email, $subject, $file = false)
	{

		$mailData = array(
			'contacts' => is_array($email) ? $email : array($email),
			'body' => $body,
			'subject' => $subject
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->back()->with('success', 'User notification sent');
	}

	public function send_purchase_order($req)
	{
		// \App\SupplierRFQ::where('request_id', $req->id)->update(['rfq_sent'=>1]);

		$parentREQ = \App\RequestEntity::find($req->parent_request_id);

		$companyDetails = getCompanyDetails();
		$supplier = \App\Supplier::find($req->supplier_id);
		$quotes = \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
			->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
			->where('supplier_quotes.request_id', $req->parent_request_id)
			->where('supplier_quotes.is_awarded', 1)
			->selectRaw('ics.name, rei.quantity, rei.comments, ics.code')
			->where('supplier_quotes.supplier_id', $supplier->id)->get();

		if ($quotes->count() == 0 && $parentREQ->request_type != "Purchase Request") {
			return \redirect()->back()->with('error', 'Supplier Quotes not found.');
		}

		if (!isETCU()) {
			$approvalMoreInfo = $this->approvalMoreInfo($req, false, true, false);

			$body = '
			Hi ' . $supplier->name . ',<br><br>
			You were awarded the following items from ' . $parentREQ->request_type . ' - ' . $parentREQ->request_code . ' in the Purchase Order ' . $req->request_code . ' below:<br>
			' . $approvalMoreInfo . '
			<br>
			Regards,<br>
			' . $companyDetails['name'] . '
			';

			$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
			$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;

			$users = getUsersByRole($procurement_officer_role_id, true);

			$emails = split_emails(';', $supplier->email);

			foreach ($users as $s) {
				$emails[] = $s->email;
			}

			$emails = split_emails(';', $supplier->email);

			foreach ($users as $s) {
				$emails[] = $s->email;
			}

			$mailData = array(
				'contacts' => array_unique($emails),
				'body' => $body,
				'subject' => '[' . $companyDetails["name"] . '] Purchase Order ' . $req->request_code
			);

			if (trim($req->downloadable_link) != "" && $req->downloadable_link != "pending") {
				$parts = explode('/storage', $req->downloadable_link);
				$mailData['file'] = storage_path() . '/app' . end($parts);
			}

			$mailer = new Mailer;

			$sendMail = $mailer->html_email($mailData, 'default');
		}

		$req->status = "Purchase Order Sent";
		$req->save();

		$parentREQ->status = "Completed";
		$parentREQ->save();

		return \redirect()->back()->with('success', 'Purchase Order sent out to supplier.');
	}

	public function return_goods_to_supplier($request, $entity)
	{

		if ($request->has('return_action') && $request->return_action == 'credit_note') {
			if (!$request->hasFile('credit_note')) {
				return redirect()->back()->with('error', 'No credit note was attached.');
			} else {
				$path = $request->credit_note->path();
				$attachment =  new EntityAttachment;
				$attachment->type = "GR - Credit Note";
				$attachment->title = 'credit_note';
				$attachment->description = 'credit_note';
				$attachment->model = $entity->request_type;
				$attachment->model_id = $entity->id;
				$attachment->created_by = \Auth::user()->id;

				$file = Storage::putFile('goods-return', new File($path));
				$file = explode('/', $file);

				$fName = '/storage/goods-return/' . urlencode(end($file));

				$attachment->file = (string) $fName;

				$attachment->save();
				foreach ($request->items['req_item_id'] as $i => $id) {
					$entityItem = RequestEntityItem::find($id);
					$quantity = $request->items['quantity'][$i];

					if ($quantity > 0) {
						$receivedItem = $entityItem->replicate();
						$receivedItem->quantity = $quantity;
						$receivedItem->action = 'issued_received';
						$receivedItem->net_value = floatval($receivedItem->quantity) / floatval($entityItem->quantity) * $entityItem->net_value;
						$receivedItem->status = 'completed';

						$receivedItem->issued_by = \Auth::user()->id;
						$receivedItem->save();

						$entityItem->status = "completed";
						$entityItem->save();
					}
				}
			}
		}


		$entity->status = "Completed";
		$entity->save();

		$purchaseOrder = RequestEntity::find($entity->parent_request_id);
		$supplier_emails = split_emails(';', \App\Supplier::find($entity->supplier_id)->email);

		$companyDetails = getCompanyDetails();

		$body = 'Hi,<br><br>
			Some items from ' . $purchaseOrder->request_code . ' have been returned. Reason given for return;
			<p><em>' . $entity->description . '</em></p>.
			Please click <a href="' . route('download-requisition-doc', ['id' => $entity->id, 'type' => "goods-return-note"]) . '">here</a> to download the Goods Return Note.
			<br>Regards,<br>
			' . $companyDetails['name'];

		$mailData = array(
			'contacts' => $supplier_emails,
			'body' => $body,
			'subject' => '[' . $purchaseOrder->request_code . '] Goods Return Note Notice.'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		$entity->description = $request->message;

		$this->mark_po_as_complete($purchaseOrder->id);

		return redirect()->back()->with('success', 'Items have been returned to the supplier.');
	}

	public function accept_goods_receipt($request, $entity, $isInternal = false)
	{

		// return response()->json($request->all(), 200);

		$otp_value = $request->otp_value;

		$OTPController = new OTPController;

		$otp = $OTPController->close(
			(object)[
				"code" => $otp_value,
				"model" => "Goods Receipt",
				"model_id" => $entity->id,
				"approved_by" => \Auth::user()->id
			]
		);

		if (!isset($otp->code)) {
			return redirect()->back()->with('error', 'No matching OTP code found for this Goods Receipt.');
		}

		$anyPendingItems = array();
		foreach ($request->items['req_item_id'] as $i => $id) {
			$entityItem = RequestEntityItem::find($id);
			$quantity = $request->items['received_quantity'][$i];
			$expiry = $request->items['expiry'][$i] ?? '2099-12-31';
			$date_of_manufacture = $request->items['date_of_manufacture'][$i] ?? '2099-12-31';
			$lot_no = $request->items['lot_no'][$i] ?? $request->request_code;
			$store = $request->items['store_id'][$i];
			$slot = $request->items['slot_id'][$i];

			$entityItem->slot_id = $slot;
			$entityItem->store_id = $store;
			$entityItem->lot_no = $lot_no;
			$entityItem->gr_expiry = $expiry;
			$entityItem->date_of_manufacture = $date_of_manufacture;
			$entityItem->issued_by = \Auth::user()->id;

			$entityItem->save();

			if ($quantity > 0) {
				$receivedItem = $entityItem->replicate();
				$receivedItem->quantity = $quantity;
				$receivedItem->action = 'issued_received';
				$receivedItem->net_value = floatval($receivedItem->quantity) / floatval($entityItem->quantity) * $entityItem->net_value;
				$receivedItem->status = 'completed';
				$receivedItem->gr_expiry = $expiry;
				$receivedItem->date_of_manufacture = $date_of_manufacture;
				$receivedItem->slot_id = $slot;
				$receivedItem->store_id = $store;
				$receivedItem->lot_no = $lot_no;
				$receivedItem->issued_by = \Auth::user()->id;
				$receivedItem->save();

				$this->affect_inventory('receive', $receivedItem, $entity);

				if ($entityItem->pending() > 0) {
					$entityItem->status = "partial";
				} else {
					$entityItem->status = "completed";
				}

				$entityItem->save();
			}
		}

		$entityItems = RequestEntityItem::where('request_id', $entity->id)->where('ammendment', $entity->ammendment)
			->where('action', 'normal')->get();

		foreach ($entityItems as $i) {
			$anyPendingItems[] = $i->status;
		}

		// return json_encode($anyPendingItems);

		$status = "Goods Accepted";

		$parentReq = \App\RequestEntity::find($entity->parent_request_id);

		if (in_array($parentReq->request_type, ["Lend", "Loan"])) {
			$parentReq->status = "Goods Accepted";
			$parentReq->save();
		}

		$entity->status = $status;
		$entity->save();

		$Requester = \App\User::find($otp->user_id);

		$companyDetails = getCompanyDetails();

		$body = '
			Hi ' . $Requester->name . ',<br><br>
			Items from Goods Receipt[' . $entity->request_code . '] have been added to the inventory.
			Regards,<br>
			' . $companyDetails['name'] . '
		';

		$mailData = array(
			'contacts' => array($Requester->email),
			'body' => $body,
			'subject' => '[' . $entity->request_code . '] Goods Receipt Confirmation'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		if ($isInternal) {
			return true;
		}

		if($request->has('supplier_rating_criteria')){
			$criterias = json_decode($request->supplier_rating_criteria); 

			// return json_encode($criterias);

			$reasons = [];
			$ratings =[];
			foreach($criterias as $crit){
				$reasons[$crit->id] = $crit->reason;
				$ratings[$crit->id] = $crit->rating;
			}

			$newReqOBJ = new Request();

			$newReqOBJ->merge(['criteria'=>$ratings]);
			$newReqOBJ->merge(['reason'=>$reasons]);
			$newReqOBJ->merge(['request_id'=>$entity->id]);

			$suppRating = new SuppliersRatingCriteriaController();
			return $suppRating->update($newReqOBJ, $parentReq->supplier_id);
		}
		else{
			throw new \Error("Issue setting rating.");
		}

		return redirect()->back()->with('success', ' Items Received.');
	}

	public function get_requester_approval($entity)
	{
		$companyDetails = getCompanyDetails();
		$APPR_USER = \App\User::find($entity->request_initiator);

		$body = '
			Hi ' . $APPR_USER->name . ',<br><br>
			There has been an ammendment made to the RFQ from your Purchase Request.
			Please click on this link <a href="' . route('view-request-details', ['stage' => $entity->request_type, 'id' => $entity->id]) . '">' . route('view-request-details', ['stage' => $entity->request_type, 'id' => $entity->id]) . '</a> to view the changes made to the items and then approve/reject the ammendment.
			<br>Regards,<br>
			' . $companyDetails['name'] . '
		';

		$entity->status = "Amendment Awaiting Approval";
		$entity->save();
		// return $APPR_USER;

		$mailData = array(
			'contacts' => array($APPR_USER->email),
			'body' => $body,
			'subject' => '[' . $entity->request_type . '] Amendment Approval Request'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->back()->with('success', 'Awaiting Requester Approval.');
	}

	public function cancel_ammendment(Request $request, $entity)
	{
		$companyDetails = getCompanyDetails();

		$ammendment_no = $entity->ammendment;

		if ($entity->in_ammendment) {
			auditableDelete(RequestEntityItem::where('request_id', $entity->id)->where('ammendment', $ammendment_no)->get());
		}

		$entity->ammendment = $ammendment_no - 1;
		$entity->in_ammendment = 0;
		$entity->status = "Completed";
		$entity->save();

		$theUser = \App\User::find($entity->created_by);

		$body = '
			Hi ' . $theUser->name . ',<br><br>
			Amendments that were made on this RFQ have been rejected by the requester. They gave the following reason;
			<p style="padding: 5px 10px; font-size: 13px; color: #444"> <em>' . $request->rejection_message . '</em></p>
			<br>Regards,<br><br>
			' . $companyDetails['name'] . '
		';

		$subject = "[" . $entity->request_code . "] Requester has rejected ammendments";

		return $this->notify_user($body, $theUser->email, $subject);

		return redirect()->back()->with('success', 'Amendment Cancelled.');
	}

	public function make_an_ammendment($entity)
	{
		$RFQ = RequestEntity::find($entity->parent_request_id);

		$RFQ->in_ammendment = 1;
		$RFQ->ammendment = $RFQ->ammendment + 1;

		$RFQ->save();

		return redirect()->route('view-request-details', ['stage' => $RFQ->request_type, 'id' => $RFQ->id])->with('success', 'RFQ Set into edit mode.');
	}

	public function approve_ammendments_details($request, $entity)
	{
		$entities = RequestEntity::where('request_type', '=', 'Purchase Orders')->where('parent_request_id', $entity->id)->pluck('id');


		$rfqItems = RequestEntityItem::where('request_id', $entity->id)->get();

		foreach ($rfqItems as $rItem) {
			$poItem = RequestEntityItem::whereIn('request_id', $entities)->where('quantity', $rItem->quantity)
				->where('inventory_sub_category_id', $rItem->inventory_sub_category_id)->first();

			$poItem->item_brand_id = $rItem->item_brand_id;
			$poItem->brand = $rItem->brand;
			$poItem->ammendment = 1;
			$poItem->request_entity_item_ammended_id = 0;
			$poItem->save();
		}

		$entity->in_ammendment = 0;
		$entity->status = "Completed";
		$entity->save();

		$theUser = \App\User::find($entity->created_by);
		$companyDetails = getCompanyDetails();

		$body = '
			Hi ' . $theUser->name . ',<br><br>
			Amendments that were made on this RFQ have been approved by the requester.
			<br>Regards,<br><br>
			' . $companyDetails['name'] . '
		';

		$subject = "[" . $entity->request_code . "] Requester has approved amendments";

		return $this->notify_user($body, $theUser->email, $subject);
	}

	public function mark_po_as_complete($id)
	{
		$getPO = RequestEntity::find($id);

		$allItems = $getPO->items($getPO->ammendment) ?? array();

		$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

		$markAsComplete = true;

		foreach ($normalItems as $req_item) {
			if ($req_item->pending() > 0) {
				$markAsComplete = false;
			}
		}

		if ($markAsComplete) {
			$getPO->status = "Completed";
			$getPO->save();
		}

		return $markAsComplete;
	}

	public function mark_gr_as_complete($id)
	{
		$req = RequestEntity::find($id);

		$req->status = "Completed";
		$req->save();

		$this->mark_po_as_complete($req->parent_request_id);

		return redirect()->back()->with('success', 'Goods Receipt has been marked as complete.');
	}

	public function upload_bank_confirmation(Request $request, $id)
	{
		$companyDetails = getCompanyDetails();
		$entity = RequestEntity::find($id);
		$supplier = $entity->supplier();
		if ($request->hasFile('file')) {
			$path = $request->file->path();
			$attachment =  new EntityAttachment;
			$attachment->type = "Bank Notification Document";
			$attachment->title = "Confirmation Document";
			$attachment->description = "Confirmation Document";
			$attachment->model = $entity->request_type;
			$attachment->model_id = $entity->id;
			$attachment->created_by = \Auth::user()->id;

			$fileName = urlencode(space_underscore("Bank-Confirmation-" . $supplier->name)) . '.' . $request->file('file')->getClientOriginalExtension();
			$file = Storage::putFileAs('bank-confirmation', new File($path), $fileName);
			$file = explode('/', $file);

			$filelink = storage_path() . '/app/bank-confirmation/' . urlencode(end($file));
			$fName = '/storage/bank-confirmation/' . urlencode(end($file));

			$attachment->file = (string) $fName;

			$attachment->save();
		} else {
			return redirect()->back()->with('error', 'Confirmation Document not attached.');
		}

		$body = '
			Dear Supplier,<br>
			Please find attached the bank confirmation for Purchase Order - ' . $entity->request_code . '. Click on this link to view the purchase order details: <a href="' . route('req-report-generate', ['id' => $id]) . '">' . route('req-report-generate', ['id' => $id]) . '</a>';

		$body .= '<br><br>
			Regards,<br>
			' . $companyDetails['name'];

		$mailData = array(
			'contacts' => split_emails(';', $supplier->email),
			'body' => $body,
			'subject' => '[' . $companyDetails["name"] . '] Purchase Order Confirmation',
			'file' => $filelink
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		$entity->supplier_bank_notification = 1;
		$entity->save();


		return redirect()->back()->with('success', 'Bank Notification Sent');
	}

	public function submit_bank_details(Request $request, $id)
	{
		$companyDetails = getCompanyDetails();
		$entity = RequestEntity::find($id);
		$supplier = $entity->supplier();
		$hasMessage = $request->has('message');
		$files = [];

		$bank_contact_email = getConfigByName('bank_contact_email');
		$backEmail = count($bank_contact_email) > 0 ? $bank_contact_email[0]->value : 0;

		foreach ($request->docs['file'] ?? [] as $doc) {
			$docParts = explode("storage", $doc);
			if (count($docParts) > 1) {
				// echo storage_path().'/app'.$docParts[1];
				$files[] =  storage_path() . '/app' . $docParts[1];
			}
		}
		if (trim($entity->downloadable_link) != "" && $entity->downloadable_link != "pending") {
			$parts = explode('/storage', $entity->downloadable_link);
			$files[] = storage_path() . '/app' . end($parts);
		}

		// return json_encode($files);

		$body = '
			Hi,<br>
			Please find attached, documents for the Purchase Order - ' . $entity->request_code . ' for the supplier ' . $supplier->name;

		if ($hasMessage) {
			$body .= '<p><em>ADDITIONAL MESSAGE</em></p>
				<p><small><em>' . $request->message . '</em></small></p>
			';
		}

		$body .= '<br><br>
		Regards,<br>
		' . $companyDetails['name'];

		// echo $body;

		$mailData = array(
			'contacts' => array($backEmail),
			'body' => $body,
			'subject' => '[' . $companyDetails["name"] . '] Purchase Order Confirmation',
			'file' => $files
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		$entity->bank_notified = 1;
		$entity->save();

		return redirect()->back()->with('success', 'Bank Notification Sent');
	}

	public function send_to_finance($req)
	{
		$req->status = "Awaiting Finance Approval";
		$req->save();

		$finance_role_id = getConfigByName('finance_department_role_id')[0]->value ?? 0;

		$users = \App\Role::find($finance_role_id)->getUsersByRole();

		$userEmails = $users->pluck('email')->toArray();

		$companyDetails = getCompanyDetails();

		$body = '
			Hi,<br><br>
			The Goods Receipt ' . $req->request_code . ' has been sent to the finance department for processing.
			Please review this GRN by clicking <a href="' . route("view-request-details", ["stage" => $req->request_type, "id" => $req->id]) . '">here</a>.
			<br>Regards,<br><br>
			' . $companyDetails['name'] . '
		';

		$subject = "[" . $req->request_code . "] Invoice Awaiting Processing";
		return $this->notify_user($body, $userEmails, $subject);
	}

	public function change_req_approver(Request $request, $stage, $id)
	{
		$approval = \App\EntityApproval::find($id);

		if (in_array($approval->status, ["Approved", "Rejected"])) {
			return redirect()->back()->with('error', 'Approval already completed.');
		}

		$approval->user_id = $request->user_id;
		$approval->save();

		$req = \App\RequestEntity::find($approval->model_id);

		$user = \App\User::find($request->user_id);
		$companyDetails = getCompanyDetails();

		$approvalMoreInfo = $this->approvalMoreInfo($req, $approval);

		$body = '
			Hi ' . $user->name . ',<br>
			There is an approval request for ' . $req->request_type . ' - ' . $req->request_code . '.Please find the details below<br>
			' . $approvalMoreInfo . '<br>
			Click this link
			<a href="' . route("view-request-details", ["stage" => $req->request_type, "id" => $req->id]) . '">' . route("view-request-details", ["stage" => $req->request_type, "id" => $req->id]) . '</a> to view the request.
			<br>Regards,<br>
			' . $companyDetails['name'] . '
		';

		// return $APPR_USER;

		$mailData = array(
			'contacts' => [$user->email],
			'body' => $body,
			'subject' => '[Approval Request] Approval Request for ' . $req->request_type . ' - ' . $req->request_code
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return  redirect()->back()->with('success', 'Approver has been changed.');
	}

	public function create_lpo_from_mr(Request $request, $id)
	{
		$currency = getCurrencies();

		if (!$request->has('supplier_id')) {
			return redirect()->back()->with('error', 'Cannot create LPO. No supplier was selected!');
		}

		$existsreq = RequestEntity::where('parent_material_requisition', $id)->where('request_type', 'Purchase Orders')
			->where('supplier_id', $request->supplier_id)->get();

		if ($existsreq->count() > 0) {
			return redirect()->back()->with('error', 'An LPO for this purchase request and the same supplier already exists!');
		}


		$req = RequestEntity::find($id);
		$purchaseOrder = $req->replicate();
		$purchaseOrder->parent_request = $req->request_type;
		$purchaseOrder->request_code = getNamingConventionCode("Purchase Orders", false, "PO");
		$purchaseOrder->parent_request_id = $req->id;
		$purchaseOrder->request_type = "Purchase Orders";
		$purchaseOrder->status = "Awaiting Approval";
		$purchaseOrder->approval_status = "";
		$purchaseOrder->supplier_id = $request->supplier_id;
		$purchaseOrder->currency = $currency[0]->id ?? NULL;
		$purchaseOrder->created_by = \Auth::user()->id;
		$purchaseOrder->parent_material_requisition = $req->id;
		$purchaseOrder->request_initiator = $req->created_by;
		$purchaseOrder->net_value = 0;
		$purchaseOrder->save();

		$approvals = getStageApprovals('Requisition', 'Purchase Orders');

		$firstApprover = false;

		$CREATOR = \App\User::find($purchaseOrder->request_initiator);

		$previousApprovers = [];
		foreach ($approvals as $app) {
			$hasDepartmentalApprovals = \App\UserDepartmentalApproval::where('role_id', $app->role_id)
				->where('department_idd', $CREATOR->department_id)->first();

			if (!isset($hasDepartmentalApprovals->user_id)) {
				$approver = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
					->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id, users.email')->first();
			} else {
				$approver = \App\User::find($hasDepartmentalApprovals->user_id);
			}
			$approverID = $approver->id;

			$entity_approval = \App\EntityApproval::where('model_id', $purchaseOrder->id)->where('approval_id', $app->id)
				->where('model', 'Purchase Orders')->first() ?? new \App\EntityApproval;
			if (!$firstApprover) {
				$firstApprover = $approver;

				$entity_approval->is_current = 1;
				$entity_approval->save();
				$companyDetails = getCompanyDetails();

				$body = '
					Hi,<br><br>
					There is a Purchase Order that requires your approval. <br>
					Click <b><a href="' . route('view-request-details', ['stage' => $purchaseOrder->request_type, 'id' => $purchaseOrder->id]) . '">here</a></b> to view RFQ.
					<br>
					Regards,<br>
					' . $companyDetails['name'] . '
				';

				$mailData = array(
					'contacts' => [$firstApprover->email],
					'body' => $body,
					'subject' => '[Approval Request] Approval Request for Purchase Order - ' . $purchaseOrder->request_code
				);

				$mailer = new Mailer;

				$sendMail = $mailer->html_email($mailData, 'default');
			}

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Purchase Orders';
			$entity_approval->model_id = $purchaseOrder->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;
		}

		$entityItems = RequestEntityItem::where('request_id', $req->id)->get();
		$itemsCatNames = array();

		foreach ($entityItems as $i) {
			$iO = $i->replicate();
			$iO->request_id = $purchaseOrder->id;
			$iO->save();

			$purchaseOrder->net_value += floatval($iO->net_value);
			$purchaseOrder->save();


			$subCat = \App\InventorySubCategories::find($iO->inventory_sub_category_id);
			$itemsCatNames[] = $subCat->name . "(" . $iO->quantity . ")";

			$inventoryItem = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

			$due_date = \Carbon\Carbon::now()->addDays($inventoryItem->external_lead_time);

			$purchaseOrder->due_date = $due_date > $purchaseOrder->due_date ? $due_date : $purchaseOrder->due_date;
		}

		$purchaseOrder->description = implode(", ", $itemsCatNames);
		$purchaseOrder->save();

		return redirect()->route('view-request-details', ['stage' => 'Purchase Orders', 'id' => $purchaseOrder->id])->with('success', 'Purchase Order Successfully created');
	}

	public function update(Request $request, $stage, $id, $isInternal = false)
	{
		// return response()->json($request->all());

		// return $isInternal;
		if (!is_numeric($id)) {
			$req = RequestEntity::where('request_code', $id)->where('request_type', $stage)->where('ammendment', $isInternal)->first();
			$id = $req->id;

			$isInternal = false;
		}

		if ($isInternal == false && $request->nature_of_purchase != "Capex" && in_array($stage, ['Purchase Request', 'Request to Store'])) {
			$itemz = $request->items['item_id'] ?? [];
			$items_totals = [];

			if ($stage == 'Goods Receipt') {
				$quantz = $request->items['received_quantity'] ?? [];
			} else {
				$quantz = $request->items['quantity'] ?? [];
			}

			// return json_encode($quantz);

			foreach ($itemz as $in => $it) {
				if (!isset($items_totals[$it])) {
					$items_totals[$it] = 0;
				}

				$items_totals[$it] += floatval($quantz[$in]);
			}

			$exceedingItems = [];
			$itemsBySubID = [];

			foreach ($items_totals as $is => $it) {
				if (!isset($itemsBySubID[$is])) {
					$itemsBySubID[$is] = 0;
				}
				$itemsBySubID[$is] += $it;
			}

			foreach ($itemsBySubID as $subID => $total) {
				$itemS = \App\InventorySubCategories::find($subID);
				$ccs = array_map('trim', $request->cost_center);
				$costcenter = implode(',', $ccs);

				$availableInStore = getAvailableStockByCostCenter($subID, $costcenter);
				$maxOrder = floatval($itemS->max_standard_inventory()) - floatval($availableInStore);

				if ($stage == "Request to Store") {
					$maxOrder = floatval($availableInStore);
				}

				if (floatval($total) > floatval($maxOrder)) {
					$exceedingItems[] = $itemS->name . " maximum order quantity - " . $maxOrder . " ordered quantity " . $total;
				}
			}

			if (count($exceedingItems) > 0) {
				return redirect()->back()->with('error', 'The following items exceeded the maximum order quantity ' . implode(',', $exceedingItems));
			}
		}

		// $data = array();
		// foreach($request->attachments['title'] as $i=>$title){
		// 	echo $title." >>>> ".$request->attachments['file'][$i]->path()."<br>";
		// 	$data[] = [$title, $request->attachments['file'][$i]->path()];
		// }

		// return response()->json($data, 200);

		$companyDetails = getCompanyDetails();

		$req = RequestEntity::find($id) ?? new RequestEntity;

		if ($request->has('make_an_ammendment')) {
			return $this->make_an_ammendment($req);
		}

		if ($request->has('cancel_ammendment')) {
			return $this->cancel_ammendment($request, $req);
		}

		if ($request->has('send_to_finance')) {
			return $this->send_to_finance($req);
		}

		if ($request->has('approve_ammendments_details')) {
			return $this->approve_ammendments_details($request, $req);
		}

		if ($request->has('get_requester_approval')) {
			return $this->get_requester_approval($req);
		}

		if ($request->has('mark_as_complete')) {
			$req->status = "Completed";
			$req->save();

			if ($stage == "Goods Receipt") {
				$requisition = $req->parent_material_requisition;

				$parentRequisitionEntity = RequestEntity::find($req->parent_material_requisition);

				$materialIssue = RequestEntity::where('parent_material_requisition', $req->parent_material_requisition)->where('request_type', 'Material Issuance')
					->first();

				if ($materialIssue) {
					$theUser = \App\User::find($parentRequisitionEntity->request_initiator) ?? \App\User::find($parentRequisitionEntity->created_by);

					$body = '
						Hi ' . $theUser->name . ',<br><br>
						There are items from your Purchase Request available for you to pickup at the store.<br>
						Regards,<br><br>
						' . $companyDetails['name'] . '
					';

					$subject = '[' . $companyDetails["name"] . '] Material Issue ' . $materialIssue->request_code;

					$materialIssue->status = "Awaiting User Reception";
					$materialIssue->save();

					return $this->notify_user($body, $theUser->email, $subject);
				}
			}

			return redirect()->back()->with('success', rtrim($stage, 's') . ' marked as complete');
		}

		if ($request->has('issue_out_items')) {
			return $this->issue_out_items($request, $req);
		}

		if ($request->has('accept_goods_receipt')) {
			return $this->accept_goods_receipt($request, $req);
		}

		if ($request->has('return_goods_to_supplier')) {
			return $this->return_goods_to_supplier($request, $req);
		}

		if ($request->has('notify_the_user')) {
			$theUser = \App\User::find($req->request_initiator) ?? \Auth::user();

			$body = '
				Hi ' . $theUser->name . ',<br><br>
				There are items from your Purchase Request available for you to pickup at the store.<br>
				Regards,<br><br>
				' . $companyDetails['name'] . '
			';

			$subject = '[' . $companyDetails["name"] . '] ' . $stage . ' ' . $req->request_code;

			$req->status = "Awaiting User Reception";
			$req->save();

			return $this->notify_user($body, $theUser->email, $subject);
		}

		if ($request->has('generate_material_issuance')) {
			return $this->create_material_issuance($request, $req);
		}

		if ($request->has('generate_goods_receipt')) {
			return $this->create_goods_receipt($request, $req);
		}

		if ($request->has('create_goods_return')) {
			return $this->create_goods_return($request, $req);
		}

		if ($request->has('create_gate_pass')) {
			return $this->create_gate_pass($request, $req);
		}

		if ($request->has('send_purchase_order')) {
			return $this->send_purchase_order($req);
		}

		if ($request->has('create_rfq_from_material_requisition')) {
			return $this->create_rfq_from_material_requisition($req);
		}

		if ($request->has('generate_purchase_order')) {
			return $this->create_purchase_order($req, $request);
		}

		if ($request->has('awarded_quote_is')) {
			$awarded_quotes = explode(',', $request->awarded_quote_is);

			// return json_encode($request->all());

			foreach ($awarded_quotes as $aq) {
				$awardedQuote = \App\SupplierQuote::find($aq);

				Log::error($awardedQuote ? json_encode($awardedQuote) : $aq . " =====> There is nothing here...");

				$awardedQuote->awarded_at = \Carbon\Carbon::now();
				$awardedQuote->is_awarded = 1;
				$awardedQuote->save();

				$siblings = \App\SupplierQuote::where('request_id', $awardedQuote->request_id)
					->where('request_item_id', $awardedQuote->request_item_id)->update(['awarded_at' => \Carbon\Carbon::now()]);
			}

			// return json_encode($awardedQuote);

			$isMultiple = false;

			if ($request->has('is_multiple_row')) {
				$isMultiple = true;
				$INVitems = $quotes = \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
					->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')->selectRaw('ics.code, ics.name')->where('supplier_quotes.request_id', $req->id)->whereIn('supplier_quotes.id', $awarded_quotes)->get();

				foreach ($INVitems as $invI) {
					$note = new EntityNote;
					$note->type = "Supplier Awarded";
					$note->description = "<h5>" . $invI->code . " " . $invI->name . "</h5><p>" . $request->award_reason . "</p>";
					$note->model = $stage;
					$note->model_id = $req->id;
					$note->created_by = \Auth::user()->id;
					$note->save();
				}
			} else {
				$INVitem = $quotes = \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
					->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')->where('supplier_quotes.request_id', $req->id)->whereIn('supplier_quotes.id', $awarded_quotes);

				if (count($awarded_quotes) > 1) { //Is Kit items
					$INVitem = $INVitem->selectRaw('rei.catalog_number as code, GROUP_CONCAT(ics.name) as name')
						->groupBy('rei.catalog_number');
				} else {
					$INVitem = $INVitem->selectRaw('ics.code, ics.name');
				}

				$INVitem = $INVitem->first();

				$note = new EntityNote;
				$note->type = "Supplier Awarded";
				$note->description = "<h5>" . $INVitem->code . " " . $INVitem->name . "</h5><p>" . $request->award_reason . "</p>";
				$note->model = $stage;
				$note->model_id = $req->id;
				$note->created_by = \Auth::user()->id;

				$note->save();
			}

			$req->status = "Awarded";
			$req->save();

			return \redirect()->back()->with('success', $isMultiple ? 'Multiple Quotes Awarded' : 'Supplier Quote Awarded');
		}

		if ($request->has('get_approval')) {
			if(trim($req->zoho_status) == "" && $stage == "Purchase Orders"){
				$req->zoho_status = "draft";
			}
			$req->status = "Awaiting Approval";
			$req->save();

			$approvals = getStageApprovals('Requisition', $stage);
			$notifyFirstApprover = true;
			foreach ($approvals as $app) {
				$requestInitiator = \App\User::find(isset($req->request_initiator) ? $req->request_initiator : $req->created_by);

				$hasDepartmentalApprovals = \App\UserDepartmentalApproval::where('role_id', $app->role_id)
					->where('department_id', $requestInitiator->department_id)->first();

				if (!isset($hasDepartmentalApprovals->user_id)) {
					$APPR_USER = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')->where('ur.role_id', $app->role_id);

					if (getDepartmentalHeadID() == $app->role_id) {
						$APPR_USER = $APPR_USER->where('users.department_id', $requestInitiator->department_id);
					}

					$APPR_USER = $APPR_USER->selectRaw('users.id, users.name, users.email')->first();
				} else {
					$APPR_USER = \App\User::find($hasDepartmentalApprovals->user_id);
				}

				$entity_approval = \App\EntityApproval::where('model_id', $req->id)->where('approval_id', $app->id)
					->where('model', $stage)->first() ?? new \App\EntityApproval;

				$entity_approval->approval_id = $app->id;
				$entity_approval->model = $stage;
				$entity_approval->user_id = $APPR_USER->id;
				$entity_approval->status = "Pending";
				$entity_approval->approved_at = null;
				$entity_approval->description = "";

				$entity_approval->model_id = $req->id;
				$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
				$entity_approval->save();

				if ($notifyFirstApprover) {
					$notifyFirstApprover = false;
					$approvalMoreInfo = $this->approvalMoreInfo($req, $entity_approval);

					$body = '
						Hi,<br><br>
						There is a ' . $stage . ' Approval Request. Please find the details below<br>
						' . $approvalMoreInfo . '<br>
						Click this link
						<a href="' . route("view-request-details", ["stage" => $stage, "id" => $req->id]) . '">' . route("view-request-details", ["stage" => $stage, "id" => $req->id]) . '</a> to view the request.
						<br>Regards,<br>
						' . $companyDetails['name'] . '
					';

					$req->approval_status = "Pending: " . $app->title;

					$entity_approval->is_current = 1;
					$entity_approval->save();
					$notifiableUser = \App\User::where('id', $entity_approval->user_id)->select('email')->first();
					$mailData = array(
						'contacts' => [$notifiableUser->email],
						'body' => $body,
						'subject' => '[Approval Request] Approval Request for ' . $req->request_type . ' - ' . $req->request_code
					);

					$mailer = new Mailer;

					$sendMail = $mailer->html_email($mailData, 'default');
				}
				// return $APPR_USER;
			}

			if ($isInternal) {
				return $sendMail;
			}

			// return json_encode($emailList);

			return \redirect()->back()->with('success', $stage . ' Awaiting Approval.');
		}

		if ($request->has('is_rfq_quote')) {

			// return response()->json($request->all(), 200);

			foreach ($request->quote['id'] as $j => $k) {
				$ids = explode(',', $j);
				foreach ($ids as $i) {
					$newQuote = \App\SupplierQuote::where('supplier_id', $request->supplier_id)
						->where('request_id', $req->id)->where('request_item_id', $i)->first() ?? new \App\SupplierQuote;
					$newQuote->supplier_id = $request->supplier_id;
					$newQuote->request_id = $req->id;
					$newQuote->request_item_id = $i;
					$newQuote->vat_inc = $request->quote['inc'][$j];
					$newQuote->vat_perc = $request->quote['vat'][$j];
					$newQuote->currency_id = $request->quote['currency'][$j];
					$newQuote->quote_amount = floatval($request->quote['amount'][$j]);

					$newQuote->save();

					\App\SupplierQuote::where('request_id', $req->id)->where('request_item_id', $i)->update([
						'awarded_at' => null,
						'is_awarded' => null,
					]);
				}
			}

			\App\SupplierRFQ::where('request_id', $req->id)->where('supplier_id', $request->supplier_id)->update(['quote_received' => 1]);
			if ($req->status != "Awarded") {
				$req->status = "Receiving Quotes";
			}
			$req->save();

			if ($request->hasFile('supplier_quote')) {
				$theSupplier = \App\Supplier::find($request->supplier_id);
				$path = $request->supplier_quote->path();
				$extension = $request->supplier_quote->extension();
				$attachment =  new EntityAttachment;
				$attachment->type = "Supplier Quote";
				$attachment->title = 'Quote from ' . $theSupplier->name;
				$attachment->description = 'Supplier Quote';
				$attachment->model = $req->request_type;
				$attachment->model_id = $req->id;
				$attachment->created_by = \Auth::user()->id;

				$fileName = urlencode('Quote from ' . $theSupplier->name . "-" . time()) . '.' . $request->file('supplier_quote')->getClientOriginalExtension();

				$file = Storage::putFileAs('supplier-quotes', new File($path), $fileName);
				$file = explode('/', $file);

				$fName = '/storage/supplier-quotes/' . urlencode(end($file));

				$attachment->file = (string) $fName;

				$attachment->save();
			}


			return \redirect()->back()->with('success', 'Supplier Quote Received');
		}

		if ($request->has('is_send_rfq')) {
			// \App\SupplierRFQ::where('request_id', $req->id)->update(['rfq_sent'=>1]);

			$supplier_rfqs = \App\SupplierRFQ::where('request_id', $req->id)->where('rfq_sent', '!=', 1)->where('quote_received', '!=', 1)->get();

			if ($supplier_rfqs->count() == 0) {
				return redirect()->back()->with('error', 'No items to send out.');
			}

			// return json_encode($supplier_rfqs);

			foreach ($supplier_rfqs as $rfq) {
				$supplier = \App\Supplier::find($rfq->supplier_id);
				// $quotes = \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
				//         ->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
				//         ->where('supplier_quotes.request_id', $req->id)
				//         ->selectRaw('ics.name, rei.quantity, ics.code')
				//         ->where('supplier_quotes.supplier_id', $supplier->id)->get();

				$supplierItemsArr = $supplier->itemIDs();

				$quotes = RequestEntityItem::where('request_id', $req->id)
					->join('inventory_sub_categories as ics', 'ics.id', '=', 'request_entity_items.inventory_sub_category_id')
					->selectRaw('GROUP_CONCAT(CONCAT(IFNULL(ics.name,""), " - ", IFNULL(request_entity_items.quantity, ""), "", IFNULL(request_entity_items.uom, ""), " ",
					IFNULL(request_entity_items.comments, ""))) as kit_item_name, request_entity_items.catalog_number, ics.name, request_entity_items.quantity, request_entity_items.comments, ics.code, ics.unit_type, request_entity_items.brand, COALESCE(request_entity_items.brand, 0) as has_brand')
					->where('ammendment', $req->ammendment)->whereIn('ics.id', $supplierItemsArr)->groupBy('request_entity_items.catalog_number')
					->orderBy('catalog_number', 'asc')->get();

				$quoteItems = array();

				foreach ($quotes as $o => $q) {
					$isKitRow = !is_numeric($q->catalog_number);
					$o++;
					$quoteItems[] = '<tr style=" border: 1px solid #aaa !important">
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">' . $o . '</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">' . $req->request_code . '</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">' . ($isKitRow ? ($q->catalog_number) : $q->code) . '</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">' . ($isKitRow ? ($q->kit_item_name) : ($q->name . ' ' . $q->brand)) . '</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">' . $q->comments . ' ' . $q->brand . '</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">' . ($isKitRow ? 1 : number_format($q->quantity) . $q->unit_type) . '</td>
					</tr>';
				}


				$pro_contact = getConfigByName('po_contact_email');
				$pro_contact_email = count($pro_contact) > 0 ? $pro_contact[0]->value : 'contact not set';

				$body = '
					Hi ' . $supplier->name . ',<br><br>
					'.$req->email_body.'
					<table style="width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #aaa !important">
						<thead>
							<tr>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">No</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">RFQ Code</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Item Code</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Item Description</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Comments</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Quantity</th>
							</tr>
						</thead>
						<tbody>' . implode("
						", $quoteItems) . '</tbody>
					</table><br><br>
					Regards,<br>
					' . $companyDetails['name'] . '
				';

				$emails = split_emails(';', $supplier->email);

				$emails = array_merge($emails, split_emails(';', $pro_contact_email));

				$attacheableFiles = [storage_path() . '/app/templates/Quotation-Template.xlsx'];

				$filesForwardable = EntityAttachment::where('type', 'Supplier Item Specification File')
					->where('model', $req->request_type)->where('model_id', $req->id)->get();

				foreach ($filesForwardable as $forwardable) {
					$fParts = explode('/storage/', $forwardable->file);
					$filePath = 'app/' . end($fParts);

					$attacheableFile = storage_path($filePath);

					$attacheableFiles[] = $attacheableFile;
				}

				$mailData = array(
					'contacts' => array_unique($emails),
					'body' => $body,
					'subject' => '[' . $companyDetails["name"] . '] Request for Quotation ' . $req->request_code,
					'file' => $attacheableFiles
				);

				$mailer = new Mailer;

				$sendMail = $mailer->html_email($mailData, 'default');

				$rfq->rfq_sent = 1;
				$rfq->save();
			}

			if ($req->status != "Awarded") {
				$req->status = "RFQs sent out";
			}

			$req->save();

			return \redirect()->back()->with('success', '[' . $stage . '] RFQs sent out to suppliers');
		}



		if ($request->has('reject_reason')) {
			$creator = \App\User::find($req->created_by);

			$req->status = "Rejected";
			$req->save();

			$note = new EntityNote;
			$note->type = "Rejection";
			$note->description = $request->reject_reason;
			$note->model = $stage;
			$note->model_id = $id;
			$note->created_by = \Auth::user()->id ?? $request->user_id;

			$note->save();

			$entity_approval = \App\EntityApproval::find($request->approval_id);
			$entity_approval->status = "Rejected";
			$entity_approval->approved_at = \Carbon\Carbon::now();
			$entity_approval->description = $request->reject_reason;
			$entity_approval->user_id = \Auth::user()->id ?? $request->user_id;
			$entity_approval->save();

			$USER = \Auth::user() ?? \App\User::find($request->user_id);

			$body = '
				Hi ' . $creator->name . ',<br><br>
				Your ' . rtrim($stage, 's') . ' has been rejected by ' . $USER->name . '. The reason given is:<br><br>
				<p style="padding: 10px !important; font-size: 13px; font-weight:600; color: #a1a1a1; border: solid 1px #ccc;">' . $request->reject_reason . '</p>
				<br><br>Regards,<br>
				' . $companyDetails['name'] . '
			';

			$subject = '[' . $companyDetails["name"] . '] ' . $stage . ' ' . $req->request_code . ' Rejected.';
			$this->notify_user($body, $creator->email, $subject);

			if ($isInternal) {
				return $req;
			}

			return \redirect()->back()->with('success', $stage . ' Request Rejected');
		}

		if ($request->has('recheck_reason')) {
			// return json_encode($request->all());

			$creator = \App\User::find($req->created_by);

			$req->status = in_array($req->request_type, ['Purchase Request', 'Request for Quotation', 'Request to Store', 'Gate Pass']) ? 'In Preparation' : 'Awaiting Approval';
			$req->save();

			$note = new EntityNote;
			$note->type = "Return Reason";
			$note->description = $request->recheck_reason;
			$note->model = $stage;
			$note->model_id = $id;
			$note->created_by = \Auth::user()->id ?? $request->user_id;

			$note->save();

			$entity_approvals = \App\EntityApproval::where('model', $req->request_type)->where('model_id', $req->id)->get();

			foreach ($entity_approvals as $eapp) {
				$eapp->description = null;
				$eapp->approved_at = null;
				$eapp->status = 'Pending';
				$eapp->save();
			}

			$USER = \Auth::user() ?? \App\User::find($request->user_id);

			$body = '
				Hi ' . $creator->name . ',<br><br>
				' . $req->request_type . '[' . $req->request_code . '] has been returned by ' . $USER->name . '. The reason given is:<br><br>
				<p style="padding: 10px !important; font-size: 13px; font-weight:600; color: #a1a1a1; border: solid 1px #ccc;">' . $request->recheck_reason . '</p>
				<br><br>Regards,<br>
				' . $companyDetails['name'] . '
			';

			$subject = '[' . $companyDetails["name"] . '] ' . $stage . ' ' . $req->request_code . ' Returned.';
			$this->notify_user($body, $creator->email, $subject);

			if ($isInternal) {
				return $req;
			}

			return \redirect()->back()->with('success', $stage . ' Request Returned');
		}

		if ($request->has('approve_this')) {
			$entity_approval = \App\EntityApproval::find($request->approval_id);
			$entity_approval->status = "Approved";
			$entity_approval->approved_at = \Carbon\Carbon::now();
			$entity_approval->description = "Approved";
			$entity_approval->user_id = \Auth::user()->id ?? $request->user_id;
			$entity_approval->save();

			$doneApprovals = $req->done_approvals()->count();
			$totalApprovals = $req->defined_approvals()->count();

			if ($doneApprovals < $totalApprovals) {
				$req->status = "Partially Approved";
			} else {
				$req->status = "Approval Complete";
				$req->approval_status = null;
			}

			$req->save();

			$USER = \App\User::find($req->created_by);
			$APPROVAL = \App\Approvals::find($entity_approval->approval_id);

			$contacteMails = array($USER->email);

			$isMRMessage = "";

			if ($req->request_type == "Purchase Orders" && $req->status == "Approval Complete") {
				$rptG = new ReportGeneratorController;
				$rptG->generate_report_pdf($req->id, false);
				$currentDate = Carbon::now(); // Get current date/time
				$newDate = $currentDate->addDays(90);

				$req->validity_period = $newDate->format('Y-m-d');
			}

			if ($req->request_type == "Purchase Request" && $req->status == "Approval Complete") {
				$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
				$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;

				$procurementOfficers = getUsersByRole($procurement_officer_role_id, true);

				$emailList = [];

				foreach ($procurementOfficers as $au) {
					$emailList[] = $au->email;
				}

				$contacteMails = array_merge($contacteMails, $emailList);

				$createdRFQ = $this->create_rfq_from_material_requisition($req, true);

				$isMRMessage = "and is " . $createdRFQ->request_code . " has been created from this Purchase Request.";
			}

			if ($req->request_type == "Goods Receipt" && $req->status == "Approval Complete") {
				$newReqOBJ = new Request();

				$thisAmmendment = $req->ammendment;

				$theItems = [
					'req_item_id' => [],
					'received_quantity' => [],
					'expiry' => [],
					'date_of_manufacture' => [],
					'lot_no' => [],
					'store_id' => [],
					'slot_id' => []
				];

				foreach ($req->items($thisAmmendment)['normal'] as $iz => $itm) {
					$theItems['req_item_id'][$iz] = $itm->id;
					$theItems['received_quantity'][$iz] = $itm->quantity;
					$theItems['expiry'][$iz] = $itm->gr_expiry ?? '2099-12-31';
					$theItems['date_of_manufacture'][$iz] = $itm->date_of_manufacture ?? '2099-12-31';
					$theItems['lot_no'][$iz] =  $itm->lot_no;
					$theItems['store_id'][$iz] =  $itm->store_id;
					$theItems['slot_id'][$iz] =  $itm->slot_id;
				}

				$newReqOBJ->merge(['items' => $theItems]);
				$newReqOBJ->merge(['request_code' => $req->request_code]);
				$this->accept_goods_receipt($newReqOBJ, $req, true);
			}

			$body = 'Hi ' . $USER->name . ',<br>
				<strong>' . $APPROVAL->title . '</strong> has been completed for ' . $stage . ' ' . $req->request_code . ' ' . $isMRMessage . '. Click the link
				<a href="' . route("view-request-details", ["stage" => $stage, "id" => $req->id]) . '">' . route("view-request-details", ["stage" => $stage, "id" => $req->id]) . '</a> to view the request.
				<br>Regards,<br>
				' . $companyDetails['name'];

			$mailData = array(
				'contacts' => $contacteMails,
				'body' => $body,
				'subject' => $stage . ' ' . $req->request_code . ' Approval Completed'
			);

			$nextEntity_Approval = \App\EntityApproval::where('id', '>', $request->approval_id)->orderBy('id', 'asc')->first();


			if (isset($nextEntity_Approval->id)) {
				$APPROVAL = \App\Approvals::find($nextEntity_Approval->approval_id);

				$nextEntity_Approval->is_current = 1;
				$nextEntity_Approval->save();

				$req->approval_status = "Pending: " . $APPROVAL->title;
				$req->save();

				$approvalMoreInfo = $this->approvalMoreInfo($req, $nextEntity_Approval);

				$body = '
					Hi,<br>
					There is a ' . $stage . ' Approval Request. Please find the details below<br>
					' . $approvalMoreInfo . '<br>
					Click this link
					<a href="' . route("view-request-details", ["stage" => $stage, "id" => $req->id]) . '">' . route("view-request-details", ["stage" => $stage, "id" => $req->id]) . '</a> to view the request.
					<br>Regards,<br>
					' . $companyDetails['name'];

				$nextEntAppr = \App\User::find($nextEntity_Approval->user_id);

				$mailData = array(
					'contacts' => [$nextEntAppr->email],
					'body' => $body,
					'subject' => '[Approval Request] Approval Request for ' . $req->request_type . ' - ' . $req->request_code
				);
			}

			$mailer = new Mailer;

			$sendMail = $mailer->html_email($mailData, 'default');

			// return json_encode($emailList);

			if ($isInternal) {
				return $req;
			}

			return \redirect()->back()->with('success', $stage . ' Approval was successful');
		}

		if (!isset(RequestEntity::find($id)->id)) {
			if ($stage == "Purchase Request") {
				$pref = "PR";
			}

			if ($stage == "Request for Quotation") {
				$pref = "RFQ";
			}

			if ($stage == "Purchase Orders") {
				$pref = "PO";
			}

			if ($stage == "Request to Store") {
				$pref = "RS";
			}

			if ($stage == "Gate Pass") {
				$pref = "GP";
			}
			if ($stage == "Loan") {
				$pref = "LN";
			}
			if ($stage == "Lend") {
				$pref = "LD";
			}
			$status = "In Preparation";
		}

		if ($request->has('issue_to')) {
			if ($req->issue_to != "" && $req->issue_to != $request->issue_to) {
				$sendOTP = true;
				$OTPUser = \App\User::find($request->issue_to);
			}

			$req->issue_to = $request->issue_to;

			if (isset($sendOTP)) {
				$OTPController = new OTPController;
				$OTP = $OTPController->create(
					(object) [
						'user_id' => $OTPUser->id,
						'model' => "Material Issuance",
						'model_id' => $req->id
					]
				);

				$body = 'Hi ' . $OTPUser->name . ',<br><br>
					Items from Request to Store <a href="' . route("view-request-details", ["stage" => "Request to Store", "id" => $req->id]) . '">' . $req->request_code . '</a> are available for pickup at the store. <br>
					Please use the code <b>' . $OTP->code . '</b> when picking up your items.
					<br>Regards,<br>
					' . $companyDetails['name'];

				$subject = '[' . $req->request_code . '] Items are available at the store for pick-up.';

				$store_manager_roles = getConfigByName('store_manager_role_id');
				$store_manager_role_id = count($store_manager_roles) > 0 ? $store_manager_roles[0]->value : 0;

				$storeManagers = getUsersByRole($store_manager_role_id, true);

				$emailList = [];

				foreach ($storeManagers as $sm) {
					$emailList[] = $sm->email;
				}

				$this->notify_user($body, $OTPUser->email, $subject);
			}
		}
		$req->priority = $request->priority;

		if ($request->has('cost_center')) {
			$ccs = array_map('trim', $request->cost_center);
			$req->cost_center = implode(',', $ccs);
		}

		$req->nature_of_purchase = $request->nature_of_purchase;
		if (isETCU()) {
			$req->method_to_send_po = $request->method_to_send_po ?? 'EMAIL';
			$req->print_price_on_po = $request->print_price_on_po ?? 1;
		}

		if ($request->has('nature_of_purchase') && $request->nature_of_purchase == "Capex") {
			if (trim($request->capex_project_number) != "") {
				$req->capex_project_number = $request->capex_project_number;
			} else {
				return redirect()->back()->with('error', 'Project Number missing for Capex');
			}
		}

		$req->currency = $request->currency;

		$updateDueDate = \Carbon\Carbon::parse($req->due_date) != \Carbon\Carbon::parse($request->valid_until ?? $request->delivery_date);

		if (in_array($stage, ["Goods Receipt", "Goods Return", "Material Issuance", "Gate Pass"])) {
			$req->gate_pass = $request->gate_pass ?? null;
			$req->destination = $request->destination ?? null;
			$req->note_bearer = $request->note_bearer ?? null;
			$req->time_out = $request->time_out ?? null;
			$req->remarks = $request->remarks ?? null;
			$req->moisture_contents = $request->moisture_contents ?? null;
			$req->delivery_note_number = $request->delivery_note_number ?? null;
			$req->supplier_invoice_number = $request->supplier_invoice_number ?? null;
			$req->vehicle_no = $request->vehicle_no ?? null;
		}

		if ($stage == "Request for Quotation") {
			$req->submission_deadline = \Carbon\Carbon::parse($request->submission_deadline);
		}

		if (!isset(RequestEntity::find($id)->id)) {
			$req->status = $status;

			$req->request_code = getNamingConventionCode($stage, false, $pref);

			$req->request_type = $stage;
			$req->created_by = \Auth::user()->id;
			if (in_array($stage, ["Purchase Request", "Request to Store", "Gate Pass"])) {
				$req->request_initiator = \Auth::user()->id;
			}
		}

		if ($stage != "Purchase Request" && trim($req->parent_request) == "") {
			$req->parent_request = $request->prev_stage;
			$req->parent_request_id = $request->prev_stage_id;
		}

		$req->required_approvals = getStageApprovals('Requisition', $stage)->count() ?? 0;

		$req->save();

		$req->approval_count = getEntityApprovals($stage, $req->id)->count() ?? 0;

		$notesArray = array();
		$attachmentArray = array();
		$sentSuppliers = [];
		if ($request->has('suppliers')) {
			foreach ($request->suppliers['supplier_rfq_id'] ?? array() as $i => $it) {
				$suprfqEx = \App\SupplierRFQ::find($it);

				$rfq = $suprfqEx ?? new \App\SupplierRFQ;
				$rfq->supplier_id = $request->suppliers['id'][$i];
				$rfq->request_id = $req->id;
				$rfq->save();

				$sentSuppliers[] = $rfq->id;
			}
		}
		if (count($sentSuppliers) > 0) {
			auditableDelete(\App\SupplierRFQ::where('request_id', $req->id)->whereNotIn('id', $sentSuppliers)->get());
		}

		foreach ($request->notes['note_id'] ?? array() as $i => $it) {
			$noteExist = EntityNote::find($it);

			$note = $noteExist ?? new EntityNote;
			$note->type = $request->notes['type'][$i];
			$note->description = $request->notes['description'][$i];
			$note->model = $stage;
			$note->model_id = $req->id;
			if (!isset($noteExist->id)) {
				$note->created_by = \Auth::user()->id;
			}

			$note->save();

			$notesArray[] = $note->id;
		}

		auditableDelete(EntityNote::whereNotIn('id', $notesArray)->where('model', $stage)->where('model_id', $req->id)->get());
		$grnHasInvoiceUploaded = false;
		foreach ($request->attachments['attachment_id'] ?? array() as $i => $it) {
			$attachmentExist = EntityAttachment::find($it);

			$attachment = $attachmentExist ?? new EntityAttachment;
			$attachment->type = $request->attachments['type'][$i];
			$attachment->title = $request->attachments['title'][$i];
			$attachment->description = $request->attachments['description'][$i] ?? $request->attachments['type'][$i];
			$attachment->model = $stage;
			$attachment->model_id = $req->id;
			if (!isset($attachmentExist->id)) {
				$attachment->created_by = \Auth::user()->id;
			}


			$filed = $request->attachments['file'][$i] ?? false;

			if ($filed) {
				$path = $filed->path();
				$fileName = urlencode(space_underscore($request->attachments['title'][$i]) . "-" . time()) . '.' . $filed->getClientOriginalExtension();

				$file = Storage::putFileAs('requisition', new File($path), $fileName);
				$file = explode('/', $file);

				$fName = '/storage/requisition/' . space_underscore(urlencode(end($file)));

				$attachment->file = (string) $fName;
			}

			$attachment->save();

			$attachmentArray[] = $attachment->id;
		}

		if ($grnHasInvoiceUploaded) {
		}

		$defaultStore = [];
		if (isset($request->id)) {
			$myCCs = explode(',', $req->cost_center ?? '');
			$zStore = \App\StoreToCostCenter::whereIn('cost_center', $myCCs)->first();
			if (isset($zStore->store_id)) {
				$zStoreSlot = \App\InventoryStoreSlot::where('inventory_store_id', $zStore->store_id)->first();
			}

			$defaultStore['store'] = isset($zStore->store_id) ? $zStore->store_id : 0;
			$defaultStore['slot'] = isset($zStoreSlot) && isset($zStoreSlot->id) ? $zStoreSlot->id : 0;
		}

		auditableDelete(EntityAttachment::whereNotIn('id', $attachmentArray)->where('model', $stage)->where('model_id', $req->id)->get());
		if (in_array($req->request_type, ["Purchase Request", "Gate Pass", "Request for Quotation", "Request to Store", "Material Issuance", "Goods Receipt", "Purchase Orders", "Loan", "Lend"])) {
			$itemsCatNames = array();
			$itemsArrIds = array();
			$totalValue = 0;

			$has_been_ammended = false;
			foreach ($request->items['req_item_id'] ?? array() as $i => $it) {
				$entityItem = RequestEntityItem::find($it);

				if (isset($entityItem->id) && $req->in_ammendment > 0) {
					if (
						$entityItem->item_brand_id != $request->items['item_brand_id'][$i] || $entityItem->brand != $request->items['brand'][$i] || $entityItem->quantity != $request->items['quantity'][$i]
						|| $entityItem->inventory_sub_category_id != $request->items['item_id'][$i]
					) {
						$has_been_ammended = true;
					}
				}

				// if($req->in_ammendment == 1){
				// 	$entityID = $entityItem->id;
				// 	$entityItem = $entityItem->replicate();

				// 	$entityItem->ammendment = $req->ammendment;
				// 	$entityItem->request_entity_item_ammended_id = $entityID;
				// 	$entityItem->save();
				// }

				$subCatID = $request->items['item_id'][$i];
				$account_id = isset($request->items['item_account_id']) ? $request->items['item_account_id'][$i] : null;

				$subCat = \App\InventorySubCategories::find($subCatID);
				$itemCat = \App\InventoryCategories::find($subCat->inventory_category_id);

				if(isset($request->items['quantity_change_reason']) && trim($request->items['quantity_change_reason'][$i])!=""){
					$note = new EntityNote;
					$note->type = "Item Quantity Change Reason";
					$note->title = "Quantity Changed for ".$subCat->name;
					$note->description = $request->items['quantity_change_reason'][$i];
					$note->model = $stage;
					$note->model_id = $req->id;
					$note->created_by = \Auth::user()->id;
					$note->save();
				}

				$defaultStore['store'] = $itemCat->default_store_id == 0 ? $defaultStore['store'] : $itemCat->default_store_id;
				$defaultStore['slot'] = $defaultStore['slot'] ?? 0;

				$item = $entityItem ?? new RequestEntityItem;
				$item->request_id = $req->id;
				$item->item_brand_id = $request->items['item_brand_id'][$i] ?? 0;
				$item->brand = $request->items['brand'][$i] ?? 0;
				$item->store_id = $request->items['store_id'][$i] ?? $defaultStore['store'];
				$item->slot_id = $request->items['slot_id'][$i] ?? $defaultStore['slot'];
				$item->uom = $request->items['uom'][$i] ?? 0;
				$item->inventory_sub_category_id = $subCatID;
				$item->item_account_id = $account_id;

				$item->comments = $request->items['comments'][$i];
				$item->quantity = isset($request->items['quantity'][$i]) ? $request->items['quantity'][$i] : $request->items['received_quantity'][$i];
				$item->net_value = $request->items['net_value'][$i] ?? 0;
				$item->currency = $request->items['currency'][$i] ?? 0;

				$item->starting_sample = $request->items['starting_sample'][$i] ?? null;
				$item->lot_no = $request->items['lot_no'][$i] ?? null;

				$item->compensation_kind = $request->items['compensation_kind'][$i] ?? null;
				$item->compensation_uom = $request->items['compensation_uom'][$i] ?? null;
				$item->compensation_quantity = $request->items['compensation_quantity'][$i] ?? null;
				$item->compensation_value = $request->items['compensation_value'][$i] ?? null;
				$item->compensation_remarks = $request->items['compensation_remarks'][$i] ?? null;

				$item->save();
				if ((!isset($entityItem->id) || $item->net_value == 0) && $req->request_type == "Purchase Orders") {
					$item->ammendment = $req->ammendment;

					$itemQuotePrice = RequestEntityItem::where('request_entity_items.request_id', $req->parent_request_id)
						->join('supplier_quotes as sq', function ($join) use ($req) {
							$join->where('sq.supplier_id', $req->supplier_id);
							$join->on('sq.request_item_id', 'request_entity_items.id');
						})->where('request_entity_items.inventory_sub_category_id', $item->inventory_sub_category_id)->selectRaw('(sq.quote_amount/request_entity_items.quantity) as unit_price')->first();

					$itemSPrice = (isset($itemQuotePrice->unit_price) ? floatval($itemQuotePrice->unit_price) : 0) * floatval($item->quantity);

					$item->net_value = $itemSPrice;
					$item->save();
				}

				if ($request->has('catalog_number') && trim($request->catalog_number) != "") {
					$item->catalog_number = $request->catalog_number;
				} else {
					if (trim($item->catalog_number) == "") {
						$item->catalog_number = $item->id;
					}
				}
				$item->save();

				$itemsArrIds[] = $item->id;
				$itemsCatNames[] = $subCat->name . "(" . (isset($request->items['quantity'][$i]) ? $request->items['quantity'][$i] : $request->items['received_quantity'][$i]) . ")";

				// $totalValue += convert_currency(floatval($item->net_value), $item->currency, $request->currency);
				$totalValue += floatval($item->net_value);

				if ($stage == "Purchase Request") {
					$due_date = $req->created_at->addDays($subCat->internal_lead_time);
				}

				$shippingMode = false;

				if ($stage == "Request for Quotation") {
					$pref = "RFQ";
					$due_date = $req->created_at->addDays($subCat->internal_lead_time);
					$shippingMode = $request->items['mode'][$i] ?? "Not Specified";
				}

				if ($stage == "Purchase Orders") {
					$pref = "PO";
					$due_date = $req->created_at->addDays($subCat->external_lead_time);
					$shippingMode = $request->items['mode'][$i] ?? "Not Specified";
				}

				if ($shippingMode) {
					$item->shipping_mode = $shippingMode;
					$item->save();
				}

				if (!in_array($stage, ["Purchase Orders", "Request for Quotation"])) {
					$req->due_date = isset($due_date) ? $due_date : \Carbon\Carbon::now();
				}
			}

			if ($has_been_ammended) {
				$req->in_ammendment = 2;
			}

			if ($updateDueDate) {
				$req->due_date = \Carbon\Carbon::parse($request->valid_until ?? $request->delivery_date);
			}

			// return response()->json($req, 200);
			if (!$has_been_ammended) {
				if (in_array($stage, ["Purchase Request", "Request to Store", "Request for Quotation", "Loan", "Lend"])) {
					$requesterID = isset($req->request_initiator) ? $req->request_initiator : $req->created_by;
					if ($requesterID != \Auth::user()->id) {
						// $deletedItems = RequestEntityItem::join('inventory_sub_categories as isc', 'isc.id', 'request_entity_items.inventory_sub_category_id')
						// 	->whereNotIn('request_entity_items.id', $itemsArrIds)->where('request_entity_items.request_id', $req->id)
						// 	->selectRaw('isc.name, isc.unit_type, request_entity_items.quantity')->get();

						// $delitems = [];

						// $requisitionID = in_array($stage, ["Purchase Request", "Request to Store", "Loan", "Lend"]) ? $req->id : $req->parent_material_requisition;
						// $MaterialRequisition = RequestEntity::find($requisitionID);

						// foreach($deletedItems as $dI){
						// 	$delitems[] = $dI->name."".$dI->unit_type." (".$dI->quantity.")";
						// }

						// $DelUser = \App\User::find($requesterID);

						// $body = '
						// 	Hi '.$DelUser->name.',<br><br>
						// 	The following items were removed from your '.($stage == "Request to Store" ? "Request to Store" : "Purchase Request").' <b>'.$MaterialRequisition->request_code.'</b> by '.$DelUser->email.'<br>
						// 	'.implode(", ", $delitems).'
						// 	<br>
						// 	Regards,<br>
						// 	'.$companyDetails['name'].'
						// ';

						// $mailData = array(
						// 	'contacts' => split_emails(';', $DelUser->email),
						// 	'body' => $body,
						// 	'subject' => '['.$companyDetails["name"].'] Items were removed',
						// );

						// $mailer = new Mailer;

						// $sendMail = $mailer->html_email($mailData, 'default');
					}
				}
				auditableDelete(RequestEntityItem::whereNotIn('id', $itemsArrIds)->where('request_id', $req->id)->where('action', 'normal')->get());
			}

			if (in_array($stage, ["Purchase Request", "Request to Store", "Loan", "Lend"])) {
				$req->farm_id =	$request->farm_id ?? null;
				$req->description =	$request->description;
				$req->is_lab_kit =	$request->has('is_lab_kit');
				$req->catalog_number =	$request->has('catalog_number') ? $request->catalog_number : NULL;
				$req->kit_total_price =	$request->has('kit_total_price') ? $request->kit_total_price : 0;

				if ($req->status == "In Preparation" && $request->has('initiator')) {
					$req->request_initiator = $request->initiator ?? \Auth::user()->id;
				}
			} else {
				$req->description = $request->description;
			}
			$req->net_value = $totalValue;

			$req->inventory_location_id = getCurrentUserLocation()->id;

			$req->save();
		}

		return redirect()->route('view-request-details', ['stage' => $stage, 'id' => $req->id])->with('success', $stage . ' saved.');
	}

	public function download($id, $type)
	{
		// return view($TEMPLATE[$type], )
	}

	public function approvalMoreInfo($req, $appr, $notApproval = false, $isHTML = true)
	{
		$requestedBy = \App\User::find(in_array($req->request_type, ["Purchase Request", "Request to Store", "Loan", "Lend"]) ? $req->created_by : $req->request_initiator);
		$department = \App\InventoryDepartment::find($requestedBy->department_id);
		$currency = getCurrencyById($req->currency);

		$currency = $currency->name ?? 'Not Defined';

		$Creator = \App\User::find($req->created_by);

		if ($appr) {
			$appr->link_key = base64_encode(\Illuminate\Support\Facades\Hash::make(time()));
			$appr->save();
		}


		$reportGen = new ReportGeneratorController;

		if (in_array($req->request_type, ["Purchase Request", "Request to Store", "Loan", "Lend"])) {
			$allItems = isset($req) ? $req->items($req->ammendment) : array();

			$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

			$itemsBody = '';

			foreach ($normalItems as $i => $item) {
				$itemsBody .= '<tr style="border: 1px solid #ccc;">
					<td style="border: 1px solid #ccc;">' . ($i + 1) . '</td>
					<td style="border: 1px solid #ccc;">' . $item['item_name'] . '</td>
					<td style="border: 1px solid #ccc;">' . number_format($item['quantity'], 1) . '' . $item['unit_type'] . '</td>
				</tr>';
			}

			$html = '
				<table border="0" style="border-collapse: collapse; width: 100%">
					<tr>
						<td><strong>Requestor</strong><br>' . $requestedBy->name . '</td>
					</tr>
					<tr>
						<td><strong>Department By</strong><br>' . $department->name . '</td>
					</tr>
					<tr>
						<td><strong>Currency</strong><br>' . $currency . '</td>
					</tr>
					<tr>
						<td><strong>Purpose</strong><br>' . $req->description . '</td>
					</tr>
					<tr>
						<td style="padding: 0px">
							<table style="border-collapse: collapse; margin: 0px; width: 100%">
								<tr style="border: 1px solid #ccc;">
									<th style="border: 1px solid #ccc;">No.</th>
									<th style="border: 1px solid #ccc;">Item</th>
									<th style="border: 1px solid #ccc;">Quantity</th>
								</tr>
								' . $itemsBody . '
							</table>
						</td>
					</tr>
				</table>
				<br>
			';
		} else {
			$view = $reportGen->generate_report($req->id, false, $isHTML);
			$MRPurpose = RequestEntity::find($req->parent_material_requisition);
			$purpose = '';
			if (isset($MRPurpose->id)) {
				$purpose = '
				<h6>Purpose/Description</h6><br>' . $MRPurpose->description . '<br>';
			}
			$html = $purpose . $view->render();
		}

		if (!$notApproval) {
			$html .= '
			<table border="0" style="width:100%">
				<tr>
					<td style="text-align: center;">
						<a href="' . route('email-approval', ['link_key' => $appr->link_key, 'type' => 'requisition', 'userid' => $appr->user_id]) . '"
						style="display: inline-block;
						font-weight: 400;
						color: #fff;
						background-color: #28a745;
						border-color: #28a745;
						text-align: center;
						white-space: nowrap;
						vertical-align: middle;
						-webkit-user-select: none;
						-moz-user-select: none;
						-ms-user-select: none;
						width: 70%;
						text-decoration: none;
						user-select: none;
						border: 1px solid transparent;
						padding: 8px 16px;
						font-size: 24px;
						line-height: 1.5;
						border-radius: 4px;">APPROVE</a>
					</td>
					<td style="text-align: center;">
						<a href="' . route('email-rejection', ['link_key' => $appr->link_key, 'type' => 'requisition', 'userid' => $appr->user_id]) . '"
						style="display: inline-block;
						font-weight: 400;
						color: #fff;
						background-color: #dc3545;
						border-color: #dc3545;
						text-align: center;
						white-space: nowrap;
						vertical-align: middle;
						-webkit-user-select: none;
						-moz-user-select: none;
						-ms-user-select: none;
						user-select: none;
						width: 70%;
						text-decoration: none;
						border: 1px solid transparent;
						padding: 8px 16px;
						font-size: 24px;
						line-height: 1.5;
						border-radius: 4px;">REJECT</a>
					</td>
					<td style="text-align: center;">
						<a href="' . route('email-recheck', ['link_key' => $appr->link_key, 'type' => 'requisition', 'userid' => $appr->user_id]) . '"
						style="display: inline-block;
						font-weight: 400;
						color: #fff;
						background-color: #007bff;
						border-color: #007bff;
						text-align: center;
						white-space: nowrap;
						vertical-align: middle;
						-webkit-user-select: none;
						-moz-user-select: none;
						-ms-user-select: none;
						width: 70%;
						text-decoration: none;
						user-select: none;
						border: 1px solid transparent;
						padding: 8px 16px;
						font-size: 24px;
						line-height: 1.5;
						border-radius: 4px;">RETURN</a>
					</td>
				</tr>
			</table>
			';
		}

		return $html;
	}

	public function clone_entity(Request $request, $stage, $internal = false)
	{
		// return $request->all();

		$entities = $request->request_id;
		if (count($entities) == 0) {
			return redirect()->back()->with('error', 'No ' . $stage . ' were selected.');
		}

		$id = $entities[0];

		$entity = \App\RequestEntity::find($id);

		$replicate = $entity->replicate();
		$replicate->request_code = getNamingConventionCode("Purchase Request", false, "PR");
		$replicate->status = "In Preparation";
		$replicate->approval_status = "";
		$replicate->created_by = \Auth::user()->id;
		$replicate->net_value = 0;
		$replicate->ammendment = $request->has('ammendment_no') ? ($request->ammendment_no + 1) : 1;
		$replicate->is_lab_kit = 0;
		$replicate->catalog_number = '';
		$replicate->request_initiator = \Auth::user()->id;
		$replicate->parent_material_requisition = NULL;
		$replicate->save();

		$items = \App\RequestEntityItem::whereIn('request_id', $entities)->get();

		// return json_encode($items);

		foreach ($items as $item) {
			$zitem = $item->replicate();
			$zitem->request_id = $replicate->id;
			$zitem->ammendment = $replicate->ammendment;
			$zitem->save();
		}

		$notes = \App\EntityNote::where('model', $entity->request_type)->whereIn('model_id', $entities)->get();
		foreach ($notes as $note) {
			$znote = $note->replicate();
			$znote->model = $replicate->request_type;
			$znote->model_id = $replicate->id;
			$znote->save();
		}

		$attachments = \App\EntityAttachment::where('model', $entity->request_type)->whereIn('model_id', $entities)->get();
		foreach ($attachments as $attachment) {
			$zattachment = $attachment->replicate();
			$zattachment->model = $replicate->request_type;
			$zattachment->model_id = $replicate->id;
			$zattachment->save();
		}

		if ($internal) {
			return $replicate;
		}

		return redirect()->route('view-request-details', ['stage' => $replicate->request_type, 'id' => $replicate->id])->with('success', $replicate->request_type . ' successfully cloned.');
	}

	public function removeRequestEntity($stage, $id)
	{
		$entity = \App\RequestEntity::find($id);
		$status = $entity->status;
		$type = $entity->request_type;

		$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
		$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;

		if ($status != "In Preparation") {
			if (\Auth::user()->hasRole($procurement_officer_role_id, true)) {
				$entity->delete = 1;
				$entity->save();
			} else {
				return redirect()->back()->with('error', 'You can not delete this ' . $type . ' since it is no longer In Preparation.');
			}
		} else {
			\App\EntityNote::where('model', $type)->where('model_id', $id)->delete();
			\App\EntityAttachment::where('model', $type)->where('model_id', $id)->delete();
			\App\RequestEntityItem::where('request_id', $id)->delete();
			$entity->delete();
		}


		return redirect()->back()->with('success', $type . ' has been removed from the system.');
	}

	public function make_po_ammendment(Request $request, $id, $stage)
	{
		$req = RequestEntity::find($id);

		$ammendment_no = RequestEntity::where('request_code', $req->request_code)->where('delete', 0)->count();

		$request->request->add(['request_id' => [$id]]);
		$request->request->add(['ammendment_no' => $ammendment_no]);
		$cloneReq = $this->clone_entity($request, $stage, true);

		$is_supplement = $request->ammendent_type == "supplement";

		$cloneReq->request_code = $req->request_code;
		$cloneReq->is_supplement = $is_supplement ? 1 : 0;
		$cloneReq->request_initiator = $req->request_initiator;
		$cloneReq->parent_material_requisition = $req->parent_material_requisition;
		$cloneReq->save();

		if (!$is_supplement) {
			$req->is_supplement = 1;
			$req->status = "Completed";
			$req->save();
		}

		return redirect()->route('view-request-details', ['stage' => $cloneReq->request_type, 'id' => $cloneReq->id])->with('success', $cloneReq->request_type . ' successfully cloned.');
	}

	public function reverse_entity_action(Request $request, $id)
	{
		$items = RequestEntityItem::where('request_id', $id)->where('action', 'issued_received')->get();
		$entity = RequestEntity::find($id);
		$res_code = $entity->request_code;

		// return "Here";

		$companyDetails = getCompanyDetails();

		$action = $entity->request_type == "Goods Receipt" ? "issue" : "receive";

		foreach ($items as $item) {
			$entity->request_code = 'REVERSE-' . $res_code;
			if ($action) {
				$this->affect_inventory($action, $item, $entity);
			}

			$item->delete();
		}

		$entity->status = "Reversed";
		$entity->save();

		$note = new EntityNote;
		$note->type = "Reversal Reason";
		$note->title = "Reversal of ".$entity->request_type." - ".$res_code;
		$note->description = $request->reason;
		$note->model = $entity->request_type;
		$note->model_id = $entity->id;
		$note->created_by = \Auth::user()->id;
		$note->save();

		$requestInitiator = \App\User::find($entity->request_initiator);

		$body = 'Hi ' . $requestInitiator->name . ', <br><br>
		  ' . $entity->request_type . '[' . $res_code . '] has been reversed.<br>
		  Click <a style="font-weight: 600" href="' . route("view-request-details", ["stage" => $entity->request_type, "id" => $entity->id]) . '">here</a> to view the Goods return note.
		  <br><br>Regards,<br>
		  ' . $companyDetails['name'];

		$mailData = array(
			'contacts' => [$requestInitiator->email],
			'body' => $body,
			'subject' => $entity->request_type . "[" . $res_code . "] has been reversed"
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->back()->with('success', 'The ' . $entity->request_code . ' has been reversed');
	}

	public function add_email_body_rfq(Request $request, $id){
		$entity = RequestEntity::find($id);

		$entity->email_body = $request->body;
		$entity->save();

		return redirect()->back()->with('success', 'RFQ email body has been updated!');
	}

	public function streamlineSlotStockOut()
	{
		$items = \App\InventorySubCategories::where('available_stock', '>', 0)->get();
		foreach ($items as $it) {
			$invItems = \App\InventoryItem::where('inventory_sub_category_id', $it->id)->selectRaw('inventory_store_id as store, inventory_store_slot_id as slot')
				->where('stock_in', '>', 0)->get();

			$stock_in_slot_id = [];
			foreach ($invItems as $iI) {
				if (!isset($stock_in_slot_id[$iI->store])) {
					$stock_in_slot_id[$iI->store][] = $iI->slot;
				}
			}

			foreach ($stock_in_slot_id as $store => $slots) {
				$inItemsOut = \App\InventoryItem::where('inventory_sub_category_id', $it->id)->where('stock_out', '>', 0)->where('inventory_store_id', $store)->get();

				foreach ($inItemsOut as $iI) {
					if (!in_array($iI->inventory_store_slot_id, $slots)) {
						$iI->inventory_store_slot_id = $slots[0];
						$iI->save();
					}
				}
			}
		}
	}

	public function resend_to_zoho($id){
		$request = RequestEntity::find($id);
		$request->zoho_id = null;
		$request->errors = null;
		$request->save();

		return redirect()->back()->with('success', 'Retrying Zoho PO creation.');
	}
}