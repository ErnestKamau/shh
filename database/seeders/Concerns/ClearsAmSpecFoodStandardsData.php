<?php

namespace Database\Seeders\Concerns;

use App\StandardAnalytes;
use App\Standards;

trait ClearsAmSpecFoodStandardsData
{
    public const FOOD_STANDARD_CODE = 'STD-FOOD-REG';

    protected function clearAmSpecFoodStandardsData(): void
    {
        $standard = Standards::query()
            ->where('code', self::FOOD_STANDARD_CODE)
            ->first();

        if ($standard === null) {
            $this->command?->info('No AmSpec food standard to clear.');

            return;
        }

        $deletedAnalytes = StandardAnalytes::query()
            ->where('standard_id', $standard->id)
            ->delete();

        $standard->delete();

        $this->command?->info(sprintf(
            'Cleared AmSpec food standard %s (%d standard analyte rows).',
            self::FOOD_STANDARD_CODE,
            $deletedAnalytes,
        ));
    }
}
