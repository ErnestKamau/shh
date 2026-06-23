<?php

namespace Database\Seeders\Concerns;

use App\StandardValue;
use App\Standards;

trait ClearsAmSpecStandardLookupData
{
    /** @var list<string> */
    public const ALLOWED_STANDARD_VALUE_CODES = [
        'IsValue',
        'Not Detectable',
        'Not Specified',
    ];

    protected function clearAmSpecStandardLookupData(): void
    {
        $deletedStandards = Standards::query()->delete();

        $deletedValues = StandardValue::query()
            ->whereNotIn('code', self::ALLOWED_STANDARD_VALUE_CODES)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared standard lookup data: %d standard(s), %d standard value(s) removed (kept: %s).',
            $deletedStandards,
            $deletedValues,
            implode(', ', self::ALLOWED_STANDARD_VALUE_CODES),
        ));
    }
}
