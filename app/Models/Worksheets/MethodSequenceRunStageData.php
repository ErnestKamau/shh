<?php

namespace App\Models\Worksheets;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\MethodSequences\MethodSequenceStage;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MethodSequenceRunStageData extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'run_id',
        'stage_id',
        'date_in',
        'time_in',
        'started_by_user_id',
        'date_out',
        'time_out',
        'completed_by_user_id',
        'status',
        'safe_duration_hours',
        'duration_hours',
        'safe_duration_alert_sent',
        'duration_alert_sent',
    ];

    protected $casts = [
        'date_in' => 'date',
        'date_out' => 'date',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
        'safe_duration_hours' => 'decimal:2',
        'duration_hours' => 'decimal:2',
        'safe_duration_alert_sent' => 'boolean',
        'duration_alert_sent' => 'boolean',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceRun::class, 'run_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceStage::class);
    }

    public function startedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function equipmentUsage(): HasMany
    {
        return $this->hasMany(MethodSequenceStageEquipmentUsage::class, 'run_stage_data_id');
    }

    public function mediaUsage(): HasMany
    {
        return $this->hasMany(MethodSequenceStageMediaUsage::class, 'run_stage_data_id');
    }

    public function controlUsage(): HasMany
    {
        return $this->hasMany(MethodSequenceStageControlUsage::class, 'run_stage_data_id');
    }

    public function controlResults(): HasMany
    {
        return $this->hasMany(MethodSequenceStageControlResult::class, 'run_stage_data_id');
    }

    public function sampleResults(): HasMany
    {
        return $this->hasMany(MethodSequenceStageSampleResult::class, 'run_stage_data_id');
    }

    /**
     * Calculate remaining time for the stage
     */
    public function getRemainingTime(): ?float
    {
        if (!$this->time_in || $this->status !== 'in_progress') {
            return null;
        }

        // Safeguard for Carbon objects
        $dateStr = $this->date_in instanceof \Carbon\Carbon ? $this->date_in->format('Y-m-d') : $this->date_in;
        $timeStr = $this->time_in instanceof \Carbon\Carbon ? $this->time_in->format('H:i:s') : $this->time_in;

        try {
            $startTime = \Carbon\Carbon::parse($dateStr . ' ' . $timeStr);
        } catch (\Exception $e) {
            return null;
        }

        $currentTime = now();
        $elapsedHours = $currentTime->diffInHours($startTime, true);

        // If we have a safe duration, calculate remaining time based on that first
        if ($this->safe_duration_hours) {
            $remaining = $this->safe_duration_hours - $elapsedHours;
            if ($remaining > 0) {
                return $remaining;
            }

            // Safe duration expired, calculate remaining based on total duration
            if ($this->duration_hours) {
                return $this->duration_hours - $elapsedHours;
            }
        } elseif ($this->duration_hours) {
            return $this->duration_hours - $elapsedHours;
        }

        return null;
    }

    /**
     * Get timer status: 'safe', 'warning', or 'expired'
     */
    public function getTimerStatus(): string
    {
        $remaining = $this->getRemainingTime();

        if ($remaining === null) {
            return 'safe';
        }

        if ($remaining <= 0) {
            return 'expired';
        }

        // If we have safe duration and are still within it
        if ($this->safe_duration_hours && $this->getRemainingTime() > ($this->duration_hours - $this->safe_duration_hours)) {
            return 'safe';
        }

        return 'warning';
    }

    /**
     * Check if safe duration has expired
     */
    public function isSafeDurationExpired(): bool
    {
        if (!$this->safe_duration_hours || !$this->time_in) {
            return false;
        }

        $dateStr = $this->date_in instanceof \Carbon\Carbon ? $this->date_in->format('Y-m-d') : $this->date_in;
        $timeStr = $this->time_in instanceof \Carbon\Carbon ? $this->time_in->format('H:i:s') : $this->time_in;

        try {
            $startTime = \Carbon\Carbon::parse($dateStr . ' ' . $timeStr);
        } catch (\Exception $e) {
            return false;
        }

        $elapsedHours = now()->diffInHours($startTime, true);

        return $elapsedHours >= $this->safe_duration_hours;
    }

    /**
     * Check if total duration has expired
     */
    public function isDurationExpired(): bool
    {
        if (!$this->duration_hours || !$this->time_in) {
            return false;
        }

        $dateStr = $this->date_in instanceof \Carbon\Carbon ? $this->date_in->format('Y-m-d') : $this->date_in;
        $timeStr = $this->time_in instanceof \Carbon\Carbon ? $this->time_in->format('H:i:s') : $this->time_in;

        try {
            $startTime = \Carbon\Carbon::parse($dateStr . ' ' . $timeStr);
        } catch (\Exception $e) {
            return false;
        }

        $elapsedHours = now()->diffInHours($startTime, true);

        return $elapsedHours >= $this->duration_hours;
    }

    /**
     * Calculate and set the estimated time out based on time in and duration
     */
    public function calculateEstimatedTimeOut(): void
    {
        if (!$this->time_in || !$this->date_in || !$this->duration_hours) {
            return;
        }

        // Safeguard for Carbon objects vs strings
        $dateStr = $this->date_in instanceof \Carbon\Carbon ? $this->date_in->format('Y-m-d') : $this->date_in;
        $timeStr = $this->time_in instanceof \Carbon\Carbon ? $this->time_in->format('H:i:s') : $this->time_in;

        try {
            $startTime = \Carbon\Carbon::parse($dateStr . ' ' . $timeStr);
            $endTime = $startTime->addMinutes(round($this->duration_hours * 60));

            $this->date_out = $endTime->toDateString();
            $this->time_out = $endTime->format('H:i:s');
            $this->save();

            \Illuminate\Support\Facades\Log::info('Calculated estimated time out', [
                'stage_data_id' => $this->id,
                'time_in' => $timeStr,
                'duration' => $this->duration_hours,
                'calculated_time_out' => $this->time_out
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to calculate estimated time out', [
                'error' => $e->getMessage(),
                'stage_data_id' => $this->id
            ]);
        }
    }
}
