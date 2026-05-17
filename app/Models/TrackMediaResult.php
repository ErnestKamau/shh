<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackMediaResult extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'track_media_results';

    protected $fillable = [
        'track_id',
        'media_id',
        'result',
        'analyst_id',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the track this media result belongs to
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(SampleCapturedTestStagesTrack::class, 'track_id');
    }

    /**
     * Get the media (solution) used
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(\App\LabSubCategory::class, 'media_id');
    }

    /**
     * Get the analyst who recorded the result
     */
    public function analyst(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'analyst_id');
    }
}
