<?php

namespace App\Http\Controllers\GeneralRequisition;

use App\Supplier;
use App\Approvals;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\InventorySubCategories as InventoryItem;
use App\EntityAttachment as Attachment;
use App\Http\Controllers\MailController as Mailer;
use App\Models\GeneralRequisition\GeneralRequistionRequest as GRequest;
use App\Models\GeneralRequisition\GeneralRequisitionRequestItem as Item;
use App\Models\GeneralRequisition\GeneralRequisitionSupplierQuotes as Quote;
use App\User;
use Carbon\Carbon;

class GeneralRequisitionController extends Controller
{

	protected $approval_modes = [
		"pr" =>	['request_by_1', 'checked_by_1', 'approved_by_1'],
		"quote" =>	['request_by_2', 'checked_by_2', 'approved_by_2'],

	];

	public function __construct()
  {
    $this->middleware('auth');
  }

	public function index(Request $request){
		$stage = "General Requisition";

		$nowDate = Carbon::now();
		
		$start_date = Carbon::parse('first day of this month');
		$end_date = Carbon::parse('last day of this month');

		if($request->has('start_date')){
			$start_date = Carbon::parse($request->start_date);
		}

		if($request->has('end_date')){
			$end_date = Carbon::parse($request->end_date);
		}

		$requests = GRequest::orderBy('date_required', 'asc')->where('created_at', '>=',$start_date)->where('created_at', '<=',$end_date)->get();
		$approvals = Approvals::where('stage', $stage)->orderBy('level', 'asc')->get();
		return view('layouts.inventory.requisition.general.index', compact('requests', 'approvals', 'stage', 'start_date', 'end_date'));
	}

	public function show(Request $request, $id=false){
		$stage = "General Requisition";
		$grequest = $id ? GRequest::find($id) : new GRequest;

		$general_requisition_category_id = getConfigByName('general_requisition_category_id');
		$general_requisition_category_id = count($general_requisition_category_id) > 0 ? $general_requisition_category_id[0]->value : 0;
		
		$listedItems =  InventoryItem::where('inventory_category_id', $general_requisition_category_id)->orderBy('name', 'asc')->get();

		$filteredSuppliers = Supplier::join('general_requisition_supplier_quotes as grsq', 'grsq.supplier_id', 'suppliers.id')
			->selectRaw('suppliers.id, suppliers.name');

		if($id){
			$filteredSuppliers = $filteredSuppliers->where('grsq.request_id', $id);
		}
				
		$filteredSuppliers = $filteredSuppliers->groupBy('suppliers.id')->orderBy('name', 'asc')->get();

		$getUsers = \App\User::where('active',1)->orderBy('name', 'asc')->get();

		return view('layouts.inventory.requisition.general.show', compact('getUsers', 'grequest', 'listedItems', 'filteredSuppliers', 'id', 'stage'));
	}

