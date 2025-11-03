<?php

namespace App\Models\Worksheets;

use App\CapturedResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MethodSequenceStageSampleResult extends Model
{
    protected $fillable = [
        'run_stage_data_id',
        'captured_result_id',
        'result',
        'remark',
    ];

    public function stageData(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceRunStageData::class, 'run_stage_data_id');
    }

    public function capturedResult(): BelongsTo
    {
        return $this->belongsTo(CapturedResult::class);
    }
}
