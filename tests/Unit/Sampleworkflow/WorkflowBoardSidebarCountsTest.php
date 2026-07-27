<?php

namespace Tests\Unit\Sampleworkflow;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkflowBoardSidebarCountsTest extends TestCase
{
    #[Test]
    public function receiving_sidebar_status_keys_exclude_interzone_tab(): void
    {
        $keys = WorkflowBoard::receivingRequestStatusKeys();
        $tabs = WorkflowBoard::receivingRequestTabs();

        $this->assertContains('submitted', $keys);
        $this->assertContains('in_additional_info', $keys);
        $this->assertNotContains('in_review', $keys);
        $this->assertNotContains('interzone_transfers', $keys);
        $this->assertNotContains('complete', $keys);
        $this->assertNotContains('sub_contracting', $keys);
        $this->assertArrayHasKey('ready_for_reception', $tabs);
        $this->assertArrayNotHasKey('in_review', $tabs);
        $this->assertSame('sub_contracting', array_key_last($tabs));
        $this->assertArrayNotHasKey('complete', $tabs);
    }

    #[Test]
    public function sidebar_count_methods_return_non_negative_integers(): void
    {
        $this->assertGreaterThanOrEqual(0, WorkflowBoard::sidebarReceivingRequestCount());
        $this->assertGreaterThanOrEqual(0, WorkflowBoard::sidebarRequestReviewCount());
    }

    #[Test]
    public function receiving_dashboard_stats_shape_includes_todays_check_ins(): void
    {
        $board = new WorkflowBoard;
        $board->status = 'All Samples';

        $stats = $board->receivingDashboardStats;

        $this->assertSame([
            'sub_contracting' => 0,
            'submitted' => 0,
            'ready_for_reception' => 0,
            'accepted' => 0,
            'todays_check_ins' => 0,
        ], $stats);
    }

    #[Test]
    public function status_days_until_target_uses_signed_whole_days_at_start_of_day(): void
    {
        Carbon::setTestNow('2026-07-02 15:45:00');

        $this->assertSame(3, WorkflowBoard::statusDaysUntilTarget('2026-07-05'));
        $this->assertSame(-2, WorkflowBoard::statusDaysUntilTarget('2026-06-30'));
        $this->assertSame(0, WorkflowBoard::statusDaysUntilTarget('2026-07-02'));
        $this->assertNull(WorkflowBoard::statusDaysUntilTarget(null));

        Carbon::setTestNow();
    }

    #[Test]
    public function format_status_days_label_never_contains_fractions(): void
    {
        $this->assertSame('3 days left', WorkflowBoard::formatStatusDaysLabel(3));
        $this->assertSame('1 day overdue', WorkflowBoard::formatStatusDaysLabel(-1));
        $this->assertSame('Due today', WorkflowBoard::formatStatusDaysLabel(0));
        $this->assertSame('—', WorkflowBoard::formatStatusDaysLabel(null));
    }
}
