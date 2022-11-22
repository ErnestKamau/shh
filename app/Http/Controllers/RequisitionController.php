<?php

namespace App\Http\Controllers;

use App\Approvals;
use App\EntityNote;
use App\RequestEntity;
use App\EntityAttachment;
use App\RequestEntityItem;
use App\Http\Controllers\MailController as Mailer;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
  public function open_stage(Request $request, $stage)
  {
		$list = RequestEntity::leftJoin('module_pre_configs as mpc', function($join){
			$join->on('mpc.id', '=', 'request_entities.currency');
			$join->where('mpc.type', "Currency");
		})->selectRaw('request_entities.*, mpc.name as currency')
		->where('request_type', $stage)->where('request_entities.inventory_location_id', getCurrentUserLocation()->id)->orderBy('id', 'desc')->get();

		// return response()->json($stage, 200);

		$approvals = Approvals::where('stage', $stage)->orderBy('level', 'asc')->get();
    return view('layouts.inventory.requisition.index', compact('stage', 'approvals', 'list'));
	}

	public function jump_request_to_status(Request $request, $id){
		$entity = RequestEntity::find($id);

		$entity->status = $request->status;
		$entity->save();

		return redirect()->back()->with('success', 'Status changed.');
	}

	public function show($stage, $id, $ammendment=false){
		$request = RequestEntity::where('id', $id)->where('request_type', $stage)->first() ?? new RequestEntity;

		$documentFlow = [];

		if(isset($request->request_type)){

			if(in_array($stage, getRequisitionWorkflow())){
				$documentFlow["Material Requisition"] = [RequestEntity::find($request->parent_material_requisition) ?? $request];
				$stages = getRequisitionWorkflow();
			}
			else{
				$documentFlow["Request to Store"] = [RequestEntity::find($request->parent_material_requisition) ?? $request];
				$stages = getRequestToStoreWorkflow();
			}

			array_shift($stages);

			foreach($stages as $item){
				if(in_array($stage, getRequisitionWorkflow())){
					$documentFlow[$item] = RequestEntity::where('request_type', $item)->where('parent_material_requisition', $documentFlow["Material Requisition"][0]->id)->get();
				}
				else{
					$documentFlow[$item] = RequestEntity::where('request_type', $item)->where('parent_material_requisition', $documentFlow["Request to Store"][0]->id)->get();
				}
			}

			if(isset($documentFlow['Goods Return']) && count($documentFlow['Goods Return']) == 0){
				unset($documentFlow['Goods Return']);
			}

		}

		if($ammendment === false){
			$ammendment = $request->ammendment;
		}

		// return "<pre>".json_encode($ammendment)."</pre>";

    return view('layouts.inventory.requisition.show', compact('stage', 'request', 'documentFlow', 'ammendment'));
	}

	public function create_goods_receipt($request, $entity){

		$files = ["invoice", "delivery_note"];
		$hasAttachments = false;
		foreach($files as $file){
			if ($request->hasFile($file)){
				$hasAttachments = true;
			}
		}

		if($hasAttachments == false){
			return redirect()->back()->with('error', 'Please upload either an invoice or delivery note.');
		}


		$itemsIDs = $request->item;
		$itemsReceived = $request->received;
		$itemsExpiry = $request->expiry;

		$purchaseOrder = $entity->replicate();
		$purchaseOrder->parent_request = $entity->request_type;
		$purchaseOrder->request_code = getNamingConventionCode("Goods Receipt", false, "GR");
		$purchaseOrder->parent_request_id = $entity->id;
		$purchaseOrder->request_type = "Goods Receipt";
		$purchaseOrder->status = "Awaiting Approval";
		$purchaseOrder->created_by = \Auth::user()->id;
		$purchaseOrder->net_value = 0;
		$purchaseOrder->save();

		$approvals = getStageApprovals('Requisition', 'Goods Receipt');

		$previousApprovers = [];
		foreach($approvals as $app){
			$approverID = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
				->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id')->first()->id;

			$entity_approval = new \App\EntityApproval;

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Goods Receipt';
			$entity_approval->model_id = $purchaseOrder->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;
		}

		$itemNames = [];

		foreach($itemsIDs as $in=>$it){
			if(floatval($itemsReceived[$in]) > 0){
				if(floatval($itemsReceived[$in]) > 0){
					$i = RequestEntityItem::find($it);
					$iO = $i->replicate();
					$iO->request_id = $purchaseOrder->id;
					$iO->gr_expiry = $itemsExpiry[$in] ?? '2099-12-31';
					$iO->quantity = $itemsReceived[$in];
					$iO->save();

					$itemEntity = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

					$netValue = floatval($itemEntity->unit_price)*floatval($iO->quantity);

					$purchaseOrder->net_value += floatval($netValue);
					$purchaseOrder->save();

					$itemNames[] = $itemEntity->name."(".$iO->quantity.")";
				}
			}
		}

		$purchaseOrder->description = implode(", ", $itemNames);
		$purchaseOrder->save();

		foreach($files as $file){
			if($request->hasFile($file)){
				$path = $request->$file->path();
				$attachment =  new EntityAttachment;
				$attachment->type = $file == "invoice" ? "GR - Invoice" : "GR - Delivery Note";
				$attachment->title = $file;
				$attachment->description = $file;
				$attachment->model = $purchaseOrder->request_type;
				$attachment->model_id = $purchaseOrder->id;
				$attachment->created_by = \Auth::user()->id;

				$file = Storage::putFile('goods-receipt', new File($path));
				$file = explode('/', $file);

				$fName = '/storage/goods-receipt/'.urlencode(end($file));

				$attachment->file = (String) $fName;

				$attachment->save();
			}
		}

		$APPR_USER = \App\User::find($purchaseOrder->request_initiator);
		$MATERIAL_REQUISITION = \App\RequestEntity::find($purchaseOrder->parent_material_requisition);

		$companyDetails = getCompanyDetails();
		$OTPController = new OTPController;
		$OTP = $OTPController->create(
			(object) [
				'user_id'=> $APPR_USER->id,
				'model'=>"Goods Receipt",
				'model_id'=>$purchaseOrder->id
			]
		);

		$body = 'Hi '.$APPR_USER->name.',<br><br>
			Items from your Material Requisition <a href="'.route("view-request-details", ["stage"=>"Material Requisition", "id"=>$MATERIAL_REQUISITION->id]).'">'.$MATERIAL_REQUISITION->request_code.'</a> have been delivered to the store. <br>
			Please use the code <b>'.$OTP->code.'</b> when verifying the items delivered by the Supplier.
			<h5>Message</h5>
			<small><em>'.$request->message.'</em></small>
			<br>Regards,<br>
			'.$companyDetails['name'];

		$mailData = array(
			'contacts' => array($APPR_USER->email),
			'body' => $body,
			'subject' => '['.$MATERIAL_REQUISITION->request_code.'] Goods from your Requisition have arrived at store.'
		);

		$mailer = new Mailer;
		sendTextMessage($APPR_USER->phone, "You are required at the store to inspect the goods from ".$MATERIAL_REQUISITION->request_code.". Your OTP is ".$OTP->code.".");
		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->route('view-request-details', ['stage'=> $purchaseOrder->request_type, 'id'=>$purchaseOrder->id])->with('success', 'Goods Receipt successfully created');
	}

	public function create_goods_return($request, $entity){
		// return "We here";
		$files = ["invoice", "delivery_note"];
		$hasAttachments = false;
		foreach($files as $file){
			if ($request->hasFile($file)){
				$hasAttachments = true;
			}
		}

		if($hasAttachments == false){
			return redirect()->back()->with('error', 'Please upload either an invoice or delivery note.');
		}


		$itemsIDs = $request->item;
		$itemsReceived = $request->received;
		$itemsTest = $request->received;
		$itemsRemarks = $request->received;
		$itemsExpiry = $request->expiry;

		$purchaseOrder = $entity->replicate();
		$purchaseOrder->parent_request = $entity->request_type;
		$purchaseOrder->request_code = getNamingConventionCode("Goods Return", false, "GRN");
		$purchaseOrder->parent_request_id = $entity->id;
		$purchaseOrder->request_type = "Goods Return";
		$purchaseOrder->status = "Awaiting Approval";
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
		foreach($approvals as $app){
			$approverID = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
				->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id')->first()->id;

			$entity_approval = new \App\EntityApproval;

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Goods Return';
			$entity_approval->model_id = $purchaseOrder->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;
		}

		$itemNames = [];

		foreach($itemsIDs as $in=>$it){
			if(floatval($itemsReceived[$in]) > 0){
				$i = RequestEntityItem::find($it);
				$iO = $i->replicate();
				$iO->request_id = $purchaseOrder->id;
				$iO->gr_expiry = $itemsExpiry[$in] ?? '2099-12-31';
				$iO->quantity = $itemsReceived[$in];
				$iO->test = $itemsTest[$in];
				$iO->remarks = $itemsRemarks[$in];
				$iO->save();

				$itemEntity = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

				$netValue = floatval($itemEntity->unit_price)*floatval($iO->quantity);

				$purchaseOrder->net_value += floatval($netValue);
				$purchaseOrder->save();

				$itemNames[] = $itemEntity->name."(".$iO->quantity.")";
			}
		}

		$purchaseOrder->description = implode(", ", $itemNames);
		$purchaseOrder->save();

		foreach($files as $file){
			if($request->hasFile($file)){
				$path = $request->$file->path();
				$attachment =  new EntityAttachment;
				$attachment->type = "invoice" ? "GR - Invoice" : "GR - Delivery Note";
				$attachment->title = $file;
				$attachment->description = $file;
				$attachment->model = $purchaseOrder->request_type;
				$attachment->model_id = $purchaseOrder->id;
				$attachment->created_by = \Auth::user()->id;

				$file = Storage::putFile('goods-return', new File($path));
				$file = explode('/', $file);

				$fName = '/storage/goods-return/'.urlencode(end($file));

				$attachment->file = (String) $fName;

				$attachment->save();
			}
		}

		$companyDetails = getCompanyDetails();

		$body = 'Hi,<br><br>
			Some items from '.$entity->request_code.' have been return. Reason given for return;
			<p><em>'.$request->message.'</em></p>
			Click <a href="'.route("view-request-details", ["stage"=>$purchaseOrder->request_type, "id"=>$purchaseOrder->id]).'">here</a> to view the Goods return note.
			<br>Regards,<br>
			'.$companyDetails['name'];

		$mailData = array(
			'contacts' => $contacts,
			'body' => $body,
			'subject' => '['.$entity->request_code.'] Some Items returned to supplier'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		$purchaseOrder->description = $request->message;
		$purchaseOrder->save();

		return redirect()->route('view-request-details', ['stage'=> $purchaseOrder->request_type, 'id'=>$purchaseOrder->id])->with('success', 'Goods Return Created and notifications sent out.');
	}

	public function create_purchase_order($entity){
		$quotes = \App\SupplierQuote::where('request_id', $entity->id)
			->where('is_awarded', 1)->get();

		$firstTime = array();

		foreach($quotes as $quote){
			$entityItems = RequestEntityItem::where('request_id', $entity->id)
				->where('ammendment', $entity->ammendment)->where('id', $quote->request_item_id)->get();

			$purchaseOrder = RequestEntity::where('supplier_id', $quote->supplier_id)
				->where('request_type', "Purchase Orders")
				->where('parent_request', $entity->request_type)
				->where('parent_request_id', $entity->id)->first();

			$firstApprover = false;

			if(!isset($purchaseOrder->id)){
				$purchaseOrder = $entity->replicate();
				$purchaseOrder->parent_request = $entity->request_type;
				$purchaseOrder->request_code = getNamingConventionCode("Purchase Orders", false, "PO");
				$purchaseOrder->parent_request_id = $entity->id;
				$purchaseOrder->request_type = "Purchase Orders";
				$purchaseOrder->status = "Awaiting Approval";
				$purchaseOrder->supplier_id = $quote->supplier_id;
				$purchaseOrder->created_by = \Auth::user()->id;
				$purchaseOrder->net_value = 0;
				$purchaseOrder->save();

				$approvals = getStageApprovals('Requisition', 'Purchase Orders');
				$previousApprovers = [];

				foreach($approvals as $app){
					$approver = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
						->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id, users.email')->first();
					$approverID = $approver->id;

					$entity_approval = new \App\EntityApproval;

					if(!$firstApprover){
						$firstApprover = $approver;
					}

					$entity_approval->approval_id = $app->id;
					$entity_approval->model = 'Purchase Orders';
					$entity_approval->model_id = $purchaseOrder->id;
					$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
					$entity_approval->user_id = $approverID;
					$entity_approval->save();

					$previousApprovers[] = $approverID;
				}
			}

			if(!isset($firstTime[$purchaseOrder->id])){
				RequestEntityItem::where('request_id', $purchaseOrder->id)->delete();
				$firstTime[$purchaseOrder->id] = true;
			}

			if($firstApprover){

				$companyDetails = getCompanyDetails();

				$body = '
					Hi,<br><br>
					There is a Purchase Order that requires your approval. <br>
					Click <b><a href="'.route('view-request-details', ['stage'=> $purchaseOrder->request_type, 'id'=>$purchaseOrder->id]).'">here</a></b> to view RFQ.
					<br>
					Regards,<br>
					'.$companyDetails['name'].'
				';

				$mailData = array(
					'contacts' => [$firstApprover->email],
					'body' => $body,
					'subject' => '[Approval Request] Approval Request for Purchase Order - '.$purchaseOrder->request_code
				);

				$mailer = new Mailer;

				$sendMail = $mailer->html_email($mailData, 'default');
			}

			$itemsCatNames = array();

			foreach($entityItems as $i){
				$iO = $i->replicate();
				$iO->request_id = $purchaseOrder->id;
				$iO->net_value = $quote->quote_amount;
				$iO->save();

				$purchaseOrder->net_value += floatval($iO->net_value);
				$purchaseOrder->save();


				$subCat = \App\InventorySubCategories::find($iO->inventory_sub_category_id);
				$itemsCatNames[] = $subCat->name."(".$iO->quantity.")";

				$inventoryItem = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

				$due_date = \Carbon\Carbon::now()->addDays($inventoryItem->external_lead_time);

				$purchaseOrder->due_date = $due_date > $purchaseOrder->due_date ? $due_date : $purchaseOrder->due_date;
			}

			$purchaseOrder->description = implode(", ", $itemsCatNames);
			$purchaseOrder->save();
		}

		return redirect()->route('view-request-details', ['stage'=>'Purchase Orders', 'id'=>$purchaseOrder->id])->with('success', 'Purchase Order successfuly created');
	}

	public function create_rfq_from_material_requisition($entity){
		$rfq = $entity->replicate();
		$rfq->parent_request = $entity->request_type;
		$rfq->request_code = getNamingConventionCode("Request for Quotation", false, "RFQ");
		$rfq->parent_request_id = $entity->id;
		$rfq->request_type = "Request for Quotation";
		$rfq->status = "In Preparation";
		$rfq->created_by = \Auth::user()->id;
		$rfq->net_value = 0;
		$rfq->parent_material_requisition = $entity->id;
		$rfq->save();

		$approvals = getStageApprovals('Requisition', 'Request for Quotation');

		$previousApprovers = [];
		foreach($approvals as $app){
			$approverID = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
				->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id')->first()->id;

			$entity_approval = new \App\EntityApproval;

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Request for Quotation';
			$entity_approval->model_id = $rfq->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;
		}

		$entityItems = RequestEntityItem::where('request_id', $entity->id)->where('ammendment', $entity->ammendment)->get();

		foreach($entityItems as $i){
			$iO = $i->replicate();
			$iO->request_id = $rfq->id;
			$iO->save();

			$rfq->net_value += floatval($iO->net_value);
			$rfq->save();
		}

		$companyDetails = getCompanyDetails();

		$body = '
			Hi,<br><br>
			There is a new RFQ that requires your attention. <br>
			Click <b><a href="'.route('view-request-details', ['stage'=> $rfq->request_type, 'id'=>$rfq->id]).'">here</a></b> to view RFQ.
			<br>
			Regards,<br>
			'.$companyDetails['name'].'
		';

		$procurement_officer_roles = getConfigByName('procurement_officer_role_id');
		$procurement_officer_role_id = count($procurement_officer_roles) > 0 ? $procurement_officer_roles[0]->value : 0;

		$AppUsers = getUsersByRole($procurement_officer_role_id, true);

		$emailList = [];

		foreach($AppUsers as $au){
			$emailList[] = $au->email;
		}

		$mailData = array(
			'contacts' => $emailList,
			'body' => $body,
			'subject' => '[NEW RFQ] A new RFQ has been created '.$rfq->request_code
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->route('view-request-details', ['stage'=> $rfq->request_type, 'id'=>$rfq->id])->with('success', 'Request for Quotation successfully created.');
	}

	public function create_material_issuance($request, $entity){
		// return response()->json($request->all(), 200);

		$rfq = $entity->replicate();
		$rfq->parent_request = $entity->request_type;
		$rfq->request_code = getNamingConventionCode("Material Issuance", false, "MI");
		$rfq->parent_request_id = $entity->id;
		$rfq->request_type = "Material Issuance";
		$rfq->parent_material_requisition = $entity->id;
		$rfq->request_initiator = $entity->request_initiator ?? $entity->created_by;

		$rfq->created_by = \Auth::user()->id;
		$rfq->net_value = 0;
		$rfq->status ="Awaiting User Reception";
		$rfq->save();

		$entityItems = RequestEntityItem::where('request_id', $entity->id)->whereIn('id', $request->item)->get();

		$itemNames = [];

		foreach($entityItems as $ind=>$i){
			if(floatval($request->issued[$ind]) > 0){
				$iO = $i->replicate();
				$iO->request_id = $rfq->id;
				$iO->quantity = $request->issued[$ind];
				$iO->save();

				$rfq->net_value += floatval($iO->net_value);
				$rfq->save();

				$subCat = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

				$itemNames[] = $subCat->name."(".$iO->quantity.")";
			}
		}

		$rfq->description = implode(", ", $itemNames);
		$rfq->save();

		$companyDetails = getCompanyDetails();
		$theUser = \App\User::find($entity->created_by) ?? \Auth::user();

		$OTPController = new OTPController;
		$OTP = $OTPController->create(
			(object) [
				'user_id'=> $theUser->id,
				'model'=>"Material Issuance",
				'model_id'=>$rfq->id
			]
		);

		$body = 'Hi '.$theUser->name.',<br><br>
			Items from your Request to Store <a href="'.route("view-request-details", ["stage"=>"Request to Store", "id"=>$entity->id]).'">'.$entity->request_code.'</a> are available for pickup at the store. <br>
			Please use the code <b>'.$OTP->code.'</b> when picking up your items.
			<br>Regards,<br>
			'.$companyDetails['name'];

		$subject = '['.$rfq->request_code.'] Items from your Request to Store are available for pick-up.';

		$this->notify_user($body, $theUser->email, $subject);
		sendTextMessage($theUser->phone, "Items from your Request to Store ".$entity->request_code." are available for pickup. Your OTP is ".$OTP->code.".");

		return redirect()->route('view-request-details', ['stage'=> $rfq->request_type, 'id'=>$rfq->id])->with('success', 'Material Requisition successfuly created.');
	}

	public function affect_inventory($action, $item, $entity){
		$inventoryC = new \App\Http\Controllers\InventoryItemController;

		$theUser = \App\User::find($entity->request_initiator) ?? \Auth::user();
		if($action == "issue"){
			$req = new Request;
			$req->category_id = $item->category()->id;
			$req->sub_category_id = $item->inventory_sub_category_id;
			$req->quantity = $item->quantity;
			$req->lot_no = $item->lot_no;
			$req->slot = $item->slot_id;
			$req->store = $item->store_id;
			$req->transfer_to = $theUser->department_id;
			$req->issued_to = $theUser->id;
			$req->item_brand_id = $item->item_brand_id;

			$issueOut = $inventoryC->transfer($req, true);

			$item->inventory_item_id = $issueOut->id;

			return $issueOut;
		}
		else{
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

			return $receivedItem;
		}

	}

	public function issue_out_items($request, $entity){
		// return response()->json($request->all(), 200);

		$otp_value = $request->otp_value;

		$OTPController = new OTPController;

		$otp = $OTPController->close(
			(object)[
				"code"=> $otp_value,
				"model"=> "Material Issuance",
				"model_id"=> $entity->id,
				"approved_by"=> \Auth::user()->id
			]
		);

		if(!isset($otp->code)){
			return redirect()->back()->with('error', 'No matching OTP code found for this Material Requisition.');
		}

		foreach($request->items['req_item_id'] as $i=>$id){
			$entityItem = RequestEntityItem::find($id);
			$quantity = $request->items['quantity'][$i];
			$issuedItem = $entityItem->replicate();

			$itemItself = \App\InventorySubCategories::find($entityItem->inventory_sub_category_id);

			$quantity = getUoMConverstion($quantity, $entityItem->uom, $itemItself->unit_type);

			$issuedItem->quantity = $quantity;
			$issuedItem->action = 'issued_received';
			$issuedItem->net_value = floatval($issuedItem->quantity)/floatval($entityItem->quantity) * $entityItem->net_value;
			$issuedItem->status = 'completed';
			$issuedItem->issued_by = \Auth::user()->id;
			$issuedItem->save();

			$this->affect_inventory('issue', $issuedItem, $entity);

			$entityItem->status = "completed";

			$entityItem->save();
		}

		$status = "Completed";

		$entity->status = $status;
		$entity->save();
		return redirect()->back()->with('success', ' Items Issued.');
	}

	public function notify_user($body, $email, $subject, $file=false){

		$mailData = array(
			'contacts' => is_array($email) ? $email : array($email),
			'body' => $body,
			'subject' => $subject
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->back()->with('success', 'User notification sent');
	}

	public function send_purchase_order($req){
		// \App\SupplierRFQ::where('request_id', $req->id)->update(['rfq_sent'=>1]);

		$companyDetails = getCompanyDetails();
		$supplier = \App\Supplier::find($req->supplier_id);
		$quotes = \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
			->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
			->where('supplier_quotes.request_id', $req->parent_request_id)
			->where('supplier_quotes.is_awarded', 1)
			->selectRaw('ics.name, rei.quantity, ics.code')
			->where('supplier_quotes.supplier_id', $supplier->id)->get();

		if($quotes->count() == 0){
			return \redirect()->back()->with('error', 'Supplier Quotes not found.');
		}

		$quoteItems = array();

		foreach($quotes as $o=>$q){
			$o++;
			$quoteItems[] = '<tr style=" border: 1px solid #aaa !important">
				<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$o.'</td>
				<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$req->request_code.'</td>
				<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$q->code.'</td>
				<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$q->name.'</td>
				<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.number_format($q->quantity).$q->unit_type.'</td>
			</tr>';
		}

		$body = '
			Hi '.$supplier->name.',<br><br>
			You were awarded the items in the table below from your bid. Please supply the items before '.$req->due_date.'.
			<table style="width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #aaa !important">
				<thead>
					<tr>
						<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">No</th>
						<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Purchase Order</th>
						<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Item Code</th>
						<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Item Description</th>
						<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Quantity</th>
					</tr>
				</thead>
				<tbody>'.implode("
				", $quoteItems).'</tbody>
			</table><br>
			Click <b><a href="'.route('req-report-generate', ['id'=>$req->id]).'">here</a></b> to view purchase order
			<br>
			Regards,<br>
			'.$companyDetails['name'].'
		';

		$reportGenerator = new ReportGeneratorController();
		$templateFile =  $reportGenerator->generate_report($req->id, false);

		$HTML = $templateFile->render();

		// return $HTML;

		$PDF = \App::make('dompdf.wrapper')->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
		$PDF->loadHTML($HTML);
		$PDF->setPaper("letter", "portrait");

		$pdfPath = storage_path('app/supplier-po-pdfs/');

		if (!file_exists($pdfPath)) {
			mkdir($pdfPath, 0755, true);
		}

		$file = space_underscore($supplier->name."-".$req->request_code.'-v'.$req->ammendment).'.pdf';

		$pPath = $pdfPath.$file;

		@$PDF->save($pPath);

		$templateFile = $pPath;
				//Generate PDF Bit

		$mailData = array(
			'contacts' => array($supplier->email),
			'body' => $body,
			'subject' => '['.$companyDetails["name"].'] Purchase Order '.$req->request_code,
			'file' => $templateFile
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		$req->status = "Purchase Order Sent";
		$req->save();

		return \redirect()->back()->with('success', 'Purchase Order sent out to supplier.');
	}

	public function return_goods_to_supplier($request, $entity){

		if($request->has('return_action') && $request->return_action == 'credit_note'){
			if(!$request->hasFile('credit_note')){
				return redirect()->back()->with('error', 'No credit note was attached.');
			}
			else{
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

				$fName = '/storage/goods-return/'.urlencode(end($file));

				$attachment->file = (String) $fName;

				$attachment->save();
				foreach($request->items['req_item_id'] as $i=>$id){
					$entityItem = RequestEntityItem::find($id);
					$quantity = $request->items['quantity'][$i];

					if($quantity > 0){
						$receivedItem = $entityItem->replicate();
						$receivedItem->quantity = $quantity;
						$receivedItem->action = 'issued_received';
						$receivedItem->net_value = floatval($receivedItem->quantity)/floatval($entityItem->quantity) * $entityItem->net_value;
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
		$supplier_emails = explode(',', \App\Supplier::find($entity->supplier_id)->email);

		$companyDetails = getCompanyDetails();

		$body = 'Hi,<br><br>
			Some items from '.$purchaseOrder->request_code.' have been returned. Reason given for return;
			<p><em>'.$entity->description.'</em></p>.
			Please click <a href="'.route('download-requisition-doc', ['id'=>$entity->id, 'type'=>"goods-return-note"]).'">here</a> to download the Goods Return Note.
			<br>Regards,<br>
			'.$companyDetails['name'];

		$mailData = array(
			'contacts' => $supplier_emails,
			'body' => $body,
			'subject' => '['.$purchaseOrder->request_code.'] Goods Return Note Notice.'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		$entity->description = $request->message;

		$this->mark_po_as_complete($purchaseOrder->id);

		return redirect()->back()->with('success', 'Items have been returned to the supplier.');
	}
	public function accept_goods_receipt($request, $entity){
		$otp_value = $request->otp_value;

		$OTPController = new OTPController;

		$otp = $OTPController->close(
			(object)[
				"code"=> $otp_value,
				"model"=> "Goods Receipt",
				"model_id"=> $entity->id,
				"approved_by"=> \Auth::user()->id
			]
		);

		if(!isset($otp->code)){
			return redirect()->back()->with('error', 'No matching OTP code found for this Goods Receipt.');
		}

		$anyPendingItems = array();
		foreach($request->items['req_item_id'] as $i=>$id){
			$entityItem = RequestEntityItem::find($id);
			$quantity = $request->items['received_quantity'][$i];
			$expiry = $request->items['expiry'][$i] ?? '2099-12-31';
			$date_of_manufacture = $request->items['date_of_manufacture'][$i] ?? '2099-12-31';
			$lot_no = $request->items['lot_no'][$i] ?? null;
			$store = $request->items['store_id'][$i];
			$slot = $request->items['slot_id'][$i];

			$entityItem->slot_id = $slot;
			$entityItem->store_id = $store;
			$entityItem->lot_no = $lot_no;
			$entityItem->gr_expiry = $expiry;
			$entityItem->date_of_manufacture = $date_of_manufacture;
			$entityItem->issued_by = \Auth::user()->id;

			$entityItem->save();

			if($quantity > 0){
				$receivedItem = $entityItem->replicate();
				$receivedItem->quantity = $quantity;
				$receivedItem->action = 'issued_received';
				$receivedItem->net_value = floatval($receivedItem->quantity)/floatval($entityItem->quantity) * $entityItem->net_value;
				$receivedItem->status = 'completed';
				$receivedItem->gr_expiry = $expiry;
				$receivedItem->date_of_manufacture = $date_of_manufacture;
				$receivedItem->slot_id = $slot;
				$receivedItem->store_id = $store;
				$receivedItem->lot_no = $lot_no;
				$receivedItem->issued_by = \Auth::user()->id;
				$receivedItem->save();

				$this->affect_inventory('receive', $receivedItem, $entity);

				if($entityItem->pending() > 0){
					$entityItem->status = "partial";
				}
				else{
					$entityItem->status = "completed";
				}

				$entityItem->save();
			}
		}

		$entityItems = RequestEntityItem::where('request_id', $entity->id)->where('ammendment', $entity->ammendment)
			->where('action', 'normal')->get();

		foreach($entityItems as $i){
			$anyPendingItems[] = $i->status;
		}

		// return json_encode($anyPendingItems);

		$status = "Goods Accepted";

		$entity->status = $status;
		$entity->save();

		$Requester = \App\User::find($otp->user_id);

		$companyDetails = getCompanyDetails();

		$body = '
			Hi '.$Requester->name.',<br><br>
			Items from Goods Receipt['.$entity->request_code.'] have been added to the inventory.
			Regards,<br>
			'.$companyDetails['name'].'
		';

		$mailData = array(
			'contacts' => array($Requester->email),
			'body' => $body,
			'subject' => '['.$entity->request_code.'] Goods Receipt Confirmation'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->back()->with('success', ' Items Received.');
	}

	public function get_requester_approval($entity){
		$companyDetails = getCompanyDetails();
		$APPR_USER = \App\User::find($entity->request_initiator);

		$body = '
			Hi '.$APPR_USER->name.',<br><br>
			There has been an ammendment made to the RFQ from your material requisition.
			Please click on this link <a href="'.route('view-request-details', ['stage'=>$entity->request_type,'id'=>$entity->id]).'">'.route('view-request-details', ['stage'=>$entity->request_type,'id'=>$entity->id]).'</a> to view the changes made to the items and then approve/reject the ammendment.
			<br>Regards,<br>
			'.$companyDetails['name'].'
		';

		$entity->status = "Amendment Awaiting Approval";
		$entity->save();
		// return $APPR_USER;

		$mailData = array(
			'contacts' => array($APPR_USER->email),
			'body' => $body,
			'subject' => '['.$entity->request_type.'] Amendment Approval Request'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->back()->with('success', 'Awaiting Requester Approval.');
	}

	public function cancel_ammendment(Request $request, $entity){
		$companyDetails = getCompanyDetails();

		$ammendment_no = $entity->ammendment;

		if($entity->in_ammendment){
			RequestEntityItem::where('request_id', $entity->id)->where('ammendment', $ammendment_no)->delete();
		}

		$entity->ammendment = $ammendment_no - 1;
		$entity->in_ammendment = 0;
		$entity->status = "Completed";
		$entity->save();

		$theUser = \App\User::find($entity->created_by);

		$body = '
			Hi '.$theUser->name.',<br><br>
			Amendments that were made on this RFQ have been rejected by the requester. They gave the following reason;
			<p style="padding: 5px 10px; font-size: 13px; color: #444"> <em>'.$request->rejection_message.'</em></p>
			<br>Regards,<br><br>
			'.$companyDetails['name'].'
		';

		$subject = "[".$entity->request_code."] Requester has rejected ammendments";

		return $this->notify_user($body, $theUser->email, $subject);

		return redirect()->back()->with('success', 'Amendment Cancelled.');
	}

	public function make_an_ammendment($entity){
		$RFQ = RequestEntity::find($entity->parent_request_id);

		$RFQ->in_ammendment = 1;
		$RFQ->ammendment = $RFQ->ammendment + 1;

		$RFQ->save();

		return redirect()->route('view-request-details', ['stage'=>$RFQ->request_type,'id'=>$RFQ->id])->with('success', 'RFQ Set into edit mode.');
	}

	public function approve_ammendments_details($request, $entity){
		$entities = RequestEntity::where('request_type', '=', 'Purchase Orders')->where('parent_request_id', $entity->id)->pluck('id');


		$rfqItems = RequestEntityItem::where('request_id', $entity->id)->get();

		foreach($rfqItems as $rItem){
			$poItem = RequestEntityItem::whereIn('request_id', $entities)->where('quantity', $rItem->quantity)
			->where('inventory_sub_category_id', $rItem->inventory_sub_category_id)->first();

			$poItem->item_brand_id = $rItem->item_brand_id;
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
			Hi '.$theUser->name.',<br><br>
			Amendments that were made on this RFQ have been approved by the requester.
			<br>Regards,<br><br>
			'.$companyDetails['name'].'
		';

		$subject = "[".$entity->request_code."] Requester has approved amendments";

		return $this->notify_user($body, $theUser->email, $subject);
	}

	public function mark_po_as_complete($id){
		$getPO = RequestEntity::find($id);

		$allItems = $getPO->items($getPO->ammendment) ?? array();

		$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

		$markAsComplete = true;

		foreach ($normalItems as $req_item){
			if($req_item->pending() > 0){
				$markAsComplete = false;
			}
		}

		if($markAsComplete){
			$getPO->status = "Completed";
			$getPO->save();
		}

		return $markAsComplete;
	}

	public function mark_gr_as_complete($id){
		$req = RequestEntity::find($id);

		$req->status = "Completed";
		$req->save();

		$this->mark_po_as_complete($req->parent_request_id);

		return redirect()->back()->with('success', 'Goods Receipt has been marked as complete.');
	}

	public function send_to_finance($req){
		$req->status = "Awaiting Finance Approval";
		$req->save();

		$finance_role_id = getConfigByName('finance_department_role_id')[0]->value ?? 0;

		$users = \App\Role::find($finance_role_id)->getUsersByRole();

		$userEmails = $users->pluck('email')->toArray();

		$companyDetails = getCompanyDetails();

		$body = '
			Hi,<br><br>
			The Goods Receipt '.$req->request_code.' has been sent to the finance department for processing.
			Please review this GRN by clicking <a href="'.route("view-request-details", ["stage"=>$req->request_type, "id"=>$req->id]).'">here</a>.
			<br>Regards,<br><br>
			'.$companyDetails['name'].'
		';

		$subject = "[".$req->request_code."] Invoice Awaiting Processing";
		return $this->notify_user($body, $userEmails, $subject);
	}

	public function change_req_approver(Request $request, $id){
		$approval = \App\EntityApproval::find($id);
		$approval->user_id = $request->user_id;
		$approval->save();

		$req = \App\RequestEntity::find($approval->model_id);

		$user = \App\User::find($request->user_id);
		$companyDetails = getCompanyDetails();

		$body = '
			Hi '.$user->name.',<br><br>
			There is an approval request for '.$req->request_type.' - '.$req->request_code.'. Please click this link
			<a href="'.route("view-request-details", ["stage"=>$req->request_type, "id"=>$req->id]).'">'.route("view-request-details", ["stage"=>$req->request_type, "id"=>$req->id]).'</a> to view the request.
			<br>Regards,<br>
			'.$companyDetails['name'].'
		';

		// return $APPR_USER;

		$mailData = array(
			'contacts' => [$user->email],
			'body' => $body,
			'subject' => '[Approval Request] Approval Request for '.$req->request_type.' - '.$req->request_code
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return  redirect()->back()->with('success', 'Approver has been changed.');
	}

	public function create_lpo_from_mr(Request $request, $id){
		$req = RequestEntity::find($id);
		$purchaseOrder = $req->replicate();
		$purchaseOrder->parent_request = $req->request_type;
		$purchaseOrder->request_code = getNamingConventionCode("Purchase Orders", false, "PO");
		$purchaseOrder->parent_request_id = $req->id;
		$purchaseOrder->request_type = "Purchase Orders";
		$purchaseOrder->status = "Awaiting Approval";
		$purchaseOrder->supplier_id = $request->supplier_id;
		$purchaseOrder->created_by = \Auth::user()->id;
		$purchaseOrder->parent_material_requisition = $req->id;
		$purchaseOrder->net_value = 0;
		$purchaseOrder->save();

		$approvals = getStageApprovals('Requisition', 'Purchase Orders');

		$firstApprover = false;

		$previousApprovers = [];
		foreach($approvals as $app){
			$approver = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')
				->whereNotIn('users.id', $previousApprovers)->where('ur.role_id', $app->role_id)->selectRaw('users.id, users.email')->first();
			$approverID = $approver->id;

			$entity_approval = new \App\EntityApproval;
			if(!$firstApprover){
				$firstApprover = $approver;
			}

			$entity_approval->approval_id = $app->id;
			$entity_approval->model = 'Purchase Orders';
			$entity_approval->model_id = $purchaseOrder->id;
			$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
			$entity_approval->user_id = $approverID;
			$entity_approval->save();

			$previousApprovers[] = $approverID;
		}

		if($firstApprover){

			$companyDetails = getCompanyDetails();

			$body = '
				Hi,<br><br>
				There is a Purchase Order that requires your approval. <br>
				Click <b><a href="'.route('view-request-details', ['stage'=> $purchaseOrder->request_type, 'id'=>$purchaseOrder->id]).'">here</a></b> to view RFQ.
				<br>
				Regards,<br>
				'.$companyDetails['name'].'
			';

			$mailData = array(
				'contacts' => [$firstApprover->email],
				'body' => $body,
				'subject' => '[Approval Request] Approval Request for Purchase Order - '.$purchaseOrder->request_code
			);

			$mailer = new Mailer;

			$sendMail = $mailer->html_email($mailData, 'default');
		}

		$entityItems = RequestEntityItem::where('request_id', $req->id)->get();
		$itemsCatNames = array();

		foreach($entityItems as $i){
			$iO = $i->replicate();
			$iO->request_id = $purchaseOrder->id;
			$iO->save();

			$purchaseOrder->net_value += floatval($iO->net_value);
			$purchaseOrder->save();


			$subCat = \App\InventorySubCategories::find($iO->inventory_sub_category_id);
			$itemsCatNames[] = $subCat->name."(".$iO->quantity.")";

			$inventoryItem = \App\InventorySubCategories::find($iO->inventory_sub_category_id);

			$due_date = \Carbon\Carbon::now()->addDays($inventoryItem->external_lead_time);

			$purchaseOrder->due_date = $due_date > $purchaseOrder->due_date ? $due_date : $purchaseOrder->due_date;
		}

		$purchaseOrder->description = implode(", ", $itemsCatNames);
		$purchaseOrder->save();

		return redirect()->route('view-request-details', ['stage'=>'Purchase Orders', 'id'=>$purchaseOrder->id])->with('success', 'Purchase Order Successfully created');
	}

	public function update(Request $request, $stage, $id){
		// return response()->json($request->all(), 200);

		// $data = array();
		// foreach($request->attachments['title'] as $i=>$title){
		// 	echo $title." >>>> ".$request->attachments['file'][$i]->path()."<br>";
		// 	$data[] = [$title, $request->attachments['file'][$i]->path()];
		// }

		// return response()->json($data, 200);

		$companyDetails = getCompanyDetails();

		$req = RequestEntity::find($id) ?? new RequestEntity;

		if($request->has('make_an_ammendment')){
			return $this->make_an_ammendment($req);
		}

		if($request->has('cancel_ammendment')){
			return $this->cancel_ammendment($request, $req);
		}

		if($request->has('send_to_finance')){
			return $this->send_to_finance($req);
		}

		if($request->has('approve_ammendments_details')){
			return $this->approve_ammendments_details($request, $req);
		}

		if($request->has('get_requester_approval')){
			return $this->get_requester_approval($req);
		}

		if($request->has('mark_as_complete')){
			$req->status = "Completed";
			$req->save();

			if($stage == "Goods Receipt"){
				$requisition = $req->parent_material_requisition;

				$parentRequisitionEntity = RequestEntity::find($req->parent_material_requisition);

				$materialIssue = RequestEntity::where('parent_material_requisition', $req->parent_material_requisition)->where('request_type', 'Material Issuance')
					->first();

				if($materialIssue){
					$theUser = \App\User::find($parentRequisitionEntity->request_initiator) ?? \App\User::find($parentRequisitionEntity->created_by);

					$body = '
						Hi '.$theUser->name.',<br><br>
						There are items from your material requisition available for you to pickup at the store.<br>
						Regards,<br><br>
						'.$companyDetails['name'].'
					';

					$subject = '['.$companyDetails["name"].'] Material Issue '.$materialIssue->request_code;

					$materialIssue->status ="Awaiting User Reception";
					$materialIssue->save();

					return $this->notify_user($body, $theUser->email, $subject);
				}
			}

			return redirect()->back()->with('success', rtrim($stage, 's').' marked as complete');
		}

		if($request->has('issue_out_items')){
			return $this->issue_out_items($request, $req);
		}

		if($request->has('accept_goods_receipt')){
			return $this->accept_goods_receipt($request, $req);
		}

		if($request->has('return_goods_to_supplier')){
			return $this->return_goods_to_supplier($request, $req);
		}

		if($request->has('notify_the_user')){
			$theUser = \App\User::find($req->request_initiator) ?? \Auth::user();

			$body = '
				Hi '.$theUser->name.',<br><br>
				There are items from your material requisition available for you to pickup at the store.<br>
				Regards,<br><br>
				'.$companyDetails['name'].'
			';

			$subject = '['.$companyDetails["name"].'] '.$stage.' '.$req->request_code;

			$req->status ="Awaiting User Reception";
			$req->save();

			return $this->notify_user($body, $theUser->email, $subject);
		}

		if($request->has('generate_material_issuance')){
			return $this->create_material_issuance($request, $req);
		}

		if($request->has('generate_goods_receipt')){
			return $this->create_goods_receipt($request, $req);
		}

		if($request->has('create_goods_return')){
			return $this->create_goods_return($request, $req);
		}

		if($request->has('send_purchase_order')){
			return $this->send_purchase_order($req);
		}

		if($request->has('create_rfq_from_material_requisition')){
			return $this->create_rfq_from_material_requisition($req);
		}

		if($request->has('generate_purchase_order')){
			return $this->create_purchase_order($req);
		}

		if($request->has('awarded_quote_is')){
			$awardedQuote = \App\SupplierQuote::find($request->awarded_quote_is);

			$awardedQuote->awarded_at = \Carbon\Carbon::now();
			$awardedQuote->is_awarded = 1;
			$awardedQuote->save();

			$siblings = \App\SupplierQuote::where('request_id', $awardedQuote->request_id)
				->where('request_item_id', $awardedQuote->request_item_id)->update(['awarded_at'=>\Carbon\Carbon::now()]);

			// return json_encode($awardedQuote);

			$INVitem = $quotes = \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
			->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
			->where('supplier_quotes.request_id', $req->id)->where('supplier_quotes.id', $request->awarded_quote_is)->first();

			$note = new EntityNote;
			$note->type = "Supplier Awarded";
			$note->description = "<h5>".$INVitem->code." ".$INVitem->name."</h5><p>".$request->award_reason."</p>";
			$note->model = $stage;
			$note->model_id = $req->id;
			$note->created_by = \Auth::user()->id;

			$note->save();

			$req->status = "Awarded";
			$req->save();

			return \redirect()->back()->with('success', 'Supplier Quote Awarded');
		}

		if($request->has('get_approval')){
			$req->status = "Awaiting Approval";
			$req->save();

			$approvals = getStageApprovals('Requisition', $stage);
			$notifyFirstApprover = true;
			foreach($approvals as $app){
				$entity_approval = new \App\EntityApproval;

				$requestInitiator = \App\User::find(isset($req->request_initiator) ? $req->request_initiator : $req->created_by);

				$APPR_USER = \App\User::join('user_roles as ur', 'ur.user_id', '=', 'users.id')->where('ur.role_id', $app->role_id)
					->where('users.department_id', $requestInitiator->department_id)->selectRaw('users.id, users.name, users.email')->first();

				$AppUsers = getUsersByRole($app->role_id, true);

				$emailList = [];

				$approverFromRole;

				foreach($AppUsers as $au){
					$approverFromRole = $au->id;
				}

				$entity_approval->approval_id = $app->id;
				$entity_approval->model = $stage;

				if($APPR_USER && isset($APPR_USER->id)){
					$entity_approval->user_id = $APPR_USER->id;
				}
				else{
					$entity_approval->user_id = $approverFromRole;
				}

				$entity_approval->model_id = $req->id;
				$entity_approval->inventory_location_id = getCurrentUserLocation()->id;
				$entity_approval->save();

				$body = '
					Hi,<br><br>
					There is a '.$stage.' Approval Request. Please click this link
					<a href="'.route("view-request-details", ["stage"=>$stage, "id"=>$req->id]).'">'.route("view-request-details", ["stage"=>$stage, "id"=>$req->id]).'</a> to view the request.
					<br>Regards,<br>
					'.$companyDetails['name'].'
				';

				if($notifyFirstApprover){
					$req->approval_status = "Pending: ".$app->title;
				}

				// return $APPR_USER;
				$notifiableUser = \App\User::select('email')->first();
				$mailData = array(
					'contacts' => [$notifiableUser->email],
					'body' => $body,
					'subject' => '[Approval Request] Approval Request for '.$req->request_type.' - '.$req->request_code
				);

				$mailer = new Mailer;

				$sendMail = $mailer->html_email($mailData, 'default');

				$notifyFirstApprover = false;
			}

			// return json_encode($emailList);

			return \redirect()->back()->with('success', $stage.' Awaiting Approval.');
		}

		if($request->has('is_rfq_quote')){

			// return response()->json($request->all(), 200);

			foreach($request->quote['id'] as $i=>$k){
				$newQuote = \App\SupplierQuote::where('supplier_id', $request->supplier_id)
				->where('request_id', $req->id)->where('request_item_id', $i)->first() ?? new \App\SupplierQuote;
				$newQuote->supplier_id = $request->supplier_id;
				$newQuote->request_id = $req->id;
				$newQuote->request_item_id = $i;
				$newQuote->quote_amount = floatval($request->quote['amount'][$i]);

				$newQuote->save();
			}

			\App\SupplierRFQ::where('request_id', $req->id)->where('supplier_id', $request->supplier_id)->update(['quote_received'=>1]);
			if($req->status != "Awarded"){
				$req->status = "Receiving Quotes";
			}
			$req->save();

			return \redirect()->back()->with('success', 'Supplier Quote Received');
		}

		if($request->has('is_send_rfq')){
			// \App\SupplierRFQ::where('request_id', $req->id)->update(['rfq_sent'=>1]);

			$supplier_rfqs = \App\SupplierRFQ::where('request_id', $req->id)->get();

			foreach($supplier_rfqs as $rfq){

				$supplier = \App\Supplier::find($rfq->supplier_id);
				// $quotes = \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
				// 	->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
				// 	->where('supplier_quotes.request_id', $req->id)
				// 	->selectRaw('ics.name, rei.quantity, ics.code')
				// 	->where('supplier_quotes.supplier_id', $supplier->id)->get();

				$supplierItemsArr = $supplier->itemIDs();

				$quotes = RequestEntityItem::where('request_id', $req->id)
					->join('inventory_sub_categories as ics', 'ics.id', '=', 'request_entity_items.inventory_sub_category_id')
					->selectRaw('ics.name, request_entity_items.quantity, ics.code, ics.sap_code, ics.unit_type')->where('ammendment', $req->ammendment)
					->whereIn('ics.id', $supplierItemsArr)->get();

				$quoteItems = array();

				// return response()->json($req);

				$requesterEmail = \App\User::find($req->request_initiator)->email;
				$response_email = getConfigByName('supplier_rfq_email')[0]->value ?? 0;


				foreach($quotes as $o=>$q){
					$o++;
					$quoteItems[] = '<tr style=" border: 1px solid #aaa !important">
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$o.'</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$req->request_code.'</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$q->sap_code.'</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.$q->name.'</td>
						<td style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">'.number_format($q->quantity).$q->unit_type.'</td>
					</tr>';
				}

				$body = '
					Hi '.$supplier->name.',<br><br>
					Please provide us with a quote for the following items. Provide your quote as per the template attached. Send your quotes to <b>'.$response_email.'</b> and <b>cc '.$requesterEmail.'</b>.
					<table style="width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #aaa !important">
						<thead>
							<tr>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">No</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">RFQ Code</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">CAT No</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Item Description</th>
								<th style="text-align: center; vertical-align: middle;border: 1px solid #aaa !important">Quantity</th>
							</tr>
						</thead>
						<tbody>'.implode("
						", $quoteItems).'</tbody>
					</table><br><br>
					Regards,<br>
					'.$companyDetails['name'].'
				';

				$lab_contact = getConfigByName('lab_contact');
				$lab_contact_email = count($lab_contact) > 0 ? $lab_contact[0]->value : 'lab@aqualyticlab.com';

				$emails = split_emails(';', $supplier->email);
				$emails[] = \Auth::user()->email;
				$emails[] = $lab_contact_email;

				if(trim($requesterEmail) != ""){
					$emails[] = $requesterEmail;
				}

				$emails = array_filter($emails);

				//Generate PDF Bit
				$reportGenerator = new ReportGeneratorController();
				$templateFile =  $reportGenerator->generate_supplier_pdf($supplier->id, $req->id, true);

				$HTML = $templateFile->render();

				$PDF = \App::make('dompdf.wrapper')->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
				$PDF->loadHTML($HTML);
				$PDF->setPaper("letter", "portrait");

				$pdfPath = storage_path('app/supplier-rfq-pdfs/');

				if (!file_exists($pdfPath)) {
					mkdir($pdfPath, 0755, true);
				}

				$file = space_underscore(" ", $supplier->name."-".$req->request_code.'-v'.$req->ammendment).'.pdf';

				$pPath = $pdfPath.$file;
				$PDF->save($pPath);

				$templateFile = $pPath;
				//Generate PDF Bit

				$mailData = array(
					'contacts' => $emails,
					'body' => $body,
					'subject' => '['.$companyDetails["name"].'] Request for Quotation '.$req->request_code,
					'file' => $templateFile
				);

				$mailer = new Mailer;

				$sendMail = $mailer->html_email($mailData, 'default');

				$rfq->rfq_sent = 1;
				$rfq->save();
			}
			if($req->status != "Awarded"){
				$req->status = "RFQs sent out";
			}

			$req->save();

			return \redirect()->back()->with('success', '['.$stage.'] RFQs sent out to suppliers');
		}

		if($request->has('reject_reason')){
			$creator = \App\User::find($req->created_by);

			$req->status = "Rejected";
			$req->save();

			$note = new EntityNote;
			$note->type = "Rejection";
			$note->description = $request->reject_reason;
			$note->model = $stage;
			$note->model_id = $id;
			$note->created_by = \Auth::user()->id;

			$note->save();

			$entity_approval = \App\EntityApproval::find($request->approval_id);
			$entity_approval->status = "Rejected";
			$entity_approval->approved_at = \Carbon\Carbon::now();
			$entity_approval->description = $request->reject_reason;
			$entity_approval->user_id = \Auth::user()->id;
			$entity_approval->save();

			$body = '
				Hi '.$creator->name.',<br><br>
				Your '.rtrim($stage, 's').' has been rejected by '.\Auth::user()->name.'. The reason given is:<br><br>
				<p style="padding: 10px !important; font-size: 13px; font-weight:600; color: #a1a1a1; border: solid 1px #ccc;">'.$request->reject_reason.'</p>
				<br><br>Regards,<br>
				'.$companyDetails['name'].'
			';

			$subject = '['.$companyDetails["name"].'] '.$stage.' '.$req->request_code.' Rejected.';
			$this->notify_user($body, $creator->email, $subject);
			return \redirect()->back()->with('success', $stage.' Request Rejected');
		}

		if($request->has('approve_this')){

			$entity_approval = \App\EntityApproval::find($request->approval_id);
			$entity_approval->status = "Approved";
			$entity_approval->approved_at = \Carbon\Carbon::now();
			$entity_approval->description = "Approved";
			$entity_approval->user_id = \Auth::user()->id;
			$entity_approval->save();

			$doneApprovals = $req->done_approvals()->count();
			$totalApprovals = getStageApprovals('Requisition', $stage)->count();

			if($doneApprovals < $totalApprovals){
				$req->status = "Partially Approved";
			}
			else{
				$req->status = "Approval Complete";
				$req->approval_status = null;
			}
			$req->save();

			$USER = \App\User::find($req->created_by);
			$APPROVAL = \App\Approvals::find($entity_approval->approval_id);

			$body = 'Hi '.$USER->name.',<br>
				<strong>'.$APPROVAL->title.'</strong> has been completed for '.$stage.'. Click link
				<a href="'.route("view-request-details", ["stage"=>$stage, "id"=>$req->id]).'">'.route("view-request-details", ["stage"=>$stage, "id"=>$req->id]).'</a> to view the request.
				<br>Regards,<br>
				'.$companyDetails['name'];

			$mailData = array(
				'contacts' => array($USER->email),
				'body' => $body,
				'subject' => $stage.' Approval Completed'
			);

			$nextEntity_Approval = \App\EntityApproval::where('id', '>', $request->approval_id)->orderBy('id', 'asc')->first();


			if(isset($nextEntity_Approval->id)){
				$APPROVAL = \App\Approvals::find($nextEntity_Approval->approval_id);

				$AppUsers = getUsersByRole($APPROVAL->role_id, true);

				$emailList = [];

				foreach($AppUsers as $au){
					$emailList[] = $au->email;
				}

				$req->approval_status = "Pending: ".$APPROVAL->title;
				$req->save();
				$body = 'Hi,<br><br>
					There is a '.$stage.' Approval Request. Please click this link
					<a href="'.route("view-request-details", ["stage"=>$stage, "id"=>$req->id]).'">'.route("view-request-details", ["stage"=>$stage, "id"=>$req->id]).'</a> to view the request.<br>
					Regards,<br>
					'.$companyDetails['name'];

				$mailData = array(
					'contacts' => $emailList,
					'body' => $body,
					'subject' => '[Approval Request] Approval Request for '.$req->request_type.' - '.$req->request_code
				);
			}

			$mailer = new Mailer;

			$sendMail = $mailer->html_email($mailData, 'default');

			// return json_encode($emailList);

			return \redirect()->back()->with('success', $stage.' Approval was successful');
		}

		if(!isset(RequestEntity::find($id)->id)){
			if($stage == "Material Requisition"){
				$pref = "MR";
			}

			if($stage == "Request for Quotation"){
				$pref = "RFQ";
			}

			if($stage == "Purchase Orders"){
				$pref = "PO";
			}

			if($stage == "Request to Store"){
				$pref = "RS";
			}
			$status = "In Preparation";
		}

		if($request->has('issue_to')){
			$req->issue_to = $request->issue_to;
		}
		$req->priority = $request->priority;
		$req->nature_of_purchase = $request->nature_of_purchase;
		$req->currency = $request->currency;

		$req->due_date = \Carbon\Carbon::parse($request->valid_until ?? $request->delivery_date);

		if($stage == "Goods Return"){
			$req->gate_pass = $request->gate_pass;
			$req->time_out = $request->time_out;
			$req->vehicle_no = $request->vehicle_no;
		}

		if($stage == "Request for Quotation"){
			$req->submission_deadline = \Carbon\Carbon::parse($request->submission_deadline);
		}

		if(!isset(RequestEntity::find($id)->id)){
			$req->status = $status;

			$req->request_code = getNamingConventionCode($stage, false, $pref);

			$req->request_type = $stage;
			$req->created_by = \Auth::user()->id;
			if($stage == "Material Requisition"){
				$req->request_initiator = \Auth::user()->id;
			}

		}

		if($stage != "Material Requisition" && trim($req->parent_request) == ""){
			$req->parent_request = $request->prev_stage;
			$req->parent_request_id = $request->prev_stage_id;
		}

		$req->required_approvals = getStageApprovals('Requisition', $stage)->count() ?? 0;

		$req->save();

		$req->approval_count = getEntityApprovals($stage, $req->id)->count() ?? 0;

		$notesArray = array();
		$attachmentArray = array();

		if($request->has('suppliers')){
			foreach($request->suppliers['supplier_rfq_id'] ?? array() as $i=>$it){
				$suprfqEx = \App\SupplierRFQ::find($it);

				$rfq = $suprfqEx ?? new \App\SupplierRFQ;
				$rfq->supplier_id = $request->suppliers['id'][$i];
				$rfq->request_id = $req->id;
				$rfq->save();
			}
		}

		foreach($request->notes['note_id'] ?? array() as $i=>$it){
			$noteExist = EntityNote::find($it);

			$note = $noteExist ?? new EntityNote;
			$note->type = $request->notes['type'][$i];
			$note->description = $request->notes['description'][$i];
			$note->model = $stage;
			$note->model_id = $req->id;
			$note->created_by = \Auth::user()->id;

			$note->save();

			$notesArray[] = $note->id;
		}

		EntityNote::whereNotIn('id', $notesArray)->where('model', $stage)->where('model_id', $req->id)->delete();

		foreach($request->attachments['attachment_id'] ?? array() as $i=>$it){
			$attachmentExist = EntityAttachment::find($it);

			$attachment = $attachmentExist ?? new EntityAttachment;
			$attachment->type = $request->attachments['type'][$i];
			$attachment->title = $request->attachments['title'][$i];
			$attachment->description = $request->attachments['description'][$i];
			$attachment->model = $stage;
			$attachment->model_id = $req->id;
			$attachment->created_by = \Auth::user()->id;


			$filed = $request->attachments['file'][$i] ?? false;

			if($filed){
				$path = $filed->path();

				$file = Storage::putFile('requisition', new File($path));
				$file = explode('/', $file);

				$fName = '/storage/requisition/'.urlencode(end($file));

				$attachment->file = (String) $fName;
			}

			$attachment->save();

			$attachmentArray[] = $attachment->id;
		}

		EntityAttachment::whereNotIn('id', $attachmentArray)->where('model', $stage)->where('model_id', $req->id)->delete();
		if(in_array($req->request_type, ["Material Requisition", "Request for Quotation", "Request to Store", "Goods Receipt"])){
			$itemsCatNames = array();
			$itemsArrIds = array();
			$totalValue = 0;

			$has_been_ammended = false;
			foreach($request->items['req_item_id'] ?? array() as $i=>$it){
				$entityItem = RequestEntityItem::find($it);

				// return json_encode($request->items['remarks']);

				if(isset($entityItem->id) && $req->in_ammendment > 0){
					if($entityItem->item_brand_id != $request->items['item_brand_id'][$i] || $entityItem->quantity != $request->items['quantity'][$i]
					|| $entityItem->inventory_sub_category_id!=$request->items['item_id'][$i]){
						$has_been_ammended = true;
					}
				}

				if($req->in_ammendment == 1){
					$entityID = $entityItem->id;
					$entityItem = $entityItem->replicate();

					$entityItem->ammendment = $req->ammendment;
					$entityItem->request_entity_item_ammended_id = $entityID;
					$entityItem->save();
				}

				$subCatID = $request->items['item_id'][$i];

				$subCat = \App\InventorySubCategories::find($subCatID);

				$item = $entityItem ?? new RequestEntityItem;
				$item->request_id = $req->id;
				$item->item_brand_id = $request->items['item_brand_id'][$i] ?? 0;
				$item->store_id =$request->items['store_id'][$i] ?? 0;
				$item->slot_id = $request->items['slot_id'][$i] ?? 0;
				$item->uom = $request->items['uom'][$i] ?? 0;
				$item->inventory_sub_category_id = $subCatID;

				$item->comments = $request->items['comments'][$i];
				$item->quantity = $request->items['quantity'][$i] ?? $request->items['received_quantity'][$i];
				$item->net_value = $request->items['net_value'][$i];

				$item->starting_sample = $request->items['starting_sample'][$i] ?? null;
				$item->lot_no = $request->items['lot_no'][$i] ?? null;
				$item->test = $request->items['test'][$i] ?? null;
				$item->remarks = $request->items['remarks'][$i] ?? null;

				$item->save();


				// return json_encode($item);

				$itemsArrIds[] = $item->id;
				$itemsCatNames[] = $subCat->name."(".($request->items['quantity'][$i] ?? $request->items['received_quantity'][$i]).")";

				$totalValue += floatval($item->net_value);

				if($stage == "Material Requisition"){
					$due_date = $req->created_at->addDays($subCat->internal_lead_time);
				}

				if($stage == "Request for Quotation"){
					$pref = "RFQ";
					$due_date = $req->created_at->addDays($subCat->internal_lead_time);
				}

				if($stage == "Purchase Orders"){
					$pref = "PO";
					$due_date = $req->created_at->addDays($subCat->external_lead_time);
				}

				$req->due_date = isset($due_date) ? $due_date : \Carbon\Carbon::now();
			}

			if($has_been_ammended){
				$req->in_ammendment = 2;
			}

			// return response()->json($itemsArrIds, 200);
			if(!$has_been_ammended){
				RequestEntityItem::whereNotIn('id', $itemsArrIds)->where('request_id', $req->id)->delete();
			}
			if(in_array($stage, ["Material Requisition", "Request to Store"])){
				$req->description =	$request->description;

				if($req->status == "In Preparation" && $request->has('initiator')){
					$req->request_initiator = $request->initiator ?? \Auth::user()->id;
				}
			}
			else{
				$req->description = $request->description;
			}
			$req->net_value = $totalValue;

			$req->inventory_location_id = getCurrentUserLocation()->id;

			$req->save();
		}

		return redirect()->route('view-request-details', ['stage'=>$stage, 'id'=>$req->id])->with('success', $stage.' saved.');
	}

	public function download($id, $type){
		// return view($TEMPLATE[$type], )
	}
}
