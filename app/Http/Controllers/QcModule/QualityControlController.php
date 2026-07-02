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
use App\SamplesCategory;
use App\SampleType;
use App\StandardAnalytes;
use App\Standards;
use App\AnalysisType;
use App\Models\QcModule\Configurations\Approvers;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\QcModule\QCProcessedResults;




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
        $filter_data = [
            "start_date" => '',
            "end_date" => '',
            "analysis_type_id" =>  '',
            'sample_type_id' =>  '',
            'qc_type_id'=>  '',
            'qc_scheme_id' =>  '',
            'analyte_id' =>  '',
            'group_by' =>  '',
            'remark' =>  '',
            'standard_id' =>  '',
        ];
        $selected_qc_type = [];

		return view('layouts.qcmodule.qchistory.index', compact('sample_types', 'qc_types','qc_schemes','status','analytes','data','results','release_ids','filter_data','selected_qc_type'));
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
        $status = "Qc Approved";

        $results = QCResultsView::query();
        $results = isset($request->start_date) && $request->start_date != '' ? $results->where('receipt_date','>=',$request->start_date) : $results;
        $results = isset($request->end_date) && $request->end_date != '' ? $results->where('receipt_date','<=',$request->end_date) : $results;

        $results = isset($request->sample_type_id) && $request->sample_type_id !='' ? $results->where('sample_type_id',$request->sample_type_id) : $results;
        $results = isset($request->analysis_type_id) && $request->analysis_type_id !='' ? $results->where('analysis_type_id',$request->analysis_type_id) : $results;
        $results = isset($request->qc_type_id) && $request->qc_type_id !=  '' ? $results->where('qc_type_id',$request->qc_type_id) : $results;
        $results = isset($request->qc_scheme_id) && $request->qc_scheme_id != '' ? $results->where('qc_scheme_id',$request->qc_scheme_id) : $results;
        $results = isset($request->analyte_id) && $request->analyte_id != '' ? $results->where('analyte_id',$request->analyte_id) : $results;
        // $results = isset($request->)
        // return response()->json($request->all());
        // $data = $this->computeNumericResultsModule($results);
        if(intval($request->group_by) == 2){
            $results = $results->groupBy('sample_detail_id');
            // return response()->json('here1');
        }
        if(intval($request->group_by) == 3){
            $results = $results->groupBy('sample_header_id');
        }
        $results = $results->get();
        $release_ids = implode(',',$results->pluck('id')->toArray());

        $selected_qc_type = isset($request->qc_type_id) ? QcTypes::find($request->qc_type_id) : [];

        $filter_data = [
            "start_date" => $request->start_date ?? '',
            "end_date" => $request->end_date ?? '',
            "analysis_type_id" => $request->analysis_type_id ?? '',
            'sample_type_id' => $request->sample_type_id ?? '',
            'qc_type_id'=> $request->qc_type_id ?? '',
            'qc_scheme_id' => $request->qc_scheme_id ?? '',
            'analyte_id' => $request->analyte_id ?? '',
            'group_by' => $request->group_by ?? '',
            'remark' => $request->remark ?? '',
            'standard_id' => $request->standard_id ?? '',
        ];
        

        // return response()->json($request->all());

        // $results =  QCResultsView::where('analysis_type_id',$request->analysis_type_id)->where('qc_type_id',$request->qc_type_id)->where('qc_scheme_id',$request->qc_scheme_id)->get();
        return view('layouts.qcmodule.qchistory.index', compact('sample_types', 'qc_types','qc_schemes','status','results','release_ids','filter_data','selected_qc_type'));
    }

    public function getQcTypeConfigAjax(Request $request, $id){
        $qc_type = QcTypes::find($id);
        $res = [
            "data"=>$qc_type,
            "samples"=>SamplesCategory::where('sample_type_id',$request->sample_type_id)->where('workflow_stage','Completed')->selectRaw('id,sample_code')->get()
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
    public function editQcApprovers(Request $request){
        Approvers::find($request->approver_id)->update(['personnel_id'=>$request->personnel_id]);
        return redirect()->back()->with('success','Approver updated successfully!');
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

    public function showUnProcessed(){
        $results = QcResults::where('is_qc_processed',0)->get();
        return view('layouts.qcmodule.qchistory.processing', compact('results'));
    }

    public function showQcReport(){
        $results = QCProcessedResults::with(['method','analyte','sampletype','analysistype','results'])->get();
        return view('layouts.qcmodule.qchistory.reports', compact('results'));
    }
    public function showQcReportGraph($result_id){
        $results = QCProcessedResults::with(['method','analyte','sampletype','analysistype','results'])->find($result_id);
        $results['results_arr'] = $results->getresultsarr();
        $labels = [];
        $data = [];

        foreach ($results['results_arr'] as $sampleId => $result) {
            $labels[] = $sampleId;
            $data[] = number_format((float)$result, 4, '.', ''); // Convert to float, format to 4 dp
        }
        // return response()->json($results);
        return view('layouts.qcmodule.qchistory.reportshow', compact('results','labels','data'));
    }


    // ---------------------------------------statistical methods -------------------------------
    public function processResults(Request $request){
        $unProcessedAnalyteIds = QcResults::where('is_qc_processed',0)->pluck('analyte_processed_id')->toArray();
        $unprocessed = QCProcessedResults::whereIn('id',$unProcessedAnalyteIds)->get();
        foreach ($unprocessed as $up) {
            $raw_results = QcResults::where('analyte_processed_id', $up->id)
                            ->pluck('result')
                            ->filter(function ($value) {
                                // Keep only numeric values
                                return is_numeric($value);
                            })
                            ->map(function ($value) {
                                // Convert to integer
                                return (int) $value;
                            })
                            ->values() // Re-index the array
                            ->toArray();
            $statistical_results = $this->calculateRobustCV($raw_results);
            $up->robust_standard_deviation = $statistical_results['rSD'];
            $up->robust_median = $statistical_results['median'];
            $up->robust_mean = $statistical_results['mean'];
            $up->robust_cv = $statistical_results['rCV'];
            $up->robust_cv_percentage = $statistical_results['percent_rCV'];
            $up->save();

            # code...
        }
        QcResults::where('is_qc_processed',0)->update(['is_qc_processed'=>1]);
        return redirect()->back()->with('success','All qc results have been processed');

    }

    private function calculateMean(array $values): float {
        if (count($values) === 0) {
            return 0; // or throw exception if preferred
        }
    
        return array_sum($values) / count($values);
    }
    private function calculateMedian(array $values): ?float {
        $count = count($values);
        if ($count === 0) {
            return null; // No values
        }
    
        sort($values);
        $middle = (int) floor($count / 2);
    
        if ($count % 2) {
            return $values[$middle]; // Odd
        } else {
            return ($values[$middle - 1] + $values[$middle]) / 2; // Even
        }
    }
    
    private function calculateRobustSD(array $values): ?float {
        $count = count($values);
        if ($count <= 1) {
            return 0.0; // No variation with 1 or 0 values
        }
    
        $median = $this->calculateMedian($values);
    
        // Absolute deviations from median
        $deviations = array_map(fn($v) => abs($v - $median), $values);
    
        // Median absolute deviation (MAD)
        $mad = $this->calculateMedian($deviations);
    
        return $mad * 1.4826;
    }
    
    private function calculateRobustCV(array $values): ?array {
        if (count($values) === 0) {
            return null; // No values to calculate
        }
    
        $median = $this->calculateMedian($values);
    
        if ($median == 0) {
            return [
                'mean' => 0,
                'median' => $median,
                'rSD' => 0.0,
                'rCV' => null,
                'percent_rCV' => null
            ]; // Prevent division by zero
        }
    
        $rSD = $this->calculateRobustSD($values);
        $mean = $this->calculateMean($values);
        $rCV = $rSD / $median;
        $percentRCV = $rCV * 100;
    
        return [
            'mean' => $mean,
            'median' => $median,
            'rSD' => $rSD,
            'rCV' => $rCV,
            'percent_rCV' => $percentRCV
        ];
    }
    
    
}
