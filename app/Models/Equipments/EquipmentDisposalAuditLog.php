<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDisposalAuditLog extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'disposal_id',
        'action',
        'user_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the disposal this audit log belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function disposal(): BelongsTo
    {
        return $this->belongsTo(EquipmentDisposal::class, 'disposal_id');
    }

    /**
     * Get the user who performed the action
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by action
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope to filter by date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Create an audit log entry
     *
     * @param EquipmentDisposal $disposal
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @return static
     */
    public static function log(
        EquipmentDisposal $disposal,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): self {
        return static::create([
            'disposal_id' => $disposal->id,
            'action' => $action,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
        ]);
    }
}

