<?php

namespace App\Http\Controllers;

use App\PersonnelWorkHistory;
use Illuminate\Http\Request;

class PersonnelWorkHistoryController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function updateWorkHistory($user_id, $department_id, $job_id){
		$previousHistory = PersonnelWorkHistory::where('user_id', $user_id)->whereNull('end_date')->first();
		if(isset($previousHistory->id)){
			$previousHistory->end_date = \Carbon\Carbon::now();
			$previousHistory->save();
		}

		$newHistory = new PersonnelWorkHistory;
		$newHistory->user_id = $user_id;
		$newHistory->department_id = $department_id;
		$newHistory->job_id = $job_id;

		$newHistory->save();

		return true;
	}
}
