<?php

namespace Database\Seeders\Concerns;

use App\Company;
use Database\Seeders\Concerns\AmSpecSeedData;

trait ResolvesAmSpecCompany
{
    protected function resolveAmSpecCompany(): ?Company
    {
        $preferredCodes = [
            AmSpecSeedData::DUBAI_COMPANY_CODE,
            AmSpecSeedData::BRAZIL_COMPANY_CODE,
        ];

        $byCode = Company::query()
            ->whereIn('code', $preferredCodes)
            ->orderByRaw('CASE WHEN code = ? THEN 0 WHEN code = ? THEN 1 ELSE 2 END', $preferredCodes)
            ->first();

        if ($byCode !== null) {
            return $byCode;
        }

        $preferredIds = [
            AmSpecSeedData::DUBAI_COMPANY_ID,
            AmSpecSeedData::BRAZIL_COMPANY_ID,
        ];

        $byPreferredId = Company::query()
            ->whereIn('id', $preferredIds)
            ->orderByRaw('CASE WHEN id = ? THEN 0 WHEN id = ? THEN 1 ELSE 2 END', $preferredIds)
            ->first();

        if ($byPreferredId !== null) {
            return $byPreferredId;
        }

        return Company::query()
            ->where('active', true)
            ->orderBy('created_at')
            ->first()
            ?? Company::query()->orderBy('created_at')->first();
    }
}
