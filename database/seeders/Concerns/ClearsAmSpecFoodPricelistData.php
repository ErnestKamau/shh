<?php

namespace Database\Seeders\Concerns;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;

trait ClearsAmSpecFoodPricelistData
{
    public const FOOD_PRICELIST_CODE = 'PL-FOOD-ADNOC-001';

    protected function clearAmSpecFoodPricelistData(): void
    {
        $pricelist = Pricelist::query()
            ->where('code', self::FOOD_PRICELIST_CODE)
            ->first();

        if ($pricelist === null) {
            $this->command?->info('No AmSpec food pricelist to clear.');

            return;
        }

        $deletedItems = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->delete();

        $deletedAssignments = PricelistCustomer::query()
            ->where('pricelist_id', $pricelist->id)
            ->delete();

        $pricelist->delete();

        $this->command?->info(sprintf(
            'Cleared AmSpec food pricelist %s (%d items, %d customer assignments).',
            self::FOOD_PRICELIST_CODE,
            $deletedItems,
            $deletedAssignments,
        ));
    }
}
