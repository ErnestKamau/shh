<?php

namespace Modules\QualityControl\Http\Controllers;

use App\Analyte;
use App\Models\System\SystemConfiguration;
use App\SampleDetails;
use App\SampleHeader;
use App\StandardAnalytes;
use App\Standards;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\QualityControl\Entities\Configurations\QcSchemes;
use Modules\QualityControl\Entities\Configurations\QcTypes;

class QualityControlController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('qualitycontrol::configurations.index');
    }

    public function createQcTypes(Request $request)
    {
        $qc_type = $request->qc_type_id > 0 ? QcTypes::find($request->qc_type_id) : new QcTypes();
        $qc_type->name = $request->name;
        $qc_type->code = $request->code;
        $qc_type->has_standards = isset($request->has_standards) ? 1 : 0;
        $qc_type->has_configured_samples = isset($request->has_configured_samples) ? 1 :0;
        $qc_type->is_active = isset($request->is_active) ? 1 : 0;
        $qc_type->created_by = auth()->user()->id;
        $qc_type->save();
        return redirect()->back()->with('success','Qc Type record updated successfully');
    }

    public function deleteQCTypes(Request $request){
        $qc_type = QcTypes::find($request->qc_type_id);
        $qc_type->is_active = 0;
        $qc_type->save();
        return redirect()->back()->with('success','Qc Type record updated successfully');
    }

    public function configuration_index(){
        $qc_types = QcTypes::all();
        $standards = Standards::where('is_qc_standard',1)->get();
        $qc_schemes = QcSchemes::all();
        return view('qualitycontrol::configurations.index',compact('qc_types','standards','qc_schemes'));
    }
    public function addQcStandard(Request $request){
        $standard = Standards::find($request->standard_id) ?? new Standards();
        $standard->name = $request->name;
        $standard->code = $request->code;
        $standard->is_qc_standard = 1;
        $standard->qc_type_id =  $request->qc_type_id;
        $standard->status = isset($request->is_active)  ? 1 :0;
        $standard->edited_by = auth()->user()->id;
        $standard->qc_scheme_ids = implode(',',$request->qc_scheme_ids);
        $standard->save();
        return redirect()->back()->with('success','Qc standard added successfully!');
    }
    public function deleteQcStandard(Request $request){
        $standard = Standards::find($request->standard_id);
        $standard->status = 0;
        $standard->save();
        return redirect()->back()-with('success','Qc Standard deleted successfully!');
    }
    
    public function qcStandardShow($id){
        $standard = Standards::find($id);
        $standardAnalytes = StandardAnalytes::where('standard_id',$id)->get();
        $analytes = Analyte::where('active',1)->get();
        // return response()->json('done');
        return view('qualitycontrol::configurations.show',compact('standard','standardAnalytes','analytes'));
    }

    public function addQcStandardAnalyte(Request $request){
        $analyte = StandardAnalytes::find($request->standard_analyte_id) ?? new StandardAnalytes();
        $analyte->standard_id = $request->standard_id;
        $analyte->analyte_id = $request->analyte_id;
        $analyte->absolute_tolerance = isset($request->use_absolute) ? 1 : 0;
        $analyte->tolerance_1 = $request->tolerance_1;
        $analyte->low = isset($request->use_absolute) ? $request->tolerance_1 : $request->expected_value -  $request->tolerance_1 ;

        $analyte->tolerance_2 = $request->tolerance_2;
        $analyte->high = isset($request->use_absolute) ? $request->tolerance_2 : $request->expected_value +  $request->tolerance_1 ; 

        $analyte->mean_value = $request->mean_value;
        $analyte->rel_std_dev = $request->rel_std_dev;
        $analyte->recommendations = $request->recomendation;
        $analyte->comments = $request->comment;
        $analyte->is_active = isset($request->is_active ) ? 1 : 0;
        $analyte->expected_value = $request->expected_value;
        $analyte->standard_value_id = 0;
        $analyte->standard_value_type = 'is_range';
        $analyte->save();
        return redirect()->back()->with('success','Standard analyte record updated successfully!');
    }
    public function deleteQcStandardAnalyte(Request $request){
        $analyte = StandardAnalytes::find($request->standard_analyte_id);
        $analyte->is_active = 0;
        $analyte->save();
        return redirect()->back()->with('success','Standard analyte record deleted  successfully!');
    }
    public function MaintainQcSchemes(Request $request){
        $scheme = QcSchemes::find($request->scheme_id) ?? new QcSchemes();
        $scheme->name = $request->name;
        $scheme->code = $request->code;
        $scheme->is_active = isset($request->is_active) ? 1 : 0;
        $scheme->save();
        return redirect()->back()->with('success','Qc Scheme records updated successfully!');
    }
    public function DeleteQcSchemes(Request $request){
        $scheme = QcSchemes::find($request->scheme_id);
        $scheme->delete();
        return redirect()->back()->with('success','Qc scheme deleted successfully!');
    }
    public function qcWorkflowIndex(){
		// return response()->json('test');
		$batches = SampleHeader::where('isactive', 1)->orderBy('receipt_date', 'desc')->where('status','Qc Approved')->get();
		foreach ($batches as $b) {
			$sample_codes = SampleDetails::where('sample_header_id', $b->id)->pluck('sample_code')->toArray();
			// return response()->json()
			$b['sample_codes'] = implode(',', $sample_codes);
		}
        $status = 'Qc Approved';

		return view('qualitycontrol::qchistory.index', compact('batches', 'status'));
    }
    public function qcWorkflowShow($id){
        
    }
}
