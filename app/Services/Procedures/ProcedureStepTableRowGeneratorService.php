<?php

namespace App\Services\Procedures;

use App\AnalysisMethod;
use App\CapturedResult;
use App\Enums\Procedures\ProcedureTableRowDriver;
use App\Models\Equipments\Equipment;
use App\Models\Procedures\ProcedureStepTableColumn;
use App\Models\Procedures\ProcedureStepTableStaticCell;
use App\Models\Procedures\ProcedureStepTableStaticRow;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\Procedures\SampleProcedureStepTableCellValue;
use App\Models\Procedures\SampleProcedureStepTableInstance;
use App\Models\Procedures\SampleProcedureStepTableRow;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Collection;

class ProcedureStepTableRowGeneratorService
{
    public function firstOrCreateInstance(SampleHeader $batch, ProcedureWorksheetStep $step): SampleProcedureStepTableInstance
    {
        return SampleProcedureStepTableInstance::firstOrCreate(
            [
                'sample_header_id' => $batch->id,
                'procedure_worksheet_step_id' => $step->id,
            ],
            ['status' => 'draft'],
        );
    }

    /**
     * Ensure rows exist for the instance based on step table_mode.
     *
     * @return int Number of new rows created
     */
    public function syncRows(SampleProcedureStepTableInstance $instance, SampleHeader $batch, ProcedureWorksheetStep $step): int
    {
        if ($step->table_mode === 'static') {
            return $this->materializeStaticRows($instance, $step);
        }

        return $this->syncAutoRows($instance, $batch, $step);
    }

    /**
     * @return int Number of new auto rows created
     */
    public function syncAutoRows(
        SampleProcedureStepTableInstance $instance,
        SampleHeader $batch,
        ProcedureWorksheetStep $step,
    ): int {
        if (! $step->row_driver) {
            return 0;
        }

        $driver = ProcedureTableRowDriver::from($step->row_driver);
        $definitions = $this->buildDriverDefinitions($driver, $batch, $step);

        $existing = SampleProcedureStepTableRow::where('instance_id', $instance->id)
            ->where('row_source', 'auto')
            ->get()
            ->keyBy(fn ($r) => ($r->driver_type ?? '').':'.($r->driver_id ?? ''));

        $created = 0;
        $maxIndex = (int) SampleProcedureStepTableRow::where('instance_id', $instance->id)->max('row_index');

        foreach ($definitions as $def) {
            $key = $def['driver_type'].':'.$def['driver_id'];
            if ($existing->has($key)) {
                continue;
            }

            $maxIndex++;
            $row = SampleProcedureStepTableRow::create([
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

    public function materializeStaticRows(
        SampleProcedureStepTableInstance $instance,
        ProcedureWorksheetStep $step,
    ): int {
        $existingStatic = SampleProcedureStepTableRow::where('instance_id', $instance->id)
            ->where('row_source', 'static')
            ->count();

        if ($existingStatic > 0) {
            return 0;
        }

        $staticRows = ProcedureStepTableStaticRow::where('procedure_worksheet_step_id', $step->id)
            ->with('cells')
            ->orderBy('order')
            ->get();

        $columns = ProcedureStepTableColumn::where('procedure_worksheet_step_id', $step->id)
            ->orderBy('order')
            ->get()
            ->keyBy('id');

        $created = 0;
        foreach ($staticRows as $index => $staticRow) {
            $row = SampleProcedureStepTableRow::create([
                'instance_id' => $instance->id,
                'row_index' => $index + 1,
                'row_source' => 'static',
                'driver_type' => null,
                'driver_id' => null,
            ]);

            foreach ($columns as $column) {
                $staticCell = ProcedureStepTableStaticCell::where('static_row_id', $staticRow->id)
                    ->where('column_id', $column->id)
                    ->first();

                SampleProcedureStepTableCellValue::updateOrCreate(
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
        SampleProcedureStepTableInstance $instance,
        ProcedureWorksheetStep $step,
    ): SampleProcedureStepTableRow {
        $maxIndex = (int) SampleProcedureStepTableRow::where('instance_id', $instance->id)->max('row_index');

        $row = SampleProcedureStepTableRow::create([
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
        SampleHeader $batch,
        ProcedureWorksheetStep $step,
    ): array {
        return match ($driver) {
            ProcedureTableRowDriver::SampleHeader => [[
                'driver_type' => SampleHeader::class,
                'driver_id' => (string) $batch->id,
            ]],
            ProcedureTableRowDriver::SampleDetail => SampleDetails::where('sample_header_id', $batch->id)
                ->orderBy('sample_code')
                ->get()
                ->map(fn ($d) => [
                    'driver_type' => SampleDetails::class,
                    'driver_id' => (string) $d->id,
                ])
                ->all(),
            ProcedureTableRowDriver::CapturedResult => $this->capturedResultDefinitions($batch, $step),
            ProcedureTableRowDriver::Method => $this->methodDefinitions($batch, $step),
            ProcedureTableRowDriver::Equipment => $this->equipmentDefinitions($batch, $step),
        };
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function capturedResultDefinitions(SampleHeader $batch, ProcedureWorksheetStep $step): array
    {
        $query = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('procedure_worksheet_id', $step->procedure_worksheet_id);

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
    protected function methodDefinitions(SampleHeader $batch, ProcedureWorksheetStep $step): array
    {
        $methodIds = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('procedure_worksheet_id', $step->procedure_worksheet_id)
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
    protected function equipmentDefinitions(SampleHeader $batch, ProcedureWorksheetStep $step): array
    {
        $equipmentIds = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('procedure_worksheet_id', $step->procedure_worksheet_id)
            ->whereNotNull('equipment_id')
            ->distinct()
            ->pluck('equipment_id');

        if ($equipmentIds->isEmpty()) {
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

        return $equipmentIds->map(fn ($id) => [
            'driver_type' => Equipment::class,
            'driver_id' => (string) $id,
        ])->all();
    }

    protected function seedEmptyCellsForRow(SampleProcedureStepTableRow $row, ProcedureWorksheetStep $step): void
    {
        $columns = ProcedureStepTableColumn::where('procedure_worksheet_step_id', $step->id)->get();
        foreach ($columns as $column) {
            SampleProcedureStepTableCellValue::firstOrCreate(
                [
                    'row_id' => $row->id,
                    'column_id' => $column->id,
                ],
                ['value' => null],
            );
        }
    }

    /**
     * @return Collection<int, ProcedureWorksheetStep>
     */
    public function customTableStepsForWorksheet(string $worksheetId): Collection
    {
        return ProcedureWorksheetStep::query()
            ->where('procedure_worksheet_id', $worksheetId)
            ->where('value_type', 'custom_table')
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }
}
