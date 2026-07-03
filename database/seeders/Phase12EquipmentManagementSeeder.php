<?php

namespace Database\Seeders;

use App\Lab;
use Database\Seeders\Concerns\ClearsAmSpecEquipmentData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase12EquipmentManagementSeeder extends Seeder
{
    use ClearsAmSpecEquipmentData;
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 12 SEEDING: Equipment Management');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();
            $labs = Lab::query()->get();
            if (! $company || $labs->isEmpty()) {
                $this->command?->error('Missing company or labs. Run earlier phases first.');
                return;
            }

            $this->clearAmSpecEquipmentData($company);

            $equipmentSpecs = [
                'LAB-FUEL' => ['name' => 'Anton Paar Fuel Analysis System', 'make' => 'Anton Paar', 'model' => 'SVM 3001', 'prefix' => 'EQ-FUEL'],
                'LAB-CRD' => ['name' => 'Crude Oil Distillation Unit', 'make' => 'Grabner', 'model' => 'MINIDIS ADXpert', 'prefix' => 'EQ-CRD'],
                'LAB-BNK' => ['name' => 'Marine Bunker Fuel Analyzer', 'make' => 'XOS', 'model' => 'Sindie 7039', 'prefix' => 'EQ-BNK'],
                'LAB-AGF' => ['name' => 'CO2 Microbiology Incubator', 'make' => 'Thermo Fisher', 'model' => 'Heracell VIOS', 'prefix' => 'EQ-INC'],
                'LAB-ENV' => ['name' => 'ICP-MS Environmental Metals Analyzer', 'make' => 'PerkinElmer', 'model' => 'NexION 2000', 'prefix' => 'EQ-ICP'],
                'LAB-CHM' => ['name' => 'Agilent 8890 Gas Chromatograph', 'make' => 'Agilent Technologies', 'model' => '8890 / 5977B', 'prefix' => 'EQ-GCMS'],
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
