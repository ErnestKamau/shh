<?php

namespace App\Http\Controllers\SkillsMatrix;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\System\SystemConfiguration;
use App\UserRole;
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

		$edit_skills_matrixs = SystemConfiguration::where('key', 'edit_skills_matrix_role_id')->first();
		$checkAddPerm = UserRole::where('user_id', auth()->user()->id)->where('role_id', $edit_skills_matrixs->value)->first();
		$can_edit_skills_matrix = isset($checkAddPerm->id) ? 1 : 0;
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
		// return response()->json(['matrix'=>$matrix,"roles"=>$roles,"skills_proficiency"=>$skills_proficiency]);
		return view('layouts.skillsmatrix.show', compact('matrix','roles','skills_proficiency','module','competence_areas','competence_types','competence_description'));
	}

}
