<?php

namespace Database\Seeders;

use App\Company;
use App\Lab;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase12EquipmentManagementSeeder extends Seeder
{
    public function run(): void
    {
        config(['database.default' => 'pgsql']);

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 12 SEEDING: Equipment Management');
            $this->command?->info('====================================================');

            $company = Company::query()->first();
            $labs = Lab::query()->get();
            if (! $company || $labs->isEmpty()) {
                $this->command?->error('Missing company or labs. Run earlier phases first.');
                return;
            }

            $equipmentSpecs = [
                'LAB-FCH' => ['name' => 'Agilent 8890 Gas Chromatograph', 'make' => 'Agilent Technologies', 'model' => '8890 / 5977B', 'prefix' => 'EQ-GCMS'],
                'LAB-FDNA' => ['name' => 'ABI 3500xl DNA Genetic Analyzer', 'make' => 'Applied Biosystems', 'model' => '3500xl', 'prefix' => 'EQ-SEQ'],
                'LAB-FTOX' => ['name' => 'LC-MS/MS Toxicology System', 'make' => 'Waters', 'model' => 'Xevo TQ-S', 'prefix' => 'EQ-LCMS'],
                'LAB-FD' => ['name' => 'Shimadzu HPLC Food and Drug System', 'make' => 'Shimadzu', 'model' => 'LC-20AD', 'prefix' => 'EQ-HPLC'],
                'LAB-MIC' => ['name' => 'CO2 Microbiology Incubator', 'make' => 'Thermo Fisher', 'model' => 'Heracell VIOS', 'prefix' => 'EQ-INC'],
                'LAB-ENV' => ['name' => 'ICP-MS Environmental Metals Analyzer', 'make' => 'PerkinElmer', 'model' => 'NexION 2000', 'prefix' => 'EQ-ICP'],
                'LAB-TSU' => ['name' => 'Reference Calibration Workstation', 'make' => 'Fluke', 'model' => '5522A', 'prefix' => 'EQ-CAL'],
            ];

            $seededCount = 0;
            foreach ($labs as $lab) {
                $baseCode = null;
                foreach (array_keys($equipmentSpecs) as $specCode) {
                    if (str_starts_with($lab->code, $specCode)) {
                        $baseCode = $specCode;
                        break;
                    }
                }

                if (!$baseCode) {
                    continue;
                }

                $spec = $equipmentSpecs[$baseCode];
                $equipmentNumber = "{$spec['prefix']}-" . str_replace('LAB-', '', $lab->code);

                $id = DB::connection('pgsql')->table('equipment')
                    ->where('equipment_number', $equipmentNumber)
                    ->value('id') ?? (string) Str::uuid();

                DB::connection('pgsql')->table('equipment')->updateOrInsert(
                    ['id' => $id],
                    [
                        'name' => "{$spec['name']} ({$lab->zone?->value})",
                        'equipment_number' => $equipmentNumber,
                        'description' => "{$spec['name']} assigned to {$lab->name} in {$lab->zone?->value}.",
                        'make' => $spec['make'],
                        'model' => $spec['model'],
                        'date_purchased' => '2024-01-15',
                        'maintainance_days' => 180,
                        'maintainance_notification_in_days' => 14,
                        'calibration_days' => 365,
                        'calibration_notification_in_days' => 30,
                        'company_id' => $company->id,
                        'assigned_department' => $lab->name,
                        'lab_id' => $lab->id,
                        'active' => true,
                        'is_disposal' => false,
                        'requires_daily_log' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $this->command?->info("Seeded Equipment: {$equipmentNumber} -> {$lab->code}");
                $seededCount++;
            }

            $this->command?->info("Successfully seeded {$seededCount} physical instrumentation assets.");
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 12 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });
    }
}
