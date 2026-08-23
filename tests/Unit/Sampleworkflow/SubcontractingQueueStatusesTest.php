<?php

namespace Tests\Unit\Sampleworkflow;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Models\SampleSubmissionRequest;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class SubcontractingQueueStatusesTest extends TestCase
{
    #[Test]
    public function accepted_status_constant_is_amspec_accepted_not_subcontract_receipt(): void
    {
        $this->assertSame('received_at_lab', SampleSubmissionRequest::STATUS_ACCEPTED);

        $enquiry = new SampleSubmissionRequest([
            'status' => SampleSubmissionRequest::STATUS_ACCEPTED,
        ]);

        $this->assertSame('Accepted', $enquiry->commercialStatus());
    }

    #[Test]
    public function subcontracting_queue_statuses_are_accepted_only_not_an_enquiry_stage(): void
    {
        $statuses = SampleSubmissionRequest::subcontractingQueueEnquiryStatuses();

        $this->assertSame([SampleSubmissionRequest::STATUS_ACCEPTED], $statuses);
        $this->assertContains('received_at_lab', $statuses);
        $this->assertNotContains(SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK, $statuses);
        $this->assertFalse(defined(SampleSubmissionRequest::class.'::STATUS_SUB_CONTRACTING'));
    }

    #[Test]
    public function awaiting_dispatch_query_includes_amspec_accepted_and_skips_receiving_exclusion(): void
    {
        if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_status')) {
            $this->markTestSkipped('subcontracting_dispatch_status column is not available.');
        }

        $board = new WorkflowBoard;
        $board->status = 'Samples Receiving';
        $board->workflowSubTab = 'sub_contracting';
        $board->subcontractingDispatchStatus = SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING;

        $method = new ReflectionMethod(WorkflowBoard::class, 'subcontractingSubmissionFormsQuery');
        $method->setAccessible(true);

        $query = $method->invoke($board, SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING);
        $sql = strtolower($query->toSql());
        $bindings = $query->getBindings();

        $this->assertContains(SampleSubmissionRequest::STATUS_ACCEPTED, $bindings);
        $this->assertContains(SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING, $bindings);
        $this->assertStringContainsString('subcontracting_dispatch_status', $sql);
        // Must not rely on the receiving base query exclusion of Samples In Lab batches.
        $this->assertStringNotContainsString('samples en-route', $sql);
    }
}
