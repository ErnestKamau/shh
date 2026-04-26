<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_areas';
    
    protected $fillable = [
        'code',
        'name',
        'created_by',
    ];

    /**
     * Get the user who created this area
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sample point areas linked to this area
     */
    public function samplePointAreas()
    {
        return $this->hasMany(\App\Models\SamplePointArea::class, 'crm_area_id');
    }

    /**
     * Get the global sample points linked to this area
     */
    public function globalSamplePoints()
    {
        return $this->hasMany(\App\Models\SamplePoint::class, 'crm_area_id');
    }

    /**
     * Get the sample types that this area is assigned to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sampleTypes()
    {
        return $this->belongsToMany(
            \App\SampleType::class,
            'sampletype_area_relation',
            'area_id',
            'sample_type_id'
        )->withTimestamps();
    }
}
