<?php

namespace App\DTOs\Monitoring;

class CalibrationSnapshotData
{
    public function __construct(
        public readonly ?string $calibrationLogId,
        public readonly ?float $correctionFactor,
        public readonly ?float $uncertaintyOfMeasure,
        public readonly ?string $calibrationDate,
        public readonly ?string $calibrationCertificate,
        public readonly ?string $standardUsed,
        public readonly array $snapshotPayload = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'calibration_log_id' => $this->calibrationLogId,
            'correction_factor' => $this->correctionFactor,
            'uncertainty_of_measure' => $this->uncertaintyOfMeasure,
            'calibration_date' => $this->calibrationDate,
            'calibration_certificate' => $this->calibrationCertificate,
            'standard_used' => $this->standardUsed,
            'snapshot_payload' => $this->snapshotPayload,
        ];
    }
}
