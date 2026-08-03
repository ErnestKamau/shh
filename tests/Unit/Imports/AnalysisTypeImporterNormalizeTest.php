<?php

namespace Tests\Unit\Imports;

use App\Imports\Lab\AnalysisTypeImporter;
use App\Models\BulkImportBatch;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class AnalysisTypeImporterNormalizeTest extends TestCase
{
    private function importer(): AnalysisTypeImporter
    {
        $batch = new BulkImportBatch([
            'id' => (string) Str::uuid(),
            'company_id' => (string) Str::uuid(),
            'user_id' => (string) Str::uuid(),
            'module' => 'lab',
            'form_type' => 'analysis_type',
            'status' => 'processing',
        ]);

        return new AnalysisTypeImporter($batch);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function invokeNormalize(AnalysisTypeImporter $importer, array $row): array
    {
        $method = new ReflectionMethod($importer, 'normalizeRow');
        $method->setAccessible(true);

        return $method->invoke($importer, $row);
    }

    public function test_normalize_fills_blank_analysis_type_from_sample_type(): void
    {
        $normalized = $this->invokeNormalize($this->importer(), [
            'sample_type' => 'Swab',
            'analysis_type' => '',
            'lab' => 'Amspec Dubai Lab',
            'test_method' => 'AMS/M/SOP/047',
        ]);

        $this->assertSame('Swab', $normalized['analysis_type']);
        $this->assertSame('Swab', $normalized['sample_type']);
    }

    public function test_normalize_keeps_explicit_analysis_type(): void
    {
        $normalized = $this->invokeNormalize($this->importer(), [
            'sample_type' => 'Food & Feed',
            'analysis_type' => 'Dairy',
            'lab' => 'Amspec Dubai Lab',
        ]);

        $this->assertSame('Dairy', $normalized['analysis_type']);
    }
}
