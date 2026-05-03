<?php

namespace App\Http\Controllers\SkillsMatrix;

use App\Http\Controllers\Controller;
use App\Models\SkillsMatrix\OtherTrainingUsers;
use App\Models\SkillsMatrix\TrainingDetail;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Models\SkillsMatrix\TrainingPlannerDetails;
use App\Models\SkillsMatrix\TrainingPlannerHeader;
use App\Models\SkillsMatrix\TrainPlanDetailView;
use App\ModulePreConfigs;
use App\User;
use Illuminate\Http\Request;

class TrainingPlanController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
        $this->middleware('can:skills-matrix.components.training-plan.view')->only(['index', 'show']);
        $this->middleware('can:skills-matrix.components.training-plan.add')->only(['store', 'storeOther']);
        $this->middleware('can:skills-matrix.components.training-plan.edit')->only(['storeDetail', 'editPlan']);
        $this->middleware('can:skills-matrix.components.training-plan.delete')->only(['deleteOtherDetail', 'deletePlan']);
	}

    public function index(){
        $needs = TrainingHeader::whereNull('deleted_at')->get();
        $plans = TrainingPlannerHeader::whereNull('deleted_at')->get();
        $module = 'Skills Matrix';
        return view('layouts.skillsmatrix.trainingPlanner.index', compact('needs','plans','module'));
    }
    public function store(Request $request){
        // return response()->json($request->all());
        $plan = isset($request->planner_id) && $request->planner_id > 0 ?  TrainingPlannerHeader::find($request->planner_id) : new TrainingPlannerHeader();
        $plan->name = $request->name;
        $plan->training_need_header_id = $request->training_need_header_id;
        $plan->created_by = auth()->user()->id;
        $plan->save();

        $details = TrainingDetail::where('training_header_id',$request->training_need_header_id)->get();
        foreach($details as $detail){
            $planDetail = TrainingPlannerDetails::where('training_plan_header_id',$plan->id)->where('training_need_detail_id',$detail->id)->first() ?? new TrainingPlannerDetails();
            $planDetail->training_plan_header_id = $plan->id;
            $planDetail->training_need_detail_id = $detail->id;
            $planDetail->save();
        }
        return redirect()->back()->with('success','Training Plan saved successfully');
    }
    public function storeOther(Request $request){
        // return response()->json($request->all());
        $other = TrainingPlannerDetails::find($request->other_id) ?? new TrainingPlannerDetails();
        if(!isset($other->id)){
            $other->training_plan_header_id = $request->plan_id;
        }
        $other->training_start_date = $request->start_date;
        $other->training_end_date = $request->end_date;
        $other->week_no = $request->week_no;
        $other->organizer_trainer = $request->trainer;
        $other->remark = $request->remark;
        $other->other_competency = $request->name;
        if(isset($request->other_id) && $request->other_id > 0){
            $other->status = $request->status;
        }
        $other->is_others = 1;
        $other->save();

        $staff= [];
        $exist_staff = OtherTrainingUsers::where('train_plan_detail_id',$other->id)->pluck('user_id')->toArray();
        $new_ids = array_diff($request->staff_ids,$exist_staff);
        foreach($new_ids as $staff_id){
            $staff[]=["train_plan_detail_id"=>$other->id,"user_id"=>$staff_id];
        }
        OtherTrainingUsers::insert($staff);
        OtherTrainingUsers::where('train_plan_detail_id',$other->id)->whereNotIn('user_id',$request->staff_ids)->delete();
        return redirect()->back()->with('success','Other Trainings added successfully!');
    }
    public function show(Request $request, $id){
        $plan  =TrainingPlannerHeader::with(['groupdetails.trainneeddetail','groupdetails.competency.competencyarea','groupdetails.competency.competencytype','groupdetails.competency.competencydescription','others.otheruser.user'])->find($id);
        // return response()->json($plan->others);
        $module = 'Skills Matrix';
        $selectedUsers = isset($rtrequest->selected_users) ? $request->selected_users : [];
        $proficiencies = ModulePreConfigs::where('type', 'Training')->where('module','Skills-Matrix')->where('inventory_location_id', getCurrentUserLocation()->id)->selectRaw('id,color,code,description')->orderBy('code', 'asc')->get();
        $staffs = User::where('active',1)->get();
        return view('layouts.skillsmatrix.trainingPlanner.show', compact('plan','module', 'selectedUsers','proficiencies','staffs'));
    }
    public function storeDetail(Request $request){
        $detail = TrainPlanDetailView::find($request->detail_id);
        $details = TrainPlanDetailView::where('competency_id',$detail->competency_id)->where('training_plan_header_id',$detail->training_plan_header_id)->pluck('id')->toArray();
        $updatearr = [
            "training_start_date"=>$request->start_date,
            "training_end_date" => $request->end_date,
            "status"=> intval($request->status),
            "week_no"=>$request->week_no,
            "organizer_trainer"=>$request->trainer,
            "remark"=>$request->remark,
        ];
        TrainingPlannerDetails::whereIn('id',$details)->update($updatearr);
        return redirect()->back()->with('success','Training Material edited successfully');


    }
    public function deleteOtherDetail(Request $request){
        // return response()->json($request->all());
        TrainingPlannerDetails::find($request->other_id)->delete();
        OtherTrainingUsers::where('train_plan_detail_id',$request->other_id)->delete();
        return redirect()->back()->with('success','Other Training record(s) deleted successfully');
    }

    public function editPlan(Request $request){
        TrainingPlannerHeader::find($request->plan_id)->update(['name'=>$request->name]);
        return redirect()->back()->with('success','Train plan edit successfully!');
    }
    public function deletePlan(Request $request){
        TrainingPlannerHeader::find($request->plan_id)->update(['deleted_at'=>date('Y-m-d')]);
        return redirect()->back()->with('success','Training plan deleted successfully');
    }
}
