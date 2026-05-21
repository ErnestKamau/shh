<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Phase8EquipmentManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 8 SEEDING: Equipment & Asset Management');
            $this->command?->info('====================================================');

            // 1. Fetch Company and Lab IDs
            $company = DB::connection('pgsql')->table('companies')->first();
            if (!$company) {
                $this->command?->error('No company found! Run core seeders first.');
                return;
            }
            $companyId = $company->id;

            $labs = DB::connection('pgsql')->table('labs')->get();
            if ($labs->isEmpty()) {
                $this->command?->error('No labs found! Run core seeders first.');
                return;
            }
            $dnaLabId = $labs->firstWhere('name', 'Forensic DNA Department')?->id ?? $labs->first()->id;
            $chemLabId = $labs->firstWhere('name', 'Forensic Chemistry Department')?->id ?? $labs->first()->id;

            $users = DB::connection('pgsql')->table('users')->limit(5)->get();
            $operatorName = $users->first()?->name ?? 'Sarah Jenkins';

            // 2. Define premium forensic scientific lab instruments
            $instruments = [
                [
                    'name' => 'Agilent 8890 Gas Chromatograph (GC-MS)',
                    'equipment_number' => 'EQ-GCMS-001',
                    'description' => 'High-performance GC-MS for volatile organic compound analysis and toxicology screenings.',
                    'make' => 'Agilent Technologies',
                    'model' => '8890 / 5977B',
                    'date_purchased' => '2023-11-12',
                    'maintainance_days' => 180,
                    'maintainance_notification_in_days' => 14,
                    'calibration_days' => 365,
                    'calibration_notification_in_days' => 30,
                    'requires_daily_log' => true,
                    'lab_id' => $chemLabId,
                    'assigned_department' => 'Forensic Chemistry Department',
                ],
                [
                    'name' => 'NexION 2000 ICP-MS Spectrometer',
                    'equipment_number' => 'EQ-ICPMS-002',
                    'description' => 'Inductively Coupled Plasma Mass Spectrometer for trace metal analysis and poison screening.',
                    'make' => 'PerkinElmer',
                    'model' => 'NexION 2000',
                    'date_purchased' => '2024-02-20',
                    'maintainance_days' => 90,
                    'maintainance_notification_in_days' => 7,
                    'calibration_days' => 180,
                    'calibration_notification_in_days' => 14,
                    'requires_daily_log' => true,
                    'lab_id' => $chemLabId,
                    'assigned_department' => 'Forensic Chemistry Department',
                ],
                [
                    'name' => 'Shimadzu Prominence HPLC System',
                    'equipment_number' => 'EQ-HPLC-003',
                    'description' => 'High-Performance Liquid Chromatography system configured for drug purity analysis.',
                    'make' => 'Shimadzu',
                    'model' => 'LC-20AD',
                    'date_purchased' => '2023-05-10',
                    'maintainance_days' => 180,
                    'maintainance_notification_in_days' => 15,
                    'calibration_days' => 365,
                    'calibration_notification_in_days' => 30,
                    'requires_daily_log' => true,
                    'lab_id' => $chemLabId,
                    'assigned_department' => 'Forensic Chemistry Department',
                ],
                [
                    'name' => 'ABI 3500xl DNA Genetic Analyzer',
                    'equipment_number' => 'EQ-SEQ-004',
                    'description' => '24-capillary electrophoresis system for human and non-human wildlife STR DNA profiling.',
                    'make' => 'Applied Biosystems',
                    'model' => '3500xl',
                    'date_purchased' => '2024-01-08',
                    'maintainance_days' => 30,
                    'maintainance_notification_in_days' => 5,
                    'calibration_days' => 180,
                    'calibration_notification_in_days' => 15,
                    'requires_daily_log' => true,
                    'lab_id' => $dnaLabId,
                    'assigned_department' => 'Forensic DNA Department',
                ],
                [
                    'name' => 'Qubit 4 Fluorometer',
                    'equipment_number' => 'EQ-QUBIT-005',
                    'description' => 'Fluorometer for high-precision quantitation of DNA, RNA, and proteins prior to PCR.',
                    'make' => 'Thermo Fisher Scientific',
                    'model' => 'Qubit 4',
                    'date_purchased' => '2024-04-01',
                    'maintainance_days' => 60,
                    'maintainance_notification_in_days' => 7,
                    'calibration_days' => 90,
                    'calibration_notification_in_days' => 10,
                    'requires_daily_log' => true,
                    'lab_id' => $dnaLabId,
                    'assigned_department' => 'Forensic DNA Department',
                ]
            ];

            // 3. Clear existing equipment records in public schema to avoid duplicates
            DB::connection('pgsql')->table('equipment')->delete();
            $this->command?->info('Cleared existing operational equipment records.');

            // 4. Seed equipment & logs
            $now = now();
            foreach ($instruments as $inst) {
                $id = Str::uuid()->toString();

                DB::connection('pgsql')->table('equipment')->insert([
                    'id' => $id,
                    'name' => $inst['name'],
                    'equipment_number' => $inst['equipment_number'],
                    'description' => $inst['description'],
                    'make' => $inst['make'],
                    'model' => $inst['model'],
                    'date_purchased' => $inst['date_purchased'],
                    'maintainance_days' => $inst['maintainance_days'],
                    'maintainance_notification_in_days' => $inst['maintainance_notification_in_days'],
                    'calibration_days' => $inst['calibration_days'],
                    'calibration_notification_in_days' => $inst['calibration_notification_in_days'],
                    'company_id' => $companyId,
                    'lab_id' => $inst['lab_id'],
                    'assigned_department' => $inst['assigned_department'],
                    'active' => true,
                    'is_disposal' => false,
                    'requires_daily_log' => $inst['requires_daily_log'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $this->command?->info("Seeded Instrument: {$inst['name']} ({$inst['equipment_number']})");

                // Seed some historical calibration & maintenance records for analytics
                // Event 1: Calibration completed successfully in the past
                DB::connection('pgsql')->table('maintainance_calibration_logs')->insert([
                    'id' => Str::uuid()->toString(),
                    'equipment_id' => $id,
                    'type' => 'calibration',
                    'date' => date('Y-m-d', strtotime('-4 months')),
                    'notes' => 'Annual master calibration completed against NIST traceable standards. Deviation within allowable 0.05% tolerance.',
                    'certificate' => 'CERT-CAL-' . rand(10000, 99999),
                    'overseen_by' => $operatorName,
                    'operator_approve' => true,
                    'proccess_owner_approve' => true,
                    'maintainance_type' => 'Routine Calibration',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Event 2: Maintenance event (routine cleanup)
                DB::connection('pgsql')->table('maintainance_calibration_logs')->insert([
                    'id' => Str::uuid()->toString(),
                    'equipment_id' => $id,
                    'type' => 'maintenance',
                    'date' => date('Y-m-d', strtotime('-2 months')),
                    'notes' => 'Routine preventive maintenance: replaced vacuum pumps oil, cleaned ion source, verified injector gas flows.',
                    'certificate' => 'no-document',
                    'overseen_by' => $operatorName,
                    'operator_approve' => true,
                    'proccess_owner_approve' => true,
                    'maintainance_type' => 'Preventive Maintenance',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Event 3: Verification event (daily standard check)
                DB::connection('pgsql')->table('maintainance_calibration_logs')->insert([
                    'id' => Str::uuid()->toString(),
                    'equipment_id' => $id,
                    'type' => 'verification',
                    'date' => date('Y-m-d', strtotime('-1 week')),
                    'notes' => 'Daily baseline stability check run with certified QC standard. Met target parameters.',
                    'certificate' => 'no-document',
                    'overseen_by' => $operatorName,
                    'operator_approve' => true,
                    'proccess_owner_approve' => true,
                    'maintainance_type' => 'Routine Verification',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->command?->info('Completed seeding equipment calibration & maintenance logs.');



            // Clear cache
            \Illuminate\Support\Facades\Cache::flush();

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 8 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
