<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Dashboards\LabTatDashboardService;
use Livewire\Livewire;
use App\Livewire\Mas\LabTat;
use Illuminate\Support\Facades\DB;

class LabTatDashboardTest extends TestCase
{
    protected LabTatDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LabTatDashboardService::class);
    }

    public function test_get_lab_section_options_returns_the_seven_seeded_sections(): void
    {
        $options = $this->service->getLabSectionOptions();

        $this->assertCount(7, $options);
        $this->assertEquals('LAB-FCH', $options[0]['id']);
        $this->assertEquals('LAB-FDNA', $options[1]['id']);
        $this->assertEquals('LAB-FTOX', $options[2]['id']);
    }

    public function test_get_zone_options_returns_the_six_seeded_zones(): void
    {
        $options = $this->service->getZoneOptions();

        $this->assertCount(6, $options);
        $keys = array_column(DB::table('zones')->select('value')->orderBy('value')->get()->toArray(), 'value');
        $this->assertEquals($keys[0], $options[0]['name']);
    }

    public function test_get_available_analysts_returns_only_seeded_analysts(): void
    {
        $analysts = $this->service->getAvailableAnalysts();

        $this->assertNotEmpty($analysts);
        
        // Assert every analyst has an email matching @gcla-labs.com in users table
        foreach ($analysts as $analyst) {
            $user = DB::table('users')->where('id', $analyst['analyst_id'])->first();
            $this->assertNotNull($user);
            $this->assertStringContainsString('@gcla-labs.com', $user->email);
        }
    }

    public function test_get_lab_section_tat_stats_generates_correct_leaderboard_and_trends(): void
    {
        $stats = $this->service->getLabSectionTatStats();

        $this->assertArrayHasKey('leaderboard', $stats);
        $this->assertArrayHasKey('trends', $stats);
        
        $this->assertCount(7, $stats['leaderboard']);
        $this->assertCount(7, $stats['trends']['series']);
        $this->assertCount(6, $stats['trends']['labels']);
    }

    public function test_get_tat_analysis_payload_executes_without_db_exceptions(): void
    {
        $filters = [
            'lab_id' => null,
            'zone_id' => null,
            'analyst_id' => null,
            'start_date' => null,
            'end_date' => null,
        ];

        $payload = $this->service->getTatAnalysisPayload($filters, 'my_tasks', 1, 10, 'active', 1, 20);

        $this->assertArrayHasKey('summary', $payload);
        $this->assertArrayHasKey('pivot', $payload);
        $this->assertArrayHasKey('detailed_logs', $payload);

        // Verify compliance rows are populated correctly
        $this->assertArrayHasKey('compliance_rows', $payload['pivot']);
        $this->assertCount(7, $payload['pivot']['compliance_rows']);
        $this->assertEquals('Forensic Chemistry Lab', $payload['pivot']['compliance_rows'][0]['section']);

        // Verify pivot pagination fields
        $this->assertArrayHasKey('total', $payload['pivot']);
        $this->assertEquals(70, $payload['pivot']['total']);
        $this->assertEquals(1, $payload['pivot']['page']);
        $this->assertEquals(7, $payload['pivot']['total_pages']);
    }

    public function test_get_tat_analysis_payload_filters_by_analyst(): void
    {
        $analyst = DB::table('users')->where('email', 'like', '%@gcla-labs.com')->first();
        $this->assertNotNull($analyst);

        $filters = [
            'lab_id' => null,
            'zone_id' => null,
            'analyst_id' => $analyst->id,
            'start_date' => null,
            'end_date' => null,
        ];

        $payload = $this->service->getTatAnalysisPayload($filters, 'my_tasks', 1, 10, 'active', 1, 20);

        // Verify the number of pivot total parameters is restricted strictly to what the analyst has tested
        $testedCount = DB::table('tat_captured_view')
            ->where('analyst_id', $analyst->id)
            ->where('finished_date', '>=', now()->subDays(90))
            ->distinct('analyte_id')
            ->count('analyte_id');

        $this->assertEquals($testedCount, $payload['pivot']['total']);
    }

    public function test_get_tat_analysis_payload_dynamic_compliance_grouping(): void
    {
        // 1. Lab Section filter active, no Zone filter (Case C) -> should group by 6 zones
        $filters = [
            'lab_id' => 'LAB-FCH',
            'zone_id' => null,
            'analyst_id' => null,
            'start_date' => null,
            'end_date' => null,
        ];

        $payload = $this->service->getTatAnalysisPayload($filters, 'my_tasks', 1, 10, 'active', 1, 20);
        $this->assertCount(6, $payload['pivot']['compliance_rows']);
        $this->assertEquals('Central Zone', $payload['pivot']['compliance_rows'][0]['section']);

        // 2. Both Lab Section and Zone active (Case D) -> should group by the 10 parameters
        $zoneId = DB::table('zones')->orderBy('value')->value('id');
        $filters = [
            'lab_id' => 'LAB-FCH',
            'zone_id' => $zoneId,
            'analyst_id' => null,
            'start_date' => null,
            'end_date' => null,
        ];

        $payload = $this->service->getTatAnalysisPayload($filters, 'my_tasks', 1, 10, 'active', 1, 20);
        $this->assertCount(10, $payload['pivot']['compliance_rows']);
    }

    public function test_lab_tat_stage_summary_exposes_completion_status_metrics(): void
    {
        $payload = $this->service->getLabTatBoard();

        $this->assertArrayHasKey('completed_batches', $payload['summary']);

        foreach ($payload['stage_summary'] as $stage) {
            $expectedCompleted = max($stage['total_batches'] - $stage['overdue_batches'], 0);
            $expectedRate = $stage['total_batches'] > 0
                ? (int) round(($expectedCompleted / $stage['total_batches']) * 100)
                : 0;

            $this->assertArrayHasKey('completed_batches', $stage);
            $this->assertArrayHasKey('completion_rate', $stage);
            $this->assertSame($expectedCompleted, $stage['completed_batches']);
            $this->assertSame($expectedRate, $stage['completion_rate']);
        }
    }
}
