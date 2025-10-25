<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SamplePoint extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'crm_company_unit_id',
		'sample_point_area_id',
		'crm_area_id',
		'crm_sample_point_id',
		'crm_company_sub_unit_id',
		'crm_customer_id',
		'active',
		'gps'
	];

	protected $casts = [
		'active' => 'boolean',
	];

	public function unit()
	{
		return $this->belongsTo('App\Models\CRM\CRMCompanyUnit', 'crm_company_unit_id');
	}

	public function area()
	{
		return $this->belongsTo('App\Models\SamplePointArea', 'sample_point_area_id');
	}

	public function crmArea()
	{
		return $this->belongsTo('App\Models\Area', 'crm_area_id');
	}

	public function crmSamplePoint()
	{
		return $this->belongsTo('App\Models\SamplePoint', 'crm_sample_point_id');
	}

	public function subUnit()
	{
		return $this->belongsTo('App\Models\CRM\CRMCompanySubUnit', 'crm_company_sub_unit_id');
	}

	public function customer()
	{
		return $this->belongsTo('App\Models\CRM\CRMCustomer', 'crm_customer_id');
	}
}
