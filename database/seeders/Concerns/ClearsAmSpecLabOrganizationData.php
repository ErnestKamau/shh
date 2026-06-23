<?php

namespace Database\Seeders\Concerns;

use App\Company;
use App\Directorate;
use App\Lab;
use Illuminate\Support\Facades\DB;

trait ClearsAmSpecLabOrganizationData
{
    protected function clearAmSpecLabOrganizationData(Company $company): void
    {
        $labCodes = array_column(AmSpecSeedData::labTypes(), 'code');
        $directorateCodes = array_keys(AmSpecSeedData::directorates());

        $labIds = Lab::query()
            ->where('company_id', $company->id)
            ->whereIn('code', $labCodes)
            ->pluck('id');

        $directorateIds = Directorate::query()
            ->whereIn('code', $directorateCodes)
            ->pluck('id');

        $deletedRelations = 0;
        if ($directorateIds->isNotEmpty() && DB::connection('pgsql')->getSchemaBuilder()->hasTable('directorate_zone')) {
            $deletedRelations = DB::connection('pgsql')
                ->table('directorate_zone')
                ->whereIn('directorate_id', $directorateIds)
                ->delete();
        }

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

        $deletedDirectorates = 0;
        if ($directorateIds->isNotEmpty()) {
            $deletedDirectorates = Directorate::query()
                ->whereIn('id', $directorateIds)
                ->delete();
        }

        $this->command?->info(sprintf(
            'Cleared AmSpec lab organization data: %d labs, %d directorates, %d zone relations.',
            $deletedLabs,
            $deletedDirectorates,
            $deletedRelations,
        ));
    }
}
