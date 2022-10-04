<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SampleHeader;
use App\BatchAmmendment;
use App\SampleDetails;

class BatchAmmendmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function add(Request $request){
        $batch = getSampleHeaderByID($request->batch_id);
        
       
        // return response()->json($batch->batch_report_url,200);
        if(isset($batch->id)){
            $samples = json_encode($request->samples);
            $t = array();
            foreach($request->samples as $s){
                $h = getSampleDetailById($s);
                $t[$h->sample_code] = $s;
            }
            $y = json_encode($t);
            
            $new_ammendment = new BatchAmmendment();
            $new_ammendment->samples = $y;
            $new_ammendment->created_by_id = auth()->user()->id;
            $new_ammendment->reason = $request->reason;
            $new_ammendment->batch_id = $request->batch_id;
            $new_ammendment->report_url = $batch->batch_report_url;
            $new_ammendment->version_number = $batch->is_amendment + 1;
            // return response()->json($new_ammendment->version_number,200);
            $new_ammendment->save();
            $batch->is_amendment = $new_ammendment->version_number;
            $batch->status = 'Sample Verification';
            $batch->in_ammendment_proccess = 1;
            $batch->save();
            return redirect()->back()->with('success','Ammendment added successfully!');

        }else{
            return redirect()->back()->with('error','There is no batch with the specified ID!');
        }
    }
    public function edit(Request $request){
        $ammendment = BatchAmmendment::find($request->ammendment_id);
        if(isset($ammendment->id)){
            $batch = getSampleHeaderByID($ammendment->batch_id);
            $ammendment->samples = json_encode($request->samples);
            $ammendment->reason = $request->reason;
            $ammendment->report_url = $batch->batch_report_url;
            $ammendment->save();
            return redirect()->back()->with('success','Ammendment generated successfully');
        }else{
            return redirect()->back()->with('error','No ammendment with the specified ID!');
        }
    }
}
