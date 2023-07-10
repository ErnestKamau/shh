<?php

namespace App;

use App\Models\System\SystemConfiguration;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CapturedResult extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $appends = ['repeatsampleresult'];
	protected $fillable  =['lab_section_id'];

	public function getRepeatSampleResultAttribute(){
		if($this->repeat_captured_id > 0){
			$captured = CapturedResult::find($this->repeat_captured_id);
			$percentage_config = SystemConfiguration::where('key','qc_percentage_config')->first();
			$perc_value = intval($percentage_config->value)/100 * intval($captured->result);
			$lower_limit =  intval($captured->result) - intval($perc_value);
			$upper_limit = intval($captured->result) + intval($perc_value);
			return  $lower_limit.' - '.$upper_limit;

		}
		return '';
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
