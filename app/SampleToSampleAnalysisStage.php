<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleToSampleAnalysisStage extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public function sample_analysis_stage(){
	  return $this->belongsTo('App\SampleAnalysisStage');
	}
}
