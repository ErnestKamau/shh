<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Exports\Sampleworkflow\BatchResultsTemplateExport;
use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\SampleHeader;
use App\Services\ResultRemarkService;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

final class BatchResultsExcelImportService
{
    public function __construct(
        private readonly CapturedResultCaptureService $captureService,
        private readonly LabSectionResultAccess $labSectionAccess,
        private readonly LabSectionWorksheetAccess $worksheetAccess,
        private readonly RequestTestExportDataService $dataService,
        private readonly ResultRemarkService $remarkService,
        private readonly SampleWorkflowEventRecorder $eventRecorder,
    ) {}

    public function downloadTemplate(SampleHeader $batch)
    {
        $payload = $this->dataService->buildFromBatch($batch, includeResultColumn: true);
        $filename = 'batch-results-'.$this->safeFilename((string) ($batch->batch_code ?? $batch->id)).'.xlsx';

        return $this->downloadTemplateFromIntegrityRows($payload['flat_rows'], $filename);
    }

    /**
     * @param  list<array<string, mixed>>  $flatRows
     */
    public function downloadTemplateFromIntegrityRows(array $flatRows, string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new BatchResultsTemplateExport($flatRows, 'Results'),
            $filename
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function storeLabSectionWorksheetExcel(array $payload, string $worksheetNumber): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $worksheetNumber) ?? 'worksheet';
        $path = 'request-test-worksheets/section-'.trim($safe, '-').'.xlsx';
        Storage::disk('public')->makeDirectory(dirname($path));

        Excel::store(
            new BatchResultsTemplateExport($payload['flat_rows'] ?? [], 'Results', includeWorksheetNumber: true),
            $path,
            'public',
        );

