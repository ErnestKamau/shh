<?php

namespace App\Console\Commands;

use App\Company;
use App\Models\CRM\CRMCustomer;
use App\Services\Commercial\AmSpecRebrandService;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Phase10AnalyticalResultsSeeder;
use Database\Seeders\Phase11QcAnalyticsSeeder;
use Database\Seeders\Phase2LocationSeeder;
use Database\Seeders\Phase3CrmMasterDataSeeder;
use Database\Seeders\Phase5LaboratoryOrganizationSeeder;
use Database\Seeders\Phase6PersonnelLabInsightsSeeder;
use Database\Seeders\Phase9SampleWorkflowSeeder;
use Database\Seeders\Setup\Languages\MasLanguageDatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RebrandAmSpecSeedData extends Command
{
    protected $signature = 'amspec:rebrand-seed-data
                            {--force : Confirm destructive CRM cleanup and company reset}';

    protected $description = 'Rebrand seed data to AmSpec Dubai (active) + Brazil Rio, and refresh CRM/lab demo data';

    public function handle(AmSpecRebrandService $rebrandService): int
    {
        if (! $this->option('force')) {
            $this->error('This command updates companies and purges legacy CRM customers.');
            $this->line('Run with --force to proceed.');

            return self::FAILURE;
        }

        config(['database.default' => 'pgsql']);
        Model::unguard();

        try {
            DB::connection('pgsql')->transaction(function () use ($rebrandService): void {
                $this->info('Step 1/4: Rebranding companies...');
                $rebrandService->rebrandCompanies($this);

                $dubaiId = AmSpecSeedData::DUBAI_COMPANY_ID;
                $this->info('Step 2/4: Purging legacy CRM data...');
                $rebrandService->purgeLegacyCrm($dubaiId, $this);

                $this->info('Step 3/4: Running AmSpec phase seeders...');
                foreach ([
                    Phase2LocationSeeder::class,
                    Phase3CrmMasterDataSeeder::class,
                    Phase5LaboratoryOrganizationSeeder::class,
                    Phase6PersonnelLabInsightsSeeder::class,
                    Phase9SampleWorkflowSeeder::class,
                    Phase10AnalyticalResultsSeeder::class,
                    Phase11QcAnalyticsSeeder::class,
                    MasLanguageDatabaseSeeder::class,
                ] as $seederClass) {
                    $this->runSeeder($seederClass);
                }

                $this->info('Step 4/4: Summary');
            });
        } catch (\Throwable $exception) {
            $this->error('Rebrand failed: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            Model::reguard();
        }

        $companies = Company::query()->orderByDesc('active')->get(['id', 'name', 'active']);
        $this->table(['ID', 'Name', 'Active'], $companies->map(fn (Company $c) => [
            $c->id,
            $c->name,
            $c->active ? 'yes' : 'no',
        ])->all());

        $customerCount = CRMCustomer::query()
            ->where('company_id', AmSpecSeedData::DUBAI_COMPANY_ID)
            ->count();

        $this->info("CRM customers (Dubai): {$customerCount}");
        $this->newLine();
        $this->line('Verification checklist:');
        $this->line('  1. Open /companies — expect 2 companies, Dubai active');
        $this->line('  2. CRM → Customers — 9 AmSpec-sector clients, no Tanzania names');
        $this->line('  3. CRM → Contacts — Gulf/international contact names');
        $this->line('  4. Reports/quotations — active company shows AmSpec Dubai details');
        $this->line('  5. Upload logo manually via Companies UI when ready');

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Seeder>  $seederClass
     */
    private function runSeeder(string $seederClass): void
    {
        $this->line('  → '.class_basename($seederClass));

        /** @var Seeder $seeder */
        $seeder = $this->laravel->make($seederClass);
        $seeder->setCommand($this);
        $seeder->run();
    }
}
