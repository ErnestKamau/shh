<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use App\Services\Sampleworkflow\BatchCustodyTimelineBuilder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BatchCustodyTimelineBuilderTest extends TestCase
{
    #[Test]
    public function receiving_pipeline_labels_align_with_samples_receiving_tabs(): void
    {
        $tabs = WorkflowBoard::receivingRequestTabs();

        $this->assertSame('Submitted Requests', $tabs['submitted'] ?? null);
        $this->assertSame('Ready for Reception', $tabs['ready_for_reception'] ?? null);
        $this->assertSame('Sample Integrity & Acceptance Check', $tabs['sample_integrity_check'] ?? null);
        $this->assertSame('Accepted', $tabs['accepted'] ?? null);
        $this->assertSame('Request Additional Info', $tabs['in_additional_info'] ?? null);
        $this->assertSame('Subcontracted', $tabs['sub_contracting'] ?? null);

        $this->assertTrue(class_exists(BatchCustodyTimelineBuilder::class));
    }
}
