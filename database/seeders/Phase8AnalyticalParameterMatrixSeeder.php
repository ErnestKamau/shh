<?php

namespace Database\Seeders;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\AnalysisMethod;
use App\Company;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\SeedsAmSpecParameterMatrix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase8AnalyticalParameterMatrixSeeder extends Seeder
{
    use SeedsAmSpecParameterMatrix;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 8 SEEDING: Analytical Parameter Matrix (AmSpec Excel)');
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

            $spreadsheetPath = base_path(self::AMSPEC_PARAMETERS_PATH);
            if (! is_file($spreadsheetPath)) {
                $this->command?->error('AmSpec parameters spreadsheet not found.');

                return;
            }

            $stats = $this->seedAmSpecParameterMatrix($company);

            $this->command?->info("Processed {$stats['rows']} spreadsheet rows (new: {$stats['sample_types']} sample types, {$stats['analysis_types']} analysis types, {$stats['analytes']} analytes, {$stats['elements']} elements, {$stats['methods']} methods).");

            $this->logPostImportCounts($company);

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 8 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function logPostImportCounts(Company $company): void
    {
        $analysisTypeCount = AnalysisType::query()->where('company_id', $company->id)->count();
        $analyteCount = Analyte::query()->where('company_id', $company->id)->count();
        $elementCount = AnalysisElements::query()
            ->whereHas('analysis_type', fn ($q) => $q->where('company_id', $company->id))
            ->count();
        $methodCount = AnalysisMethod::query()->where('company_id', $company->id)->count();

        $this->command?->info("Post-seed totals — analysis types: {$analysisTypeCount}, analytes: {$analyteCount}, elements: {$elementCount}, methods: {$methodCount}");
    }
}
