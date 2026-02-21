<?php

namespace App\Services;

use App\BatchAttachment;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubmissionFormPdfService
{
    /**
     * Generate a PDF of the submission form instance and attach it to the batch as a batch attachment.
     * Uses the form's print template (e.g. submission-forms.print.default). On failure, logs and does not throw.
     */
    public function attachSubmissionFormPdfToBatch(
        SubmissionFormInstance $instance,
        SampleHeader $sampleHeader,
        int $attachmentTypeId
    ): void {
        try {
            $submissionForm = $instance->submissionForm;

            $instance->load('submittedBy', 'reviewedBy');

            $submissionForm->load([
                'sections.elementHolders.elements' => function ($query) {
                    $query->orderBy('sort_order');
                }
            ]);

            $templateName = $submissionForm->getPrintTemplateName();

            if (! view()->exists($templateName)) {
                $templateName = 'submission-forms.print.default';
            }

            $customTemplates = ['submission-forms.print.microbiology', 'submission-forms.print.serology'];
            if (in_array($templateName, $customTemplates, true)) {
                $existingValues = $instance->getSubmittedFormData();
            } else {
                $existingValues = $instance->values()->with('element')->get()->map(function ($v) {
                    return (object) ['element_id' => $v->submission_form_element_id, 'value' => $v->value];
                });
            }

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView($templateName, compact('submissionForm', 'instance', 'existingValues'));
            $pdfContent = $pdf->output();

            $safeBatchCode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sampleHeader->batch_code);
            $filename = 'submission-form-' . $instance->id . '-batch-' . $safeBatchCode . '.pdf';
            Storage::put('batch-attachments/' . $filename, $pdfContent);

            $attachmentUrl = '/storage/batch-attachments/' . urlencode($filename);

            $attachment = new BatchAttachment();
            $attachment->batch_id = $sampleHeader->id;
            $attachment->uploaded_by = auth()->id();
            $attachment->title = $submissionForm->name;
            $attachment->attachment_type = $attachmentTypeId;
            $attachment->attachment_url = $attachmentUrl;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = 0;
            $attachment->save();

            Log::info('Submission form PDF attached to batch', [
                'batch_id' => $sampleHeader->id,
                'instance_id' => $instance->id,
                'template' => $templateName,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to attach submission form PDF to batch', [
                'instance_id' => $instance->id,
                'batch_id' => $sampleHeader->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
