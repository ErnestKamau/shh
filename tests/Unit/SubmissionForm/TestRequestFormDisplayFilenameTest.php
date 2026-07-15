<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceAttachment;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Tests\TestCase;

class TestRequestFormDisplayFilenameTest extends TestCase
{
    public function test_resolve_display_filename_uses_sanitized_form_number(): void
    {
        $instance = new SubmissionFormInstance([
            'id' => '019f64f6-07d1-7122-8fc8-6ac37a46e3bc',
            'form_number' => 'TRFF017/26',
        ]);
        $instance->id = '019f64f6-07d1-7122-8fc8-6ac37a46e3bc';

        $filename = app(TestRequestFormPdfService::class)->resolveDisplayFilename($instance);

        $this->assertSame('Test-Request-Form-TRFF017-26.pdf', $filename);
    }

    public function test_resolve_display_filename_falls_back_to_instance_id(): void
    {
        $instance = new SubmissionFormInstance([
            'id' => '019f64f6-07d1-7122-8fc8-6ac37a46e3bc',
            'form_number' => null,
        ]);
        $instance->id = '019f64f6-07d1-7122-8fc8-6ac37a46e3bc';

        $filename = app(TestRequestFormPdfService::class)->resolveDisplayFilename($instance);

        $this->assertSame('Test-Request-Form-019f64f6-07d1-7122-8fc8-6ac37a46e3bc.pdf', $filename);
    }

    public function test_attachment_file_name_accessor_hides_trf_storage_basename(): void
    {
        $instance = new SubmissionFormInstance([
            'id' => '019f64f6-07d1-7122-8fc8-6ac37a46e3bc',
            'form_number' => 'TRFF017/26',
        ]);
        $instance->id = '019f64f6-07d1-7122-8fc8-6ac37a46e3bc';

        $attachment = new SubmissionFormInstanceAttachment([
            'original_name' => 'trf-sfi-019f64f6-07d1-7122-8fc8-6ac37a46e3bc.pdf',
            'attachment_heading' => 'Test Request Form',
        ]);
        $attachment->setRelation('submissionFormInstance', $instance);

        $this->assertSame('Test-Request-Form-TRFF017-26.pdf', $attachment->file_name);
    }

    public function test_attachment_file_name_accessor_keeps_customer_upload_names(): void
    {
        $attachment = new SubmissionFormInstanceAttachment([
            'original_name' => 'ADNOCGroup-ADNOCGroup2026005-15-Jul-2026.pdf',
            'attachment_heading' => 'Quotation - ADNOCGroup2026005',
        ]);

        $this->assertSame('ADNOCGroup-ADNOCGroup2026005-15-Jul-2026.pdf', $attachment->file_name);
    }
}
