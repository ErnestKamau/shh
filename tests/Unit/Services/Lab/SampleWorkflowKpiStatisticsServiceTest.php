<?php

namespace Tests\Unit\Services\Lab;

use App\Services\Lab\SampleWorkflowKpiStatisticsService;
use Tests\TestCase;

class SampleWorkflowKpiStatisticsServiceTest extends TestCase
{
    public function test_overview_metrics_returns_expected_keys(): void
    {
        $service = app(SampleWorkflowKpiStatisticsService::class);

        $metrics = $service->getOverviewMetrics();

        $this->assertArrayHasKey('total', $metrics);
        $this->assertArrayHasKey('samples_receiving', $metrics);
        $this->assertArrayHasKey('request_review', $metrics);
        $this->assertArrayHasKey('samples_in_lab', $metrics);
        $this->assertArrayHasKey('verification', $metrics);
        $this->assertArrayHasKey('approval', $metrics);
        $this->assertArrayHasKey('completed', $metrics);
        $this->assertArrayHasKey('portal_submitted', $metrics);
        $this->assertArrayHasKey('ready_for_reception', $metrics);
        $this->assertArrayHasKey('this_month_registered', $metrics);
        $this->assertArrayHasKey('as_of', $metrics);
        $this->assertIsInt($metrics['total']);
        $this->assertIsString($metrics['as_of']);
    }

    public function test_registration_period_metrics_returns_spec_fields(): void
    {
        $service = app(SampleWorkflowKpiStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $kpi = $service->getRegistrationPeriodMetrics($start, $end);

        $this->assertArrayHasKey('samples_scheduled', $kpi);
        $this->assertArrayHasKey('samples_collected', $kpi);
        $this->assertArrayHasKey('registration_rate_percent', $kpi);
        $this->assertArrayHasKey('clients_registered', $kpi);
        $this->assertIsInt($kpi['samples_scheduled']);
        $this->assertIsInt($kpi['samples_collected']);
    }

    public function test_laboratory_period_metrics_returns_spec_fields(): void
    {
        $service = app(SampleWorkflowKpiStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $kpi = $service->getLaboratoryPeriodMetrics($start, $end);

        $this->assertArrayHasKey('jobs_received', $kpi);
        $this->assertArrayHasKey('jobs_completed', $kpi);
        $this->assertArrayHasKey('jobs_pending', $kpi);
        $this->assertArrayHasKey('data_entry_complete', $kpi);
        $this->assertArrayHasKey('review_pending', $kpi);
        $this->assertArrayHasKey('review_approved', $kpi);
    }

    public function test_registration_daily_rows_includes_total_row(): void
    {
        $service = app(SampleWorkflowKpiStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->startOfMonth()->addDays(2);

        $rows = $service->getRegistrationDailyRows($start, $end);

        $this->assertNotEmpty($rows);
        $this->assertSame('TOTAL', end($rows)['date']);
        $this->assertArrayHasKey('samples_scheduled', $rows[0]);
        $this->assertArrayHasKey('registration_rate_percent', $rows[0]);
    }

    public function test_laboratory_daily_rows_includes_total_row(): void
    {
        $service = app(SampleWorkflowKpiStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->startOfMonth()->addDays(2);

        $rows = $service->getLaboratoryDailyRows($start, $end);

        $this->assertNotEmpty($rows);
        $this->assertSame('TOTAL', end($rows)['date']);
        $this->assertArrayHasKey('jobs_received', $rows[0]);
    }

    public function test_registration_detail_rows_have_expected_column_keys(): void
    {
        $service = app(SampleWorkflowKpiStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $rows = $service->getRegistrationDetailRows($start, $end);

        $this->assertIsArray($rows);

        if ($rows === []) {
            $this->assertTrue(true);

            return;
        }

        $expectedKeys = [
            'date',
            'client',
            'samples_scheduled_collected',
            'sampler',
            'equipment_id',
            'job_sample_id',
            'location',
            'sampling_points',
            'parameters',
            'temp',
            'units',
            'volume',
            'registered_by',
            'due_date',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $rows[0]);
        }
    }

    public function test_laboratory_detail_rows_have_expected_column_keys(): void
    {
        $service = app(SampleWorkflowKpiStatisticsService::class);
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $rows = $service->getLaboratoryDetailRows($start, $end);

        $this->assertIsArray($rows);

        if ($rows === []) {
            $this->assertTrue(true);

            return;
        }

        $expectedKeys = [
            'date',
            'client',
            'sample_details',
            'jobs_received_completed_pending',
            'data_entry_status',
            'review_approval_status',
            'final_reports',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $rows[0]);
        }
    }
}
