<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CRMCompanyUnit extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = "crm_company_units";

  public function products(){
    return $this->hasMany('App\Models\CRM\CompanyProduct', 'crm_company_unit_id');
	}

  public function sample_points(){
    return $this->hasMany('App\Models\CRM\SamplePoint', 'crm_company_unit_id');
  }
}
