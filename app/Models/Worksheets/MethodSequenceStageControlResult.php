<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MethodSequenceStageControlResult extends Model
{
    protected $fillable = [
        'run_stage_data_id',
        'control_usage_id',
        'result',
        'remark',
    ];

    public function stageData(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceRunStageData::class, 'run_stage_data_id');
    }

    public function controlUsage(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceStageControlUsage::class, 'control_usage_id');
    }
}
