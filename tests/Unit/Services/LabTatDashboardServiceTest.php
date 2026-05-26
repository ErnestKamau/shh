<?php

namespace Tests\Unit\Services;

use App\Services\Dashboards\LabTatDashboardService;
use PHPUnit\Framework\TestCase;

class LabTatDashboardServiceTest extends TestCase
{
    private LabTatDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LabTatDashboardService();
    }

    public function test_signed_tat_offset_uses_twelve_hour_late_grace(): void
    {
        $expected = '2026-05-21 08:00:00';

        $this->assertSame(0, $this->service->signedTatOffsetDays($expected, '2026-05-21 20:00:00'));
        $this->assertSame(1, $this->service->signedTatOffsetDays($expected, '2026-05-21 21:00:00'));
        $this->assertSame(1, $this->service->signedTatOffsetDays($expected, '2026-05-22 20:00:00'));
        $this->assertSame(2, $this->service->signedTatOffsetDays($expected, '2026-05-22 21:00:00'));
    }

    public function test_signed_tat_offset_uses_twelve_hour_early_grace(): void
    {
        $expected = '2026-05-21 08:00:00';

        $this->assertSame(0, $this->service->signedTatOffsetDays($expected, '2026-05-20 20:00:00'));
        $this->assertSame(-1, $this->service->signedTatOffsetDays($expected, '2026-05-20 19:00:00'));
        $this->assertSame(-1, $this->service->signedTatOffsetDays($expected, '2026-05-19 20:00:00'));
        $this->assertSame(-2, $this->service->signedTatOffsetDays($expected, '2026-05-19 19:00:00'));
    }

    public function test_signed_tat_offset_returns_zero_when_dates_are_missing(): void
    {
        $this->assertSame(0, $this->service->signedTatOffsetDays(null, '2026-05-21 08:00:00'));
        $this->assertSame(0, $this->service->signedTatOffsetDays('2026-05-21 08:00:00', null));
    }

    public function test_due_today_window_uses_twelve_hour_grace_around_expected_date(): void
    {
        $expected = '2026-05-21 08:00:00';

        $this->assertFalse($this->service->isWithinDueTodayWindow($expected, '2026-05-20 19:59:59'));
        $this->assertTrue($this->service->isWithinDueTodayWindow($expected, '2026-05-20 20:00:00'));
        $this->assertTrue($this->service->isWithinDueTodayWindow($expected, '2026-05-21 20:00:00'));
        $this->assertFalse($this->service->isWithinDueTodayWindow($expected, '2026-05-21 20:00:01'));
        $this->assertFalse($this->service->isWithinDueTodayWindow(null, '2026-05-21 08:00:00'));
    }

    public function test_completion_days_returns_rounded_day_difference(): void
    {
        $this->assertSame(2.5, $this->service->completionDays('2026-05-19 08:00:00', '2026-05-21 20:00:00'));
        $this->assertSame(0.5, $this->service->completionDays('2026-05-21 08:00:00', '2026-05-21 20:00:00'));
        $this->assertNull($this->service->completionDays(null, '2026-05-21 20:00:00'));
        $this->assertNull($this->service->completionDays('2026-05-21 08:00:00', null));
    }

    public function test_stage_completion_rate_uses_completed_batches_over_total_batches(): void
    {
        $this->assertSame(80, $this->service->stageCompletionRate(8, 10));
        $this->assertSame(0, $this->service->stageCompletionRate(0, 10));
        $this->assertSame(0, $this->service->stageCompletionRate(8, 0));
        $this->assertSame(0, $this->service->stageCompletionRate(-2, 10));
    }
}
