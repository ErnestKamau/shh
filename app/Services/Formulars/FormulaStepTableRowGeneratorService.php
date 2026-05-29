<?php

namespace App\Services\Formulars;

use App\AnalysisMethod;
use App\CapturedResult;
use App\Enums\Procedures\ProcedureTableRowDriver;
use App\Models\Equipments\Equipment;
use App\Models\Formulars\FormulaStep;
use App\Models\Formulars\FormulaStepTableColumn;
use App\Models\Formulars\FormulaStepTableStaticCell;
use App\Models\Formulars\FormulaStepTableStaticRow;
use App\Models\Formulars\SampleFormulaStepTableCellValue;
use App\Models\Formulars\SampleFormulaStepTableInstance;
use App\Models\Formulars\SampleFormulaStepTableRow;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Collection;

class FormulaStepTableRowGeneratorService
{
    public function firstOrCreateInstance(
        SampleCapturedWorksheetFormula $worksheet,
        FormulaStep $step,
    ): SampleFormulaStepTableInstance {
        return SampleFormulaStepTableInstance::firstOrCreate(
            [
                'worksheet_formular_id' => $worksheet->id,
                'formula_step_id' => $step->id,
            ],
            ['status' => 'draft'],
        );
    }

    public function syncRows(
        SampleFormulaStepTableInstance $instance,
        SampleCapturedWorksheetFormula $worksheet,
        FormulaStep $step,
    ): int {
        if ($step->table_mode === 'static') {
            return $this->materializeStaticRows($instance, $step);
        }

        return $this->syncAutoRows($instance, $worksheet, $step);
    }

    /**
     * Sync rows for a worksheet-level formula custom table (one table, one row per sample).
     *
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $capturedResults
     */
    public function syncRowsForFormulaWorksheet(
        SampleFormulaStepTableInstance $instance,
        SampleHeader $batch,
        FormulaStep $step,
        Collection $capturedResults,
    ): int {
        if ($step->table_mode === 'static') {
            return $this->materializeStaticRows($instance, $step);
        }

        if (! $step->row_driver) {
            return 0;
        }

        $driver = ProcedureTableRowDriver::from($step->row_driver);
        $definitions = $this->buildFormulaWorksheetDriverDefinitions($driver, $batch, $step, $capturedResults);

        return $this->createMissingAutoRows($instance, $step, $definitions);
    }

