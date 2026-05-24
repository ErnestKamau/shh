<?php

namespace App\Http\Controllers\SkillsMatrix;
use Auth;
use App\Country;
use App\User;
use App\Supplier;
use App\SampleHeader;
use App\Models\SkillsMatrix\SkillsMatrixConfig;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\Models\SkillsMatrix\SkillsMatrixConfiguration;
use App\Models\SkillsMatrix\SkillsMatrixRoleRequirment;
use App\Models\SkillsMatrix\SkillsMatrixUserRoleRequirment;
use App\Models\SkillsMatrix\SkillsMatrixTrainingComment;
use App\Models\Training\SkillsMatrixTrainingNeed;
use App\ModulePreConfigs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class SkillsMatrixConfigController extends Controller
{

    public function __construct(){
    $this->middleware('auth');
	$this->middleware('can:skills-matrix.components.matrix-configuration.view')->only(['index', 'getTopologies', 'competence_history', 'get_phase_comments', 'get_weeks_listing']);
	$this->middleware('can:skills-matrix.components.matrix-configuration.add')->only(['add', 'save_new_week']);
	$this->middleware('can:skills-matrix.components.matrix-configuration.edit')->only(['updat_user_role_matrix_Config', 'updat_matrix_Config', 'assign_phase_comments', 'update_trainner', 'assign_trainner']);
	$this->middleware('can:skills-matrix.components.matrix-configuration.delete')->only(['remove']);
	}

	public function index($id,Request $request){
		
		$module = "Matrix Configuration";
				
		$matrix_data = SkillsMatrix::where('skillsmatrices.id', $id)->withDepartmentName()->get();
		foreach($matrix_data as $aType){			
			$matrix_info = $aType;
		}
		$current_dt = date('Y-m-d');
		$show_competence_history = false;	
		$competence_history_date ="";	
		if(isset($request->competence_history_date) && !empty($request->competence_history_date) && $current_dt!=$request->competence_history_date){
			$show_competence_history = true;
			$competence_history_date = $request->competence_history_date;	
			$current_dt = $request->competence_history_date;		
		}				
		$role_ids = explode(',',$matrix_info->matrix_role_ids);
		// Selected Matrix Role Collections 
		$roles = ModulePreConfigs::whereIn('id', $role_ids)->selectRaw('id,description')->orderBy('level', 'asc')->get();		
		// Get all competence areas collection
		$competence_areas = ModulePreConfigs::where('type', 'Competence')->selectRaw('id,description')->orderBy('level', 'asc')->get();
		// Get all competence type collection
		$competence_types = ModulePreConfigs::where('type', 'Competence Type')->selectRaw('id,description')->orderBy('level', 'asc')->get();
		// Get all competence description collection
		$competence_description = ModulePreConfigs::where('type', 'Competence Description')->selectRaw('id,description')->orderBy('level', 'asc')->get();
		// Get all matrix Configuration
		$topologies = SkillsMatrixConfiguration::leftJoin('skills_matrix_configurations as t', 't.parent', 'skills_matrix_configurations.id')
			 ->where('skills_matrix_configurations.skills_matrix_id', $id)
			 ->where('skills_matrix_configurations.active', 1)
			->selectRaw('skills_matrix_configurations.id, skills_matrix_configurations.name, skills_matrix_configurations.level, skills_matrix_configurations.parent, count(t.id) as cNo')
			->groupBy('skills_matrix_configurations.id', 'skills_matrix_configurations.name', 'skills_matrix_configurations.level', 'skills_matrix_configurations.parent')->get();

		// Get all matrix role Configuration
		$role_topologies = SkillsMatrixConfiguration::leftJoin('skills_matrix_configurations as t', 't.parent', 'skills_matrix_configurations.id')
			->where('skills_matrix_configurations.skills_matrix_id', $id)
			->where('skills_matrix_configurations.active', 1)
		   ->selectRaw('skills_matrix_configurations.id,skills_matrix_configurations.skills_matrix_id, skills_matrix_configurations.name,skills_matrix_configurations.level,skills_matrix_configurations.competence_area_id')
		   ->groupBy('skills_matrix_configurations.id', 'skills_matrix_configurations.name', 'skills_matrix_configurations.level', 'skills_matrix_configurations.parent')->get();
   
		// Get all Proficiency	
		$proficiency = ModulePreConfigs::where('type', 'Proficiency')->where('module','Skills-Matrix')
			->where('inventory_location_id', getCurrentUserLocation()->id)->selectRaw('id,color,code,description')->orderBy('level', 'asc')->get();
		//dd($proficiency);

		// Get all Proficiency	
		$traning_need_proficiency = ModulePreConfigs::where('type', 'Training')->where('module','Skills-Matrix')
			->where('inventory_location_id', getCurrentUserLocation()->id)->selectRaw('id,color,description')->orderBy('level', 'asc')->get();
		
		$user_id = (int) Auth::id();
		$can_edit_skills_matrix = Auth::user()->can('skills-matrix.components.matrix-configuration.edit') ? 1 : 0;

		$training_type_phase = getConfigByName('skills_training_phase')->first();
		$training_phases= isset($training_type_phase->id) ? explode(',',$training_type_phase->value) : [];

		// Get all matrix role user 
		$users = User::join('inventory_departments as d', 'd.id', '=', 'users.department_id')			
			->leftJoin('module_pre_configs as p', function($join){
				$tp = "Job Description";
				$join->on('p.id', '=', 'users.position');
				$join->where('p.type', '=', $tp);
			})
			->join('skillsmatrices as sm', function($join) use($role_ids,$id,$user_id,$can_edit_skills_matrix){				
				$tp = "position";
				$join->on('sm.department_id', '=', 'users.department_id');
				$join->where('sm.id', '=', $id);
				$join->where('users.active',1);	
				$join->whereIn('users.position',$role_ids);
				if($can_edit_skills_matrix==0){
					$join->where('users.id',$user_id);
				}
				
			})
			->selectRaw('users.id,users.name, d.name as department_name, p.name as position_name,users.position')
			->orderBy('p.level', 'asc')
			->where('users.company_id', getUserCompany())->get();	
		
		$topologies = SkillsMatrixConfiguration::leftJoin('skills_matrix_configurations as t', 't.parent', 'skills_matrix_configurations.id')
			->where('skills_matrix_configurations.skills_matrix_id', $id)
			->where('skills_matrix_configurations.active', 1)
		   ->selectRaw('skills_matrix_configurations.id, skills_matrix_configurations.name, skills_matrix_configurations.level, skills_matrix_configurations.parent, count(t.id) as cNo')
		   ->groupBy('skills_matrix_configurations.id', 'skills_matrix_configurations.name', 'skills_matrix_configurations.level', 'skills_matrix_configurations.parent')->get();	
		
		$users_role_topologies = SkillsMatrixConfiguration::leftJoin('skills_matrix_configurations as t', 't.parent', 'skills_matrix_configurations.id')
		   ->leftJoin('skills_matrix_user_role_requirments as de', function($join){
			   $join->on('de.skills_matrix_config_id', '=', 'skills_matrix_configurations.skills_matrix_id');			
		   })
		   ->leftJoin('module_pre_configs as mc', function($join){
			   $join->on('mc.id', '=', 'de.color_code');			
		   })
		   ->where('skills_matrix_configurations.skills_matrix_id', $id)
		   ->where('skills_matrix_configurations.active', 1)
		  ->selectRaw('skills_matrix_configurations.id, skills_matrix_configurations.name, skills_matrix_configurations.level, skills_matrix_configurations.parent, count(t.id) as cNo,de.user_id,de.color_code,mc.color')
		  ->groupBy('skills_matrix_configurations.id', 'skills_matrix_configurations.name', 'skills_matrix_configurations.level', 'skills_matrix_configurations.parent')->get();
		
		$users_training_need_topologies = SkillsMatrixConfiguration::leftJoin('skills_matrix_configurations as t', 't.parent', 'skills_matrix_configurations.id')
		  ->leftJoin('skills_matrix_training_needs as de', function($join){
			  $join->on('de.skills_matrix_config_id', '=', 'skills_matrix_configurations.skills_matrix_id');			
		  })
		  ->leftJoin('module_pre_configs as mc', function($join){
			  $join->on('mc.id', '=', 'de.color_code');			
		  })
		  ->where('skills_matrix_configurations.skills_matrix_id', $id)
		  ->where('skills_matrix_configurations.active', 1)
		 ->selectRaw('skills_matrix_configurations.*, count(t.id) as cNo,de.user_id,de.color_code,mc.color')
		 ->groupBy('skills_matrix_configurations.id', 'skills_matrix_configurations.name', 'skills_matrix_configurations.level', 'skills_matrix_configurations.parent')->get();	  
		   
		return view('layouts.skillsmatrix.matrixconfig.index', compact('topologies','matrix_info','module','competence_areas','competence_types','competence_description','role_topologies','roles','proficiency','traning_need_proficiency','users','users_role_topologies','can_edit_skills_matrix','current_dt','competence_history_date','users_training_need_topologies','training_phases'));
	}

	public function getTopologies($parent=0,$matrix_id){
	
		$matrix_topology = SkillsMatrixConfiguration::leftJoin('skills_matrix_configurations as t', 't.parent', 'skills_matrix_configurations.id')
			->where('skills_matrix_configurations.skills_matrix_id', $matrix_id)
			->where('skills_matrix_configurations.active', 1)
			->selectRaw('skills_matrix_configurations.id, skills_matrix_configurations.name, skills_matrix_configurations.competence_area_id,skills_matrix_configurations.competence_type_id, skills_matrix_configurations.level, skills_matrix_configurations.parent, count(t.id) as cNo')->where('skills_matrix_configurations.parent', $parent)
			->groupBy('skills_matrix_configurations.id', 'skills_matrix_configurations.name', 'skills_matrix_configurations.level', 'skills_matrix_configurations.parent')
			->get();

		return json_encode($matrix_topology);
	}

	public function competence_history(Request $request){

		$skills_matrix_id = $request->matrix_id;
		$skills_matrix_config_id = $request->user_role_topology;
		$user_id =$request->user_id;

		$matrix_info = SkillsMatrix::find($skills_matrix_id)->first();  
				
		$role_values =  SkillsMatrixUserRoleRequirment::leftJoin('skills_matrix_configurations as c', 'c.id', '=', 'skills_matrix_user_role_requirments.skills_matrix_config_id')
		  ->leftJoin('module_pre_configs as a', 'a.id', '=','skills_matrix_user_role_requirments.color_code')
		  ->where('skills_matrix_user_role_requirments.skills_matrix_config_id', $skills_matrix_config_id)
		  ->where('skills_matrix_user_role_requirments.skills_matrix_id', $skills_matrix_id)		
		  ->where('skills_matrix_user_role_requirments.user_id', $user_id)
		  ->where('skills_matrix_user_role_requirments.active', 1)
		  ->selectRaw('skills_matrix_user_role_requirments.id as role_auto_id,a.name,role_id,color,skills_matrix_user_role_requirments.created_at')
		  ->orderBy('version_id', 'asc')
		  ->get();

		foreach($role_values as $role_info){
			$competence_update_time[] = date("F j, Y, g:i a",strtotime( $role_info['created_at']));
			$competence_role_name[] = $role_info['name'];
			$role_colors[] = $role_info['color'];
		}
		array_unshift($competence_update_time,"");
		array_unshift($competence_role_name,"");
		array_unshift($role_colors,"");

		$proficiency_type = ModulePreConfigs::where('type', 'Proficiency')
		->where('inventory_location_id', getCurrentUserLocation()->id)->selectRaw('name')->orderBy('level', 'asc')->get();	
		
		$arr_proficiency = array();
		foreach($proficiency_type as $proficiency){
			$arr_proficiency[] = $proficiency->name;
		}
		array_unshift($arr_proficiency,"");				
		$response = array();
		$response = array('competence_update_time'=>$competence_update_time,'competence_role_name'=>$competence_role_name,'role_colors'=>$role_colors,'arr_proficiency'=>$arr_proficiency);
		return response()->json($response, 200);		
	}

	public function updat_user_role_matrix_Config(Request $request){

		$user_current_role = SkillsMatrixUserRoleRequirment::where('user_id', $request->user_id)->orderBy('version_id', 'desc')->where('active', 1)->first(); 		
		$have_different_role = true;
		if($user_current_role && $user_current_role->skills_matrix_config_id == $request->role_auto_id && $user_current_role->skills_matrix_id == $request->skills_matrix_id && 
		  $user_current_role->user_id == $request->user_id && $user_current_role->role_id == $request->role_id &&  $user_current_role->color_code == $request->color_code){
			$have_different_role = false;
		}
		$maxVersionLevel = SkillsMatrixUserRoleRequirment::where('skills_matrix_config_id', $request->role_auto_id)->where('skills_matrix_id', $request->skills_matrix_id)
		->where('user_id', $request->user_id)->where('active', 1)->max('version_id');	
		$maxVersionLevel = $maxVersionLevel+1 ?? 1;
		$change_training_need = false;
		$show_sucess_message = false;
		if($have_different_role){
			$user_role_info = new SkillsMatrixUserRoleRequirment;
			$user_role_info->skills_matrix_config_id = $request->role_auto_id;
			$user_role_info->skills_matrix_id = $request->skills_matrix_id;	
			$user_role_info->user_id = $request->user_id;	
			$user_role_info->version_id = $maxVersionLevel;	
			$user_role_info->role_name = $request->role_name;
			$user_role_info->role_id = $request->role_id;		
			$user_role_info->color_code = $request->color_code;	
			$user_role_info->code = $request->code;	
			$user_role_info->save();

			$skill_role_info = SkillsMatrixRoleRequirment::where('skills_matrix_config_id', $request->role_auto_id)->where('skills_matrix_id', $request->skills_matrix_id)->where('role_id', $request->role_id)->where('active', 1)->first(); 
			$training_require_config = ModulePreConfigs::where('type','Training')->selectRaw('id,code')->orderBy('code', 'asc')->first();	
			$training_no_require_config = ModulePreConfigs::where('type','Training')->selectRaw('id,code')->orderBy('code', 'desc')->first();	
		
			$maxVersionLevel = SkillsMatrixTrainingNeed::where('skills_matrix_config_id', $request->role_auto_id)->where('skills_matrix_id', $request->skills_matrix_id)
				->where('user_id', $request->user_id)->where('role_id', $request->role_id)->where('active', 1)->max('version_id');	
			$maxVersionLevel = $maxVersionLevel+1 ?? 1;	

			$training_need_config = new SkillsMatrixTrainingNeed;	
			$training_need_config->skills_matrix_config_id = $request->role_auto_id;
			$training_need_config->skills_matrix_id = $request->skills_matrix_id;
			$training_need_config->user_id =$request->user_id;
			$training_need_config->version_id = $maxVersionLevel;		
			$training_need_config->role_id = $request->role_id;
			$training_need_config->role_name = $request->role_name;

			if($request->code < $skill_role_info->code) {				
				$training_need_config->color_code = $training_require_config->id;
				$training_need_config->code = $training_require_config->code;
				$training_need_config->save();
				$change_training_need = true;
				$show_sucess_message = true;
			}else{			

				$version_count = SkillsMatrixTrainingNeed::where('skills_matrix_config_id', $request->role_auto_id)->where('skills_matrix_id', $request->skills_matrix_id)
				->where('user_id', $request->user_id)->where('role_id', $request->role_id)->where('active', 1)->count();
				
				if($request->code==$skill_role_info->code && $version_count==1){
					$training_need_config->color_code = $training_no_require_config->id;
					$training_need_config->code = $training_no_require_config->code;
					$training_need_config->save();
					$change_training_need = true;	
					$show_sucess_message = false;
				}else
				if($request->code==$skill_role_info->code && $version_count>1){
					$training_need_config->color_code = $training_no_require_config->id;
					$training_need_config->code = $training_no_require_config->code;
					$training_need_config->save();
					$change_training_need = true;	
					$show_sucess_message = true;
				}					
			}
		}	
				
		$error = "";
		$sucess_msg = "";
		if(!$have_different_role){
			$error  = "Error : User have the same role.";
		}
		if($change_training_need && $show_sucess_message){		
			$sucess_msg = 'Training Needs Configuration updated successfully.';
		}		
		$response = array();
		$response = array('color'=>$request->color,'description_id'=>$request->role_auto_id."_".$request->user_id,'error_description'=>$error,'sucess_msg'=>$sucess_msg);
		return response()->json($response, 200);
	}

	public function updat_matrix_Config(Request $request){
		$role_info = SkillsMatrixRoleRequirment::where('id', $request->role_auto_id)->where('role_id', $request->role_id)->where('active',1)->first(); 
		$role_info->color_code = $request->color_id;	
		$role_info->code = $request->code;		
		$role_info->save();			
		$response = array();
		$response = array('color'=>$request->color,'description_id'=>$request->role_auto_id."_".$request->role_id);
		return response()->json($response, 200);
	}

	public function add(Request $request,$parent=0,$matrix_id){
		$add_default_role = false;
		if(!empty($request->competence_area_id) && !empty($request->competence_type_id) && !empty($request->competence_description_id)){
			$module_id = $request->competence_description_id;
			$add_default_role = true;
		}else
		if(!empty($request->competence_area_id) && !empty($request->competence_type_id) && empty($request->competence_description_id)){
			$module_id = $request->competence_type_id;
		}else
		if(!empty($request->competence_area_id) && empty($request->competence_type_id) && empty($request->competence_description_id)){
			$module_id = $request->competence_area_id;
		}	
		$level_name = ModulePreConfigs::find($module_id);  
		$matrix_topology = new SkillsMatrixConfiguration;
		$matrix_topology->name = $level_name->name;
		$matrix_topology->competence_area_id = $request->competence_area_id;
		$matrix_topology->competence_type_id = $request->competence_type_id;
		$matrix_topology->competence_description_id = $request->competence_description_id;
		$matrix_topology->skills_matrix_id = $matrix_id;
		if($parent > 0){
			$matrix_parent_Topology = SkillsMatrixConfiguration::find($parent);
			$matrix_topology->parent = $matrix_parent_Topology->id;
			$matrix_topology->level = $matrix_parent_Topology->level + 1;
		}
		$matrix_topology->save();
		if($add_default_role){
			$matrix_info = SkillsMatrix::find($matrix_id);    
			$role_ids = explode(',',$matrix_info->matrix_role_ids);
			// Selected Matrix Role Collections 
			$roles = ModulePreConfigs::whereIn('id', $role_ids)->selectRaw('id,description')->orderBy('level', 'asc')->get();
			// Get all matrix role user 
			$users = User::join('inventory_departments as d', 'd.id', '=', 'users.department_id')			
			->leftJoin('module_pre_configs as p', function($join){
				$tp = "Job Description";
				$join->on('p.id', '=', 'users.position');
				$join->where('p.type', '=', $tp);
			})
			->join('skillsmatrices as sm', function($join) use($role_ids,$matrix_id){				
				$tp = "position";
				$join->on('sm.department_id', '=', 'users.department_id');
				$join->where('sm.id', '=', $matrix_id);
				$join->where('users.active',1);	
				$join->whereIn('users.position',$role_ids);	
				
			})
			->selectRaw('users.id,users.name')
			->orderBy('p.level', 'asc')
			->where('users.company_id', getUserCompany())->get();	
		
			foreach($roles as $role){		
				$matrix_role_config = new SkillsMatrixRoleRequirment;	
				$matrix_role_config->skills_matrix_config_id = $matrix_topology->id;
				$matrix_role_config->skills_matrix_id =$matrix_id;
				$matrix_role_config->color_code = $request->colorcode;		
				$matrix_role_config->role_id = $role->id;
				$matrix_role_config->role_name = $role->description;
				$matrix_role_config->save();			
			}	
			$default_color_code = ModulePreConfigs::where('type','Training')->where('color','#ffffff')->first();
			foreach($users as $user){
				foreach($roles as $role){
					$maxVersionLevel = SkillsMatrixTrainingNeed::where('skills_matrix_config_id', $role->id)->where('skills_matrix_id', $matrix_id)
					->where('user_id', $user->id)->where('active', 1)->max('version_id');				
					$maxVersionLevel = $maxVersionLevel+1 ?? 1;
					$training_need_config = new SkillsMatrixTrainingNeed;	
					$training_need_config->skills_matrix_config_id = $matrix_topology->id;
					$training_need_config->skills_matrix_id =$matrix_id;
					$training_need_config->user_id =$user->id;
					$training_need_config->version_id = $maxVersionLevel;		
					$training_need_config->role_id = $role->id;
					$training_need_config->role_name = $role->description;
					$training_need_config->color_code = $default_color_code->id;
					$training_need_config->code = $default_color_code->code;
					$training_need_config->save();
				}	
			}			
		}
		return redirect()->back()->with('success', 'Matrix Configuration added successfully.');
	}

	public function remove(Request $request, $id,$matrix_id){		
	
		$matrix_topology = SkillsMatrixConfiguration::find($id);
		$hasChildren = SkillsMatrixConfiguration::where('parent', $id)->get()->count();
		if($hasChildren > 0){
			return redirect()->back()->with('error', 'Can not remove Matrix Configuration with children. Delete children Matrix Configuration items first.');
		}	
		$matrix_topology->active = 0;
		$matrix_topology->save();
		$matrix_role_topology = SkillsMatrixRoleRequirment::where('skills_matrix_config_id', $id)->where('skills_matrix_id', $matrix_id)->where('active', 1)->get();		
		foreach ($matrix_role_topology as $res) {		
			$res->active = 0;
			$res->save();
		}
		
		$training_need_topology = SkillsMatrixTrainingNeed::where('skills_matrix_config_id', $id)->where('skills_matrix_id', $matrix_id)->where('active', 1)->get();		
		foreach ($training_need_topology as $res) {		
			$res->active = 0;
			$res->save();
		}

		$matrix_user_role_topology = SkillsMatrixUserRoleRequirment::where('skills_matrix_config_id', $id)->where('skills_matrix_id', $matrix_id)->where('active', 1)->get();		
		foreach ($matrix_user_role_topology as $res) {		
			$res->active = 0;
			$res->save();
		}

		return redirect()->back()->with('success', 'Matrix Configuration edited successfully.');
	}

	public function get_phase_comments(Request $request){

 	   	$training_type_phase = getConfigByName('skills_training_phase')->first();
	    $training_phases= explode(',',$training_type_phase->value);
		$training_phase_html = '';
		$selected = '';
		$training_phase_html.="<option value='-1'>Select Training Phase</option>";		
		$selected = '';
		foreach($training_phases as $training_phase){
			if($request->training_phase==$training_phase) {
				$selected = 'selected';
			}
			$training_phase_html.="<option ".$selected." value='".$training_phase."'>".$training_phase."</option>";  
			$selected = '';
		}		
	    $previous_comments = getSkillsTrainingPreviousComments($request->skills_matrix_auto_id,$request->matrix_id);  
	    $comments_html = '';
		$i=1;
		if($previous_comments){
			$comments_html.="<h6>Previous Comments.</h6><div class='form-control'>";
			foreach($previous_comments as $previous_comment){
				$comments_html.="<p>".$i.".<span>".html_entity_decode($previous_comment->comment, ENT_QUOTES, 'UTF-8')."</span></p>";
				$i++;  
			}
			$comments_html.="<div class='cl'></div></div>";
		}
		if(sizeof($previous_comments)==0){
			$comments_html = "No Previous Comments";
		}		
		$response = array();
		$response = array('comments_html'=>$comments_html,'training_phase_html'=>$training_phase_html);
		return response()->json($response, 200);
	}

	public function assign_phase_comments(Request $request){
		$is_phase_update = 0;
		$matrix_info = SkillsMatrix::find($request->matrix_id);
		$training_info = SkillsMatrixConfiguration::find($request->skills_matrix_auto_id);	
		if($training_info->training_phase!= $request->training_phase){
			$training_info->training_phase=$request->training_phase;
			$training_info->save();
			$is_phase_update = 1;
		}		
		$week_text = "";
		if(!empty($training_info->week_number)){											
			$week_info_status = getStartAndEndDate($request->week_number,$matrix_info->training_year);											
			$week_text =  $week_info_status['dates'][0]." to ".$week_info_status['dates'][1]; 			
		}	
		$comment =(!empty($request->comment) && strlen($request->comment)>0) ? "<b>Comment - </b>".$request->comment." <b>Training Phase - </b>".$training_info->training_phase." <b>Training Week - </b>".$week_text." <b> Date Added - </b>".date('M j', strtotime(date("Y/m/d")))   : "<b>Training Phase - </b>".$training_info->training_phase." <b>Training Week - </b>".$week_text." <b> Date Added - </b>".date('M j', strtotime(date("Y/m/d"))); 
		$training_comment = new SkillsMatrixTrainingComment;	
		$training_comment->skills_matrix_config_id = $request->skills_matrix_auto_id;
		$training_comment->skills_matrix_id = $request->matrix_id;
		$training_comment->comment =$comment;				
		$training_comment->save();
		$response = array();
		$response = array('is_phase_update'=>$is_phase_update);	  
		return response()->json($response, 200);	
	}

	public function update_trainner(Request $request){
		$trainer_info = explode('__',$request->trainer_id);
		$trainer_id = $trainer_info[0];
		$trainer_name = $trainer_info[1];
		$training_type = $request->training_type;
		$training_mode = $request->training_mode;
		$skills_matrix_auto_id = $request->skills_matrix_auto_id;
		$training_info = SkillsMatrixConfiguration::find($skills_matrix_auto_id);     
		$training_info->training_type =$training_type;	
		$training_info->training_mode =$training_mode;	
		$training_info->trainer_id =$trainer_id;	
		$training_info->save();		
		$training_type_mode = "";
		$new_trainer = "";
		if(!empty($trainer_id)){	
			$training_type_mode =  "Type - ".$training_type." / Mode - ".$training_mode; 
			$new_trainer = $request->skills_matrix_auto_id."__".$request->training_type."__".$request->training_mode."__".$trainer_id;
		}	
		$response = array();
		$response = array('msg'=>"Trainer Updated Sucessfully",'training_type_mode'=>$training_type_mode,'trainer_name'=>$trainer_name,'new_trainer_name'=>$new_trainer);
	  	return response()->json($response, 200);		
	}	

	public function assign_trainner(Request $request) {

		$training_info = SkillsMatrixConfiguration::find($request->skills_matrix_auto_id);		
		$training_set_type = $training_info->training_type;
		$training_set_mode = $training_info->training_mode;
		$trainer_set_id = $training_info->trainer_id;		
		$training_type_info = getConfigByName('training_type')->first();
		$training_types = explode(',',$training_type_info->value);
		$training_mode_info = getConfigByName('tranning_mode')->first();
		$training_modes = explode(',',$training_mode_info->value);
		 
		$users = User::where('active', '1')->selectRaw('id,name')->orderBy('name', 'asc')->get();		  
		$suppliers = Supplier::where('active', '1')->selectRaw('id,name')->orderBy('name', 'asc')->get();

		$training_type_html = '';
		$selected = '';
		$training_type_html.="<option value='-1'>Select Training Type</option>";		
		$selected = '';
		foreach($training_types as $training_type){
			if($training_set_type==$training_type) {
				$selected = 'selected';
			}
			$training_type_html.="<option ".$selected." value='".$training_type."'>".$training_type."</option>";  
			$selected = '';
		}			
		$training_mode_html = '';
		$selected = '';
		$training_mode_html.="<option value='-1'>Select Training Mode</option>";		
		$selected = '';
		foreach($training_modes as $training_mode){
			if($training_set_mode==$training_mode) {
				$selected = 'selected';
			}
			$training_mode_html.="<option ".$selected." value='".$training_mode."'>".$training_mode."</option>";  
			$selected = '';
		}
		if($training_set_type=='Inhouse'){
			$trainers = $users;
		}else{
			$trainers = $suppliers;
		}		
		$trainers_html = '';
		$selected = '';
		$trainers_html.="<option value='-1'>Select Trainer</option>";		
		$selected = '';
		foreach($trainers as $trainer){
			if($trainer_set_id==$trainer->id) {
				$selected = 'selected';
			}
			$trainers_html.="<option ".$selected." value='".$trainer->id."__".$trainer->name."'>".$trainer->name."</option>";  
			$selected = '';
		}
		$users_html = '';
		$selected = '';
		$users_html.="<option value='-1'>Select Trainer</option>";		
		$selected = '';
		foreach($users as $user){
			if($trainer_set_id==$user->id) {
				$selected = 'selected';
			}
			$users_html.="<option ".$selected." value='".$user->id."__".$user->name."'>".$user->name."</option>";  
			$selected = '';
		}
		$supplier_html = '';
		$selected = '';
		$supplier_html.="<option value='-1'>Select Trainer</option>";		
		$selected = '';
		foreach($suppliers as $supplier){
			if($trainer_set_id==$supplier->id) {
				$selected = 'selected';
			}
			$supplier_html.="<option ".$selected." value='".$supplier->id."__".$supplier->name."'>".$supplier->name."</option>";  
			$selected = '';
		}	
		$response = array();
		$response = array('training_type_option_html'=>$training_type_html,'training_mode_option_html'=>$training_mode_html,'trainers_html'=>$trainers_html,'users_html'=>$users_html,'supplier_html'=>$supplier_html);
		return response()->json($response, 200);	
	} 

	public function save_new_week(Request $request){
		$role_info = SkillsMatrixConfiguration::where('id', $request->skills_matrix_auto_id)->first(); 
		$role_info->week_number = $request->new_week_id;		
		$role_info->save();

		$matrix_info = SkillsMatrix::find($role_info->skills_matrix_id)->first();    
		$week_text = "";	
		if($request->new_week_id>0){
			$week_info = getStartAndEndDate($request->new_week_id,$matrix_info->training_year);				
			$week_text =  $week_info['dates'][0]." to ".$week_info['dates'][1];		
		}		
		$response = array();
		$response = array('msg'=>"Week Updated Sucessfully",'title'=>$week_text);
	   return response()->json($response, 200);		
	}

	public function get_weeks_listing(Request $request){
		$current_week_number = $request->week_number;
		$skills_matrix_auto_id = $request->skills_matrix_auto_id;		
		$week_select = '';
		$selected = '';
		$week_select.="<select id='".$request->skills_matrix_auto_id."' name='new_week_number' class='form-control analyte' original='".$current_week_number."' style='width:75% !important;float:left;height:26px !important;padding-left: 0px;'>";		
		$week_select.="<option value='-1'>Select Week</option>";		
		$selected = '';
		for($week_id=1;$week_id<53;$week_id++){
			if($week_id==$current_week_number) {
				$selected = 'selected';
			}
			$week_select.="<option ".$selected." value='".$week_id."'>".$week_id."</option>";  
			$selected = '';
		}				
		$week_select.="</select>";			
		$week_select.="<div style='float:right;width:16% !important;margin-left:5px; text-align:left;padding-top:2px;line-height:12px;'>
		<a style='cursor: pointer; margin: 0px 0px 0px 3px;display:block;line-height:5px;' title='Save' 
		id='save_analyte__$skills_matrix_auto_id' onclick='save_new_week(".$skills_matrix_auto_id.",".$current_week_number.");'
		><i class='fa fa-check text-success' aria-hidden='true' style='font-size: 11px;'></i></a>
		<a style='cursor: pointer; margin: 0px 0px 0px 3px;line-height:5px;'  id='cancel_show_week__$skills_matrix_auto_id' onclick='show_original_week(".$skills_matrix_auto_id.",".$current_week_number.");' data-skills_matrix_auto_id=".$skills_matrix_auto_id." title='Cancel'>
		<i class='fa fa-undo' aria-hidden='true' style='font-size: 11px;color:#ef5c5c'></i></a></div>";
		$response = array();
		$response = array('weeks_html'=>$week_select);
		return response()->json($response, 200);		
	}
 
}
