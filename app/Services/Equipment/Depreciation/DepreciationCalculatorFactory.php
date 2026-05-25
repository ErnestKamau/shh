<?php

namespace App\Services\Equipment\Depreciation;

use App\Enums\Equipment\DepreciationMethodCode;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use InvalidArgumentException;

class DepreciationCalculatorFactory
{
    public function forConfig(EquipmentDepreciationConfig $config): DepreciationCalculatorContract
    {
        $code = $this->resolveMethodCode($config);

        return match ($code) {
            DepreciationMethodCode::StraightLine => app(StraightLineCalculator::class),
            DepreciationMethodCode::DecliningBalance => app(DecliningBalanceCalculator::class),
            DepreciationMethodCode::UnitsOfProduction => app(UnitsOfProductionCalculator::class),
            DepreciationMethodCode::SumOfYearsDigits => app(SumOfYearsDigitsCalculator::class),
        };
    }

    protected function resolveMethodCode(EquipmentDepreciationConfig $config): DepreciationMethodCode
    {
        $method = $config->relationLoaded('method') ? $config->method : $config->method()->first();

        if (! $method) {
            throw new InvalidArgumentException('Depreciation method is required.');
        }

        $code = $method->code;

        return $code instanceof DepreciationMethodCode
            ? $code
            : DepreciationMethodCode::from((string) $code);
    }
}
