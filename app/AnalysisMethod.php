<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AnalysisMethod extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public $fillable = ['name','code','description','company_id','active','is_ltm'];
  public function analysis_method_elements(){
    return $this->hasMany('App\AnalysisMethodElements');
	}

	public function analytes(){
    	return $this->is_ltm == 0 ? AnalysisElements::where('method', $this->id)->get() :  AnalysisElements::where('ltm_method_id', $this->id)->get() ;
	}

	public function reagents(){
		$reagents = MethodReagent::join('inventory_sub_categories as isc', 'isc.id', '=', 'method_reagents.inventory_sub_category_id')
			->selectRaw('isc.id as reagent_id, isc.name as reagent_name, method_reagents.quantity, method_reagents.reporting_unit as reagent_unit')
			->where('method_id', $this->id)->get();

		$arr = array();

		foreach($reagents as $r){
			$arr[$r->reagent_id] = $r;
		}

		return $arr;
	}
	public function referencemethod(){
		return $this->belongsTo(AnalysisMethod::class,'reference_type_id');
	}
}
