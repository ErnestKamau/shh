<?php

namespace App\Models\Worksheets;

use OwenIt\Auditing\Contracts\Auditable;

use App\LabSubCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MethodSequenceStageControlUsage extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'method_sequence_stage_control_usage';

    protected $fillable = [
        'run_stage_data_id',
        'control_id',
        'control_name',
        'volume',
        'unit',
        'batch_number',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
    ];

    public function stageData(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceRunStageData::class, 'run_stage_data_id');
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(LabSubCategory::class, 'control_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(MethodSequenceStageControlResult::class, 'control_usage_id');
    }
}
