<?php

namespace App;

use App\Models\System\SystemConfiguration;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AnalysisMethod extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public $fillable = [
		'name',
		'code',
		'description',
		'company_id',
		'active',
		'is_ltm',
		'is_sampling_method',
		'reference_type_id',
		'method_type_id',
		'validation_status',
		'sample_header_id',
	];

	const STATUS_PENDING = 'pending';
	const STATUS_SENT_FOR_VALIDATION = 'sent_for_validation';
	const STATUS_IN_VALIDATION = 'in_validation';
	const STATUS_VALIDATED = 'validated';
	const STATUS_VALIDATION_FAILED = 'validation_failed';
	const STATUS_RETURNED_TO_LAB = 'returned_to_lab';
	const STATUS_RELEASED = 'released';
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
	public function methodtype(){
		return $this->belongsTo(SystemConfiguration::class,'method_type_id');
	}

	public function company()
	{
		return $this->belongsTo('App\Company', 'company_id');
	}

	public function sampleHeader()
	{
		return $this->belongsTo('App\SampleHeader', 'sample_header_id');
	}

	public function validationRequests()
	{
		return $this->hasMany('App\MethodValidationRequest', 'method_id');
	}

	public function latestValidationRequest()
	{
		return $this->hasOne('App\MethodValidationRequest', 'method_id')->latest();
	}
}
