<?php

namespace Database\Seeders;

use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase4QualityControlSeeder extends Seeder
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
            $this->command?->info('STARTING PHASE 4 SEEDING: Quality Control & Configs');
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
                    'value' => 'KES',
                ],
                [
                    'type_key' => 'operational',
                    'key' => 'company_email',
                    'value' => 'support@imara.com',
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
            $this->command?->info('PHASE 4 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
