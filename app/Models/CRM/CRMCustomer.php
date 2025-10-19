<?php

namespace App\Models\CRM;

use App\ModulePreConfigs;
use App\ZohoCustomers;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CRMCustomer extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = "crm_customers";
	
	protected $fillable = [
		'name',
		'code',
		'email',
		'telephone1',
		'telephone2',
		'postal_address',
		'physical_address',
		'fax',
		'website',
		'country_id',
		'company_id',
		'active',
		'unit_configurable_name',
		'sample_point_configurable_name',
		'product_configurable_name',
		'credit_days',
		'account_status',
		'lpos_required',
		'vat_no',
		'is_hidden',
		'zoho_id',
		'currency_id',
		'lab_id'
	];

	public function country(){
    return $this->belongsTo('App\Country');
	}

  public function units(){
    return $this->hasMany('App\Models\CRM\CRMCompanyUnit', 'crm_customer_id');
  }

  public function contacts(){
    return $this->hasMany('App\Models\CRM\CustomerContact', 'crm_customer_id')->where('active',1);
  }
  public function quotes(){
    return $this->hasMany('App\QuotationHeader','crm_customer_id');
  }
  public function currencyinfo(){
    return $this->belongsTo(ModulePreConfigs::class,'currency_id');
  }
  public function zohocustomer(){
    return $this->belongsTo(ZohoCustomers::class,'zoho_id');
  }
}