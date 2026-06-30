<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated-remove TRF_LAYER_MANIFEST.md Phase 0
 * Replaced by LabAnalysisAcceptanceFormSeeder.
 */
class GCLAF03FormSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LabAnalysisAcceptanceFormSeeder::class);
    }
}
