<?php

namespace Database\Seeders;

use App\Lab;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\ClearsAmSpecPersonnelData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Phase6PersonnelLabInsightsSeeder extends Seeder
{
    use ClearsAmSpecPersonnelData;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 6 SEEDING: Personnel + Lab Insights');
            $this->command?->info('====================================================');

            $this->clearAmSpecPersonnelData();

            $templateUser = User::query()->where('active', 1)->first() ?? User::query()->first();
            if (! $templateUser) {
                $this->command?->error('No template user found.');
                return;
            }

            $amspecLabCodes = array_column(AmSpecSeedData::labTypes(), 'code');

            $labs = Lab::query()
                ->with(['directorate', 'zone'])
                ->where('active', true)
                ->whereIn('code', $amspecLabCodes)
                ->orderBy('code')
                ->get();
            if ($labs->isEmpty()) {
                $this->command?->error('No labs found. Run Phase 5 first.');
                return;
            }

            $names = [
                'Sarah Jenkins', 'Dr. Marcus Vance', 'Elena Rostova', 'Dr. Raj Koothrappali', 'Dr. Amy Farrah', 'Dr. Bernadette Rosten', 'Dr. Howard Wolowitz',
                'Dr. Leonard Hofstadter', 'Dr. Sheldon Cooper', 'Penny Hofstadter', 'Stuart Bloom', 'Leslie Winkle', 'Barry Kripke', 'Emily Sweeney',
                'Dr. Walter White', 'Jesse Pinkman', 'Saul Goodman', 'Mike Ehrmantraut', 'Gustavo Fring', 'Hank Schrader', 'Kim Wexler',
                'Dr. Arthur Spooner', 'Zack Johnson', 'Priya Koothrappali', 'Beverley Hofstadter', 'Wil Wheaton', 'Skyler White', 'Marie Schrader',
                'Todd Alquist', 'Lydia Rodarte', 'Lalo Salamanca', 'Nacho Varga', 'Chuck McGill', 'Howard Hamlin', 'Tuco Salamanca',
                'Gale Boetticher', 'Hector Salamanca', 'Eduardo Salamanca', 'Domingo Molina', 'Christian Bale', 'Daniel Craig', 'Elena Gilbert'
            ];

            $designations = [
                'Senior Analytical Chemist', 'Lead DNA Analyst', 'Quality Control Analyst',
                'Organic Residues Specialist', 'Lead Microbiologist', 'Biosafety Officer',
                'Lead Instrumentation Engineer'
            ];

            $analystIds = [];
            foreach ($labs as $index => $lab) {
                $fullName = $names[$index % count($names)];
                $email = strtolower(str_replace([' ', '.'], '', $fullName)).'.'.strtolower($lab->code).'@'.AmSpecSeedData::PERSONNEL_EMAIL_DOMAIN;
                $designation = $designations[$index % count($designations)];

                $user = User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fullName,
                        'password' => $templateUser->password ?: bcrypt('Secret123!'),
                        'company_id' => $templateUser->company_id,
                        'active' => 1,
                        'location_id' => $templateUser->location_id ?? 3,
                        'zone_id' => $lab->zone_id,
                        'is_client' => false,
                        'is_online' => false,
                        'designation' => $designation,
                        'lab_id' => $lab->id,
                    ]
                );

                $analystIds[] = $user->id;
                $this->assignUser($user->id, $lab);
                $this->command?->info("Seeded analyst {$user->name} -> {$lab->code}");
            }

            foreach ($labs as $index => $lab) {
                $assigned = array_values(array_filter($analystIds, fn ($_id, $i) => $i % $labs->count() === $index % $labs->count(), ARRAY_FILTER_USE_BOTH));
                $lab->update(['analyst_ids' => $assigned ?: $analystIds]);
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 6 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function assignUser(string $userId, Lab $lab): void
    {
        if (Schema::hasTable('user_lab_relation')) {
            DB::connection('pgsql')->table('user_lab_relation')->updateOrInsert(
                ['user_id' => $userId, 'lab_id' => $lab->id],
                ['id' => $this->relationId('user_lab_relation', ['user_id' => $userId, 'lab_id' => $lab->id]), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        if ($lab->zone_id && Schema::hasTable('user_zone_relation')) {
            DB::connection('pgsql')->table('user_zone_relation')->updateOrInsert(
                ['user_id' => $userId, 'zone_id' => $lab->zone_id],
                ['id' => $this->relationId('user_zone_relation', ['user_id' => $userId, 'zone_id' => $lab->zone_id]), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        if ($lab->directorate_id && Schema::hasTable('user_directorate_relation')) {
            DB::connection('pgsql')->table('user_directorate_relation')->updateOrInsert(
                ['user_id' => $userId, 'directorate_id' => $lab->directorate_id],
                ['id' => $this->relationId('user_directorate_relation', ['user_id' => $userId, 'directorate_id' => $lab->directorate_id]), 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function relationId(string $table, array $where): string
    {
        $query = DB::connection('pgsql')->table($table);
        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        return $query->value('id') ?? (string) Str::uuid();
    }
}
