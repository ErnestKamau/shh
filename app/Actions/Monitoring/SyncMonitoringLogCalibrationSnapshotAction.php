<?php

namespace App\Actions\Monitoring;

use App\Models\Monitoring\MonitoringLog;
use App\Services\Monitoring\CalibrationSnapshotService;

class SyncMonitoringLogCalibrationSnapshotAction
{
    public function __construct(
        protected CalibrationSnapshotService $calibrationSnapshotService,
    ) {
    }

    public function execute(MonitoringLog $log, ?string $equipmentId): void
    {
        $log->calibrationSnapshots()->delete();

        if (blank($equipmentId)) {
            return;
        }

        $snapshot = $this->calibrationSnapshotService->latestForEquipment($equipmentId);

        if ($snapshot === null) {
            return;
        }

        $log->calibrationSnapshots()->create(array_merge([
            'equipment_id' => $equipmentId,
        ], $snapshot->toArray()));
    }
}
