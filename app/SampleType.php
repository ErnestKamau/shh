<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\SampleTypeCategory;

class SampleType extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'name',
		'code', 
		'description',
		'company_id',
		'active',
		'sample_type_category',
		'rating_header_id',
		'report_template_id',
		'report_format_id'
	];
	
  public $with =['analysis_types', 'sample_condition'];
  public function analysis_types(){
    return $this->hasMany('App\AnalysisType')->orderBy('level','asc');
  }
  public function sample_condition(){
    return $this->hasMany('App\SampleCondition');
  }
  public function sample_analysis_stage(){
    return $this->hasMany('App\SampleToSampleAnalysisStage');
  }
  public function category(){
    return SampleTypeCategory::find($this->sample_type_category)->sample_type_category ?? '';
  }

  public function ratingHeader(){
    return $this->belongsTo('App\Models\RatingHeader');
  }

  public function reportFormat(){
    return $this->belongsTo('App\ReportFormat');
  }
}
