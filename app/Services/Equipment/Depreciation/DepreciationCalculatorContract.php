<?php

namespace App\Services\Equipment\Depreciation;

use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;

interface DepreciationCalculatorContract
{
    /**
     * @return array<int, array{
     *   period_label: string,
     *   period_date: \Carbon\CarbonInterface,
     *   period_index: int,
     *   opening_book_value: float,
     *   depreciation_amount: float,
     *   accumulated_depreciation: float,
     *   closing_book_value: float
     * }>
     */
    public function generateSchedule(EquipmentDepreciationConfig $config): array;
}
