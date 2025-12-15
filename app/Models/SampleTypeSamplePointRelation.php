<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleTypeSamplePointRelation extends Model
{
    protected $table = 'sampletype_sample_point_relation';

    protected $fillable = [
        'sample_type_id',
        'sample_point_id',
    ];

    /**
     * Get the sample type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sampleType()
    {
        return $this->belongsTo(\App\SampleType::class, 'sample_type_id');
    }

    /**
     * Get the sample point
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function samplePoint()
    {
        return $this->belongsTo(SamplePoint::class, 'sample_point_id');
    }
}

