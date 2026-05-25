<?php

namespace App\Livewire\Analysis;

use App\AnalysisElements;
use App\AnalysisType;
use App\CapturedResult;
use App\SampleAnalysisTypeRelation;
use App\SampleHeader;
use App\Services\Analysis\CapturedResultWorksheetSyncService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class SyncWorksheetsModal extends Component
{
    public string $analysisTypeId;

    public bool $showModal = false;

    public bool $loadingSamples = false;

    public bool $syncing = false;

    /** @var array<int, string> */
    public array $selectedWorkflowStatuses = [];

    /**
     * @var list<array{
     *     relation_id: string,
     *     sample_detail_id: string,
     *     batch_id: string,
     *     sample_code: string,
     *     batch_code: string,
     *     batch_status: string
     * }>
     */
    public array $sampleRows = [];

    /** @var array<int, bool> row index => selected */
    public array $selectedSampleIds = [];

    public int $previewCapturedCount = 0;

    public string $message = '';

    public string $messageType = '';

    public function mount(string $analysisTypeId): void
    {
        $this->analysisTypeId = $analysisTypeId;
    }

    /**
     * @return array<int, string>
     */
    public function getWorkflowStageOptionsProperty(): array
    {
        $stages = getSampleWorflowStages();

        return array_values(array_filter($stages, fn (string $stage) => $stage !== 'All Samples'));
    }

    public function getAnalysisTypeProperty(): ?AnalysisType
    {
        return AnalysisType::query()
            ->with(['procedureWorksheet', 'groupedWorksheetHolder', 'hybridWorksheet'])
            ->find($this->analysisTypeId);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfigSummaryProperty(): array
    {
        $analysisType = $this->analysisType;

        if (! $analysisType) {
            return [
                'procedure_name' => null,
                'grouped_name' => null,
                'hybrid_name' => null,
                'elements_with_procedure' => 0,
                'elements_with_formula' => 0,
                'elements_with_method_sequence' => 0,
                'elements_with_log_entry' => 0,
            ];
        }

        $elements = AnalysisElements::query()
            ->where('analysis_type_id', $analysisType->id)
            ->get();

        return [
            'procedure_name' => $analysisType->procedureWorksheet?->name,
            'grouped_name' => $analysisType->groupedWorksheetHolder?->name,
            'hybrid_name' => $analysisType->hybridWorksheet?->name,
            'elements_with_procedure' => $elements->whereNotNull('procedure_worksheet_id')->count(),
            'elements_with_formula' => $elements->filter(
                fn (AnalysisElements $element) => $element->result_is_calculated && $element->formular_id
            )->count(),
            'elements_with_method_sequence' => $elements->filter(
                fn (AnalysisElements $element) => $element->has_method_sequence && $element->method_sequence_id
            )->count(),
            'elements_with_log_entry' => $elements->whereNotNull('log_entry_worksheet_id')->count(),
        ];
    }

    public function getSelectedSampleCountProperty(): int
    {
        return count($this->resolveSelectedSampleRows());
    }

    public function getDistinctBatchCountProperty(): int
    {
        return collect($this->resolveSelectedSampleRows())
            ->pluck('batch_id')
            ->unique()
            ->count();
    }

    /**
     * @return list<array{
     *     relation_id: string,
     *     sample_detail_id: string,
     *     batch_id: string,
     *     sample_code: string,
     *     batch_code: string,
     *     batch_status: string
     * }>
     */
    protected function resolveSelectedSampleRows(): array
    {
        $selected = [];

        foreach ($this->sampleRows as $index => $row) {
            if ($this->isSampleRowSelected($index)) {
                $selected[] = $row;
            }
        }

        return $selected;
    }

    protected function isSampleRowSelected(int|string $index): bool
    {
        return (bool) ($this->selectedSampleIds[$index] ?? $this->selectedSampleIds[(string) $index] ?? false);
    }

    #[On('open-sync-worksheets-modal')]
    public function openModal(): void
    {
        $this->resetValidation();
        $this->selectedWorkflowStatuses = [];
        $this->sampleRows = [];
        $this->selectedSampleIds = [];
        $this->previewCapturedCount = 0;
        $this->message = '';
        $this->messageType = '';
        $this->loadingSamples = false;
        $this->syncing = false;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->loadingSamples = false;
        $this->syncing = false;
        $this->message = '';
        $this->messageType = '';
        $this->resetValidation();
    }

    public function updatedSelectedWorkflowStatuses(): void
    {
        $this->loadSampleRows();
    }

    public function toggleWorkflowStatus(string $status): void
    {
        if (in_array($status, $this->selectedWorkflowStatuses, true)) {
            $this->selectedWorkflowStatuses = array_values(
                array_filter(
                    $this->selectedWorkflowStatuses,
                    fn (string $value) => $value !== $status
                )
            );
        } else {
            $this->selectedWorkflowStatuses[] = $status;
        }

        $this->loadSampleRows();
    }

    public function loadSampleRows(): void
    {
        $this->sampleRows = [];
        $this->selectedSampleIds = [];
        $this->previewCapturedCount = 0;

        if ($this->selectedWorkflowStatuses === []) {
            return;
        }

        $this->loadingSamples = true;

        try {
            $batchIds = SampleHeader::query()
                ->where('isactive', true)
                ->whereIn('status', $this->selectedWorkflowStatuses)
                ->pluck('id');

            if ($batchIds->isEmpty()) {
                return;
            }

            $rows = SampleAnalysisTypeRelation::query()
                ->where('sample_analysis_type_relation.analysis_type_id', $this->analysisTypeId)
                ->whereIn('sample_analysis_type_relation.batch_id', $batchIds)
                ->join('sample_details', 'sample_details.id', '=', 'sample_analysis_type_relation.sample_detail_id')
                ->join('sample_headers', 'sample_headers.id', '=', 'sample_analysis_type_relation.batch_id')
                ->select([
                    'sample_analysis_type_relation.id as relation_id',
                    'sample_analysis_type_relation.sample_detail_id',
                    'sample_analysis_type_relation.batch_id',
                    'sample_details.sample_code',
                    'sample_headers.batch_code',
                    'sample_headers.status as batch_status',
                ])
                ->distinct()
                ->orderBy('sample_headers.batch_code')
                ->orderBy('sample_details.sample_code')
                ->get();

            $this->sampleRows = $rows->map(fn ($row) => [
                'relation_id' => (string) $row->relation_id,
                'sample_detail_id' => (string) $row->sample_detail_id,
                'batch_id' => (string) $row->batch_id,
                'sample_code' => (string) $row->sample_code,
                'batch_code' => (string) $row->batch_code,
                'batch_status' => (string) $row->batch_status,
            ])->values()->all();

            foreach (array_keys($this->sampleRows) as $index) {
                $this->selectedSampleIds[$index] = true;
            }

            $sampleDetailIds = array_column($this->sampleRows, 'sample_detail_id');
            $batchIdsFromRows = array_unique(array_column($this->sampleRows, 'batch_id'));

            if ($sampleDetailIds !== []) {
                $this->previewCapturedCount = CapturedResult::query()
                    ->where('analysis_type_id', $this->analysisTypeId)
                    ->whereIn('sample_detail_id', $sampleDetailIds)
                    ->whereIn('sample_header_id', $batchIdsFromRows)
                    ->count();
            }
        } finally {
            $this->loadingSamples = false;
        }
    }

    public function toggleSelectAllSamples(bool $selected): void
    {
        foreach (array_keys($this->sampleRows) as $index) {
            $this->selectedSampleIds[$index] = $selected;
        }
    }

    public function syncWorksheets(): void
    {
        $this->resetValidation();

        $this->validate([
            'selectedWorkflowStatuses' => 'required|array|min:1',
            'selectedWorkflowStatuses.*' => 'string',
        ], [
            'selectedWorkflowStatuses.required' => 'Select at least one workflow stage.',
            'selectedWorkflowStatuses.min' => 'Select at least one workflow stage.',
        ]);

        $selectedRows = $this->resolveSelectedSampleRows();

        if ($selectedRows === []) {
            $this->addError('selectedSampleIds', 'Select at least one sample to sync.');

            return;
        }

        $selectedSampleDetailIds = collect($selectedRows)
            ->pluck('sample_detail_id')
            ->unique()
            ->values()
            ->all();

        $analysisType = $this->analysisType;

        if (! $analysisType) {
            $this->message = 'Analysis type not found.';
            $this->messageType = 'danger';

            return;
        }

        $batchIds = collect($selectedRows)
            ->pluck('batch_id')
            ->unique()
            ->values()
            ->all();

        $this->syncing = true;
        $this->message = '';
        $this->messageType = '';

        try {
            $worksheetSyncService = app(CapturedResultWorksheetSyncService::class);
            $elementsByAnalyteId = $worksheetSyncService->loadElementsByAnalyteId($this->analysisTypeId);
            $totalUpdated = 0;
            $totalUnchanged = 0;

            DB::transaction(function () use (
                $worksheetSyncService,
                $analysisType,
                $elementsByAnalyteId,
                $selectedSampleDetailIds,
                $batchIds,
                &$totalUpdated,
                &$totalUnchanged,
            ) {
                CapturedResult::query()
                    ->where('analysis_type_id', $this->analysisTypeId)
                    ->whereIn('sample_detail_id', $selectedSampleDetailIds)
                    ->whereIn('sample_header_id', $batchIds)
                    ->orderBy('id')
                    ->chunk(200, function ($capturedResults) use (
                        $worksheetSyncService,
                        $analysisType,
                        $elementsByAnalyteId,
                        &$totalUpdated,
                        &$totalUnchanged,
                    ) {
                        $result = $worksheetSyncService->syncMany(
                            $capturedResults,
                            $analysisType,
                            $elementsByAnalyteId,
                        );
                        $totalUpdated += $result['updated'];
                        $totalUnchanged += $result['unchanged'];
                    });
            });

            $sampleCount = count($selectedSampleDetailIds);
            $batchCount = count($batchIds);

            $successMessage = sprintf(
                'Synced %d captured result%s across %d sample%s in %d batch%s (%d unchanged).',
                $totalUpdated,
                $totalUpdated === 1 ? '' : 's',
                $sampleCount,
                $sampleCount === 1 ? '' : 's',
                $batchCount,
                $batchCount === 1 ? '' : 'es',
                $totalUnchanged,
            );

            $this->syncing = false;
            $this->closeModal();

            $this->dispatch(
                'worksheets-synced',
                message: $successMessage,
                messageType: 'success',
            )->to(ElementManager::class);

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->message = 'Failed to sync worksheets: '.$exception->getMessage();
            $this->messageType = 'danger';
        } finally {
            $this->syncing = false;
        }
    }

    public function render()
    {
        return view('livewire.analysis.sync-worksheets-modal');
    }
}
