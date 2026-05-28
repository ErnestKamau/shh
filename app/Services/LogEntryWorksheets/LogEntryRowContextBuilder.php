<?php

namespace App\Services\LogEntryWorksheets;

use App\AnalysisMethod;
use App\CapturedResult;
use App\SampleDetails;
use App\SampleHeader;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetRow;

class LogEntryRowContextBuilder
{
    /**
     * Built-in variables available in derived column expressions.
     *
     * @return array<string, mixed>
     */
    public function buildForRow(SampleLogEntryWorksheetRow $row): array
    {
        if (! $row->driver_type || ! $row->driver_id) {
            return [
                'row_index' => $row->row_index,
            ];
        }

        return match ($row->driver_type) {
            SampleHeader::class => $this->fromSampleHeader($row),
            SampleDetails::class => $this->fromSampleDetail($row),
            CapturedResult::class => $this->fromCapturedResult($row),
            AnalysisMethod::class => $this->fromMethod($row),
            default => ['row_index' => $row->row_index],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromSampleHeader(SampleLogEntryWorksheetRow $row): array
    {
        $header = SampleHeader::find($row->driver_id);

        return [
            'row_index' => $row->row_index,
            'batch_code' => $header?->batch_code ?? '',
            'sample_header_id' => (string) $row->driver_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromSampleDetail(SampleLogEntryWorksheetRow $row): array
    {
        $detail = SampleDetails::find($row->driver_id);

        return [
            'row_index' => $row->row_index,
            'sample_code' => $detail?->sample_code ?? '',
            'sample_detail_id' => (string) $row->driver_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromCapturedResult(SampleLogEntryWorksheetRow $row): array
    {
        $cr = CapturedResult::with(['sample', 'analysis_type'])->find($row->driver_id);
        $method = $cr?->method_id ? AnalysisMethod::find($cr->method_id) : null;
        $analyte = $cr?->analyte_id ? \App\Analyte::find($cr->analyte_id) : null;

        return [
            'row_index' => $row->row_index,
            'sample_code' => $cr?->sample_detail_code ?? $cr?->sample?->sample_code ?? '',
            'analyte_name' => $analyte?->name ?? '',
            'method_name' => $method?->name ?? '',
            'captured_result_id' => (string) $row->driver_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromMethod(SampleLogEntryWorksheetRow $row): array
    {
        $method = AnalysisMethod::find($row->driver_id);

        return [
            'row_index' => $row->row_index,
            'method_name' => $method?->name ?? '',
            'method_id' => (string) $row->driver_id,
        ];
    }
}