	public function update(Request $request, $id=false){
		$grequest = $id ? GRequest::find($id) : new GRequest;

		// return '<pre>'.json_encode($request->all(), JSON_PRETTY_PRINT);

		$grequest->code = '-';
		$grequest->requesting_department = $request->requesting_department;
		$grequest->reference = $request->reference;
		$grequest->payment_mode = $request->payment_mode;
		$grequest->laboratory = $request->laboratory;
		$grequest->description = $request->description;
		$grequest->date_required = $request->date_required;
		if(!$id){
			$grequest->status = 'In Preparation';
			$grequest->created_by = \Auth::user()->id;
		}

		$grequest->save();
		$grequest->code = 'GR'.str_pad($grequest->id, 3, '0', STR_PAD_LEFT);
		$grequest->save();

		$general_requisition_category_id = getConfigByName('general_requisition_category_id');
		$general_requisition_category_id = count($general_requisition_category_id) > 0 ? $general_requisition_category_id[0]->value : 0;
		

		if(isset($request->item['item_id'])){
			foreach($request->item['item_id'] as $ind=>$rid){
				if(trim($request->item['text_item_description'][$ind])!=""){
					$invItem = new InventoryItem;
					$invItem->name = $request->item['text_item_description'][$ind];
					$invItem->description = $request->item['text_item_description'][$ind];
					$invItem->image = '/images/no-image.png';
					$invItem->inventory_category_id = $general_requisition_category_id;
					$invItem->save();
				}
				else{
					$invItem = InventoryItem::find($request->item['item_id'][$ind]);
				}

				$item = Item::find($ind);

				
				if(trim($request->item['supplier_id'][$ind])!="" && $item->supplier_id != trim($request->item['supplier_id'][$ind])){
					$supp = Supplier::find($request->item['supplier_id'][$ind]);
					$quote = Quote::where('supplier_id', $supp->id)->where('request_id', $grequest->id)->where('request_item_id', $ind)->first();
					
					$item->supplier = $supp->name;
					$item->supplier_id = $supp->id;
					if(trim($item->supplier_assignment_date) == "" && isset($supp->id)){
						$item->supplier_assignment_date = \Carbon\Carbon::now();
					}
					$item->ext_cost = $quote->amount ?? 0;

					$grequest->show_quote_approval = 1;
					$grequest->save();
				}
				else{
					if($grequest->show_pr_approval != 1){
						$grequest->show_pr_approval = 1;
						$grequest->save();
					}
				}

				$item->item_id = $invItem->id;
				$item->item_description = $invItem->name;
				$item->product_no = $request->item['product_no'][$ind];
				$item->request_id = $grequest->id;
				$item->purpose = $request->item['purpose'][$ind];
				$item->qty = $request->item['qty'][$ind];
				$item->last_unit_price = $request->item['last_unit_price'][$ind] ?? 0;
				
				$item->save();
			}
		}

		if(isset($request->new_item['item_id']) || isset($request->new_item['text_item_description'])){
			foreach($request->new_item['item_id'] as $ind=>$rid){
				if(trim($request->new_item['text_item_description'][$ind])!=""){
					$invItem = new InventoryItem;
					$invItem->name = $request->new_item['text_item_description'][$ind];
					$invItem->description = $request->new_item['text_item_description'][$ind];
					$invItem->image = '/images/no-image.png';
					$invItem->inventory_category_id = $general_requisition_category_id;
					$invItem->save();
				}
				else{
					$invItem = InventoryItem::find($request->new_item['item_id'][$ind]);
				}

				if($grequest->show_pr_approval != 1){
					$grequest->show_pr_approval = 1;
					$grequest->save();
				}

				$previousItem = Item::where('item_id', $invItem->id)->orderBy('id', 'desc')->first();
				$previousPrice = isset($previousItem->ext_cost) ? floatval($previousItem->ext_cost)/floatval($previousItem->qty) : 0;
				$item = new Item;
				$item->item_id = $invItem->id;
				$item->item_description = $invItem->name;
				$item->product_no = $request->new_item['product_no'][$ind];
				$item->request_id = $grequest->id;
				$item->purpose = $request->new_item['purpose'][$ind];
				$item->qty = $request->new_item['qty'][$ind];
				$item->last_unit_price = $previousPrice;
				$item->save();
			}
		}

		return redirect()->route('general-requisition-view', $grequest->id)->with('success', 'General Requisition details updated successfully.');
	}

	public function remove_items(Request $request, $id){
		Item::whereIn('id', $request->items)->delete();
		return json_encode(['status'=>true, 'message'=>'items were deleted']);
	}

	public function suppliers(Request $request, $search=''){
		$suppliers = Supplier::where('active', 1);
		if(trim($search) != ''){
			$suppliers = $suppliers->where('name', 'like', '%'.$search.'%');
		}

		$suppliers = $suppliers->selectRaw('id, name, CONCAT(id, "--", name) as id_name')->get();

		return json_encode($suppliers);
	}

