<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfFoodSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => 'Test Request Form - Food',
            'document_code' => 'TRF-FOOD-019',
            'description' => 'AmSpec LWS-019 test request form for food samples.',
            'naming_convention_prefix' => 'TRFF',
            'naming_convention_format' => 'TRFF-{YYYY}{MM}-{0000}',
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-food',
        ]);

        $this->syncSampleTypeCategoriesByNames($form, ['Food']);

        // Food & Feed has its own TRF — never keep it linked here.
        $this->detachFoodAndFeedSampleTypes($form);

        if ($form->sections()->exists()) {
            $this->command?->info('Test Request Form - Food structure already exists; patching fields.');
            $this->patchCustomerDetailsSection($form);
            $this->patchCollectionDataSection($form, true, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);
            $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
            $this->patchMiscellaneousSection($form);
            $this->promoteCollectionFieldsToSampleRows($form, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);
        } else {
            $this->createCustomerDetailsSection($form, 1);
            $this->createCollectionDataSection($form, 2, true, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);

            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->foodTrfRowFields());
            $this->createMiscellaneousSection($form, 4);

            $this->createSubmitAndSignSection($form, 5);
            $this->promoteCollectionFieldsToSampleRows($form, [
                ['value' => 'air_sampler', 'label' => 'Air sampler'],
            ]);
        }

        $this->clearCaches();

        $this->command?->info('Test Request Form - Food seeded successfully.');
    }
}
