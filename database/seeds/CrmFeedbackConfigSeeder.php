<?php

namespace Database\Seeders;

use App\Models\CRM\CrmFeedbackConfig;
use Illuminate\Database\Seeder;

class CrmFeedbackConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (CrmFeedbackConfig::count() === 0) {
            CrmFeedbackConfig::create([
                'lowest_rating_threshold' => 1.50,
            ]);
        }
    }
}
