<?php

namespace Tests\Feature\Sampleworkflow;

use App\Enums\Commercial\SampleHeaderPoStatus;
use App\SampleHeader;
use App\Services\Sampleworkflow\SplitJobReportGroupService;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SplitJobReportGroupServiceTest extends TestCase
{
    use RefreshDatabase;

    private SplitJobReportGroupService $groups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groups = app(SplitJobReportGroupService::class);
    }

    public function test_a_job_that_was_never_split_reports_alone(): void
    {
        $job = $this->job('Samples In Lab');

        $this->assertSame([(string) $job->id], $this->groups->reportGroupIds($job));
        $this->assertFalse($this->groups->hasParts($job));
        $this->assertFalse($this->groups->isSplitPart($job));
        $this->assertNull($this->groups->reportBlocker($job));
    }

    public function test_group_is_the_root_then_live_parts_in_creation_order(): void
    {
        $root = $this->job('Samples In Lab', [], now()->subHours(3));
        $later = $this->part($root, 'Awaiting PO', SampleHeaderPoStatus::AwaitingPo, now()->subHour());
        $earlier = $this->part($root, 'Samples In Lab', SampleHeaderPoStatus::Covered, now()->subHours(2));
        $this->part($root, 'Cancelled (No PO)', SampleHeaderPoStatus::Cancelled, now()->subMinutes(30));

        $expected = [(string) $root->id, (string) $earlier->id, (string) $later->id];

        $this->assertSame($expected, $this->groups->reportGroupIds($root));
        $this->assertSame($expected, $this->groups->reportGroupIds($later), 'A part resolves to the same group as its root.');
        $this->assertSame((string) $root->id, $this->groups->rootIdOf($later));
    }

    public function test_a_split_part_never_issues_its_own_report(): void
    {
        $root = $this->job('Reports for Collection');
        $part = $this->part($root, 'Reports for Collection', SampleHeaderPoStatus::Covered);

        $blocker = $this->groups->reportBlocker($part);

        $this->assertNotNull($blocker);
        $this->assertStringContainsString($part->batch_code, $blocker);
        $this->assertStringContainsString("job {$root->batch_code}'s test report", $blocker);
    }

    public function test_root_waits_for_a_part_still_awaiting_a_po(): void
    {
        $root = $this->job('Sample Approval');
        $part = $this->part($root, 'Awaiting PO', SampleHeaderPoStatus::AwaitingPo);

        $blocker = $this->groups->reportBlocker($root);

        $this->assertNotNull($blocker);
        $this->assertStringContainsString("{$part->batch_code} (Awaiting PO)", $blocker);
        $this->assertStringContainsString('that job is', $blocker);
    }

    public function test_root_waits_for_a_released_part_still_in_the_lab(): void
    {
        $root = $this->job('Sample Approval');
        $first = $this->part($root, 'Samples In Lab', SampleHeaderPoStatus::Covered, now()->subMinutes(10));
        $second = $this->part($root, 'Sample Verification', SampleHeaderPoStatus::Covered);

        $blocker = $this->groups->reportBlocker($root);

        $this->assertNotNull($blocker);
        $this->assertStringContainsString("{$first->batch_code} (Samples In Lab)", $blocker);
        $this->assertStringContainsString("{$second->batch_code} (Sample Verification)", $blocker);
        $this->assertStringContainsString('those jobs are', $blocker);
    }

    public function test_root_can_issue_once_every_part_is_report_ready(): void
    {
        $root = $this->job('Sample Approval');
        $this->part($root, 'Reports for Collection', SampleHeaderPoStatus::Covered);
        $this->part($root, 'Reports In Payment', SampleHeaderPoStatus::Covered);

        $this->assertNull($this->groups->reportBlocker($root));
    }

    public function test_root_can_issue_once_its_held_part_is_cancelled(): void
    {
        $root = $this->job('Sample Approval');
        $this->part($root, 'Cancelled (No PO)', SampleHeaderPoStatus::Cancelled);

        $this->assertTrue($this->groups->hasParts($root));
        $this->assertSame([(string) $root->id], $this->groups->reportGroupIds($root));
        $this->assertNull($this->groups->reportBlocker($root));
    }

    public function test_a_part_whose_root_is_missing_reports_alone(): void
    {
        $orphan = $this->job('Samples In Lab', ['split_from_sample_header_id' => (string) Str::uuid()]);

        $this->assertSame([(string) $orphan->id], $this->groups->reportGroupIds($orphan));
        $this->assertStringContainsString('the original job', (string) $this->groups->reportBlocker($orphan));
    }

    public function test_report_ready_statuses(): void
    {
        $this->assertTrue($this->groups->isReportReady($this->job('Reports for Collection')));
        $this->assertTrue($this->groups->isReportReady($this->job('Reports In Payment')));
        $this->assertFalse($this->groups->isReportReady($this->job('Samples In Lab')));
        $this->assertFalse($this->groups->isReportReady($this->job('Awaiting PO')));
    }

    private function part(
        SampleHeader $root,
        string $status,
        SampleHeaderPoStatus $poStatus,
        ?DateTimeInterface $createdAt = null,
    ): SampleHeader {
        return $this->job($status, [
            'split_from_sample_header_id' => (string) $root->id,
            'po_status' => $poStatus->value,
            'isactive' => $poStatus === SampleHeaderPoStatus::Cancelled ? 0 : 1,
        ], $createdAt);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function job(string $status, array $attributes = [], ?DateTimeInterface $createdAt = null): SampleHeader
    {
        return SampleHeader::query()->create(array_merge([
            'batch_code' => 'T'.random_int(100000000, 999999999),
            'crm_customer_id' => (string) Str::uuid(),
            'crm_unit_name' => 'Main site',
            'status' => $status,
            'is_routine' => false,
            'routine_frequency' => 0,
            'isactive' => 1,
            'created_at' => $createdAt ?? now(),
        ], $attributes));
    }
}
