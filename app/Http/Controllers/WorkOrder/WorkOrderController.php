<?php

namespace App\Http\Controllers\WorkOrder;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


use App\EntityAttachment;
use App\InventoryDepartment;
use App\Datatables\Datatables;
use App\Models\CRM\CRMCustomer;
use App\Models\Workorder\Service;
use App\Models\Workorder\WorkOrder;
use App\Models\Workorder\WorkorderEdit;
use App\Models\Workorder\WorkOrderResource;
use App\Models\Workorder\WorkOrderStatusHistory;

use App\Http\Controllers\MailController as Mailer;

use App\Http\Controllers\Controller;

class WorkOrderController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

	public function dashboard(){
		$data = [];

		return view('layouts.workorder.dashboard', compact('data'));
	}

	public function index($wo_type='all'){
		$orders = [];
		$statusCounts = getStatusTypes();
		$calendardata = [];

		$orderList = array("ALL" => 0,"REQUEST" => 0,"PENDING" => 0,"ACCEPTED" => 0,"ASSIGNED" => 0,"COMPLETED" => 0);

		$orders['ALL'] = WorkOrder::select('id', 'current_status', 'service_id', 'ticket_no', 'demand_type', 'start_date', 'due_date')->get();

		$backgroundColor = array("REQUEST" => "#dca900", "PENDING" => "#ffb418", "ACCEPTED" => "#1596ab", "ASSIGNED"=>"#4e0071", "COMPLETED" => "#00713b", "REQUEST_REJECTION"=> "#9c0000" );

		foreach($orders['ALL'] as $order){
			if(!isset($orderList[$order->current_status])){
				$orderList[$order->current_status] = 0;
			}
			$orderList['ALL']+=1;
			$orderList[$order->current_status]+= 1;

			$service = Service::find($order->service_id);

			$calendardata[] = [
				"id"=>$order->id,
				"title"=> $order->ticket_no." [".$order->demand_type."] - ".$service->name."",
				"start"=>$order->start_date,
				"end"=>$order->due_date,
				"backgroundColor"=> $backgroundColor[$order->current_status],
				"borderColor"=> $backgroundColor[$order->current_status],
				"textColor"=> "#fff",
				"url"=> route('workorder-view', ["id"=>$order->id])
			];
		}
		$orders = $orderList;

		unset($orders['']);

		// return json_encode($calendardata);

		return view('layouts.workorder.workorders.index', compact('orders', 'calendardata', 'wo_type'));
	}

	public function show($id=0){
		$workorder = WorkOrder::find($id);

		$is_new_mo = !isset($workorder->id) ? "yes" : "no";

		// return $is_new_mo;

		$clients = InventoryDepartment::where('module', 'organizational')->orderBy('name', 'asc')->get();
		$services = Service::orderBy('name', 'asc')->get();

		return view('layouts.workorder.workorders.show', compact('workorder', 'is_new_mo', 'clients', 'services'));
	}

	public function assign_job_card($id){
		$resources = WorkOrderResource::where('workorder_id', $id)->get();
		$workorder = WorkOrder::find($id);

		$contacts = [];

		foreach($resources as $r){
			if($r->email){
				$contacts[] = $r->email;
			}

			$contacts = array_unique($contacts);
		}

		// return json_encode($contacts);


		$companyDetails = getCompanyDetails();

		$body = 'Hi,<br><br>
			Please find the job card in the link below: <br>
			<a href="'.route('view-job-card', ['id'=>$id]).'">'.route('view-job-card', ['id'=>$id]).'</a>
			<br>Regards,<br>
			'.$companyDetails['name'];

		$mailData = array(
			'contacts' => $contacts,
			'body' => $body,
			'subject' => '[Workorder Notification] Job Card for Workorder - '.$workorder->ticket_no
		);

		$mailer = new Mailer;
		$sendMail = $mailer->html_email($mailData, 'default');


		if($workorder->current_status != "ASSIGNED"){
			$workorder->current_status = "ASSIGNED";
			$this->change_workorder_status($id, "ASSIGNED");
		}

		$workorder->save();
		return redirect()->back()->with('success', 'Work Order status changed to assigned and job cards sent out.');
	}

	public function wo_completion($id){
		$workorder = WorkOrder::find($id);
		$creator = \App\User::find($workorder->created_by_id);
	}

	public function change_workorder_status($id, $status, $comment=false){
		$history = new WorkOrderStatusHistory;
		$history->workorder_id = $id;
		$history->created_by = \Auth::user()->name;
		$history->created_by_id = \Auth::user()->id;

		if($comment){
			$history->comments = $comment;
		}

		$history->status = $status;
		$history->save();

		$workorder = WorkOrder::find($id);
		$workorder->current_status = $status;
		$workorder->save();

		$body = 'Hi,<br><br>
			The workorder status has been changed to '.$status;

		if($comment){
			$body .= '. The following reason was provided for the status change:<br> <p><em>'.$comment.'</em></p>';
		}

		$companyDetails = getCompanyDetails();

		$body .= '
			Click this link to view the workorder: '.route('workorder-view', ['id'=>$id]).'
			<br>Regards,<br>
			'.$companyDetails['name'];

		$creator = \App\User::find($workorder->created_by);

		$contacts = [\Auth::user()->email];

		if(isset($creator->email)){
			$contacts[] = $creator->email;
		}

		$mailData = array(
			'contacts' => $contacts,
			'body' => $body,
			'subject' => $status == "REQUEST_REJECTION" ?
				'[Workorder Status] Workorder - '.$workorder->ticket_no.' was rejected' :
				'[Workorder Status] Workorder status changed to '.$status
		);

		$mailer = new Mailer;
		$sendMail = $mailer->html_email($mailData, 'default');

		return $history;
	}

	public function update(Request $request, $id=false){
		// return response()->json($request->all(), 200);

		$workorder = WorkOrder::find($id) ?? new WorkOrder;
		$workorder->client_id = $request->main['department_id'];
		$workorder->ticket_no = getNamingConventionCode("WorkOrder", false, date('Ymd'));
		$workorder->routine = $request->main['routine'];
		$workorder->site = $request->main['topology_id'];
		$workorder->demand_type = $request->main['demand_type'];
		$workorder->priority = $request->main['priority'];
		$workorder->reminder = $request->main['reminder'];
		$workorder->external_resource_id = isset($request->main['external_resource_id']) ? $request->main['external_resource_id'] : 0;

		if(!$id){ //if new workorder
			$workorder->current_status = 'REQUEST';
			$workorder->created_by = \Auth::user()->name;
			$workorder->created_by_id = \Auth::user()->id;
		}

		$workorder->service_id = $request->main['service'];
		$workorder->start_date = $request->main['start_date'];
		$workorder->due_date = $request->main['due_date'];
		$workorder->description = $request->main['description'];
		$workorder->save();

		if(!$id){
			$this->change_workorder_status($workorder->id, 'REQUEST');
		}

		if($request->has('edit_reason')){
			foreach($request->edit_reason as $rs){
				$reason = new WorkorderEdit;
				$reason->reason = $rs;
				$reason->workorder_id = $id;
				$reason->created_by = \Auth::user()->name;
				$reason->created_by_id = \Auth::user()->id;
				$reason->save();
			}
		}

		$availableResources = [];

		foreach($request->contacts['pid'] ?? array() as $i=>$pid){
			$rContact = WorkOrderResource::find(isset($request->contacts['id']) ? $request->contacts['id'][$i] : null) ?? new WorkOrderResource;
			$rContact->name = $request->contacts['name'][$i] ?? 'n/a';
			$rContact->email = $request->contacts['email'][$i];
			$rContact->phone = $request->contacts['phone'][$i];
			$rContact->resource_id = $request->contacts['pid'][$i];
			$rContact->type = "contacts";
			$rContact->workorder_id = $workorder->id;
			$rContact->save();

			$availableResources[] = $rContact->id;
		}

		foreach($request->personnel_resources['pid'] ?? array() as $i=>$personnel){
			$rPersonnel = WorkOrderResource::find(isset($request->personnel_resources['id']) ? $request->personnel_resources['id'][$i] : null) ?? new WorkOrderResource;
			$rPersonnel->name = $request->personnel_resources['name'][$i] ?? 'n/a';
			$rPersonnel->email = $request->personnel_resources['email'][$i];
			$rPersonnel->phone = $request->personnel_resources['phone'][$i];
			$rPersonnel->resource_id = $request->personnel_resources['pid'][$i] ?? 0;
			$rPersonnel->type = "personnel";
			$rPersonnel->workorder_id = $workorder->id;
			$rPersonnel->is_external = $request->personnel_resources['pid'][$i] == "Select Personnel" ? 1 : 0;
			$rPersonnel->save();
			$availableResources[] = $rPersonnel->id;
		}

		foreach($request->inventory_resources['rid'] ?? array() as $i=>$inventory){
			$rInventory = WorkOrderResource::find(isset($request->inventory_resources['id']) ? $request->inventory_resources['id'][$i] : null) ?? new WorkOrderResource;
			$rInventory->name = $request->inventory_resources['name'][$i];
			$rInventory->quantity = $request->inventory_resources['quantity'][$i];
			$rInventory->resource_id = $request->inventory_resources['rid'][$i];
			$rInventory->type = "inventory";
			$rInventory->workorder_id = $workorder->id;
			$rInventory->save();
			$availableResources[] = $rInventory->id;
		}

		WorkOrderResource::whereNotIn('id', $availableResources)->where('workorder_id', $workorder->id)->delete();

		$attachmentArray = [];
		foreach($request->attachments['attachment_id'] ?? array() as $i=>$it){
			$attachmentExist = EntityAttachment::find($it);

			$attachment = $attachmentExist ?? new EntityAttachment;
			$attachment->type = $request->attachments['type'][$i];
			$attachment->title = $request->attachments['title'][$i];
			$attachment->description = $request->attachments['description'][$i];
			$attachment->model = 'WorkOrder';
			$attachment->model_id = $workorder->id;
			$attachment->created_by = \Auth::user()->id;


			$filed = $request->attachments['file'][$i] ?? false;

			if($filed){
				$path = $filed->path();

				$file = Storage::putFile('workorder', new File($path));
				$file = explode('/', $file);

				$fName = '/storage/workorder/'.urlencode(end($file));

				$attachment->file = (String) $fName;
			}

			$attachment->save();

			$attachmentArray[] = $attachment->id;
		}

		EntityAttachment::whereNotIn('id', $attachmentArray)->where('model', "WorkOrder")->where('model_id', $workorder->id)->delete();

		return redirect()->route('workorder-view', ['id'=>$workorder->id])->with('success', 'Work Order created successfully.');
	}

	public function fetch_workorders(Request $request, $status="ALL"){
		$columns = array(
			array( 'db' => 'id',  'dt' => 0),
			array( 'db' => 'ticket_no',  'dt' => 1 ),
			array( 'db' => 'department', 'dt' => 2 ),
			array( 'db' => 'site', 'dt' => 3 ),
			array( 'db' => 'service_type',  'dt' => 4 ),
			array( 'db' => 'priority',   'dt' => 5 ),
			array( 'db' => 'due_date',     'dt' => 6 ),
			array( 'db' => 'current_status',     'dt' => 7 ),
			array( 'db' => 'created_at',     'dt' => 8 ),
			array( 'db' => 'created_by',     'dt' => 9 ),
			array( 'db' => 'start_date',     'dt' => 10 )
		);

		$workorders = WorkOrder::leftJoin('inventory_departments as ind', 'ind.id', 'work_orders.client_id')
			->leftJoin('services as s', 's.id', 'work_orders.service_id')
			->selectRaw('work_orders.id, work_orders.ticket_no, work_orders.site, ind.name as department,
				work_orders.service_id, s.name as service_type, work_orders.priority, work_orders.start_date, work_orders.due_date, work_orders.current_status,
				DATE_FORMAT(work_orders.created_at, "%Y-%m-%d %H:%i") as created_at, work_orders.created_by');

		if($status!="ALL"){
			$workorders = $workorders->where('work_orders.current_status', $status);
		}

		$results = new Datatables($workorders, $request, $columns);
		$results = $results->execute();

		return response()->json($results, 200);
	}

	public function send_personnel_message(Request $request, $id){

		if(!isset($request->email)){
			return redirect()->back()->with('error', 'No personnel were selected!');
		}

		// return response()->json($request->all(), 200);

		$companyDetails = getCompanyDetails();

		$contacts = $request->email;
		$workorder = WorkOrder::find($id);
		$message = $request->message;

		$body = 'Hi,<br><br>
			The message below was left by user.'.\Auth::user()->name.' from the workorder '.$workorder->ticket_no.':<br>
			<p><em>'.$message.'</em></p><br>
			Click this link to view the workorder: '.route('workorder-view', ['id'=>$id]).'
			<br>Regards,<br>
			'.$companyDetails['name'];

		$mailData = array(
			'contacts' => $contacts,
			'body' => $body,
			'subject' => '[Workorder Notification] Message sent from Workorder - '.$workorder->ticket_no
		);

		$mailer = new Mailer;
		$sendMail = $mailer->html_email($mailData, 'default');

		return redirect()->back()->with('success', 'Message sent to personnel.');
	}

	public function approve_wo_request(Request $request, $id, $action){
		if($action == 'request_approval'){
			$companyDetails = getCompanyDetails();

			$contacts = $request->email;
			$workorder = WorkOrder::find($id);


			$users = getInventoryWorkflowUsers('department_head', $workorder->client_id);
			$departmentalHead = $users
				->pluck('email')
				->filter()
				->values()
				->toArray();

			if(count($departmentalHead) == 0){
				return redirect()->back()->with('error', 'No departmental head found.');
			}

			// return json_encode($departmentalHead);

			$body = 'Hi,<br><br>
				A new workorder has been created in your department and requires your approval.<br>
				Click this link to view the workorder: '.route('workorder-view', ['id'=>$id]).'
				<br>Regards,<br>
				'.$companyDetails['name'];

			$mailData = array(
				'contacts' => $departmentalHead,
				'body' => $body,
				'subject' => '[Workorder Notification] Workorder approval request - '.$workorder->ticket_no
			);

			$mailer = new Mailer;
			$sendMail = $mailer->html_email($mailData, 'default');

			$workorder->approval_started = 1;
			$workorder->save();

			return redirect()->back()->with('success', 'Work Order approval request sent.');
		}

		if($action == "approve"){
			$workorder = WorkOrder::find($id);
			$this->change_workorder_status($id, $workorder->current_status == "REQUEST" ? 'PENDING' : 'ACCEPTED');
		}
		else{
			$this->change_workorder_status($id, 'REQUEST_REJECTION', $request->reason);
		}

		return redirect()->back()->with('success', 'The workorder request has been approved.');
	}

	public function services(){
		$services = Service::orderBy('name')->get();

		return view('layouts.workorder.services.index', compact('services'));

	}

	public function update_service(Request $request, $id=false){
		$service = $id ? Service::find($id) : new Service;
		$service->name = $request->name;
		$service->save();

		return redirect()->back()->with('success', $id ? 'Service updated.' : 'Service added.');
	}

	public function workorder_resources($id){
		$resources = WorkOrderResource::where('workorder_id', $id)->get();

		$data = [];

		foreach($resources as $r){
			if(!isset($data[$r->type])){
				$data[$r->type] = [];
			}

			$data[$r->type][] = [
				"name"=>$r->name,
				"email"=>$r->email ?? 0,
				"quantity"=>$r->quantity ?? 0
			];
		}

		return response()->json($data, 200);
	}
}
