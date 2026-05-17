<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackControlResult extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'track_control_results';

    protected $fillable = [
        'track_id',
        'control_id',
        'result',
        'analyst_id',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(SampleCapturedTestStagesTrack::class, 'track_id');
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(\App\LabSubCategory::class, 'control_id');
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'analyst_id');
    }
}
