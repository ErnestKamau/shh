<?php

namespace Database\Seeders\Concerns;

use App\Company;

/**
 * Food pricelist seeding is owned by Phase 14 (three-pricelist model):
 * master per-parameter, customer per-parameter, and one package pricelist.
 *
 * Kept for backward-compatible Phase 13 wiring.
 */
trait SeedsAmSpecFoodPricelist
{
    use ClearsAmSpecFoodPricelistData;

    /**
     * @return array{pricelist: int, items: int, assignment: int}
     */
    protected function seedAmSpecFoodPricelist(Company $company): array
    {
        $this->clearAmSpecFoodPricelistData();

        $this->command?->info(
            'Skipping Phase 13 food pricelist. Phase 14 seeds master, customer (per-parameter), and one package pricelist.'
        );

        return [
            'pricelist' => 0,
            'items' => 0,
            'assignment' => 0,
        ];
    }
}
