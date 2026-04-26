<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CRMCompanyUnit extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	protected $table = "crm_company_units";

  public function products(){
    return $this->hasMany('App\Models\CRM\CompanyProduct', 'crm_company_unit_id');
	}

  public function sample_points(){
    return $this->hasMany('App\Models\CRM\SamplePoint', 'crm_company_unit_id');
  }

  public function customer(){
    return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
  }

  public function section()
  {
    return $this->belongsTo(CRMCompanySection::class, 'crm_company_section_id');
  }
}
