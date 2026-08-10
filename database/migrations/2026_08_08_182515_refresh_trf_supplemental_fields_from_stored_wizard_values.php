<?php

use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\EnquiryFromQuotationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_submission_requests')
            || ! Schema::hasColumn('sample_submission_requests', 'trf_section_field_values')) {
            return;
        }

        $service = app(EnquiryFromQuotationService::class);

        SampleSubmissionRequest::query()
            ->whereNotNull('trf_section_field_values')
            ->whereNotNull('submission_form_instance_id')
            ->orderBy('id')
            ->chunkById(50, function ($enquiries) use ($service): void {
                foreach ($enquiries as $enquiry) {
                    /** @var SampleSubmissionRequest $enquiry */
                    $stored = is_array($enquiry->trf_section_field_values)
                        ? $enquiry->trf_section_field_values
                        : [];

                    if ($stored === []) {
                        continue;
                    }

                    $service->refreshSupplementalFieldsOnEnquiry($enquiry, resyncTrf: true);
                }
            });
    }

    public function down(): void
    {
        // Irreversible: supplemental fields are rebuilt from stored wizard values.
    }
};
