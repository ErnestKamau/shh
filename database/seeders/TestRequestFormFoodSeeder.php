<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsTestRequestFormSections;
use Illuminate\Database\Seeder;

class TestRequestFormFoodSeeder extends Seeder
{
    use BuildsTestRequestFormSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTestRequestForm([
            'name' => 'Test Request Form - Food',
            'document_code' => 'TRF-FOOD-019',
            'description' => 'AmSpec LWS-019 test request form for food samples.',
            'naming_convention_prefix' => 'TRFF',
            'naming_convention_format' => 'TRFF-{YYYY}{MM}-{0000}',
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-food',
        ]);

        $this->syncSampleTypesByCodes($form, ['FOOD', 'FOOD FEED']);

        $this->createCustomerDetailsSection($form, 1);
        $this->createCollectionDataSection($form, 2, true, [
            ['value' => 'air_sampler', 'label' => 'Air sampler'],
        ]);

        $this->createSampleRowsSection($form, 3, 'Test & sample information', [
            ['text', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling point / location', 'sampling_point', 2],
            ['number', 'Qty', 'number_of_samples', 3],
            ['analysis_type_select', 'Analysis type', 'analysis_type_id', 4, null, true],
            ['analysis_elements_select', 'Parameters', 'parameters', 5],
            ['text', 'Sample temp (°C)', 'field_sample_temp', 6],
            ['date', 'Production date', 'production_date', 7],
            ['date', 'Expiration date', 'expiration_date', 8],
            ['text', 'Batch number', 'batch_number', 9],
            ['radio', 'State of sample', 'state_of_sample', 10, [
                ['value' => 'L', 'label' => 'L - Liquid'],
                ['value' => 'SS', 'label' => 'SS - Semi solid'],
                ['value' => 'S', 'label' => 'S - Solid'],
            ]],
        ]);

        $this->createSubmitAndSignSection($form, 4);
        $this->clearCaches();

        $this->command?->info('Test Request Form - Food seeded successfully.');
    }
}
