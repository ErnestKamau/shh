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
use App\Services\GroupedWorksheets\GroupedResultsCaptureService;
use App\Services\GroupedWorksheets\GroupedWorksheetCapturePreviewService;
use App\Services\GroupedWorksheets\GroupedWorksheetPipelineStages;
use App\Services\GroupedWorksheets\GroupedWorksheetRunService;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
        if ($this->run->status === GroupedWorksheetRunStatus::Completed) {
            return;
        }

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

        $procedureWorksheetId = null;
        $procedureSectionKey = null;
        $procedureRowKeys = null;
        $procedureShowConfigFields = true;
        $msStageOrder = null;
        $embeddedProcedureOnMs = false;

        if ($current) {
            $type = $current->getItemTypeEnum();

            if ($type === GroupedWorksheetItemType::Procedure) {
                $procedureWorksheetId = $current->reference_id;
                $procedureSectionKey = $current->getConfigValue('section_key');
                $rowKeys = $current->getConfigValue('row_keys');
                $procedureRowKeys = is_array($rowKeys) ? array_values($rowKeys) : null;
                $procedureShowConfigFields = (bool) ($current->getConfigValue('show_config_fields') ?? true);
            }

            if ($type === GroupedWorksheetItemType::StageHeader) {
                $order = $current->getConfigValue('stage_order');
                $msStageOrder = $order !== null && $order !== '' ? (int) $order : null;

                // Optional same-page matrix: StageHeader config may link a procedure section.
                $linkedProcedureId = $current->getConfigValue('procedure_worksheet_id');
                $linkedSectionKey = $current->getConfigValue('section_key');
                if (filled($linkedProcedureId) && filled($linkedSectionKey)) {
                    $procedureWorksheetId = (string) $linkedProcedureId;
                    $procedureSectionKey = (string) $linkedSectionKey;
                    $rowKeys = $current->getConfigValue('row_keys');
                    $procedureRowKeys = is_array($rowKeys) ? array_values($rowKeys) : null;
                    $procedureShowConfigFields = (bool) ($current->getConfigValue('show_config_fields') ?? false);
                    $embeddedProcedureOnMs = true;
                }
            }
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
            'procedureWorksheetId' => $procedureWorksheetId,
            'procedureSectionKey' => $procedureSectionKey,
            'procedureRowKeys' => $procedureRowKeys,
            'procedureShowConfigFields' => $procedureShowConfigFields,
            'msStageOrder' => $msStageOrder,
            'embeddedProcedureOnMs' => $embeddedProcedureOnMs,
            'isRunComplete' => $this->run->status === GroupedWorksheetRunStatus::Completed,
            'isVirtualResultsCapture' => $current
                ? $pipelineStages->isVirtualResultsCapture($current)
                : false,
            'holderPipelineMode' => $this->holder->getSettingValue('pipeline_mode', 'classic'),
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
        if ($this->run->status === GroupedWorksheetRunStatus::Completed) {
            return;
        }

        $current = $this->currentItem();
        if ($current && app(GroupedWorksheetPipelineStages::class)->isVirtualResultsCapture($current)) {
            if (! app(GroupedResultsCaptureService::class)->hasPostedResults($this->batch, $this->holder)) {
                $this->setMessage(
                    'Post Results Capture before completing the pipeline.',
                    'error'
                );

                return;
            }
        }

        // Stock deduction when completing a phase that has a matrix section
        // (standalone Procedure chip, or StageHeader with embedded procedure).
        if ($current) {
            $sectionKey = $current->getConfigValue('section_key');
            $hasMatrix = filled($sectionKey) && (
                $current->getItemTypeEnum() === GroupedWorksheetItemType::Procedure
                || (
                    $current->getItemTypeEnum() === GroupedWorksheetItemType::StageHeader
                    && filled($current->getConfigValue('procedure_worksheet_id'))
                )
            );

            if ($hasMatrix) {
                $this->dispatch('deductMatrixSectionStock')->to('worksheets.procedure-worksheet-manager');
            }
        }

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
        if ($this->run->status === GroupedWorksheetRunStatus::Completed) {
            return;
        }

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
        // Force Method Sequences widget to remount/rebind after pipeline navigation.
        $this->dispatch('grouped-pipeline-stage-changed');
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
        $access = app(LabSectionResultAccess::class);
        $user = Auth::user();
        $batchSampleIds = $this->batch->samples()->pluck('id')->map(fn ($id) => (string) $id)->all();

        return $stageHeaders->map(function ($stageHeader) use ($access, $user, $batchSampleIds) {
            return [
                'id' => $stageHeader->id,
                'name' => $stageHeader->name,
                'method_name' => $stageHeader->method ? $stageHeader->method->name : 'N/A',
                'analyte_name' => $stageHeader->analyte ? $stageHeader->analyte->name : 'N/A',
                'sample_type_name' => $stageHeader->sampleType ? $stageHeader->sampleType->name : 'All',
                'total_days' => $stageHeader->total_days,
                'stages_count' => $stageHeader->testStages->count(),
                'can_edit' => $access->canEditStageHeaderResults(
                    $user,
                    (string) $stageHeader->id,
                    $batchSampleIds
                ),
            ];
        })->values()->all();
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
