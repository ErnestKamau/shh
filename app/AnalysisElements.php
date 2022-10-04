<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AnalysisElements extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function analysis_type(){
    return $this->belongsTo('App\AnalysisType');
	}

  public function analyte(){
    return $this->belongsTo('App\Analyte');
	}

  public function method(){
    return AnalysisMethod::find($this->method);
	}

  public function equipment(){
    return $this->belongsTo('App\Models\Equipments\Equipment');
	}

  public function operator(){
    return $this->belongsTo('App\User', 'operator_id');
  }
}
