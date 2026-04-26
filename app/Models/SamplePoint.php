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

    protected $table = 'crm_sample_points';
    
    protected $fillable = [
        'code',
        'name',
        'created_by',
    ];

    /**
     * Get the user who created this sample point
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the area that this sample point belongs to
     */
    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class, 'crm_area_id');
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
