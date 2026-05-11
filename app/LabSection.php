<?php

namespace App;

use App\Models\Equipments\Equipment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class LabSection extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'lab_sections';

    protected $fillable = [
        'lab_id',
        'name',
        'code',
        'description',
        'does_environmental_analysis',
        'equipment_id',
        'expected_value_type',
        'expected_value',
        'expected_min',
        'expected_max',
        'optimum_level',
        'result_nature',
        'reporting_unit',
        'active',
        'company_id',
    ];

    protected $casts = [
        'does_environmental_analysis' => 'boolean',
        'active' => 'boolean',
        'expected_min' => 'float',
        'expected_max' => 'float',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
