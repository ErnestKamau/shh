<?php

namespace Database\Seeders;

use App\Zone;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\ClearsAmSpecLocationData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Phase2LocationSeeder extends Seeder
{
    use ClearsAmSpecLocationData;
    use ResolvesAmSpecCompany;

    public const ZONE_LOCATIONS = AmSpecSeedData::ZONE_LOCATIONS;

    public const ZONES = AmSpecSeedData::ZONE_NAMES;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 2 SEEDING: Locations / Zones');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $this->clearAmSpecLocationData($company);

            foreach (AmSpecSeedData::zoneLocations() as $code => $location) {
                $name = $location['name'];
                $inventoryLocation = DB::connection('pgsql')
                    ->table('inventory_locations')
                    ->where('name', $name)
                    ->first();

                $inventoryLocationId = $inventoryLocation?->id ?? (string) Str::uuid();
                DB::connection('pgsql')->table('inventory_locations')->updateOrInsert(
                    ['id' => $inventoryLocationId],
                    [
                        'name' => $name,
                        'level' => 1,
                        'inventory_location_id' => null,
                        'active' => 1,
                        'company_id' => $company->id,
                        'updated_at' => now(),
                        'created_at' => $inventoryLocation?->created_at ?? now(),
                    ]
                );

                Zone::query()->updateOrCreate(
                    ['key' => $code],
                    [
                        'value' => $name,
                        'description' => $this->zoneDescription($location),
                        'module' => 'laboratory',
                        'inventory_location_id' => $inventoryLocationId,
                        'is_hq_zone' => $code === 'CZO',
                    ]
                );

                $this->command?->info("Seeded Zone: {$code} - {$name}");
            }

            $this->seedZoneSamplePoints();

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 2 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function seedZoneSamplePoints(): void
    {
        if (! Schema::connection('pgsql')->hasTable('sample_points') || ! Schema::connection('pgsql')->hasTable('crm_company_units')) {
            return;
        }

        $units = DB::connection('pgsql')
            ->table('crm_company_units')
            ->select('id', 'crm_customer_id', 'name')
            ->orderBy('name')
            ->get();

        if ($units->isEmpty()) {
            $this->command?->info('CRM units not available yet; zone sample points will be created when Phase 2 is rerun after CRM seeding.');
            return;
        }

        $prefix = AmSpecSeedData::REFERENCE_PREFIX;

        foreach ($units as $unit) {
            foreach (AmSpecSeedData::zoneLocations() as $code => $location) {
                $pointName = "{$location['name']} - {$unit->name}";
                $existing = DB::connection('pgsql')
                    ->table('sample_points')
                    ->where('crm_company_unit_id', $unit->id)
                    ->where('name', $pointName)
                    ->first();

                $payload = [
                    'crm_company_unit_id' => $unit->id,
                    'crm_customer_id' => $unit->crm_customer_id,
                    'name' => $pointName,
                    'description' => $this->zoneDescription($location),
                    'gps' => $location['gps'],
                    'active' => 1,
                    'updated_at' => now(),
                    'created_at' => $existing?->created_at ?? now(),
                ];

                if (Schema::connection('pgsql')->hasColumn('sample_points', 'code')) {
                    $payload['code'] = $prefix.'-'.$code.'-'.substr(md5((string) $unit->id), 0, 8);
                }

                DB::connection('pgsql')->table('sample_points')->updateOrInsert(
                    ['id' => $existing?->id ?? (string) Str::uuid()],
                    $payload
                );
            }
        }
    }

    private function zoneDescription(array $location): string
    {
        return sprintf(
            '%s office at %s. Regions served: %s.',
            $location['name'],
            $location['office'],
            implode(', ', $location['regions'])
        );
    }
}
