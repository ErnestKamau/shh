<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CRMCustomer extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = "crm_customers";

	public function country(){
    return $this->belongsTo('App\Country');
	}

  public function units(){
    return $this->hasMany('App\Models\CRM\CRMCompanyUnit', 'crm_customer_id');
  }

  public function contacts(){
    return $this->hasMany('App\Models\CRM\CustomerContact', 'crm_customer_id')->orderBy('active','desc');
  }
  public function quotes(){
    return $this->hasMany('App\QuotationHeader','crm_customer_id');
  }
}