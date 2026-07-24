<?php

namespace Tests\Unit\Sampleworkflow;

use App\Livewire\Sampleworkflow\WorkflowBoard;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class WorkflowBoardAllSamplesTest extends TestCase
{
    #[Test]
    public function all_samples_defaults_to_unfiltered_all_values(): void
    {
        $board = new WorkflowBoard;
        $method = (new ReflectionClass($board))->getMethod('defaultAllSamplesFilter');
        $method->setAccessible(true);

        $defaults = $method->invoke($board);

        $this->assertSame('All', $defaults['customer_id']);
        $this->assertSame('All', $defaults['sample_type_id']);
        $this->assertSame('All', $defaults['schedule_sent']);
        $this->assertSame('', $defaults['receipt_date_from']);
        $this->assertSame('', $defaults['receipt_date_to']);
        $this->assertSame('', $defaults['tat_date_from']);
        $this->assertSame('', $defaults['tat_date_to']);
    }

    #[Test]
    public function clear_filters_resets_all_samples_filter_state(): void
    {
        $board = new WorkflowBoard;
        $defaultsMethod = (new ReflectionClass($board))->getMethod('defaultAllSamplesFilter');
        $defaultsMethod->setAccessible(true);

        $board->allFilter = $defaultsMethod->invoke($board);
        $board->allFilter['tat_date_from'] = '2026-01-01';
        $board->allFilter['schedule_sent'] = 'sent';
        $board->search = 'BATCH-1';
        $board->receiptDateFrom = '2026-01-01';
        $board->receiptDateTo = '2026-01-31';
        $board->customerFilter = 12;
        $board->sampleTypeFilter = 34;

        $board->clearFilters();

        $this->assertSame('', $board->search);
        $this->assertNull($board->receiptDateFrom);
        $this->assertNull($board->receiptDateTo);
        $this->assertNull($board->customerFilter);
        $this->assertNull($board->sampleTypeFilter);
        $this->assertSame('All', $board->allFilter['schedule_sent']);
        $this->assertSame('', $board->allFilter['tat_date_from']);
        $this->assertSame('', $board->allFilter['tat_date_to']);
    }

    #[Test]
    public function active_request_filter_count_includes_all_samples_advanced_filters(): void
    {
        $board = new WorkflowBoard;
        $defaultsMethod = (new ReflectionClass($board))->getMethod('defaultAllSamplesFilter');
        $defaultsMethod->setAccessible(true);

        $board->allFilter = $defaultsMethod->invoke($board);
        $board->allFilter['tat_date_from'] = '2026-02-01';
        $board->allFilter['schedule_sent'] = 'not_sent';

        $this->assertSame(2, $board->activeRequestFilterCount);
    }

    #[Test]
    public function all_samples_blade_uses_receiving_filter_chrome_and_shows_batches(): void
    {
        $blade = file_get_contents(resource_path('views/livewire/sampleworkflow/workflow-board.blade.php'));

        $this->assertIsString($blade);
        $this->assertStringNotContainsString("@if(\$status != 'All Samples')", $blade);
        $this->assertStringContainsString("\$status === 'All Samples'", $blade);
        $this->assertStringContainsString('all-samples-customer-dropdown', $blade);
        $this->assertStringContainsString('workflow-brand-filters-card', $blade);
        $this->assertStringContainsString('workflow-filters-primary-row', $blade);
        $this->assertStringContainsString('wire:model.live="allFilter.tat_date_from"', $blade);
        $this->assertStringContainsString('workflow-table', $blade);
        $this->assertStringContainsString('No batches match your current filters.', $blade);
    }
}
