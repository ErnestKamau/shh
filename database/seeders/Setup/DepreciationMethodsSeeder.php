<?php

namespace Database\Seeders\Setup;

use App\Enums\Equipment\DepreciationMethodCode;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use Illuminate\Database\Seeder;

class DepreciationMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => DepreciationMethodCode::StraightLine->value,
                'name' => 'Straight-Line',
                'description' => 'Equal depreciation per period over useful life.',
                'default_rate' => null,
            ],
            [
                'code' => DepreciationMethodCode::DecliningBalance->value,
                'name' => 'Declining Balance',
                'description' => 'Accelerated depreciation with a fixed rate applied to book value.',
                'default_rate' => 20.0,
            ],
            [
                'code' => DepreciationMethodCode::UnitsOfProduction->value,
                'name' => 'Units of Production',
                'description' => 'Depreciation based on actual usage units.',
                'default_rate' => null,
            ],
            [
                'code' => DepreciationMethodCode::SumOfYearsDigits->value,
                'name' => "Sum-of-the-Years'-Digits (SYD)",
                'description' => 'Front-loaded depreciation using year-weighted fractions.',
                'default_rate' => null,
            ],
        ];

        foreach ($methods as $method) {
            DepreciationMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'description' => $method['description'],
                    'default_rate' => $method['default_rate'],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Depreciation methods seeded: ' . count($methods));
    }
}
