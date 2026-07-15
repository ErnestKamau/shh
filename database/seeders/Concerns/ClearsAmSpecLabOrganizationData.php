<?php

namespace Database\Seeders\Concerns;

use App\Company;
use App\Lab;
use Illuminate\Support\Facades\DB;

trait ClearsAmSpecLabOrganizationData
{
    protected function clearAmSpecLabOrganizationData(Company $company): void
    {
        $labCodes = array_column(AmSpecSeedData::labTypes(), 'code');

        $labIds = Lab::query()
            ->where('company_id', $company->id)
            ->whereIn('code', $labCodes)
            ->pluck('id');

        $deletedLabs = 0;
        if ($labIds->isNotEmpty()) {
            if (DB::connection('pgsql')->getSchemaBuilder()->hasTable('equipment')) {
                DB::connection('pgsql')
                    ->table('equipment')
                    ->whereIn('lab_id', $labIds)
                    ->update(['lab_id' => null]);
            }

            $deletedLabs = Lab::query()
                ->whereIn('id', $labIds)
                ->delete();
        }

        $this->command?->info(sprintf(
            'Cleared AmSpec lab organization data: %d labs.',
            $deletedLabs,
        ));
    }
}
