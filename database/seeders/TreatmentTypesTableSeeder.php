<?php

namespace Database\Seeders;

use App\Models\RiskManagement\TreatmentType;
use Illuminate\Database\Seeder;

class TreatmentTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Avoid',
                'code' => 'AVOID',
                'description' => 'Eliminate the risk by removing the source or activity',
            ],
            [
                'name' => 'Mitigate',
                'code' => 'MITIGATE',
                'description' => 'Reduce likelihood or severity through controls',
            ],
            [
                'name' => 'Transfer',
                'code' => 'TRANSFER',
                'description' => 'Shift risk to a third party (e.g. insurance, outsourcing)',
            ],
            [
                'name' => 'Accept',
                'code' => 'ACCEPT',
                'description' => 'Acknowledge and monitor the risk within tolerance',
            ],
        ];

        foreach ($types as $type) {
            TreatmentType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                    'company_id' => null,
                ]
            );
        }
    }
}
