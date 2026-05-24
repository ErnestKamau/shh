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
use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase11QcAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('====================================================');
        $this->command?->info('STARTING PHASE 11 SEEDING: QC Config + Location-Aware Analytics');
        $this->command?->info('====================================================');

        $this->seedQcReferenceData();
        $this->seedQcStabilityData();

        $this->command?->info('QC runs inherit location through their linked sample headers/details and lab zone assignments.');
        $this->command?->info('====================================================');
        $this->command?->info('PHASE 11 SEEDING COMPLETED SUCCESSFULLY!');
        $this->command?->info('====================================================');
    }

    private function seedQcReferenceData(): void
    {
        // Enforce the correct pgsql connection.
        config(['database.default' => 'pgsql']);

        // Allow mass assignment on all models.
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING QC REFERENCE SEEDING: Quality Control & Configs');
            $this->command?->info('====================================================');

            // Retrieve context user
            $activeUser = User::where('active', 1)->first() ?? User::first();
            $activeUserId = $activeUser ? $activeUser->id : null;

            // ----------------------------------------------------------------
            // 1. Seed 5 QC Types
            // ----------------------------------------------------------------
            $qcTypes = [
                [
                    'name' => 'Blank Sample (Instrument Zeroing)',
                    'code' => 'BLK',
                    'has_standards' => false,
                    'has_configured_samples' => true,
                    'is_active' => true,
                ],
                [
                    'name' => 'Spiked Calibration Standard',
                    'code' => 'SPK',
                    'has_standards' => true,
                    'has_configured_samples' => false,
                    'is_active' => true,
                ],
                [
                    'name' => 'Duplicate Laboratory Control',
                    'code' => 'DUP',
                    'has_standards' => false,
                    'has_configured_samples' => true,
                    'is_active' => true,
                ],
                [
                    'name' => 'Certified Reference Material (CRM)',
                    'code' => 'CRM',
                    'has_standards' => true,
                    'has_configured_samples' => true,
                    'is_active' => true,
                ],
                [
                    'name' => 'Calibration Verification Check',
                    'code' => 'CAL',
                    'has_standards' => true,
                    'has_configured_samples' => false,
                    'is_active' => true,
                ],
            ];

            foreach ($qcTypes as $qt) {
                $existing = DB::connection('pgsql')->table('qc_types')->where('code', $qt['code'])->first();
                if ($existing) {
                    DB::connection('pgsql')->table('qc_types')->where('code', $qt['code'])->update([
                        'name' => $qt['name'],
                        'has_standards' => $qt['has_standards'],
                        'has_configured_samples' => $qt['has_configured_samples'],
                        'is_active' => $qt['is_active'],
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::connection('pgsql')->table('qc_types')->insert([
                        'id' => (string) Str::uuid(),
                        'code' => $qt['code'],
                        'name' => $qt['name'],
                        'has_standards' => $qt['has_standards'],
                        'has_configured_samples' => $qt['has_configured_samples'],
                        'is_active' => $qt['is_active'],
                        'created_by' => $activeUserId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $this->command?->info("Seeded QC Type: {$qt['name']} (Code: {$qt['code']})");
            }

            // ----------------------------------------------------------------
            // 2. Seed 5 QC Schemes
            // ----------------------------------------------------------------
            $qcSchemes = [
                ['name' => 'UNODC Narcotics Identification Protocol', 'code' => 'UNODC-QA'],
                ['name' => 'ISO 17025 Core Calibration Guidelines', 'code' => 'ISO-17025'],
                ['name' => 'SWGDAM Human DNA Interpretation Guidelines', 'code' => 'SWGDAM-QA'],
                ['name' => 'ENFSI Forensic Science Quality Assurance Protocol', 'code' => 'ENFSI-QA'],
                ['name' => 'SOFT Forensic Toxicology Standard Calibration', 'code' => 'SOFT-CAL'],
            ];

            foreach ($qcSchemes as $qs) {
                $existing = DB::connection('pgsql')->table('qc_scheme')->where('code', $qs['code'])->first();
                if ($existing) {
                    DB::connection('pgsql')->table('qc_scheme')->where('code', $qs['code'])->update([
                        'name' => $qs['name'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::connection('pgsql')->table('qc_scheme')->insert([
                        'id' => (string) Str::uuid(),
                        'code' => $qs['code'],
                        'name' => $qs['name'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $this->command?->info("Seeded QC Scheme: {$qs['name']} (Code: {$qs['code']})");
            }

            // ----------------------------------------------------------------
            // 3. Seed System Config Types & Values
            // ----------------------------------------------------------------
            $configTypes = [
                [
                    'key' => 'operational',
                    'configuration_type' => 'General Settings',
                    'description' => 'Core operational LIMS dashboard and system settings',
                ],
                [
                    'key' => 'quality_control',
                    'configuration_type' => 'Quality Control Settings',
                    'description' => 'Configuration flags and percentage thresholds for QA/QC rules',
                ],
                [
                    'key' => 'sla_alerts',
                    'configuration_type' => 'Dashboard & SLA Alert Settings',
                    'description' => 'Visual thresholds and refresh intervals for monitoring panels',
                ],
            ];

            $typesMap = [];
            foreach ($configTypes as $ct) {
                $type = SystemConfigurationsType::updateOrCreate(
                    ['configuration_type' => $ct['configuration_type']],
                    [
                        'description' => $ct['description'],
                        'status' => true,
                    ]
                );
                $typesMap[$ct['key']] = $type;
                $this->command?->info("Seeded Configuration Type: {$type->configuration_type}");
            }

            // ----------------------------------------------------------------
            // 4. Seed 10 System Configuration Values
            // ----------------------------------------------------------------
            $configs = [
                [
                    'type_key' => 'quality_control',
                    'key' => 'qc_percentage_config',
                    'value' => '10',
                ],
                [
                    'type_key' => 'sla_alerts',
                    'key' => 'dashboard_refresh_rate',
                    'value' => '30',
                ],
                [
                    'type_key' => 'sla_alerts',
                    'key' => 'sla_warning_threshold_hours',
                    'value' => '12',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'default_currency',
                    'value' => 'TZS',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'company_email',
                    'value' => 'support@gcla.go.tz',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'max_report_export_limit',
                    'value' => '1000',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'enable_realtime_sync',
                    'value' => 'true',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'ai_intent_routing_fallback',
                    'value' => 'public_schema',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'worker_orchestration_driver',
                    'value' => 'redis',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'sms_alert_enabled',
                    'value' => 'false',
                ],
            ];

            foreach ($configs as $cfg) {
                $associatedType = $typesMap[$cfg['type_key']] ?? null;

                $config = SystemConfiguration::updateOrCreate(
                    ['key' => $cfg['key']],
                    [
                        'configuration_type_id' => $associatedType?->id,
                        'value' => $cfg['value'],
                        'status' => true,
                    ]
                );
                $this->command?->info("Seeded System Configuration: {$config->key} => [Encrypted value]");
            }

            $this->command?->info('====================================================');
            $this->command?->info('QC REFERENCE SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function seedQcStabilityData(): void
    {
        // Enforce the correct pgsql connection.
        config(['database.default' => 'pgsql']);

        // Allow mass assignment on all models.
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING QC STABILITY SEEDING: Quality Control & Stability');
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

            // Retrieve QC Types and Schemes
            $qcTypes = DB::connection('pgsql')->table('qc_types')->get();
            $qcSchemes = DB::connection('pgsql')->table('qc_scheme')->get();

            if ($qcTypes->isEmpty() || $qcSchemes->isEmpty()) {
                $this->command?->error('QC Types or Schemes not found!');
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
            $this->command?->info('QC STABILITY SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}