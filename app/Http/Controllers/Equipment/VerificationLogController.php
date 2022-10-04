<?php

namespace App\Http\Controllers\Equipment;

use App\Models\Equipments\VerificationLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function add(Request $request,$id){
        $log = new VerificationLog;

        $log->verification_date = $request->date;
        $log->procedure = $request->procedure;
        $log->reference_standard = $request->reference;
        $log->response = $request->response;
        $log->remarks = $request->remark;
        if (isset($request->maintainance_type) && $request->maintainance_type == 'external'){
			// return response()->json($request->supplier,200);
			$log->supplier_id =  (int)$request->supplier;
			$log->maintainance_type = "external";
			
		}elseif(isset($request->maintainance_type) && $request->maintainance_type == 'in_house' ){
			$log->maintainance_type = "in-house";
			$log->operator_id  = $request->employee;
		}
        
        $log->equipment_id = $id;

        $log->save();
        return redirect()->back()->with('success', 'Verification log added!');
    }
    public function edit(Request $request,$id){
        $log = VerificationLog::find($id);

        $log->verification_date = $request->date;
        $log->procedure = $request->procedure;
        $log->reference_standard = $request->reference;
        $log->response = $request->response;
        $log->remarks = $request->remark;
        $log->operator_id = $request->operator;

        $log->save();

        return redirect()->back()->with('success', 'Verification log edited!');
    }
    public function delete($id){
        $log = VerificationLog::find($id);

        $log->is_delete = 1;
        $log->save();

        return redirect()->back()->with('success', 'Verification log deleted!');
    }
}
