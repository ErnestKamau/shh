<?php

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\CommercialEnquirySampleLineSync;
use App\Services\Commercial\PortalEnquiryFormInstanceSyncService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_submission_requests') || ! Schema::hasTable('submission_form_instances')) {
            return;
        }

        $pipelineStatuses = [
            'Requested',
            'Quotation In Progress',
            'Quotation Pending Approval',
            'Quotation Ready to Send',
            'Quotation Sent',
            'Quotation Under Review',
            'Quotation Accepted',
            'Ready for Reception',
            'In Review',
            'Sample Integrity Check',
        ];

        SampleSubmissionRequest::query()
            ->whereIn('status', $pipelineStatuses)
            ->whereNotNull('submission_form_instance_id')
            ->orderBy('id')
            ->chunkById(50, function ($enquiries): void {
                $sampleLineSync = app(CommercialEnquirySampleLineSync::class);
                $formInstanceSync = app(PortalEnquiryFormInstanceSyncService::class);

                foreach ($enquiries as $enquiry) {
                    /** @var SampleSubmissionRequest $enquiry */
                    $configs = is_array($enquiry->enquiry_sample_configuration)
                        ? $enquiry->enquiry_sample_configuration
                        : [];

                    if ($configs === [] || count($configs) <= 1) {
                        continue;
                    }

                    $sampleLineSync->syncFromSampleConfigs($enquiry, $configs);

                    $instance = SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
                    $submit = $instance !== null
                        && in_array(strtolower((string) $instance->status), ['submitted'], true);

                    $formInstanceSync->syncAllSampleTypesFromEnquiry(
                        $enquiry->fresh(['customer', 'requestedAnalyses']) ?? $enquiry,
                        submit: $submit,
                    );
                }
            });
    }

    public function down(): void
    {
        // Irreversible: TRF instance values are rebuilt from enquiry data.
    }
};
