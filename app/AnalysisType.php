<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AnalysisType extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  protected $fillable  = [
    'name', 'code', 'description', 'sample_type_id', 'lab_id', 'company_id', 
    'active', 'level', 'reporting_time', 'short_name', 'lab_section_id', 
    'brand_id', 'is_pesticide', 'zoho_id', 'product_type', 'result_expo',
    'include_hygiene_score', 'include_sanitizer_efficiency'
  ];
  protected $appends = ['labsectionname'];
  public function lab(){
    return $this->belongsTo('App\Lab');
  }

  public function sample_type(){
    return $this->belongsTo('App\SampleType');
  }

  public function analysis_elements(){
    return $this->hasMany('App\AnalysisElements')->with(['mmethod','ltmethod','analyte','equipment','operator'])->orderBy('level', 'asc');
	}

  public function guides(){
    return $this->hasMany('App\AnalysisGuide');
	}

	public function active_analysis_elements(){
		$active = 1;
		return AnalysisElements::join('analytes as a', 'a.id', '=', 'analysis_elements.analyte_id')
			->where('analysis_elements.active', $active)->where('analysis_type_id', $this->id)
			->selectRaw('analysis_elements.*, RTRIM(a.code) as analyte_code')->get();
	}
  public function getLabSectionNameAttribute(){
    return SampleAnalysisStage::find($this->lab_section_id)->name ?? '';
  }
  public function zohoitem(){
    return $this->belongsTo(InventorySubCategories::class,'zoho_id');
  }

}
