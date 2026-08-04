<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceAttachment;
use App\QuotationHeader;
use App\Services\Commercial\AmSpecQuotationPdfService;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SubmissionFormInstanceDocumentAttachmentService
{
    public const QUOTATION_TYPE = 'Quotation';

    public function attachTestRequestForm(
        SubmissionFormInstance $instance,
        ?string $userId = null,
        bool $regenerate = true,
    ): ?SubmissionFormInstanceAttachment {
        $pdfService = app(TestRequestFormPdfService::class);
        $storagePath = $pdfService->resolveStoragePath($instance);

        if ($regenerate || ! Storage::disk('public')->exists($storagePath)) {
            try {
                $pdfService->generateAndStore($instance->fresh(['values.element', 'submissionForm', 'crmCustomer']));
            } catch (\Throwable $exception) {
                Log::warning('Failed to attach test request form PDF to submission instance.', [
                    'instance_id' => $instance->id,
                    'error' => $exception->getMessage(),
                ]);

                return null;
            }
        }

        if (! Storage::disk('public')->exists($storagePath)) {
            return null;
        }

        return $this->upsert(
            $instance,
            TestRequestFormPdfService::ATTACHMENT_TITLE,
            'Report',
            $storagePath,
            $pdfService->resolveDisplayFilename($instance),
            'Test request form PDF generated automatically.',
            $userId,
        );
    }

    public function attachQuotation(
        SubmissionFormInstance $instance,
        QuotationHeader $quotation,
        ?string $userId = null,
    ): ?SubmissionFormInstanceAttachment {
        if (empty($quotation->upload_url)) {
            try {
                app(AmSpecQuotationPdfService::class)->generateAndStore($quotation->fresh() ?? $quotation);
                $quotation->refresh();
            } catch (\Throwable $exception) {
                Log::warning('Failed to generate quotation PDF for instance attachment.', [
                    'instance_id' => $instance->id,
                    'quotation_id' => $quotation->id,
                    'error' => $exception->getMessage(),
                ]);

                return null;
            }
        }

        $sourcePath = $this->resolveQuotationSourcePath($quotation);
        if ($sourcePath === null) {
            return null;
        }

        $folder = 'request-attachments/'.Str::slug($instance->form_number ?: $instance->id, '-');
        $filename = basename($sourcePath);
        $destinationPath = $folder.'/'.$filename;

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put($destinationPath, File::get($sourcePath));

        $heading = self::QUOTATION_TYPE;
        $quoteNumber = trim((string) ($quotation->quote_number ?? ''));
        if ($quoteNumber !== '') {
            $heading .= ' - '.$quoteNumber;
        }

        return $this->upsert(
            $instance,
            $heading,
            self::QUOTATION_TYPE,
            $destinationPath,
            $filename,
            'Quotation PDF attached when sent to customer.',
            $userId,
        );
    }

    private function resolveQuotationSourcePath(QuotationHeader $quotation): ?string
    {
        $uploadUrl = trim((string) ($quotation->upload_url ?? ''));
        if ($uploadUrl === '') {
            return null;
        }

        $relative = ltrim($uploadUrl, '/');
        $candidates = [
            storage_path('app/'.$relative),
            public_path($relative),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function upsert(
        SubmissionFormInstance $instance,
        string $heading,
        string $attachmentType,
        string $storagePath,
        string $originalName,
        ?string $description,
        ?string $userId,
    ): SubmissionFormInstanceAttachment {
        $existing = $instance->customAttachments()
            ->where('attachment_type', $attachmentType)
            ->where('attachment_heading', $heading)
            ->orderByDesc('created_at')
            ->first();

        if ($existing === null && $attachmentType === self::QUOTATION_TYPE) {
            $existing = $instance->customAttachments()
                ->where('attachment_type', self::QUOTATION_TYPE)
                ->orderByDesc('created_at')
                ->first();
        }

        if ($existing === null) {
            $existing = new SubmissionFormInstanceAttachment();
            $existing->submission_form_instance_id = $instance->id;
            $existing->uploaded_by = $this->resolveUploaderId($userId);
        }

        $existing->file_path = $storagePath;
        $existing->original_name = $originalName;
        $existing->attachment_type = $attachmentType;
        $existing->attachment_heading = $heading;
        $existing->description = $description;
        $existing->save();

        return $existing;
    }

    private function resolveUploaderId(?string $userId): ?string
    {
        foreach ([$userId, Auth::id()] as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            $normalized = (string) $candidate;
            if (Str::isUuid($normalized)) {
                return $normalized;
            }
        }

        // uploaded_by is nullable; automated attach (send/queue) must not fail the send path.
        return null;
    }
}
