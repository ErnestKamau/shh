<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BatchComment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public $with = ['creator'];
  public function creator(){
    return $this->belongsTo('App\User', 'created_by');
	}

  public function reminder_for(){
    return User::find($this->reminder_for);
	}

  public function people_to_cc(){
		$people = explode(",",$this->personnel_to_cc);
		$users = User::whereIn('id', $people)->get();

		$response = array("ids"=>array(), "names"=>array());

		foreach($users as $u){
			$response['ids'][] = $u->id;
			$response['names'][] = $u->name;
		}

		return $response;
	}
}