	public function inventory_items(Request $request, $search=''){
		$general_requisition_category_id = getConfigByName('general_requisition_category_id');
		$general_requisition_category_id = count($general_requisition_category_id) > 0 ? $general_requisition_category_id[0]->value : 0;
		
		$items = InventoryItem::leftJoin('general_requisition_request_items as grri', 'grri.item_id', '	inventory_sub_categories.id')->where('inventory_category_id', $general_requisition_category_id);
		if(trim($search) != ''){
			$items = $items->where('name', 'like', '%'.$search.'%');
		}

		$items = $items->selectRaw('id, name, CONCAT(id, "--", name) as id_name')->get();

		return json_encode($suppliers);
	}

	public function view_document(Request $request, $id){
		$stage = "General Requisition";
		$grequest = GRequest::find($id);

		return view('layouts.inventory.templates.gr-2', compact('grequest', 'id', 'stage'));
	}

	public function change_status(Request $request, $id, $field){
		$grequest = GRequest::find($id);
		$user = \Auth::user();
		$now = \Carbon\Carbon::now();
		$grequest->$field = $user->name.'||'.$now.'||'.$user->electronic_sig;

		if($request->has('reason_for_approval')){
			$grequest->reason_for_approval = $request->reason_for_approval;
		}

		$grequest->status = $field == "" ? 'Approval Completed' : 'In Approvals';

		$grequest->save();
		
		return redirect()->back()->with('success', 'General Requisition approved successfully.');
	}

	public function sendApprovalNotifications(Request $request, $id, $type){
		$grequest = GRequest::find($id);
		$grequest->status = 'In Approvals';
		$grequest->save();

		$user = $type=='pr' ? $grequest->requester->id : \Auth::user()->id;
		
		$checkUser = $request->checked_by;
		$approveUser = $request->approved_by;

		$requestedBy = $this->setApprovalObject($user, 'Completed');
		$checkedBy = $this->setApprovalObject($checkUser, 'Pending');
		$approvedBy = $this->setApprovalObject($approveUser, 'Pending');

		$requestedByField = $type == 'pr' ? 'requested_by_1' : 'requested_by_2';
		$checkedByField = $type == 'pr' ? 'checked_by_1' : 'checked_by_2';
		$approvedByField = $type == 'pr' ? 'approved_by_1' : 'approved_by_2';

		$fields = [[$requestedByField, $requestedBy], [$checkedByField, $checkedBy], [$approvedByField, $approvedBy]];

		foreach($fields as $fld){
			$fldS = (string) $fld[0];
			$grequest->$fldS = json_encode($fld[1]);
		}
		$grequest->save();

		$checkUser = \App\User::find($checkUser);

		$this->send_approver_msg($checkUser, $grequest);

		return redirect()->back()->with('success', 'Approvals Set.');
 	}

