<?php

namespace Tests\Unit\Batch\Tabs;

use App\Livewire\Batch\Tabs\Samples;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SampleParametersModalHelpersTest extends TestCase
{
    #[Test]
    public function it_normalizes_equipment_ids_from_array_json_and_fallback(): void
    {
        $helper = new class extends Samples
        {
            public function normalize(mixed $equipmentIds, mixed $fallback = null): array
            {
                return $this->normalizeEquipmentIds($equipmentIds, $fallback);
            }

            public function group(array $parametersForm): array
            {
                $this->parametersForm = $parametersForm;

                return $this->groupedParametersForm;
            }
        };

        $this->assertSame(
            ['eq-1', 'eq-2'],
            $helper->normalize(['eq-1', 'eq-2', '', null])
        );

        $this->assertSame(
            ['eq-9'],
            $helper->normalize(null, 'eq-9')
        );

        $this->assertSame(
            ['eq-3', 'eq-4'],
            $helper->normalize('["eq-3","eq-4"]')
        );

        $grouped = $helper->group([
            'a' => ['analysis_type' => 'Food and feed', 'analyte_name' => 'Fat'],
            'b' => ['analysis_type' => 'Water', 'analyte_name' => 'pH'],
            'c' => ['analysis_type' => 'Food and feed', 'analyte_name' => 'Protein'],
        ]);

        $this->assertSame(['Food and feed', 'Water'], array_keys($grouped));
        $this->assertCount(2, $grouped['Food and feed']);
        $this->assertCount(1, $grouped['Water']);
    }

    #[Test]
    public function it_exposes_editable_parameter_flags_from_can_edit(): void
    {
        $helper = new class extends Samples
        {
            public function bootFlags(array $parametersForm, bool $readOnly = false): void
            {
                $this->parametersForm = $parametersForm;
                $this->parametersReadOnly = $readOnly;
            }
        };

        $helper->bootFlags([
            'a' => ['can_edit' => true, 'analyte_name' => 'A'],
            'b' => ['can_edit' => false, 'analyte_name' => 'B'],
        ]);

        $this->assertTrue($helper->userCanEditParameterRow('a'));
        $this->assertFalse($helper->userCanEditParameterRow('b'));
        $this->assertTrue($helper->hasEditableParameters);
        $this->assertTrue($helper->hasNonEditableParameters);

        $helper->bootFlags([
            'a' => ['can_edit' => false],
            'b' => ['can_edit' => false],
        ]);

        $this->assertFalse($helper->hasEditableParameters);
        $this->assertTrue($helper->hasNonEditableParameters);

        $helper->bootFlags([
            'a' => ['can_edit' => true],
        ], true);

        $this->assertFalse($helper->hasEditableParameters);
        $this->assertFalse($helper->hasNonEditableParameters);
    }
}
