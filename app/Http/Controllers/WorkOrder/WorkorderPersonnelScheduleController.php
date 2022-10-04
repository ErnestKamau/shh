<?php

namespace App\Http\Controllers\WorkOrder;


use App\Models\Workorder\WorkOrder;
use App\Models\Workorder\WorkorderPersonnelSchedule as pSchedule;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkorderPersonnelScheduleController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, $id, $pid){
		$schedules = pSchedule::where('personnel_id', $pid)->where('workorder_id', $id)->get();

		$previousSchedules = pSchedule::where('personnel_id', $pid)->whereNotIn('workorder_id', [$id])->get();

		$events = [];

		// $otherEvents = [];

		foreach($schedules as $s){
			$startH = \Carbon\Carbon::parse($s->start)->format('H:i');
			$endH = \Carbon\Carbon::parse($s->end)->format('H:i');

			$events[\Carbon\Carbon::parse($s->start).'-'.\Carbon\Carbon::parse($s->end)] = [
				// "title"=> $startH." - ".$endH,
				"start"=>\Carbon\Carbon::parse($s->start),
				"end"=>\Carbon\Carbon::parse($s->end),
				"backgroundColor"=> "#00713b",
				"borderColor"=> "#00713b",
				"textColor"=> "#fff",
				"other"=>false
			];
		}

		foreach($previousSchedules as $s){
			$startH = \Carbon\Carbon::parse($s->start)->format('H:i');
			$endH = \Carbon\Carbon::parse($s->end)->format('H:i');

			$events[\Carbon\Carbon::parse($s->start).'-'.\Carbon\Carbon::parse($s->end)] = [
				// "title"=> $startH." - ".$endH,
				"start"=>\Carbon\Carbon::parse($s->start),
				"end"=>\Carbon\Carbon::parse($s->end),
				"backgroundColor"=> "#e61e15",
				"borderColor"=> "#e61e15",
				"textColor"=> "#fff",
				"other"=>true
			];
		}

		return json_encode($events);
	}

	public function update(Request $request, $id, $pid, $action='add'){
		if($action == 'remove'){
			$start = explode('(', $request->start)[0];
			$end = explode('(', $request->end)[0];

			pSchedule::where('personnel_id', $pid)->where('workorder_id', $id)->where('start', \Carbon\Carbon::parse($start))->where('end', \Carbon\Carbon::parse($end))->delete();

		}
		else{

			$start = $request->start;
			$end = $request->end;
			$failed = [];

			if($this->isTimeSlotAllowed([$start, $end], $pid)){
				$schedule = new pSchedule;
				$schedule->personnel_id = $pid;
				$schedule->workorder_id = $id;
				$schedule->start = $request->start;
				$schedule->end = $request->end;
				$schedule->save();
			}
			else{
				$failed[] = \Carbon\Carbon::parse($start).' to '.\Carbon\Carbon::parse($end);
			}

			if($request->duplicate == 1){
				$workorder = WorkOrder::find($id);
				$woEndDate = \Carbon\Carbon::parse(\Carbon\Carbon::parse($workorder->due_date)->format('Y-m-d'));
				$checkID = 0;

				$beginDate = \Carbon\Carbon::parse(\Carbon\Carbon::parse($request->start)->format('Y-m-d'));

				$numberOfDaysToDueDate = $woEndDate->diffInDays($beginDate);

				$startDate = \Carbon\Carbon::parse($start);
				$endDate = \Carbon\Carbon::parse($end);
				for($i=1; $i<=$numberOfDaysToDueDate; $i++){
					$newStartDate = $startDate->addDays(1);
					$newEndDate = $endDate->addDays(1);

					$allowed = $this->isTimeSlotAllowed([$newStartDate, $newEndDate], $pid);

					if($allowed){
						$schedule = new pSchedule;
						$schedule->personnel_id = $pid;
						$schedule->workorder_id = $id;
						$schedule->start = $newStartDate;
						$schedule->end = $newEndDate;
						$schedule->save();
					}
					else{
						$failed[] = \Carbon\Carbon::parse($newStartDate).' to '.\Carbon\Carbon::parse($newEndDate);
					}
				}
			}
		}

		$startH = \Carbon\Carbon::parse($start)->format('H:i');
		$endH = \Carbon\Carbon::parse($end)->format('H:i');

		return json_encode([
			"status"=>true,
			"action"=>$action,
			"event"=>[
				"key"=>\Carbon\Carbon::parse($start).'-'.\Carbon\Carbon::parse($end),
				"start"=>\Carbon\Carbon::parse($start),
				"end"=>\Carbon\Carbon::parse($end),
				"backgroundColor"=> "00713b",
				"textColor"=> "#fff"
			],
			"failed"=> $failed ?? []
		]);
	}

	public function isTimeSlotAllowed($t, $pid){
		$response = true;
		$exists = pSchedule::where('personnel_id', $pid)->where('start', '<=', $t[1])->where('end', '>=', $t[0])->get();

		if($exists->count() > 0){
			$response = false;
		}

		return $response;
	}

	public function clone($id, $pid, $pid2){
		pSchedule::where('personnel_id', $pid)->where('workorder_id', $id)->delete();

		$slots = pSchedule::where('personnel_id', $pid2)->where('workorder_id', $id)->get();

		foreach($slots as $s){
			$newS = $s->replicate();

			$newS->personnel_id = $pid;
			$newS->save();
		}

		return json_encode(['status'=>true]);
	}
}
