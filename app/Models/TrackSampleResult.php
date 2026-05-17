<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackSampleResult extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'track_sample_results';

    protected $fillable = [
        'track_id',
        'captured_result_id',
        'sample_code',
        'parameter',
        'method',
        'reporting_unit',
        'result',
        'raw_numeric_result',
        'standard_limit',
        'remark',
        'remark_is_auto_calculated',
        'reporting_symbol',
        'analyst_id',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'raw_numeric_result' => 'decimal:6',
        'remark_is_auto_calculated' => 'boolean',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(SampleCapturedTestStagesTrack::class, 'track_id');
    }

    public function capturedResult(): BelongsTo
    {
        return $this->belongsTo(\App\CapturedResult::class, 'captured_result_id');
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'analyst_id');
    }
}
