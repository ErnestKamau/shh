<?php

namespace App\Services\Equipment\Depreciation;

use App\Enums\Equipment\DepreciationStatus;
use App\Models\Equipments\Depreciation\DepreciationSchedule;
use App\Models\Equipments\Depreciation\EquipmentAppraisal;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use Illuminate\Support\Collection;

class DepreciationReportService
{
    /**
     * @return Collection<int, EquipmentDepreciationConfig>
     */
    public function assetRegister(): Collection
    {
        return EquipmentDepreciationConfig::query()
            ->with(['equipment', 'method'])
            ->where('enable_depreciation', true)
            ->orderBy('equipment_id')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function monthlyDepreciation(string $year, string $month): Collection
    {
        $label = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        return DepreciationSchedule::query()
            ->with(['equipment', 'version.config'])
            ->where('period_label', $label)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function yearlySummary(string $year): array
    {
        $schedules = DepreciationSchedule::query()
            ->whereYear('period_date', $year)
            ->get();

        return [
            'year' => $year,
            'total_depreciation' => $schedules->sum('depreciation_amount'),
            'periods' => $schedules->count(),
        ];
    }

    /**
     * @return Collection<int, EquipmentDepreciationConfig>
     */
    public function bookValueReport(): Collection
    {
        return $this->assetRegister();
    }

    /**
     * @return Collection<int, EquipmentAppraisal>
     */
    public function appraisalAdjustments(): Collection
    {
        return EquipmentAppraisal::query()
            ->with(['equipment', 'config'])
            ->orderByDesc('appraisal_date')
            ->get();
    }

    /**
     * @return Collection<int, EquipmentDepreciationConfig>
     */
    public function fullyDepreciated(): Collection
    {
        return EquipmentDepreciationConfig::query()
            ->with(['equipment', 'method'])
            ->where('status', DepreciationStatus::FullyDepreciated->value)
            ->get();
    }

    /**
     * @return Collection<int, DepreciationSchedule>
     */
    public function forecast(): Collection
    {
        return DepreciationSchedule::query()
            ->with(['equipment'])
            ->where('is_posted', false)
            ->whereDate('period_date', '>=', now())
            ->orderBy('period_date')
            ->limit(500)
            ->get();
    }
}
