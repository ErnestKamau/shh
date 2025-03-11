<?php

namespace App;

use App\Models\System\SystemConfiguration;
use App\Observers\CapturedObserver;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;


// [ObservedBy([CapturedObserver::class])];
class CapturedResult extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $appends = ['repeatsampleresult','isitalic'];
	protected $fillable  =['lab_section_id','remark_is_manual','sample_detail_code','sample_header_id','sample_detail_id','result','user_id','remark','is_pesticide'];
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
		return $this->belongsTo('App\SampleDetails', 'sample_detail_id');
	}

	public function analysis_type()
	{
		return $this->belongsTo('App\AnalysisType');
	}

	public function my_analyte(){
		return $this->belongsTo(Analyte::class, 'analyte_id');
	}

	public function analyte(){
		return Analyte::where('code', $this->id)->first();
	}

	public function defacto_analyst_with(){
		return $this->belongsTo(User::class, 'operator_id');
	}

	public function defacto_analyst(){
		return User::where('id',$this->operator_id)->where('active',1)->first() ?? AnalysisElements::join('users as u', 'u.id', '=', 'operator_id')
			->where('analysis_type_id', $this->analysis_type_id)
			->where('u.active',1)
			->selectRaw('u.*')
			->where('analyte_id', $this->analyte_id)->first();
	}

	public function equipment_with(){
		return $this->belongsTo(Models\Equipments\Equipment::class, 'equipment_id');
	}

	public function equipment(){
		return Models\Equipments\Equipment::where('id', $this->equipment_id)->first();
	}

	public function method(){
		return AnalysisMethod::find($this->method_id);
	}
	public function ltmethod(){
		return $this->belongsTo(AnalysisMethod::class,'ltm_method_id');
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
	public function getIsItalicAttribute(){
		return Analyte::find($this->analyte_id)->is_italic;
	}

}
