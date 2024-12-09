<?php

namespace App\Http\Controllers\SkillsMatrix;

use App\Http\Controllers\Controller;
use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use App\Models\SkillsMatrix\TrainingDetail;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Models\SkillsMatrix\TrainingHeaderStaff;
use App\ModulePreConfigs;
use Illuminate\Http\Request;

class TrainingNeedsController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}
    public function index(){
        $trainings = TrainingHeader::whereNull('deleted_at')->get();
        $capabilities = CapabilityMatrix::whereNull('deleted_at')->get();
        $module = 'Skills Matrix';
        return view('layouts.skillsmatrix.trainingNeeds.index', compact('trainings','capabilities','module'));
    }
    public function store(Request $request){
        // return response()->json($request->all());
        $train_header = TrainingHeader::find($request->training_header_id) ?? new TrainingHeader();
        $train_header->name = $request->name;
        $train_header->capability_id = $request->capability_id;
        $train_header->created_by = auth()->user()->id;
        $train_header->save();
        $exist_user_ids = TrainingHeaderStaff::where('training_header_id',$train_header->id)->pluck('capability_matrix_role_id')->toArray();
        $new_user_ids = array_diff($request->user_id,$exist_user_ids);
        $insertuserarr = [];
        foreach($new_user_ids as $user_id){
            $insertuserarr[]=[
                "capability_matrix_role_id"=>$user_id,
                "training_header_id"=>$train_header->id
            ];
        }
        TrainingHeaderStaff::insert($insertuserarr);
        TrainingHeaderStaff::where('training_header_id',$train_header)->whereNotIn('capability_matrix_role_id',$request->user_id)->delete();
        $details = CapabilityMatrixDetail::with(['competency','proficiency'])->where('capability_id',$request->capability_id)->whereIn('user_id',$request->user_id)->get();
        $insert_arr = [];
        foreach($details as $detail){
            $detail['skill_proficiency'] = $detail->skillproficiency();
            $detail['requires_training'] = intval($detail->proficiency->code) < intval($detail->skillproficiency()->proficiency->code) ? 1 : 0;
            if($detail['requires_training'] == 1){
                $check_exist = TrainingDetail::where('capability_detail_id',$detail->id)->where('training_header_id',$train_header->id)->first();
                if(isset($check_exist->id)){
                    TrainingDetail::where('capability_detail_id',$detail->id)->where('training_header_id',$train_header->id)->update([
                        'training_header_id'=>$train_header->id,
                        'capability_detail_id'=>$detail->id,
                        'skill_matrix_role_proficiency_id'=>$detail->skill_proficiency->id,
                        'require_training'=>1,
                        'deleted_at'=>null
                    ]);
                }else{
                    $insert_arr[] = [
                        'training_header_id'=>$train_header->id,
                        'capability_detail_id'=>$detail->id,
                        'skill_matrix_role_proficiency_id'=>$detail->skill_proficiency->id,
                        'require_training'=>1,
                        'deleted_at'=>null
                    ];
                }
            }
        }
        TrainingDetail::insert($insert_arr);
        TrainingDetail::join('skill_capability_detail','skills_training_detail.capability_detail_id','=','skill_capability_detail.id')->where('skills_training_detail.training_header_id',$train_header->id)->whereNotIn('skill_capability_detail.user_id',$request->user_id)->update(['skills_training_detail.deleted_at'=>date('Y-m-d')]);
        return redirect()->back()->with('success','Training Needs recorded added successfully');
    }
    public function getCapabilityUsers($id){
        $filled = CapabilityMatrixDetail::where('capability_id',$id)->pluck('user_id')->toArray();
        $users =  CapabilityMatrixRoles::with('user')->whereIn('id',$filled)->where('capability_id',$id)->whereNull('deleted_at')->get();
        return response()->json($users);
    }
    public function show(Request $request,$id){
        $train_header = TrainingHeader::with(['details.capabilitydetail.competency.competencyarea','details.capabilitydetail.competency.competencytype','details.capabilitydetail.competency.competencydescription'])->find($id);
        $selectedUsers = isset($request->user_ids)  ? $request->user_ids : [];
        $proficiencies = ModulePreConfigs::where('type', 'Training')->where('module','Skills-Matrix')->where('inventory_location_id', getCurrentUserLocation()->id)->selectRaw('id,color,code,description')->orderBy('code', 'asc')->get();
        $module = 'Skills Matrix';
        return view('layouts.skillsmatrix.trainingNeeds.show', compact('train_header','selectedUsers','module','proficiencies'));
    }

}
