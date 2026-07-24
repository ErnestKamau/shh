<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Models\SubmissionFormElement;
use App\Services\Sampleworkflow\WalkInTrfFieldMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WalkInTrfFieldMapperTest extends TestCase
{
    private WalkInTrfFieldMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new WalkInTrfFieldMapper();
    }

    #[DataProvider('fieldOverrideProvider')]
    public function test_to_field_applies_walk_in_overrides(
        string $name,
        string $elementType,
        string $expectedType,
    ): void {
        $element = new SubmissionFormElement([
            'name' => $name,
            'label' => ucfirst(str_replace('_', ' ', $name)),
            'element_type' => $elementType,
            'is_required' => false,
            'options' => [
                ['value' => 'a', 'label' => 'A'],
            ],
        ]);

        $field = $this->mapper->toField($element);

        $this->assertSame($name, $field['name']);
        $this->assertSame($expectedType, $field['type']);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function fieldOverrideProvider(): array
    {
        return [
            'sampling time text becomes time' => ['sampling_time', 'text', 'time'],
            'method of sampling radio becomes checkbox' => ['method_of_sampling', 'radio', 'checkbox'],
            'sampling location stays crm select' => ['sampling_location', 'customer_sample_point_select', 'customer_sample_point_select'],
            'sampling point text becomes crm select' => ['sampling_point', 'text', 'customer_sample_point_select'],
            'sampling point select becomes crm select' => ['sampling_point', 'select', 'customer_sample_point_select'],
            'test category radio becomes checkbox' => ['test_category', 'radio', 'checkbox'],
            'test requirements radio becomes checkbox' => ['test_requirements', 'radio', 'checkbox'],
        ];
    }
}
