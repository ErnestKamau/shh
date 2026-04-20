<?php

namespace App\Http\Controllers\CRM\Dashboard;

use App\Country;
use App\SampleHeader;
use App\SampleType;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerCertification;
use App\Models\Lab\Qualification;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\CustomerFeedback;

use App\Http\Controllers\Controller;
use App\Models\CRM\SamplePoint;
use App\SampleDetails;
use DateTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request){
       
        $samples = SampleHeader::where('crm_customer_id',auth()->user()->client_id)->join('sample_details','sample_details.sample_header_id','=','sample_headers.id')->selectRaw('sample_headers.*,sample_details.id as sample_detail_id')->get();

        $sample_batches = SampleHeader::where('crm_customer_id',auth()->user()->client_id)->get();
        // return response()->json($samples,200);
        $samples_submitted = SampleHeader::where('crm_customer_id',auth()->user()->client_id)->join('sample_details','sample_details.sample_header_id','=','sample_headers.id')->get()->count();
        
        // $samples_lab = getSampleLab(auth()->user()->client_id);
        $samples_lab = SampleHeader::where('isactive',1)->whereNotIn('status',['Completed','Finished Sample'])->get()->count();
        $samples_type_arr = SampleType::all();
        $sample_types = SampleType::all()->count();
        $names = array();
        $ids = array();
        $total = array();
        $samples_complete = SampleHeader::where('status','Completed')->where('crm_customer_id',auth()->user()->client_id)->join('sample_details','sample_details.sample_header_id','=','sample_headers.id')->get();
        
        

        $samples_type = array();
        foreach($samples_type_arr as $type){
            $sample_header = SampleHeader::where('sample_type_id',$type->id)->where('crm_customer_id',auth()->user()->client_id)->get();
            if(sizeof($sample_header)> 0){
                array_push($names,$type->name);
                array_push($ids,$type->id);
            }
        }
        $months = array();
        $gps = array();
        $crm_unit_name = array();
        if($request->has('sample_type') ){
            $year = $request->sample_type;
            foreach($ids as $x){
                $sample = SampleHeader::where('sample_type_id',$x)->where('crm_customer_id',auth()->user()->client_id)->get();
                $number = 0;
                foreach($sample as $item){
                    $date = strtotime($item->created_at);
                    $yearcreated = date('Y',$date);
                    if($yearcreated == $year){
                        ++$number;
                    }
                }
                $total[$x] = $number;
            }
            // return response()->json($total, 200);
        }elseif($request->has('month') ){
            $year = $request->month;
            $crm_unit_name =array();
            $months = array();
            foreach($samples as $sample){
                $date = strtotime($sample->created_at);
                $month = date('F',$date);
                $dateyear = date('Y',$date);
                array_push($crm_unit_name,$sample->unit_name);
                if($year == $dateyear){

                    array_push($months,$month);
                }
                
                
                // $sample_detail = SampleDetails::where('sample_header_id',$sample->id)->get();
                
                // // return response()->json(sizeof($sample_detail) , 200);
                // // array_push($gps,$sample_detail);
               
                // foreach($sample_detail as $detail){
    
                //     $sample_point = SamplePoint::find($detail->sample_point_id);
                //     array_push($gps,$sample_point->gps);
                // }
                
    
            }
            
            // return response()->json($year, 200);

        }elseif($request->has('unit')){
            $year = $request->unit;
            $months =array();
            $crm_unit_name =array();
            foreach($samples as $sample){
                $date = strtotime($sample->created_at);
                $month = date('F',$date);
                $dateyear = date('Y',$date);
                array_push($months,$month);
                if($year == $dateyear){
                    array_push($crm_unit_name,$sample->unit_name);

                }
                
                
                // $sample_detail = SampleDetails::where('sample_header_id',$sample->id)->get();
                
                // // return response()->json(sizeof($sample_detail) , 200);
                // // array_push($gps,$sample_detail);
               
                // foreach($sample_detail as $detail){
    
                //     $sample_point = SamplePoint::find($detail->sample_point_id);
                //     array_push($gps,$sample_point->gps);
                // }
                
    
            }
            // return response()->json($year, 200);

        }else{

            foreach($ids as $x){
                
        
                $sample = SampleHeader::where('sample_type_id',$x)->where('crm_customer_id',auth()->user()->client_id)->join('sample_details','sample_details.sample_header_id','=','sample_headers.id')->count();
                $total[$x] = $sample;
            }
            // return response()->json($crm_unit_name,200);
            foreach($samples as $sample){
                $date = strtotime($sample->created_at);
                $month = date('F',$date);
                array_push($months,$month);
                array_push($crm_unit_name,$sample->unit_name);
                
                
                $sample_detail = SampleDetails::find($sample->sample_detail_id);
                
                // return response()->json($sample->sample_detail_id, 200);
                // array_push($gps,$sample_detail);
                
                $sample_point = SamplePoint::find($sample_detail->sample_point_id);
                array_push($gps,$sample_point->gps);
                
                
            }
        }
        // foreach($samples as $sample){
        //     $date = strtotime($sample->created_at);
        //     $month = date('F',$date);
        //     array_push($months,$month);
        //     array_push($crm_unit_name,$sample->crm_unit_name);
            
            
        //     $sample_detail = SampleDetails::where('sample_header_id',$sample->id)->get();
            
        //     // return response()->json(sizeof($sample_detail) , 200);
        //     // array_push($gps,$sample_detail);
           
        //     foreach($sample_detail as $detail){

        //         $sample_point = SamplePoint::find($detail->sample_point_id);
        //         array_push($gps,$sample_point->gps);
        //     }
            

        // }
        $res = array('January'=>0,'February'=>0,'March'=>0,'April'=>0,'May'=>0,'June'=>0,'July'=>0,'August'=>0,'September'=>0,'October'=>0,'November'=>0,'December'=>0);
        foreach($months as $m){
            ++$res[$m];
            
        }
        
        $gps_count = array_count_values($gps);
        $unit_name = array_count_values($crm_unit_name);

        $id= auth()->user()->client_id;
        $customer = CRMCustomer::find($id);
        $complaint = Complaint::where('received_from',$customer->name)->get();

        // return response()->json($complaint->count(), 200);
        // return response()->json($gps,200);
    
        return view('layouts.crm.dashboard.index',compact('samples','samples_lab','names','ids','total','res','gps_count','unit_name','samples_complete','complaint','samples_submitted', 'sample_batches'));
    }

    public function show(){
        $id= auth()->user()->client_id;
        $customer = CRMCustomer::find($id);

        $countries = Country::orderBy('name')->get();
        $certifications = CustomerCertification::where('customer_id',$customer->id)->get();
        $qualification_list = Qualification::all();
        $complaints = Complaint::where('client_id',$customer->id)->orderBy('id','desc')->get();
        $feedbacks = CustomerFeedback::where('client_id',$customer->id)->orderBy('id','desc')->get();
        $complaint_types = Complaint_Type::all();
        $samplesSel = SampleHeader::join('sample_types as st', 'st.id', 'sample_type_id')
            ->selectRaw('sample_headers.*, st.name as sample_type')->where('crm_customer_id', $id)
            ->whereIn('sample_headers.status', ["Completed"])
            ->get();

        $ordersSel = SampleHeader::leftJoin('sample_details as sd', 'sample_headers.id', '=', 'sd.sample_header_id')
            ->join('sample_types as st', 'st.id', 'sample_headers.sample_type_id')
            ->selectRaw('sample_headers.id, sample_headers.batch_code, sample_headers.date_collected, sample_headers.reference_number, sample_headers.document_number, sample_headers.status, count(sd.id) as samples, st.name as sample_type')->where('crm_customer_id', $id)
            ->groupBy('sample_headers.id','sample_headers.batch_code', 'sample_headers.date_collected', 'sample_headers.reference_number', 'sample_headers.document_number', 'sample_headers.status', 'st.name')
            ->whereNotIn('status', ["Completed"])
            ->orderBy('sample_headers.id','desc')->get();

        $samples = array();

        foreach($samplesSel as $s){
            $reason_ids = explode(",", $s->reason_for_submission);

            $reasons = \App\RequestType::whereIn('id', $reason_ids)->get()->pluck('name')->toArray();

            $s->reasons = implode(", ", $reasons);

            $samples[] = $s;
            }

        // return response()->json($samples,200);

        return view('layouts.crm.dashboard.show', compact('customer', 'countries', 'samples','complaints','feedbacks','complaint_types','ordersSel','certifications','qualification_list'));
    }

}
