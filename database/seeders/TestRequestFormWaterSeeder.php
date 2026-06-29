<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated-remove TRF_LAYER_MANIFEST.md Phase 0
 * Replaced by SubmissionFormTrfWaterSeeder.
 */
class TestRequestFormWaterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SubmissionFormTrfWaterSeeder::class);
    }
}
