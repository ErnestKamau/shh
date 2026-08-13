<?php

namespace App\Services\Sampleworkflow;

use App\BatchAttachment;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\QuotationHeader;
use App\SampleHeader;
use App\Services\Billing\QuotationReportService;
use App\Services\Commercial\AmSpecQuotationPdfService;
use App\Services\System\AttachmentTypeResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BatchWorkflowDocumentAttachmentService
{
    public const QUOTATION_TITLE = 'Quotation';

    public const SAMPLE_PHOTO_TYPE = 'Sample Photo';

    public const TEST_REPORT_TITLE = 'Test Report';

    public function attachForAcceptedBatch(SampleHeader $batch, ?string $userId = null): void
    {
        try {
            $this->attachQuotation($batch, $userId);
        } catch (\Throwable $e) {
            Log::warning('Failed to attach quotation to batch', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $this->attachTestRequestForm($batch, $userId);
        } catch (\Throwable $e) {
            Log::warning('Failed to attach test request form to batch', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  Collection<int, \App\SampleDetails>  $details
     */
    public function attachSamplePhotos(SampleHeader $batch, Collection $details, ?string $userId = null): void
    {
        $attachmentTypeId = app(AttachmentTypeResolver::class)->resolveOrCreateAttachmentTypeId(self::SAMPLE_PHOTO_TYPE);

        foreach ($details as $detail) {
            $photoPath = trim((string) ($detail->photo_url ?? ''));
            if ($photoPath === '') {
                continue;
            }

            try {
                $publicUrl = str_starts_with($photoPath, 'http')
                    ? $photoPath
                    : Storage::url($photoPath);

                $this->upsertBatchAttachment(
                    $batch,
                    self::SAMPLE_PHOTO_TYPE.' - '.($detail->sample_code ?? $detail->id),
                    $publicUrl,
                    $attachmentTypeId,
                    $userId,
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to attach sample photo to batch', [
                    'batch_id' => $batch->id,
                    'sample_detail_id' => $detail->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function attachQuotation(SampleHeader $batch, ?string $userId = null): ?string
    {
        $quotation = $this->resolveQuotationForBatch($batch);
        if ($quotation === null) {
            return null;
        }

        if (empty($quotation->upload_url)) {
            app(AmSpecQuotationPdfService::class)->generateAndStore($quotation->fresh() ?? $quotation);
            $quotation->refresh();
        }

        $attachmentUrl = $this->resolveQuotationPublicUrl($quotation, $batch);

        return $this->upsertBatchAttachment(
            $batch,
            self::QUOTATION_TITLE,
            $attachmentUrl,
            app(AttachmentTypeResolver::class)->resolveOrCreateAttachmentTypeId(self::QUOTATION_TITLE),
            $userId
        );
    }

    public function attachTestRequestForm(SampleHeader $batch, ?string $userId = null): ?string
    {
        $instance = $this->resolveSubmissionFormInstanceForBatch($batch);
        if ($instance === null) {
            return null;
        }

        $pdfService = app(TestRequestFormPdfService::class);
        $attachmentUrl = $pdfService->generateAndStore($instance);

        return $this->upsertBatchAttachment(
            $batch,
            TestRequestFormPdfService::ATTACHMENT_TITLE,
            $attachmentUrl,
            app(AttachmentTypeResolver::class)->resolveOrCreateAttachmentTypeId(TestRequestFormPdfService::ATTACHMENT_TITLE),
            $userId
        );
    }

    /**
     * Upsert the generated Test Request Report PDF onto the batch Attachments tab.
     */
    public function attachTestReport(
        SampleHeader $batch,
        ?string $reportUrl = null,
        ?string $userId = null,
    ): ?string {
        $attachmentUrl = $this->resolveTestReportPublicUrl($batch, $reportUrl);
        if ($attachmentUrl === null) {
            return null;
        }

        return $this->upsertBatchAttachment(
            $batch,
            self::TEST_REPORT_TITLE,
            $attachmentUrl,
            app(AttachmentTypeResolver::class)->resolveOrCreateAttachmentTypeId(self::TEST_REPORT_TITLE),
            $userId,
            true,
        );
    }

    public function resolveTestReportPublicUrl(SampleHeader $batch, ?string $reportUrl = null): ?string
    {
        $candidate = trim((string) ($reportUrl ?: ''));
        if ($candidate === '') {
            $candidate = trim((string) ($batch->batch_report_online_url ?? ''));
        }
        if ($candidate === '') {
            $relative = trim((string) ($batch->batch_report_url ?? ''));
            if ($relative !== '') {
                $candidate = str_starts_with($relative, '/storage')
                    ? $relative
                    : '/storage'.(str_starts_with($relative, '/') ? $relative : '/'.$relative);
            }
        }

        if ($candidate === '') {
            return null;
        }

        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            $path = parse_url($candidate, PHP_URL_PATH);

            return is_string($path) && $path !== '' ? $path : $candidate;
        }

        return $candidate;
    }

    public function resolveQuotationForBatch(SampleHeader $batch): ?QuotationHeader
    {
        if (! empty($batch->quote_id)) {
            $quotation = QuotationHeader::query()->find($batch->quote_id);
            if ($quotation !== null) {
                return $quotation;
            }
        }

        $instance = $this->resolveSubmissionFormInstanceForBatch($batch);
        if ($instance?->sample_submission_request_id) {
            $request = SampleSubmissionRequest::query()
                ->with('currentQuotation')
                ->find($instance->sample_submission_request_id);

            if ($request?->currentQuotation) {
                return $request->currentQuotation;
            }
        }

        $acceptanceForm = AnalysisAcceptanceForm::query()
            ->where('sample_header_id', $batch->id)
            ->latest('created_at')
            ->first();

        if ($acceptanceForm?->sample_submission_request_id) {
            $request = SampleSubmissionRequest::query()
                ->with('currentQuotation')
                ->find($acceptanceForm->sample_submission_request_id);

            if ($request?->currentQuotation) {
                return $request->currentQuotation;
            }
        }

        return null;
    }

    public function resolveSubmissionFormInstanceForBatch(SampleHeader $batch): ?SubmissionFormInstance
    {
        if (! empty($batch->submission_form_instance_id)) {
            $instance = SubmissionFormInstance::query()->find($batch->submission_form_instance_id);
            if ($instance !== null) {
                return $instance;
            }
        }

        $acceptanceForm = AnalysisAcceptanceForm::query()
            ->where('sample_header_id', $batch->id)
            ->latest('created_at')
            ->first();

        if ($acceptanceForm?->submission_form_instance_id) {
            return SubmissionFormInstance::query()->find($acceptanceForm->submission_form_instance_id);
        }

        return null;
    }

    public function resolveQuotationPublicUrl(QuotationHeader $quotation, SampleHeader $batch): string
    {
        return route('batch.workflow-quotation.pdf', ['batch' => $batch->id]);
    }

    public function streamQuotationForBatch(SampleHeader $batch): Response
    {
        $quotation = $this->resolveQuotationForBatch($batch);
        abort_if($quotation === null, 404, 'No quotation linked to this batch.');

        $storedResponse = $this->streamStoredQuotationPdf($quotation);
        if ($storedResponse !== null) {
            return $storedResponse;
        }

        app(AmSpecQuotationPdfService::class)->generateAndStore($quotation->fresh() ?? $quotation);
        $quotation->refresh();

        $storedResponse = $this->streamStoredQuotationPdf($quotation);
        if ($storedResponse !== null) {
            return $storedResponse;
        }

        return app(QuotationReportService::class)->streamPdf($quotation);
    }

    private function streamStoredQuotationPdf(QuotationHeader $quotation): ?Response
    {
        $uploadUrl = trim((string) ($quotation->upload_url ?? ''));
        if ($uploadUrl === '' || ! str_starts_with($uploadUrl, '/quotations/')) {
            return null;
        }

        $path = storage_path('app'.$uploadUrl);
        if (! is_file($path)) {
            return null;
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }

    private function upsertBatchAttachment(
        SampleHeader $batch,
        string $title,
        string $attachmentUrl,
        ?string $attachmentTypeId,
        ?string $userId,
        bool $showOnCoa = false,
    ): string {
        $attachment = BatchAttachment::query()
            ->where('batch_id', $batch->id)
            ->where('title', $title)
            ->orderByDesc('created_at')
            ->first();

        if ($attachment === null) {
            $attachment = new BatchAttachment();
            $attachment->batch_id = $batch->id;
            $attachment->uploaded_by = $this->resolveUploaderId($batch, $userId);
            $attachment->title = $title;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = $showOnCoa ? 1 : 0;
        } elseif ($showOnCoa) {
            $attachment->show_on_coa = 1;
        }

        $attachment->attachment_type = $attachmentTypeId;
        $attachment->attachment_url = $attachmentUrl;
        $attachment->save();

        return $attachmentUrl;
    }

    private function resolveUploaderId(SampleHeader $batch, ?string $userId): string
    {
        foreach ([$userId, Auth::id(), $batch->receiving_officer] as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            $normalized = (string) $candidate;
            if (\Illuminate\Support\Str::isUuid($normalized)) {
                return $normalized;
            }
        }

        throw new \RuntimeException('Unable to resolve a valid uploader for batch attachment.');
    }
}
