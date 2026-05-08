<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SamplePoint extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'sample_points';
    
    protected $fillable = [
        'crm_customer_id',
        'crm_company_unit_id',
        'name',
        'description',
        'gps',
        'active',
    ];

    /**
     * Get the user who created this sample point
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CRM\CRMCompanyUnit::class, 'crm_company_unit_id');
    }

    /**
     * Get the sample types that this sample point is assigned to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sampleTypes()
    {
        return $this->belongsToMany(
            \App\SampleType::class,
            'sampletype_sample_point_relation',
            'sample_point_id',
            'sample_type_id'
        )->withTimestamps();
    }
}
