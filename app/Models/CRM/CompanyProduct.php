<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CompanyProduct extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	protected $guarded = [];
  public function unit()
	{
		return $this->belongsTo('App\Models\CRM\CRMCompanyUnit', 'crm_company_unit_id');
	}
}
