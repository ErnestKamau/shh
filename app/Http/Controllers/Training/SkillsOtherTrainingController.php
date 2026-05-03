<?php

namespace App\Http\Controllers\Training;
use Auth;
use App\Country;
use App\User;
use App\Supplier;
use App\SampleHeader;
use App\Models\Training\SkillsOtherTraining;
use App\Models\Training\SkillsOtherTrainingParticipant;
use App\Models\Training\SkillsOtherTrainingComment;
use App\ModulePreConfigs;
use App\InventoryDepartment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class SkillsOtherTrainingController extends Controller
{

    public function __construct(){
    $this->middleware('auth');
	$this->middleware('can:skills-matrix.components.other-training.view')->only(['index']);
	$this->middleware('can:skills-matrix.components.other-training.add')->only(['add']);
	$this->middleware('can:skills-matrix.components.other-training.edit')->only(['edit', 'training_acceptance']);
	}

	public function checkConfig(){
		return redirect()->back()->with('error','Kindly set the Account Settings configuration');
	}
	public function index()
	{
		
		$module='Other Training';  	
		$training_other_info =  SkillsOtherTraining::selectRaw('skills_other_trainings.*')->get();
		
		$training_typesArr = array("inactive"=>array(), "active"=>array());

		foreach($training_other_info as $aType){			
			$active = $aType->status == "0" ? "inactive" : "active";
			$training_typesArr[$active][] = $aType;
		}
		$training_information = $training_typesArr;
		//dd($training_information);

		$user = Auth::user();
		$module_text = "organizational";
		$departments = InventoryDepartment::where('company_id', getUserCompany())
			->where('module', $module_text)
			->where('location_id', getCurrentUserLocation()->id)->selectRaw('inventory_departments.id,name')
			->orderBy('name', 'asc')->get();
		
		$can_edit_skills_matrix = Auth::user()->can('skills-matrix.components.other-training.edit') ? 1 : 0;

		$training_type_info = getConfigByName('training_type')->first();
		$training_types = explode(',',$training_type_info->value);	
		$training_mode_info = getConfigByName('tranning_mode')->first();
		$training_modes = explode(',',$training_mode_info->value);
		$training_type_phase = getConfigByName('skills_training_phase')->first();
		$training_phases= explode(',',$training_type_phase->value);		
		$users = User::where('active', '1')->selectRaw('id,name')->orderBy('name', 'asc')->get();		  
		$suppliers = Supplier::where('active', '1')->selectRaw('id,name')->orderBy('name', 'asc')->get();
		$users_html = '';		
		$users_html.="<option value=''>Select Trainer</option>";	
		foreach($users as $user){			
			$users_html.="<option value='".$user->id."'>".$user->name."</option>";  
			$selected = '';
		}
		$supplier_html = '';	
		$supplier_html.="<option value=''>Select Trainer</option>";		
		foreach($suppliers as $supplier){			
			$supplier_html.="<option value='".$supplier->id."'>".$supplier->name."</option>";  
			$selected = '';
		}

		return view('layouts.other_training.index', compact('training_information','module','departments','can_edit_skills_matrix','training_types','training_modes','users','suppliers','users_html','supplier_html','training_phases'));
	}

	public function add(Request $request){

		if(is_array($request->department_id)){
			$department_ids = implode(',',$request->department_id);
		}else{
			$department_ids ='';
		}
		$week_text = "";
		if(!empty($request->week_number)){											
			$week_info_status = getStartAndEndDate($request->week_number,$request->training_year);											
			$week_text =  $week_info_status['dates'][0]." to ".$week_info_status['dates'][1]; 			
		}
		$training_type_mode = "";
		$trainer_name = "";
		   if(!empty($request->trainer_id)){	
		   $training_type_mode =  "Type - ".$request->training_type." / Mode - ".$request->training_mode; 
		   $trainer_name = getTrainerName($request->training_type,$request->trainer_id);	
		}	  
		$comment =(!empty($request->comment) && strlen($request->comment)>0) ? "<b>Comment - </b>".$request->comment." <b>Training Phase - </b>".$request->training_phase." <b>Training Week - </b>".$week_text." <b> Date Added - </b>".date('M j', strtotime(date("Y/m/d")))   : "<b>Training Phase - </b>".$request->training_phase." <b>Training Week - </b>".$week_text." <b> Date Added - </b>".date('M j', strtotime(date("Y/m/d"))); 
		
		$other_training = new SkillsOtherTraining;
		$other_training->name = $request->name;
		$other_training->description =  $request->description;
		$other_training->department_id =  $department_ids;
		$other_training->training_year =  $request->training_year;
		$other_training->week_number =  $request->week_number;
		$other_training->training_type = $request->training_type;
		$other_training->training_mode =  $request->training_mode;
		$other_training->trainer_id =   $request->trainer_id;
		$other_training->status = $request->status ?? 0;
		$other_training->training_phase = $request->training_phase ?? Null;
		$other_training->save();	
		foreach($request->department_id as $dept_number){
			$users =  User::where('active',1)->where('department_id',$dept_number)->selectRaw('id,first_name,email')->get();
			if(count($users)>0){
				$subject = "New Training Session is organized";	
				foreach($users as $user){			
					$training_participant = new SkillsOtherTrainingParticipant;	
					$training_participant->skills_other_training_id = $other_training->id;
					$training_participant->department_id =$dept_number;
					$training_participant->participant_id = $user->id;		
					$training_participant->is_attended = 0;				
					$training_participant->save();
					$body = '
						Hi '.$user->first_name.',<br><br>
						New Training Session is organied below are the details of it.<br/>
						Name - '.$request->name.'<br/>
						Description - '.$request->description.'<br/>
						Week - '.$week_text.'<br/>
						Traning Mode - '.$training_type_mode.'<br/>
						Trainer - '.$trainer_name.'<br/>		
						Click below link to to aceept or decline the session.<br/>
						Click here for <b><a href="'.route('training-acceptance', ['training_id'=> $other_training->id,'dept_number'=> $dept_number, 'user_id'=>$user->id,'acceptance'=>1]).'">Accept</a></b>.&nbsp;OR&nbsp;<b>
						<b><a href="'.route('training-acceptance', ['training_id'=> $other_training->id, 'dept_number'=> $dept_number, 'user_id'=>$user->id,'acceptance'=>0]).'">Reject</a></b>
						<br>
						<br>Regards,<br><br>					
					';		
					//$this->notify_user($body, $user->email, $subject);*/
					$notify = notify_user($body, 'shrikanthshete@gmail.com', $subject);
				}	
			}			
		}			
		$training_comment = new SkillsOtherTrainingComment;	
		$training_comment->skills_other_training_id = $other_training->id;
		$training_comment->comment =$comment;				
		$training_comment->save();
		  
	  return redirect()->back()->with('success', 'Other Training information added  successfully.');
	}

	public function training_acceptance($training_id,$dept_number, $user_id, $acceptance=0){
		$request = SkillsOtherTrainingParticipant::where('skills_other_training_id', $training_id)->where('department_id', $dept_number)->where('participant_id', $user_id)->first() ?? new SkillsOtherTrainingParticipant;
		$request->skills_other_training_id = $training_id;
		$request->department_id = $dept_number;
		$request->participant_id = $user_id;
		$request->is_attended = $acceptance;
		$request->save();
		return redirect('other-training')->with('success', 'Training acceptance information updated successfully.');		
	}

	public function edit(Request $request, $id){   

		if(is_array($request->department_id)){
			$department_ids = implode(',',$request->department_id);
		}else{
			$department_ids ='';
		}

		$other_training = SkillsOtherTraining::find($id); 
		$old_year = $other_training->training_year;
		$old_week = $other_training->week_number;		 	
		$other_training->name = $request->name;
		$other_training->description =  $request->description;
		$other_training->department_id =  $department_ids;
		$other_training->training_year =  $request->training_year;
		$other_training->week_number =  $request->week_number;
		$other_training->training_type = $request->training_type;
		$other_training->training_mode =  $request->training_mode;
		$other_training->trainer_id =   $request->trainer_id;
		$other_training->status = $request->status ?? 0;
		$other_training->training_phase = $request->training_phase ?? Null;
		$other_training->save();	

		if($old_year!=$request->training_year || $old_week!=$request->week_number || !empty($request->comment) && strlen($request->comment)>0){
			$week_text = "";
			if(!empty($request->week_number)){											
				$week_info_status = getStartAndEndDate($request->week_number,$request->training_year);											
				$week_text =  $week_info_status['dates'][0]." to ".$week_info_status['dates'][1]; 			
			}
			$comment =(!empty($request->comment) && strlen($request->comment)>0) ? "<b>Comment - </b>".$request->comment." <b>Training Phase - </b>".$request->training_phase." <b>Training Week - </b>".$week_text." <b> Date Added - </b>".date('M j', strtotime(date("Y/m/d")))   : "<b>Training Phase - </b>".$request->training_phase." <b>Training Week - </b>".$week_text." <b> Date Added - </b>".date('M j', strtotime(date("Y/m/d"))); 
		
			$training_comment = new SkillsOtherTrainingComment;	
			$training_comment->skills_other_training_id = $other_training->id;
			$training_comment->comment =$comment;				
			$training_comment->save();
		}	
	
		return redirect()->back()->with('success', 'Training information edited successfully.');
	}
 
}
