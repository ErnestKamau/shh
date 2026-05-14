<?php

namespace App\Models\Monitoring;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringLogEntry extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'log_id',
        'template_field_id',
        'field_key',
        'field_label',
        'raw_value',
        'computed_value',
        'status',
        'pass',
        'meta',
    ];

    protected $casts = [
        'pass' => 'boolean',
        'meta' => 'array',
    ];

    public function log(): BelongsTo
    {
        return $this->belongsTo(MonitoringLog::class, 'log_id');
    }

    public function templateField(): BelongsTo
    {
        return $this->belongsTo(MonitoringTemplateField::class, 'template_field_id');
    }
}
