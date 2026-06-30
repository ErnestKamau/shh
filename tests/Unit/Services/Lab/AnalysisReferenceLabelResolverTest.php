<?php

namespace Tests\Unit\Services\Lab;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
use App\Services\Lab\AnalysisReferenceLabelResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalysisReferenceLabelResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_mixed_maps_comma_separated_analysis_element_ids_to_analyte_names(): void
    {
        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food Microbiology',
            'active' => 1,
        ]);

        $analyteA = Analyte::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Salmonella',
            'code' => 'SAL',
            'active' => 1,
        ]);

        $analyteB = Analyte::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'E. coli',
            'code' => 'ECO',
            'active' => 1,
        ]);

        $elementA = AnalysisElements::query()->create([
            'id' => (string) Str::uuid7(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyteA->id,
            'active' => 1,
        ]);

        $elementB = AnalysisElements::query()->create([
            'id' => (string) Str::uuid7(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyteB->id,
            'active' => 1,
        ]);

        $resolver = app(AnalysisReferenceLabelResolver::class);

        $resolved = $resolver->resolveMixed($elementA->id.','.$elementB->id);

        $this->assertSame('Salmonella, E. coli', $resolved);
    }
}
