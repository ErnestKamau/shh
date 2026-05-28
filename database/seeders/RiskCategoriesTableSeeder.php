<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskManagement\RiskCategory;

class RiskCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Operational Risk',
                'code' => 'OPR',
                'description' => 'Risks related to day-to-day operations and processes',
                'is_active' => true,
                'company_id' => null,
            ],
            [
                'name' => 'Financial Risk',
                'code' => 'FIN',
                'description' => 'Risks related to financial performance and resources',
                'is_active' => true,
                'company_id' => null,
            ],
            [
                'name' => 'Strategic Risk',
                'code' => 'STR',
                'description' => 'Risks related to strategic objectives and business direction',
                'is_active' => true,
                'company_id' => null,
            ],
            [
                'name' => 'Compliance Risk',
                'code' => 'COM',
                'description' => 'Risks related to regulatory compliance and legal requirements',
                'is_active' => true,
                'company_id' => null,
            ],
            [
                'name' => 'Reputational Risk',
                'code' => 'REP',
                'description' => 'Risks related to brand reputation and public perception',
                'is_active' => true,
                'company_id' => null,
            ],
            [
                'name' => 'Technology Risk',
                'code' => 'TEC',
                'description' => 'Risks related to IT systems, cybersecurity, and technology infrastructure',
                'is_active' => true,
                'company_id' => null,
            ],
            [
                'name' => 'Health & Safety Risk',
                'code' => 'HSE',
                'description' => 'Risks related to health, safety, and environmental concerns',
                'is_active' => true,
                'company_id' => null,
            ],
            [
                'name' => 'Quality Risk',
                'code' => 'QAL',
                'description' => 'Risks related to product or service quality and standards',
                'is_active' => true,
                'company_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            RiskCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}

