<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CRMCompanySubUnit extends Model
{
    protected $table = 'crm_company_sub_units';
    
    protected $fillable = [
        'name',
        'code',
        'crm_customer_id',
        'crm_company_unit_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the customer that owns this sub unit
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    /**
     * Get the parent company unit
     */
    public function companyUnit(): BelongsTo
    {
        return $this->belongsTo(CRMCompanyUnit::class, 'crm_company_unit_id');
    }

    /**
     * Get the sample points for this sub unit
     */
    public function samplePoints(): HasMany
    {
        return $this->hasMany(\App\Models\CRM\SamplePoint::class, 'crm_company_sub_unit_id');
    }
}
