<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleType extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function analysis_types(){
    return $this->hasMany('App\AnalysisType')->orderBy('level','asc');
  }
  public function sample_condition(){
    return $this->hasMany('App\SampleCondition');
  }
  public function sample_analysis_stage(){
    return $this->hasMany('App\SampleToSampleAnalysisStage');
  }
}
