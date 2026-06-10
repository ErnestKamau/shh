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
            'logoSrc' => $this->resolveLogoAsDataUri(),
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

        // Try to get data from Test Request Form Instance if available
        $trfData = [];
        if ($form->testRequestFormInstance) {
            $trfData = $form->testRequestFormInstance->form_data ?? [];
        }

        // Use TRF data if available, otherwise fall back to existing form data
        $conformityRequest = 'not_requested';
        $managerCapability = 'has';
        $laboratoryManagerName = $form->manager_signer_name ?? 'Manager';

        if (!empty($trfData)) {
            $statement = $trfData['statement_of_conformity'] ?? '';
            if (is_array($statement)) {
                $isSequential = array_keys($statement) === range(0, count($statement) - 1);
                $statement = $isSequential ? implode(', ', $statement) : implode(', ', array_keys(array_filter($statement)));
            }
            $conformityRequest = stripos((string)$statement, 'YES') !== false ? 'requested' : 'not_requested';
            $laboratoryManagerName = $trfData['lab_received_by'] ?? $trfData['manager_name'] ?? $laboratoryManagerName;
        }

        return [
            'customer_name' => $form->customer_name,
            'customer_address' => $customerAddress,
            'customer_email' => $customerEmail,
            'number_of_samples' => $form->number_of_samples,
            'mode_of_work' => $form->mode_of_work,
            'type_of_samples' => $batch->sample_type?->name ?? '',
            'date_of_sampling' => $form->date_of_sampling ?? $submissionRequest?->date_of_seizure ?? $trfData['sampling_date'] ?? $trfData['collection_date'] ?? '',
            'date' => $form->request_date ?? now()->format('Y-m-d'),
            'tel' => $customerTel,
            'deviation_answer' => 'No',
            'customer_certification_text' => $form->customer_certification_text,
            'customer_name_certified' => $form->customer_signer_name,
            'customer_signature' => $form->customer_signature,
            'customer_date' => $form->customer_signed_at ? \Carbon\Carbon::parse($form->customer_signed_at)->format('Y-m-d') : '',
            'conformity_request' => $conformityRequest,
            'manager_capability' => $managerCapability,
            'laboratory_name' => config('app.name'),
            'laboratory_manager_name' => $laboratoryManagerName,
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

    private function resolveAttachmentTypeId(): ?string
    {
        $label = 'Analysis Acceptance Form';

        $existingId = SystemConfiguration::query()->where('key', 'attachment_type')
            ->where('value', $label)
            ->value('id');

        if ($existingId !== null) {
            return $existingId;
        }

        $existingType = SystemConfiguration::query()->where('key', 'attachment_type')->whereNotNull('configuration_type_id')->first();
        if (! $existingType) {
            return null;
        }

        $newConfig = new SystemConfiguration();
        $newConfig->key = 'attachment_type';
        $newConfig->value = $label;
        $newConfig->configuration_type_id = $existingType->configuration_type_id;
        $newConfig->save();

        return $newConfig->id;
    }

    /**
     * Resolve the active company's logo to a base64 data URI.
     */
    private function resolveLogoAsDataUri(): string
    {
        $company = getActiveCompany();

        if ($company && !empty($company->logo)) {
            $path = $company->logo;

            // Strip URL prefix if stored as a full URL
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $path = parse_url($path, PHP_URL_PATH) ?? $path;
            }
            $path = ltrim($path, '/');
            $filename = basename($path);

            if ($filename !== '') {
                // 1) Public storage disk (storage/app/public/...)
                $relative = preg_replace('#^storage/#', '', $path);
                if ($relative !== $path) {
                    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
                    if (file_exists($fullPath)) {
                        return $this->imagePathToDataUri($fullPath);
                    }
                }

                // 2) App convention: storage/app/companies/<filename>
                $fullPath = storage_path('app/companies/' . $filename);
                if (file_exists($fullPath)) {
                    return $this->imagePathToDataUri($fullPath);
                }

                // 3) The company logo field may also be a public/ relative path
                if (file_exists(public_path($path))) {
                    return $this->imagePathToDataUri(public_path($path));
                }

                // 4) public/ ltrim fallback
                if (file_exists(public_path(ltrim($path, '/')))) {
                    return $this->imagePathToDataUri(public_path(ltrim($path, '/')));
                }
            }
        }

        // Fallback: default logo
        $defaultLogo = public_path('images/logo.png');
        if (file_exists($defaultLogo)) {
            return $this->imagePathToDataUri($defaultLogo);
        }

        $defaultReportLogo = public_path('images/logo-report.png');
        if (file_exists($defaultReportLogo)) {
            return $this->imagePathToDataUri($defaultReportLogo);
        }

        return '';
    }

    /**
     * Convert an image path to a base64 data URI.
     */
    private function imagePathToDataUri(string $absolutePath): string
    {
        if ($absolutePath === '' || !is_readable($absolutePath)) {
            return '';
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return '';
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'        => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'        => 'image/gif',
            'webp'       => 'image/webp',
            'svg'        => 'image/svg+xml',
            default      => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }
}

