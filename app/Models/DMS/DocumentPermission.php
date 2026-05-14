<?php

namespace App\Models\DMS;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPermission extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'permissionable_type',
        'permissionable_id',
        'subject_type',
        'subject_id',
        'permission_type',
        'granted_by',
    ];

    /**
     * Get the permissionable model (Document or DocumentType)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function permissionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the subject model (User or Role)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who granted this permission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Scope to filter permissions for a specific user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $user)
    {
        return $query->where('subject_type', User::class)
                     ->where('subject_id', $user->id);
    }

    /**
     * Scope to filter permissions for a specific role
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $roleId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRole($query, string $roleId)
    {
        return $query->where('subject_type', \App\Models\Auth\Role::class)
                     ->where('subject_id', $roleId);
    }

    /**
     * Scope to filter by permission type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $permissionType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $permissionType)
    {
        return $query->where('permission_type', $permissionType);
    }
}