	public function sendGRequestPDF($user, $grequest){
		$companyDetails = getCompanyDetails();

		$body = '
			Hi '.$user->name.',<br><br>
			Please find attached a copy of your general requisition form. All approvals have been completed.
			<br>
			Regards,<br>
			'.$companyDetails['name'].'
		';
		
		$grHTML = $this->view_document(new Request(), $grequest);

		$mailData = array(
			'contacts' => [$user->email],
			'body' => $body,
			'subject' => '['.$grequest->code.'] General Requisition Document'
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return true;
	}

	public function send_approver_msg($user, $grequest){
		$companyDetails = getCompanyDetails();

		$body = '
			Hi '.$user->name.',<br><br>
			General Requisition <strong>'.$grequest->code.'</strong> requires your approval. <br>
			Click <b><a href="'.route('general-requisition-view', $grequest->id).'">here</a></b> to view the GR.
			<br>
			Regards,<br>
			'.$companyDetails['name'].'
		';
		
		$mailData = array(
			'contacts' => [$user->email],
			'body' => $body,
			'subject' => '[GR Approval Request] General Requisition '.$grequest->code
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');

		return true;
	}
	 
	public function setApprovalObject($user, $status){
		$USER = \App\User::find($user);
		return  [
			"user" => ["id"=> $USER->id, "position" => $USER->position_name(), "name"=> $USER->name, "signature"=>$USER->electronic_sig],
			"status" => $status,
			"created_at" => date("Y-m-d h:i:s"),
			"time" => in_array($status, ["Completed", "Rejected"]) ? date("Y-m-d h:i:s") : ''
		];
	}

	public function approve_this(Request $request, $id, $dType){
		$type = explode('-', $dType);
		$field = (string) $type[1];
		$grequest = GRequest::find($id);
		$user = \Auth::user();

		if($request->action == "approve"){
			$approvalOBJ = $this->setApprovalObject($user->id, 'Completed');
			$reason = $request->reason_for_approval;
		}
		elseif($request->action == "return"){
			$approvalOBJ = $this->setApprovalObject($user->id, 'Return');
			$reason = $request->reason_for_approval;
		}
		else{
			$approvalOBJ = $this->setApprovalObject($user->id, 'Rejected');
			$reason = $request->reason_for_approval;
		} 

		$requester = $grequest->requester;
		$companyDetails = getCompanyDetails();
		$action = $request->action == 'approve' ? 'approved' : $request->action.'ed';

		$body = '
			Hi '.$requester->name.',<br><br>
			Your General Requisition <strong>'.$grequest->code.'</strong> was '.$action.' by '.$user->name.'. <br>';
			
		if(trim($reason)!=""){
			$body .= 'The following reason was provided by '.$user->name.'<br><p><em>'.$reason.'</em></p>';
		}
		
		$body .= '
			Click <b><a href="'.route('general-requisition-view', $grequest->id).'">here</a></b> to view the GR.
			<br>
			Regards,<br>
			'.$companyDetails['name'].'
		';
		
		$mailData = array(
			'contacts' => [$requester->email],
			'body' => $body,
			'subject' => '[GR '.ucwords($action).'] Your General Requisition '.$grequest->code. ' has been '.$action
		);

		$mailer = new Mailer;

		$sendMail = $mailer->html_email($mailData, 'default');
		
		$grequest->$field = json_encode($approvalOBJ);

		if($field == 'approved_by_2'){
			$grequest->show_quote_approval = 0;
		}
		if($field == 'approved_by_1'){
			$grequest->show_pr_approval = 0;
		}

		$grequest->save();

		if(in_array($field, ['checked_by_1', 'checked_by_2'])){
			$aField = $field == 'checked_by_1' ? 'approved_by_1' : 'approved_by_2';
			$dataJ = json_decode($grequest->$aField);
			$apprUser = \App\User::find($dataJ->user->id);
			$this->send_approver_msg($apprUser, $grequest);
		}			

		$isFullyApproved = true;

		foreach($this->approval_modes as $md => $types){
			foreach($types as $ty){
				if(trim($grequest->$ty) == ''){
					$isFullyApproved = false;
				}
			}
		}


		if($isFullyApproved){
			$HTML = $this->show($request, $id);


			$PDF = \App::make('dompdf.wrapper')->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
			$PDF->loadHTML($HTML);
			$PDF->setPaper("letter", "portrait");

			$pdfPath = storage_path('app/general-requisition-pdfs/');

			if (!file_exists($pdfPath)) {
				mkdir($pdfPath, 0755, true);
			}

			$file = $grequest->code.'.pdf';

			$pPath = $pdfPath.$file;
			$PDF->save($pPath);
			$templateFile = $pPath;

			$email = User::find($grequest->created_by);

			if(isset($email->email)){
				$body = '
					Hi '.$email->name.',<br>
					Your requisition is fully approved. Please find attached the General Requisition document.<br>
					<br>
					Regards,<br>
					'.$companyDetails['name'].' 
				';
				$mailData = array(
					'contacts' => $email->email,
					'body' => $body,
					'subject' => 'General Requisition '.$grequest->code,
					'file' => $templateFile
				);
			}

			
		}

		return redirect()->back()->with('success', 'Approval Complete');
	}

	public function change_approver(Request $request, $id, $field){
		$grequest = GRequest::find($id);
		$newUser = \App\User::find($request->user);
		
		$jData = \json_decode($grequest->$field);

		$newData = $this->setApprovalObject($newUser->id, $jData->status);

		$grequest->$field = $newData;
		$grequest->save();

		$this->send_approver_msg($newUser, $grequest);

		return redirect()->back()->with('success', 'New approver set.');
	}
}