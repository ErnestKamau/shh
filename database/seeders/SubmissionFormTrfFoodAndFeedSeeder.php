<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfFoodAndFeedSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => 'Test Request Form - Food & Feed',
            'document_code' => 'TRF-FOOD-FEED-021',
            'description' => 'AmSpec test request form for Food & Feed samples.',
            'naming_convention_prefix' => 'TRFFF',
            'naming_convention_format' => 'TRFFF-{YYYY}{MM}-{0000}',
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-food',
        ]);

        $this->syncSampleTypesByCodes($form, [
            'FOOD AND FEED',
            'FOOD_AND_FEED',
            'Food & Feed',
            'Food and Feed',
        ]);

        if ($form->sampleTypes()->count() === 0) {
            $this->syncSampleTypesByExactNames($form, [
                'Food & Feed',
                'Food and Feed',
            ]);
        }

        if ($form->sections()->exists()) {
            $this->command?->info('Test Request Form - Food & Feed structure already exists; patching fields.');
            $this->patchCustomerDetailsSection($form);
            $this->patchCollectionDataSection($form, true, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);
            $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
            $this->patchMiscellaneousSection($form);
        } else {
            $this->createCustomerDetailsSection($form, 1);
            $this->createCollectionDataSection($form, 2, true, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);

            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->foodTrfRowFields());
            $this->createMiscellaneousSection($form, 4);

            $this->createSubmitAndSignSection($form, 5);
        }

        $this->clearCaches();

        $this->command?->info('Test Request Form - Food & Feed seeded successfully.');
    }
}
