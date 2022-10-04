<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleAnalysisStage extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = 'sample_analysis_stages';

  public function sample_analysis_stage(){
    return $this->hasMany('App\SampleToSampleAnalysisStage');
  }
}
