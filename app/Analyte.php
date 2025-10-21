<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Analyte extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
        'code',
        'name',
        'common_name',
        'decimal_places',
        'equivalent_weight',
        'reporting_symbol',
        'reporting_unit',
        'method',
        'equipment_id',
        'is_italic',
        'non_detectable',
        'non_accredited',
        'show_on_report',
        'active',
        'company_id',
        'deleted_at'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function ($query) {
            $query->whereNull('deleted_at');
        });
    }

    public function analysis_elements()
    {
        return $this->hasMany('App\AnalysisElements');
    }

	public function equipment(){
    return $this->belongsTo('App\Models\Equipments\Equipment');
  }

	public function method(){
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
