<?php

namespace App\Models\Sampleworkflow;

use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleHeaderUserAssignment extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'from_user_id',
        'to_user_id',
        'assigned_by_user_id',
        'comment',
        'status',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForAssignee($query, string $userId)
    {
        return $query->where('to_user_id', $userId);
    }
}
