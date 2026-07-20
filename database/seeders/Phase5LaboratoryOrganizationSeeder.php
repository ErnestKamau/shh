<?php

namespace Database\Seeders;

use App\Lab;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\ClearsAmSpecLabOrganizationData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase5LaboratoryOrganizationSeeder extends Seeder
{
    use ClearsAmSpecLabOrganizationData;
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 5 SEEDING: Laboratory Organization');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $this->clearAmSpecLabOrganizationData($company);

            $activeUser = User::query()->where('active', 1)->first() ?? User::query()->first();
            $activeUserId = $activeUser?->id;
            $emailDomain = AmSpecSeedData::LAB_EMAIL_DOMAIN;

            foreach (AmSpecSeedData::labTypes() as $data) {
                $lab = Lab::query()->updateOrCreate(
                    [
                        'code' => $data['code'],
                        'company_id' => $company->id,
                    ],
                    [
                        'name' => $data['name'],
                        'address' => $data['name'].' Facility',
                        'location' => $data['name'].' Location',
                        'email' => strtolower($data['code']).'@'.$emailDomain,
                        'company_id' => $company->id,
                        'manager_id' => $activeUserId,
                        'section_head_user_id' => $activeUserId,
                        'is_external' => false,
                        'phone1' => '+971 4 323 0399',
                        'active' => true,
                    ]
                );

                $this->command?->info("Seeded Lab: {$lab->code} - {$lab->name}");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 5 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
