<?php

namespace App\Models\Equipments\Depreciation;

use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepreciationScheduleVersion extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'depreciation_schedule_versions';

    protected $fillable = [
        'equipment_depreciation_config_id',
        'version_number',
        'reason',
        'is_archived',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
        ];
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(EquipmentDepreciationConfig::class, 'equipment_depreciation_config_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class, 'depreciation_schedule_version_id')
            ->orderBy('period_index');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
