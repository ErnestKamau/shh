<?php

namespace App\Http\Controllers\CRM\Complaint;


use App\Models\CRM\Complaintattachment;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\Complaintnotes;
use App\Models\CRM\Complaint;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaint_Type;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request){
        $new_complaint = new Complaint();
        $config = SystemConfigurationsType::where('configuration_type','Personnel to Recieve Feedback and Complaint Notification')->first();
        
        $complaints = Complaint::all();
        $user = auth()->user();
        $complaint_total = count($complaints) + 1;
        $complaint_totalstr = strval($complaint_total);
        if(strlen($complaint_totalstr)<4){
            $diff = 4 - strlen($complaint_totalstr);
            $zero = str_repeat("0",$diff);
            $new_complaint->complaint_id = "COMP".$zero.$complaint_totalstr;
        }else{
            $new_complaint->complaint_id = "COMP".$complaint_totalstr;
        }
        $new_complaint->description = $request->description;
        $new_complaint->priority = $request->priority;
        $new_complaint->type = $request->type;
        $new_complaint->received_from = $request->received_from;
        $customer = CRMCustomer::where('name',$request->received_from)->first();
        if(isset($customer->id)){
            $new_complaint->client_id = $customer->id;
        }
        $new_complaint->registered_by = $user->name;
        $new_complaint->date  = $request->date;
        $new_complaint->complaint_workflow = 1;   

        $new_complaint->save();

        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $new_complaint->id;
        $chain_custody->action = "Create complaint";
        $chain_custody->action_taker_id = auth()->user()->id;
        $chain_custody->workflow_stage = $new_complaint->complaint_workflow;
        $chain_custody->save();
        
        if(isset($config->id)){
            $config_users = SystemConfiguration::where('configuration_type_id',$config->id)->get();
            $company = getCompanyDetails();
            foreach($config_users as $user){
                $subject = '['.$company['name'].'] Complaint Notification - '.$new_complaint->complaint_id;
                $body = 'Hi '.$user->key.', <br> We hereby inform you that there is a complaint of ID <b>'.$new_complaint->id.'<b> that needs your attention.<br>Kindly review it.<br>Regards '.$company['name'];
                notify_user($body,$user->value,$subject);
            }
        }

        return redirect()->back()->with('success','Complaint added successfully');
    }

    public function customer_add(Request $request){
        $new_complaint = new Complaint();
        $customer = CRMCustomer::find(auth()->user()->client_id);
        $complaints = Complaint::all();
        $user = auth()->user();
        $complaint_total = count($complaints) + 1;
        $complaint_totalstr = strval($complaint_total);
        if(strlen($complaint_totalstr)<4){
            $diff = 4 - strlen($complaint_totalstr);
            $zero = str_repeat("0",$diff);
            $new_complaint->complaint_id = "COMP".$zero.$complaint_totalstr;
        }else{
            $new_complaint->complaint_id = "COMP".$complaint_totalstr;
        }
        $new_complaint->description = $request->description;
        $new_complaint->priority = $request->priority;
        $new_complaint->type = $request->type;
        $new_complaint->received_from = $user->name;
        $new_complaint->registered_by = $user->name;
        $new_complaint->date  = $request->date;
        $new_complaint->complaint_workflow = 1;   
        $new_complaint->client_id = $customer->id;

        $new_complaint->save();

        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $new_complaint->id;
        $chain_custody->action = "Create complaint";
        $chain_custody->action_taker_id = auth()->user()->id;
        $chain_custody->workflow_stage = $new_complaint->complaint_workflow;
        $chain_custody->save();
        $config = SystemConfigurationsType::where('configuration_type','Personnel to Recieve Feedback and Complaint Notification')->first();
        if(isset($config->id)){
            $config_users = SystemConfiguration::where('configuration_type_id',$config->id)->get();
            $company = getCompanyDetails();
            foreach($config_users as $user){
                $subject = '['.$company['name'].'] Complaint Notification - '.$new_complaint->complaint_id;
                $body = 'Hi '.$user->key.', <br> We hereby inform you that there is a complaint of ID <b>'.$new_complaint->complaint_id.'</b> that needs your attention.<br>Kindly review it.<br>Regards '.$company['name'];
                notify_user($body,$user->value,$subject);
            }
        }
       
        return redirect()->back()->with('success','Complaint added successfully!');
    }

    public function edit(Request $request,$id){
        $complaint = Complaint::find($id);
        $user = auth()->user();
        $complaint->description = $request->description;
        $complaint->priority = $request->priority;
        $complaint->date  = $request->date;
        $complaint->type = $request->type;
        $complaint->received_from = $request->received_from;
        $complaint->edited_by = $user->name;
        
        $complaint->save();

        $new_chain_custody = new Chain_of_Custody_Complaint();
        $new_chain_custody->complaint_id = $complaint->id;
        $new_chain_custody->action = "Edit complaint";
        $new_chain_custody->action_taker_id = auth()->user()->id;
        $new_chain_custody->workflow_stage = $complaint->complaint_workflow;
        $new_chain_custody->save();

        return redirect()->back()->with('sucess','Complaint edited successfully!');

    }

    public function index($stage){
        $stages_values = getComplaintsWorkFlowValues();
        $stage_value = $stages_values[$stage];
        if($stage_value == 0){
            $complaints = getAllComplaintsOrder();
            return view('layouts.crm.complaints.all_complaints',compact('complaints'));
        }
        $complaints = getComplaintsByWorkflow($stage_value);
        $customers = CRMCustomer::where('company_id', getUserCompany())->orderBy('name')->where('active',1)->get();
        $complaint_types = Complaint_Type::all();
        
        return view('layouts.crm.complaints.open_complaint',compact('complaints','customers','stage','complaint_types'));
    }
    public function show($id){
        $complaint = Complaint::find($id);
        $complaint_workflow = getComplaintsWorkFlowValues();
        foreach($complaint_workflow as $x=>$x_value){
            if($x_value == $complaint->complaint_workflow){
                $workflow_stage = $x;
            }
        }
       
        
        return view('layouts.crm.complaints.show_complaint',compact('complaint','workflow_stage'));
    }

    public function show_all($id){
        $complaint = Complaint::find($id);
        $complaint_workflow = getComplaintsWorkFlowValues();
        foreach($complaint_workflow as $x=>$x_value){
            if($x_value == $complaint->complaint_workflow){
                $workflow_stage = $x;
            }
        }
       
        
        return view('layouts.crm.complaints.show_allcomplaints',compact('complaint','workflow_stage'));
    }

}
