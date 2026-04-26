<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\Equipments\Equipment;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MethodSequenceStageEquipmentUsage extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'method_sequence_stage_equipment_usage';

    protected $fillable = [
        'run_stage_data_id',
        'equipment_id',
        'equipment_name',
        'started_at',
        'completed_at',
        'started_by_user_id',
        'completed_by_user_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function stageData(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceRunStageData::class, 'run_stage_data_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function startedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    /**
     * Get the duration the equipment was in use (in minutes)
     * Returns null if equipment hasn't been completed yet
     */
    public function getDurationMinutes(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Get the duration formatted as HH:MM:SS
     */
    public function getFormattedDuration(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $minutes = $this->getDurationMinutes() ?? 0;
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d:00', $hours, $mins);
    }

    /**
     * Check if equipment usage is currently in progress
     */
    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->completed_at === null;
    }

    /**
     * Check if equipment usage has been completed
     */
    public function isCompleted(): bool
    {
        return $this->started_at !== null && $this->completed_at !== null;
    }

    /**
     * Mark equipment as started (turned on)
     */
    public function markAsStarted(?int $userId = null): void
    {
        $this->started_at = now();
        $this->started_by_user_id = $userId;
        $this->save();
    }

    /**
     * Mark equipment as completed (turned off)
     */
    public function markAsCompleted(?int $userId = null): void
    {
        $this->completed_at = now();
        $this->completed_by_user_id = $userId;
        $this->save();
    }
}

