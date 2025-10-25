<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CompanyProduct extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'name',
		'crm_company_unit_id',
		'active',
	];
	
	public function unit()
	{
		return $this->belongsTo('App\Models\CRM\CRMCompanyUnit', 'crm_company_unit_id');
	}
}
