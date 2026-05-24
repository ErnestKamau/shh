<?php

namespace App\Services\GroupedWorksheets;

use App\AnalysisType;
use App\CapturedResult;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetRun;
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
            ->values()
            ->all();

        if ($analysisTypeIds === []) {
            $analysisTypeIds = $batch->samples()
                ->whereNotNull('analysis_type_id')
                ->pluck('analysis_type_id')
                ->flatMap(fn (?string $ids) => array_filter(explode(',', (string) $ids)))
                ->unique()
                ->filter()
                ->values()
                ->all();
        }

        return $this->resolveHoldersForAnalysisTypeIds($analysisTypeIds);
    }

    /**
     * @param  array<int, string|int>  $analysisTypeIds
     * @return Collection<int, GroupedWorksheetHolder>
     */
    public function resolveHoldersForAnalysisTypeIds(array $analysisTypeIds): Collection
    {
        $analysisTypeIds = collect($analysisTypeIds)->filter()->unique()->values();

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

    /**
     * Grouped worksheet summaries keyed by sample detail id.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function buildSampleWorksheetMap(SampleHeader $batch): array
    {
        $batch->loadMissing('samples');

        $allAnalysisTypeIds = $batch->samples
            ->flatMap(fn ($sample) => array_filter(explode(',', (string) ($sample->analysis_type_id ?? ''))))
            ->unique()
            ->filter()
            ->values()
            ->all();

        if ($allAnalysisTypeIds === []) {
            return [];
        }

        $analysisTypes = AnalysisType::query()
            ->whereIn('id', $allAnalysisTypeIds)
            ->whereNotNull('grouped_worksheet_holder_id')
            ->with(['groupedWorksheetHolder.items'])
            ->get()
            ->keyBy('id');

        $runs = GroupedWorksheetRun::query()
            ->where('sample_header_id', $batch->id)
            ->get()
            ->keyBy('grouped_worksheet_holder_id');

        $map = [];

        foreach ($batch->samples as $sample) {
            $sampleAnalysisIds = array_filter(explode(',', (string) ($sample->analysis_type_id ?? '')));
            $map[(string) $sample->id] = $this->summariesFromAnalysisTypes(
                $sampleAnalysisIds,
                $analysisTypes,
                $batch,
                $runs
            );
        }

        return $map;
    }

    /**
     * @param  array<int, string|int>  $analysisTypeIds
     * @return array<int, array<string, mixed>>
     */
    public function summariesForAnalysisTypeIds(array $analysisTypeIds, SampleHeader $batch): array
    {
        $analysisTypeIds = collect($analysisTypeIds)->filter()->unique()->values()->all();

        if ($analysisTypeIds === []) {
            return [];
        }

        $analysisTypes = AnalysisType::query()
            ->whereIn('id', $analysisTypeIds)
            ->whereNotNull('grouped_worksheet_holder_id')
            ->with(['groupedWorksheetHolder.items'])
            ->get()
            ->keyBy('id');

        $runs = GroupedWorksheetRun::query()
            ->where('sample_header_id', $batch->id)
            ->get()
            ->keyBy('grouped_worksheet_holder_id');

        return $this->summariesFromAnalysisTypes($analysisTypeIds, $analysisTypes, $batch, $runs);
    }

    /**
     * @param  array<int, string|int>  $analysisTypeIds
     * @param  Collection<string, AnalysisType>  $analysisTypes
     * @param  Collection<string, GroupedWorksheetRun>  $runs
     * @return array<int, array<string, mixed>>
     */
    protected function summariesFromAnalysisTypes(
        array $analysisTypeIds,
        Collection $analysisTypes,
        SampleHeader $batch,
        Collection $runs
    ): array {
        $holders = [];

        foreach ($analysisTypeIds as $analysisTypeId) {
            $analysisType = $analysisTypes->get((string) $analysisTypeId);

            if (! $analysisType?->grouped_worksheet_holder_id) {
                continue;
            }

            $holderId = (string) $analysisType->grouped_worksheet_holder_id;

            if (isset($holders[$holderId])) {
                $holders[$holderId]['analysis_names'][] = $analysisType->name;

                continue;
            }

            $holder = $analysisType->groupedWorksheetHolder;

            if (! $holder) {
                continue;
            }

            $run = $runs->get($holderId);
            $holders[$holderId] = $this->formatHolderSummary($holder, [$analysisType->name], $batch, $run);
        }

        return array_values($holders);
    }

    /**
     * @param  array<int, string>  $analysisNames
     * @return array<string, mixed>
     */
    protected function formatHolderSummary(
        GroupedWorksheetHolder $holder,
        array $analysisNames,
        SampleHeader $batch,
        ?GroupedWorksheetRun $run
    ): array {
        $stages = $holder->items->map(function ($item, int $index) {
            $type = $item->getItemTypeEnum();

            return [
                'sequence' => $index + 1,
                'label' => $item->label ?: $type->value,
                'item_type' => $type->value,
                'reference_name' => $item->referenceName(),
                'is_required' => (bool) $item->is_required,
            ];
        })->values()->all();

        $runStatus = $run?->status?->value;

        return [
            'holder_id' => (string) $holder->id,
            'holder_name' => $holder->name,
            'analysis_names' => array_values(array_unique($analysisNames)),
            'step_count' => count($stages),
            'stages' => $stages,
            'run_status' => $runStatus,
            'run_status_label' => $this->runStatusLabel($runStatus),
            'capture_url' => route('batch-worksheets', [
                'batch' => $batch->id,
                'tab' => 'grouped-pipelines',
                'pipeline' => $holder->id,
            ]),
        ];
    }

    protected function runStatusLabel(?string $status): string
    {
        return match ($status) {
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Not started',
        };
    }
}
