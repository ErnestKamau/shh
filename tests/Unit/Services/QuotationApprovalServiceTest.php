<?php

namespace Tests\Unit\Services;

use App\Models\QuotationApprovalLog;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Commercial\QuotationApprovalService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $analyst;

    private User $labManager;

    private QuotationApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(QuotationApprovalService::class);

        Role::query()->firstOrCreate(['name' => 'Lab Manager', 'guard_name' => 'web']);

        $this->analyst = User::query()->create([
            'name' => 'Analyst User',
            'email' => 'analyst.approval@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);

        $this->labManager = User::query()->create([
            'name' => 'Lab Manager User',
            'email' => 'lab.manager.approval@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);
        $this->labManager->assignRole('Lab Manager');
    }

    public function test_submit_approve_and_reject_enquiry_quotation_flow(): void
    {
        $this->actingAs($this->analyst);

        [$enquiry, $header] = $this->makeEnquiryQuotation();

        $enquiry = $this->service->submitForApproval(
            $enquiry,
            $header,
            (string) $this->labManager->id,
            notifyEmail: false,
            comments: 'Please review',
        );

        $header->refresh();

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL, $enquiry->status);
        $this->assertSame(QuotationApprovalService::HEADER_STATUS_IN_APPROVAL, $header->status);
        $this->assertSame((string) $this->labManager->id, (string) $header->approved_by);
        $this->assertSame(0, (int) $header->is_approved);
        $this->assertTrue(
            QuotationApprovalLog::query()
                ->where('quotation_header_id', $header->id)
                ->where('action', QuotationApprovalLog::ACTION_SUBMITTED)
                ->exists()
        );

        $this->actingAs($this->labManager);

        $enquiry = $this->service->approve($enquiry->fresh(), $header->fresh(), 'Looks good');
        $header->refresh();

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND, $enquiry->status);
        $this->assertSame(QuotationApprovalService::HEADER_STATUS_COMPLETE, $header->status);
        $this->assertSame(1, (int) $header->is_approved);
        $this->assertSame((string) $this->labManager->id, (string) $header->approved_by);
        $this->assertTrue($this->service->isApprovedReadyToSend($enquiry, $header));

        // Resubmit and reject
        $this->actingAs($this->analyst);
        $enquiry = $this->service->submitForApproval(
            $enquiry->fresh(),
            $header->fresh(),
            (string) $this->labManager->id,
            notifyEmail: false,
        );

        $this->actingAs($this->labManager);
        $enquiry = $this->service->reject(
            $enquiry->fresh(),
            $header->fresh(),
            'Fix pricing',
        );
        $header->refresh();

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS, $enquiry->status);
        $this->assertSame(QuotationApprovalService::HEADER_STATUS_IN_PREPARATION, $header->status);
        $this->assertSame(0, (int) $header->is_approved);
        $this->assertSame('Fix pricing', $header->approval_comments);
    }

    public function test_reassign_lab_manager_while_pending_approval(): void
    {
        $otherManager = User::query()->create([
            'name' => 'Other Lab Manager',
            'email' => 'other.lab.manager@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);
        $otherManager->assignRole('Lab Manager');

        $this->actingAs($this->analyst);
        [$enquiry, $header] = $this->makeEnquiryQuotation();
        $this->service->submitForApproval($enquiry, $header, (string) $this->labManager->id, false);

        $enquiry = $this->service->reassignLabManager(
            $enquiry->fresh(),
            $header->fresh(),
            (string) $otherManager->id,
            notifyEmail: false,
        );
        $header->refresh();

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_PENDING_APPROVAL, $enquiry->status);
        $this->assertSame((string) $otherManager->id, (string) $header->approved_by);
        $this->assertSame(0, (int) $header->is_approved);
        $this->assertTrue(
            QuotationApprovalLog::query()
                ->where('quotation_header_id', $header->id)
                ->where('action', QuotationApprovalLog::ACTION_REASSIGNED)
                ->where('assignee_user_id', $otherManager->id)
                ->exists()
        );
    }

    public function test_reassign_after_approval_is_blocked(): void
    {
        $otherManager = User::query()->create([
            'name' => 'Second Lab Manager',
            'email' => 'second.lab.manager@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);
        $otherManager->assignRole('Lab Manager');

        $this->actingAs($this->analyst);
        [$enquiry, $header] = $this->makeEnquiryQuotation();
        $this->service->submitForApproval($enquiry, $header, (string) $this->labManager->id, false);

        $this->actingAs($this->labManager);
        $enquiry = $this->service->approve($enquiry->fresh(), $header->fresh());
        $this->assertTrue($this->service->isApprovedReadyToSend($enquiry, $header->fresh()));

        $this->actingAs($this->analyst);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('pending approval');

        $this->service->reassignLabManager(
            $enquiry->fresh(),
            $header->fresh(),
            (string) $otherManager->id,
            notifyEmail: false,
        );
    }

    public function test_assert_ready_to_send_blocks_unapproved_enquiry_quote(): void
    {
        $this->expectException(\RuntimeException::class);

        $header = QuotationHeader::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => QuotationApprovalService::HEADER_STATUS_IN_PREPARATION,
            'from_enquiry' => true,
            'is_approved' => 0,
            'is_complete' => 0,
            'is_draft' => 0,
            'quote_date' => now()->toDateString(),
        ]);

        $this->service->assertReadyToSend($header);
    }

    public function test_pending_approvals_for_user_lists_assigned_quotes(): void
    {
        $this->actingAs($this->analyst);
        [$enquiry, $header] = $this->makeEnquiryQuotation();
        $this->service->submitForApproval($enquiry, $header, (string) $this->labManager->id, false);

        $pending = $this->service->pendingApprovalsForUser((string) $this->labManager->id);

        $this->assertCount(1, $pending);
        $this->assertSame((string) $header->id, (string) $pending->first()->id);
    }

    /**
     * @return array{0: SampleSubmissionRequest, 1: QuotationHeader}
     */
    private function makeEnquiryQuotation(): array
    {
        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);

        $header = QuotationHeader::query()->create([
            'crm_customer_id' => $enquiry->crm_customer_id,
            'status' => QuotationApprovalService::HEADER_STATUS_IN_PREPARATION,
            'from_enquiry' => true,
            'sample_submission_request_id' => $enquiry->id,
            'prepared_by_id' => (string) $this->analyst->id,
            'is_approved' => 0,
            'is_complete' => 0,
            'is_draft' => 0,
            'quote_date' => now()->toDateString(),
            'quote_number' => 'AMSQTEST-001',
            'upload_url' => '/tmp/fake-quote.pdf',
        ]);

        QuotationDetails::query()->create([
            'quotation_header_id' => $header->id,
            'quantity' => 1,
            'unit_price' => 10,
            'tax' => 0,
            'description' => 'Test sample',
            'item_name' => 'Parameter A',
        ]);

        $enquiry->current_quotation_header_id = $header->id;
        $enquiry->save();

        return [$enquiry->fresh(), $header->fresh()];
    }
}
