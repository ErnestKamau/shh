<?php

namespace App\Services;

use App\Models\Worksheets\MethodSequenceRunStageData;
use App\Models\Worksheets\MethodSequenceStageEquipmentUsage;
use Illuminate\Support\Collection;

/**
 * EquipmentChecksService
 * 
 * Manages equipment checks during method sequence stage execution.
 * Handles tracking when equipment is turned on/off during analysis runs.
 */
class EquipmentChecksService
{
    /**
     * Start all equipment usage for a stage
     * Called when analyst starts running a stage
     * 
     * @param MethodSequenceRunStageData $stageData The stage data record
     * @param int|null $userId The ID of the user starting the stage
     */
    public function startStageEquipmentUsage(MethodSequenceRunStageData $stageData, ?int $userId = null): void
    {
        // Get all equipment usage records for this stage
        $equipmentUsages = $stageData->equipmentUsage;

        foreach ($equipmentUsages as $usage) {
            // Only mark as started if not already started
            if (!$usage->started_at) {
                $usage->markAsStarted($userId ?? auth()->id());
            }
        }
    }

    /**
     * Complete all equipment usage for a stage
     * Called when analyst completes a stage
     * 
     * @param MethodSequenceRunStageData $stageData The stage data record
     * @param int|null $userId The ID of the user completing the stage
     */
    public function completeStageEquipmentUsage(MethodSequenceRunStageData $stageData, ?int $userId = null): void
    {
        // Get all equipment usage records for this stage
        $equipmentUsages = $stageData->equipmentUsage;

        foreach ($equipmentUsages as $usage) {
            // Only mark as completed if already started but not yet completed
            if ($usage->started_at && !$usage->completed_at) {
                $usage->markAsCompleted($userId ?? auth()->id());
            }
        }
    }

    /**
     * Get equipment checks for a run
     * Returns all equipment usage records grouped by stage with timing info
     * 
     * @param int $runId The run ID
     * @return Collection
     */
    public function getRunEquipmentChecks(int $runId): Collection
    {
        return MethodSequenceRunStageData::where('run_id', $runId)
            ->with(['equipmentUsage.equipment', 'equipmentUsage.startedByUser', 'equipmentUsage.completedByUser'])
            ->get()
            ->flatMap(function ($stageData) {
                return $stageData->equipmentUsage->map(function ($usage) use ($stageData) {
                    return [
                        'stage_id' => $stageData->stage_id,
                        'stage_data_id' => $stageData->id,
                        'equipment_id' => $usage->equipment_id,
                        'equipment_name' => $usage->equipment_name,
                        'equipment' => $usage->equipment,
                        'started_at' => $usage->started_at,
                        'completed_at' => $usage->completed_at,
                        'duration_minutes' => $usage->getDurationMinutes(),
                        'duration_formatted' => $usage->getFormattedDuration(),
                        'started_by' => $usage->startedByUser,
                        'completed_by' => $usage->completedByUser,
                        'status' => $this->getEquipmentStatus($usage),
                    ];
                });
            });
    }

    /**
     * Get summary of equipment usage for a run
     * 
     * @param int $runId The run ID
     * @return array
     */
    public function getRunEquipmentChecksSummary(int $runId): array
    {
        $checks = $this->getRunEquipmentChecks($runId);

        return [
            'total_equipment_used' => $checks->unique('equipment_id')->count(),
            'equipment_instances' => $checks->count(),
            'completed' => $checks->filter(fn($c) => $c['status'] === 'completed')->count(),
            'in_progress' => $checks->filter(fn($c) => $c['status'] === 'in_progress')->count(),
            'not_started' => $checks->filter(fn($c) => $c['status'] === 'not_started')->count(),
            'total_duration_minutes' => $checks
                ->filter(fn($c) => $c['duration_minutes'] !== null)
                ->sum('duration_minutes'),
        ];
    }

    /**
     * Get all equipment check records for a specific equipment instance during a date range
     * Useful for generating equipment check history/logs
     * 
     * @param int $equipmentId
     * @param string $fromDate Format: YYYY-MM-DD
     * @param string $toDate Format: YYYY-MM-DD
     * @return Collection
     */
    public function getEquipmentCheckHistory(int $equipmentId, string $fromDate, string $toDate): Collection
    {
        return MethodSequenceStageEquipmentUsage::where('equipment_id', $equipmentId)
            ->whereNotNull('started_at')
            ->whereBetween('started_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->with(['stageData.run.analyst', 'startedByUser', 'completedByUser'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function ($usage) {
                return [
                    'date' => $usage->started_at?->format('Y-m-d'),
                    'time_started' => $usage->started_at?->format('H:i:s'),
                    'time_completed' => $usage->completed_at?->format('H:i:s'),
                    'duration' => $usage->getFormattedDuration(),
                    'duration_minutes' => $usage->getDurationMinutes(),
                    'analyst' => $usage->stageData?->run?->analyst?->name,
                    'started_by' => $usage->startedByUser?->name,
                    'completed_by' => $usage->completedByUser?->name,
                    'status' => $this->getEquipmentStatus($usage),
                ];
            });
    }

    /**
     * Get the status of an equipment usage record
     * 
     * @param MethodSequenceStageEquipmentUsage $usage
     * @return string One of: 'not_started', 'in_progress', 'completed'
     */
    private function getEquipmentStatus(MethodSequenceStageEquipmentUsage $usage): string
    {
        if (!$usage->started_at) {
            return 'not_started';
        }

        if (!$usage->completed_at) {
            return 'in_progress';
        }

        return 'completed';
    }
}
