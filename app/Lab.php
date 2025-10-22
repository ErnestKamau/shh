<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Lab extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  
  protected $fillable = [
    'code',
    'name',
    'address',
    'location',
    'fax',
    'email',
    'website',
    'company_id',
    'is_external',
    'phone1',
    'phone2',
    'phone3',
    'active',
    'start_sample_no',
  ];
  
  public function company(){
    return $this->belongsTo('App\Company');
  }

  public function analysis_types(){
    return $this->hasMany('App\AnalysisType');
  }
}
