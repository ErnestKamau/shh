<?php

namespace Database\Seeders\Concerns;

use App\Company;
use Illuminate\Support\Facades\DB;

trait ClearsAmSpecEquipmentData
{
    protected function clearAmSpecEquipmentData(Company $company): void
    {
        $deleted = DB::connection('pgsql')
            ->table('equipment')
            ->where('company_id', $company->id)
            ->where('equipment_number', 'like', 'EQ-%')
            ->delete();

        $this->command?->info("Cleared {$deleted} AmSpec seeded equipment record(s).");
    }
}
