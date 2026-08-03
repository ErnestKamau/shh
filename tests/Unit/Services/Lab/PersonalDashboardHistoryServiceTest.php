<?php

namespace Tests\Unit\Services\Lab;

use App\Services\Commercial\QuotationApprovalService;
use App\Services\Lab\PersonalDashboardHistoryService;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonalDashboardHistoryServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    #[Test]
    public function sort_unified_rows_puts_pending_before_completed_and_orders_by_newest_stamp(): void
    {
        $service = new PersonalDashboardHistoryService(
            Mockery::mock(QuotationApprovalService::class)
        );

        $rows = Collection::make([
            [
                'reference' => 'DONE-OLD',
                'status_tone' => 'complete',
                'sort_at' => '2026-07-01 10:00:00',
            ],
            [
                'reference' => 'PENDING-NEW',
                'status_tone' => 'pending',
                'sort_at' => '2026-08-02 12:00:00',
            ],
            [
                'reference' => 'PENDING-OLD',
                'status_tone' => 'pending',
                'sort_at' => '2026-08-01 09:00:00',
            ],
            [
                'reference' => 'DONE-NEW',
                'status_tone' => 'complete',
                'sort_at' => '2026-08-03 08:00:00',
            ],
            [
                'reference' => 'REJECTED',
                'status_tone' => 'rejected',
                'sort_at' => '2026-08-04 11:00:00',
            ],
        ]);

        $sorted = $service->sortUnifiedRows($rows)->pluck('reference')->all();

        $this->assertSame([
            'PENDING-NEW',
            'PENDING-OLD',
            'REJECTED',
            'DONE-NEW',
            'DONE-OLD',
        ], $sorted);
    }

    #[Test]
    public function sort_unified_rows_treats_missing_tone_as_non_pending(): void
    {
        $service = new PersonalDashboardHistoryService(
            Mockery::mock(QuotationApprovalService::class)
        );

        $rows = Collection::make([
            [
                'reference' => 'NO-TONE',
                'sort_at' => '2026-08-05 10:00:00',
            ],
            [
                'reference' => 'PENDING',
                'status_tone' => 'pending',
                'sort_at' => '2026-08-01 10:00:00',
            ],
        ]);

        $sorted = $service->sortUnifiedRows($rows)->pluck('reference')->all();

        $this->assertSame(['PENDING', 'NO-TONE'], $sorted);
    }
}
