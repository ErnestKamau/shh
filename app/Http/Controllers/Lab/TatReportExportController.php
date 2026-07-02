<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\Lab\TatReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TatReportExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:laboratory.components.lab-reports.view');
    }

    public function __invoke(Request $request, TatReportService $service): StreamedResponse
    {
        $filters = $service->normalizeFilters($request->all());
        $type = $request->string('type')->toString() ?: $request->string('tab')->toString();

        if ($type === 'parameters') {
            return $this->exportParameters($service, $filters);
        }

        return $this->exportBatch($service, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportBatch(TatReportService $service, array $filters): StreamedResponse
    {
        $rows = $service->getBatchExportRows($filters);
        $fileName = 'tat-batch-deadline-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Batch Code',
                'Client',
                'Sample Type',
                'Workflow Stage',
                'Priority',
                'Receipt Date',
                'Target Date',
                'Status Days',
                'Deadline Status',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->batch_code,
                    $row->client_name,
                    $row->sample_type_name,
                    $row->status,
                    $row->priority,
                    $row->receipt_date,
                    $row->target_date,
                    $row->status_days_label,
                    $row->deadline_bucket,
                ]);
            }

            fclose($file);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportParameters(TatReportService $service, array $filters): StreamedResponse
    {
        $rows = $service->getParameterExportRows($filters);
        $fileName = 'tat-per-parameter-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Analyte',
                'Sample Code',
                'Sample Type',
                'Analysis Type',
                'Receipt Date',
                'Start Analysis',
                'Expected Date',
                'Actual Date',
                'TAT Days',
                'Analyst',
                'TAT Remark',
            ]);

            foreach ($rows as $row) {
                $offset = (int) ($row->signed_offset ?? 0);
                $offsetLabel = $offset < 0
                    ? "{$offset}d"
                    : ($offset === 0 ? '0d' : "+{$offset}d");

                fputcsv($file, [
                    $row->analyte_name,
                    $row->sample_code,
                    $row->sample_type_name,
                    $row->analysis_type_name,
                    $row->receipt_date,
                    $row->start_date_analysis,
                    $row->tat_date,
                    $row->finished_date,
                    $offsetLabel,
                    $row->analyst_name,
                    $row->tat_remark_label,
                ]);
            }

            fclose($file);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
