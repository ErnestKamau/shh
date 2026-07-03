<?php

namespace Database\Seeders;

use App\Company;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\User;
use Database\Seeders\Concerns\ClearsSeedCommercialDemoData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase15EquipmentMonitoringSeeder extends Seeder
{
    use ClearsSeedCommercialDemoData;
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 15 SEEDING: Equipment Monitoring History');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $staffUser = User::query()->where('active', 1)->where('is_client', 0)->orderBy('id')->first()
                ?? User::query()->orderBy('id')->first();

            if (! $staffUser) {
                $this->command?->error('No staff user found for equipment monitoring logs.');

                return;
            }

            $this->clearSeedEquipmentMonitoringData();

            $equipmentRows = DB::connection('pgsql')->table('equipment')
                ->where('company_id', $company->id)
                ->where('equipment_number', 'like', 'EQ-%')
                ->where('active', true)
                ->orderBy('equipment_number')
                ->limit(6)
                ->get();

            if ($equipmentRows->isEmpty()) {
                $this->command?->error('No seeded equipment found. Run Phase 12 first.');

                return;
            }

            $logCount = 0;
            $dailyCount = 0;

            foreach ($equipmentRows as $index => $equipment) {
                $logCount += $this->seedMaintenanceLog($equipment, $staffUser, $index);
                $logCount += $this->seedCalibrationLog($equipment, $staffUser, $index);

                if ((bool) ($equipment->requires_daily_log ?? false)) {
                    $dailyCount += $this->seedDailyLogEntries($company, $equipment, $index);
                }
            }

            $this->command?->info("Seeded {$logCount} maintenance/calibration log(s) and {$dailyCount} daily log entr(ies).");
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 15 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function seedMaintenanceLog(object $equipment, User $staffUser, int $index): int
    {
        MaintainanceCalibrationLog::query()->create([
            'equipment_id' => $equipment->id,
            'description' => 'Preventive maintenance — filters, seals, and general inspection.',
            'service_provider' => 'AmSpec Technical Services',
            'notes' => 'Seed maintenance record for demo dashboards.',
            'type' => 'Maintainance',
            'date' => now()->subDays(45 + ($index * 3))->toDateString(),
            'reference_number' => 'SEED-MNT-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'maintenance_type' => 'Preventive',
            'overseen_by' => (string) $staffUser->id,
            'employee_id' => (string) $staffUser->id,
            'comments' => 'Completed without anomalies.',
        ]);

        return 1;
    }

    private function seedCalibrationLog(object $equipment, User $staffUser, int $index): int
    {
        MaintainanceCalibrationLog::query()->create([
            'equipment_id' => $equipment->id,
            'description' => 'Annual calibration against reference standards.',
            'service_provider' => 'Fluke Calibration UAE',
            'notes' => 'Seed calibration certificate on file.',
            'correction_factor' => 1.000000,
            'uncertainty_of_measure' => 0.015000,
            'type' => 'Calibration',
            'date' => now()->subDays(90 + ($index * 5))->toDateString(),
            'reference_number' => 'SEED-CAL-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'certificate' => '/storage/certificate/seed-cal-'.($index + 1).'.pdf',
            'overseen_by' => (string) $staffUser->id,
            'employee_id' => (string) $staffUser->id,
            'comments' => 'Within acceptance limits.',
        ]);

        return 1;
    }

    private function seedDailyLogEntries(Company $company, object $equipment, int $index): int
    {
        $count = 0;

        foreach (range(1, 5) as $dayOffset) {
            DB::connection('pgsql')->table('equipment_daily_log_entries')->updateOrInsert(
                [
                    'equipment_id' => $equipment->id,
                    'log_date' => now()->subDays($dayOffset)->toDateString(),
                    'slot_number' => 1,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'recorded_value' => 'SEED-PASS-'.($index + 1),
                    'recorded_by' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $count++;
        }

        return $count;
    }
}
