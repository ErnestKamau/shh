<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\Sampleworkflow\TrfSampleFieldMapper;
use PHPUnit\Framework\TestCase;

class TrfSampleFieldMapperScalarValueTest extends TestCase
{
    public function test_scalar_value_returns_first_non_empty_from_per_sample_array(): void
    {
        $mapper = new TrfSampleFieldMapper;

        $this->assertSame('Test', $mapper->scalarValue(['Test', 'Test']));
        $this->assertSame('Site B', $mapper->scalarValue(['', 'Site B']));
        $this->assertSame('plain', $mapper->scalarValue('plain'));
        $this->assertNull($mapper->scalarValue(null));
        $this->assertNull($mapper->scalarValue([]));
        $this->assertNull($mapper->scalarValue([['nested']]));
    }
}
