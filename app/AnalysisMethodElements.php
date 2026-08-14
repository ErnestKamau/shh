<?php

namespace App;

use App\Concerns\DefaultsActiveOnCreate;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AnalysisMethodElements extends Model implements Auditable
{
	use DefaultsActiveOnCreate;
	use \OwenIt\Auditing\Auditable;
  public function analysis_method(){
    return $this->belongsTo('App\AnalysisMethod');
  }
  public function analyte(){
    return $this->belongsTo('App\Analyte');
  }
}
