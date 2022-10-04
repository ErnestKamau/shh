<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AnalysisGuide extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function analyte(){
    return $this->belongsTo('App\Analyte');
	}
}