        return $path;
    }

    /**
     * Import free-text results. Rejects the entire upload when any row fails validation.
     *
     * @return array{updated: int, skipped: int}
     *
     * @throws ValidationException
     */
    public function import(SampleHeader $batch, UploadedFile $file, ?User $actingUser): array
    {
        $sheets = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray
        {
            public function array(array $array): array
            {
                return $array;
            }
        }, $file);

        $rows = is_array($sheets[0] ?? null) ? $sheets[0] : [];
        if ($rows === []) {
            throw ValidationException::withMessages([
                'importFile' => 'The Excel file is empty.',
            ]);
        }

        $headerRow = array_map(static fn ($value): string => trim((string) $value), array_shift($rows) ?? []);
        $expected = [
            BatchResultsTemplateExport::HEADING_ROW_KEY,
            BatchResultsTemplateExport::HEADING_BATCH_CODE,
            BatchResultsTemplateExport::HEADING_LAB_SECTION,
            BatchResultsTemplateExport::HEADING_SAMPLE,
            BatchResultsTemplateExport::HEADING_TEST,
            BatchResultsTemplateExport::HEADING_ANALYSTS,
            BatchResultsTemplateExport::HEADING_RESULT,
        ];
        $expectedWithWorksheet = [
            BatchResultsTemplateExport::HEADING_ROW_KEY,
            BatchResultsTemplateExport::HEADING_WORKSHEET_NUMBER,
            BatchResultsTemplateExport::HEADING_BATCH_CODE,
            BatchResultsTemplateExport::HEADING_LAB_SECTION,
            BatchResultsTemplateExport::HEADING_SAMPLE,
            BatchResultsTemplateExport::HEADING_TEST,
            BatchResultsTemplateExport::HEADING_ANALYSTS,
            BatchResultsTemplateExport::HEADING_RESULT,
        ];

        // Accept the previous template headers so in-flight downloads still import.
        $legacyExpected = [
            'Captured Result ID',
            'Batch ID',
            BatchResultsTemplateExport::HEADING_LAB_SECTION,
            BatchResultsTemplateExport::HEADING_SAMPLE,
            BatchResultsTemplateExport::HEADING_TEST,
            BatchResultsTemplateExport::HEADING_ANALYSTS,
            BatchResultsTemplateExport::HEADING_RESULT,
        ];

        $isLegacy = $headerRow === $legacyExpected;
        $hasWorksheetColumn = $headerRow === $expectedWithWorksheet;
        if ($headerRow !== $expected && ! $isLegacy && ! $hasWorksheetColumn) {
            throw ValidationException::withMessages([
                'importFile' => 'Invalid Excel template. Download a fresh template from this batch and try again.',
            ]);
        }

        $columnOffset = $hasWorksheetColumn ? 1 : 0;
        $batchId = (string) $batch->id;
        $batchCode = trim((string) ($batch->batch_code ?? ''));
        $errors = [];
        $worksheetNumbers = [];

        foreach ($rows as $row) {
            if (! $hasWorksheetColumn) {
                break;
            }

            $worksheetNumber = trim((string) ($row[1] ?? ''));
            if ($worksheetNumber !== '') {
                $worksheetNumbers[$worksheetNumber] = true;
            }
        }

        $worksheetContext = null;
        if ($hasWorksheetColumn && $worksheetNumbers !== []) {
            if (count($worksheetNumbers) > 1) {
                $errors[] = 'Import one lab section worksheet at a time. The file contains multiple worksheet numbers.';
            } else {
                $worksheetNumber = array_key_first($worksheetNumbers);
                $worksheetContext = LabSectionWorksheet::query()
                    ->with('labSection')
                    ->where('worksheet_number', $worksheetNumber)
                    ->where('sample_header_id', $batchId)
                    ->first();

                if ($worksheetContext === null) {
                    $errors[] = "Worksheet {$worksheetNumber} was not found for this batch.";
                } elseif (! $this->worksheetAccess->canImportAgainstWorksheet($actingUser, $worksheetContext)) {
                    $errors[] = $this->worksheetAccess->denyImportMessage($actingUser);
                }
            }
        }

        /** @var list<array{captured: CapturedResult, result: string, excel_row: int}> $pending */
        $pending = [];
        $seenIds = [];
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;
            $capturedResultId = trim((string) ($row[0] ?? ''));
            $worksheetNumber = $hasWorksheetColumn ? trim((string) ($row[1] ?? '')) : '';
            $rowBatchRef = trim((string) ($row[1 + $columnOffset] ?? ''));
            $labSectionLabel = trim((string) ($row[2 + $columnOffset] ?? ''));
            $sampleLabel = trim((string) ($row[3 + $columnOffset] ?? ''));
            $testLabel = trim((string) ($row[4 + $columnOffset] ?? ''));
            $result = trim((string) ($row[6 + $columnOffset] ?? ''));

            if ($capturedResultId === '' && $rowBatchRef === '' && $result === ''
                && $labSectionLabel === ''
                && $sampleLabel === ''
                && $testLabel === '') {
                continue;
            }

            if ($capturedResultId === '') {
                $errors[] = "Row {$excelRow}: Missing row key for {$sampleLabel} / {$testLabel}. Re-download the template.";
                continue;
            }

            if (isset($seenIds[$capturedResultId])) {
                $errors[] = "Row {$excelRow}: Duplicate row for {$sampleLabel} / {$testLabel}.";
                continue;
            }
            $seenIds[$capturedResultId] = true;

            if ($rowBatchRef !== '') {
                $batchMatches = $isLegacy
                    ? ($rowBatchRef === $batchId)
                    : ($rowBatchRef === $batchCode || $rowBatchRef === $batchId);

                if (! $batchMatches) {
                    $errors[] = "Row {$excelRow}: Batch code does not match this batch.";
                    continue;
                }
            }

            if ($result === '') {
                $skipped++;
                continue;
            }

            $captured = $this->resolveCapturedResult($batchId, $capturedResultId, $worksheetContext);

            if (! $captured) {
                $label = trim($sampleLabel.' / '.$testLabel, ' /');
                $errors[] = "Row {$excelRow}: Could not match".($label !== '' ? " {$label}" : ' this row').' to a test on this batch.';
                continue;
            }

            if ($hasWorksheetColumn && $worksheetNumber !== '' && $worksheetContext !== null) {
                if (! $this->snapshotContainsCapturedResult($worksheetContext, $captured)) {
                    $label = trim($sampleLabel.' / '.$testLabel, ' /');
                    $errors[] = "Row {$excelRow}: {$label} is not on worksheet {$worksheetNumber}.";
                    continue;
                }
            }

            if (! $this->labSectionAccess->canEditCapturedResult($actingUser, $captured)) {
                $label = trim($sampleLabel.' / '.$testLabel, ' /');
                $errors[] = "Row {$excelRow}: You are not allowed to edit".($label !== '' ? " {$label}" : ' this result').'.';
                continue;
            }

            $pending[] = [
                'captured' => $captured,
                'result' => $result,
                'excel_row' => $excelRow,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'importFile' => $errors,
            ]);
        }

        if ($pending === []) {
            return ['updated' => 0, 'skipped' => $skipped];
        }

        $actingUserId = $actingUser?->id !== null ? (string) $actingUser->id : null;

        DB::transaction(function () use ($pending, $actingUserId, $worksheetContext, $batch, $actingUser): void {
            foreach ($pending as $item) {
                $attributes = ['result' => $item['result']];
                $remark = $this->remarkService->autoRemarkForCapturedResult($item['captured'], $item['result']);
                if ($remark !== null) {
                    $attributes['remark'] = $remark;
                }

                $this->captureService->applyOnSave(
                    $item['captured'],
                    $attributes,
                    $actingUserId,
                    $worksheetContext !== null ? 'Excel upload (section worksheet)' : 'Excel upload',
                );
            }

            $sampleDetailIds = collect($pending)
                ->map(static fn (array $item) => $item['captured']->sample_detail_id ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();
            app(StatementOfConformityService::class)->ensureForSampleIds($sampleDetailIds, $batch);

            if ($worksheetContext !== null) {
                $worksheetContext->forceFill([
                    'imported_at' => now(),
                    'status' => 'imported',
                ])->save();

                $this->eventRecorder->record(
                    subjectType: LabSectionWorksheet::class,
                    subjectId: (string) $worksheetContext->id,
                    eventType: 'worksheet_imported',
                    what: 'Lab section worksheet results imported',
                    how: 'Excel upload',
                    where: (string) ($worksheetContext->labSection?->name ?? 'Lab section'),
                    instanceId: $worksheetContext->submission_form_instance_id,
                    batchId: (string) $batch->id,
                    metadata: [
                        'worksheet_number' => $worksheetContext->worksheet_number,
                        'updated_rows' => count($pending),
                    ],
                    user: $actingUser,
                );
            }
        });

        return [
            'updated' => count($pending),
            'skipped' => $skipped,
        ];
    }

    private function resolveCapturedResult(string $batchId, string $rowKey, ?LabSectionWorksheet $worksheet): ?CapturedResult
    {
        $captured = CapturedResult::query()
            ->with(['sample', 'my_analyte'])
            ->whereKey($rowKey)
            ->where('sample_header_id', $batchId)
            ->first();

        if ($captured !== null) {
            return $captured;
        }

        if ($worksheet === null || ! is_array($worksheet->test_snapshot)) {
            return null;
        }

        foreach ($worksheet->test_snapshot as $snapshotRow) {
            if (! is_array($snapshotRow)) {
                continue;
            }

            $snapshotKey = trim((string) ($snapshotRow['row_key'] ?? ''));
            $capturedResultId = trim((string) ($snapshotRow['captured_result_id'] ?? ''));

            if ($snapshotKey !== $rowKey || $capturedResultId === '') {
                continue;
            }

            return CapturedResult::query()
                ->with(['sample', 'my_analyte'])
                ->whereKey($capturedResultId)
                ->where('sample_header_id', $batchId)
                ->first();
        }

        return null;
    }

    private function snapshotContainsCapturedResult(LabSectionWorksheet $worksheet, CapturedResult $captured): bool
    {
        $capturedId = (string) $captured->id;
        $elementId = trim((string) ($captured->analysis_element_id ?? ''));

        foreach (is_array($worksheet->test_snapshot) ? $worksheet->test_snapshot : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (trim((string) ($row['captured_result_id'] ?? '')) === $capturedId) {
                return true;
            }

            if ($elementId !== '' && trim((string) ($row['element_id'] ?? '')) === $elementId) {
                return true;
            }
        }

        return false;
    }

    private function safeFilename(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? 'export';
        $safe = trim($safe, '-');

        return $safe !== '' ? $safe : 'export';
    }
}
