<?php

namespace App\Services\GroupedWorksheets;

use App\AnalysisType;
use App\CapturedResult;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\SampleHeader;
use Illuminate\Support\Collection;

class GroupedWorksheetAssignmentService
{
    /**
     * @return Collection<int, GroupedWorksheetHolder>
     */
    public function resolveHoldersForBatch(SampleHeader $batch): Collection
    {
        $analysisTypeIds = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->whereNotNull('analysis_type_id')
            ->pluck('analysis_type_id')
            ->unique()
            ->filter()
            ->values();

        if ($analysisTypeIds->isEmpty()) {
            return collect();
        }

        $holderIds = AnalysisType::query()
            ->whereIn('id', $analysisTypeIds)
            ->whereNotNull('grouped_worksheet_holder_id')
            ->pluck('grouped_worksheet_holder_id')
            ->unique()
            ->filter()
            ->values();

        if ($holderIds->isEmpty()) {
            return collect();
        }

        return GroupedWorksheetHolder::query()
            ->whereIn('id', $holderIds)
            ->where('is_active', true)
            ->with(['items'])
            ->orderBy('name')
            ->get();
    }
}
