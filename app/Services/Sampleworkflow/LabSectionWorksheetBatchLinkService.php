<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\Models\Sampleworkflow\SampleWorkflowEvent;
use App\Models\SubmissionFormInstance;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Facades\DB;

final class LabSectionWorksheetBatchLinkService
{
    public function __construct(
        private readonly BatchResultsExcelImportService $excelImportService,
        private readonly RequestTestExportDataService $exportDataService,
        private readonly RequestTestWorksheetPdfService $pdfService,
    ) {}

    /**
     * Attach integrity-check worksheets to a newly created batch and refresh row snapshots.
     */
    public function linkInstanceWorksheetsToBatch(
        SubmissionFormInstance|string $instance,
        SampleHeader|string $batch,
    ): int {
        $instanceId = $instance instanceof SubmissionFormInstance ? (string) $instance->id : trim($instance);
        $batchModel = $batch instanceof SampleHeader
            ? $batch
            : SampleHeader::query()->findOrFail($batch);
        $batchId = (string) $batchModel->id;

        if ($instanceId === '') {
            return 0;
        }

        $worksheets = LabSectionWorksheet::query()
            ->where('submission_form_instance_id', $instanceId)
            ->where(function ($query) use ($batchId): void {
                $query->whereNull('sample_header_id')
                    ->orWhere('sample_header_id', $batchId);
            })
            ->get();

        if ($worksheets->isEmpty()) {
            $this->attachWorkflowEventsToBatch($instanceId, $batchId);

            return 0;
        }

        $configSampleMap = $this->configIdToSampleDetailMap($instanceId, $batchId);

        $linked = 0;
        DB::transaction(function () use ($worksheets, $batchModel, $batchId, $configSampleMap, $instanceId, &$linked): void {
            foreach ($worksheets as $worksheet) {
                $snapshot = $this->refreshSnapshotForBatch(
                    $worksheet,
                    $batchId,
                    $configSampleMap,
                );

                $worksheet->sample_header_id = $batchId;
                $worksheet->test_snapshot = $snapshot;
                $worksheet->save();

                $this->regenerateStoredExports($worksheet, $batchModel, $snapshot);
                $linked++;
            }

            $this->attachWorkflowEventsToBatch($instanceId, $batchId);
        });

        return $linked;
    }

    public function attachWorkflowEventsToBatch(string $instanceId, string $batchId): void
    {
        if ($instanceId === '' || $batchId === '') {
            return;
        }

        SampleWorkflowEvent::query()
            ->where('submission_form_instance_id', $instanceId)
            ->whereNull('sample_header_id')
            ->update(['sample_header_id' => $batchId]);
    }

    /**
     * @return array<string, string> config_id => sample_detail_id
     */
    private function configIdToSampleDetailMap(string $instanceId, string $batchId): array
    {
        $instance = SubmissionFormInstance::query()->find($instanceId);
        if ($instance === null) {
            return [];
        }

        $enquiry = $instance->sampleSubmissionRequest;
        if ($enquiry === null) {
            $enquiry = SampleSubmissionRequest::query()
                ->where('submission_form_instance_id', $instanceId)
                ->first();
        }

        if ($enquiry === null) {
            return [];
        }

        $configs = app(SampleIntegrityCheckService::class)->resolveConfigs($enquiry, $instance);
        $sampleDetails = SampleDetails::query()
            ->where('sample_header_id', $batchId)
            ->orderBy('id')
            ->get(['id', 'customer_sample_id', 'sample_code']);

        $map = [];
        foreach ($configs as $index => $config) {
            $configId = trim((string) ($config['id'] ?? ''));
            if ($configId === '') {
                continue;
            }

            $customerSampleId = trim((string) ($config['customer_sample_id'] ?? ''));
            $matched = null;

            if ($customerSampleId !== '') {
                $matched = $sampleDetails->first(
                    fn (SampleDetails $detail): bool => trim((string) ($detail->customer_sample_id ?? '')) === $customerSampleId
                );
            }

            if ($matched === null && isset($sampleDetails[$index])) {
                $matched = $sampleDetails[$index];
            }

            if ($matched !== null) {
                $map[$configId] = (string) $matched->id;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $configSampleMap
     * @return list<array<string, mixed>>
     */
    private function refreshSnapshotForBatch(
        LabSectionWorksheet $worksheet,
        string $batchId,
        array $configSampleMap,
    ): array {
        $sectionId = trim((string) ($worksheet->lab_section_id ?? ''));
        $existing = is_array($worksheet->test_snapshot) ? $worksheet->test_snapshot : [];

        return array_values(array_map(function (array $row) use ($batchId, $sectionId, $configSampleMap): array {
            $elementId = trim((string) ($row['element_id'] ?? ''));
            $configId = trim((string) ($row['config_id'] ?? ''));
            $sampleDetailId = $configSampleMap[$configId] ?? null;

            $captured = null;
            if ($elementId !== '') {
                $query = CapturedResult::query()
                    ->where('sample_header_id', $batchId)
                    ->where('analysis_element_id', $elementId);

                if ($sectionId !== '') {
                    $query->where('lab_section_id', $sectionId);
                }

                if ($sampleDetailId !== null) {
                    $query->where('sample_detail_id', $sampleDetailId);
                }

                $captured = $query->first();
            }

            if ($captured !== null) {
                $row['captured_result_id'] = (string) $captured->id;
            }

            return $row;
        }, $existing));
    }

    /**
     * @param  list<array<string, mixed>>  $snapshot
     */
    private function regenerateStoredExports(
        LabSectionWorksheet $worksheet,
        SampleHeader $batch,
        array $snapshot,
    ): void {
        $elementIds = array_values(array_unique(array_filter(array_map(
            static fn (array $row): string => trim((string) ($row['element_id'] ?? '')),
            $snapshot,
        ))));

        if ($elementIds === []) {
            return;
        }

        $payload = $this->exportDataService->buildFromBatch($batch, includeResultColumn: true);
        $sectionId = trim((string) ($worksheet->lab_section_id ?? ''));

        $flatRows = array_values(array_filter(
            $payload['flat_rows'] ?? [],
            static function (array $row) use ($sectionId, $elementIds): bool {
                if ($sectionId !== '' && trim((string) ($row['lab_section_id'] ?? '')) !== $sectionId) {
                    return false;
                }

                return in_array(trim((string) ($row['element_id'] ?? '')), $elementIds, true);
            },
        ));

        foreach ($flatRows as $index => $row) {
            $flatRows[$index]['worksheet_number'] = (string) $worksheet->worksheet_number;
        }

        if ($flatRows === []) {
            return;
        }

        $payload['flat_rows'] = $flatRows;
        $payload['worksheet_number'] = (string) $worksheet->worksheet_number;
        $payload['context'] = 'integrity_section';

        $instance = SubmissionFormInstance::query()->find($worksheet->submission_form_instance_id);

        if ($worksheet->pdf_path !== null && $worksheet->pdf_path !== '' && $instance !== null) {
            $worksheet->pdf_path = $this->pdfService->storeLabSectionWorksheetPdf($payload, $instance);
        }

        if ($worksheet->excel_path !== null && $worksheet->excel_path !== '') {
            $worksheet->excel_path = $this->excelImportService->storeLabSectionWorksheetExcel(
                $payload,
                (string) $worksheet->worksheet_number,
            );
        }

        $worksheet->save();
    }
}
