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
        $configured = $this->configuredItems($holder);

        // Do not append a virtual Results capture when the holder already configures one.
        if ($this->hasConfiguredResultsCapture($holder)) {
            return $configured;
        }

        if (! $this->isVirtualResultsCaptureEnabled($holder)) {
            return $configured;
        }

        return $configured
            ->concat([$this->virtualResultsCaptureItem($holder)])
            ->values();
    }

    public function hasConfiguredResultsCapture(GroupedWorksheetHolder $holder): bool
    {
        return $this->configuredItems($holder)->contains(
            fn (GroupedWorksheetItem $item) => $item->getItemTypeEnum() === GroupedWorksheetItemType::ResultsCapture
        );
    }

    public function isVirtualResultsCaptureEnabled(GroupedWorksheetHolder $holder): bool
    {
        $settings = $holder->getSettingValue('results_capture');

        if (! is_array($settings)) {
            return true;
        }

        return (bool) ($settings['enabled'] ?? true);
    }

    /**
     * Virtual Results chip for admin timeline display when enabled (null when disabled or real item exists).
     */
    public function virtualResultsCaptureItemForDisplay(GroupedWorksheetHolder $holder): ?GroupedWorksheetItem
    {
        if ($this->hasConfiguredResultsCapture($holder) || ! $this->isVirtualResultsCaptureEnabled($holder)) {
            return null;
        }

        return $this->virtualResultsCaptureItem($holder);
    }

    public function virtualResultsCaptureItem(GroupedWorksheetHolder $holder): GroupedWorksheetItem
    {
        $maxSortOrder = $holder->items->max('sort_order') ?? 0;
        $settings = is_array($holder->getSettingValue('results_capture'))
            ? $holder->getSettingValue('results_capture')
            : [];

        $label = filled($settings['label'] ?? null)
            ? (string) $settings['label']
            : self::RESULTS_CAPTURE_LABEL;
        $required = (bool) ($settings['required'] ?? true);

        $item = new GroupedWorksheetItem([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => $maxSortOrder + 1,
            'label' => $label,
            'description' => 'Enter result values for each parameter across all samples in this batch.',
            'item_type' => GroupedWorksheetItemType::ResultsCapture,
            'reference_id' => null,
            'is_required' => $required,
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
        if ($this->hasConfiguredResultsCapture($holder)) {
            return $this->configuredItems($holder)
                ->search(fn (GroupedWorksheetItem $item) => $item->getItemTypeEnum() === GroupedWorksheetItemType::ResultsCapture);
        }

        return $this->configuredStageCount($holder);
    }

    public function totalStageCount(GroupedWorksheetHolder $holder): int
    {
        return $this->allStages($holder)->count();
    }

    public function isVirtualStageIndex(int $index, GroupedWorksheetHolder $holder): bool
    {
        if ($this->hasConfiguredResultsCapture($holder) || ! $this->isVirtualResultsCaptureEnabled($holder)) {
            return false;
        }

        return $index === $this->virtualStageIndex($holder);
    }
}
