<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Analyte extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function analysis_elements(){
    return $this->hasMany('App\AnalysisElements');
	}

	public function equipment(){
    return $this->belongsTo('App\Models\Equipments\Equipment');
  }

	public function method(){
    return $this->belongsTo('App\AnalysisMethod', 'method');
	}

	public function methods_with(){
		return $this->belongsTo('App\AnalysisMethod', 'method');
	}

	public function methods(){
		$methods = AnalysisMethod::whereIn('id', explode(",", $this->method))->get();
		$response = array();

		foreach($methods as $method){
			$response[$method->name] = $method->id;
		}

		return $response;
	}

	public function equipments(){
		$equipments = Models\Equipments\Equipment::whereIn('id', explode(",", $this->equipment_id))->get();
		$response = array();

		foreach($equipments as $equipment){
			$response[$equipment->name] = $equipment->id;
		}

		return $response;
	}
}
