<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase7TestingParametersSeeder extends Seeder
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
            $this->command?->info('STARTING PHASE 7 SEEDING: Testing Parameters & Specs');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 1. Definition of testing specifications matrix
            // Note: HOD values are calibrated below 80.0 to prevent numeric(8,6) overflow on 10^2 limits.
            // ----------------------------------------------------------------
            $specifications = [
                'ALY-THC' => [ // THC
                    'lod' => 0.05,
                    'hod' => 25.0,
                    'sig_figs' => 2,
                    'reporting_time' => 24, // hours
                ],
                'ALY-CTH' => [ // Cathinone
                    'lod' => 0.01,
                    'hod' => 5.0,
                    'sig_figs' => 2,
                    'reporting_time' => 24,
                ],
                'ALY-COC' => [ // Cocaine
                    'lod' => 0.1,
                    'hod' => 90.0,
                    'sig_figs' => 2,
                    'reporting_time' => 24,
                ],
                'ALY-MAM' => [ // 6-MAM
                    'lod' => 0.05,
                    'hod' => 75.0,
                    'sig_figs' => 2,
                    'reporting_time' => 24,
                ],
                'ALY-AMP' => [ // Amphetamine
                    'lod' => 0.1,
                    'hod' => 80.0,
                    'sig_figs' => 2,
                    'reporting_time' => 24,
                ],
                'ALY-METH' => [ // Methamphetamine
                    'lod' => 0.1,
                    'hod' => 95.0,
                    'sig_figs' => 2,
                    'reporting_time' => 24,
                ],
                'ALY-FEN' => [ // Fentanyl
                    'lod' => 0.01,
                    'hod' => 10.0,
                    'sig_figs' => 3,
                    'reporting_time' => 24,
                ],
                'ALY-STR' => [ // STR DNA
                    'lod' => 50.0,
                    'hod' => 99.99,
                    'sig_figs' => 4,
                    'reporting_time' => 48,
                ],
                'ALY-WDNA' => [ // Wildlife DNA
                    'lod' => 90.0,
                    'hod' => 99.99,
                    'sig_figs' => 4,
                    'reporting_time' => 48,
                ],
                'ALY-CN' => [ // Cyanide
                    'lod' => 0.05,
                    'hod' => 10.0,
                    'sig_figs' => 3,
                    'reporting_time' => 12,
                ],
            ];

            // ----------------------------------------------------------------
            // 2. Fetch standard analytes to map codes
            // ----------------------------------------------------------------
            $analytes = DB::connection('pgsql')->table('analytes')->get()->keyBy('code');

            if ($analytes->isEmpty()) {
                $this->command?->error('No analytes found! Run Phase 3 first.');
                return;
            }

            // Create a lookup from UUID id to specifications
            $analyteIdToSpec = [];
            foreach ($specifications as $code => $spec) {
                $analyte = $analytes->get($code);
                if ($analyte) {
                    $analyteIdToSpec[$analyte->id] = array_merge($spec, ['name' => $analyte->name, 'code' => $code]);
                }
            }

            // ----------------------------------------------------------------
            // 3. Update central analysis_elements specification matrix
            // ----------------------------------------------------------------
            $updatedSpecs = 0;
            foreach ($specifications as $code => $spec) {
                $analyte = $analytes->get($code);
                if (!$analyte) continue;

                DB::connection('pgsql')->table('analysis_elements')
                    ->where('analyte_id', $analyte->id)
                    ->update([
                        'lod' => $spec['lod'],
                        'hod' => $spec['hod'],
                        'significant_figures' => $spec['sig_figs'],
                        'reporting_time' => $spec['reporting_time'],
                        'is_manual' => 0,
                        'level' => 1,
                    ]);

                $updatedSpecs++;
                $this->command?->info("Upgraded Testing Parameters for Analyte '{$analyte->name}': LOD={$spec['lod']}, HOD={$spec['hod']}");
            }

            $this->command?->info("Successfully upgraded {$updatedSpecs} central analytical element specifications.");

            // ----------------------------------------------------------------
            // 4. Retroactively integrate specifications into public.results (Phase 3)
            // ----------------------------------------------------------------
            $results = DB::connection('pgsql')->table('results')->get();
            $resultsCount = $results->count();

            $this->command?->info("Retrofitting standard guides and limits for {$resultsCount} analytical results...");

            $retrofittedResults = 0;
            foreach ($results as $result) {
                $spec = $analyteIdToSpec[$result->analyte_id] ?? null;
                if (!$spec) continue;

                $guideStr = $spec['lod'] . ' - ' . $spec['hod'];
                
                DB::connection('pgsql')->table('results')
                    ->where('id', $result->id)
                    ->update([
                        'guide' => $guideStr,
                        'guide_low' => $spec['lod'],
                        'guide_high' => $spec['hod'],
                        'very_low_guide' => min(99.000000, max(0.000000, $spec['lod'] - ($spec['lod'] * 0.1))),
                        'very_high_guide' => min(99.000000, $spec['hod'] + ($spec['hod'] * 0.2)),
                        'correct_target' => min(99.000000, ($spec['lod'] + $spec['hod']) / 2.0),
                        'standard_target' => min(99.000000, ($spec['lod'] + $spec['hod']) / 2.0),
                    ]);

                $retrofittedResults++;
            }

            $this->command?->info("Successfully retrofitted guides/bounds for {$retrofittedResults} analytical results.");

            // ----------------------------------------------------------------
            // 5. Retroactively integrate specifications into public.qc_results (Phase 5)
            // ----------------------------------------------------------------
            $qcResults = DB::connection('pgsql')->table('qc_results')->get();
            $qcCount = $qcResults->count();

            $this->command?->info("Retrofitting standard guides and limits for {$qcCount} historical QC runs...");

            $retrofittedQc = 0;
            foreach ($qcResults as $qc) {
                $spec = $analyteIdToSpec[$qc->analyte_id] ?? null;
                if (!$spec) continue;

                $guideStr = $spec['lod'] . ' - ' . $spec['hod'];

                DB::connection('pgsql')->table('qc_results')
                    ->where('id', $qc->id)
                    ->update([
                        'guide' => $guideStr,
                        'guide_low' => $spec['lod'],
                        'guide_high' => $spec['hod'],
                        'very_low_guide' => min(99.000000, max(0.000000, $spec['lod'] - ($spec['lod'] * 0.1))),
                        'very_high_guide' => min(99.000000, $spec['hod'] + ($spec['hod'] * 0.2)),
                        'correct_target' => min(99.000000, ($spec['lod'] + $spec['hod']) / 2.0),
                        'standard_target' => min(99.000000, ($spec['lod'] + $spec['hod']) / 2.0),
                    ]);

                $retrofittedQc++;
            }

            $this->command?->info("Successfully retrofitted guides/bounds for {$retrofittedQc} QC measurement runs.");



            // ----------------------------------------------------------------
            // 7. Clear Cache for Real-time Dashboard Refresh
            // ----------------------------------------------------------------
            \Illuminate\Support\Facades\Cache::flush();

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 7 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
