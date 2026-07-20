<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\SampleHeader;
use App\Services\Sampleworkflow\BatchWorkflowStageSyncService;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BatchWorkflowStageSyncServiceTest extends TestCase
{
    #[Test]
    public function apply_workflow_status_records_chain_of_custody_when_status_changes(): void
    {
        $trackingStageId = (string) Str::uuid();
        $recorded = [];

        $service = new class ($recorded, $trackingStageId) extends BatchWorkflowStageSyncService {
            public function __construct(private array &$recorded, private string $trackingStageId)
            {
            }

            public function resolveTrackingStageId(SampleHeader $batch, string $workflowStatus): ?string
            {
                return $this->trackingStageId;
            }

            public function recordChainOfCustodyTransition(
                SampleHeader $batch,
                string $workflowStatus,
                ?string $trackingStageId,
                ?string $comments = null,
            ): void {
                $this->recorded[] = [
                    'batch_id' => $batch->id,
                    'workflow_status' => $workflowStatus,
                    'tracking_stage_id' => $trackingStageId,
                    'comments' => $comments,
                ];
            }
        };

        $batch = new SampleHeader();
        $batch->id = (string) Str::uuid();
        $batch->status = 'Samples In Lab';
        $batch->sample_tracking_stage = null;

        $service->applyWorkflowStatus($batch, 'Sample Verification', 'Moved to Sample Verification from Samples In Lab.');

        $this->assertSame('Sample Verification', $batch->status);
        $this->assertSame($trackingStageId, $batch->sample_tracking_stage);
        $this->assertCount(1, $recorded);
        $this->assertSame('Sample Verification', $recorded[0]['workflow_status']);
        $this->assertSame('Moved to Sample Verification from Samples In Lab.', $recorded[0]['comments']);
    }

    #[Test]
    public function apply_workflow_status_skips_chain_of_custody_when_status_unchanged(): void
    {
        $recorded = [];

        $service = new class ($recorded) extends BatchWorkflowStageSyncService {
            public function __construct(private array &$recorded)
            {
            }

            public function resolveTrackingStageId(SampleHeader $batch, string $workflowStatus): ?string
            {
                return null;
            }

            public function recordChainOfCustodyTransition(
                SampleHeader $batch,
                string $workflowStatus,
                ?string $trackingStageId,
                ?string $comments = null,
            ): void {
                $this->recorded[] = [$workflowStatus, $comments];
            }
        };

        $batch = new SampleHeader();
        $batch->id = (string) Str::uuid();
        $batch->status = 'Sample Verification';

        $service->applyWorkflowStatus($batch, 'Sample Verification');

        $this->assertSame('Sample Verification', $batch->status);
        $this->assertSame([], $recorded);
    }

    #[Test]
    public function apply_workflow_status_uses_default_comment_when_none_provided(): void
    {
        $recorded = [];

        $service = new class ($recorded) extends BatchWorkflowStageSyncService {
            public function __construct(private array &$recorded)
            {
            }

            public function resolveTrackingStageId(SampleHeader $batch, string $workflowStatus): ?string
            {
                return null;
            }

            public function recordChainOfCustodyTransition(
                SampleHeader $batch,
                string $workflowStatus,
                ?string $trackingStageId,
                ?string $comments = null,
            ): void {
                $this->recorded[] = $comments;
            }
        };

        $batch = new SampleHeader();
        $batch->id = (string) Str::uuid();
        $batch->status = 'Sample Verification';

        $service->applyWorkflowStatus($batch, 'Sample Approval');

        $this->assertSame(['Moved to Sample Approval (from Sample Verification).'], $recorded);
    }
}
