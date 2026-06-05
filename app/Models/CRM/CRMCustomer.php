<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\ModulePreConfigs;
use App\ZohoCustomers;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CRMCustomer extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	protected $table = "crm_customers";

	protected $casts = [
		'report_columns_config' => 'array',
		'is_internal' => 'boolean',
		'code' => \App\Casts\SafeEncrypted::class,
		'postal_address' => \App\Casts\SafeEncrypted::class,
		'physical_address' => \App\Casts\SafeEncrypted::class,
		'email' => \App\Casts\SafeEncrypted::class,
		'telephone1' => \App\Casts\SafeEncrypted::class,
		'telephone2' => \App\Casts\SafeEncrypted::class,
		'contract_valid_from' => 'date',
		'contract_valid_to' => 'date',
	];

	public function country(){
    return $this->belongsTo('App\Country');
	}

  public function units(){
    return $this->hasMany('App\Models\CRM\CRMCompanyUnit', 'crm_customer_id');
  }

  public function sections()
  {
    return $this->hasMany(CRMCompanySection::class, 'crm_customer_id');
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

  public function zohoCustomers(): \Illuminate\Support\Collection
  {
    $zohoIds = [];

    if (is_array($this->zoho_customer_id) && count($this->zoho_customer_id) > 0) {
      $zohoIds = $this->zoho_customer_id;
    } elseif (is_string($this->zoho_customer_id) && $this->zoho_customer_id !== '') {
      $decodedIds = json_decode($this->zoho_customer_id, true);
      if (is_array($decodedIds)) {
        $zohoIds = $decodedIds;
      }
    }

    $zohoIds = array_values(array_filter($zohoIds, static function ($id) {
      return is_numeric($id);
    }));

    if (count($zohoIds) > 0) {
      return ZohoCustomers::query()
        ->whereIn('id', $zohoIds)
        ->orderBy('name')
        ->get();
    }

    if (!empty($this->zoho_id)) {
      $legacyZohoCustomer = $this->zohocustomer;
      if ($legacyZohoCustomer) {
        return collect([$legacyZohoCustomer]);
      }
    }

    return collect();
  }

  /**
   * Custom field categories this customer is assigned to.
   */
  public function customFieldCategories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
  {
      return $this->belongsToMany(
          \App\Models\CustomFieldCategory::class,
          'custom_field_category_customers',
          'crm_customer_id',
          'custom_field_category_id'
      )->withTimestamps();
  }

  /**
   * Report info columns (extra fields to show in report info block).
   */
  public function reportInfoColumns()
  {
      return $this->hasMany(\App\Models\CRMCustomerReportInfoColumn::class, 'crm_customer_id')
          ->orderBy('display_order')
          ->orderBy('id');
  }

  /**
   * Submission form columns (sample_details columns) for NAS submission form.
   * source = 'sample_detail', source_key = column_name.
   */
  public function submissionFormColumns()
  {
      return $this->hasMany(\App\Models\CustomerSubmissionFormColumn::class, 'crm_customer_id')
          ->where('source', 'sample_detail')
          ->where('is_active', true)
          ->orderBy('display_order')
          ->orderBy('id');
  }

  public function documentAttachments()
  {
      return $this->hasMany(CrmCustomerAttachment::class, 'crm_customer_id');
  }
}