<?php

namespace App\Livewire\Worksheets;

use App\CapturedResult;
use App\Enums\GroupedWorksheetItemType;
use App\Models\Formulars\Formula;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\StageHeader;
use App\SampleHeader;
use App\Services\GroupedWorksheets\GroupedWorksheetAssignmentService;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class WorksheetManager extends Component
{
    public SampleHeader $batch;

    /** @var Collection<int, \App\Models\StageHeader> */
    public Collection $stageHeaders;

    public string $activeTab = 'formulas';

    /** @var Collection<int, GroupedWorksheetHolder> */
    public Collection $groupedHolders;

    public ?string $pipeline = null;

    public ?string $activeGroupedHolderId = null;

    public $formulas;

    public $activeFormulaId = null;

    public $hasNoCaptureSamples = false;

    public $groupedNoCaptureSamples = [];

    /** @var \Illuminate\Support\Collection<int, LogEntryWorksheet> */
    public $logEntryWorksheets;

    public ?string $activeLogEntryWorksheetId = null;

    /** @var \Illuminate\Support\Collection<int, ProcedureWorksheet> */
    public $procedureWorksheets;

    public ?string $activeStageHeaderId = null;

    public ?string $activeSerAnalysisTypeId = null;

    public ?string $activeProcedureWorksheetId = null;

    protected bool $formulaDataLoaded = false;

    protected bool $stageHeadersLoaded = false;

    protected $queryString = [
        'activeTab' => ['except' => 'formulas', 'as' => 'tab'],
        'pipeline' => ['except' => null, 'as' => 'pipeline'],
    ];

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
        $this->stageHeaders = collect();
        $this->formulas = collect();
        $this->logEntryWorksheets = collect();
        $this->procedureWorksheets = collect();
        $this->groupedHolders = app(GroupedWorksheetAssignmentService::class)->resolveHoldersForBatch($batch);

        if ($this->groupedHolders->isNotEmpty()) {
            $this->activeGroupedHolderId = (string) ($this->groupedHolders->first()->id);
            $this->pipeline = $this->activeGroupedHolderId;
        }

        // Load all worksheet types up front so tabs can be gated to active ones only.
        // Engines embedded in an active grouped pipeline are suppressed from standalone tabs.
        $this->loadProcedureWorksheetData();
        $this->loadLogEntryWorksheetData();
        $this->loadFormulaWorksheetData();
        $this->loadStageHeaders();
        $this->suppressPipelineEmbeddedStandaloneTabs();

        $tabResolved = false;

        $requestedPipeline = request()->query('pipeline');
        if ($requestedPipeline && $this->groupedHolders->contains('id', $requestedPipeline)) {
            $this->activeTab = 'grouped-pipelines';
            $this->activeGroupedHolderId = (string) $requestedPipeline;
            $this->pipeline = (string) $requestedPipeline;
            $tabResolved = true;
        }

        $requestedFormulaId = request()->query('formula');
        if ($requestedFormulaId && $this->formulas->contains('id', $requestedFormulaId)) {
            $this->activeTab = 'formulas';
            $this->activeFormulaId = $requestedFormulaId;
            $tabResolved = true;
        }

        if (! $tabResolved) {
            $requestedTab = request()->query('tab', $this->resolveDefaultTab());
            $this->activeTab = $this->isTabAvailable($requestedTab)
                ? $requestedTab
                : $this->resolveDefaultTab();
        }

        if ($this->activeTab === 'method-sequences') {
            $this->queueMethodSequencesInit();
        }

        $this->initializePrintContextDefaults();
    }

    protected function initializePrintContextDefaults(): void
    {
        if ($this->procedureWorksheets->isNotEmpty() && $this->activeProcedureWorksheetId === null) {
            $this->activeProcedureWorksheetId = (string) $this->procedureWorksheets->first()->id;
        }

        if ($this->stageHeaders->isNotEmpty() && $this->activeStageHeaderId === null) {
            $this->activeStageHeaderId = (string) $this->stageHeaders->first()->id;
        }

        if ($this->hasNoCaptureSamples && $this->activeSerAnalysisTypeId === null) {
            $firstKey = array_key_first($this->groupedNoCaptureSamples);
            if ($firstKey !== null) {
                $this->activeSerAnalysisTypeId = (string) $firstKey;
            }
        }
    }

    /**
     * @return list<string>
     */
    protected function availableTabs(): array
    {
        $tabs = [];

        if ($this->groupedHolders->isNotEmpty()) {
            $tabs[] = 'grouped-pipelines';
        }
        if ($this->formulas->isNotEmpty()) {
            $tabs[] = 'formulas';
        }
        if ($this->stageHeaders->isNotEmpty()) {
            $tabs[] = 'method-sequences';
        }
        if ($this->logEntryWorksheets->isNotEmpty()) {
            $tabs[] = 'log-entry';
        }
        if ($this->procedureWorksheets->isNotEmpty()) {
            $tabs[] = 'procedures';
        }
        if ($this->hasNoCaptureSamples) {
            $tabs[] = 'ser';
        }

        return $tabs;
    }

    protected function isTabAvailable(string $tab): bool
    {
        return in_array($tab, $this->availableTabs(), true);
    }

    protected function resolveDefaultTab(): string
    {
        return $this->availableTabs()[0] ?? 'formulas';
    }

    protected function ensureTabDataLoaded(): void
    {
        if (in_array($this->activeTab, ['formulas', 'ser'], true)) {
            $this->loadFormulaWorksheetData();
        }

        if ($this->activeTab === 'log-entry') {
            $this->loadLogEntryWorksheetData();
        }

        if ($this->activeTab === 'method-sequences') {
            $this->loadStageHeaders();
        }
    }

    public function loadLogEntryWorksheetData(): void
    {
        $query = CapturedResult::where('sample_header_id', $this->batch->id)
            ->whereNotNull('log_entry_worksheet_id');
        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());
        $ids = $query->distinct()->pluck('log_entry_worksheet_id');

        $this->logEntryWorksheets = LogEntryWorksheet::whereIn('id', $ids)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($this->activeLogEntryWorksheetId === null && $this->logEntryWorksheets->isNotEmpty()) {
            $this->activeLogEntryWorksheetId = (string) $this->logEntryWorksheets->first()->id;
        }
    }

    public function loadProcedureWorksheetData(): void
    {
        $query = CapturedResult::query()
            ->where('sample_header_id', $this->batch->id)
            ->whereNotNull('procedure_worksheet_id')
            // Standalone procedure tab: ignore rows that belong to a grouped pipeline.
            ->where(function ($q) {
                $q->where('has_grouped_worksheet', false)
                    ->orWhereNull('has_grouped_worksheet')
                    ->orWhereNull('grouped_worksheet_holder_id');
            });
        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());
        $ids = $query->distinct()->pluck('procedure_worksheet_id');

        $this->procedureWorksheets = ProcedureWorksheet::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    protected function loadStageHeaders(): void
    {
        if ($this->stageHeadersLoaded) {
            return;
        }

        $pipelineRefs = app(GroupedWorksheetAssignmentService::class)
            ->pipelineReferenceIdsForHolders($this->groupedHolders);
        $pipelineStageHeaderIds = $pipelineRefs[GroupedWorksheetItemType::StageHeader->value] ?? [];

        $this->stageHeaders = StageHeader::query()
            ->whereHas('capturedResults', function ($q) {
                $q->whereHas('sample', function ($sq) {
                    $sq->where('sample_header_id', $this->batch->id);
                });
                app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($q, Auth::user());
            })
            ->when($pipelineStageHeaderIds !== [], function ($q) use ($pipelineStageHeaderIds) {
                $q->whereNotIn('id', $pipelineStageHeaderIds);
            })
            ->with(['method', 'analyte', 'sampleType', 'testStages'])
            ->orderBy('name')
            ->get();

        $this->stageHeadersLoaded = true;
    }

    /**
     * Remove standalone tabs for worksheets that are already stages in an active grouped pipeline.
     */
    protected function suppressPipelineEmbeddedStandaloneTabs(): void
    {
        if ($this->groupedHolders->isEmpty()) {
            return;
        }

        $pipelineRefs = app(GroupedWorksheetAssignmentService::class)
            ->pipelineReferenceIdsForHolders($this->groupedHolders);

        $procedureIds = $pipelineRefs[GroupedWorksheetItemType::Procedure->value] ?? [];
        if ($procedureIds !== [] && $this->procedureWorksheets instanceof Collection) {
            $this->procedureWorksheets = $this->procedureWorksheets
                ->reject(fn (ProcedureWorksheet $worksheet) => in_array((string) $worksheet->id, $procedureIds, true))
                ->values();
        }

        $formulaIds = $pipelineRefs[GroupedWorksheetItemType::Formula->value] ?? [];
        if ($formulaIds !== [] && $this->formulas instanceof Collection) {
            $this->formulas = $this->formulas
                ->reject(fn (Formula $formula) => in_array((string) $formula->id, $formulaIds, true))
                ->values();

            if ($this->activeFormulaId && in_array((string) $this->activeFormulaId, $formulaIds, true)) {
                $this->activeFormulaId = $this->formulas->first()?->id;
            }
        }

        $logEntryIds = $pipelineRefs[GroupedWorksheetItemType::LogEntryWorksheet->value] ?? [];
        if ($logEntryIds !== [] && $this->logEntryWorksheets instanceof Collection) {
            $this->logEntryWorksheets = $this->logEntryWorksheets
                ->reject(fn (LogEntryWorksheet $worksheet) => in_array((string) $worksheet->id, $logEntryIds, true))
                ->values();

            if ($this->activeLogEntryWorksheetId && in_array((string) $this->activeLogEntryWorksheetId, $logEntryIds, true)) {
                $this->activeLogEntryWorksheetId = $this->logEntryWorksheets->first()?->id
                    ? (string) $this->logEntryWorksheets->first()->id
                    : null;
            }
        }

        if ($this->activeProcedureWorksheetId
            && $this->procedureWorksheets instanceof Collection
            && ! $this->procedureWorksheets->contains('id', $this->activeProcedureWorksheetId)
        ) {
            $this->activeProcedureWorksheetId = $this->procedureWorksheets->first()?->id
                ? (string) $this->procedureWorksheets->first()->id
                : null;
        }

        if ($this->activeStageHeaderId
            && $this->stageHeaders instanceof Collection
            && ! $this->stageHeaders->contains('id', $this->activeStageHeaderId)
        ) {
            $this->activeStageHeaderId = $this->stageHeaders->first()?->id
                ? (string) $this->stageHeaders->first()->id
                : null;
        }
    }

    public function loadFormulaWorksheetData(): void
    {
        if ($this->formulaDataLoaded) {
            return;
        }

        $this->loadWorksheetData();
        $this->formulaDataLoaded = true;
    }

    public function loadWorksheetData(): void
    {
        $query = CapturedResult::where('sample_header_id', $this->batch->id)
            ->whereNotNull('formular_id')
            ->with(['analysisElement', 'formular', 'sample']);
        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());
        $capturedResults = $query->get();

        $formulaIds = $capturedResults->whereNotNull('formular_id')
            ->pluck('formular_id')
            ->unique()
            ->values();

        $this->formulas = Formula::whereIn('id', $formulaIds)
            ->with('activeVersion')
            ->get();

        if ($this->activeFormulaId === null && $this->formulas->isNotEmpty()) {
            $this->activeFormulaId = $this->formulas->first()->id;
        }

        if (Schema::hasColumn('captured_results', 'has_no_result_capture')) {
            $noCaptureQuery = CapturedResult::where('sample_header_id', $this->batch->id)
                ->where('has_no_result_capture', 1)
                ->with(['sample', 'analysis_type']);
            app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($noCaptureQuery, Auth::user());
            $noCaptureResults = $noCaptureQuery->get();

            $this->groupedNoCaptureSamples = [];

            foreach ($noCaptureResults as $result) {
                $analysisId = $result->analysis_type_id;

                if (! isset($this->groupedNoCaptureSamples[$analysisId])) {
                    $this->groupedNoCaptureSamples[$analysisId] = [
                        'name' => $result->analysis_type->name ?? 'Unknown Analysis',
                        'samples' => [],
                    ];
                }

                $existingSampleIds = array_map(fn ($s) => $s->id, $this->groupedNoCaptureSamples[$analysisId]['samples']);

                if ($result->sample && ! in_array($result->sample->id, $existingSampleIds, true)) {
                    $this->groupedNoCaptureSamples[$analysisId]['samples'][] = $result->sample;
                }
            }

            $this->hasNoCaptureSamples = count($this->groupedNoCaptureSamples) > 0;
        } else {
            $this->groupedNoCaptureSamples = [];
            $this->hasNoCaptureSamples = false;
        }
    }

    public function switchTab(string $tab): void
    {
        if (! $this->isTabAvailable($tab)) {
            return;
        }

        $this->activeTab = $tab;
        $this->ensureTabDataLoaded();

        if ($tab === 'method-sequences') {
            $this->queueMethodSequencesInit();
        }
    }

    public function selectGroupedHolder(string $holderId): void
    {
        $this->activeTab = 'grouped-pipelines';
        $this->activeGroupedHolderId = $holderId;
        $this->pipeline = $holderId;
    }

    public function updatedActiveTab(string $tab): void
    {
        $this->ensureTabDataLoaded();

        if ($tab === 'method-sequences') {
            $this->queueMethodSequencesInit();
        }
    }

    protected function queueMethodSequencesInit(): void
    {
        $this->dispatch('init-method-sequences');
        $this->js('setTimeout(function () { window.scheduleMethodSequencesInit && window.scheduleMethodSequencesInit(15); }, 100)');
    }

    public function postAllResults(): void
    {
        if ($this->activeTab === 'formulas') {
            $this->dispatch('triggerFormulaPostResults', formulaId: $this->activeFormulaId)
                ->to(FormulaWorksheet::class);
        }
    }

    public function updatedActiveFormulaId($formulaId): void
    {
        $this->activeTab = 'formulas';
        $this->activeFormulaId = $formulaId;
    }

    public function printUrl(): ?string
    {
        $params = ['tab' => $this->activeTab];

        return match ($this->activeTab) {
            'grouped-pipelines' => $this->activeGroupedHolderId
                ? route('batch-worksheets.print', ['batch' => $this->batch->id] + $params + [
                    'pipeline_id' => $this->activeGroupedHolderId,
                ])
                : null,
            'formulas' => $this->activeFormulaId
                ? route('batch-worksheets.print', ['batch' => $this->batch->id] + $params + [
                    'formula_id' => $this->activeFormulaId,
                ])
                : null,
            'method-sequences' => $this->activeStageHeaderId
                ? route('batch-worksheets.print', ['batch' => $this->batch->id] + $params + [
                    'stage_header_id' => $this->activeStageHeaderId,
                ])
                : null,
            'log-entry' => $this->activeLogEntryWorksheetId
                ? route('batch-worksheets.print', ['batch' => $this->batch->id] + $params + [
                    'log_entry_worksheet_id' => $this->activeLogEntryWorksheetId,
                ])
                : null,
            'procedures' => $this->activeProcedureWorksheetId
                ? route('batch-worksheets.print', ['batch' => $this->batch->id] + $params + [
                    'procedure_worksheet_id' => $this->activeProcedureWorksheetId,
                ])
                : null,
            'ser' => $this->activeSerAnalysisTypeId
                ? route('batch-worksheets.print', ['batch' => $this->batch->id] + $params + [
                    'analysis_type_id' => $this->activeSerAnalysisTypeId,
                ])
                : null,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function updatePrintContext(array $context = []): void
    {
        if (! empty($context['procedure_worksheet_id'])) {
            $this->activeProcedureWorksheetId = (string) $context['procedure_worksheet_id'];
        }

        if (! empty($context['stage_header_id'])) {
            $this->activeStageHeaderId = (string) $context['stage_header_id'];
        }

        if (! empty($context['analysis_type_id'])) {
            $this->activeSerAnalysisTypeId = (string) $context['analysis_type_id'];
        }
    }

    protected function getListeners(): array
    {
        return [
            'worksheet-print-context-updated' => 'updatePrintContext',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stageHeadersPayload(): array
    {
        $access = app(LabSectionResultAccess::class);
        $user = Auth::user();
        $batchSampleIds = $this->batch->samples()->pluck('id')->map(fn ($id) => (string) $id)->all();

        return $this->stageHeaders->map(function ($stageHeader) use ($access, $user, $batchSampleIds) {
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

    public function render()
    {
        $activeGroupedHolder = $this->groupedHolders->firstWhere('id', $this->activeGroupedHolderId)
            ?? $this->groupedHolders->first();

        $stageHeadersPayload = $this->activeTab === 'method-sequences'
            ? $this->stageHeadersPayload()
            : [];

        return view('livewire.worksheets.worksheet-manager', [
            'stageHeadersPayload' => $stageHeadersPayload,
            'activeGroupedHolder' => $activeGroupedHolder,
        ]);
    }
}
