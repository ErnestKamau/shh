<?php

namespace Database\Seeders\Concerns;

use App\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ClearsAmSpecLocationData
{
    protected function clearAmSpecLocationData(Company $company): void
    {
        $locationNames = collect(AmSpecSeedData::zoneLocations())->pluck('name')->all();

        $deletedSamplePoints = 0;
        if (Schema::connection('pgsql')->hasTable('sample_points')) {
            $query = DB::connection('pgsql')->table('sample_points');
            $query->where(function ($builder) use ($locationNames): void {
                foreach ($locationNames as $name) {
                    $builder->orWhere('name', 'like', $name.'%');
                }
            });
            $deletedSamplePoints = $query->delete();
        }

        $deletedLocations = DB::connection('pgsql')
            ->table('inventory_locations')
            ->where('company_id', $company->id)
            ->whereIn('name', $locationNames)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared AmSpec location data: %d inventory locations, %d sample points (zones are upserted by key).',
            $deletedLocations,
            $deletedSamplePoints,
        ));
    }
}
