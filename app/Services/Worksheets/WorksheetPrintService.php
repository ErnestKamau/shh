<?php

namespace App\Services\Worksheets;

use App\AnalysisMethod;
use App\CapturedResult;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaStep;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheetColumn;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetCellValue;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetInstance;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetRow;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\SerWorksheetStep;
use App\Models\StageHeader;
use App\Models\TrackSampleResult;
use App\Models\Worksheets\SerHeaderWorksheetSampleRelation;
use App\SampleHeader;
use App\Services\GroupedWorksheets\GroupedResultsCaptureService;
use App\Services\LogEntryWorksheets\LogEntryRowGeneratorService;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class WorksheetPrintService
{
    public function __construct(
        private WorksheetMetaResolver $metaResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(string $tab, SampleHeader $batch, array $params = []): array
    {
        return match ($tab) {
            'formulas' => $this->buildFormulaPrint($batch, $params),
            'method-sequences' => $this->buildMethodSequencePrint($batch, $params),
            'log-entry' => $this->buildLogEntryPrint($batch, $params),
            'grouped-pipelines' => $this->buildGroupedPipelinePrint($batch, $params),
            'procedures' => $this->buildProcedurePrint($batch, $params),
            'ser' => $this->buildSerPrint($batch, $params),
            default => throw new InvalidArgumentException("Unsupported worksheet print tab [{$tab}]."),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormulaPrint(SampleHeader $batch, array $params): array
    {
        $formulaId = (string) ($params['formula_id'] ?? '');
        $formula = Formula::with('activeVersion')->findOrFail($formulaId);

        $capturedResults = $this->scopedCapturedResults($batch)
            ->where('formular_id', $formulaId)
            ->with(WorksheetMetaResolver::EAGER)
            ->get();

        $metaRows = $this->metaResolver->forMany($capturedResults);
        $metaSummary = $this->metaResolver->summaryForMany($capturedResults);
        $runDetails = $this->formulaRunDetails($capturedResults);

        $steps = $formula->activeVersion
            ? FormulaStep::where('formula_version_id', $formula->activeVersion->id)->orderBy('step_number')->get()
            : collect();

        $captureRows = [];
        foreach ($capturedResults as $captured) {
            $saved = SampleCapturedWorksheetFormula::query()
                ->where('captured_result_id', $captured->id)
                ->with(['stepData', 'mandatoryData'])
                ->first();

            $stepValues = [];
            if ($saved) {
                foreach ($saved->stepData as $stepData) {
                    $stepValues[(string) $stepData->formula_step_id] = (string) ($stepData->step_value ?? '');
                }
            }

            $captureRows[] = [
                'sample_code' => $captured->sample?->sample_code ?? '—',
                'final_result' => (string) ($saved?->final_result ?? $captured->result ?? '—'),
                'step_values' => $stepValues,
            ];
        }

        return [
            'view' => 'worksheets.print.formula',
            'title' => $formula->name,
            'batch' => $batch,
            'metaRows' => $metaRows,
            'metaSummary' => $metaSummary,
            'runDetails' => $runDetails,
            'steps' => $steps,
            'captureRows' => $captureRows,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMethodSequencePrint(SampleHeader $batch, array $params): array
    {
        $stageHeaderId = (string) ($params['stage_header_id'] ?? '');
        $trackId = isset($params['track_id']) ? (string) $params['track_id'] : null;

        $stageHeader = StageHeader::with(['method', 'analyte', 'sampleType'])->findOrFail($stageHeaderId);

        $capturedQuery = CapturedResult::query()
            ->whereHas('sample', fn ($q) => $q->where('sample_header_id', $batch->id))
            ->where('stage_header_id', $stageHeaderId);

        $this->scopeVisible($capturedQuery);

        $capturedResults = $capturedQuery
            ->with(WorksheetMetaResolver::EAGER)
            ->get();

        $metaRows = $this->metaResolver->forMany($capturedResults);
        $metaSummary = $this->metaResolver->summaryForMany($capturedResults);

        $sampleResults = [];
        if ($trackId) {
            $trackResults = TrackSampleResult::query()
                ->where('track_id', $trackId)
                ->get()
                ->keyBy('captured_result_id');

            foreach ($capturedResults as $captured) {
                $trackRow = $trackResults->get($captured->id);
                $meta = $this->metaResolver->forCapturedResult($captured);

                $sampleResults[] = array_merge($meta, [
                    'result' => (string) ($trackRow?->result ?? $captured->result ?? '—'),
                    'remark' => (string) ($trackRow?->remark ?? $captured->remark ?? '—'),
                ]);
            }
        }

        return [
            'view' => 'worksheets.print.method-sequence',
            'title' => $stageHeader->name,
            'batch' => $batch,
            'stageHeader' => $stageHeader,
            'metaRows' => $metaRows,
            'metaSummary' => $metaSummary,
            'sampleResults' => $sampleResults,
            'trackId' => $trackId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLogEntryPrint(SampleHeader $batch, array $params): array
    {
        $worksheetId = (string) ($params['log_entry_worksheet_id'] ?? '');
        $worksheet = LogEntryWorksheet::findOrFail($worksheetId);
        $instance = app(LogEntryRowGeneratorService::class)->firstOrCreateInstance($batch, $worksheet);

        $columns = LogEntryWorksheetColumn::query()
            ->where('log_entry_worksheet_id', $worksheetId)
            ->orderBy('order')
            ->get();

        $rows = SampleLogEntryWorksheetRow::query()
            ->where('instance_id', $instance->id)
            ->orderBy('row_index')
            ->get();

        $cellValues = SampleLogEntryWorksheetCellValue::query()
            ->whereIn('row_id', $rows->pluck('id'))
            ->get()
            ->groupBy('row_id');

        $capturedIds = $rows
            ->where('driver_type', 'captured_results')
            ->pluck('driver_id')
            ->filter()
            ->values();

        $capturedResults = CapturedResult::query()
            ->whereIn('id', $capturedIds)
            ->with(WorksheetMetaResolver::EAGER)
            ->get()
            ->keyBy('id');

        $metaRows = $this->metaResolver->forMany($capturedResults->values());
        $metaSummary = $this->metaResolver->summaryForMany($capturedResults->values());

        $tableRows = [];
        foreach ($rows as $row) {
            $cells = [];
            $rowCells = $cellValues->get($row->id) ?? collect();

            foreach ($columns as $column) {
                $match = $rowCells->firstWhere('column_id', $column->id);
                $cells[$column->key] = (string) ($match?->value ?? '');
            }

            $tableRows[] = [
                'row_index' => (int) $row->row_index,
                'cells' => $cells,
                'meta' => $row->driver_type === 'captured_results' && $capturedResults->has($row->driver_id)
                    ? $this->metaResolver->forCapturedResult($capturedResults->get($row->driver_id))
                    : null,
            ];
        }

        return [
            'view' => 'worksheets.print.log-entry',
            'title' => $worksheet->name,
            'batch' => $batch,
            'worksheet' => $worksheet,
            'columns' => $columns,
            'tableRows' => $tableRows,
            'metaRows' => $metaRows,
            'metaSummary' => $metaSummary,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGroupedPipelinePrint(SampleHeader $batch, array $params): array
    {
        $pipelineId = (string) ($params['pipeline_id'] ?? '');
        $holder = GroupedWorksheetHolder::findOrFail($pipelineId);

        $service = app(GroupedResultsCaptureService::class);
        $matrix = $service->loadMatrixWithDrafts($batch, $holder);
        $capturedResults = $service->loadCapturedResults($batch, $holder)
            ->load(WorksheetMetaResolver::EAGER);

        $metaRows = $this->metaResolver->forMany($capturedResults);
        $metaSummary = $this->metaResolver->summaryForMany($capturedResults);

        return [
            'view' => 'worksheets.print.grouped-pipeline',
            'title' => $holder->name,
            'batch' => $batch,
            'holder' => $holder,
            'matrix' => $matrix,
            'metaRows' => $metaRows,
            'metaSummary' => $metaSummary,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildProcedurePrint(SampleHeader $batch, array $params): array
    {
        $worksheetId = (string) ($params['procedure_worksheet_id'] ?? '');
        $worksheet = ProcedureWorksheet::findOrFail($worksheetId);

        $capturedResults = $this->scopedCapturedResults($batch)
            ->where('procedure_worksheet_id', $worksheetId)
            ->with(WorksheetMetaResolver::EAGER)
            ->get();

        return [
            'view' => 'worksheets.print.procedure',
            'title' => $worksheet->name,
            'batch' => $batch,
            'worksheet' => $worksheet,
            'metaRows' => $this->metaResolver->forMany($capturedResults),
            'metaSummary' => $this->metaResolver->summaryForMany($capturedResults),
            'redirectToPdf' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSerPrint(SampleHeader $batch, array $params): array
    {
        $runId = (string) ($params['run_id'] ?? '');
        $analysisTypeId = (string) ($params['analysis_type_id'] ?? '');

        if ($runId !== '') {
            $header = SerHeaderWorksheetSampleRelation::with(['sample', 'steps', 'testKits'])->findOrFail($runId);
            $analysisTypeId = (string) $header->analysis_type_id;
        } else {
            $sampleIds = $batch->samples()->pluck('id');
            $header = SerHeaderWorksheetSampleRelation::query()
                ->whereIn('sample_detail_id', $sampleIds)
                ->when($analysisTypeId !== '', fn ($q) => $q->where('analysis_type_id', $analysisTypeId))
                ->with(['sample', 'steps', 'testKits'])
                ->latest('updated_at')
                ->first();

            if (! $header) {
                throw new InvalidArgumentException('No SER worksheet run found for this batch.');
            }
        }

        $capturedResults = $this->scopedCapturedResults($batch)
            ->where('analysis_type_id', $analysisTypeId)
            ->where('has_no_result_capture', true)
            ->with(WorksheetMetaResolver::EAGER)
            ->get();

        $analystNames = collect($header->analyst_ids ?? [])
            ->map(fn ($id) => User::query()->find($id)?->name)
            ->filter()
            ->implode(', ');

        $methodName = $header->method_id
            ? (AnalysisMethod::query()->find($header->method_id)?->name ?? '—')
            : '—';

        $metaSummary = $this->metaResolver->summaryForMany($capturedResults, [
            'analyst_name' => $analystNames !== '' ? $analystNames : null,
        ]);

        if ($methodName !== '—') {
            $metaSummary['method'] = $methodName;
        }

        $steps = $header->steps->map(function ($step) {
            $configStep = SerWorksheetStep::find($step->ser_worksheet_step_id);

            return [
                'step_name' => $configStep->step ?? 'Step',
                'measurand_id' => $step->measurand_id,
                'equipment_id' => $step->equipment_id,
                'analyst' => User::query()->find($step->analyst_id)?->name ?? '—',
            ];
        })->all();

        $testKits = $header->testKits->map(fn ($kit) => [
            'test_name' => $kit->test_name,
            'kit_lot_number' => $kit->kit_lot_number,
            'wells_used' => $kit->wells_used,
        ])->all();

        return [
            'view' => 'worksheets.print.ser',
            'title' => 'SER Worksheet',
            'batch' => $batch,
            'header' => $header,
            'metaRows' => $this->metaResolver->forMany($capturedResults, [
                'analyst_name' => $analystNames !== '' ? $analystNames : null,
            ]),
            'metaSummary' => $metaSummary,
            'steps' => $steps,
            'testKits' => $testKits,
        ];
    }

    private function scopedCapturedResults(SampleHeader $batch)
    {
        $query = CapturedResult::query()->where('sample_header_id', $batch->id);
        $this->scopeVisible($query);

        return $query;
    }

    private function scopeVisible($query): void
    {
        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());
    }

    /**
     * @param  Collection<int, CapturedResult>  $capturedResults
     * @return array<string, string>
     */
    private function formulaRunDetails(Collection $capturedResults): array
    {
        $firstCaptured = $capturedResults->first();
        if (! $firstCaptured) {
            return [];
        }

        $saved = SampleCapturedWorksheetFormula::query()
            ->where('captured_result_id', $firstCaptured->id)
            ->with(['doneByUser', 'readByUser'])
            ->first();

        if (! $saved) {
            return [];
        }

        return [
            'date' => $saved->date?->format('Y-m-d') ?? '—',
            'lab_no' => trim((string) ($saved->lab_no ?? '')) !== '' ? (string) $saved->lab_no : '—',
            'time_in' => $saved->time_in?->format('H:i') ?? '—',
            'done_by' => $saved->doneByUser?->name ?? '—',
            'time_out' => $saved->time_out?->format('H:i') ?? '—',
            'read_date' => $saved->read_date?->format('Y-m-d') ?? '—',
            'read_by' => $saved->readByUser?->name ?? '—',
        ];
    }
}
