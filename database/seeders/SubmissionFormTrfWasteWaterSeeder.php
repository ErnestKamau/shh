<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfWasteWaterSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => 'Test Request Form - Waste Water',
            'document_code' => 'TRF-WASTE-036',
            'description' => 'AmSpec LWS-036 test request form for waste water samples.',
            'naming_convention_prefix' => 'TRFWW',
            'naming_convention_format' => 'TRFWW-{YYYY}{MM}-{0000}',
        ]);

        $this->syncSampleTypesByCodes($form, ['SMP WWTR']);

        if ($form->sections()->exists()) {
            $this->command?->info('Test Request Form - Waste Water structure already exists; patching fields.');
            $this->patchCollectionDataSection($form, true, [], [
                ['textarea', 'Sample & sampling point description', 'sample_sampling_point_description', 30],
                ['select', 'Sampling technique', 'sampling_technique', 31, [
                    ['value' => 'grab', 'label' => 'Grab'],
                    ['value' => 'composite', 'label' => 'Composite'],
                ]],
                ['select', 'Sampling source', 'sampling_source', 32, [
                    ['value' => 'tank', 'label' => 'Tank'],
                    ['value' => 'holding_tank', 'label' => 'Holding tank'],
                ]],
                ['select', 'Sample physical state', 'sample_physical_state', 33, [
                    ['value' => 'liquid', 'label' => 'Liquid'],
                    ['value' => 'semi_solid', 'label' => 'Semi solid'],
                ]],
            ]);
            $this->patchSampleRowsSection($form, $this->wasteWaterTrfRowFields());
        } else {
            $this->createCustomerDetailsSection($form, 1);

            $this->createCollectionDataSection($form, 2, true, [], [
                ['textarea', 'Sample & sampling point description', 'sample_sampling_point_description', 30],
                ['select', 'Sampling technique', 'sampling_technique', 31, [
                    ['value' => 'grab', 'label' => 'Grab'],
                    ['value' => 'composite', 'label' => 'Composite'],
                ]],
                ['select', 'Sampling source', 'sampling_source', 32, [
                    ['value' => 'tank', 'label' => 'Tank'],
                    ['value' => 'holding_tank', 'label' => 'Holding tank'],
                ]],
                ['select', 'Sample physical state', 'sample_physical_state', 33, [
                    ['value' => 'liquid', 'label' => 'Liquid'],
                    ['value' => 'semi_solid', 'label' => 'Semi solid'],
                ]],
            ]);

            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->wasteWaterTrfRowFields());

            $this->createSubmitAndSignSection($form, 4);
        }

        $this->clearCaches();

        $this->command?->info('Test Request Form - Waste Water seeded successfully.');
    }
}
