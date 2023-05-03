<?php

namespace App\Http\Controllers\QcModule;

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

		return view('layouts.qcmodule.qchistory.index', compact('sample_types', 'qc_types','qc_schemes','status','analytes','data','results'));
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

        // $data =[
        //     "max"=>"4.1",
        //     "min"=>"4",
        //     "median"=>"4",
        //     "average"=>"4",
        //     "std"=>"0.1",
        //     "cv"=>"0.12",
        //     "population"=>"2",
        //     "z_score"=>0,
        //     "std_star"=>0,
        //     "cv_star"=>0
        // ];
        $results = QCResultsView::query();
        $results = isset($request->start_date) ? $results->where('created_at','>=',$request->start_date) : $results;
        $results = isset($request->end_date) ? $results->where('created_at','<=',$request->end_date) : $results;

        $results = isset($request->sample_type_id) && $request->sample_type_id !='' ? $results->where('sample_type_id',$request->sample_type_id) : $results;
        $results = isset($request->analysis_type_id) && $request->analysis_type_id !='' ? $results->where('analysis_type_id',$request->analysis_type_id) : $results;
        $results = isset($request->qc_type_id) && $request->qc_type_id !=  '' ? $results->where('qc_type_id',$request->qc_type_id) : $results;
        $results = isset($request->qc_scheme_id) && $request->qc_scheme_id != '' ? $results->where('qc_scheme_id',$request->qc_scheme_id) : $results;
        $results = isset($request->analyte_id) && $request->analyte_id != '' ? $results->where('analyte_id',$request->analyte_id) : $results;
        return response()->json($results->get());
        $data = $this->computeNumericResultsModule($results);
        $results = $results->get();

        // return response()->json($request->all());

        // $results =  QCResultsView::where('analysis_type_id',$request->analysis_type_id)->where('qc_type_id',$request->qc_type_id)->where('qc_scheme_id',$request->qc_scheme_id)->get();
        return view('layouts.qcmodule.qchistory.index', compact('sample_types', 'qc_types','qc_schemes','status','analytes','data','results'));
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


    // --------------------------qc compute Reports-------------------------
     /**
     * Calculate median of the valid result data.
        *@param $validResults
     */
    public function getMedian($validResults){
        $count = count($validResults);
        $isEven = $count % 2  == 0;
        if($isEven){
            $median =  ($validResults[intval($count/2)]->result + $validResults[intval($count/2)-1]->result) / 2;
        }else{
            $median = $validResults[intval($count/2)]->result;
        }
        return $median;
    }
     /**
     * Calculate Robust median of the valid result data.
        *@param $validResults
     */
    public function getMedianIndex($validResults){
        $count = count($validResults);
        $isEven = $count % 2  == 0;
        if($isEven){
            $median =  ($validResults[intval($count/2)] + $validResults[intval($count/2)-1]) / 2;
        }else{
            $median = $validResults[intval($count/2)];
        }
        return $median;
    }

    /**
     * Show the form for creating a new resource.
     * @param 
     */

    public function computeQcReport($validResults,$onlyResultsArr){
        $statisticalPopulationSize = sizeof($validResults);
        // configuraftios
        $sstar_config =  1.483 ;
        $decimal_place = 3;
        $sstar2_const = 1.134;

        // initialize all variables
		$sum = null;
		$robust_average = null;
		$robust_std_deviation = null;
		$robust_cv = null; 
		$xstar = null;
		$median_diff = null;
		$cv_star = null;
		$sd_star = null;
		$z_score = null;
		$sstar = null;
		$delta = null;
		$ysum = null;
		$sumsq = null;
		$sumx = null;
		$oldsstar = 0;
		$oldxstar = 0;

        #processing Area
        $xstar = $this->getMedian($validResults);

        $diff = [];
        $loop =0;
        foreach($onlyResultsArr as $res){
            $diff[$loop] = abs($res-$xstar);
            ++$loop;
        }
        usort($diff,function($a,$b){
            if($a == $b){
                return 0;
            }
            return $a < $b ? -1 : 1;
        });

        $median_diff = $this->getMedianIndex($diff);
        $sstar = 1.483 * $median_diff;

        // the tolerance for convergence
        $delta = pow(10,-$decimal_place);
        while((abs($xstar - $oldxstar) > 0) || (abs($sstar - $oldsstar) > $delta)){
            $phi =1.5 * $sstar;
            $loop = 0;
            foreach($onlyResultsArr as $resdif){
                if($resdif < ($xstar - $phi)){
                    $onlyResultsArr[$loop] = $xstar-$phi;
                }else{
                    $sumXstarPhi = $xstar + $phi;
                    $onlyResultsArr[$loop] = $resdif > $sumXstarPhi ? $sumXstarPhi : $resdif;
                }
                ++$loop;
            }
            $oldsstar = $sstar;
            $oldxstar = $xstar;
            $sumx = 0;
            $sumsq = 0;

            $sumx = array_sum($onlyResultsArr);
            $xstar = $sumx / sizeof($onlyResultsArr);

            foreach($onlyResultsArr as $onlyRes){
                $xdiff = $onlyRes - $xstar;
                $sumsq = $sumsq + ($xdiff * $xdiff);
            }
            $ysum = $sumsq / (sizeof($validResults) - 1);
            $sstar =  $sstar2_const  * sqrt($ysum);
        }
        $robust_average = $xstar;
        $robust_std_deviation = $sstar;
        $robust_cv = $robust_std_deviation * 100 / $robust_average;
        $compute_res = array(
            'robust_average' => $robust_average,
            'robust_median' => $xstar,
            'robust_std_deviation' => $robust_std_deviation,
            'robust_cv' => $robust_cv,
            'sstar' => $sstar,
            'statisticalPopulationSize' => $statisticalPopulationSize
        );

        return $compute_res;

    }

   private function computeNumericResultsModule($rawResults){
        
        $rawResultsClone = clone $rawResults;
        $raw_valid_results = clone $rawResults;
        $min_value = null;
        $max_value = null;
        $onlyResultsArr = $rawResultsClone->pluck('result')->toarray();
        $validResults = $raw_valid_results->get();
        $min_value = min($onlyResultsArr);
        $max_value = max($onlyResultsArr);

        if ($min_value == $max_value) {
            $robust_average = $min_value;
            $robust_std_deviation =0.0;
            $robust_cv = 0.0; 
            $statisticalPopulationSize = sizeof($onlyResultsArr) > 0  ? sizeof($onlyResultsArr) : 0;
        } else {
            $compute_res = $this->computeQcReport($validResults,$onlyResultsArr);
            $robust_average = $compute_res['robust_average'];
            $robust_cv = $compute_res['robust_cv'];
            $robust_std_deviation = $compute_res['robust_std_deviation'];
            $statisticalPopulationSize = $compute_res['statisticalPopulationSize'];
            $cvstar = $compute_res['statisticalPopulationSize'] > 0 ?  $compute_res['robust_cv'] /  $compute_res['statisticalPopulationSize'] : 0;
            $sd_star = $cvstar * ($robust_average/100);
            foreach($validResults as $vRes){
                $z_score = $sd_star > 0 ? ($vRes->result - $robust_average) / $sd_star : 0;
                $z_score = round($z_score, 2);
                $vres['zscore'] = $z_score;
            }
        }

        $data = [
            "robust_average" =>$robust_average,
            "robust_cv"=>$robust_cv,
            "robust_std_deviation" => $robust_std_deviation,
            "statisticalPopulationSize"=>$statisticalPopulationSize,
            "cvstar"=>$cv_star ?? 0,
            "sd_star" => $sd_star ?? 0,
            "valid_res"=> $validResults
        ];

        return $data;

    
   }
    
    
}
