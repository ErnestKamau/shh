<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsTestRequestFormSections;
use Illuminate\Database\Seeder;

class TestRequestFormWasteWaterSeeder extends Seeder
{
    use BuildsTestRequestFormSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTestRequestForm([
            'name' => 'Test Request Form - Waste Water',
            'document_code' => 'TRF-WASTE-036',
            'description' => 'AmSpec LWS-036 test request form for waste water samples.',
            'naming_convention_prefix' => 'TRFWW',
            'naming_convention_format' => 'TRFWW-{YYYY}{MM}-{0000}',
        ]);

        $this->syncSampleTypesByNamePatterns($form, ['waste', 'effluent', 'wastewater', 'sewage']);

        $this->createCustomerDetailsSection($form, 1);

        $this->createCollectionDataSection($form, 2, true, [], [
            ['textarea', 'Sample & sampling point description', 'sample_sampling_point_description', 9],
            ['select', 'Sampling technique', 'sampling_technique', 10, [
                ['value' => 'grab', 'label' => 'Grab'],
                ['value' => 'composite', 'label' => 'Composite'],
            ]],
            ['select', 'Sampling source', 'sampling_source', 11, [
                ['value' => 'tank', 'label' => 'Tank'],
                ['value' => 'holding_tank', 'label' => 'Holding tank'],
            ]],
            ['select', 'Sample physical state', 'sample_physical_state', 12, [
                ['value' => 'liquid', 'label' => 'Liquid'],
                ['value' => 'semi_solid', 'label' => 'Semi solid'],
            ]],
        ]);

        $this->createSampleRowsSection($form, 3, 'Test & sample information', [
            ['text', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling point / location', 'sampling_point', 2],
            ['number', 'Qty', 'number_of_samples', 3],
            ['sample_type_select', 'Type of sample', 'sample_type_id', 4, null, true],
            ['analysis_type_select', 'Matrix', 'analysis_type_id', 5, null, true],
            ['analysis_elements_select', 'Parameters', 'parameters', 6],
            ['text', 'Field data - Appearance', 'field_appearance', 7],
            ['text', 'Field data - Color', 'field_color', 8],
            ['text', 'Field data - Odor', 'field_odor', 9],
            ['text', 'Field data - pH', 'field_ph', 10],
            ['text', 'Field data - Temperature (°C)', 'field_sample_temp', 11],
            ['text', 'Field data - Free chlorine', 'field_free_chlorine', 12],
            ['select', 'Sample condition', 'sample_condition', 13, [
                ['value' => 'acceptable', 'label' => 'Acceptable'],
                ['value' => 'chilled', 'label' => 'Chilled'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['select', 'State of sample', 'state_of_sample', 14, [
                ['value' => 'L', 'label' => 'L - Liquid'],
                ['value' => 'SS', 'label' => 'SS - Semi solid'],
                ['value' => 'S', 'label' => 'S - Solid'],
            ]],
            ['camera_photo', 'Picture of sample(s)', 'picture_of_samples', 15],
        ]);

        $this->createSubmitAndSignSection($form, 4);
        $this->clearCaches();

        $this->command?->info('Test Request Form - Waste Water seeded successfully.');
    }
}
