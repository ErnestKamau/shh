<?php

namespace App\Models\Registry;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistryRequestStatusLog extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'registry_request_id',
        'stage_code',
        'entered_at',
        'exited_at',
        'duration_seconds',
        'sla_breached',
    ];

    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
            'exited_at' => 'datetime',
            'sla_breached' => 'boolean',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(RegistryRequest::class, 'registry_request_id');
    }
}
