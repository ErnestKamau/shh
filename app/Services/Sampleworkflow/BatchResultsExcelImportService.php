<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Exports\Sampleworkflow\BatchResultsTemplateExport;
use App\SampleHeader;
use App\Services\ResultRemarkService;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

final class BatchResultsExcelImportService
{
    public function __construct(
        private readonly CapturedResultCaptureService $captureService,
        private readonly LabSectionResultAccess $labSectionAccess,
        private readonly RequestTestExportDataService $dataService,
        private readonly ResultRemarkService $remarkService,
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
        if ($headerRow !== $expected && ! $isLegacy) {
            throw ValidationException::withMessages([
                'importFile' => 'Invalid Excel template. Download a fresh template from this batch and try again.',
            ]);
        }

        $batchId = (string) $batch->id;
        $batchCode = trim((string) ($batch->batch_code ?? ''));
        $errors = [];
        /** @var list<array{captured: CapturedResult, result: string, excel_row: int}> $pending */
        $pending = [];
        $seenIds = [];
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;
            $capturedResultId = trim((string) ($row[0] ?? ''));
            $rowBatchRef = trim((string) ($row[1] ?? ''));
            $sampleLabel = trim((string) ($row[3] ?? ''));
            $testLabel = trim((string) ($row[4] ?? ''));
            $result = trim((string) ($row[6] ?? ''));

            if ($capturedResultId === '' && $rowBatchRef === '' && $result === ''
                && trim((string) ($row[2] ?? '')) === ''
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

            $captured = CapturedResult::query()
                ->with(['sample', 'my_analyte'])
                ->whereKey($capturedResultId)
                ->where('sample_header_id', $batchId)
                ->first();

            if (! $captured) {
                $label = trim($sampleLabel.' / '.$testLabel, ' /');
                $errors[] = "Row {$excelRow}: Could not match".($label !== '' ? " {$label}" : ' this row').' to a test on this batch.';
                continue;
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

        DB::transaction(function () use ($pending, $actingUserId): void {
            foreach ($pending as $item) {
                $attributes = ['result' => $item['result']];
                $remark = $this->remarkService->autoRemarkForCapturedResult($item['captured'], $item['result']);
                if ($remark !== null) {
                    $attributes['remark'] = $remark;
                }

                $this->captureService->applyOnSave(
                    $item['captured'],
                    $attributes,
                    $actingUserId
                );
            }
        });

        return [
            'updated' => count($pending),
            'skipped' => $skipped,
        ];
    }

    private function safeFilename(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? 'export';
        $safe = trim($safe, '-');

        return $safe !== '' ? $safe : 'export';
    }
}
