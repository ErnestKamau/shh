<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsTestRequestFormSections;
use Illuminate\Database\Seeder;

class TestRequestFormWaterSeeder extends Seeder
{
    use BuildsTestRequestFormSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTestRequestForm([
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
            'description' => 'AmSpec LWS-020 test request form for water samples.',
            'naming_convention_prefix' => 'TRFW',
            'naming_convention_format' => 'TRFW-{YYYY}{MM}-{0000}',
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-water',
        ]);

        $this->syncSampleTypesByNamePatterns($form, ['water', 'WTR', 'potable', 'SMP-WTR', 'Water Quality']);

        $this->createCustomerDetailsSection($form, 1);
        $this->createCollectionDataSection($form, 2, true);

        $this->createSampleRowsSection($form, 3, 'Test & sample information', [
            ['text', 'Sample description', 'sample_description', 1],
            ['text', 'Location', 'location', 2],
            ['number', 'Qty', 'number_of_samples', 3],
            ['analysis_type_select', 'Analysis type', 'analysis_type_id', 4, null, true],
            ['analysis_elements_select', 'Parameters', 'parameters', 5],
            ['checkbox', 'Sampling point', 'sampling_point_type', 6, [
                ['value' => 'tap', 'label' => 'Tap'],
                ['value' => 'tank', 'label' => 'Tank'],
                ['value' => 'pool', 'label' => 'Pool'],
                ['value' => 'shower_head', 'label' => 'Shower head'],
                ['value' => 'other', 'label' => 'Other'],
            ]],
            ['text', 'Sampling point (other)', 'sampling_point_other', 7],
            ['checkbox', 'Test requirements', 'test_requirements', 8, [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'legionella', 'label' => 'Legionella'],
                ['value' => 'chemical_analysis', 'label' => 'Chemical analysis'],
            ]],
            ['text', 'Field data - pH', 'field_ph', 9],
            ['text', 'Field data - Appearance', 'field_appearance', 10],
            ['text', 'Field data - Residual chlorine', 'field_residual_chlorine', 11],
            ['text', 'Field data - Odor', 'field_odor', 12],
            ['text', 'Field data - Sample temp (°C)', 'field_sample_temp', 13],
        ]);

        $this->createSubmitAndSignSection($form, 4);
        $this->clearCaches();

        $this->command?->info('Test Request Form - Water seeded successfully.');
    }
}
