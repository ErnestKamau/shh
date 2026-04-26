<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\Equipments\Equipment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MethodSequenceStageEquipmentUsage extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'method_sequence_stage_equipment_usage';

    protected $fillable = [
        'run_stage_data_id',
        'equipment_id',
        'equipment_name',
    ];

    public function stageData(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceRunStageData::class, 'run_stage_data_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
