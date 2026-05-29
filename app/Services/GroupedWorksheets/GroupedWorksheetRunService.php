<?php

namespace App\Services\GroupedWorksheets;

use App\Enums\GroupedWorksheetRunItemStatus;
use App\Enums\GroupedWorksheetRunStatus;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetRun;
use App\Models\GroupedWorksheets\GroupedWorksheetRunItem;
use App\Models\User;
use App\SampleHeader;
use Illuminate\Support\Facades\Auth;

class GroupedWorksheetRunService
{
    public function __construct(
        protected GroupedWorksheetPipelineStages $pipelineStages,
    ) {}

    public function findOrCreateRun(SampleHeader $batch, GroupedWorksheetHolder $holder, ?User $user = null): GroupedWorksheetRun
    {
        $existing = GroupedWorksheetRun::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->where('status', GroupedWorksheetRunStatus::InProgress)
            ->first();

        if ($existing) {
            return $existing->load(['holder.items', 'runItems']);
        }

        $userId = $user?->id ?? Auth::id();

        $run = GroupedWorksheetRun::create([
            'sample_header_id' => $batch->id,
            'grouped_worksheet_holder_id' => $holder->id,
            'current_item_index' => 0,
            'status' => GroupedWorksheetRunStatus::InProgress,
            'started_by' => $userId,
            'started_at' => now(),
        ]);

        $holder->load('items');
        foreach ($holder->items as $item) {
            GroupedWorksheetRunItem::create([
                'grouped_worksheet_run_id' => $run->id,
                'grouped_worksheet_item_id' => $item->id,
                'status' => GroupedWorksheetRunItemStatus::Pending,
            ]);
        }

        return $run->load(['holder.items', 'runItems']);
    }

    public function completeCurrentStage(GroupedWorksheetRun $run, ?User $user = null, bool $force = false): GroupedWorksheetRun
    {
        $run->load(['holder.items', 'runItems']);
        $holder = $run->holder;
        $allStages = $this->pipelineStages->allStages($holder);
        $current = $allStages->get($run->current_item_index);

        if ($current && ! $this->pipelineStages->isVirtualResultsCapture($current)) {
            $runItem = $run->runItems->firstWhere('grouped_worksheet_item_id', $current->id);
            if ($runItem) {
                $runItem->update([
                    'status' => GroupedWorksheetRunItemStatus::Completed,
                    'completed_by' => $user?->id ?? Auth::id(),
                    'completed_at' => now(),
                ]);
            }
        }

        $nextIndex = $run->current_item_index + 1;

        if ($nextIndex >= $allStages->count()) {
            $run->update([
                'status' => GroupedWorksheetRunStatus::Completed,
                'completed_at' => now(),
            ]);
        } else {
            $run->update(['current_item_index' => $nextIndex]);
            $nextItem = $allStages->get($nextIndex);
            if ($nextItem && ! $this->pipelineStages->isVirtualResultsCapture($nextItem)) {
                $nextRunItem = $run->runItems->firstWhere('grouped_worksheet_item_id', $nextItem->id);
                if ($nextRunItem && $nextRunItem->status === GroupedWorksheetRunItemStatus::Pending) {
                    $nextRunItem->update(['status' => GroupedWorksheetRunItemStatus::InProgress]);
                }
            }
        }

        return $run->fresh(['holder.items', 'runItems']);
    }

    public function skipCurrentStage(GroupedWorksheetRun $run, ?User $user = null): GroupedWorksheetRun
    {
        $run->load(['holder.items', 'runItems']);
        $allStages = $this->pipelineStages->allStages($run->holder);
        $current = $allStages->get($run->current_item_index);

        if ($this->pipelineStages->isVirtualResultsCapture($current)) {
            return $run;
        }

        if ($current && ! $current->is_required) {
            $runItem = $run->runItems->firstWhere('grouped_worksheet_item_id', $current->id);
            if ($runItem) {
                $runItem->update([
                    'status' => GroupedWorksheetRunItemStatus::Skipped,
                    'completed_by' => $user?->id ?? Auth::id(),
                    'completed_at' => now(),
                ]);
            }
        }

        return $this->completeCurrentStage($run, $user, true);
    }

    public function goToStage(GroupedWorksheetRun $run, int $index): GroupedWorksheetRun
    {
        $run->load('holder.items');
        $max = $this->pipelineStages->totalStageCount($run->holder) - 1;
        $index = max(0, min($index, $max));
        $run->update(['current_item_index' => $index]);

        return $run->fresh(['holder.items', 'runItems']);
    }
}
