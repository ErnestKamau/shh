<?php

namespace Tests\Unit\Services\Billing;

use App\Services\Billing\QuotationStatisticsService;
use Tests\TestCase;

class QuotationStatisticsServiceTest extends TestCase
{
    public function test_overview_metrics_returns_expected_keys(): void
    {
        $service = app(QuotationStatisticsService::class);

        $metrics = $service->getOverviewMetrics();

        $this->assertArrayHasKey('total', $metrics);
        $this->assertArrayHasKey('drafts', $metrics);
        $this->assertArrayHasKey('in_preparation', $metrics);
        $this->assertArrayHasKey('finalised', $metrics);
        $this->assertArrayHasKey('from_enquiry', $metrics);
        $this->assertArrayHasKey('sent_to_customer', $metrics);
        $this->assertArrayHasKey('pdf_generated', $metrics);
        $this->assertArrayHasKey('total_value_formatted', $metrics);
        $this->assertArrayHasKey('this_month', $metrics);
        $this->assertArrayHasKey('expiring_soon', $metrics);
        $this->assertArrayHasKey('analysis', $metrics);
        $this->assertArrayHasKey('general', $metrics);
        $this->assertIsInt($metrics['total']);
        $this->assertIsString($metrics['total_value_formatted']);
    }

    public function test_get_kpi_period_metrics_returns_spec_fields(): void
    {
        $service = app(QuotationStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $kpi = $service->getKpiPeriodMetrics($start, $end);

        $this->assertArrayHasKey('quotations_sent', $kpi);
        $this->assertArrayHasKey('quotations_accepted', $kpi);
        $this->assertArrayHasKey('success_rate_percent', $kpi);
        $this->assertArrayHasKey('total_quotation_value', $kpi);
        $this->assertArrayHasKey('accepted_value', $kpi);
        $this->assertIsInt($kpi['quotations_sent']);
        $this->assertIsInt($kpi['quotations_accepted']);
    }

    public function test_get_kpi_daily_rows_includes_total_row(): void
    {
        $service = app(QuotationStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->startOfMonth()->addDays(2);

        $rows = $service->getKpiDailyRows($start, $end);

        $this->assertNotEmpty($rows);
        $this->assertSame('TOTAL', end($rows)['date']);
        $this->assertArrayHasKey('quotations_sent', $rows[0]);
        $this->assertArrayHasKey('success_rate_percent', $rows[0]);
    }
}
