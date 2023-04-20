<?php

namespace App\Http\Controllers\CRM;

use App\Country;
use App\User;
use App\SampleHeader;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerCertification;
use App\Models\Lab\Qualification;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\CustomerFeedback;


use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\QuotationHeader;

class CRMCustomerController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
	}

	public function checkConfig(){
		return redirect()->back()->with('error','Kindly set the Account Settings configuration');
	}
	public function index()
	{
		$customers = CRMCustomer::where('company_id', getUserCompany())->where('active',1)->orderBy('name')->get();
		$countries = Country::orderBy('name')->get();
		$account_settings = getConfigTypeByName('Account Settings');
		if(isset($account_settings->id)){
			$accounts = getconfigByID($account_settings->id);		
		}else{

			$accounts = array();
		}
		return view('layouts.crm.index', compact('customers', 'countries','accounts','account_settings'));
	}

	public function add(Request $request)
  {
		// return response()->json($request->all(), 200);
	$check = CRMCustomer::where('name',$request->name)->first();
	if(isset($check->id)){
		return redirect()->back()->with('error','Customer already exists');
	}
    $customer = new CRMCustomer;
    $customer->code = getNamingConventionCode("Customers", $request->name);
    $customer->name = $request->name;
    $customer->physical_address = $request->physical_address;
    $customer->postal_address = $request->postal_address;
    $customer->company_id = getUserCompany();
    $customer->website = $request->website == '' ? '' : $request->website;
    $customer->email = $request->email;
    $customer->fax = $request->fax;
    $customer->telephone1 = $request->phone1;
	$customer->telephone2 = $request->phone2;
	$customer->credit_days = $request->credit_day;
    $customer->country_id = $request->country_id;
	$customer->active = $request->active ?? 0;
	$customer->account_status = $request->account_id;
	$customer->vat_no = $request->vat_no;
	if(isset($request->lpos_required)){
		$customer->lpos_required = 1;
	}

    $customer->save();

    return redirect()->back()->with('success', 'Customer added.');
	}

	public function show($id){
    $customer = CRMCustomer::find($id);

	$countries = Country::orderBy('name')->get();
	$certifications = CustomerCertification::where('customer_id',$customer->id)->get();
	$qualification_list = Qualification::all();
    $complaints = Complaint::where('client_id',$customer->id)->orderBy('id','desc')->get();
    $feedbacks = CustomerFeedback::where('client_id',$customer->id)->orderBy('id','desc')->get();
	$quotes = QuotationHeader::where('crm_customer_id',$id)->get();
    $complaint_types = Complaint_Type::all();
		$samplesSel = SampleHeader::join('sample_types as st', 'st.id', 'sample_type_id')
			->selectRaw('sample_headers.*, st.name as sample_type')->where('crm_customer_id', $id)
			->where('sample_headers.status', ["Completed"])
			->orderBy('id','desc')->get();

		$ordersSel = SampleHeader::leftJoin('sample_details as sd', 'sample_headers.id', '=', 'sd.sample_header_id')
			->join('sample_types as st', 'st.id', 'sample_headers.sample_type_id')
			->selectRaw('sample_headers.id, sample_headers.batch_code, sample_headers.date_collected, sample_headers.reference_number, sample_headers.document_number, sample_headers.status, count(sd.id) as samples, st.name as sample_type')->where('crm_customer_id', $id)
			->groupBy('sample_headers.id','sample_headers.batch_code', 'sample_headers.date_collected', 'sample_headers.reference_number', 'sample_headers.document_number', 'sample_headers.status', 'st.name')
			->whereNotIn('status', ["Completed"])
			->get();

		$samples = array();

		foreach($samplesSel as $s){
			$reason_ids = explode(",", $s->reason_for_submission);

			$reasons = \App\RequestType::whereIn('id', $reason_ids)->get()->pluck('name')->toArray();

			$s->reasons = implode(", ", $reasons);

			$samples[] = $s;
		}
		$account_settings = getConfigTypeByName('Account Settings');
		if(isset($account_settings->id)){
			$accounts = getconfigByID($account_settings->id);		
		}else{

			$accounts = array();
		}

		// return response()->json($samples, 200);

		return view('layouts.crm.show', compact('customer', 'countries', 'samples','complaints','feedbacks','complaint_types','ordersSel','certifications','qualification_list','accounts','quotes'));
		
	}

  public function edit_label(Request $request, $id)
  {
		$customer = CRMCustomer::find($id);

		if($request->column == "unit_configurable_name"){
			$customer->unit_configurable_name = $request->name;
		}

		if($request->column == "sample_point_configurable_name"){
			$customer->sample_point_configurable_name = $request->name;
		}

		if($request->column == "product_configurable_name"){
			$customer->product_configurable_name = $request->name;
		}

		$customer->save();

		return redirect()->back()->with('success', 'Configuration Saved.');
	}


  public function edit(Request $request, $id)
  {
    $customer = CRMCustomer::find($id);
    $customer->name = $request->name;
    $customer->physical_address = $request->physical_address;
    $customer->postal_address = $request->postal_address;
    $customer->company_id = getUserCompany();
    $customer->website = $request->website;
    $customer->email = $request->email;
    $customer->fax = $request->fax;
    $customer->telephone1 = $request->phone1;
    $customer->telephone2 = $request->phone2;
	$customer->country_id = $request->country_id;
	$customer->credit_days = $request->credit_day;
	$customer->active = $request->active ?? 0;
	$customer->account_status = $request->account_id;
	$customer->vat_no = $request->vat_no;
	if(isset($request->lpos_required)){
		$customer->lpos_required = 1;
	}elseif(!isset($request->lpos_required) && $customer->lpos_required == 1){
		$customer->lpos_required = 0;
	}
    $customer->save();

    return redirect()->back()->with('success', 'Customer edited.');
  }

  public function add_to_users(){
	  $all_clients = CustomerContact::all();
	//   return response()->json($all_clients, 200);
	  foreach($all_clients as $client){
		  $new_user = new User();
		  $new_user->name = $client->first_name." ".$client->middle_name." ".$client->last_name;
		  $new_user->password =  bcrypt('test1234');
		  $new_user->email = $client->email;
		  $new_user->company_id = $client->company_id;
		  $new_user->is_client = 1;
		  $new_user->client_id = $client->crm_customer_id;
		  $new_user->save();
		  $client->can_login = 1;
		  $client->save();
		}
		return redirect()->route('home');
  }
  public function fetch_client_quote(Request $request){
	  $client = CRMCustomer::find($request->client_id);
	  if(isset($client->id)){
		  $quotes = QuotationHeader::where('crm_customer_id',$client->id)->get();
		  
		  return response()->json($quotes,200);
	  }else{
		  return response()->json($request->client_id,200);
	  }
  }
  public function delete_customer(Request $request){
	$customer = getCrmCustomerByID($request->customer_id);
	if(!isset($customer->id)){
		return redirect()->back()->with('error','No Crm customer with the specified ID!');
	}
	$complaints = Complaint::where('client_id',$customer->id)->get();
	$contacts = getCrmCustomerContacts($customer->id);
	$users = User::where('client_id',$customer->id)->get();
	foreach($users as $user){
		$user->active = 0;
		$user->save();
	}
	foreach($complaints as $complaint){
		$complaint->rejected = 1;
		$complaint->save();
	}
	foreach($contacts as $contact){
		$contact->active = 0;
		$contact->save();
	}
	$customer->active = 0;
	$customer->save();
	return redirect()->back()->with('success','Crm Customer deleted successfully!');


  }
  public function validateCrmCustomerNameAjax($name){
	$namearr = explode(' ',$name);
	$tit = $namearr[0];
	$customers = CRMCustomer::where('name','LIKE', "%".$tit."%")->get();
	return response()->json($customers);
  }
  
}
