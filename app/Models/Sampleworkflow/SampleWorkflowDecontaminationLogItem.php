<?php

namespace App\Models\Sampleworkflow;

use App\LabDecontaminationArea;
use App\LabSection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleWorkflowDecontaminationLogItem extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'decontamination_log_id',
        'lab_section_id',
        'lab_decontamination_area_id',
        'swabbing',
    ];

    protected function casts(): array
    {
        return [
            'swabbing' => 'boolean',
        ];
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(SampleWorkflowDecontaminationLog::class, 'decontamination_log_id');
    }

    public function labSection(): BelongsTo
    {
        return $this->belongsTo(LabSection::class, 'lab_section_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(LabDecontaminationArea::class, 'lab_decontamination_area_id');
    }
}
