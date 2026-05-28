<?php

namespace App\Services\LogEntryWorksheets;

use App\AnalysisMethod;
use App\CapturedResult;
use App\Enums\LogEntryWorksheet\LogEntryRowDriver;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetInstance;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetRow;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Collection;

class LogEntryRowGeneratorService
{
    /**
     * Ensure auto rows exist for the instance; does not remove manual rows.
     *
     * @return int Number of new rows created
     */
    public function syncAutoRows(SampleLogEntryWorksheetInstance $instance, SampleHeader $batch): int
    {
        $worksheet = $instance->worksheet;
        $driver = LogEntryRowDriver::from($worksheet->row_driver);
        $definitions = $this->buildDriverDefinitions($driver, $batch, $worksheet, $instance);

        $existing = SampleLogEntryWorksheetRow::where('instance_id', $instance->id)
            ->where('row_source', 'auto')
            ->get()
            ->keyBy(fn ($r) => ($r->driver_type ?? '').':'.($r->driver_id ?? ''));

        $created = 0;
        $maxIndex = (int) SampleLogEntryWorksheetRow::where('instance_id', $instance->id)->max('row_index');

        foreach ($definitions as $def) {
            $key = $def['driver_type'].':'.$def['driver_id'];
            if ($existing->has($key)) {
                continue;
            }

            $maxIndex++;
            SampleLogEntryWorksheetRow::create([
                'instance_id' => $instance->id,
                'row_index' => $maxIndex,
                'row_source' => 'auto',
                'driver_type' => $def['driver_type'],
                'driver_id' => $def['driver_id'],
            ]);
            $created++;
        }

        return $created;
    }

    public function addManualRow(SampleLogEntryWorksheetInstance $instance): SampleLogEntryWorksheetRow
    {
        $maxIndex = (int) SampleLogEntryWorksheetRow::where('instance_id', $instance->id)->max('row_index');

        return SampleLogEntryWorksheetRow::create([
            'instance_id' => $instance->id,
            'row_index' => $maxIndex + 1,
            'row_source' => 'manual',
            'driver_type' => null,
            'driver_id' => null,
        ]);
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function buildDriverDefinitions(
        LogEntryRowDriver $driver,
        SampleHeader $batch,
        LogEntryWorksheet $worksheet,
        SampleLogEntryWorksheetInstance $instance,
    ): array {
        return match ($driver) {
            LogEntryRowDriver::SampleHeader => [[
                'driver_type' => SampleHeader::class,
                'driver_id' => (string) $batch->id,
            ]],
            LogEntryRowDriver::SampleDetail => SampleDetails::where('sample_header_id', $batch->id)
                ->orderBy('sample_code')
                ->get()
                ->map(fn ($d) => [
                    'driver_type' => SampleDetails::class,
                    'driver_id' => (string) $d->id,
                ])
                ->all(),
            LogEntryRowDriver::CapturedResult => $this->capturedResultDefinitions($batch, $worksheet),
            LogEntryRowDriver::Method => $this->methodDefinitions($batch, $worksheet),
        };
    }

    /**
     * @return array<int, array{driver_type: string, driver_id: string}>
     */
    protected function capturedResultDefinitions(SampleHeader $batch, LogEntryWorksheet $worksheet): array
    {
        $query = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('log_entry_worksheet_id', $worksheet->id);

        $filters = $worksheet->row_driver_filters ?? [];
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
    protected function methodDefinitions(SampleHeader $batch, LogEntryWorksheet $worksheet): array
    {
        $methodIds = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('log_entry_worksheet_id', $worksheet->id)
            ->whereNotNull('method_id')
            ->distinct()
            ->pluck('method_id');

        return $methodIds->map(fn ($id) => [
            'driver_type' => AnalysisMethod::class,
            'driver_id' => (string) $id,
        ])->all();
    }

    public function firstOrCreateInstance(SampleHeader $batch, LogEntryWorksheet $worksheet): SampleLogEntryWorksheetInstance
    {
        return SampleLogEntryWorksheetInstance::firstOrCreate(
            [
                'sample_header_id' => $batch->id,
                'log_entry_worksheet_id' => $worksheet->id,
            ],
            ['status' => 'draft'],
        );
    }
}
