<?php

namespace App\Livewire\Worksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Enums\GroupedWorksheetRunItemStatus;
use App\Enums\GroupedWorksheetRunStatus;
use App\Models\Formulars\Formula;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetRun;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\StageHeader;
use App\SampleHeader;
use App\Services\GroupedWorksheets\GroupedWorksheetCapturePreviewService;
use App\Services\GroupedWorksheets\GroupedWorksheetPipelineStages;
use App\Services\GroupedWorksheets\GroupedWorksheetRunService;
use Illuminate\Support\Collection;
use Livewire\Component;

class GroupedWorksheetWizard extends Component
{
    public SampleHeader $batch;

    public GroupedWorksheetHolder $holder;

    public GroupedWorksheetRun $run;

    public string $message = '';

    public string $messageType = '';

    /** @var array<string, mixed>|null */
    public ?array $capturePreview = null;

    protected $listeners = [
        'groupedStageCompleted' => 'handleStageCompleted',
    ];

    public function mount(SampleHeader $batch, GroupedWorksheetHolder $holder): void
    {
        $this->batch = $batch;
        $this->holder = $holder->load('items');
        $this->run = app(GroupedWorksheetRunService::class)->findOrCreateRun($batch, $holder);
        $this->markFirstStageInProgress();
        $this->refreshCapturePreview();
    }

    protected function refreshCapturePreview(): void
    {
        $current = $this->currentItem();

        $this->capturePreview = $current
            ? app(GroupedWorksheetCapturePreviewService::class)->previewForItem($current)
            : null;
    }

    protected function markFirstStageInProgress(): void
    {
        $this->run->load('runItems');
        $current = $this->currentItem();
        if (! $current) {
            return;
        }

        if (app(GroupedWorksheetPipelineStages::class)->isVirtualResultsCapture($current)) {
            return;
        }

        $runItem = $this->run->runItems->firstWhere('grouped_worksheet_item_id', $current->id);
        if ($runItem && $runItem->status === GroupedWorksheetRunItemStatus::Pending) {
            $runItem->update(['status' => GroupedWorksheetRunItemStatus::InProgress]);
        }
    }

    public function render()
    {
        $this->run->load(['holder.items', 'runItems']);
        $this->holder->load('items');
        $pipelineStages = app(GroupedWorksheetPipelineStages::class);
        $items = $pipelineStages->allStages($this->holder);
        $current = $this->currentItem();
        $formula = null;
        $stageHeaders = collect();
        $hybridWorksheet = null;
        $logEntryWorksheetId = null;

        if ($current) {
            match ($current->getItemTypeEnum()) {
                GroupedWorksheetItemType::Formula => $formula = Formula::with('activeVersion')->find($current->reference_id),
                GroupedWorksheetItemType::StageHeader => $stageHeaders = StageHeader::with(['method', 'analyte', 'sampleType', 'testStages'])
                    ->where('id', $current->reference_id)
                    ->get(),
                GroupedWorksheetItemType::HybridWorksheet => $hybridWorksheet = HybridWorksheet::with('activeVersion.blocks')->find($current->reference_id),
                GroupedWorksheetItemType::LogEntryWorksheet => $logEntryWorksheetId = $current->reference_id,
                default => null,
            };
        }

        return view('livewire.worksheets.grouped-worksheet-wizard', [
            'items' => $items,
            'currentItem' => $current,
            'capturePreview' => $this->capturePreview,
            'runItems' => $this->run->runItems,
            'formula' => $formula,
            'stageHeaders' => $stageHeaders,
            'stageHeadersPayload' => $this->stageHeadersPayload($stageHeaders),
            'hybridWorksheet' => $hybridWorksheet,
            'logEntryWorksheetId' => $logEntryWorksheetId,
            'procedureWorksheetId' => $current?->getItemTypeEnum() === GroupedWorksheetItemType::Procedure
                ? $current->reference_id
                : null,
            'isRunComplete' => $this->run->status === GroupedWorksheetRunStatus::Completed,
            'isVirtualResultsCapture' => $current
                ? $pipelineStages->isVirtualResultsCapture($current)
                : false,
        ]);
    }

    public function currentItem(): ?\App\Models\GroupedWorksheets\GroupedWorksheetItem
    {
        return app(GroupedWorksheetPipelineStages::class)
            ->allStages($this->holder)
            ->get($this->run->current_item_index);
    }

    public function completeStage(): void
    {
        $this->run = app(GroupedWorksheetRunService::class)->completeCurrentStage($this->run);
        $this->setMessage(
            $this->run->status === GroupedWorksheetRunStatus::Completed
                ? 'Pipeline completed.'
                : 'Stage marked complete. Continue to the next stage.',
            'success'
        );
        $this->markFirstStageInProgress();
        $this->refreshCapturePreview();
    }

    public function skipStage(): void
    {
        $current = $this->currentItem();
        if (! $current || $current->is_required || app(GroupedWorksheetPipelineStages::class)->isVirtualResultsCapture($current)) {
            $this->setMessage('This stage is required and cannot be skipped.', 'error');

            return;
        }

        $this->run = app(GroupedWorksheetRunService::class)->skipCurrentStage($this->run);
        $this->setMessage('Stage skipped.', 'success');
        $this->markFirstStageInProgress();
        $this->refreshCapturePreview();
    }

    public function goToStage(int $index): void
    {
        $this->run = app(GroupedWorksheetRunService::class)->goToStage($this->run, $index);
        $this->refreshCapturePreview();
    }

    public function handleStageCompleted(): void
    {
        $this->completeStage();
    }

    /**
     * @param  Collection<int, StageHeader>  $stageHeaders
     * @return array<int, array<string, mixed>>
     */
    protected function stageHeadersPayload(Collection $stageHeaders): array
    {
        return $stageHeaders->map(function ($stageHeader) {
            return [
                'id' => $stageHeader->id,
                'name' => $stageHeader->name,
                'method_name' => $stageHeader->method ? $stageHeader->method->name : 'N/A',
                'analyte_name' => $stageHeader->analyte ? $stageHeader->analyte->name : 'N/A',
                'sample_type_name' => $stageHeader->sampleType ? $stageHeader->sampleType->name : 'All',
                'total_days' => $stageHeader->total_days,
                'stages_count' => $stageHeader->testStages->count(),
            ];
        })->values()->all();
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
