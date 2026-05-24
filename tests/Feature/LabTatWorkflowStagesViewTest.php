<?php

namespace Tests\Feature;

use Tests\TestCase;

class LabTatWorkflowStagesViewTest extends TestCase
{
    public function test_workflow_stage_status_shows_completed_batches_over_total_batches(): void
    {
        $stats = [
            'summary' => [
                'active_batches' => 10,
                'completed_batches' => 8,
                'overdue_batches' => 2,
            ],
            'stage_summary' => [
                [
                    'workflow_stage' => 'Testing',
                    'total_batches' => 10,
                    'completed_batches' => 8,
                    'completion_rate' => 80,
                    'overdue_batches' => 2,
                    'due_today_batches' => 1,
                    'avg_completion_days' => 2.5,
                ],
            ],
        ];

        $this->blade(
            "@include('livewire.mas.lab._workflow_stages', ['stats' => \$stats])",
            ['stats' => $stats]
        )
            ->assertSee('8 completed')
            ->assertSee('8/10 (80%)')
            ->assertDontSee('2/10 (20%)');
    }

    public function test_lab_tat_pdf_report_shows_completed_batches_over_total_batches_status(): void
    {
        config(['translation-loader.translation_loaders' => []]);

        $stats = $this->reportStats();

        $html = view('layouts.mas.pdf.lab_pdf', [
            'stats' => $stats,
            'chartImage' => null,
            'company' => (object) ['name' => 'Test Company', 'logo' => null],
            'selectedFilters' => [
                'lab_section' => 'All Sections',
                'zone' => 'All Zones',
                'analyst' => 'All Analysts',
                'start_date' => '12 Months',
                'end_date' => 'Present',
            ],
        ])->render();

        $this->assertStringContainsString('Workflow Stage Pipeline', $html);
        $this->assertStringContainsString('8/10 (80%)', $html);
        $this->assertStringContainsString('116/192 within TAT (60%)', $html);
        $this->assertStringContainsString('Tests Completed', $html);
        $this->assertStringContainsString('TAT Status', $html);
        $this->assertStringNotContainsString('2/10 (20%)', $html);
        $this->assertStringNotContainsString('76 OVERDUE', $html);
    }

    public function test_lab_general_pdf_report_shows_completed_batches_over_total_batches_status(): void
    {
        config(['translation-loader.translation_loaders' => []]);

        $stats = $this->reportStats();
        $stats['summary']['tests_completed'] = 8;
        $stats['monthly_trends'] = ['labels' => [], 'data' => []];
        $stats['geographic_data'] = [];
        $stats['top_clients'] = [];

        $html = view('layouts.mas.pdf.lab_general_pdf', [
            'stats' => $stats,
            'chartImage' => null,
            'company' => (object) ['name' => 'Test Company', 'logo' => null],
            'selectedFilters' => [
                'start_date' => '12 Months',
                'end_date' => 'Present',
            ],
        ])->render();

        $this->assertStringContainsString('8/10 (80%)', $html);
        $this->assertStringNotContainsString('2/10 (20%)', $html);
    }

    private function reportStats(): array
    {
        return [
            'summary' => [
                'active_batches' => 10,
                'completed_batches' => 8,
                'overdue_batches' => 2,
                'due_today_batches' => 1,
            ],
            'testing_metrics' => [
                'total_params' => 0,
                'tested_vs_requested' => 0,
                'tat_compliance_tes' => 0,
                'avg_tat_tes' => 0,
                'avg_delivery_tat' => 0,
                'delivery_compliance' => 0,
            ],
            'stage_summary' => [
                [
                    'workflow_stage' => 'Testing',
                    'total_batches' => 10,
                    'completed_batches' => 8,
                    'completion_rate' => 80,
                    'overdue_batches' => 2,
                    'due_today_batches' => 1,
                    'avg_days_to_target' => 3.2,
                    'avg_completion_days' => 2.5,
                ],
            ],
            'pivot' => [
                'total' => 0,
                'headers' => [],
                'rows' => [],
                'row_label' => 'Analyte',
            ],
            'aging_buckets' => [],
            'charts' => [
                'throughput_labels' => [],
                'throughput_counts' => [],
            ],
            'sections' => [
                'leaderboard' => [
                    [
                        'name' => 'Forensic Chemistry Lab',
                        'code' => 'LAB-FCH',
                        'total' => 192,
                        'avg_tat' => 1.9,
                        'overdue' => 76,
                        'within_tat' => 116,
                        'compliance_rate' => 60,
                    ],
                ],
                'trends' => ['labels' => [], 'series' => []],
            ],
            'analyst_performance' => [],
            'smart_grid' => [],
            'detailed_logs' => [
                'grouped_rows' => [],
                'total' => 0,
                'source_total' => 0,
                'is_capped' => false,
                'page' => 1,
                'total_pages' => 1,
            ],
        ];
    }
}
