<?php

namespace Tests\Unit\Services\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\SampleType;
use App\Services\Billing\PricelistPackageImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PricelistPackageImportAnalyteResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_prefers_analyte_linked_to_target_sample_type_over_duplicate_labels(): void
    {
        $freshWater = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Fresh Water Import Test',
            'code' => 'FW-IMPORT-TEST',
            'active' => 1,
        ]);

        $condensateWater = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Condensate Water Import Test',
            'code' => 'CW-IMPORT-TEST',
            'active' => 1,
        ]);

        $genericAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Electrical conductivity',
            'code' => 'EC-GENERIC',
            'active' => 1,
        ]);

        $specificAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Electrical Conductivity @25°C',
            'code' => 'EC-25C',
            'active' => 1,
        ]);

        $condensateAnalysis = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Condensate Chemical',
            'sample_type_id' => $condensateWater->id,
            'active' => 1,
        ]);

        $freshWaterAnalysis = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Fresh Water Chemical',
            'sample_type_id' => $freshWater->id,
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $condensateAnalysis->id,
            'analyte_id' => $genericAnalyte->id,
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $freshWaterAnalysis->id,
            'analyte_id' => $specificAnalyte->id,
            'active' => 1,
        ]);

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-EC-TEST',
            'description' => 'Electrical conductivity import test',
            'active' => true,
            'is_master' => false,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-EC-TEST',
        ]);

        $result = app(PricelistPackageImportService::class)->persistRows($pricelist, [
            [
                'sample_type' => 'Fresh Water Import Test',
                'parameters' => 'Electrical Conductivity @25°C',
                'selling_price' => 120.0,
                'pricing_mode' => 'per_package',
                'is_package' => true,
            ],
        ]);

        $this->assertSame(0, $result['skipped']);
        $this->assertSame([], $result['warnings']);
        $this->assertSame(1, $result['created']);
        $this->assertCount(1, $result['items']);
    }

    public function test_import_does_not_match_analyte_from_other_sample_types_only(): void
    {
        $freshWater = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Fresh Water Only Test',
            'code' => 'FW-ONLY-TEST',
            'active' => 1,
        ]);

        $otherWater = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Condensate Water Only Test',
            'code' => 'CW-ONLY-TEST',
            'active' => 1,
        ]);

        $genericAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Electrical conductivity',
            'code' => 'EC-OTHER-ONLY',
            'active' => 1,
        ]);

        $otherAnalysis = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Other Chemical',
            'sample_type_id' => $otherWater->id,
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $otherAnalysis->id,
            'analyte_id' => $genericAnalyte->id,
            'active' => 1,
        ]);

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-EC-SKIP',
            'description' => 'Electrical conductivity skip test',
            'active' => true,
            'is_master' => false,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-EC-SKIP',
        ]);

        $result = app(PricelistPackageImportService::class)->persistRows($pricelist, [
            [
                'sample_type' => 'Fresh Water Only Test',
                'parameters' => 'Electrical Conductivity @25°C',
                'selling_price' => 120.0,
                'pricing_mode' => 'per_package',
                'is_package' => true,
            ],
        ]);

        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, $result['created']);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_import_persists_sample_type_panel_with_null_analysis_id_and_all_elements(): void
    {
        $freshWater = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Fresh Water Panel Test',
            'code' => 'FW-PANEL-TEST',
            'active' => 1,
        ]);

        $chemicalAnalysis = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Fresh Water Chemical Panel',
            'sample_type_id' => $freshWater->id,
            'active' => 1,
        ]);

        $microAnalysis = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Fresh Water Micro Panel',
            'sample_type_id' => $freshWater->id,
            'active' => 1,
        ]);

        $labels = ['pH', 'HPC', 'Legionella'];
        $elements = [];

        foreach ($labels as $index => $label) {
            $analyte = Analyte::query()->create([
                'id' => (string) Str::uuid(),
                'name' => $label,
                'code' => 'FW-'.$index,
                'active' => 1,
            ]);

            $elements[] = AnalysisElements::query()->create([
                'id' => (string) Str::uuid(),
                'analysis_type_id' => $index < 1 ? $chemicalAnalysis->id : $microAnalysis->id,
                'analyte_id' => $analyte->id,
                'active' => 1,
            ]);
        }

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-FW-PANEL',
            'description' => 'Fresh Water panel import test',
            'active' => true,
            'is_master' => false,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-FW-PANEL',
        ]);

        $result = app(PricelistPackageImportService::class)->persistRows($pricelist, [
            [
                'sample_type' => 'Fresh Water Panel Test',
                'parameters' => implode('; ', $labels),
                'cost_price' => 500.0,
                'selling_price' => 700.0,
                'tax' => 5,
                'pricing_mode' => 'per_package',
                'is_package' => true,
            ],
        ]);

        $this->assertSame(0, $result['skipped']);
        $this->assertSame(1, $result['created']);
        $this->assertCount(1, $result['items']);
        $this->assertSame(3, $result['items'][0]['element_count']);

        $item = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('sample_type_id', $freshWater->id)
            ->where('is_package', true)
            ->whereNull('analysis_id')
            ->first();

        $this->assertNotNull($item);
        $this->assertSame(700.0, (float) $item->selling_price);
        $this->assertTrue((bool) $item->vat);
        $this->assertCount(3, $item->fresh('packageElements')->packageElements);
    }

    public function test_import_maps_numeric_tax_to_vat_flag(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'VAT Import Sample',
            'code' => 'VAT-IMPORT',
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'VAT Import Analysis',
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'pH',
            'code' => 'PH-VAT',
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
        ]);

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-VAT-IMPORT',
            'description' => 'VAT import mapping test',
            'active' => true,
            'is_master' => false,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-VAT',
        ]);

        $service = app(PricelistPackageImportService::class);

        $service->persistRows($pricelist, [[
            'sample_type' => 'VAT Import Sample',
            'parameters' => 'pH',
            'selling_price' => 100.0,
            'tax' => 5,
            'pricing_mode' => 'per_package',
            'is_package' => true,
        ]]);

        $withVat = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('sample_type_id', $sampleType->id)
            ->first();

        $this->assertNotNull($withVat);
        $this->assertTrue((bool) $withVat->vat);

        $service->persistRows($pricelist, [[
            'sample_type' => 'VAT Import Sample',
            'parameters' => 'pH',
            'selling_price' => 100.0,
            'tax' => 0,
            'pricing_mode' => 'per_package',
            'is_package' => true,
        ]]);

        $this->assertFalse((bool) $withVat->fresh()->vat);

        $service->persistRows($pricelist, [[
            'sample_type' => 'VAT Import Sample',
            'parameters' => 'pH',
            'selling_price' => 100.0,
            'tax' => 'yes',
            'pricing_mode' => 'per_package',
            'is_package' => true,
        ]]);

        $this->assertTrue((bool) $withVat->fresh()->vat);
    }

    public function test_import_prefers_analysis_element_matching_method_hint(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Condensate Water Method Test',
            'code' => 'CW-METHOD-TEST',
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Condensate Chemical Method Test',
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Bromate',
            'code' => 'AMS_BROMATE',
            'active' => 1,
        ]);

        $wrongMethod = \App\AnalysisMethod::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Wrong Method',
            'code' => 'AMS/C/SOP/999',
            'active' => 1,
        ]);

        $correctMethod = \App\AnalysisMethod::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Bromate Method',
            'code' => 'AMS/C/SOP/054',
            'active' => 1,
        ]);

        $wrongElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'method' => $wrongMethod->id,
            'active' => 1,
        ]);

        $correctElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'method' => $correctMethod->id,
            'active' => 1,
        ]);

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-METHOD-HINT',
            'description' => 'Method hint import test',
            'active' => true,
            'is_master' => false,
            'status' => 'no-changes',
            'revision_number' => '1',
            'document_no' => 'DOC-METHOD',
        ]);

        $result = app(PricelistPackageImportService::class)->persistRows($pricelist, [
            [
                'sample_type' => 'Condensate Water Method Test',
                'parameters' => 'Bromate',
                'method_hints' => ['AMS-C-SOP-054'],
                'selling_price' => 5.0,
                'cost_price' => 5.0,
                'pricing_mode' => 'per_package',
                'is_package' => true,
            ],
        ]);

        $this->assertSame(0, $result['skipped']);
        $this->assertSame([], $result['warnings']);
        $this->assertSame(1, $result['created']);

        $item = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('sample_type_id', $sampleType->id)
            ->where('is_package', true)
            ->first();

        $this->assertNotNull($item);
        $linkedIds = $item->fresh('packageElements')->packageElements
            ->pluck('analysis_element_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->assertSame([(string) $correctElement->id], $linkedIds);
        $this->assertNotContains((string) $wrongElement->id, $linkedIds);
    }
}
