<?php

namespace App\Console\Support;

use Database\Seeders\Concerns\AmSpecSeedData;

class AmSpecCompanyCleanupTargets
{
    /**
     * Legacy inactive companies to remove.
     *
     * @return list<string>
     */
    public static function companyIds(): array
    {
        return [
            AmSpecSeedData::DUBAI_COMPANY_ID, // AmSpec (Dubai legacy row, currently named "AmSpec")
            AmSpecSeedData::BRAZIL_COMPANY_ID, // AmSpec Rio Crude Oil Center
        ];
    }

    /**
     * @return list<string>
     */
    public static function companyEmails(): array
    {
        return [
            'info@amspecgroup.com',
            'brazilcrude@amspecgroup.com',
        ];
    }

    /**
     * @return list<string>
     */
    public static function companyNames(): array
    {
        return [
            'AmSpec',
            'AmSpec Rio Crude Oil Center',
        ];
    }
}
