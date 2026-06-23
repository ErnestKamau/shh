<?php

namespace Database\Seeders\Concerns;

use App\Company;
use Database\Seeders\Concerns\AmSpecSeedData;

trait ResolvesAmSpecCompany
{
    protected function resolveAmSpecCompany(): ?Company
    {
        return Company::query()
            ->where('id', AmSpecSeedData::DUBAI_COMPANY_ID)
            ->orWhere('active', true)
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [AmSpecSeedData::DUBAI_COMPANY_ID])
            ->first();
    }
}
