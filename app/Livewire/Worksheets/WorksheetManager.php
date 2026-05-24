<?php

namespace App\Livewire\Worksheets;

use App\CapturedResult;
use App\Models\Formulars\Formula;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\StageHeader;
use App\SampleHeader;
use App\Services\GroupedWorksheets\GroupedWorksheetAssignmentService;
use Illuminate\Support\Collection;
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

    public ?string $activeGroupedHolderId = null;

    public $formulas;

    public $activeFormulaId = null;

    public $hasNoCaptureSamples = false;

    public $groupedNoCaptureSamples = [];

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
        $this->groupedHolders = app(GroupedWorksheetAssignmentService::class)->resolveHoldersForBatch($batch);

        if ($this->groupedHolders->isNotEmpty()) {
            $this->activeGroupedHolderId = (string) ($this->groupedHolders->first()->id);
        }

        $requestedTab = request()->query('tab', $this->groupedHolders->isNotEmpty() ? 'grouped-pipelines' : 'formulas');
        if (in_array($requestedTab, ['grouped-pipelines', 'method-sequences', 'procedures', 'ser', 'formulas'], true)) {
            $this->activeTab = $requestedTab;
            if ($requestedTab === 'method-sequences') {
                $this->queueMethodSequencesInit();
            }
        }

        $requestedPipeline = request()->query('pipeline');
        if ($requestedPipeline && $this->groupedHolders->contains('id', $requestedPipeline)) {
            $this->activeTab = 'grouped-pipelines';
            $this->activeGroupedHolderId = (string) $requestedPipeline;
        }

        $requestedFormulaId = request()->query('formula');
        if ($requestedFormulaId) {
            $this->activeTab = 'formulas';
            $this->activeFormulaId = $requestedFormulaId;
        }

        $this->ensureTabDataLoaded();
    }

    protected function ensureTabDataLoaded(): void
    {
        if (in_array($this->activeTab, ['formulas', 'ser'], true)) {
            $this->loadFormulaWorksheetData();
        }

        if ($this->activeTab === 'method-sequences') {
            $this->loadStageHeaders();
        }
    }

    protected function loadStageHeaders(): void
    {
        if ($this->stageHeadersLoaded) {
            return;
        }

        $this->stageHeaders = StageHeader::query()
            ->whereHas('capturedResults', function ($q) {
                $q->whereHas('sample', function ($sq) {
                    $sq->where('sample_header_id', $this->batch->id);
                });
            })
            ->with(['method', 'analyte', 'sampleType', 'testStages'])
            ->orderBy('name')
            ->get();

        $this->stageHeadersLoaded = true;
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
        $capturedResults = CapturedResult::where('sample_header_id', $this->batch->id)
            ->whereNotNull('formular_id')
            ->with(['analysisElement', 'formular', 'sample'])
            ->get();

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
            $noCaptureResults = CapturedResult::where('sample_header_id', $this->batch->id)
                ->where('has_no_result_capture', 1)
                ->with(['sample', 'analysis_type'])
                ->get();

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
        if (! in_array($tab, ['grouped-pipelines', 'formulas', 'method-sequences', 'procedures', 'ser'], true)) {
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stageHeadersPayload(): array
    {
        return $this->stageHeaders->map(function ($stageHeader) {
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
