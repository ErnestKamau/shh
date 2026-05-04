<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class Analyte extends Model implements Auditable
{
    use HasUuids;
	use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;
    
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
		if (empty($this->method)) return [];
		$ids = array_filter(explode(",", $this->method));
		if (empty($ids)) return [];
		$methods = AnalysisMethod::whereIn('id', $ids)->get();
		$response = array();

		foreach($methods as $method){
			$response[$method->name] = $method->id;
		}

		return $response;
	}

	public function equipments(){
		if (empty($this->equipment_id)) return [];
		$ids = array_filter(explode(",", $this->equipment_id));
		if (empty($ids)) return [];
		$equipments = Models\Equipments\Equipment::whereIn('id', $ids)->get();
		$response = array();

		foreach($equipments as $equipment){
			$response[$equipment->name] = $equipment->id;
		}

		return $response;
	}
}
