<?php

namespace Tests\Unit\Imports;

use App\Imports\Lab\UnifiedLabHierarchyImporter;
use App\Models\BulkImportBatch;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class UnifiedLabHierarchyImporterTransformTest extends TestCase
{
    private function importer(): UnifiedLabHierarchyImporter
    {
        $batch = new BulkImportBatch([
            'id' => (string) Str::uuid(),
            'company_id' => (string) Str::uuid(),
            'user_id' => (string) Str::uuid(),
            'module' => 'lab',
            'form_type' => 'lab_hierarchy',
            'status' => 'processing',
        ]);

        return new UnifiedLabHierarchyImporter($batch);
    }

    private function invokeTransform(UnifiedLabHierarchyImporter $importer, array $row): array
    {
        $method = new ReflectionMethod($importer, 'transformRow');
        $method->setAccessible(true);

        return $method->invoke($importer, $row);
    }

    private function invokeParseBoolean(UnifiedLabHierarchyImporter $importer, mixed $value, int $default = 0): int
    {
        $method = new ReflectionMethod($importer, 'parseBooleanCell');
        $method->setAccessible(true);

        return $method->invoke($importer, $value, $default);
    }

    public function test_transform_maps_loq_column_to_hod(): void
    {
        $importer = $this->importer();

        $transformed = $this->invokeTransform($importer, [
            'sample_type_code' => 'Food & Feed',
            'analysis_type_code' => 'Dairy',
            'analyte_name' => 'pH Level',
            'lab_section_code' => 'Microbiology',
            'LOQ' => '10',
        ]);

        $this->assertSame('10', $transformed['hod']);
        $this->assertFalse($transformed['analyte_code_explicit']);
        $this->assertNotEmpty($transformed['analyte_code']);
    }

    public function test_transform_generates_analyte_code_from_name_when_missing(): void
    {
        $importer = $this->importer();

        $transformed = $this->invokeTransform($importer, [
            'sample_type_code' => 'Food & Feed',
            'analysis_type_code' => 'Dairy',
            'analyte_name' => 'Mesophilic Aerobic Plate Count in Food Samples',
            'lab_section_code' => 'Microbiology',
        ]);

        $this->assertSame('MESOPHILIC_AEROBIC_PLATE_COUNT_IN_FOOD_SAMPLES', $transformed['analyte_code']);
    }

    public function test_parse_boolean_cell_handles_yes_no_strings(): void
    {
        $importer = $this->importer();

        $this->assertSame(0, $this->invokeParseBoolean($importer, 'No'));
        $this->assertSame(1, $this->invokeParseBoolean($importer, 'Yes'));
        $this->assertSame(0, $this->invokeParseBoolean($importer, 'false'));
    }

    public function test_transform_marks_explicit_analyte_code(): void
    {
        $importer = $this->importer();

        $transformed = $this->invokeTransform($importer, [
            'sample_type_code' => 'ST-WATER',
            'analysis_type_code' => 'AT-POTABLE',
            'analyte_code' => 'AN-PH',
            'analyte_name' => 'pH Level',
            'lab_section_code' => 'LS-PHYSCHEM',
        ]);

        $this->assertTrue($transformed['analyte_code_explicit']);
        $this->assertSame('AN-PH', $transformed['analyte_code']);
    }

    public function test_transform_humanizes_snake_and_kebab_names(): void
    {
        $importer = $this->importer();

        $transformed = $this->invokeTransform($importer, [
            'sample_type_code' => 'food_and_feed',
            'sample_type_name' => 'food_and_feed',
            'analysis_type_code' => 'general-foods',
            'analysis_type_name' => 'general-foods',
            'analyte_name' => 'moisture_and_water',
            'lab_section_code' => 'chemistry',
            'method' => 'iso-4833-1',
            'unit' => 'CFU/g',
        ]);

        $this->assertSame('Food and Feed', $transformed['sample_type_name']);
        $this->assertSame('General Foods', $transformed['analysis_type_name']);
        $this->assertSame('Moisture and Water', $transformed['analyte_name']);
        $this->assertSame('iso-4833-1', $transformed['method']);
        $this->assertSame('CFU/g', $transformed['reporting_unit']);
        $this->assertSame('MOISTURE_AND_WATER', $transformed['analyte_code']);
    }

    public function test_transform_maps_method_and_unit_aliases(): void
    {
        $importer = $this->importer();

        $transformed = $this->invokeTransform($importer, [
            'sample_type_code' => 'Food',
            'analysis_type_code' => 'Dairy',
            'analyte_name' => 'pH Level',
            'lab_section_code' => 'Microbiology',
            'test_method' => 'Electrometric Method',
            'reporting_unit' => 'units',
        ]);

        $this->assertSame('Electrometric Method', $transformed['method']);
        $this->assertSame('units', $transformed['reporting_unit']);
    }
}
