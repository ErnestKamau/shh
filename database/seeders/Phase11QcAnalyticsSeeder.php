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
use Database\Seeders\Concerns\ClearsAmSpecQcData;
use Database\Seeders\Concerns\ResolvesQcApproverColumns;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase11QcAnalyticsSeeder extends Seeder
{
    use ClearsAmSpecQcData;
    use ResolvesQcApproverColumns;

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
            $this->clearAmSpecQcData();

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
                ['name' => 'ASTM Petroleum Products Testing Protocol', 'code' => 'ASTM-PET'],
                ['name' => 'ISO 17025 Core Calibration Guidelines', 'code' => 'ISO-17025'],
                ['name' => 'API MPMS Crude Oil Measurement Standard', 'code' => 'API-MPMS'],
                ['name' => 'IP Test Methods for Petroleum', 'code' => 'IP-PET'],
                ['name' => 'EN 14214 Biodiesel Quality Standard', 'code' => 'EN-14214'],
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
                    'value' => 'AED',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'company_email',
                    'value' => 'info@amspecgroup.com',
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

            $astmScheme = $qcSchemes->where('code', 'ASTM-PET')->first() ?? $qcSchemes->first();
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

            $isValueRow = DB::connection('pgsql')->table('standard_values')
                ->where('code', 'IsValue')
                ->first();

            if ($isValueRow === null) {
                $this->command?->error('IsValue standard lookup not found. Run Phase 13 food standards seeder first.');

                return;
            }

            $standardValueId = $isValueRow->id;
            $this->command?->info('Using IsValue standard lookup for QC processed results.');

            // ----------------------------------------------------------------
            // 3. Build robust means & SD for analytes that already have results
            // ----------------------------------------------------------------
            $analytes = Analyte::all();
            $knownBenchmarks = [
                'ALY-TVC' => ['mean' => 1200.00, 'sd' => 45.00, 'low' => 0.10, 'high' => 5000.00],
                'ALY-PH' => ['mean' => 6.80, 'sd' => 0.15, 'low' => 4.00, 'high' => 9.00],
                'ALY-EC' => ['mean' => 10.00, 'sd' => 1.20, 'low' => 0.10, 'high' => 100.00],
                'ALY-TCC' => ['mean' => 5.00, 'sd' => 0.50, 'low' => 0.10, 'high' => 50.00],
                'ALY-TURB' => ['mean' => 2.50, 'sd' => 0.20, 'low' => 0.10, 'high' => 10.00],
                'ALY-DO' => ['mean' => 8.00, 'sd' => 0.30, 'low' => 2.00, 'high' => 12.00],
                'ALY-COD-ENV' => ['mean' => 25.00, 'sd' => 2.00, 'low' => 1.00, 'high' => 100.00],
                'ALY-TDS' => ['mean' => 350.00, 'sd' => 15.00, 'low' => 50.00, 'high' => 1000.00],
                'ALY-CD' => ['mean' => 0.05, 'sd' => 0.01, 'low' => 0.01, 'high' => 0.50],
                'ALY-HG' => ['mean' => 0.02, 'sd' => 0.005, 'low' => 0.001, 'high' => 0.10],
            ];

            // ----------------------------------------------------------------
            // 4. Seed Pre-Computed Robust Statistics (qc_processed_result)
            // ----------------------------------------------------------------
            $processedMap = [];
            foreach ($analytes as $analyte) {
                $dbResult = Result::where('analyte_id', $analyte->id)->first();
                if (! $dbResult) {
                    continue;
                }

                $benchmark = $knownBenchmarks[$analyte->code] ?? $this->deriveQcBenchmarkFromResult($dbResult);
                if ($benchmark === null) {
                    continue;
                }

                $sdLine = SampleDetails::find($dbResult->sample_detail_id);
                if (! $sdLine) {
                    continue;
                }

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

                    $qcResultRow = [
                        'id' => $qcResultId,
                        'captured_result_id' => $dbCaptured ? $dbCaptured->id : (string) Str::uuid(),
                        'sample_detail_code' => $sdLine->sample_code,
                        'sample_detail_id' => $sdLine->id,
                        'sample_header_id' => $header->id,
                        'analyte_id' => $analyte->id,
                        'analyte_code' => $analyte->code,
                        'result' => (string) round($numericVal, 4),
                        'guide' => $benchmark['low'].' - '.$benchmark['high'],
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
                    ];

                    if ($this->qcResultProcessedColumnSupportsUuid()) {
                        $qcResultRow['analyte_processed_id'] = $info['id'];
                    }

                    DB::connection('pgsql')->table('qc_results')->insert($qcResultRow);

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

    /**
     * @return array{mean: float, sd: float, low: float, high: float}|null
     */
    private function deriveQcBenchmarkFromResult(Result $result): ?array
    {
        $low = is_numeric($result->guide_low) ? (float) $result->guide_low : 0.1;
        $high = is_numeric($result->guide_high) ? (float) $result->guide_high : min(99.0, $low + 10);
        $mean = is_numeric($result->correct_target)
            ? (float) $result->correct_target
            : (is_numeric($result->standard_target)
                ? (float) $result->standard_target
                : ($low + $high) / 2);

        if ($mean <= 0) {
            $mean = max($low, 1.0);
        }

        $sd = max($mean * 0.05, 0.01);

        return [
            'mean' => $mean,
            'sd' => $sd,
            'low' => $low,
            'high' => $high,
        ];
    }
}