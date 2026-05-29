<?php

namespace App\Services\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use Illuminate\Support\Collection;

class GroupedWorksheetPipelineStages
{
    public const VIRTUAL_RESULTS_CAPTURE_ID = '00000000-0000-4000-8000-000000000001';

    public const RESULTS_CAPTURE_LABEL = 'Results capture';

    /**
     * @return Collection<int, GroupedWorksheetItem>
     */
    public function configuredItems(GroupedWorksheetHolder $holder): Collection
    {
        return $holder->items->values();
    }

    /**
     * @return Collection<int, GroupedWorksheetItem>
     */
    public function allStages(GroupedWorksheetHolder $holder): Collection
    {
        return $this->configuredItems($holder)
            ->concat([$this->virtualResultsCaptureItem($holder)])
            ->values();
    }

    public function virtualResultsCaptureItem(GroupedWorksheetHolder $holder): GroupedWorksheetItem
    {
        $configuredCount = $this->configuredStageCount($holder);
        $maxSortOrder = $holder->items->max('sort_order') ?? 0;

        $item = new GroupedWorksheetItem([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => $maxSortOrder + 1,
            'label' => self::RESULTS_CAPTURE_LABEL,
            'description' => 'Enter result values for each parameter across all samples in this batch.',
            'item_type' => GroupedWorksheetItemType::ResultsCapture,
            'reference_id' => null,
            'is_required' => true,
        ]);

        $item->id = self::VIRTUAL_RESULTS_CAPTURE_ID;
        $item->exists = false;

        return $item;
    }

    public function isVirtualResultsCapture(?GroupedWorksheetItem $item): bool
    {
        if ($item === null) {
            return false;
        }

        return $item->id === self::VIRTUAL_RESULTS_CAPTURE_ID
            || $item->getItemTypeEnum() === GroupedWorksheetItemType::ResultsCapture;
    }

    public function configuredStageCount(GroupedWorksheetHolder $holder): int
    {
        return $this->configuredItems($holder)->count();
    }

    public function virtualStageIndex(GroupedWorksheetHolder $holder): int
    {
        return $this->configuredStageCount($holder);
    }

    public function totalStageCount(GroupedWorksheetHolder $holder): int
    {
        return $this->configuredStageCount($holder) + 1;
    }

    public function isVirtualStageIndex(int $index, GroupedWorksheetHolder $holder): bool
    {
        return $index === $this->virtualStageIndex($holder);
    }
}
