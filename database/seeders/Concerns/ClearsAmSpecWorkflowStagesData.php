<?php

namespace Database\Seeders\Concerns;

use App\Company;
use App\SampleAnalysisStage;
use Illuminate\Support\Facades\DB;

trait ClearsAmSpecWorkflowStagesData
{
    /** @var list<string> */
    private const WORKFLOW_STAGE_CODES = ['REC', 'PREP', 'TEST', 'QCR', 'APP'];

    protected function clearAmSpecWorkflowStagesData(Company $company): void
    {
        $stageIds = SampleAnalysisStage::query()
            ->where('company_id', $company->id)
            ->whereIn('code', self::WORKFLOW_STAGE_CODES)
            ->pluck('id');

        if ($stageIds->isEmpty()) {
            $this->command?->info('No AmSpec workflow stages to clear.');

            return;
        }

        $deletedRelations = DB::connection('pgsql')
            ->table('sample_to_sample_analysis_stages')
            ->whereIn('sample_analysis_stage_id', $stageIds)
            ->delete();

        $deletedStages = SampleAnalysisStage::query()
            ->whereIn('id', $stageIds)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared AmSpec workflow stages: %d stages, %d sample-type stage links.',
            $deletedStages,
            $deletedRelations,
        ));
    }
}
