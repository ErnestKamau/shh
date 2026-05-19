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

            $epaScheme = $qcSchemes->where('code', 'EPA-QA')->first() ?? $qcSchemes->first();
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
            // 3. Define Scientific Robust Means & SD for each Analyte
            // ----------------------------------------------------------------
            $analytes = Analyte::all();
            $qcBenchmarks = [
                'ALY-EC' => ['mean' => 0.00, 'sd' => 0.01, 'low' => 0.00, 'high' => 0.00], // E. Coli
                'ALY-PB' => ['mean' => 0.0050, 'sd' => 0.0004, 'low' => 0.0001, 'high' => 0.0100], // Lead Content
                'ALY-CAF' => ['mean' => 3.00, 'sd' => 0.08, 'low' => 1.50, 'high' => 5.00], // Caffeine
                'ALY-HB' => ['mean' => 14.20, 'sd' => 0.35, 'low' => 10.00, 'high' => 18.00], // Hemoglobin
                'ALY-PH' => ['mean' => 6.50, 'sd' => 0.15, 'low' => 4.00, 'high' => 9.00], // Soil pH
                'ALY-NIT' => ['mean' => 50.00, 'sd' => 2.50, 'low' => 10.00, 'high' => 90.00], // Nitrogen
                'ALY-GLY' => ['mean' => 0.80, 'sd' => 0.05, 'low' => 0.01, 'high' => 2.00], // Glyphosate
                'ALY-PHO' => ['mean' => 40.00, 'sd' => 1.80, 'low' => 10.00, 'high' => 85.00], // Phosphorus
                'ALY-AFL' => ['mean' => 4.00, 'sd' => 0.22, 'low' => 0.05, 'high' => 10.00], // Aflatoxin B1
                'ALY-CO' => ['mean' => 25.00, 'sd' => 1.20, 'low' => 0.10, 'high' => 50.00], // CO
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
                        'very_low_guide' => max(0, $benchmark['mean'] - 4 * $benchmark['sd']),
                        'very_high_guide' => $benchmark['mean'] + 4 * $benchmark['sd'],
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

            // ----------------------------------------------------------------
            // 6. Dual-Schema Syncing (Direct insertion into reporting schema)
            // ----------------------------------------------------------------
            $this->command?->info('Syncing seeded QC entries to reporting schema for AI Analytics module...');

            // A. Sync reporting.analytes (cast UUID to Bigint)
            DB::connection('pgsql')->statement("
                INSERT INTO reporting.analytes (source_id, code, name, reporting_unit, decimal_places, is_active, source_created_at, source_updated_at, synced_at, payload)
                SELECT 
                    ('x' || substr(replace(a.id::text, '-', ''), 1, 15))::bit(60)::bigint as source_id,
                    a.code,
                    a.name,
                    a.reporting_symbol as reporting_unit,
                    a.decimal_places,
                    a.active as is_active,
                    a.created_at::text,
                    a.updated_at::text,
                    now()::text,
                    '{}'::jsonb
                FROM public.analytes a
                ON CONFLICT (source_id) DO UPDATE 
                SET code = EXCLUDED.code, name = EXCLUDED.name, reporting_unit = EXCLUDED.reporting_unit;
            ");
            $this->command?->info('Synchronized reporting.analytes table.');

            // B. Sync reporting.qc_processed_results
            DB::connection('pgsql')->statement("TRUNCATE TABLE reporting.qc_processed_results CASCADE");
            DB::connection('pgsql')->statement("
                INSERT INTO reporting.qc_processed_results (source_id, sample_type_id, analysis_type_id, analyte_id, method_id, standard_id, standard_value_id, robust_standard_deviation, robust_mean, robust_median, robust_cv, robust_cv_percentage, source_created_at, source_updated_at, synced_at, payload)
                SELECT 
                    ('x' || substr(replace(id::text, '-', ''), 1, 15))::bit(60)::bigint as source_id,
                    ('x' || substr(replace(sample_type_id::text, '-', ''), 1, 15))::bit(60)::bigint as sample_type_id,
                    ('x' || substr(replace(analysis_type_id::text, '-', ''), 1, 15))::bit(60)::bigint as analysis_type_id,
                    ('x' || substr(replace(analyte_id::text, '-', ''), 1, 15))::bit(60)::bigint as analyte_id,
                    method_id,
                    ('x' || substr(replace(standard_id::text, '-', ''), 1, 15))::bit(60)::bigint as standard_id,
                    ('x' || substr(replace(standard_value_id::text, '-', ''), 1, 15))::bit(60)::bigint as standard_value_id,
                    robust_standard_deviation,
                    robust_mean,
                    robust_median,
                    robust_cv,
                    robust_cv_percentage,
                    created_at::text,
                    updated_at::text,
                    now()::text,
                    '{}'::jsonb
                FROM public.qc_processed_result;
            ");
            $this->command?->info('Synchronized reporting.qc_processed_results table.');

            // C. Sync reporting.qc_results
            DB::connection('pgsql')->statement("TRUNCATE TABLE reporting.qc_results CASCADE");
            DB::connection('pgsql')->statement("
                INSERT INTO reporting.qc_results (source_id, analyte_id, analyte_code, analyte_processed_id, sample_header_id, sample_detail_id, sample_detail_code, result, status_code, guide_low, guide_high, unit_code, is_qc_processed, source_created_at, source_updated_at, synced_at, payload)
                SELECT 
                    ('x' || substr(replace(qr.id::text, '-', ''), 1, 15))::bit(60)::bigint as source_id,
                    ('x' || substr(replace(qr.analyte_id::text, '-', ''), 1, 15))::bit(60)::bigint as analyte_id,
                    qr.analyte_code,
                    ('x' || substr(replace(pr.id::text, '-', ''), 1, 15))::bit(60)::bigint as analyte_processed_id,
                    ('x' || substr(replace(qr.sample_header_id::text, '-', ''), 1, 15))::bit(60)::bigint as sample_header_id,
                    ('x' || substr(replace(qr.sample_detail_id::text, '-', ''), 1, 15))::bit(60)::bigint as sample_detail_id,
                    qr.sample_detail_code,
                    qr.result,
                    qr.status_code,
                    qr.guide_low,
                    qr.guide_high,
                    qr.unit_code,
                    qr.is_qc_processed,
                    qr.created_at::text,
                    qr.updated_at::text,
                    now()::text,
                    '{}'::jsonb
                FROM public.qc_results qr
                JOIN public.qc_processed_result pr ON pr.analyte_id = qr.analyte_id;
            ");
            $this->command?->info('Synchronized reporting.qc_results table.');

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 5 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
