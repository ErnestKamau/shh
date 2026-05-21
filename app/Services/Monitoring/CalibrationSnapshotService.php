<?php

namespace App\Services\Monitoring;

use App\DTOs\Monitoring\CalibrationSnapshotData;
use App\Models\Equipments\MaintainanceCalibrationLog;

class CalibrationSnapshotService
{
    public function latestForEquipment(?string $equipmentId): ?CalibrationSnapshotData
    {
        if ($equipmentId === null || $equipmentId === '') {
            return null;
        }

        $log = MaintainanceCalibrationLog::query()
            ->where('equipment_id', $equipmentId)
            ->whereIn('type', ['Calibration', 'calibration'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->first();

        if (! $log) {
            return null;
        }

        return new CalibrationSnapshotData(
            calibrationLogId: $log->id,
            correctionFactor: $log->correction_factor !== null ? (float) $log->correction_factor : null,
            uncertaintyOfMeasure: $log->uncertainty_of_measure !== null ? (float) $log->uncertainty_of_measure : null,
            calibrationDate: $log->date,
            calibrationCertificate: $log->certificate,
            standardUsed: $log->reference_number,
            snapshotPayload: [
                'service_provider' => $log->service_provider,
                'notes' => $log->notes,
                'overseen_by' => $log->overseen_by,
            ],
        );
    }
}
