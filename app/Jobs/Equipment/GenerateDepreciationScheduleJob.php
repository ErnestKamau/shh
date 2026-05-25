<?php

namespace App\Jobs\Equipment;

use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\DepreciationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDepreciationScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $configId,
        public ?string $userId = null,
        public string $reason = 'initial'
    ) {}

    public function handle(DepreciationService $service): void
    {
        $config = EquipmentDepreciationConfig::query()
            ->with('method')
            ->findOrFail($this->configId);

        if (! $config->enable_depreciation) {
            return;
        }

        $service->generateSchedule($config, $this->reason, $this->userId);
    }
}
