<?php

namespace App\Livewire\Lab\Reports;

use App\AnalysisType;
use App\SampleType;
use App\Services\Lab\TatReportService;
use Livewire\Component;
use Livewire\WithPagination;

class TatReport extends Component
{
    use WithPagination;

    public string $activeTab = 'batch';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?string $userId = null;

    public ?string $sampleTypeId = null;

    public ?string $analysisTypeId = null;

    public ?string $analyteId = null;

    public ?string $workflowStage = null;

    public string $deadlineStatus = 'all';

    public string $tatRemark = 'all';

    public int $perPage = 25;

    /** @var array<int, array{id: int|string, name: string}> */
    public array $analysisTypes = [];

    /** @var array<int, array{id: int|string, name: string, code?: string}> */
    public array $analytes = [];

    protected $queryString = [
        'activeTab' => ['except' => 'batch', 'as' => 'tab'],
        'dateFrom' => ['except' => null, 'as' => 'date_from'],
        'dateTo' => ['except' => null, 'as' => 'date_to'],
        'userId' => ['except' => null, 'as' => 'user_id'],
        'sampleTypeId' => ['except' => null, 'as' => 'sample_type_id'],
        'analysisTypeId' => ['except' => null, 'as' => 'analysis_type_id'],
        'analyteId' => ['except' => null, 'as' => 'analyte_id'],
        'workflowStage' => ['except' => null, 'as' => 'workflow_stage'],
        'deadlineStatus' => ['except' => 'all', 'as' => 'deadline_status'],
        'tatRemark' => ['except' => 'all', 'as' => 'tat_remark'],
    ];

    public function mount(): void
    {
        if (! $this->dateFrom && ! $this->dateTo) {
            $this->dateFrom = now()->subDays(30)->toDateString();
            $this->dateTo = now()->toDateString();
        }

        $this->loadDependentOptions();
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['batch', 'parameters'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage('batchPage');
        $this->resetPage('parameterPage');
    }

    public function updatedSampleTypeId(): void
    {
        $this->analysisTypeId = null;
        $this->analyteId = null;
        $this->loadDependentOptions();
        $this->resetPage('batchPage');
        $this->resetPage('parameterPage');
    }

    public function updatedAnalysisTypeId(): void
    {
        $this->analyteId = null;
        $this->loadDependentOptions();
        $this->resetPage('batchPage');
        $this->resetPage('parameterPage');
    }

    public function applyFilters(): void
    {
        $this->resetPage('batchPage');
        $this->resetPage('parameterPage');
    }

    public function resetFilters(): void
    {
        $this->dateFrom = now()->subDays(30)->toDateString();
        $this->dateTo = now()->toDateString();
        $this->userId = null;
        $this->sampleTypeId = null;
        $this->analysisTypeId = null;
        $this->analyteId = null;
        $this->workflowStage = null;
        $this->deadlineStatus = 'all';
        $this->tatRemark = 'all';
        $this->loadDependentOptions();
        $this->resetPage('batchPage');
        $this->resetPage('parameterPage');
    }

    public function exportUrl(string $type): string
    {
        return route('lab-report-tat.export', array_filter([
            'type' => $type,
            'tab' => $this->activeTab,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'user_id' => $this->userId,
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_id' => $this->analysisTypeId,
            'analyte_id' => $this->analyteId,
            'workflow_stage' => $this->workflowStage,
            'deadline_status' => $this->deadlineStatus !== 'all' ? $this->deadlineStatus : null,
            'tat_remark' => $this->tatRemark !== 'all' ? $this->tatRemark : null,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function render()
    {
        $service = app(TatReportService::class);
        $filters = $this->filterPayload();

        return view('livewire.lab.reports.tat-report', [
            'batchKpis' => $service->getBatchKpis($filters),
            'parameterKpis' => $service->getParameterKpis($filters),
            'batchRows' => $service->getBatchDeadlinePage($filters, $this->getPage('batchPage'), $this->perPage),
            'parameterRows' => $service->getParameterPage($filters, $this->getPage('parameterPage'), $this->perPage),
            'sampleTypes' => SampleType::query()->where('active', 1)->orderBy('name')->get(),
            'analysts' => getActiveUsersByRole('Laboratory Analyst'),
            'workflowStages' => $this->workflowStageOptions(),
            'tatRemarks' => getTatRemark(),
            'exportBatchUrl' => $this->exportUrl('batch'),
            'exportParameterUrl' => $this->exportUrl('parameters'),
        ])
            ->extends('layouts.lab.layout.app', ['dataTable' => false, 'datePicker' => true, 'select2' => false])
            ->section('content2');
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterPayload(): array
    {
        return [
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'user_id' => $this->userId,
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_id' => $this->analysisTypeId,
            'analyte_id' => $this->analyteId,
            'workflow_stage' => $this->workflowStage,
            'deadline_status' => $this->deadlineStatus,
            'tat_remark' => $this->tatRemark,
        ];
    }

    protected function loadDependentOptions(): void
    {
        $this->analysisTypes = [];
        $this->analytes = [];

        if ($this->sampleTypeId) {
            $this->analysisTypes = AnalysisType::query()
                ->where('sample_type_id', $this->sampleTypeId)
                ->where('active', 1)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['id' => $row->id, 'name' => $row->name])
                ->all();
        }

        if ($this->analysisTypeId) {
            $this->analytes = \App\Analyte::query()
                ->where('analysis_type_id', $this->analysisTypeId)
                ->where('active', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
                ->map(fn ($row) => ['id' => $row->id, 'name' => $row->name, 'code' => $row->code])
                ->all();
        }
    }

    /**
     * @return array<int, string>
     */
    protected function workflowStageOptions(): array
    {
        return [
            'Samples En-Route',
            'Samples Reception',
            'Samples Request Review',
            'Samples In Lab',
            'Sample Verification',
            'Sample Approval',
            'Reports In Payment',
            'Reports for Collection',
            'Finished Sample',
        ];
    }
}
