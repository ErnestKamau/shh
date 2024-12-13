<?php

namespace App\Http\Controllers\SkillsMatrix;

use App\Http\Controllers\Controller;
use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\ModulePreConfigs;
use Illuminate\Http\Request;

class CapabilityController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}
    public function index(){
        $skillmatrixs = SkillsMatrix::where('status',1)->get();
        $capabilities = CapabilityMatrix::with(['grouproles.jobdescription','skillmatrix','creator'])->where('status',1)->get();
        $module = 'Skills Matrix';
        // return response()->json($capabilities);
        return view('layouts.skillsmatrix.capability.index', compact('module', 'skillmatrixs','capabilities'));
    }
    public function getSkillMatrixRolesAjax($matrix_id){
        $roles = SkillMarixRole::with(['users','jobdescription'])->where('skills_matrix_id',$matrix_id)->get();
        return response()->json(['roles'=>$roles]);
    }
    public function getMatrixUsersByPositionAjax(Request $request){
        $roles = SkillMarixRole::with(['users','jobdescription'])->whereIn('id',$request->positions)->get();
        return response()->json(['roles'=>$roles]);

    }
    public function store(Request $request){
        // return response()->json($request->all());
        $matrix = $request->matrix_id > 0 ? CapabilityMatrix::find($request->matrix_id) : new CapabilityMatrix();
        $matrix->name = $request->name;
        $matrix->matrix_id = $request->skills_matrix_id;
        $matrix->created_by = auth()->user()->id;
        $matrix->save();

        $loop = 0;
        $capability_role_insert = [];
        foreach($request->code as $code){
            if($request->capability_user_id[$loop] > 0){
                CapabilityMatrixRoles::find($request->capability_user_id[$loop])->update([
                    "capability_id"=>$matrix->id,
                    "skill_matrix_role_id"=>$request->skill_matrix_role_id[$loop],
                    "role_id"=>$request->jobdescription_id[$loop],
                    "user_id"=>$request->user_id[$loop],
                    "code"=>$request->code[$loop],
                ]);
            }else{
                $capability_role_insert [] = [
                    "capability_id"=>$matrix->id,
                    "skill_matrix_role_id"=>$request->skill_matrix_role_id[$loop],
                    "role_id"=>$request->jobdescription_id[$loop],
                    "user_id"=>$request->user_id[$loop],
                    "code"=>$request->code[$loop],
                ];
            }
            ++$loop;
        }
        sizeof($capability_role_insert) > 0 ? CapabilityMatrixRoles::insert($capability_role_insert) : '';
        CapabilityMatrixRoles::where('capability_id',$matrix->id)->whereNotIn('user_id',$request->user_id)->update(['deleted_at'=>date('Y-m-d')]);
        return redirect()->back()->with('success','Capability Matrix created successfully!');
    }
    public function show(Request $request, $id){
        $capability = CapabilityMatrix::with(['skillmatrix','creator','roles.user'])->find($id);
        
        $capability_roles = array_unique($capability->grouproles->pluck('jobdescription.name')->toArray());
        if(isset($request->role_id)){
            $selectedUsers = $request->role_id;
        }else{
            $selectedUsers = [];
        }
        $competencies  = SkillMatrixDetails::with(['competencyarea','competencytype','competencydescription','capabilityusers'])->where('skill_matrix_id',$capability->skillmatrix->id)->get();
        // return response()->json($competencies);
        $competencyUserIds = $competencies->mapWithKeys(function ($competency) {
            return [
                $competency->id => $competency->capabilityusers->map(function ($user) {
                    return [
                        'user_id' => $user->user_id,
                        'proficiency_id' => $user->proficiency_id,
                        'id'=>$user->id,
                    ];
                })->toArray(),
            ];
        });
        // return response()->json($capability->roles);
        $proficiencies = ModulePreConfigs::where('type', 'Proficiency')->where('module','Skills-Matrix')
		->where('inventory_location_id', getCurrentUserLocation()->id)->selectRaw('id,color,code,description')->orderBy('code', 'asc')->get();
        // return response()->json($capability);
        $module = 'Skills Matrix';
        return view('layouts.skillsmatrix.capability.show', compact('module', 'capability','capability_roles','selectedUsers','competencies','proficiencies','competencyUserIds'));
    }

    public function storeDetails(Request $request){
        // return response()->json($request->all());
        $loop = 0;
        foreach($request->competency_id as $competency){
            $detail = isset($request->competency_detail[$loop]) && $request->competency_detail[$loop] > 0 ? CapabilityMatrixDetail::find($request->competency_detail[$loop]) : new CapabilityMatrixDetail();
            $detail->capability_id = $request->capability_id;
            $detail->competency_id = $competency;
            $detail->user_id = $request->capability_user_id[$loop];
            $detail->proficiency_id = $request->proficiency_id[$loop];
            $detail->skill_matrix_role_id = $request->skill_matrix_role_id[$loop];
            $detail->save();
            ++$loop;
        }
        return redirect()->back()->with('success','Capability Matrix stored succesfully!');
    }

    public function editCapabaility(Request $request){
        // return response()->json($request->all());
        $matrix = CapabilityMatrix::find($request->matrix_id);
        $matrix->name = $request->name;
        $matrix->save();
        return redirect()->back()->with('success','Matrix edited successfully');
    }
    public function deleteCapabaility(Request $request){
        // return response()->json($request->all());
        CapabilityMatrix::find($request->matrix_id)->update(['deleted_at'=>date('Y-m-d')]);
        
        return redirect()->back()->with('success','Matrix deleted successfully');
    }
   

}
