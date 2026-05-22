<?php

namespace App\Services\Sampleworkflow;

use App\BatchAttachment;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AcceptanceFormPdfService
{
    public const ATTACHMENT_TITLE = 'Laboratory Analysis Acceptance Form (GCLA/F/63)';

    public function generatePdfAndStoreAttachment(AnalysisAcceptanceForm $form): ?string
    {
        if (!$form->sample_header_id) {
            return null;
        }

        $batch = SampleHeader::find((string) $form->sample_header_id);
        if (!$batch) {
            return null;
        }

        $pdfFilename = 'laboratory-analysis-acceptance-batch-' . $batch->id . '.pdf';
        $pdfStoragePath = 'batch-attachments/' . $pdfFilename;
        $pdfPublicUrl = '/storage/batch-attachments/' . urlencode($pdfFilename);

        $formData = $this->hydrateFormData($form, $batch);
        $parameters = $this->hydrateParameters($form);
        $acceptedTotal = collect($parameters)->where('selected', true)->sum('price');

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('batch.attachments.laboratory-analysis-acceptance-pdf', [
            'batch' => $batch,
            'form' => $formData,
            'requestedParameters' => $parameters,
            'acceptedParameters' => collect($parameters)->where('selected', true)->all(),
            'rejectedParameters' => collect($parameters)->where('selected', false)->all(),
            'acceptedTotal' => $acceptedTotal,
        ]);

        Storage::disk('public')->put($pdfStoragePath, $pdf->output());

        $attachmentTypeId = $this->resolveAttachmentTypeId();

        $attachment = BatchAttachment::where('batch_id', $batch->id)
            ->where('title', self::ATTACHMENT_TITLE)
            ->orderByDesc('created_at')
            ->first();

        if (! $attachment) {
            $attachment = new BatchAttachment();
            $attachment->batch_id = $batch->id;
            $attachment->uploaded_by = Auth::id() ?? $form->created_by;
            $attachment->title = self::ATTACHMENT_TITLE;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = 0;
        }

        $attachment->attachment_type = $attachmentTypeId;
        $attachment->attachment_url = $pdfPublicUrl;
        $attachment->save();

        return $pdfPublicUrl;
    }

    private function hydrateFormData(AnalysisAcceptanceForm $form, SampleHeader $batch): array
    {
        $submissionRequest = null;
        if ($form->sample_submission_request_id) {
            $submissionRequest = \App\Models\SampleSubmissionRequest::with(['customer', 'contact'])->find($form->sample_submission_request_id);
        }

        $customerAddress = $submissionRequest?->customer?->postal_address ?? $submissionRequest?->physical_address ?? '';
        $customerEmail = $submissionRequest?->contact?->email ?? $submissionRequest?->email ?? $submissionRequest?->customer?->email ?? '';
        $customerTel = $submissionRequest?->mobile_telephone_no ?? $submissionRequest?->office_telephone_no ?? $submissionRequest?->customer?->telephone1 ?? '';

        return [
            'customer_name' => $form->customer_name,
            'customer_address' => $customerAddress,
            'customer_email' => $customerEmail,
            'number_of_samples' => $form->number_of_samples,
            'mode_of_work' => $form->mode_of_work,
            'type_of_samples' => $batch->sample_type?->name ?? '',
            'date_of_sampling' => $form->date_of_sampling ?? $submissionRequest?->date_of_seizure,
            'date' => $form->request_date ?? now()->format('Y-m-d'),
            'tel' => $customerTel,
            'deviation_answer' => 'No', // Defaulting as this might not be explicitly tracked on the new model
            'customer_certification_text' => $form->customer_certification_text,
            'customer_name_certified' => $form->customer_signer_name,
            'customer_signature' => $form->customer_signature,
            'customer_date' => $form->customer_signed_at ? \Carbon\Carbon::parse($form->customer_signed_at)->format('Y-m-d') : '',
            'conformity_request' => 'not_requested', // Assuming default, add field to model if needed
            'manager_capability' => 'has', // Assuming has capability since manager is signing
            'laboratory_name' => config('app.name'),
            'laboratory_manager_name' => $form->manager_signer_name,
            'manager_signature' => $form->manager_signature,
            'manager_date' => $form->manager_signed_at ? \Carbon\Carbon::parse($form->manager_signed_at)->format('Y-m-d') : '',
        ];
    }

    private function hydrateParameters(AnalysisAcceptanceForm $form): array
    {
        $lines = [];
        $form->loadMissing('lines');
        
        foreach ($form->lines as $line) {
            $lines[] = [
                'label' => $line->parameter_label,
                'selected' => $line->is_approved,
                'price' => $line->unit_amount * $line->number_of_samples, // Assuming unit_amount is per sample
            ];
        }

        return $lines;
    }

    private function resolveAttachmentTypeId(): ?int
    {
        $label = 'Analysis Acceptance Form';

        $existingId = SystemConfiguration::query()->where('key', 'attachment_type')
            ->where('value', $label)
            ->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        $typeConfig = SystemConfiguration::query()->where('key', 'attachment_type_config_id')->first();
        if (! $typeConfig) {
            return null;
        }

        $newConfig = new SystemConfiguration();
        $newConfig->key = 'attachment_type';
        $newConfig->value = $label;
        $newConfig->configuration_type_id = $typeConfig->id;
        $newConfig->save();

        return (int) $newConfig->id;
    }
}
