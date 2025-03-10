<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Analyte;

class AnalysisElements extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

  protected $appends = ['parametername'];
  protected $fillable = ['lab_section_id', 'analysis_type_id', 'analyte_id', 'method', 'reporting_unit', 'non_accredited','is_pesticide'];

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
  public function mmethod(){
    return $this->belongsTo(AnalysisMethod::class,'method');
  }
  public function ltmethod(){
    return $this->belongsTo(AnalysisMethod::class,'ltm_method_id');
	}

  public function equipment(){
    return $this->belongsTo('App\Models\Equipments\Equipment');
	}

  public function operator(){
    return $this->belongsTo('App\User', 'operator_id');
  }
}
