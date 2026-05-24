<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskManagement\RiskBusinessProcess;

class RiskBusinessProcessSeeder extends Seeder
{
    public function run()
    {
        $processes = [
            'Production',
            'Quality Control',
            'Human Resources',
            'Finance',
            'IT & Security',
            'Supply Chain',
            'Sales & Marketing',
            'Customer Service',
            'Maintenance',
            'Administration',
        ];

        foreach ($processes as $name) {
            RiskBusinessProcess::firstOrCreate(
                ['name' => $name],
                ['company_id' => null]
            );
        }
    }
}
