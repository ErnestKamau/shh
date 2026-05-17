<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class StageHeaderRun extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function stageHeader()
    {
        return $this->belongsTo(StageHeader::class, 'stage_header_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function trackRecords()
    {
        return $this->hasMany(SampleCapturedTestStagesTrack::class, 'stage_header_run_id');
    }

    public function capturedResults()
    {
        return $this->hasMany(\App\CapturedResult::class, 'run_id');
    }



    public function getSamplesListAttribute()
    {
        return $this->trackRecords()
            ->with('sampleDetail')
            ->get()
            ->pluck('sampleDetail.sample_code')
            ->unique()
            ->values();
    }

    public function getSamplesCountAttribute()
    {
        return $this->trackRecords()
            ->distinct('sample_detail_id')
            ->count('sample_detail_id');
    }
}
