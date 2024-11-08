<?php

namespace App\Http\Controllers\SkillsMatrix;
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

    public function __construct(){
    $this->middleware('auth');
	}

	public function checkConfig(){
		return redirect()->back()->with('error','Kindly set the Account Settings configuration');
	}
	public function index()
	{
        $module='Skills Matrix';  	
		$matrix_info =  SkillsMatrix::leftJoin('inventory_departments as c', 'c.id', '=', 'skillsmatrices.department_id')->selectRaw('skillsmatrices.*, c.name as department')->get();
		$skillsmatrix_typesArr = array("inactive"=>array(), "active"=>array());

		foreach($matrix_info as $aType){			
			$active = $aType->status == "0" ? "inactive" : "active";
			$skillsmatrix_typesArr[$active][] = $aType;
		}
		$matrix_information = $skillsmatrix_typesArr;

		$user = Auth::user();
		$module_text = "organizational";
		$departments = InventoryDepartment::where('company_id', getUserCompany())
			->where('module', $module_text)
			->where('location_id', getCurrentUserLocation()->id)->selectRaw('inventory_departments.id,name')
			->orderBy('name', 'asc')->get();
		
		
		$roles_info = ModulePreConfigs::where('type', 'Job Description')->selectRaw('id,description')->get();	
		
		$edit_skills_matrixs = getConfigByName('edit_skills_matrix_role_id');
		$edit_skills_matrix_role_id = count($edit_skills_matrixs) > 0 ? $edit_skills_matrixs[0]->value : 0;

		$AppUsers = getUsersByRole($edit_skills_matrix_role_id, true);		
		$user_id = Auth::id();
		$editskillsList = [];
		foreach($AppUsers as $au){
			$editskillsList[] = $au->id;
		}
		$can_edit_skills_matrix = 0;
		if(in_array($user_id,$editskillsList)){
			$can_edit_skills_matrix = 1;
		}   
		
		return view('layouts.skillsmatrix.index', compact('matrix_information','module','departments','roles_info','can_edit_skills_matrix'));
	}

	public function add(Request $request)
	{
		if(is_array($request->matrix_role_ids)){
			$role_ids = implode(',',$request->matrix_role_ids);
		}else{
			$role_ids ='';
		}
	  $matrix_info = new SkillsMatrix;
	  $matrix_info->name = $request->name;
	  $matrix_info->department_id = $request->department_id;
	  $matrix_info->training_year =  $request->training_year;
	  $matrix_info->matrix_role_ids = $role_ids;		
	  $matrix_info->status = $request->status ?? 0;
	  $matrix_info->save();
  
	  return redirect()->back()->with('success', 'Matrix information added  successfully.');
	}

	public function edit(Request $request, $id)
	{   	
		$matrix_info = SkillsMatrix::find($id);   
		$role_ids = explode(',',$matrix_info->matrix_role_ids);
	    $remove_role_arr = array_diff($role_ids,$request->matrix_role_ids);
		$add_role_arr = array_diff($request->matrix_role_ids,$role_ids);

		if(is_array($request->matrix_role_ids)){
			$role_ids = implode(',',$request->matrix_role_ids);
		}else{
			$role_ids ='';
		}
		$matrix_info->name = $request->name;
		$matrix_info->department_id = $request->department_id;
		$matrix_info->training_year =  $request->training_year;
		$matrix_info->matrix_role_ids = $role_ids;		
		$matrix_info->status = $request->status ?? 0;
		$matrix_info->save();

		if(sizeof($remove_role_arr)){

			foreach($remove_role_arr as $role_id){

				$matrix_role_topology = SkillsMatrixRoleRequirment::where('role_id', $role_id)->where('skills_matrix_id', $id)->where('active', 1)->get();		
				foreach ($matrix_role_topology as $res) {		
					$res->active = 0;
					$res->save();
				}
				
				$training_need_topology = SkillsMatrixTrainingNeed::where('role_id', $role_id)->where('skills_matrix_id', $id)->where('active', 1)->get();		
				foreach ($training_need_topology as $res) {		
					$res->active = 0;
					$res->save();
				}

				$matrix_user_role_topology = SkillsMatrixUserRoleRequirment::where('role_id', $role_id)->where('skills_matrix_id', $id)->where('active', 1)->get();		
				foreach ($matrix_user_role_topology as $res) {		
					$res->active = 0;
					$res->save();
				}
			}
		}

		$topologies = SkillsMatrixConfiguration::leftJoin('skills_matrix_configurations as t', 't.parent', 'skills_matrix_configurations.id')
		->where('skills_matrix_configurations.skills_matrix_id', $id)
		->where('skills_matrix_configurations.active', 1)
		->where('skills_matrix_configurations.parent', 2)
	   ->selectRaw('skills_matrix_configurations.*, count(t.id) as cNo')
	   ->groupBy('skills_matrix_configurations.id', 'skills_matrix_configurations.name', 'skills_matrix_configurations.level', 'skills_matrix_configurations.parent')->get();	
		
	  
	   if(sizeof($add_role_arr)>0 && sizeof($topologies)>0){

			foreach($add_role_arr as $role_id){	 

					$roles_information = getMatrixRole($role_id);

					foreach($topologies as $skill_topologie){								
						$matrix_role_config = new SkillsMatrixRoleRequirment;	
						$matrix_role_config->skills_matrix_config_id = $skill_topologie->id;
						$matrix_role_config->skills_matrix_id =$skill_topologie->skills_matrix_id;
						$matrix_role_config->role_id = $role_id;
						$matrix_role_config->role_name =$roles_information->description;
						$matrix_role_config->save();							
					}
				
					$default_color_code = ModulePreConfigs::where('type','Training')->where('color','#ffffff')->first();
					$user_list = SkillsMatrixTrainingNeed::where('skills_matrix_id', $id)->get()->unique('user_id');					
					if($user_list){
						foreach($user_list as $user_info){
							$training_need_config = new SkillsMatrixTrainingNeed;	
							$training_need_config->skills_matrix_config_id = $user_info->skills_matrix_config_id;
							$training_need_config->skills_matrix_id =$user_info->skills_matrix_id;
							$training_need_config->user_id =$user_info->user_id;
							$training_need_config->version_id = 1;		
							$training_need_config->role_id = $role_id;
							$training_need_config->role_name = $roles_information->description;
							$training_need_config->color_code = $default_color_code->id;
							$training_need_config->code = $default_color_code->code;
							$training_need_config->save();
						}	
					}

					$users = User::join('inventory_departments as d', 'd.id', '=', 'users.department_id')			
					->leftJoin('module_pre_configs as p', function($join){
						$tp = "Job Description";
						$join->on('p.id', '=', 'users.position');
						$join->where('p.type', '=', $tp);
					})
					->join('skillsmatrices as sm', function($join) use($role_id,$id){				
						$tp = "position";
						$join->on('sm.department_id', '=', 'users.department_id');
						$join->where('sm.id', '=', $id);
						$join->where('users.active',1);	
						$join->where('users.position',$role_id);	
					})
					->selectRaw('users.id,users.name')
					->orderBy('p.level', 'asc')
					->where('users.company_id', getUserCompany())->get();		
					$matrix_role_requirment_topology = SkillsMatrixRoleRequirment::where('skills_matrix_id', $id)->where('active', 1)->get();	

					if(sizeof($matrix_role_requirment_topology)){
						if($users){
							foreach($users as $user){
								foreach($matrix_role_requirment_topology as $matrix_role){
									$training_need_config = new SkillsMatrixTrainingNeed;	
									$training_need_config->skills_matrix_config_id = $matrix_role->skills_matrix_config_id;
									$training_need_config->skills_matrix_id =$matrix_role->skills_matrix_id;
									$training_need_config->user_id =$user->id;
									$training_need_config->version_id = 1;		
									$training_need_config->role_id = $matrix_role->role_id;
									$training_need_config->role_name = $matrix_role->role_name;
									$training_need_config->color_code = $default_color_code->id;
									$training_need_config->code = $default_color_code->code;
									$training_need_config->save();
								}
							}
						}	
					}
				
			}
			
		}		

		return redirect()->back()->with('success', 'Matrix information edited successfully.');
	}
 
}
