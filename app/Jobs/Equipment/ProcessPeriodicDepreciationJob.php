<?php

namespace App\Jobs\Equipment;

use App\Enums\Equipment\DepreciationStatus;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\DepreciationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPeriodicDepreciationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $configId = null
    ) {}

    public function handle(DepreciationService $service): void
    {
        $query = EquipmentDepreciationConfig::query()
            ->where('enable_depreciation', true)
            ->whereIn('status', [
                DepreciationStatus::Active->value,
                DepreciationStatus::Pending->value,
            ]);

        if ($this->configId) {
            $query->where('id', $this->configId);
        }

        $query->each(function (EquipmentDepreciationConfig $config) use ($service): void {
            $config->load('activeScheduleVersion');
            $service->postDueLedgerEntries($config);
        });
    }
}
