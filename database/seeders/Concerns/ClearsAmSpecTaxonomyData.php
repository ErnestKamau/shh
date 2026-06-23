<?php

namespace Database\Seeders\Concerns;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Company;
use App\SampleType;

trait ClearsAmSpecTaxonomyData
{
    use ClearsAllSampleBatchData;

    protected function clearAmSpecTaxonomyData(Company $company): void
    {
        $this->clearAllSampleBatchData();

        $companyId = $company->id;

        $analysisTypeIds = AnalysisType::query()
            ->where('company_id', $companyId)
            ->pluck('id');

        $deletedElements = 0;
        if ($analysisTypeIds->isNotEmpty()) {
            $deletedElements = AnalysisElements::query()
                ->whereIn('analysis_type_id', $analysisTypeIds)
                ->delete();
        }

        $deletedAnalysisTypes = AnalysisType::query()
            ->where('company_id', $companyId)
            ->delete();

        $deletedSampleTypes = SampleType::query()
            ->where('company_id', $companyId)
            ->delete();

        $deletedAnalytes = Analyte::withoutGlobalScope('notDeleted')
            ->where('company_id', $companyId)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared AmSpec taxonomy for company %s: %d sample types, %d analysis types, %d tests (elements), %d analytes.',
            $company->name,
            $deletedSampleTypes,
            $deletedAnalysisTypes,
            $deletedElements,
            $deletedAnalytes,
        ));
    }
}
