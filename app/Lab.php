<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Lab extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function company(){
    return $this->belongsTo('App\Company');
  }

  public function analysis_types(){
    return $this->hasMany('App\AnalysisType');
  }
}
