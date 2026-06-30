<?php

namespace Database\Seeders;

use App\AnalysisElements;
use App\AnalysisType;
use App\Company;
use App\SampleType;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Database\Seeders\Concerns\SeedsFoodAndFeedTaxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodAndFeedTaxonomySeeder extends Seeder
{
    use ResolvesAmSpecCompany;
    use SeedsFoodAndFeedTaxonomy;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('SEEDING: Food + Food and Feed taxonomy');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $stats = $this->seedFoodAndFeedTaxonomy($company);

            $this->command?->info(sprintf(
                'Imported %d rows (new: %d sample types, %d analysis types, %d analytes, %d elements, %d methods).',
                $stats['rows'],
                $stats['sample_types'],
                $stats['analysis_types'],
                $stats['analytes'],
                $stats['elements'],
                $stats['methods'],
            ));

            $this->logCounts($company);

            $this->command?->info('====================================================');
            $this->command?->info('FOOD TAXONOMY SEEDING COMPLETED');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function logCounts(Company $company): void
    {
        foreach (['FOOD', 'FOOD AND FEED'] as $code) {
            $sampleType = SampleType::query()
                ->where('company_id', $company->id)
                ->where('code', $code)
                ->first();

            if (! $sampleType) {
                $this->command?->warn("Sample type {$code} not found after seed.");

                continue;
            }

            $analysisTypes = AnalysisType::query()
                ->where('sample_type_id', $sampleType->id)
                ->orderBy('name')
                ->get();

            $this->command?->info("{$sampleType->name} ({$code}): {$analysisTypes->count()} analysis type(s)");

            foreach ($analysisTypes as $analysisType) {
                $elementCount = AnalysisElements::query()
                    ->where('analysis_type_id', $analysisType->id)
                    ->count();
                $this->command?->info("  - {$analysisType->name}: {$elementCount} element(s)");
            }
        }
    }
}
