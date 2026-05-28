<?php

namespace App\Models\Monitoring;

use App\Lab;
use App\LabSection;
use App\User;
use App\Models\Equipments\Equipment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class MonitoringLog extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'template_version',
        'lab_id',
        'equipment_id',
        'lab_section_id',
        'frequency_slot',
        'remark',
        'log_date',
        'monitoring_scope',
        'status',
        'overall_result',
        'deviation_triggered',
        'payload',
        'executed_by',
        'executed_at',
        'approved_by',
        'approved_at',
        'signature_payload',
        'company_id',
    ];

    protected $casts = [
        'frequency_slot' => 'integer',
        'log_date' => 'date',
        'deviation_triggered' => 'boolean',
        'payload' => 'array',
        'signature_payload' => 'array',
        'executed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MonitoringTemplate::class, 'template_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function labSection(): BelongsTo
    {
        return $this->belongsTo(LabSection::class, 'lab_section_id');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MonitoringLogEntry::class, 'log_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(MonitoringApproval::class, 'log_id');
    }

    public function calibrationSnapshots(): HasMany
    {
        return $this->hasMany(MonitoringCalibrationSnapshot::class, 'log_id');
    }

    public function resolvedFrequencySlot(): ?int
    {
        if ($this->frequency_slot !== null) {
            return (int) $this->frequency_slot;
        }

        $slot = Arr::get($this->payload ?? [], 'frequency_slot');

        if ($slot === null || $slot === '') {
            return null;
        }

        return (int) $slot;
    }

    public function resolvedLabSectionId(): ?string
    {
        if (filled($this->lab_section_id)) {
            return (string) $this->lab_section_id;
        }

        $sectionId = Arr::get($this->payload ?? [], 'lab_section_id');

        return filled($sectionId) ? (string) $sectionId : null;
    }

    public function resolvedRemark(): ?string
    {
        if (filled($this->remark)) {
            return (string) $this->remark;
        }

        $remark = Arr::get($this->payload ?? [], 'remark');

        return filled($remark) ? (string) $remark : null;
    }
}
