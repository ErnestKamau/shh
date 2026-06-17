<?php

namespace Database\Seeders;

use App\Company;
use App\Directorate;
use App\Lab;
use App\User;
use App\Zone;
use Database\Seeders\Concerns\AmSpecSeedData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase5LaboratoryOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 5 SEEDING: Laboratory Organization');
            $this->command?->info('====================================================');

            $company = Company::query()
                ->where('id', AmSpecSeedData::DUBAI_COMPANY_ID)
                ->orWhere('active', true)
                ->orderByDesc('active')
                ->first();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $zones = Zone::query()->get()->keyBy('key');
            foreach (array_keys(AmSpecSeedData::zoneNames()) as $code) {
                if (! $zones->has($code)) {
                    $this->command?->error("Zone {$code} not found. Run Phase 2 first.");
                    return;
                }
            }

            $activeUser = User::query()->where('active', 1)->first() ?? User::query()->first();
            $activeUserId = $activeUser?->id;

            $directorates = [];
            foreach (AmSpecSeedData::directorates() as $code => $data) {
                $directorate = Directorate::query()->updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $data['name'],
                        'head_id' => $activeUserId,
                        'section_head_user_id' => $activeUserId,
                        'zone_id' => $zones[$data['primary_zone']]->id,
                        'active' => true,
                    ]
                );

                $directorates[$code] = $directorate;
                $this->command?->info("Seeded Directorate: {$directorate->code} - {$directorate->name}");
            }

            foreach ($directorates as $directorate) {
                foreach ($zones as $zone) {
                    if ((string) $directorate->zone_id === (string) $zone->id) {
                        continue;
                    }

                    DB::connection('pgsql')->table('directorate_zone')->updateOrInsert(
                        [
                            'directorate_id' => $directorate->id,
                            'zone_id' => $zone->id,
                        ],
                        [
                            'id' => DB::connection('pgsql')->table('directorate_zone')
                                ->where('directorate_id', $directorate->id)
                                ->where('zone_id', $zone->id)
                                ->value('id') ?? (string) Str::uuid(),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            $emailDomain = AmSpecSeedData::LAB_EMAIL_DOMAIN;

            foreach (AmSpecSeedData::labTypes() as $data) {
                $directorate = $directorates[$data['directorate']];
                $primaryZoneKey = AmSpecSeedData::directorates()[$data['directorate']]['primary_zone'];
                $zone = $zones[$primaryZoneKey];

                $lab = Lab::query()->updateOrCreate(
                    [
                        'code' => $data['code'],
                        'company_id' => $company->id,
                    ],
                    [
                        'name' => $data['name'],
                        'directorate_id' => $directorate->id,
                        'address' => $zone->value,
                        'location' => $zone->value,
                        'email' => strtolower($data['code']).'@'.$emailDomain,
                        'company_id' => $company->id,
                        'zone_id' => $zone->id,
                        'manager_id' => $activeUserId,
                        'section_head_user_id' => $activeUserId,
                        'is_external' => false,
                        'phone1' => '+971 4 323 0399',
                        'active' => true,
                    ]
                );

                $this->command?->info("Seeded Lab: {$lab->code} - {$lab->name} ({$directorate->code}, {$zone->key})");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 5 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
