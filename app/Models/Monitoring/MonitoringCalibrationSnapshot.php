<?php

namespace App\Models\Monitoring;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringCalibrationSnapshot extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'log_id',
        'equipment_id',
        'calibration_log_id',
        'correction_factor',
        'uncertainty_of_measure',
        'calibration_date',
        'calibration_certificate',
        'standard_used',
        'snapshot_payload',
    ];

    protected $casts = [
        'correction_factor' => 'decimal:6',
        'uncertainty_of_measure' => 'decimal:6',
        'calibration_date' => 'date',
        'snapshot_payload' => 'array',
    ];

    public function log(): BelongsTo
    {
        return $this->belongsTo(MonitoringLog::class, 'log_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function calibrationLog(): BelongsTo
    {
        return $this->belongsTo(MaintainanceCalibrationLog::class, 'calibration_log_id');
    }
}
