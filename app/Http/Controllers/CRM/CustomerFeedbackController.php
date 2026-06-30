<?php

namespace App\Http\Controllers\CRM;

use App\Models\CRM\CustomerFeedback;

use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerFeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request){
        $new_feedback = new CustomerFeedback();     
        $new_feedback->feedback = $request->feedback;
        $new_feedback->received_from = $request->received_from;
        $customer = CRMCustomer::where('name',$request->received_from)->first();
        if(isset($customer->id)){
            $new_feedback->client_id = $customer->id;
        }
        $new_feedback->registered_by = auth()->user()->name;
        $new_feedback->user_type = 'Customer';
        $new_feedback->status = $request->status;
        $new_feedback->date = $request->date;
        $new_feedback->save();
        if (empty($new_feedback->code)) {
            $new_feedback->code = CustomerFeedback::generateUniqueCode();
            $new_feedback->save();
        }
        $config = SystemConfigurationsType::where('configuration_type','Personnel to Recieve Feedback and Complaint Notification')->first();
        if(isset($config->id)){
            $config_users = SystemConfiguration::where('configuration_type_id',$config->id)->get();
            $company = getCompanyDetails();
            foreach($config_users as $user){
                $subject = '['.$company['name'].'] Customer Feedback Notification - '.$new_feedback->code;
                $body = 'Hi '.$user->key.', <br> We hereby inform you that there is a Customer feedback of ID <b>'.$new_feedback->code.'</b> that needs your attention.<br>Kindly review it.<br>Regards '.$company['name'];
                notify_user($body,$user->value,$subject);
            }
        }
       
        
        return redirect()->back()->with('success','Feedback added successfully!');
    }
    public function customer_add(Request $request){
        $new_feedback = new CustomerFeedback();
        $new_feedback->feedback = $request->feedback;
        $customer = CRMCustomer::find(auth()->user()->client_id);
        $new_feedback->client_id = $customer->id;
        $new_feedback->received_from = auth()->user()->name;
        $new_feedback->registered_by = auth()->user()->name;
        $new_feedback->user_type = 'Customer';
        $new_feedback->date = $request->date;
        $new_feedback->save();
        $feeds = CustomerFeedback::all();
        if (empty($new_feedback->code)) {
            $new_feedback->code = CustomerFeedback::generateUniqueCode();
            $new_feedback->save();
        }
        $config = SystemConfigurationsType::where('configuration_type','Personnel to Recieve Feedback and Complaint Notification')->first();
        if(isset($config->id)){
            $config_users = SystemConfiguration::where('configuration_type_id',$config->id)->get();
            $company = getCompanyDetails();
            foreach($config_users as $user){
                $subject = '['.$company['name'].'] Customer Feedback Notification - '.$new_feedback->code;
                $body = 'Hi '.$user->key.', <br> We hereby inform you that there is a Customer feedback of ID <b>'.$new_feedback->code.'</b> that needs your attention.<br>Kindly review it.<br>Regards '.$company['name'];
                notify_user($body,$user->value,$subject);
            }
        }
        
        return redirect()->back()->with('success','Feedback added successfully!');
    }
    public function edit(Request $request,$id){
        $feedback = CustomerFeedback::find($id);
        if(isset($feedback->feedback)){
            $feedback->feedback = $request->feedback;
            $feedback->received_from = $request->received_from;
            $customer = CRMCustomer::where('name',$request->received_from)->first();
            if(isset($customer->id)){
                $feedback->client_id = $customer->id;
            }
            $feedback->status = $request->status;
            
            $feedback->date = $request->date;
            $feedback->edited_by = auth()->user()->name;
            $feedback->save();
            return redirect()->back()->with('success','Feedback added successfully!');
        }
    }

    public function index(){
        $customers = CRMCustomer::where('company_id', getUserCompany())->orderBy('name')->where('active',1)->get();
        $feedbacks = CustomerFeedback::orderBy('id','desc')->get();
        foreach($feedbacks as $f){
            if (empty($f->code)) {
                $f->code = CustomerFeedback::generateUniqueCode();
                $f->save();
            }
        }

        return view('layouts.crm.complaints.customer_feedback',compact('feedbacks','customers'));
    }

}
