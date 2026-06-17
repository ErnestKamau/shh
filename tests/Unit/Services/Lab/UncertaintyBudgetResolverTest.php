<?php

namespace Tests\Unit\Services\Lab;

use App\AnalysisElements;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\UncertaintyBudget;
use Tests\TestCase;

class UncertaintyBudgetResolverTest extends TestCase
{

    public function test_format_loq_trims_trailing_zeros(): void
    {
        $element = new AnalysisElements(['lod' => 0.001000]);
        $resolver = app(UncertaintyBudgetResolver::class);

        $this->assertSame('0.001', $resolver->formatLoq($element));
    }

    public function test_format_mu_percent_prefers_expanded_uncertainty_from_budget(): void
    {
        $element = new AnalysisElements([
            'analyte_id' => 'analyte-1',
            'method' => 'method-1',
            'measurement_uncertainty' => 5.5,
        ]);

        $budget = new UncertaintyBudget([
            'analyte_id' => 'analyte-1',
            'method_ids' => 'method-1',
            'expanded_uncertainty' => 0.12,
            'active' => true,
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);

        $this->assertSame('0.12', $resolver->formatMuPercent($element, $budget));
    }

    public function test_format_mu_percent_falls_back_to_element_measurement_uncertainty(): void
    {
        $element = new AnalysisElements([
            'measurement_uncertainty' => 3.25,
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);

        $this->assertSame('3.25', $resolver->formatMuPercent($element, null));
    }

    public function test_enrich_lines_with_lab_metrics_adds_loq_and_mu_percent_keys(): void
    {
        $resolver = app(UncertaintyBudgetResolver::class);

        $lines = $resolver->enrichLinesWithLabMetrics([
            [
                'analysis_element_id' => null,
                'parameter_label' => 'Generic',
            ],
        ]);

        $this->assertArrayHasKey('loq', $lines[0]);
        $this->assertArrayHasKey('mu_percent', $lines[0]);
    }
}
