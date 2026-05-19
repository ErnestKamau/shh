<?php

namespace Database\Seeders;

use App\Company;
use App\Analyte;
use App\AnalysisType;
use App\AnalysisElements;
use App\SampleHeader;
use App\SampleDetails;
use App\CapturedResult;
use App\Result;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase3AnalyticalResultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Enforce the correct pgsql connection.
        config(['database.default' => 'pgsql']);

        // Allow mass assignment on all models.
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 3 SEEDING: Analytical Results & SLAs');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 1. Retrieve Context
            // ----------------------------------------------------------------
            $company = Company::first();
            if (!$company) {
                $this->command?->error('Base company not found! Run Phase 1 seeder first.');
                return;
            }

            $activeUser = User::where('active', 1)->first() ?? User::first();
            $activeUserId = $activeUser ? $activeUser->id : null;

            // ----------------------------------------------------------------
            // 2. Seed 10 Standard Analytes
            // ----------------------------------------------------------------
            $analytesData = [
                ['code' => 'ALY-EC', 'name' => 'Escherichia coli (E. Coli)', 'symbol' => 'E. coli', 'unit' => 'CFU/100mL'],
                ['code' => 'ALY-PB', 'name' => 'Lead Content (Pb)', 'symbol' => 'Pb', 'unit' => 'mg/L'],
                ['code' => 'ALY-CAF', 'name' => 'Caffeine Concentration', 'symbol' => 'Caffeine', 'unit' => '% w/w'],
                ['code' => 'ALY-HB', 'name' => 'Hemoglobin Level', 'symbol' => 'Hb', 'unit' => 'g/dL'],
                ['code' => 'ALY-PH', 'name' => 'Soil pH Level', 'symbol' => 'pH', 'unit' => 'pH Units'],
                ['code' => 'ALY-NIT', 'name' => 'Nitrogen Content (N)', 'symbol' => 'N', 'unit' => 'mg/kg'],
                ['code' => 'ALY-GLY', 'name' => 'Glyphosate Trace Residue', 'symbol' => 'Glyphosate', 'unit' => 'ppb'],
                ['code' => 'ALY-PHO', 'name' => 'Phosphorus Content (P)', 'symbol' => 'P', 'unit' => 'mg/kg'],
                ['code' => 'ALY-AFL', 'name' => 'Aflatoxin B1 Level', 'symbol' => 'Aflatoxin', 'unit' => 'µg/kg'],
                ['code' => 'ALY-CO', 'name' => 'Carbon Monoxide (CO)', 'symbol' => 'CO', 'unit' => 'ppm'],
            ];

            $analytesMap = [];
            foreach ($analytesData as $ad) {
                $analyte = Analyte::updateOrCreate(
                    ['code' => $ad['code'], 'company_id' => $company->id],
                    [
                        'name'             => $ad['name'],
                        'common_name'      => $ad['name'],
                        'reporting_symbol' => $ad['symbol'],
                        'reporting_unit'   => $ad['unit'],
                        'decimal_places'   => 2,
                        'active'           => true,
                        'non_detectable'   => false,
                        'non_accredited'   => false,
                        'show_on_report'   => true,
                    ]
                );
                $analytesMap[$ad['code']] = $analyte;
                $this->command?->info("Seeded Analyte: {$analyte->name} (Symbol: {$analyte->reporting_symbol})");
            }

            // ----------------------------------------------------------------
            // 3. Map Analytes to 10 Analysis Types (AnalysisElements)
            // ----------------------------------------------------------------
            $mapping = [
                'ANA-BAC'  => 'ALY-EC',
                'ANA-HM'   => 'ALY-PB',
                'ANA-COMP' => 'ALY-CAF',
                'ANA-TOX'  => 'ALY-HB',
                'ANA-PH'   => 'ALY-PH',
                'ANA-MIN'  => 'ALY-NIT',
                'ANA-PEST' => 'ALY-GLY',
                'ANA-NUTR' => 'ALY-PHO',
                'ANA-MYCO' => 'ALY-AFL',
                'ANA-VOC'  => 'ALY-CO',
            ];

            $elementsMap = [];
            foreach ($mapping as $atCode => $alyCode) {
                $analysisType = AnalysisType::where('code', $atCode)->first();
                $analyte = $analytesMap[$alyCode] ?? null;

                if ($analysisType && $analyte) {
                    $element = AnalysisElements::updateOrCreate(
                        ['analysis_type_id' => $analysisType->id, 'analyte_id' => $analyte->id],
                        [
                            'decimal_places'       => 2,
                            'reporting_symbol'     => $analyte->reporting_symbol,
                            'reporting_unit'       => $analyte->reporting_unit,
                            'active'               => true,
                            'show_on_report'       => true,
                            'company_id'           => $company->id,
                            'result_is_calculated' => false,
                            'recommend_remedies'   => false,
                            'has_method_sequence'  => false,
                        ]
                    );
                    $elementsMap[$analysisType->id] = $element;
                    $this->command?->info("Mapped Analyte '{$analyte->reporting_symbol}' to Analysis Type '{$analysisType->name}'");
                }
            }

            // ----------------------------------------------------------------
            // 4. Seed Results & SLAs for In Lab / Pending Review / Approved Batches
            // ----------------------------------------------------------------
            $sampleDetails = SampleDetails::all();
            $seededCount = 0;

            foreach ($sampleDetails as $sd) {
                $header = $sd->getSampleHeader();
                if (!$header) continue;

                // Skip Draft batches (they do not have testing results yet)
                if ($header->status === 'Draft') continue;

                // Retrieve mapped analyte & element
                $element = $elementsMap[$sd->analysis_type_id] ?? null;
                if (!$element) {
                    // Fallback to first available mapping
                    $element = collect($elementsMap)->first();
                }

                $analyte = Analyte::find($element->analyte_id);
                if (!$analyte) continue;

                // Generate a realistic mock result depending on the analyte (clamped strictly under 99.0 for numeric(8,6))
                $resultVal = '0.00';
                $numericVal = 0.00;
                switch ($analyte->code) {
                    case 'ALY-EC':
                        $resultVal = rand(0, 5) === 0 ? 'Positive (12 CFU)' : 'Negative';
                        $numericVal = rand(0, 5) === 0 ? 12.0 : 0.0;
                        break;
                    case 'ALY-PB':
                        $numericVal = rand(0, 100) / 10000; // e.g. 0.0045
                        $resultVal = $numericVal . ' mg/L';
                        break;
                    case 'ALY-CAF':
                        $numericVal = 2.0 + (rand(0, 200) / 100); // 2.0 to 4.0
                        $resultVal = $numericVal . ' % w/w';
                        break;
                    case 'ALY-HB':
                        $numericVal = 11.5 + (rand(0, 60) / 10); // 11.5 to 17.5
                        $resultVal = $numericVal . ' g/dL';
                        break;
                    case 'ALY-PH':
                        $numericVal = 5.5 + (rand(0, 30) / 10); // 5.5 to 8.5
                        $resultVal = $numericVal . ' pH';
                        break;
                    case 'ALY-NIT':
                        $numericVal = 10.0 + (rand(0, 800) / 10); // 10.0 to 90.0 (safe under 99.0)
                        $resultVal = $numericVal . ' mg/kg';
                        break;
                    case 'ALY-GLY':
                        $numericVal = rand(0, 15) / 10; // 0 to 1.5
                        $resultVal = $numericVal . ' ppb';
                        break;
                    case 'ALY-PHO':
                        $numericVal = 15.0 + (rand(0, 700) / 10); // 15.0 to 85.0 (safe under 99.0)
                        $resultVal = $numericVal . ' mg/kg';
                        break;
                    case 'ALY-AFL':
                        $numericVal = rand(0, 80) / 10; // 0 to 8.0
                        $resultVal = $numericVal . ' µg/kg';
                        break;
                    case 'ALY-CO':
                        $numericVal = rand(0, 50); // 0 to 50
                        $resultVal = $numericVal . ' ppm';
                        break;
                }

                // A. Seed captured_results
                $captured = CapturedResult::create([
                    'has_no_result_capture'     => false,
                    'sample_detail_code'        => $sd->sample_code,
                    'sample_detail_id'          => $sd->id,
                    'sample_header_id'          => $header->id,
                    'analyte_id'                => $analyte->id,
                    'analyte_code'              => $analyte->code,
                    'result'                    => $resultVal,
                    'user_id'                   => $activeUserId,
                    'analysis_type_id'          => $sd->analysis_type_id,
                    'analysis_element_id'       => $element->id,
                    'analysis_type_order'       => 0,
                    'parameters_order'          => 0,
                    'analyte_status_contracted' => false,
                    'remark'                    => 'Analysis performed within controlled parameters.',
                    'scienctific_result'        => $resultVal,
                ]);

                // B. Seed final reported results
                Result::create([
                    'has_no_result_capture'     => false,
                    'captured_result_id'        => $captured->id,
                    'sample_detail_code'        => $sd->sample_code,
                    'sample_detail_id'          => $sd->id,
                    'sample_header_id'          => $header->id,
                    'analyte_id'                => $analyte->id,
                    'analyte_code'              => $analyte->code,
                    'result'                    => $resultVal,
                    'initial_result'            => $numericVal,
                    'guide_low'                 => 0.01,
                    'guide_high'                => 10.0,
                    'recheck'                   => false,
                    'analyte_status_contracted' => false,
                    'analysis_type_order'       => 0,
                    'parameters_order'          => 0,
                    'remarks'                   => 'Result validated by supervisor.',
                    'scienctific_result'        => $resultVal,
                    'analysis_type_id'          => $sd->analysis_type_id,
                ]);

                // C. Seed tat_captured SLAs
                $createdAt = $header->created_at;
                $targetDeadline = (clone $createdAt)->addDays(2); // 48-hour SLA deadline

                // Finished date logic
                $finishedDate = (clone $createdAt)->addDays(1)->addHours(rand(4, 18));
                $isOverdue = $finishedDate->gt($targetDeadline);
                $overdueDays = $isOverdue ? $finishedDate->diffInDays($targetDeadline) : 0;

                DB::connection('pgsql')->table('tat_captured')->insert([
                    'id'                  => (string) Str::uuid(),
                    'captured_result_id'  => $captured->id,
                    'analysis_type_id'    => $sd->analysis_type_id,
                    'analyte_id'          => $analyte->id,
                    'sample_type_id'      => $header->sample_type_id,
                    'sample_detail_id'    => $sd->id,
                    'sample_header_id'    => $header->id,
                    'result'              => $resultVal,
                    'analyst_id'          => $activeUserId,
                    'tat_overdue_days'    => $overdueDays,
                    'tat_date'            => $targetDeadline,
                    'finished_date'       => $finishedDate,
                    'is_complete'         => true,
                    'start_date_analysis' => $createdAt,
                    'created_at'          => $createdAt,
                    'updated_at'          => $createdAt,
                ]);

                $seededCount++;
            }

            $this->command?->info("Successfully seeded results & TAT metrics for {$seededCount} sample detail records.");
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 3 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