    /**
     * @param  array<int, array{driver_type: string, driver_id: string}>  $definitions
     */
    protected function createMissingAutoRows(
        SampleFormulaStepTableInstance $instance,
        FormulaStep $step,
        array $definitions,
    ): int {
        $existing = SampleFormulaStepTableRow::where('instance_id', $instance->id)
            ->where('row_source', 'auto')
            ->get()
            ->keyBy(fn ($r) => ($r->driver_type ?? '').':'.($r->driver_id ?? ''));

        $created = 0;
        $maxIndex = (int) SampleFormulaStepTableRow::where('instance_id', $instance->id)->max('row_index');

        foreach ($definitions as $def) {
            $key = $def['driver_type'].':'.$def['driver_id'];
            if ($existing->has($key)) {
                continue;
            }

            $maxIndex++;
            $row = SampleFormulaStepTableRow::create([
                'instance_id' => $instance->id,
                'row_index' => $maxIndex,
                'row_source' => 'auto',
                'driver_type' => $def['driver_type'],
                'driver_id' => $def['driver_id'],
            ]);
            $this->seedEmptyCellsForRow($row, $step);
            $created++;
        }

        return $created;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $capturedResults
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function buildFormulaWorksheetDriverDefinitions(
        ProcedureTableRowDriver $driver,
        SampleHeader $batch,
        FormulaStep $step,
        Collection $capturedResults,
    ): array {
        return match ($driver) {
            ProcedureTableRowDriver::SampleHeader => [[
                'driver_type' => SampleHeader::class,
                'driver_id' => (string) $batch->id,
            ]],
            ProcedureTableRowDriver::SampleDetail => SampleDetails::query()
                ->whereIn('id', $capturedResults->pluck('sample_detail_id')->filter()->unique()->values())
                ->orderBy('sample_code')
                ->get()
                ->map(fn (SampleDetails $detail) => [
                    'driver_type' => SampleDetails::class,
                    'driver_id' => (string) $detail->id,
                ])
                ->all(),
            ProcedureTableRowDriver::CapturedResult => $capturedResults
                ->sortBy('sample_detail_code')
                ->unique('id')
                ->map(fn (CapturedResult $cr) => [
                    'driver_type' => CapturedResult::class,
                    'driver_id' => (string) $cr->id,
                ])
                ->values()
                ->all(),
            ProcedureTableRowDriver::Method => $this->methodDefinitionsForBatch($batch, $capturedResults),
            ProcedureTableRowDriver::Equipment => $this->equipmentDefinitionsForBatch($capturedResults),
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $capturedResults
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function methodDefinitionsForBatch(SampleHeader $batch, Collection $capturedResults): array
    {
        $methodIds = $capturedResults
            ->pluck('method_id')
            ->filter()
            ->unique();

        if ($methodIds->isEmpty()) {
            $methodIds = CapturedResult::query()
                ->where('sample_header_id', $batch->id)
                ->whereNotNull('method_id')
                ->distinct()
                ->pluck('method_id');
        }

        return $methodIds->map(fn ($id) => [
            'driver_type' => AnalysisMethod::class,
            'driver_id' => (string) $id,
        ])->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $capturedResults
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function equipmentDefinitionsForBatch(Collection $capturedResults): array
    {
        $equipmentIds = $capturedResults
            ->pluck('equipment_id')
            ->filter()
            ->unique();

        if ($equipmentIds->isNotEmpty()) {
            return $equipmentIds->map(fn ($id) => [
                'driver_type' => Equipment::class,
                'driver_id' => (string) $id,
            ])->all();
        }

        return Equipment::query()
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn ($eq) => [
                'driver_type' => Equipment::class,
                'driver_id' => (string) $eq->id,
            ])
            ->all();
    }

    public function syncAutoRows(
        SampleFormulaStepTableInstance $instance,
        SampleCapturedWorksheetFormula $worksheet,
        FormulaStep $step,
    ): int {
        if (! $step->row_driver) {
            return 0;
        }

        $driver = ProcedureTableRowDriver::from($step->row_driver);
        $definitions = $this->buildDriverDefinitions($driver, $worksheet, $step);

        return $this->createMissingAutoRows($instance, $step, $definitions);
    }

    public function materializeStaticRows(
        SampleFormulaStepTableInstance $instance,
        FormulaStep $step,
    ): int {
        $existingStatic = SampleFormulaStepTableRow::where('instance_id', $instance->id)
            ->where('row_source', 'static')
            ->count();

        if ($existingStatic > 0) {
            return 0;
        }

        $staticRows = FormulaStepTableStaticRow::where('formula_step_id', $step->id)
            ->with('cells')
            ->orderBy('order')
            ->get();

        $columns = FormulaStepTableColumn::where('formula_step_id', $step->id)
            ->orderBy('order')
            ->get()
            ->keyBy('id');

        $created = 0;
        foreach ($staticRows as $index => $staticRow) {
            $row = SampleFormulaStepTableRow::create([
                'instance_id' => $instance->id,
                'row_index' => $index + 1,
                'row_source' => 'static',
                'driver_type' => null,
                'driver_id' => null,
            ]);

            foreach ($columns as $column) {
                $staticCell = FormulaStepTableStaticCell::where('static_row_id', $staticRow->id)
                    ->where('column_id', $column->id)
                    ->first();

                SampleFormulaStepTableCellValue::updateOrCreate(
                    [
                        'row_id' => $row->id,
                        'column_id' => $column->id,
                    ],
                    ['value' => $staticCell?->default_value],
                );
            }

            $created++;
        }

        return $created;
    }

    public function addManualRow(
        SampleFormulaStepTableInstance $instance,
        FormulaStep $step,
    ): SampleFormulaStepTableRow {
        $maxIndex = (int) SampleFormulaStepTableRow::where('instance_id', $instance->id)->max('row_index');

        $row = SampleFormulaStepTableRow::create([
            'instance_id' => $instance->id,
            'row_index' => $maxIndex + 1,
            'row_source' => 'manual',
            'driver_type' => null,
            'driver_id' => null,
        ]);

        $this->seedEmptyCellsForRow($row, $step);

        return $row;
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function buildDriverDefinitions(
        ProcedureTableRowDriver $driver,
        SampleCapturedWorksheetFormula $worksheet,
        FormulaStep $step,
    ): array {
        $batch = SampleHeader::find($worksheet->sample_header_id);

        return match ($driver) {
            ProcedureTableRowDriver::SampleHeader => $batch ? [[
                'driver_type' => SampleHeader::class,
                'driver_id' => (string) $batch->id,
            ]] : [],
            ProcedureTableRowDriver::SampleDetail => $this->sampleDetailDefinitions($worksheet),
            ProcedureTableRowDriver::CapturedResult => $this->capturedResultDefinitions($worksheet, $step),
            ProcedureTableRowDriver::Method => $this->methodDefinitions($worksheet),
            ProcedureTableRowDriver::Equipment => $this->equipmentDefinitions($worksheet),
        };
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function sampleDetailDefinitions(SampleCapturedWorksheetFormula $worksheet): array
    {
        if ($worksheet->sample_detail_id) {
            return [[
                'driver_type' => SampleDetails::class,
                'driver_id' => (string) $worksheet->sample_detail_id,
            ]];
        }

        return SampleDetails::where('sample_header_id', $worksheet->sample_header_id)
            ->orderBy('sample_code')
            ->get()
            ->map(fn ($d) => [
                'driver_type' => SampleDetails::class,
                'driver_id' => (string) $d->id,
            ])
            ->all();
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function capturedResultDefinitions(
        SampleCapturedWorksheetFormula $worksheet,
        FormulaStep $step,
    ): array {
        if ($worksheet->captured_result_id) {
            return [[
                'driver_type' => CapturedResult::class,
                'driver_id' => (string) $worksheet->captured_result_id,
            ]];
        }

        $query = CapturedResult::query()
            ->where('sample_header_id', $worksheet->sample_header_id)
            ->where('formular_id', $worksheet->formular_id);

        $filters = $step->row_driver_filters ?? [];
        if (! empty($filters['analysis_type_id'])) {
            $query->where('analysis_type_id', $filters['analysis_type_id']);
        }

        return $query->orderBy('sample_detail_code')->get()->map(fn ($cr) => [
            'driver_type' => CapturedResult::class,
            'driver_id' => (string) $cr->id,
        ])->all();
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function methodDefinitions(SampleCapturedWorksheetFormula $worksheet): array
    {
        $captured = $worksheet->capturedResult;
        if ($captured?->method_id) {
            return [[
                'driver_type' => AnalysisMethod::class,
                'driver_id' => (string) $captured->method_id,
            ]];
        }

        $methodIds = CapturedResult::query()
            ->where('sample_header_id', $worksheet->sample_header_id)
            ->where('formular_id', $worksheet->formular_id)
            ->whereNotNull('method_id')
            ->distinct()
            ->pluck('method_id');

        return $methodIds->map(fn ($id) => [
            'driver_type' => AnalysisMethod::class,
            'driver_id' => (string) $id,
        ])->all();
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function equipmentDefinitions(SampleCapturedWorksheetFormula $worksheet): array
    {
        $captured = $worksheet->capturedResult;
        if ($captured?->equipment_id) {
            return [[
                'driver_type' => Equipment::class,
                'driver_id' => (string) $captured->equipment_id,
            ]];
        }

        return Equipment::query()
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn ($eq) => [
                'driver_type' => Equipment::class,
                'driver_id' => (string) $eq->id,
            ])
            ->all();
    }

    protected function seedEmptyCellsForRow(SampleFormulaStepTableRow $row, FormulaStep $step): void
    {
        $columns = FormulaStepTableColumn::where('formula_step_id', $step->id)->get();
        foreach ($columns as $column) {
            SampleFormulaStepTableCellValue::firstOrCreate(
                [
                    'row_id' => $row->id,
                    'column_id' => $column->id,
                ],
                ['value' => null],
            );
        }
    }

    /**
     * @return Collection<int, FormulaStep>
     */
    public function customTableStepsForVersion(string $formulaVersionId): Collection
    {
        return FormulaStep::query()
            ->where('formula_version_id', $formulaVersionId)
            ->where('step_type', 'custom_table')
            ->orderBy('step_number')
            ->orderBy('id')
            ->get();
    }
}
