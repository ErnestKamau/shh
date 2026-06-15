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

        $this->syncSampleTypesByNamePatterns($form, ['food', 'FOOD', 'SMP-FOOD', 'Food Product Compliance']);

        $this->createCustomerDetailsSection($form, 1);
        $this->createCollectionDataSection($form, 2, true, [
            ['value' => 'air_sampler', 'label' => 'Air sampler'],
        ]);

        $this->createSampleRowsSection($form, 3, 'Test & sample information', [
            ['text', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling point / location', 'sampling_point', 2],
            ['number', 'Qty', 'number_of_samples', 3],
            ['sample_type_select', 'Type of sample', 'sample_type_id', 4, null, true],
            ['analysis_type_select', 'Sample type', 'analysis_type_id', 5, null, true],
            ['analysis_elements_select', 'Parameters', 'parameters', 6],
            ['text', 'Sample no.', 'lims_sample_no', 7, null, false, true],
            ['text', 'Sample temp (°C)', 'field_sample_temp', 8],
            ['date', 'Production date', 'production_date', 9],
            ['date', 'Expiration date', 'expiration_date', 10],
            ['text', 'Batch number', 'batch_number', 11],
            ['radio', 'State of sample', 'state_of_sample', 12, [
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
