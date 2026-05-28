<?php

namespace App\Jobs\Equipment;

use App\Enums\Equipment\AppraisalStatus;
use App\Models\Equipments\Depreciation\EquipmentAppraisal;
use App\Services\Equipment\Depreciation\DepreciationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEquipmentAppraisalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $appraisalId,
        public ?string $userId = null
    ) {}

    public function handle(DepreciationService $service): void
    {
        $appraisal = EquipmentAppraisal::query()->findOrFail($this->appraisalId);

        if ($appraisal->status !== AppraisalStatus::Approved) {
            return;
        }

        $service->processAppraisal($appraisal, $this->userId);
    }
}
