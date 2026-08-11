<?php

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_submission_requests') || ! Schema::hasTable('submission_form_instances')) {
            return;
        }

        $attachmentService = app(SubmissionFormInstanceDocumentAttachmentService::class);

        SampleSubmissionRequest::query()
            ->whereNotNull('created_from_quotation_header_id')
            ->where('pricing_source', 'existing_quotation')
            ->orderBy('id')
            ->chunkById(50, function ($enquiries) use ($attachmentService): void {
                foreach ($enquiries as $enquiry) {
                    $instances = SubmissionFormInstance::query()
                        ->where('portal_request_id', (string) $enquiry->id)
                        ->get();

                    if ($instances->isEmpty() && $enquiry->submission_form_instance_id) {
                        $primary = SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
                        if ($primary !== null) {
                            $instances = collect([$primary]);
                        }
                    }

                    foreach ($instances as $instance) {
                        try {
                            $attachmentService->attachTestRequestForm(
                                $instance->fresh(['values.element', 'submissionForm', 'crmCustomer']),
                                null,
                                regenerate: true,
                            );
                        } catch (Throwable $exception) {
                            Log::warning('TRF PDF backfill failed for quotation-origin enquiry.', [
                                'enquiry_id' => $enquiry->id,
                                'instance_id' => $instance->id,
                                'error' => $exception->getMessage(),
                            ]);
                        }
                    }
                }
            });
    }

    public function down(): void
    {
        // Irreversible: attachments may have been created or refreshed.
    }
};
