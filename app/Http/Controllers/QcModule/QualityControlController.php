<?php

namespace App\Http\Controllers\QcModule;

use App\AnalysisElements;
use App\Analyte;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\Configurations\QcTypes;
use App\Models\QcModule\Data\QcResults;
use App\Models\System\SystemConfiguration;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\StandardAnalytes;
use App\Standards;
use App\AnalysisType;
use App\Models\QcModule\Configurations\Approvers;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\QualityControl\Entities\QCResultsView;



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
        return view('layouts.qcmodule.configurations.index');
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
        $qc_type->use_existing_sample = isset($request->use_existing_sample) ? 1 :0;
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
        $approvals = Approvers::all();
        $staffs = User::where('active',1)->where('is_support_staff',0)->get();
        return view('layouts.qcmodule.configurations.index',compact('qc_types','standards','qc_schemes','approvals','staffs'));
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
        return view('layouts.qcmodule.configurations.show',compact('standard','standardAnalytes','analytes'));
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
		$sample_types = SampleType::where('active',1)->get();
        $qc_types = QcTypes::where('is_active',1)->get();
        $qc_schemes = QcSchemes::where('is_active',1)->get();
        $analytes = Analyte::where('active',1)->get();
        $status = "Qc Approved";
        $data = [];
        $results = [];
        $release_ids = implode(',',[]);

		return view('layouts.qcmodule.qchistory.index', compact('sample_types', 'qc_types','qc_schemes','status','analytes','data','results','release_ids'));
    }
    public function getQcStandardsAjax($qc_type_id){
        $standards = Standards::where('is_qc_standard',1)->where('status',1)->where('qc_type_id',$qc_type_id)->get();
        return response()->json($standards);
    }
    public function getQcAnalysisTypesAjax($sample_type_id){
        $analysis = AnalysisType::where('sample_type_id',$sample_type_id)->get();
        return response()->json($analysis);
    }

    public function generateQCReport(Request $request){
        // return response()->json($request->all());

        $sample_types = SampleType::where('active',1)->get();
        $qc_types = QcTypes::where('is_active',1)->get();
        $qc_schemes = QcSchemes::where('is_active',1)->get();
        $analytes = Analyte::where('active',1)->get();
        $status = "Qc Approved";

        $results = QCResultsView::query();
        $results = isset($request->start_date) && $request->start_date != '' ? $results->where('created_at','>=',$request->start_date) : $results;
        $results = isset($request->end_date) && $request->end_date != '' ? $results->where('created_at','<=',$request->end_date) : $results;

        $results = isset($request->sample_type_id) && $request->sample_type_id !='' ? $results->where('sample_type_id',$request->sample_type_id) : $results;
        $results = isset($request->analysis_type_id) && $request->analysis_type_id !='' ? $results->where('analysis_type_id',$request->analysis_type_id) : $results;
        $results = isset($request->qc_type_id) && $request->qc_type_id !=  '' ? $results->where('qc_type_id',$request->qc_type_id) : $results;
        $results = isset($request->qc_scheme_id) && $request->qc_scheme_id != '' ? $results->where('qc_scheme_id',$request->qc_scheme_id) : $results;
        $results = isset($request->analyte_id) && $request->analyte_id != '' ? $results->where('analyte_id',$request->analyte_id) : $results;
        // return response()->json($results->get());
        // $data = $this->computeNumericResultsModule($results);
        $results = $results->get();
        $release_ids = implode(',',$results->pluck('id')->toArray());
        

        // return response()->json($request->all());

        // $results =  QCResultsView::where('analysis_type_id',$request->analysis_type_id)->where('qc_type_id',$request->qc_type_id)->where('qc_scheme_id',$request->qc_scheme_id)->get();
        return view('layouts.qcmodule.qchistory.index', compact('sample_types', 'qc_types','qc_schemes','status','analytes','results','release_ids'));
    }

    public function getQcTypeConfigAjax($id){
        $qc_type = QcTypes::find($id);
        $res = [
            "data"=>$qc_type,
            "samples"=>SampleDetails::selectRaw('id,sample_code')->get()
        ];
        return response()->json($res);
    }
    public function addQcApprovvers(Request $request){
        $approver = Approvers::where('personnel_id',$request->personnel_id)->first();
        if(!isset($approver->id)){
            $approver = new Approvers();
            $approver->personnel_id = $request->personnel_id;
            $approver->created_by = auth()->user()->id;
            $approver->save();
            return redirect()->back()->with('success','Approver Added Successfully');
        }
        return redirect()->back()->with('error','Approver already exists');
    }
    public function deleteQcApprovvers($id){
        $approver = Approvers::find($id);
        $approver->delete();
       
        return redirect()->back()->with('success','Approver deleted Successfully');
       
    }

    public function getAnalysisElementsByTypeId($id){
        $elements = AnalysisElements::where('analysis_type_id',$id)->get();
        return response()->json($elements);
    }


    
    
    
}
