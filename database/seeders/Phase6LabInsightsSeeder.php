<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase6LabInsightsSeeder extends Seeder
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
            $this->command?->info('STARTING PHASE 6 SEEDING: 6 Dedicated Analysts & Lab Sections');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 1. Retrieve a template user to copy NOT NULL fields
            // ----------------------------------------------------------------
            $templateUser = DB::connection('pgsql')->table('users')
                ->where('name', 'Dante Muvinju')
                ->first() ?? DB::connection('pgsql')->table('users')->first();

            if (!$templateUser) {
                $this->command?->error('No template user found to replicate user schema constraints.');
                return;
            }

            $locationId = $templateUser->location_id;
            $passwordHash = $templateUser->password ?: bcrypt('Secret123!');

            // ----------------------------------------------------------------
            // 2. Seed 6 Dedicated Analyst Personnel
            // ----------------------------------------------------------------
            $analysts = [
                [
                    'name' => 'Sarah Jenkins',
                    'email' => 's.jenkins@gcla-labs.com',
                    'designation' => 'Senior Analytical Chemist',
                ],
                [
                    'name' => 'Dr. Marcus Vance',
                    'email' => 'm.vance@gcla-labs.com',
                    'designation' => 'Lead Microbiologist',
                ],
                [
                    'name' => 'Elena Rostova',
                    'email' => 'e.rostova@gcla-labs.com',
                    'designation' => 'Quality Control Analyst',
                ],
                [
                    'name' => 'Dr. Raj Koothrappali',
                    'email' => 'r.kooth@gcla-labs.com',
                    'designation' => 'Organic Residues Specialist',
                ],
                [
                    'name' => 'Dr. Amy Farrah',
                    'email' => 'a.farrah@gcla-labs.com',
                    'designation' => 'Lead Neuro-Biologist',
                ],
                [
                    'name' => 'Dr. Bernadette Rosten',
                    'email' => 'b.rosten@gcla-labs.com',
                    'designation' => 'Biosafety Officer',
                ],
            ];

            $analystIds = [];

            foreach ($analysts as $analystData) {
                $existing = DB::connection('pgsql')->table('users')
                    ->where('email', $analystData['email'])
                    ->first();

                if ($existing) {
                    $analystIds[$analystData['name']] = $existing->id;
                    $this->command?->info("Analyst already exists: {$analystData['name']}");
                } else {
                    $newId = (string) Str::uuid();
                    DB::connection('pgsql')->table('users')->insert([
                        'id' => $newId,
                        'name' => $analystData['name'],
                        'email' => $analystData['email'],
                        'password' => $passwordHash,
                        'active' => 1,
                        'location_id' => $locationId,
                        'is_client' => false,
                        'is_online' => false,
                        'failed_login_attempts' => 0,
                        'login_locked_by_admin_reset' => false,
                        'analyst_is_gazzetted' => true,
                        'designation' => $analystData['designation'],
                        'position' => $analystData['designation'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $analystIds[$analystData['name']] = $newId;
                    $this->command?->info("Seeded Analyst: {$analystData['name']} ({$analystData['designation']})");
                }
            }


            // ----------------------------------------------------------------
            // 2b. Link analysts to lab departments (analyst_ids JSON)
            // ----------------------------------------------------------------
            $analystIdValues = array_values($analystIds);
            DB::connection('pgsql')->table('labs')
                ->where('active', true)
                ->update(['analyst_ids' => json_encode($analystIdValues)]);

            $this->command?->info('  ✓ Linked ' . count($analystIdValues) . ' dedicated analysts to all lab departments.');

            // ----------------------------------------------------------------
            // 2c. Redistribute specialist_analyst_id on sample_headers
            //     so every batch is owned by a named forensic analyst.
            // ----------------------------------------------------------------
            $headerIds = DB::connection('pgsql')->table('sample_headers')
                ->orderBy('created_at')
                ->pluck('id');

            $analystNames  = array_keys($analystIds);
            $analystCount  = count($analystNames);
            $reassigned    = 0;

            foreach ($headerIds as $idx => $headerId) {
                $analystName = $analystNames[$idx % $analystCount];
                $analystId   = $analystIds[$analystName];

                DB::connection('pgsql')->table('sample_headers')
                    ->where('id', $headerId)
                    ->update([
                        'specialist_analyst_id' => $analystId,
                        'sampling_officer'      => $analystId,
                    ]);

                // Also update captured_results for this header so user_id is set
                DB::connection('pgsql')->table('captured_results')
                    ->where('sample_header_id', $headerId)
                    ->update(['user_id' => $analystId]);

                $reassigned++;
            }

            $this->command?->info("  ✓ Assigned dedicated analysts to {$reassigned} sample batches and their captured results.");

            // ----------------------------------------------------------------
            // 3. Rename Workflow Stages → Lab Section Names for reporting views
            // ----------------------------------------------------------------
            $this->command?->info('Upgrading Workflow Stages to Laboratory Section names...');

            DB::connection('pgsql')->table('sample_analysis_stages')
                ->where('name', 'Sample Request & Submission')
                ->update(['name' => 'Narcotics & Drug Chemistry']);

            DB::connection('pgsql')->table('sample_analysis_stages')
                ->where('name', 'Sample Receipt & Preparation')
                ->update(['name' => 'Human DNA Profiling']);

            DB::connection('pgsql')->table('sample_analysis_stages')
                ->where('name', 'Analysis in Progress')
                ->update(['name' => 'Wildlife DNA & Genetics']);

            DB::connection('pgsql')->table('sample_analysis_stages')
                ->where('name', 'Technical Verification')
                ->update(['name' => 'Forensic Toxicology']);

            DB::connection('pgsql')->table('sample_analysis_stages')
                ->where('name', 'Report Approved & Released')
                ->update(['name' => 'Trace Evidence & Arson']);

            $stages = DB::connection('pgsql')->table('sample_analysis_stages')
                ->where('active', 1)
                ->get()
                ->keyBy('name');

            if ($stages->isEmpty()) {
                $this->command?->error('No sample analysis stages found! Run Phase 1 first.');
                return;
            }

            $narcoticsId   = $stages->get('Narcotics & Drug Chemistry')?->id ?? $stages->first()?->id;
            $humanDnaId    = $stages->get('Human DNA Profiling')?->id ?? $stages->first()?->id;
            $wildlifeDnaId = $stages->get('Wildlife DNA & Genetics')?->id ?? $stages->first()?->id;
            $toxicologyId  = $stages->get('Forensic Toxicology')?->id ?? $stages->first()?->id;
            $traceId       = $stages->get('Trace Evidence & Arson')?->id ?? $stages->first()?->id;

            // Fetch all results to map their lab_section_id based on Sample Types
            $results = DB::connection('pgsql')->table('results')->get();
            $resultsCount = $results->count();

            $this->command?->info("Mapping lab sections for {$resultsCount} analytical results based on forensic analytes...");

            $mappedCount = 0;
            foreach ($results as $result) {
                // Determine the analyte name dynamically
                $analyteName = DB::connection('pgsql')->table('analytes')
                    ->where('id', $result->analyte_id)
                    ->value('name');

                // Map logically based on forensic specialties
                if ($analyteName === 'Human STR DNA Profile Match') {
                    $stageId = $humanDnaId;
                } elseif ($analyteName === 'Wildlife DNA Species Similarity') {
                    $stageId = $wildlifeDnaId;
                } elseif (in_array($analyteName, [
                    'Tetrahydrocannabinol (THC)',
                    'Cathinone Content',
                    'Cocaine Hydrochloride',
                    '6-Monoacetylmorphine (Heroin metabolite)',
                    'Amphetamine Base',
                    'Methamphetamine Content',
                    'Fentanyl Trace Concentration'
                ])) {
                    $stageId = $narcoticsId;
                } elseif ($analyteName === 'Cyanide Concentration') {
                    $stageId = $toxicologyId;
                } else {
                    $stageId = $traceId;
                }

                if ($stageId) {
                    DB::connection('pgsql')->table('results')
                        ->where('id', $result->id)
                        ->update(['lab_section_id' => $stageId]);
                    $mappedCount++;
                }
            }

            $this->command?->info("Successfully mapped {$mappedCount} results to active forensic laboratory sections.");

            // ----------------------------------------------------------------
            // 4. Redistribute tat_captured across analysts with statistical variance
            // ----------------------------------------------------------------
            $tatRecords = DB::connection('pgsql')->table('tat_captured')->get();
            $tatCount = $tatRecords->count();

            $analystNames = array_keys($analystIds);
            $analystCount = count($analystNames);
            $this->command?->info("Redistributing {$tatCount} TAT workload records among {$analystCount} analysts...");

            $redistributed = 0;

            foreach ($tatRecords as $index => $tat) {
                // Select analyst cyclically
                $analystName = $analystNames[$index % count($analystNames)];
                $analystId = $analystIds[$analystName];

                $createdAt = \Carbon\Carbon::parse($tat->start_date_analysis ?: $tat->created_at);
                $targetDeadline = \Carbon\Carbon::parse($tat->tat_date ?: $createdAt->copy()->addDays(2));

                if ($analystName === 'Elena Rostova') {
                    // 98% on-time: finishes early
                    $finishedDate = $createdAt->copy()->addDay()->addHours(rand(1, 6));
                } elseif ($analystName === 'Dr. Amy Farrah') {
                    if (rand(1, 100) <= 91) {
                        $finishedDate = $createdAt->copy()->addDay()->addHours(rand(2, 10));
                    } else {
                        $finishedDate = $targetDeadline->copy()->addHours(rand(1, 4));
                    }
                } elseif ($analystName === 'Dr. Bernadette Rosten') {
                    if (rand(1, 100) <= 85) {
                        $finishedDate = $createdAt->copy()->addDay()->addHours(rand(2, 16));
                    } else {
                        $finishedDate = $targetDeadline->copy()->addDay()->addHours(rand(1, 2));
                    }
                } elseif ($analystName === 'Sarah Jenkins') {
                    if (rand(1, 100) <= 80) {
                        $finishedDate = $createdAt->copy()->addDay()->addHours(rand(4, 18));
                    } else {
                        $finishedDate = $targetDeadline->copy()->addDay()->addHours(rand(1, 6));
                    }

                } elseif ($analystName === 'Dr. Raj Koothrappali') {
                    if (rand(1, 100) <= 66) {
                        $finishedDate = $createdAt->copy()->addDay()->addHours(rand(4, 22));
                    } else {
                        $finishedDate = $targetDeadline->copy()->addDay()->addHours(rand(12, 36));
                    }
                } else { // Dr. Marcus Vance
                    if (rand(1, 100) <= 52) {
                        $finishedDate = $createdAt->copy()->addDay()->addHours(rand(1, 12));
                    } else {
                        $finishedDate = $targetDeadline->copy()->addDays(2)->addHours(rand(1, 12));
                    }
                }

                $isOverdue = $finishedDate->gt($targetDeadline);
                
                // Securely cast and take absolute values of difference to satisfy PGSQL constraints
                $diffDays = (int) abs($finishedDate->diffInDays($targetDeadline));
                $overdueDays = $isOverdue ? $diffDays : -$diffDays;

                // Absolute upper bound clamp on overdue days to keep offsets clean
                if ($overdueDays > 4) {
                    $overdueDays = rand(1, 3);
                } elseif ($overdueDays < -4) {
                    $overdueDays = -rand(1, 2);
                }

                DB::connection('pgsql')->table('tat_captured')
                    ->where('id', $tat->id)
                    ->update([
                        'analyst_id' => $analystId,
                        'tat_overdue_days' => $overdueDays,
                        'finished_date' => $finishedDate,
                    ]);

                if ($tat->captured_result_id) {
                    DB::connection('pgsql')->table('captured_results')
                        ->where('id', $tat->captured_result_id)
                        ->update(['user_id' => $analystId]);
                }

                $redistributed++;
            }

            $this->command?->info("Successfully redistributed {$redistributed} TAT workloads.");

            // ----------------------------------------------------------------
            // 5. Clear Cache for Real-time Dashboard Refresh
            // ----------------------------------------------------------------
            \Illuminate\Support\Facades\Cache::flush();

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 6 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
