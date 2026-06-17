<?php

namespace Database\Seeders;

use App\Company;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase1FoundationSeeder extends Seeder
{
    /**
     * Seed only global foundation records.
     *
     * Domain data is intentionally split into later phases:
     * locations, CRM, sample taxonomy, lab organization, parameters, samples,
     * analytical results, QC, personnel, equipment, and inventory.
     */
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 1 SEEDING: Foundation');
            $this->command?->info('====================================================');

            $uae = AmSpecSeedData::resolveUaeCountry();
            $brazil = AmSpecSeedData::resolveBrazilCountry();

            $company = Company::query()->find(AmSpecSeedData::DUBAI_COMPANY_ID);
            if (! $company && $uae) {
                $company = Company::query()->create(array_merge(
                    ['id' => AmSpecSeedData::DUBAI_COMPANY_ID],
                    AmSpecSeedData::dubaiCompanyAttributes($uae->id)
                ));

                $this->command?->info("Created base company: {$company->name}");
            } elseif ($company) {
                $this->command?->info("Using existing base company: {$company->name}");
            }

            if ($brazil && ! Company::query()->find(AmSpecSeedData::BRAZIL_COMPANY_ID)) {
                Company::query()->create(array_merge(
                    ['id' => AmSpecSeedData::BRAZIL_COMPANY_ID],
                    AmSpecSeedData::brazilCompanyAttributes($brazil->id)
                ));

                $this->command?->info('Created Brazil company: AmSpec Rio Crude Oil Center');
            }

            $templateUser = User::query()->where('active', 1)->first() ?? User::query()->first();
            if (! $templateUser) {
                $this->command?->warn('No users found. Later personnel/sample phases may require an imported template user.');
            } else {
                $this->command?->info("Template user available: {$templateUser->email}");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 1 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
