<?php

namespace App\Http\Controllers\WorkOrder;

use App\Models\Workorder\PersonnelWorkingSchedule as Schedule;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
class PersonnelWorkingScheduleController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index(){
		$working_schedules = Schedule::orderBy('title')->get();

		return view('layouts.workorder.work-scheduler.index', compact('working_schedules'));
	}

	public function remove_working_schedule(Request $request, $id){
		$schedule = Schedule::find($id);

		$schedule->delete();

		return redirect()->back()->with('success', 'Schedule has been removed.');
	}

	public function update_working_schedule(Request $request, $id=false){
		// return json_encode($request->all());
		$dayCounter = 0;
		if($id){
			$day = $request->has('selected_day') ? $request->selected_day : $request->day[0];
			$schedule = Schedule::find($id);
			$schedule->name = $request->name;
			$schedule->title = $request->title;
			$schedule->day = $day;
			$schedule->slot_type = $request->action;
			$schedule->duration = $request->duration;
			$schedule->start_timeslot = $request->start_timeslot;
			$schedule->end_timeslot = $request->end_timeslot;
			$schedule->save();
		}
		else{
			foreach($request->day as $day){
				$schedule = new Schedule;
				$schedule->name = $request->name;
				$schedule->title = $request->title;
				$schedule->day = $day;
				$schedule->slot_type = $request->action;
				$schedule->duration = $request->duration;
				$schedule->start_timeslot = $request->start_timeslot;
				$schedule->end_timeslot = $request->end_timeslot;
				$schedule->save();
			}
		}

		return redirect()->back()->with('success', 'Schedule has been '.$id ? 'updated' : 'added'.'.');
	}
}
