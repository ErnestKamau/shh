<?php

namespace Database\Seeders;

use App\AnalysisType;
use App\Company;
use App\SampleAnalysisStage;
use App\SampleType;
use App\User;
use App\Zone;
use Database\Seeders\Concerns\ClearsAmSpecWorkflowStagesData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Database\Seeders\Concerns\SeedsTrfWorkflowSamples;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase9SampleWorkflowSeeder extends Seeder
{
    use ClearsAmSpecWorkflowStagesData;
    use ResolvesAmSpecCompany;
    use SeedsTrfWorkflowSamples;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 9 SEEDING: 5 TRF-backed Sample Jobs');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $this->purgeAllSeedTrfChains();
            $this->clearAmSpecWorkflowStagesData($company);

            $zones = Zone::query()->orderBy('key')->get();
            $customers = \App\Models\CRM\CRMCustomer::query()->orderBy('is_internal', 'desc')->orderBy('name')->get();
            $analysisTypes = AnalysisType::query()->with('lab')->where('company_id', $company->id)->get();
            $users = User::query()->where('active', 1)->get();

            if ($zones->isEmpty() || $customers->isEmpty() || $analysisTypes->isEmpty() || $users->isEmpty()) {
                $this->command?->error('Missing zones, CRM customers, analysis types, or users. Run earlier phases first.');

                return;
            }

            $stages = $this->seedWorkflowStages($company, $analysisTypes);

            $created = 0;
            $batchCount = 0;

            foreach ($this->trfWorkflowScenarios() as $scenario) {
                $chain = $this->seedTrfInstanceChain($scenario, $customers, $users, $zones, $stages);
                $created++;

                if ($chain['sampleHeader'] !== null) {
                    $batchCount++;
                }

                $this->command?->info(sprintf(
                    'Seeded %s → pipeline %s, job %s, sample %s, board %s',
                    $scenario['seed_key'],
                    $scenario['pipeline_stage'],
                    $chain['sampleHeader']?->batch_code ?? 'not created',
                    $chain['sampleHeader']?->samples()->value('sample_code') ?? 'not created',
                    $chain['sampleHeader']?->status ?? $chain['submissionFormInstance']->status,
                ));
            }

            $this->command?->info("Seeded {$created} TRF workflow requests ({$batchCount} with lab jobs).");
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 9 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    /**
     * @return array<string, SampleAnalysisStage>
     */
    private function seedWorkflowStages(Company $company, $analysisTypes): array
    {
        $firstLabId = $analysisTypes->first()?->lab_id;
        $stageData = [
            'REC' => ['name' => 'Samples Receiving', 'level' => 1],
            'PREP' => ['name' => 'Samples Request Review', 'level' => 2],
            'TEST' => ['name' => 'Samples In Lab', 'level' => 3],
            'QCR' => ['name' => 'Sample Verification', 'level' => 4],
            'APP' => ['name' => 'Sample Approval', 'level' => 5],
        ];

        $stages = [];
        foreach ($stageData as $code => $data) {
            $stages[$code] = SampleAnalysisStage::query()->updateOrCreate(
                ['code' => $code, 'company_id' => $company->id],
                [
                    'name' => $data['name'],
                    'title' => $data['name'],
                    'active' => true,
                    'level' => $data['level'],
                    'is_system' => true,
                    'is_sample_stage' => true,
                    'sample_workflow' => $data['name'],
                    'lab_id' => $firstLabId,
                ]
            );
        }

        $sampleTypes = SampleType::query()->where('company_id', $company->id)->get();
        foreach ($stages as $stage) {
            foreach ($sampleTypes as $st) {
                DB::connection('pgsql')->table('sample_to_sample_analysis_stages')->updateOrInsert(
                    [
                        'sample_type_id' => $st->id,
                        'sample_analysis_stage_id' => $stage->id,
                    ],
                    [
                        'id' => DB::connection('pgsql')->table('sample_to_sample_analysis_stages')
                            ->where('sample_type_id', $st->id)
                            ->where('sample_analysis_stage_id', $stage->id)
                            ->value('id') ?? (string) Str::uuid(),
                        'active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return $stages;
    }
}
