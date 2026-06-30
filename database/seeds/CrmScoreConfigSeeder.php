<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CRM\CrmScoreConfig;

class CrmScoreConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (CrmScoreConfig::count() === 0) {
            CrmScoreConfig::create([
                'base_score' => 50,
                'sample_weight' => 10,
                'feedback_weight' => 5,
                'complaint_weight' => -5,
                'account_registry_weight' => 10,
            ]);
        }
    }
}
