<?php

namespace Tests\Unit\Livewire\Billing;

use App\Livewire\Billing\CreateEnquiryFromQuotationWizard;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CreateEnquiryFromQuotationWizardTest extends TestCase
{
    public function test_it_exports_row_field_values_for_selected_rows_section(): void
    {
        $component = new CreateEnquiryFromQuotationWizard;
        $component->trfGroups = [[
            'sample_type_id' => 'type-1',
            'sections' => [[
                'id' => 'section-1',
                'section_type' => 'rows_section',
                'fields' => [
                    ['name' => 'sample_quantity'],
                    ['name' => 'sample_quantity_unit'],
                    ['name' => 'sampling_point'],
                ],
            ]],
        ]];
        $component->selectedSectionIdsByType = ['type-1' => ['section-1']];
        $component->sectionRowFieldValuesByType = [
            'type-1' => [
                0 => ['sample_quantity' => '5', 'sampling_point' => 'Tap'],
                1 => ['sample_quantity' => '10', 'sampling_point' => 'Tank'],
            ],
        ];
        $component->numberOfSamples = 2;
        $component->isMultiSampleType = false;

        $method = new ReflectionMethod($component, 'valuesForSelectedSectionsByType');
        $method->setAccessible(true);
        $payload = $method->invoke($component);

        $this->assertSame('5', $payload['type-1'][0]['sample_quantity'] ?? null);
        $this->assertSame('Tap', $payload['type-1'][0]['sampling_point'] ?? null);
        $this->assertSame('10', $payload['type-1'][1]['sample_quantity'] ?? null);
        $this->assertSame('Tank', $payload['type-1'][1]['sampling_point'] ?? null);
    }
}
