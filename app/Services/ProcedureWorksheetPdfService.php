<?php

namespace App\Services;

use App\BatchAttachment;
use App\CapturedResult;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\Procedures\ProcedureConfigField;
use App\SampleHeader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcedureWorksheetPdfService
{
    /**
     * Generate a PDF snapshot of a procedure worksheet and attach it to the batch.
     *
     * @param  ProcedureWorksheet                 $worksheet
     * @param  SampleHeader                       $batch
     * @param  \Illuminate\Support\Collection<int, CapturedResult> $capturedResults
     * @param  \Illuminate\Support\Collection<int, ProcedureWorksheetStep> $steps
     * @param  \Illuminate\Support\Collection<int, ProcedureConfigField>   $configFields
     * @param  array<int, array<int, mixed>>      $inputValues       [captured_result_id => [step_id => value]]
     * @param  array<int, array<int, mixed>>      $configFieldValues [captured_result_id => [config_field_id => value]]
     * @param  int                                $attachmentTypeId
     */
    public function attachProcedureWorksheetPdfToBatch(
        ProcedureWorksheet $worksheet,
        SampleHeader $batch,
        Collection $capturedResults,
        Collection $steps,
        Collection $configFields,
        array $inputValues,
        array $configFieldValues,
        int $attachmentTypeId
    ): void {
        try {
            if ($capturedResults->isEmpty()) {
                return;
            }

            $safeBatchCode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $batch->batch_code);
            $filename = 'procedure-worksheet-' . $worksheet->id . '-batch-' . $safeBatchCode . '.pdf';

            // Avoid creating duplicate attachments if a file with this name already exists for this batch.
            $existing = BatchAttachment::where('batch_id', $batch->id)
                ->where('attachment_type', $attachmentTypeId)
                ->where('attachment_url', '/storage/batch-attachments/' . urlencode($filename))
                ->first();

            if ($existing) {
                return;
            }

            $viewData = [
                'worksheet'         => $worksheet,
                'batch'             => $batch,
                'capturedResults'   => $capturedResults,
                'steps'             => $steps,
                'configFields'      => $configFields,
                'inputValues'       => $inputValues,
                'configFieldValues' => $configFieldValues,
            ];

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView('worksheets.print.procedure-worksheet', $viewData);
            $pdfContent = $pdf->output();

            Storage::put('batch-attachments/' . $filename, $pdfContent);

            $attachmentUrl = '/storage/batch-attachments/' . urlencode($filename);

            $attachment = new BatchAttachment();
            $attachment->batch_id = $batch->id;
            $attachment->uploaded_by = auth()->id();
            $attachment->title = $worksheet->name ?: 'Procedure Worksheet';
            $attachment->attachment_type = $attachmentTypeId;
            $attachment->attachment_url = $attachmentUrl;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = 0;
            $attachment->save();

            Log::info('Procedure worksheet PDF attached to batch', [
                'batch_id' => $batch->id,
                'worksheet_id' => $worksheet->id,
                'attachment_id' => $attachment->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to attach procedure worksheet PDF to batch', [
                'batch_id' => $batch->id,
                'worksheet_id' => $worksheet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

