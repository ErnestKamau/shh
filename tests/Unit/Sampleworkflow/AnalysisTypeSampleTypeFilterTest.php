<?php

namespace Tests\Unit\Sampleworkflow;

use App\Services\Sampleworkflow\AnalysisTypeOptionFilter;
use PHPUnit\Framework\TestCase;

class AnalysisTypeSampleTypeFilterTest extends TestCase
{
    public function test_filters_analysis_types_to_matching_sample_type_only(): void
    {
        $types = [
            ['id' => 'at-1', 'name' => 'Chemical', 'code' => 'Chemical', 'sample_type_id' => 'st-nonseafood'],
            ['id' => 'at-2', 'name' => 'Microbiological', 'code' => 'Micro', 'sample_type_id' => 'st-nonseafood'],
            ['id' => 'at-3', 'name' => 'Microbiology', 'code' => 'Microbiology', 'sample_type_id' => 'st-water'],
            ['id' => 'at-4', 'name' => 'Chemical', 'code' => 'Chemical', 'sample_type_id' => 'st-seafood'],
        ];

        $filtered = (new AnalysisTypeOptionFilter)->forSampleType($types, 'st-nonseafood');

        $this->assertCount(2, $filtered);
        $this->assertSame(['at-1', 'at-2'], array_column($filtered, 'id'));
    }

    public function test_returns_empty_when_sample_type_missing(): void
    {
        $types = [
            ['id' => 'at-1', 'name' => 'Chemical', 'code' => 'Chemical', 'sample_type_id' => 'st-nonseafood'],
        ];

        $this->assertSame([], (new AnalysisTypeOptionFilter)->forSampleType($types, null));
        $this->assertSame([], (new AnalysisTypeOptionFilter)->forSampleType($types, ''));
    }

    public function test_applies_search_within_sample_type_scope(): void
    {
        $types = [
            ['id' => 'at-1', 'name' => 'Chemical', 'code' => 'Chemical', 'sample_type_id' => 'st-nonseafood'],
            ['id' => 'at-2', 'name' => 'Halal', 'code' => 'Halal', 'sample_type_id' => 'st-nonseafood'],
            ['id' => 'at-3', 'name' => 'Chemical', 'code' => 'Chemical', 'sample_type_id' => 'st-seafood'],
        ];

        $filtered = (new AnalysisTypeOptionFilter)->forSampleType($types, 'st-nonseafood', 'hal');

        $this->assertCount(1, $filtered);
        $this->assertSame('at-2', $filtered[0]['id']);
    }
}
