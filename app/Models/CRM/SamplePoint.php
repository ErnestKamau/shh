<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SamplePoint extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'name',
		'description',
		'crm_company_unit_id',
		'sample_point_area_id',
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
}
