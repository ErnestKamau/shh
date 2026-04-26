<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SamplePointArea extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;
    
    protected $table = 'sample_point_area';
    
    protected $fillable = [
        'description',
        'crm_customer_id',
        'crm_company_sub_unit_id',
        'crm_area_id',
        'crm_company_unit_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function crmCustomer()
    {
        return $this->belongsTo('App\Models\CRM\CRMCustomer', 'crm_customer_id');
    }

    public function samplePoints()
    {
        return $this->hasMany('App\Models\CRM\SamplePoint', 'sample_point_area_id');
    }

    public function crmArea()
    {
        return $this->belongsTo('App\Models\Area', 'crm_area_id');
    }

    public function subUnit()
    {
        return $this->belongsTo('App\Models\CRM\CRMCompanySubUnit', 'crm_company_sub_unit_id');
    }

    public function companyUnit()
    {
        return $this->belongsTo('App\Models\CRM\CRMCompanyUnit', 'crm_company_unit_id');
    }
} 