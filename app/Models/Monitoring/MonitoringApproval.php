<?php

namespace App\Models\Monitoring;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringApproval extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'log_id',
        'workflow_stage',
        'approver_id',
        'status',
        'signed_at',
        'signature_reason',
        'password_confirmed',
        'signature_payload',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'password_confirmed' => 'boolean',
        'signature_payload' => 'array',
    ];

    public function log(): BelongsTo
    {
        return $this->belongsTo(MonitoringLog::class, 'log_id');
    }
}
