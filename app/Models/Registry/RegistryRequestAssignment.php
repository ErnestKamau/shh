<?php

namespace App\Models\Registry;

use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistryRequestAssignment extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'registry_request_id',
        'assigned_to',
        'assigned_by',
        'role_context',
        'is_active',
        'assigned_at',
        'unassigned_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(RegistryRequest::class, 'registry_request_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
