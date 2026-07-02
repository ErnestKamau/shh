<?php

namespace Tests\Unit\Services\Lab;

use App\Services\Lab\TatReportService;
use PHPUnit\Framework\TestCase;

class TatReportServiceTest extends TestCase
{
    private TatReportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TatReportService();
    }

    public function test_normalize_filters_strips_all_sentinel_values(): void
    {
        $normalized = $this->service->normalizeFilters([
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30',
            'user_id' => 'All',
            'sample_type_id' => 'All',
            'analysis_type_id' => 'All',
            'analyte_id' => 'All',
            'workflow_stage' => 'All',
            'deadline_status' => 'all',
            'tat_remark' => 'all',
        ]);

        $this->assertSame('2026-06-01', $normalized['date_from']);
        $this->assertSame('2026-06-30', $normalized['date_to']);
        $this->assertNull($normalized['user_id']);
        $this->assertNull($normalized['sample_type_id']);
        $this->assertNull($normalized['analysis_type_id']);
        $this->assertNull($normalized['analyte_id']);
        $this->assertNull($normalized['workflow_stage']);
        $this->assertNull($normalized['deadline_status']);
        $this->assertNull($normalized['tat_remark']);
    }
}
