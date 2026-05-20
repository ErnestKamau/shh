<?php

namespace App\Models\Lab;

use App\Models\Equipments\Equipment;
use App\SampleDetails;
use App\User;
use App\Zone;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentUsageRequest extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'equipment_id',
        'requester_id',
        'status',
        'request_comment',
        'proposed_start_at',
        'proposed_end_at',
        'approved_start_at',
        'approved_end_at',
        'approval_comment',
        'helping_analyst_id',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'zone_id',
    ];

    protected function casts(): array
    {
        return [
            'proposed_start_at' => 'datetime',
            'proposed_end_at' => 'datetime',
            'approved_start_at' => 'datetime',
            'approved_end_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function helpingAnalyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'helping_analyst_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function sampleDetails(): BelongsToMany
    {
        return $this->belongsToMany(
            SampleDetails::class,
            'equipment_usage_request_samples',
            'equipment_usage_request_id',
            'sample_detail_id'
        )->withTimestamps();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
