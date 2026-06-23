<?php

namespace Database\Seeders;

use App\Company;
use App\SampleType;
use Database\Seeders\Concerns\ClearsAmSpecTaxonomyData;
use Database\Seeders\Concerns\ReadsAmSpecParametersSpreadsheet;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase4SampleTaxonomySeeder extends Seeder
{
    use ClearsAmSpecTaxonomyData;
    use ReadsAmSpecParametersSpreadsheet;
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 4 SEEDING: Sample Taxonomy (AmSpec seed data)');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $this->clearAmSpecTaxonomyData($company);

            $sampleTypes = $this->uniqueAmSpecSampleTypes();

            if ($sampleTypes->isEmpty()) {
                $this->command?->error('No sample types found in AmSpec parameter seed data.');

                return;
            }

            foreach ($sampleTypes as $data) {
                $sampleType = SampleType::query()->updateOrCreate(
                    ['code' => $data['code'], 'company_id' => $company->id],
                    [
                        'name' => $data['name'],
                        'description' => $data['name'].' samples',
                        'active' => true,
                        'is_results_attachable' => true,
                    ]
                );

                $this->command?->info("Seeded Sample Type: {$sampleType->code} - {$sampleType->name}");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 4 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info("Seeded {$sampleTypes->count()} sample types from seed data.");
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
