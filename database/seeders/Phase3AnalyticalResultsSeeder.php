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
            // 2. Seed 10 Forensic Analytes
            // ----------------------------------------------------------------
            $analytesData = [
                ['code' => 'ALY-THC', 'name' => 'Tetrahydrocannabinol (THC)', 'symbol' => 'THC', 'unit' => '% w/w'],
                ['code' => 'ALY-CTH', 'name' => 'Cathinone Content', 'symbol' => 'Cathinone', 'unit' => '% w/w'],
                ['code' => 'ALY-COC', 'name' => 'Cocaine Hydrochloride', 'symbol' => 'Cocaine', 'unit' => '% w/w'],
                ['code' => 'ALY-MAM', 'name' => '6-Monoacetylmorphine (Heroin metabolite)', 'symbol' => '6-MAM', 'unit' => '% w/w'],
                ['code' => 'ALY-AMP', 'name' => 'Amphetamine Base', 'symbol' => 'Amphetamine', 'unit' => '% w/w'],
                ['code' => 'ALY-METH', 'name' => 'Methamphetamine Content', 'symbol' => 'Methamphetamine', 'unit' => '% w/w'],
                ['code' => 'ALY-FEN', 'name' => 'Fentanyl Trace Concentration', 'symbol' => 'Fentanyl', 'unit' => 'ppb'],
                ['code' => 'ALY-STR', 'name' => 'Human STR DNA Profile Match', 'symbol' => 'STR-Match', 'unit' => '% Match'],
                ['code' => 'ALY-WDNA', 'name' => 'Wildlife DNA Species Similarity', 'symbol' => 'Wildlife-DNA', 'unit' => '% Similarity'],
                ['code' => 'ALY-CN', 'name' => 'Cyanide Concentration', 'symbol' => 'Cyanide', 'unit' => 'mg/L'],
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
            // 3. Map Analytes to 10 Forensic Analysis Types (AnalysisElements)
            // ----------------------------------------------------------------
            $mapping = [
                'ANA-CAN' => 'ALY-THC',
                'ANA-CAT' => 'ALY-CTH',
                'ANA-COC' => 'ALY-COC',
                'ANA-HER' => 'ALY-MAM',
                'ANA-AMP' => 'ALY-AMP',
                'ANA-MET' => 'ALY-METH',
                'ANA-FEN' => 'ALY-FEN',
                'ANA-STR' => 'ALY-STR',
                'ANA-WLD' => 'ALY-WDNA',
                'ANA-TOX' => 'ALY-CN',
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

                // Skip Samples Reception batches (they do not have testing results yet)
                if ($header->status === 'Samples Reception') continue;

                // Retrieve mapped analyte & element
                $element = $elementsMap[$sd->analysis_type_id] ?? null;
                if (!$element) {
                    // Fallback to first available mapping
                    $element = collect($elementsMap)->first();
                }

                $analyte = Analyte::find($element->analyte_id);
                if (!$analyte) continue;

                // Generate a realistic mock result depending on the forensic analyte
                $resultVal = '0.00';
                $numericVal = 0.00;
                switch ($analyte->code) {
                    case 'ALY-THC':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 5.0 + (rand(0, 2000) / 100); // 5% to 25%
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal}% THC Detected" : 'Negative (Under LOD)';
                        break;
                    case 'ALY-CTH':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 0.5 + (rand(0, 450) / 100); // 0.5% to 5.0%
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal}% Cathinone Detected" : 'Negative (Under LOD)';
                        break;
                    case 'ALY-COC':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 10.0 + (rand(0, 8000) / 100); // 10% to 90%
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal}% Cocaine HCl" : 'Negative (Under LOD)';
                        break;
                    case 'ALY-MAM':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 5.0 + (rand(0, 7000) / 100); // 5% to 75%
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal}% 6-MAM Detected" : 'Negative (Under LOD)';
                        break;
                    case 'ALY-AMP':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 5.0 + (rand(0, 7500) / 100); // 5% to 80%
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal}% Amphetamine" : 'Negative (Under LOD)';
                        break;
                    case 'ALY-METH':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 10.0 + (rand(0, 8500) / 100); // 10% to 95%
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal}% Crystal Meth" : 'Negative (Under LOD)';
                        break;
                    case 'ALY-FEN':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 0.1 + (rand(0, 990) / 100); // 0.1 to 10.0 ppb
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal} ppb Fentanyl Detected" : 'Negative (Under LOD)';
                        break;
                    case 'ALY-STR':
                        $numericVal = rand(0, 4) === 0 ? 0.00 : 99.99;
                        $resultVal = $numericVal > 0 ? "STR DNA Profile Match: {$numericVal}% Probability" : 'No Match (Exclusion)';
                        break;
                    case 'ALY-WDNA':
                        $species = ['Loxodonta africana (African Elephant)', 'Ceratotherium simum (White Rhino)', 'Panthera leo (Lion)'];
                        $selectedSpecies = $species[rand(0, 2)];
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 98.0 + (rand(0, 199) / 100); // 98.0% to 99.99% similarity
                        $resultVal = $numericVal > 0 ? "Positive - {$numericVal}% Match to {$selectedSpecies}" : 'Inconclusive / No Animal DNA Match';
                        break;
                    case 'ALY-CN':
                        $numericVal = rand(0, 3) === 0 ? 0.00 : 0.5 + (rand(0, 950) / 100); // 0.5 to 10.0 mg/L
                        $resultVal = $numericVal > 0 ? "Toxic - {$numericVal} mg/L Cyanide Detected" : 'Normal / Not Detected';
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
