<?php

namespace Tests\Unit\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use App\Services\Commercial\CommercialEnquiryFromTrfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommercialEnquiryFromTrfServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_from_trfi_populates_ssr_from_form_data(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $template = TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Water',
            'code' => 'TRF-WATER',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $customerId = (string) Str::uuid7();

        $trfi = TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => $template->id,
            'crm_customer_id' => $customerId,
            'source_channel' => TestRequestFormInstance::CHANNEL_WALK_IN,
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
            'form_data' => [
                'customer_name' => 'Acme Labs',
                'statement_of_conformity' => 'YES',
                'sampling_date' => '2026-06-16',
                'sampling_location' => 'Site A',
                'sample_rows' => [
                    [
                        'sample_description' => 'Tap water',
                        'qty' => 2,
                    ],
                ],
            ],
        ]);

        $enquiry = app(CommercialEnquiryFromTrfService::class)->syncFromTrfi($trfi);

        $this->assertSame($trfi->id, $enquiry->test_request_form_instance_id);
        $this->assertSame($customerId, $enquiry->crm_customer_id);
        $this->assertSame('walk_in', $enquiry->source_channel);
        $this->assertTrue($enquiry->request_for_sampling);
        $this->assertIsArray($enquiry->sample_lines);
        $this->assertCount(1, $enquiry->sample_lines);
        $this->assertSame('Tap water', $enquiry->sample_lines[0]['sample_description']);

        $trfi->refresh();
        $this->assertSame($enquiry->id, $trfi->sample_submission_request_id);
    }

    public function test_resync_updates_existing_enquiry_by_trfi_id(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $template = TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Food',
            'code' => 'TRF-FOOD',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $trfi = TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => $template->id,
            'source_channel' => TestRequestFormInstance::CHANNEL_PORTAL,
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
            'form_data' => [
                'sample_rows' => [
                    ['sample_description' => 'Initial'],
                ],
            ],
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'test_request_form_instance_id' => $trfi->id,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            'source_channel' => 'portal',
            'sample_lines' => [],
        ]);

        $trfi->update([
            'sample_submission_request_id' => $enquiry->id,
            'form_data' => [
                'sample_rows' => [
                    ['sample_description' => 'Updated row'],
                ],
            ],
        ]);

        $updated = app(CommercialEnquiryFromTrfService::class)->resyncSampleDataFromTrfi($trfi->fresh());

        $this->assertNotNull($updated);
        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_SENT, $updated->status);
        $this->assertSame('Updated row', $updated->sample_lines[0]['sample_description']);
    }
}
