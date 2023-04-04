<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CapturedResult extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $appends = ['repeatsampleresult'];

	public function getRepeatSampleResultAttribute(){
		$captured = CapturedResult::find($this->repeat_captured_id);
		return $captured->result ?? '';
	}

	public function sample()
	{
		return $this->belongsTo('App\SampleDetail');
	}

	public function analysis_type()
	{
		return $this->belongsTo('App\AnalysisType');
	}

	public function analyte(){
		return Analyte::where('code', $this->analyte_code)->first();
	}

	public function defacto_analyst(){
		return User::where('id',$this->operator_id)->where('active',1)->first() ?? AnalysisElements::join('users as u', 'u.id', '=', 'operator_id')
			->where('analysis_type_id', $this->analysis_type_id)
			->where('u.active',1)
			->selectRaw('u.*')
			->where('analyte_id', $this->analyte_id)->first();
	}

	public function equipment(){
		return Models\Equipments\Equipment::where('id', $this->equipment_id)->first();
	}

	public function method(){
		return AnalysisMethod::find($this->method_id);
	}

	public function operators(){
		$equipment = Models\Equipments\Equipment::where('id', $this->equipment_id)->get();
		$operators = array();
		foreach($equipment as $e){
			$ops = $e->operators();
			foreach($ops as $o){
				$user = User::where('id',$o->user_id)->where('active',1)->first();
				$operators[] = $user;
			}
		}

		return $operators;
	}

}
