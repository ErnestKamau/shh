<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\Services\Sampleworkflow\BatchResultsExcelImportService;
use App\Services\Sampleworkflow\RequestTestWorksheetPdfService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RequestTestExportController extends Controller
{
    public function viewIntegrityPdf(SubmissionFormInstance $instance, RequestTestWorksheetPdfService $pdfService)
    {
        return $pdfService->viewStoredIntegrityPdf($instance);
    }

    public function downloadPdf(SampleHeader $batch, RequestTestWorksheetPdfService $pdfService)
    {
        $this->assertBatchExportable($batch);

        return $pdfService->viewStoredBatchPdf($batch, regenerate: true);
    }

    public function printPdf(SampleHeader $batch, RequestTestWorksheetPdfService $pdfService)
    {
        $this->assertBatchExportable($batch);

        return $pdfService->viewStoredBatchPdf($batch, regenerate: true);
    }

    public function downloadExcel(SampleHeader $batch, BatchResultsExcelImportService $importService)
    {
        $this->assertBatchExportable($batch);

        return $importService->downloadTemplate($batch);
    }

    public function importExcel(
        SampleHeader $batch,
        Request $request,
        BatchResultsExcelImportService $importService,
    ) {
        $this->assertBatchExportable($batch);

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'import_file.required' => 'Please choose an Excel file to upload.',
            'import_file.mimes' => 'The upload must be an Excel file (.xlsx or .xls).',
        ]);

        try {
            $result = $importService->import(
                $batch,
                $request->file('import_file'),
                $request->user(),
            );
        } catch (ValidationException $exception) {
            $messages = $exception->errors()['importFile'] ?? $exception->errors()['import_file'] ?? [];
            if (! is_array($messages)) {
                $messages = [(string) $messages];
            }
            $message = implode(' ', array_map('strval', $messages));

            return redirect()
                ->back()
                ->with('error', $message !== '' ? $message : 'The Excel import was rejected.');
        }

        $updated = (int) ($result['updated'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? 0);

        if ($updated === 0) {
            return redirect()
                ->back()
                ->with('warning', 'No results were imported. Fill the Result column and upload again. Skipped blank rows: '.$skipped.'.');
        }

        return redirect()
            ->back()
            ->with('success', "Imported {$updated} result(s). Blank rows skipped: {$skipped}.");
    }

    private function assertBatchExportable(SampleHeader $batch): void
    {
        $allowed = [
            'Samples In Lab',
            'Sample Verification',
            'Sample Approval',
            'Reports In Payment',
            'Reports for Collection',
        ];

        if (! in_array((string) $batch->status, $allowed, true)
            && ! in_array((string) ($batch->prelim_batch_status ?? ''), ['Sample Verification', 'Sample Approval'], true)
        ) {
            abort(403, 'Request test exports are available once the batch is in Samples In Lab.');
        }
    }
}
