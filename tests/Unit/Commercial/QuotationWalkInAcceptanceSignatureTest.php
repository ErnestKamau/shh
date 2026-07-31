<?php

namespace Tests\Unit\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\QuotationHeader;
use App\Services\Billing\QuotationReportService;
use App\Services\Commercial\QuotationAcceptanceTatService;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class QuotationWalkInAcceptanceSignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_record_walk_in_acceptance_requires_customer_signature(): void
    {
        $enquiry = $this->makeSentEnquiry();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Customer signature is required');

        app(QuotationFromEnquiryService::class)->recordWalkInAcceptance($enquiry);
    }

    public function test_record_walk_in_acceptance_persists_signature_on_quotation_header(): void
    {
        $enquiry = $this->makeSentEnquiry();
        $quotation = QuotationHeader::query()->findOrFail($enquiry->current_quotation_header_id);

        $this->mock(QuotationReportService::class, function ($mock) use ($quotation): void {
            $mock->shouldReceive('storePdf')
                ->once()
                ->andReturn($quotation);
        });

        $this->mock(QuotationAcceptanceTatService::class, function ($mock): void {
            $mock->shouldReceive('recalculateCustomerTat')->once();
        });

        $updated = app(QuotationFromEnquiryService::class)->recordWalkInAcceptance(
            $enquiry,
            null,
            false,
            [
                'signature' => 'data:image/png;base64,walkinsign',
                'signer_name' => 'Walk In Client',
            ],
        );

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, $updated->status);

        $quotation->refresh();
        $this->assertSame('data:image/png;base64,walkinsign', $quotation->customer_acceptance_signature);
        $this->assertSame('Walk In Client', $quotation->customer_acceptance_signer_name);
        $this->assertSame(QuotationHeader::ACCEPTANCE_CHANNEL_WALK_IN, $quotation->customer_acceptance_channel);
        $this->assertNotNull($quotation->customer_acceptance_signed_at);
    }

    public function test_record_walk_in_acceptance_refreshes_the_request_quotation_attachment(): void
    {
        $enquiry = $this->makeSentEnquiry();
        $instance = $this->linkSubmissionFormInstance($enquiry);

        $quotation = QuotationHeader::query()->findOrFail($enquiry->current_quotation_header_id);
        $quotation->upload_url = '/quotations/WalkInCustomer/Q-ACC-001.pdf';
        $quotation->save();

        $this->mock(QuotationReportService::class, function ($mock) use ($quotation): void {
            $mock->shouldReceive('storePdf')->once()->andReturn($quotation);
        });

        $this->mock(QuotationAcceptanceTatService::class, function ($mock): void {
            $mock->shouldReceive('recalculateCustomerTat')->once();
        });

        $this->mock(SubmissionFormInstanceDocumentAttachmentService::class, function ($mock) use ($instance, $quotation): void {
            $mock->shouldReceive('attachQuotation')
                ->once()
                ->withArgs(function ($attachedInstance, $attachedQuotation) use ($instance, $quotation): bool {
                    return (string) $attachedInstance->id === (string) $instance->id
                        && (string) $attachedQuotation->id === (string) $quotation->id;
                });
        });

        app(QuotationFromEnquiryService::class)->recordWalkInAcceptance(
            $enquiry,
            null,
            false,
            [
                'signature' => 'data:image/png;base64,walkinsign',
                'signer_name' => 'Walk In Client',
            ],
        );
    }

    public function test_record_walk_in_acceptance_skips_attachment_when_no_pdf_was_stored(): void
    {
        $enquiry = $this->makeSentEnquiry();
        $this->linkSubmissionFormInstance($enquiry);

        $quotation = QuotationHeader::query()->findOrFail($enquiry->current_quotation_header_id);

        $this->mock(QuotationReportService::class, function ($mock) use ($quotation): void {
            $mock->shouldReceive('storePdf')->once()->andReturn($quotation);
        });

        $this->mock(QuotationAcceptanceTatService::class, function ($mock): void {
            $mock->shouldReceive('recalculateCustomerTat')->once();
        });

        $this->mock(SubmissionFormInstanceDocumentAttachmentService::class, function ($mock): void {
            $mock->shouldNotReceive('attachQuotation');
        });

        app(QuotationFromEnquiryService::class)->recordWalkInAcceptance(
            $enquiry,
            null,
            false,
            [
                'signature' => 'data:image/png;base64,walkinsign',
                'signer_name' => 'Walk In Client',
            ],
        );
    }

    private function linkSubmissionFormInstance(SampleSubmissionRequest $enquiry): SubmissionFormInstance
    {
        $user = User::create([
            'name' => 'Acceptance User',
            'email' => 'acceptance.attachment@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Walk-in acceptance form',
            'document_code' => 'WALKIN/ATTACH',
            'description' => 'Test',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Walk-in instance',
            'form_number' => 'CR900',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $user->id,
            'priority' => 'normal',
        ]);

        $enquiry->submission_form_instance_id = $instance->id;
        $enquiry->save();

        return $instance;
    }

    private function makeSentEnquiry(): SampleSubmissionRequest
    {
        $enquiry = SampleSubmissionRequest::query()->create([
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            'source_channel' => 'walk_in',
            'crm_customer_id' => (string) Str::uuid(),
        ]);

        $quotation = QuotationHeader::query()->create([
            'quote_number' => 'Q-ACC-001',
            'crm_customer_id' => $enquiry->crm_customer_id,
            'sample_submission_request_id' => $enquiry->id,
            'from_enquiry' => true,
            'sent_to_customer_at' => Carbon::now()->subHour(),
            'quote_date' => Carbon::now()->toDateString(),
            'is_draft' => 0,
            'is_complete' => 1,
        ]);

        $enquiry->current_quotation_header_id = $quotation->id;
        $enquiry->save();

        return $enquiry->fresh(['currentQuotation']);
    }
}
