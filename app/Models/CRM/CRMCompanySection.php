<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class CRMCompanySection extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_company_sections';

    protected $fillable = ['name', 'crm_customer_id', 'company_id', 'active'];

    public function customer()
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    public function units()
    {
        return $this->hasMany(CRMCompanyUnit::class, 'crm_company_section_id');
    }
}
