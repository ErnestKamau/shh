<?php

namespace Database\Seeders;

use App\Company;
use App\Country;
use App\User;
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

            $tanzania = Country::query()->where('name', 'like', '%Tanzania%')->first()
                ?? Country::query()->where('name', 'like', '%Kenya%')->first()
                ?? Country::query()->first();

            $company = Company::query()->first();
            if (! $company) {
                $company = Company::query()->create([
                    'name' => 'Government Chemist Laboratory Authority',
                    'logo' => '/images/no-logo.png',
                    'location' => 'Dar es Salaam, Tanzania',
                    'address' => 'P.O. Box 164, Dar es Salaam',
                    'country_id' => $tanzania?->id,
                    'website' => 'https://www.gcla.go.tz',
                    'email' => 'gcla@gcla.go.tz',
                    'cell_phone' => '+255222113383',
                    'telephone' => '+255222113384',
                    'street' => 'Luthuli Street',
                    'active' => true,
                    'show_on_reports' => true,
                ]);

                $this->command?->info("Created base company: {$company->name}");
            } else {
                $this->command?->info("Using existing base company: {$company->name}");
            }

            $templateUser = User::query()->where('active', 1)->first() ?? User::query()->first();
            if (! $templateUser) {
                $this->command?->warning('No users found. Later personnel/sample phases may require an imported template user.');
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
