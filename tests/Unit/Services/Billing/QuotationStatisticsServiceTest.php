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
}
