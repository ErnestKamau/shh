<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentDisposalApproval extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'disposal_id',
        'step',
        'approver_id',
        'decision',
        'remarks',
        'signature_path',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the disposal this approval belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function disposal(): BelongsTo
    {
        return $this->belongsTo(EquipmentDisposal::class, 'disposal_id');
    }

    /**
     * Get the approver user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Check if approval is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return is_null($this->decision);
    }

    /**
     * Check if approval is approved
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->decision === 'approve';
    }

    /**
     * Check if approval is rejected
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->decision === 'reject';
    }

    /**
     * Scope to filter by step
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $step
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStep($query, int $step)
    {
        return $query->where('step', $step);
    }

    /**
     * Scope to filter pending approvals
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->whereNull('decision');
    }
}

