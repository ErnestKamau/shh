<?php

namespace App\Models\Equipments\Depreciation;

use App\Enums\Equipment\AppraisalStatus;
use App\Models\Equipments\Equipment;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentAppraisal extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'equipment_appraisals';

    protected $fillable = [
        'equipment_id',
        'equipment_depreciation_config_id',
        'appraisal_date',
        'prior_book_value',
        'new_appraised_value',
        'useful_life_extension_years',
        'reason',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'appraisal_date' => 'date',
            'prior_book_value' => 'decimal:2',
            'new_appraised_value' => 'decimal:2',
            'status' => AppraisalStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(EquipmentDepreciationConfig::class, 'equipment_depreciation_config_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
