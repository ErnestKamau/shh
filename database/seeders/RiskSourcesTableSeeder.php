<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskManagement\RiskSource;

class RiskSourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sources = [
            [
                'name' => 'Internal Audit',
                'code' => 'AUD',
                'description' => 'Risks identified through internal audit processes',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'External Audit',
                'code' => 'EXT',
                'description' => 'Risks identified through external audit processes',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Non-Conformance',
                'code' => 'NC',
                'description' => 'Risks identified from non-conformance reports',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Customer Complaint',
                'code' => 'CC',
                'description' => 'Risks identified from customer complaints',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Management Review',
                'code' => 'MR',
                'description' => 'Risks identified during management review meetings',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Incident Report',
                'code' => 'IR',
                'description' => 'Risks identified from incident reports',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Risk Assessment',
                'code' => 'RA',
                'description' => 'Risks identified through formal risk assessment activities',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Change Management',
                'code' => 'CM',
                'description' => 'Risks identified during change management processes',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Equipment Failure',
                'code' => 'EF',
                'description' => 'Risks identified from equipment failures or malfunctions',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Personnel Issue',
                'code' => 'PI',
                'description' => 'Risks identified from personnel-related issues',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Regulatory Change',
                'code' => 'RC',
                'description' => 'Risks identified from changes in regulations or standards',
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Other',
                'code' => 'OTH',
                'description' => 'Risks identified from other sources',
                'is_active' => true,
                'company_id' => 0,
            ],
        ];

        foreach ($sources as $source) {
            RiskSource::updateOrCreate(
                ['code' => $source['code'], 'company_id' => $source['company_id']],
                $source
            );
        }
    }
}

