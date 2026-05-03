<?php

namespace App\Http\Controllers\SkillsMatrix;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use Auth;
use App\Country;
use App\User;
use App\SampleHeader;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\Models\SkillsMatrix\SkillsMatrixConfiguration;
use App\Models\SkillsMatrix\SkillsMatrixRoleRequirment;
use App\Models\SkillsMatrix\SkillsMatrixUserRoleRequirment;
use App\Models\Training\SkillsMatrixTrainingNeed;

use App\ModulePreConfigs;
use App\InventoryDepartment;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\QuotationHeader;

class SkillsMatrixController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('can:skills-matrix.components.skills-matrix.view')->only(['index', 'show']);
		$this->middleware('can:skills-matrix.components.skills-matrix.add')->only(['add', 'createSkillsMatrix']);
		$this->middleware('can:skills-matrix.components.skills-matrix.edit')->only(['edit', 'editMatrixdetailRole']);
		$this->middleware('can:skills-matrix.components.skills-matrix.delete')->only(['deleteMatrixDetail']);
	}

	public function checkConfig()
	{
		return redirect()->back()->with('error', 'Kindly set the Account Settings configuration');
	}
	public function index()
	{
		$module = 'Skills Matrix';
		$matrix_info = SkillsMatrix::leftJoin('inventory_departments as c', 'c.id', '=', 'skillsmatrices.department_id')->selectRaw('skillsmatrices.*, c.name as department')->get();
		// return response()->json($matrix_info);
		$user = Auth::user();
		$module_text = "organizational";
		$departments = InventoryDepartment::where('company_id', getUserCompany())
			->where('module', $module_text)
			->where('location_id', getCurrentUserLocation()->id)->selectRaw('inventory_departments.id,name')
			->orderBy('name', 'asc')->get();


		$roles_info = ModulePreConfigs::where('type', 'Job Description')->selectRaw('id,description')->get();

		$can_edit_skills_matrix = auth()->user()->can('skills-matrix.components.skills-matrix.edit') ? 1 : 0;
		return view('layouts.skillsmatrix.index', compact('matrix_info', 'module', 'departments', 'roles_info', 'can_edit_skills_matrix'));
	}

	public function add(Request $request)
	{

		$matrix_info = new SkillsMatrix;
		$matrix_info->name = $request->name;
		$matrix_info->department_id = $request->department_id;	
		$matrix_info->status = $request->status ?? 0;
		$matrix_info->save();
		$insert_arr = [];
		foreach ($request->matrix_role_ids as $a_role) {
			$insert_arr[] = [
				"skills_matrix_id" => $matrix_info->id,
				"job_description_id" => $a_role
			];
		}
		SkillMarixRole::insert($insert_arr);

		return redirect()->back()->with('success', 'Matrix information added  successfully.');
	}

	public function edit(Request $request)
	{
		// return response()->json($request->all());
		$matrix_info = SkillsMatrix::find($request->matrix_id);
		$exist_roles = $matrix_info->jobdescription['ids'];
		$update_roles = $request->matrix_role_ids;
		$add_roles = array_diff($update_roles, $exist_roles);
		$delete_roles = array_diff($exist_roles, $update_roles);
		$matrix_info->name = $request->name;
		$matrix_info->department_id = $request->department_id;
		$matrix_info->status = $request->status ?? 0;
		$matrix_info->save();

		SkillMarixRole::where('skills_matrix_id', $matrix_info->id)->whereIn('job_description_id', $delete_roles)->delete();
		$insert_arr = [];
		foreach ($add_roles as $a_role) {
			$insert_arr[] = [
				"skills_matrix_id" => $matrix_info->id,
				"job_description_id" => $a_role
			];
		}
		SkillMarixRole::insert($insert_arr);
		return redirect()->back()->with('success', 'Matrix information edited successfully.');
	}

	public function show($id){
		$matrix = SkillsMatrix::where('skillsmatrices.id',$id)->leftJoin('inventory_departments as c', 'c.id', '=', 'skillsmatrices.department_id')->selectRaw('skillsmatrices.*, c.name as department')->first();
		$roles = SkillMarixRole::with('jobdescription')->where('skills_matrix_id',$id)->orderBy('job_description_id','ASC')->get();
		$module = 'Skills Matrix - Show';
		$skills_proficiency =  ModulePreConfigs::where('type', 'Proficiency')->where('module','Skills-Matrix')
		->where('inventory_location_id', getCurrentUserLocation()->id)->selectRaw('id,color,code,description')->orderBy('level', 'asc')->get();
		// Get all competence areas collection
		$competence_areas = ModulePreConfigs::where('type', 'Competence')->selectRaw('id,description')->orderBy('level', 'asc')->get();
		// Get all competence type collection
		$competence_types = ModulePreConfigs::where('type', 'Competence Type')->selectRaw('id,description')->orderBy('level', 'asc')->get();
		// Get all competence description collection
		$competence_description = ModulePreConfigs::where('type', 'Competence Description')->selectRaw('id,description')->orderBy('level', 'asc')->get();

		$details = SkillMatrixDetails::with(['competencyarea','competencytype','competencydescription','roles.role'])->where('skill_matrix_id',$id)->get();
		// return response()->json(['details'=>$details]);
		return view('layouts.skillsmatrix.show', compact('matrix','roles','skills_proficiency','module','competence_areas','competence_types','competence_description','details'));
	}
	public function createSkillsMatrix(Request $request){
		// return response()->json($request->all());
		$loop = 0;
		$roles = SkillMarixRole::with('jobdescription')->where('skills_matrix_id',$request->matrix_id)->orderBy('job_description_id','ASC')->get();
		$role_proficiency = [];
		foreach($request->competency_description as $competency_d){
			$matrix = isset($request->detail_id[$loop]) && $request->detail_id[$loop] > 0 ? SkillMatrixDetails::find($request->detail_id[$loop]) : new SkillMatrixDetails();
			$matrix->competency_description_id = $competency_d;
			$matrix->competency_type_id = $request->competency_id[$loop];
			$matrix->competency_area_id = $request->area_id[$loop];
			$matrix->skill_matrix_id = $request->matrix_id;
			$matrix->save();
			foreach($roles as $role){
				$check_exist = SkillMatrixDetailRole::where('matrix_detail_id',$matrix->id)->where('role_id',$role->job_description_id)->first();
				if(isset($check_exist->id)){
					$updatearr = [
						"matrix_role_id" => $role->id,
						"proficiency_id"=>$request->role[strval($role->id)][$loop]
					];
					SkillMatrixDetailRole::where('matrix_detail_id',$matrix->id)->where('role_id',$role->job_description_id)->update($updatearr);
				}else{
					$role_proficiency[] = [
						"matrix_detail_id" => $matrix->id,
						"role_id" => $role->job_description_id,
						"matrix_role_id" => $role->id,
						"proficiency_id"=>$request->role[strval($role->id)][$loop]
					];
				}
			}

			++$loop;
		}
		if(sizeof($role_proficiency) > 0){
			SkillMatrixDetailRole::insert($role_proficiency);
		}

		return redirect()->back()->with('success','Skills-Matrix Details saved successfully');
	}

	public function deleteMatrixDetail(Request $request){
		$entity = $request->entity;
		if($entity == 'area'){
			$detail_ids = SkillMatrixDetails::where('skill_matrix_id',$request->matrix_id)->where('competency_area_id',$request->entity_id)->pluck('id')->toArray();
			SkillMatrixDetailRole::whereIn('matrix_detail_id',$detail_ids)->delete();
			SkillMatrixDetails::whereIn('id',$detail_ids)->delete();
		}
		if($entity == 'type'){
			$detail_ids = SkillMatrixDetails::where('skill_matrix_id',$request->matrix_id)->where('competency_type_id',$request->entity_id)->pluck('id')->toArray();
			SkillMatrixDetailRole::whereIn('matrix_detail_id',$detail_ids)->delete();
			SkillMatrixDetails::whereIn('id',$detail_ids)->delete();
		}
		if($entity == 'description'){
			SkillMatrixDetailRole::where('matrix_detail_id',$request->entity_id)->delete();
			SkillMatrixDetails::whereIn('id',$detail_ids)->delete();
		}
		// return response()->json(['success'=>"Competencies deleted successfully!"]);
		return redirect()->back()->with('success','Competency record(s) deleted successfully!');
	}

	public function editMatrixdetailRole(Request $request){
		$detail_role = SkillMatrixDetailRole::find($request->matrix_detail_role_id)->update(['proficiency_id'=>$request->proficiency_id]);
		return redirect()->back()->with('success','Matrix Proficiency edited successfully!');
	}

	

}
