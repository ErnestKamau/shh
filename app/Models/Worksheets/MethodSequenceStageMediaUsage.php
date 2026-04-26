<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\LabSubCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MethodSequenceStageMediaUsage extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'method_sequence_stage_media_usage';

    protected $fillable = [
        'run_stage_data_id',
        'media_id',
        'media_name',
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

    public function media(): BelongsTo
    {
        return $this->belongsTo(LabSubCategory::class, 'media_id');
    }
}
