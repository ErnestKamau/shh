<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Analyte;

class AnalysisElements extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

  protected $appends = ['parametername'];

  public function getParameterNameAttribute(){
    return Analyte::find($this->analyte_id)->name ?? '';
  }

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
