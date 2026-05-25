<?php

namespace App\Models\Equipments\Depreciation;

use App\Models\Equipments\Equipment;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationAuditLog extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'depreciation_audit_logs';

    protected $fillable = [
        'equipment_id',
        'equipment_depreciation_config_id',
        'action_type',
        'previous_values',
        'new_values',
        'user_id',
        'approval_status',
    ];

    protected function casts(): array
    {
        return [
            'previous_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
