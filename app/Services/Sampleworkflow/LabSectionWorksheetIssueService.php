<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\Models\Sampleworkflow\SampleWorkflowEvent;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisStage;
use App\Services\Lab\LabSystemNotificationService;
use App\User;
use Illuminate\Support\Facades\Storage;

final class LabSectionWorksheetIssueService
{
    public function __construct(
        private readonly LabSectionWorksheetSequenceService $sequenceService,
        private readonly RequestTestExportDataService $exportDataService,
        private readonly RequestTestWorksheetPdfService $pdfService,
        private readonly BatchResultsExcelImportService $excelService,
        private readonly SampleWorkflowEventRecorder $eventRecorder,
        private readonly LabSystemNotificationService $notificationService,
    ) {}

    /**
     * @param  list<string>  $labSectionIds
     * @param  list<array<string, mixed>>  $testRows
     * @param  array<string, string>  $labSectionNames
     * @param  array<string, string>  $analystNamesById
     * @return list<array{worksheet: LabSectionWorksheet, pdf_url: ?string, excel_download: bool}>
     */
    public function issueForIntegrityCheck(
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $testRows,
        array $labSectionIds,
        array $labSectionNames,
        array $analystNamesById,
        User $actingUser,
        string $mode,
    ): array {
        $issued = [];

        foreach ($labSectionIds as $labSectionId) {
            $labSectionId = trim((string) $labSectionId);
            if ($labSectionId === '') {
                continue;
            }

            $section = SampleAnalysisStage::query()->find($labSectionId);
            if ($section === null) {
                continue;
            }

            $numberMeta = $this->sequenceService->nextNumber($section);
            $payload = $this->exportDataService->buildFromIntegrityRowsForSection(
                $instance,
                $enquiry,
                $testRows,
                $labSectionId,
                $labSectionNames,
                $analystNamesById,
                $numberMeta['worksheet_number'],
            );

            $snapshot = $this->buildTestSnapshot($payload['flat_rows']);
            $analystIds = $this->collectAnalystIds($testRows, $labSectionId);

            $pdfPath = null;
            $excelPath = null;

            if (in_array($mode, ['pdf', 'notify'], true)) {
                $pdfPath = $this->pdfService->storeLabSectionWorksheetPdf($payload, $instance);
            }

            if ($mode === 'excel') {
                $excelPath = $this->excelService->storeLabSectionWorksheetExcel($payload, $numberMeta['worksheet_number']);
            }

            $worksheet = LabSectionWorksheet::query()->create([
                'worksheet_number' => $numberMeta['worksheet_number'],
                'lab_section_id' => (string) $section->id,
                'calendar_year' => $numberMeta['calendar_year'],
                'sequence' => $numberMeta['sequence'],
                'context' => 'integrity',
                'submission_form_instance_id' => (string) $instance->id,
                'generated_by' => (string) $actingUser->id,
                'assigned_analyst_ids' => $analystIds,
                'test_snapshot' => $snapshot,
                'pdf_path' => $pdfPath,
                'excel_path' => $excelPath,
                'issued_at' => now(),
                'status' => 'issued',
            ]);

            $this->eventRecorder->record(
                subjectType: SubmissionFormInstance::class,
                subjectId: (string) $instance->id,
                eventType: 'worksheet_issued',
                what: 'Lab section worksheet issued',
                how: $mode === 'excel' ? 'Excel download' : 'PDF generation',
                where: (string) ($labSectionNames[$labSectionId] ?? $section->name),
                instanceId: (string) $instance->id,
                metadata: [
                    'worksheet_number' => $worksheet->worksheet_number,
                    'lab_section_id' => $labSectionId,
                    'test_count' => count($snapshot),
                ],
                user: $actingUser,
            );

            if ($mode === 'notify') {
                $this->notifyAssignedAnalysts($worksheet, $actingUser, $labSectionNames[$labSectionId] ?? $section->name);
            }

            $issued[] = [
                'worksheet' => $worksheet,
                'pdf_url' => $pdfPath !== null
                    ? Storage::disk('public')->url($pdfPath).'?v='.now()->timestamp
                    : null,
                'excel_url' => $excelPath !== null
                    ? Storage::disk('public')->url($excelPath).'?v='.now()->timestamp
                    : null,
                'excel_download' => $mode === 'excel',
            ];
        }

        return $issued;
    }

    /**
     * @param  list<array<string, mixed>>  $flatRows
     * @return list<array<string, mixed>>
     */
    private function buildTestSnapshot(array $flatRows): array
    {
        return array_values(array_map(static fn (array $row): array => [
            'row_key' => (string) ($row['row_key'] ?? ''),
            'element_id' => (string) ($row['element_id'] ?? ''),
            'config_id' => (string) ($row['config_id'] ?? ''),
            'test_label' => (string) ($row['test_label'] ?? ''),
            'sample_label' => (string) ($row['sample_label'] ?? ''),
            'lab_section_id' => (string) ($row['lab_section_id'] ?? ''),
        ], $flatRows));
    }

    /**
     * @param  list<array<string, mixed>>  $testRows
     * @return list<string>
     */
    private function collectAnalystIds(array $testRows, string $labSectionId): array
    {
        $ids = [];
        foreach ($testRows as $row) {
            if (! in_array($labSectionId, array_map('strval', $row['lab_section_ids'] ?? []), true)) {
                continue;
            }
            $bySection = is_array($row['analysts_by_lab_section'] ?? null) ? $row['analysts_by_lab_section'] : [];
            foreach ((array) ($bySection[$labSectionId] ?? []) as $analystId) {
                $analystId = trim((string) $analystId);
                if ($analystId !== '') {
                    $ids[$analystId] = true;
                }
            }
        }

        return array_keys($ids);
    }

    private function notifyAssignedAnalysts(LabSectionWorksheet $worksheet, User $actingUser, string $sectionName): void
    {
        $analystIds = is_array($worksheet->assigned_analyst_ids) ? $worksheet->assigned_analyst_ids : [];
        if ($analystIds === []) {
            return;
        }

        $users = User::query()->whereIn('id', $analystIds)->get();
        $pdfUrl = $worksheet->pdf_path !== null
            ? Storage::disk('public')->url($worksheet->pdf_path)
            : null;

        $this->notificationService->notifyUsers(
            $users,
            LabSystemNotificationService::TYPE_LAB_SECTION_WORKSHEET,
            $worksheet->worksheet_number.' — '.$sectionName,
            $actingUser->name.' issued a lab worksheet for '.$sectionName.'.',
            LabSectionWorksheet::class,
            (string) $worksheet->id,
            [
                'worksheet_number' => $worksheet->worksheet_number,
                'lab_section_id' => (string) $worksheet->lab_section_id,
                'pdf_url' => $pdfUrl,
            ],
        );
    }
}
