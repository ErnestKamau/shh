<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated-remove TRF_LAYER_MANIFEST.md Phase 0
 * Replaced by SubmissionFormTrfWasteWaterSeeder.
 */
class TestRequestFormWasteWaterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SubmissionFormTrfWasteWaterSeeder::class);
    }
}
