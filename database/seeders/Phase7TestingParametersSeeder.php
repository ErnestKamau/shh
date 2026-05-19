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
                'ALY-EC' => [ // E. Coli
                    'lod' => 0.0,
                    'hod' => 1.0,
                    'sig_figs' => 2,
                    'reporting_time' => 24, // hours
                ],
                'ALY-PB' => [ // Lead Content
                    'lod' => 0.0001,
                    'hod' => 0.05,
                    'sig_figs' => 4,
                    'reporting_time' => 48,
                ],
                'ALY-CAF' => [ // Caffeine
                    'lod' => 0.01,
                    'hod' => 5.0,
                    'sig_figs' => 3,
                    'reporting_time' => 12,
                ],
                'ALY-HB' => [ // Hemoglobin
                    'lod' => 5.0,
                    'hod' => 22.0,
                    'sig_figs' => 3,
                    'reporting_time' => 4,
                ],
                'ALY-PH' => [ // Soil pH
                    'lod' => 3.5,
                    'hod' => 10.5,
                    'sig_figs' => 2,
                    'reporting_time' => 2,
                ],
                'ALY-NIT' => [ // Nitrogen
                    'lod' => 1.0,
                    'hod' => 60.0,
                    'sig_figs' => 3,
                    'reporting_time' => 36,
                ],
                'ALY-GLY' => [ // Glyphosate
                    'lod' => 0.05,
                    'hod' => 10.0,
                    'sig_figs' => 3,
                    'reporting_time' => 72,
                ],
                'ALY-PHO' => [ // Phosphorus
                    'lod' => 1.0,
                    'hod' => 60.0,
                    'sig_figs' => 3,
                    'reporting_time' => 36,
                ],
                'ALY-AFL' => [ // Aflatoxin B1
                    'lod' => 0.1,
                    'hod' => 20.0,
                    'sig_figs' => 3,
                    'reporting_time' => 48,
                ],
                'ALY-CO' => [ // Carbon Monoxide
                    'lod' => 0.1,
                    'hod' => 60.0,
                    'sig_figs' => 3,
                    'reporting_time' => 6,
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
                        'very_low_guide' => max(0.000000, $spec['lod'] - ($spec['lod'] * 0.1)),
                        'very_high_guide' => $spec['hod'] + ($spec['hod'] * 0.2),
                        'correct_target' => ($spec['lod'] + $spec['hod']) / 2.0,
                        'standard_target' => ($spec['lod'] + $spec['hod']) / 2.0,
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
                        'very_low_guide' => max(0.000000, $spec['lod'] - ($spec['lod'] * 0.1)),
                        'very_high_guide' => $spec['hod'] + ($spec['hod'] * 0.2),
                        'correct_target' => ($spec['lod'] + $spec['hod']) / 2.0,
                        'standard_target' => ($spec['lod'] + $spec['hod']) / 2.0,
                    ]);

                $retrofittedQc++;
            }

            $this->command?->info("Successfully retrofitted guides/bounds for {$retrofittedQc} QC measurement runs.");

            // ----------------------------------------------------------------
            // 6. Upgraded Parity Syncing to Reporting Schema
            // ----------------------------------------------------------------
            $this->command?->info('Syncing upgraded testing parameters to reporting schema...');

            // Update reporting.qc_results directly
            DB::connection('pgsql')->statement("
                UPDATE reporting.qc_results qr
                SET 
                    guide_low = pr.guide_low,
                    guide_high = pr.guide_high,
                    synced_at = now()::text
                FROM public.qc_results pr
                WHERE ('x' || substr(replace(pr.id::text, '-', ''), 1, 15))::bit(60)::bigint = qr.source_id;
            ");

            $this->command?->info('Reporting schema synchronized.');

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
