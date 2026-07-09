<?php

namespace App\Http\Controllers\Lab\Reports;

use App\Exports\Lab\LaboratoryKpiDetailExport;
use App\Exports\Lab\LaboratoryKpiSummaryExport;
use App\Exports\Lab\RegistrationKpiDetailExport;
use App\Exports\Lab\RegistrationKpiSummaryExport;
use App\Http\Controllers\Controller;
use App\Services\Lab\SampleWorkflowKpiStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SampleWorkflowKpiExportController extends Controller
{
    public function __construct(
        private readonly SampleWorkflowKpiStatisticsService $kpiStatisticsService,
    ) {
        $this->middleware('auth');
        $this->middleware('can:laboratory.components.lab-reports.view');
    }

    public function registrationSummary(Request $request): BinaryFileResponse|StreamedResponse
    {
        [$startDate, $endDate] = $this->validatedExportDates($request);
        $rows = $this->kpiStatisticsService->getRegistrationDailyRows($startDate, $endDate);
        $export = new RegistrationKpiSummaryExport($rows);

        return $this->downloadExport(
            $request,
            $export,
            $export->headings(),
            $export->array(),
            'registration-kpis-summary',
            $startDate,
            $endDate,
        );
    }

    public function registrationDetail(Request $request): BinaryFileResponse|StreamedResponse
    {
        [$startDate, $endDate] = $this->validatedExportDates($request);
        $rows = $this->kpiStatisticsService->getRegistrationDetailRows($startDate, $endDate);
        $rows = $this->kpiStatisticsService->filterRegistrationDetailRows($rows, $this->registrationFiltersFromRequest($request));
        $export = new RegistrationKpiDetailExport($rows);

        return $this->downloadExport(
            $request,
            $export,
            $export->headings(),
            $export->array(),
            'registration-kpis-detail',
            $startDate,
            $endDate,
        );
    }

    public function laboratorySummary(Request $request): BinaryFileResponse|StreamedResponse
    {
        [$startDate, $endDate] = $this->validatedExportDates($request);
        $rows = $this->kpiStatisticsService->getLaboratoryDailyRows($startDate, $endDate);
        $export = new LaboratoryKpiSummaryExport($rows);

        return $this->downloadExport(
            $request,
            $export,
            $export->headings(),
            $export->array(),
            'laboratory-kpis-summary',
            $startDate,
            $endDate,
        );
    }

    public function laboratoryDetail(Request $request): BinaryFileResponse|StreamedResponse
    {
        [$startDate, $endDate] = $this->validatedExportDates($request);
        $rows = $this->kpiStatisticsService->getLaboratoryDetailRows($startDate, $endDate);
        $rows = $this->kpiStatisticsService->filterLaboratoryDetailRows($rows, $this->laboratoryFiltersFromRequest($request));
        $export = new LaboratoryKpiDetailExport($rows);

        return $this->downloadExport(
            $request,
            $export,
            $export->headings(),
            $export->array(),
            'laboratory-kpis-detail',
            $startDate,
            $endDate,
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function validatedExportDates(Request $request): array
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        return [
            Carbon::parse($request->input('start_date'))->startOfDay(),
            Carbon::parse($request->input('end_date'))->endOfDay(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function downloadExport(
        Request $request,
        object $excelExport,
        array $headings,
        array $rows,
        string $basename,
        Carbon $startDate,
        Carbon $endDate,
    ): BinaryFileResponse|StreamedResponse {
        $format = strtolower((string) $request->input('format', 'xlsx'));

        if ($format === 'csv') {
            $filename = sprintf(
                '%s-%s-to-%s.csv',
                $basename,
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            );

            return response()->streamDownload(function () use ($headings, $rows): void {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headings);

                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $filename = sprintf(
            '%s-%s-to-%s.xlsx',
            $basename,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        return Excel::download($excelExport, $filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationFiltersFromRequest(Request $request): array
    {
        return [
            'client' => $request->input('reg_client', ''),
            'sampler_name' => $request->input('reg_sampler_name', ''),
            'sampler_id' => $request->input('reg_sampler_id', ''),
            'equipment_id' => $request->input('reg_equipment_id', ''),
            'job_id' => $request->input('reg_job_id', ''),
            'sample_id' => $request->input('reg_sample_id', ''),
            'location' => $request->input('reg_location', ''),
            'sampling_points' => $request->input('reg_sampling_points', ''),
            'registered_by' => $request->input('reg_registered_by', ''),
            'registration_type' => $request->input('reg_type', 'all'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function laboratoryFiltersFromRequest(Request $request): array
    {
        return [
            'client' => $request->input('lab_client', ''),
            'job_id' => $request->input('lab_job_id', ''),
            'data_entry_status' => $request->input('lab_data_entry_status', 'all'),
            'review_status' => $request->input('lab_review_status', ''),
        ];
    }
}
