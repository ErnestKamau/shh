<?php

namespace Database\Seeders;

use App\Company;
use App\Analyte;
use App\AnalysisType;
use App\SampleHeader;
use App\SampleDetails;
use App\CapturedResult;
use App\Result;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase5QcAnalyticsSeeder extends Seeder
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
            $this->command?->info('STARTING PHASE 5 SEEDING: Quality Control & Stability');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 1. Retrieve Context
            // ----------------------------------------------------------------
            $company = Company::first();
            if (!$company) {
                $this->command?->error('Base company not found! Run previous seeders first.');
                return;
            }

            $activeUser = User::where('active', 1)->first() ?? User::first();
            $activeUserId = $activeUser ? $activeUser->id : null;

            // Retrieve QC Types and Schemes from Phase 4
            $qcTypes = DB::connection('pgsql')->table('qc_types')->get();
            $qcSchemes = DB::connection('pgsql')->table('qc_scheme')->get();

            if ($qcTypes->isEmpty() || $qcSchemes->isEmpty()) {
                $this->command?->error('QC Types or Schemes not found! Run Phase 4 seeder first.');
                return;
            }

            $blkType = $qcTypes->where('code', 'BLK')->first() ?? $qcTypes->first();
            $spkType = $qcTypes->where('code', 'SPK')->first() ?? $qcTypes->first();
            $crmType = $qcTypes->where('code', 'CRM')->first() ?? $qcTypes->first();

            $unodcScheme = $qcSchemes->where('code', 'UNODC-QA')->first() ?? $qcSchemes->first();
            $isoScheme = $qcSchemes->where('code', 'ISO-17025')->first() ?? $qcSchemes->first();

            // ----------------------------------------------------------------
            // 2. Seed Base Standards & Standard Values
            // ----------------------------------------------------------------
            $standardId = (string) Str::uuid();
            DB::connection('pgsql')->table('standards')->updateOrInsert(
                ['code' => 'STD-QC-REF'],
                [
                    'id' => $standardId,
                    'name' => 'Certified Reference Material Standard 102',
                    'status' => true,
                    'is_qc_standard' => true,
                    'qc_type_id' => $crmType->id,
                    'qc_scheme_ids' => $isoScheme->code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $this->command?->info('Seeded QC Standard: Certified Reference Material Standard 102');

            $standardValueId = (string) Str::uuid();
            DB::connection('pgsql')->table('standard_values')->updateOrInsert(
                ['code' => 'VAL-QC-TGT'],
                [
                    'id' => $standardValueId,
                    'name' => 'Baseline Target Value',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $this->command?->info('Seeded QC Standard Value: Baseline Target Value');

            // ----------------------------------------------------------------
            // 3. Define Scientific Robust Means & SD for each Forensic Analyte
            // ----------------------------------------------------------------
            $analytes = Analyte::all();
            $qcBenchmarks = [
                'ALY-THC'  => ['mean' => 15.00, 'sd' => 0.50, 'low' => 0.10, 'high' => 25.00],
                'ALY-CTH'  => ['mean' => 2.50,  'sd' => 0.10, 'low' => 0.10, 'high' => 5.00],
                'ALY-COC'  => ['mean' => 75.00, 'sd' => 2.00, 'low' => 0.10, 'high' => 90.00],
                'ALY-MAM'  => ['mean' => 60.00, 'sd' => 1.50, 'low' => 0.10, 'high' => 75.00],
                'ALY-AMP'  => ['mean' => 50.00, 'sd' => 1.20, 'low' => 0.10, 'high' => 80.00],
                'ALY-METH' => ['mean' => 80.00, 'sd' => 1.80, 'low' => 0.10, 'high' => 95.00],
                'ALY-FEN'  => ['mean' => 4.00,  'sd' => 0.15, 'low' => 0.05, 'high' => 10.00],
                'ALY-STR'  => ['mean' => 99.99, 'sd' => 0.01, 'low' => 50.00, 'high' => 99.99],
                'ALY-WDNA' => ['mean' => 99.50, 'sd' => 0.10, 'low' => 95.00, 'high' => 99.99],
                'ALY-CN'   => ['mean' => 5.00,  'sd' => 0.20, 'low' => 0.10, 'high' => 10.00],
            ];

            // ----------------------------------------------------------------
            // 4. Seed Pre-Computed Robust Statistics (qc_processed_result)
            // ----------------------------------------------------------------
            $processedMap = [];
            foreach ($analytes as $analyte) {
                if (!isset($qcBenchmarks[$analyte->code])) continue;
                $benchmark = $qcBenchmarks[$analyte->code];

                // Find a result that maps this analyte to a sample
                $dbResult = Result::where('analyte_id', $analyte->id)->first();
                if (!$dbResult) continue;

                $sdLine = SampleDetails::find($dbResult->sample_detail_id);
                if (!$sdLine) continue;

                $qcProcessedId = (string) Str::uuid();
                
                // Calculate CV and CV% (Robust Coefficient of Variation)
                $cv = $benchmark['mean'] > 0 ? ($benchmark['sd'] / $benchmark['mean']) : 0.0;
                $cvPct = $cv * 100;

                DB::connection('pgsql')->table('qc_processed_result')->insert([
                    'id' => $qcProcessedId,
                    'sample_type_id' => $sdLine->getSampleHeader()->sample_type_id,
                    'analysis_type_id' => $sdLine->analysis_type_id,
                    'analyte_id' => $analyte->id,
                    'method_id' => 1,
                    'standard_id' => $standardId,
                    'standard_value_id' => $standardValueId,
                    'robust_standard_deviation' => $benchmark['sd'],
                    'robust_mean' => $benchmark['mean'],
                    'robust_median' => $benchmark['mean'],
                    'robust_cv' => $cv,
                    'robust_cv_percentage' => $cvPct,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $processedMap[$analyte->id] = [
                    'id' => $qcProcessedId,
                    'benchmark' => $benchmark,
                    'detail' => $sdLine,
                    'result' => $dbResult,
                ];

                $this->command?->info("Pre-computed Stability Matrix for Analyte '{$analyte->name}' (Robust CV%: " . round($cvPct, 2) . "%)");
            }

            // ----------------------------------------------------------------
            // 5. Seed Historical QC Measurement Runs (qc_results)
            // ----------------------------------------------------------------
            $totalQcResults = 0;
            foreach ($processedMap as $analyteId => $info) {
                $analyte = Analyte::find($analyteId);
                $benchmark = $info['benchmark'];
                $sdLine = $info['detail'];
                $dbResult = $info['result'];
                $header = $sdLine->getSampleHeader();

                // Retrieve matching final CapturedResult to link
                $dbCaptured = CapturedResult::where('sample_detail_id', $sdLine->id)->first();

                // Seed 20 historical runs spanning the last 6 months
                for ($run = 1; $run <= 20; $run++) {
                    $timestamp = now()->subDays(20 - $run)->subHours(rand(0, 23));

                    // Generate normally distributed value around robust mean
                    if ($run == 8) {
                        // High outlier (+3.5 SDs) - Out of Control!
                        $numericVal = $benchmark['mean'] + (3.5 * $benchmark['sd']);
                    } elseif ($run == 15) {
                        // Low outlier (-3.2 SDs) - Out of Control!
                        $numericVal = $benchmark['mean'] - (3.2 * $benchmark['sd']);
                    } else {
                        // Normal stable variance (between -1.8 and +1.8 SDs)
                        $deviation = (rand(-180, 180) / 100) * $benchmark['sd'];
                        $numericVal = $benchmark['mean'] + $deviation;
                    }

                    // Clamp to positive boundaries
                    $numericVal = max(0.000000, $numericVal);
                    // Absolute clamp below 99.0 to respect numeric(8,6) postgres type constraint
                    $numericVal = min(90.000000, $numericVal);

                    // Determine stability boundaries & standard status codes
                    $diff = abs($numericVal - $benchmark['mean']);
                    if ($diff >= 3 * $benchmark['sd']) {
                        $statusCode = 'OUT_OF_CONTROL';
                        $comment = 'Out of control event flagged! High instrumental variance detected.';
                    } elseif ($diff >= 2 * $benchmark['sd']) {
                        $statusCode = 'WARNING';
                        $comment = 'Slight warning drift detected. Re-calibrating instruments.';
                    } else {
                        $statusCode = 'PASSED';
                        $comment = 'Quality control verification successful.';
                    }

                    $qcResultId = (string) Str::uuid();

                    DB::connection('pgsql')->table('qc_results')->insert([
                        'id' => $qcResultId,
                        'captured_result_id' => $dbCaptured ? $dbCaptured->id : (string) Str::uuid(),
                        'sample_detail_code' => $sdLine->sample_code,
                        'sample_detail_id' => $sdLine->id,
                        'sample_header_id' => $header->id,
                        'analyte_id' => $analyte->id,
                        'analyte_code' => $analyte->code,
                        'result' => (string) round($numericVal, 4),
                        'guide' => $benchmark['low'] . ' - ' . $benchmark['high'],
                        'comments' => $comment,
                        'recheck' => false,
                        'guide_low' => $benchmark['low'],
                        'guide_high' => $benchmark['high'],
                        'unit_code' => $analyte->reporting_unit,
                        'status_code' => $statusCode,
                        'is_qc_processed' => true,
                        'reporting_symbol' => $analyte->reporting_symbol,
                        'qc' => true,
                        'correct_target' => $benchmark['mean'],
                        'standard_target' => $benchmark['mean'],
                        'recommendations' => $statusCode === 'PASSED' ? 'None' : 'Recalibrate sensors.',
                        'initial_result' => $numericVal,
                        'initial_reporting_symbol' => $analyte->reporting_symbol,
                        'very_low_guide' => min(99.000000, max(0.000000, $benchmark['mean'] - 4 * $benchmark['sd'])),
                        'very_high_guide' => min(99.000000, $benchmark['mean'] + 4 * $benchmark['sd']),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'analysis_type_id' => $sdLine->analysis_type_id,
                        'remarks' => $comment,
                        'analyte_status_contracted' => false,
                        'analyte_accredited' => true,
                        'analysis_type_order' => 0,
                        'parameters_order' => 0,
                        'qc_scheme_id' => $isoScheme->id,
                        'qc_type_id' => $crmType->id,
                        'result_id' => $dbResult ? $dbResult->id : null,
                    ]);

                    $totalQcResults++;
                }
            }

            $this->command?->info("Successfully seeded {$totalQcResults} historical QC measurement logs in public operational schema.");



            $this->command?->info('====================================================');
            $this->command?->info('PHASE 5 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
