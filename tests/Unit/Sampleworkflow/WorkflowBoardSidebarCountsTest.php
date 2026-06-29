<?php

namespace Tests\Unit\Sampleworkflow;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkflowBoardSidebarCountsTest extends TestCase
{
    #[Test]
    public function receiving_sidebar_status_keys_exclude_interzone_tab(): void
    {
        $keys = WorkflowBoard::receivingRequestStatusKeys();

        $this->assertContains('submitted', $keys);
        $this->assertContains('received', $keys);
        $this->assertContains('in_additional_info', $keys);
        $this->assertNotContains('interzone_transfers', $keys);
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
            'my_intray' => 0,
            'submitted' => 0,
            'ready_for_reception' => 0,
            'received' => 0,
            'todays_check_ins' => 0,
        ], $stats);
    }
}
