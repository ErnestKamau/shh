<?php

namespace App\Http\Controllers\CRM;

use App\User;
use App\Models\CRM\CustomerContact;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMCustomer;

class CustomerContactController extends Controller
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
	public function add(Request $request, $cust_id)
	{
		$contact = new CustomerContact;
		$contact->first_name = $request->first_name;
		$contact->middle_name = $request->second_name;
		$contact->last_name = $request->third_name;
		$contact->job_occupation = $request->job_occupation;
		
		$contact->unit_name =  $request->unit_name == '' ? '' : implode(",", $request->unit_name);
		$contact->email = $request->email;
		$contact->telephone = $request->telephone;
		$contact->mobile = $request->mobile;
		$contact->company_id = getUserCompany();
		$contact->crm_customer_id = $cust_id;
		$contact->receive_price_list = $request->receive_price_list ?? 0;
		$contact->receive_invoice = $request->receive_invoice ?? 0;
		$contact->receive_report = $request->receive_report ?? 0;
		$contact->title_id = $request->title;
		$contact->active = $request->active ?? 0;
		
		if(isset($request->has_credential)){
			$check_user = User::where('email',$request->email)->get();
			if(isset($check_user->id)){
				return redirect()->back()->with('error','There is a user with the given email!');
			}
			$contact->can_login = 1;
			
			$user = new User();
			$user->name = $request->first_name." ".$request->middle_name." ".$request->last_name;
			$user->password = bcrypt($request->main_passwords);
			$user->email = $request->email;
			$user->company_id = getUserCompany();
			$user->is_client = 1;
			$user->client_id = $cust_id;
			
			$user->save();
			$ip_address_link = request()->root();		
			$companyDetails = getCompanyDetails();
			$message = 'We would like to welcome you to '.$companyDetails['name'].'.Please find below your Login Credentials and Link to Imara Lims:';
			$body = 'Hi '.$user->name.', <br><br>'
					.$message.'<br>
					App Link: <a href='.$ip_address_link.'>'.$ip_address_link.'</a> ,<br>
					Email: '.$user->email.' ,<br>
					Password: '.$request->main_passwords.' , <br>

					Regards, <br><br> '.$companyDetails['name'].' ';
			$subject = '['.$companyDetails['name'].'] User Credentials';

			notify_user($body,$user->email,$subject);

		}
		$contact->save();
		

	return redirect()->back()->with('success', 'Company contact added.');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id, $cust_id)
	{
		$contact = CustomerContact::find($id);
		$email = $contact->email;
		$contact->first_name = $request->first_name;
		$contact->middle_name = $request->second_name;
		$contact->last_name = $request->third_name;
		$contact->job_occupation = $request->job_occupation;
		$contact->unit_name =  $request->unit_name == '' ? '' : implode(",", $request->unit_name);
		$contact->email = $request->email;
		$contact->telephone = $request->telephone;
		$contact->mobile = $request->mobile;
		$contact->company_id = getUserCompany();
		$contact->crm_customer_id = $cust_id;
		$contact->receive_price_list = $request->receive_price_list ?? 0;
		$contact->receive_invoice = $request->receive_invoice ?? 0;
		$contact->receive_report = $request->receive_report ?? 0;
		$contact->title_id = $request->title;
		$contact->active = $request->active ?? 0;

		if(isset($request->has_credentials) && $contact->can_login == 0 ){
			$contact->can_login = 1;
			
			$user = new User();
			$user->name = $request->first_name." ".$request->middle_name." ".$request->last_name;
			$user->password = bcrypt($request->main_password);
			$user->email = $request->email;
			$user->company_id = getUserCompany();
			$user->is_client = 1;
			$user->client_id = $cust_id;
			
			$user->save();
		}elseif(isset($request->has_credentials) && $contact->can_login ==1){
			$contact->can_login = 1;
			$user = User::where('email',$email)->first();
			$user->name = $request->first_name." ".$request->middle_name." ".$request->last_name;
			$user->password = $request->main_password;
			$user->email = $request->email;
			$user->save();
		}else{
			$contact->can_login = 0;
			$user = User::where('email',$email)->first();
			if(isset($user->name)){
				$user->active = 0;
				$user->save();
			}
		}
		
		$contact->save();

		return redirect()->back()->with('success', 'Company contact added.');
	}

	public function get_contacts($type, $customer_id){
		$contact = CustomerContact::where('crm_customer_id', $customer_id);

		if($type == 'receive_report'){
			$contact = $contact->where('receive_report', 1);
		}

		return $contact->orderBy('first_name', 'asc')->get();
	}
	public function get_customer_client($name){
		$customer = CRMCustomer::find($name);
		if(isset($customer->id)){
			$contacts = CustomerContact::where('crm_customer_id',$customer->id)->get();
		}else{
			$contacts = [];
		}
		return $contacts;
	}
}
