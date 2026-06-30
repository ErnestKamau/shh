<?php

namespace Database\Seeders\Concerns;

use App\AnalysisElements;
use App\AnalysisType;
use App\Company;
use App\SampleType;
trait ClearsFoodRelatedTaxonomyData
{
    protected function clearFoodRelatedTaxonomyData(Company $company): void
    {
        $companyId = $company->id;

        $sampleTypeIds = SampleType::query()
            ->where('company_id', $companyId)
            ->where(function ($query): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', ['food'])
                    ->orWhereRaw('LOWER(TRIM(name)) IN (?, ?)', ['food and feed', 'food & feed'])
                    ->orWhere('name', 'ilike', '%food%feed%')
                    ->orWhereIn('code', [
                        'FOOD',
                        'FOOD FEED',
                        'FOOD AND FEED',
                        'FOOD_FEED',
                    ])
                    ->orWhere('code', 'ilike', 'FOOD%FEED%');
            })
            ->pluck('id');

        if ($sampleTypeIds->isEmpty()) {
            $this->command?->info('No existing Food / Food and Feed sample types to clear.');

            return;
        }

        $analysisTypeIds = AnalysisType::query()
            ->whereIn('sample_type_id', $sampleTypeIds)
            ->pluck('id');

        $deletedElements = 0;
        if ($analysisTypeIds->isNotEmpty()) {
            $deletedElements = AnalysisElements::query()
                ->whereIn('analysis_type_id', $analysisTypeIds)
                ->delete();
        }

        $deletedAnalysisTypes = AnalysisType::query()
            ->whereIn('sample_type_id', $sampleTypeIds)
            ->delete();

        $deletedSampleTypes = SampleType::query()
            ->whereIn('id', $sampleTypeIds)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared Food-related taxonomy: %d sample type(s), %d analysis type(s), %d element(s).',
            $deletedSampleTypes,
            $deletedAnalysisTypes,
            $deletedElements,
        ));
    }
}
