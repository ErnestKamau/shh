<?php

namespace App\Models\CRM;

use App\ModulePreConfigs;
use App\Models\Currency;
use App\ZohoCustomers;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CRMCustomer extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = "crm_customers";
	
	protected $casts = [
		'zoho_customer_id' => 'array',
	];
	
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
		'sub_unit_configurable_name',
		'area_configurable_name',
		'sample_point_configurable_name',
		'product_configurable_name',
		'credit_days',
		'account_status',
		'lpos_required',
		'vat_no',
		'is_hidden',
		'zoho_id',
		'currency_id',
		'zoho_customer_id',
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
  
  public function currency(){
    return $this->belongsTo(Currency::class,'currency_id');
  }
  
  public function zohocustomer(){
    return $this->belongsTo(ZohoCustomers::class,'zoho_customer_id');
  }
  
  /**
   * Get all linked Zoho customers (for multiple relationships).
   */
  public function zohoCustomers()
  {
    $zohoIds = $this->zoho_customer_id ?? [];
    if (empty($zohoIds)) {
      return collect([]);
    }
    return ZohoCustomers::whereIn('id', $zohoIds)->get();
  }
  
  /**
   * Add a Zoho customer ID to this customer.
   */
  public function addZohoCustomerId(int $zohoCustomerId): void
  {
    $zohoIds = $this->zoho_customer_id ?? [];
    if (!in_array($zohoCustomerId, $zohoIds)) {
      $zohoIds[] = $zohoCustomerId;
      $this->zoho_customer_id = $zohoIds;
      $this->save();
    }
  }
  
  /**
   * Check if this customer is linked to a Zoho customer.
   */
  public function hasZohoCustomer(int $zohoCustomerId): bool
  {
    $zohoIds = $this->zoho_customer_id ?? [];
    return in_array($zohoCustomerId, $zohoIds);
  }

  public function subUnits(){
    return $this->hasMany('App\Models\CRM\CRMCompanySubUnit', 'crm_customer_id');
  }
}