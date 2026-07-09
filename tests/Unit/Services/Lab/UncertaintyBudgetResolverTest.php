<?php

namespace Tests\Unit\Services\Lab;

use App\AnalysisElements;
use App\CapturedResult;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\UncertaintyBudget;
use Illuminate\Support\Str;
use Tests\TestCase;

class UncertaintyBudgetResolverTest extends TestCase
{

    public function test_format_loq_trims_trailing_zeros(): void
    {
        $element = new AnalysisElements(['lod' => 0.001000]);
        $resolver = app(UncertaintyBudgetResolver::class);

        $this->assertSame('0.001', $resolver->formatLoq($element));
    }

    public function test_format_loq_prefers_hod_over_lod(): void
    {
        $element = new AnalysisElements([
            'lod' => 0.001,
            'hod' => 10,
        ]);
        $resolver = app(UncertaintyBudgetResolver::class);

        $this->assertSame('10', $resolver->formatLoq($element));
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
        $this->assertSame('', $lines[0]['loq']);
        $this->assertSame('', $lines[0]['mu_percent']);
    }

    public function test_resolve_for_element_matches_budget_by_analyte_and_method_ids(): void
    {
        $analyteId = (string) \Illuminate\Support\Str::uuid();
        $methodId = (string) \Illuminate\Support\Str::uuid();

        $element = new AnalysisElements([
            'analyte_id' => $analyteId,
            'method' => $methodId,
            'lod' => 0.01,
        ]);

        $budget = new UncertaintyBudget([
            'analyte_id' => $analyteId,
            'method_ids' => $methodId.',other-method',
            'expanded_uncertainty' => 2.5,
            'active' => true,
            'version_number' => 1,
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);

        $this->assertSame('2.5', $resolver->formatMuPercent($element, $budget));
    }

    public function test_enrich_lines_with_lab_metrics_populates_mu_when_element_and_budget_exist(): void
    {
        $elementId = (string) \Illuminate\Support\Str::uuid();
        $analyteId = (string) \Illuminate\Support\Str::uuid();
        $methodId = (string) \Illuminate\Support\Str::uuid();

        \App\Analyte::query()->create([
            'id' => $analyteId,
            'code' => 'FE',
            'name' => 'Iron',
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $elementId,
            'analyte_id' => $analyteId,
            'method' => $methodId,
            'lod' => 0.005,
            'active' => 1,
        ]);

        UncertaintyBudget::query()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'analyte_id' => $analyteId,
            'method_ids' => $methodId,
            'expanded_uncertainty' => 1.75,
            'active' => true,
            'version_number' => 1,
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);
        $lines = $resolver->enrichLinesWithLabMetrics([
            [
                'analysis_element_id' => $elementId,
                'parameter_label' => 'Iron',
            ],
        ]);

        $this->assertSame('0.005', $lines[0]['loq']);
        $this->assertSame('1.75', $lines[0]['mu_percent']);
    }

    public function test_enrich_lines_matches_budget_when_ltm_method_differs_from_budget_method(): void
    {
        $elementId = (string) \Illuminate\Support\Str::uuid();
        $analyteId = (string) Str::uuid();
        $budgetMethodId = (string) Str::uuid();
        $ltmMethodId = (string) Str::uuid();

        \App\Analyte::query()->create([
            'id' => $analyteId,
            'code' => 'TVC',
            'name' => 'Total viable count',
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $elementId,
            'analyte_id' => $analyteId,
            'ltm_method_id' => $ltmMethodId,
            'method' => $budgetMethodId,
            'lod' => 10,
            'active' => 1,
        ]);

        UncertaintyBudget::query()->create([
            'id' => (string) Str::uuid(),
            'analyte_id' => $analyteId,
            'method_ids' => $budgetMethodId,
            'expanded_uncertainty' => 2.0,
            'active' => true,
            'version_number' => 1,
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);
        $lines = $resolver->enrichLinesWithLabMetrics([
            [
                'analysis_element_id' => $elementId,
                'parameter_label' => 'Total viable count',
            ],
        ]);

        $this->assertSame('10', $lines[0]['loq']);
        $this->assertSame('2', $lines[0]['mu_percent']);
    }

    public function test_enrich_lines_falls_back_to_sibling_element_for_loq(): void
    {
        $sparseElementId = (string) Str::uuid();
        $richElementId = (string) Str::uuid();
        $analyteId = (string) Str::uuid();

        \App\Analyte::query()->create([
            'id' => $analyteId,
            'code' => 'BA',
            'name' => 'Barium',
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $sparseElementId,
            'analyte_id' => $analyteId,
            'analysis_type_id' => (string) Str::uuid(),
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $richElementId,
            'analyte_id' => $analyteId,
            'analysis_type_id' => (string) Str::uuid(),
            'hod' => 0.01,
            'active' => 1,
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);
        $lines = $resolver->enrichLinesWithLabMetrics([
            [
                'analysis_element_id' => $sparseElementId,
                'parameter_label' => 'Barium',
            ],
        ]);

        $this->assertSame('0.01', $lines[0]['loq']);
        $this->assertArrayHasKey('test_method', $lines[0]);
    }

    public function test_format_mu_percent_for_captured_result_prefers_manual_value(): void
    {
        $captured = new CapturedResult([
            'measure_uncertanity' => '4.5',
            'analyte_id' => (string) Str::uuid(),
            'analysis_type_id' => (string) Str::uuid(),
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);

        $this->assertSame('4.5', $resolver->formatMuPercentForCapturedResult($captured));
    }

    public function test_build_mu_percent_index_for_captured_results_resolves_from_budget(): void
    {
        $elementId = (string) Str::uuid();
        $analyteId = (string) Str::uuid();
        $methodId = (string) Str::uuid();
        $capturedId = (string) Str::uuid();

        \App\Analyte::query()->create([
            'id' => $analyteId,
            'code' => 'MOIST',
            'name' => 'Moisture',
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $elementId,
            'analyte_id' => $analyteId,
            'analysis_type_id' => (string) Str::uuid(),
            'method' => $methodId,
            'measurement_uncertainty' => 1.5,
            'active' => 1,
        ]);

        UncertaintyBudget::query()->create([
            'id' => (string) Str::uuid(),
            'analyte_id' => $analyteId,
            'method_ids' => $methodId,
            'expanded_uncertainty' => 2.25,
            'active' => true,
            'version_number' => 1,
        ]);

        $captured = new CapturedResult([
            'id' => $capturedId,
            'analysis_element_id' => $elementId,
            'analyte_id' => $analyteId,
            'analysis_type_id' => (string) Str::uuid(),
            'measure_uncertanity' => 0,
        ]);

        $resolver = app(UncertaintyBudgetResolver::class);
        $index = $resolver->buildMuPercentIndexForCapturedResults([$captured]);

        $this->assertSame('2.25', $index[$capturedId] ?? null);
    }
}
