<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

/**
 * Amspec Dubai RFT catalog: three active TRFs (Food, Water, Waste Water).
 *
 * Visible:
 * - TRF-FOOD-019 → Food, Swab, Ice/Water, Air, Food Contact Material, Consumer Products, Other
 * - TRF-WATER-020 → Water (+ Leachate when present in taxonomy)
 * - TRF-WASTEWATER-036 → Waste Water
 *
 * Hidden from RFT:
 * - TRF-SWAB-022 (standalone swab TRF; swab stays on Food category bindings)
 * - TRF-AMSPEC-001 (legacy unified form, if present)
 *
 * Requires: AmspecDubaiSampleTaxonomySeeder or AmspecDubaiWasteWaterCategorySeeder (categories exist).
 */
class AmspecDubaiTrfCategoryBindSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    private const FOOD_DOCUMENT_CODE = 'TRF-FOOD-019';

    private const WATER_DOCUMENT_CODE = 'TRF-WATER-020';

    private const WASTE_WATER_DOCUMENT_CODE = 'TRF-WASTEWATER-036';

    private const WASTE_WATER_LEGACY_DOCUMENT_CODE = 'TRF-WASTE-036';

    private const SWAB_DOCUMENT_CODE = 'TRF-SWAB-022';

    private const UNIFIED_DOCUMENT_CODE = 'TRF-AMSPEC-001';

    /**
     * @var list<string>
     */
    private const FOOD_CATEGORIES = [
        'Food',
        'Swab',
        'Ice/Water',
        'Air',
        'Food Contact Material',
        'Consumer Products',
        'Other',
    ];

    /**
     * @var list<string>
     */
    private const WATER_CATEGORIES = [
        'Water',
        'Leachate',
    ];

    /**
     * @var list<string>
     */
    private const WASTE_WATER_CATEGORIES = [
        'Waste Water',
    ];

    public function run(): void
    {
        $this->configureFoodTrf();
        $this->configureWaterTrf();
        $this->configureWasteWaterTrf();
        $this->hideStandaloneSwabTrf();
        $this->hideUnifiedTrfIfPresent();
        $this->clearCaches();
        $this->command?->info('Amspec Dubai TRF catalog set to Food / Water / Waste Water forms.');
    }

    private function configureFoodTrf(): void
    {
        $form = SubmissionForm::query()->where('document_code', self::FOOD_DOCUMENT_CODE)->first();
        if ($form === null) {
            $this->command?->warn('Food TRF '.self::FOOD_DOCUMENT_CODE.' not found — run SubmissionFormTrfFoodSeeder first.');

            return;
        }

        $form->is_hidden_from_rft = false;
        $form->save();

        $this->syncSampleTypeCategoriesByNames($form, self::FOOD_CATEGORIES);
        $this->patchCustomerDetailsSection($form);
        $this->patchCollectionDataSection($form, true, [
            ['value' => 'air_sampler', 'label' => 'Air sampler'],
        ]);
        $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
        $this->patchMiscellaneousSection($form);
        $this->patchSubmitAndSignSection($form);
        $this->promoteCollectionFieldsToSampleRows($form, [
            ['value' => 'air_sampler', 'label' => 'Air sampler'],
        ]);

        $this->command?->info('Visible TRF '.self::FOOD_DOCUMENT_CODE.' → '.implode(', ', self::FOOD_CATEGORIES).'.');
    }

    private function configureWaterTrf(): void
    {
        $form = SubmissionForm::query()->where('document_code', self::WATER_DOCUMENT_CODE)->first();
        if ($form === null) {
            $this->command?->warn('Water TRF '.self::WATER_DOCUMENT_CODE.' not found — run SubmissionFormTrfWaterSeeder first.');

            return;
        }

        $form->is_hidden_from_rft = false;
        $form->save();

        $this->syncSampleTypeCategoriesByNames($form, self::WATER_CATEGORIES);
        $this->patchCustomerDetailsSection($form);
        $this->patchCollectionDataSection($form, true);
        $this->patchSampleRowsSection($form, $this->waterTrfRowFields());
        $this->patchMiscellaneousSection($form);
        $this->patchSubmitAndSignSection($form);
        $this->promoteCollectionFieldsToSampleRows($form);

        $this->command?->info('Visible TRF '.self::WATER_DOCUMENT_CODE.' → '.implode(', ', self::WATER_CATEGORIES).'.');
    }

    private function configureWasteWaterTrf(): void
    {
        $form = SubmissionForm::query()
            ->where(function ($query): void {
                $query->where('document_code', self::WASTE_WATER_DOCUMENT_CODE)
                    ->orWhere('document_code', self::WASTE_WATER_LEGACY_DOCUMENT_CODE);
            })
            ->first();

        if ($form === null) {
            $this->command?->warn('Waste Water TRF '.self::WASTE_WATER_DOCUMENT_CODE.' not found — run SubmissionFormTrfWasteWaterSeeder first.');

            return;
        }

        if ($form->document_code === self::WASTE_WATER_LEGACY_DOCUMENT_CODE) {
            $form->document_code = self::WASTE_WATER_DOCUMENT_CODE;
        }

        $form->is_hidden_from_rft = false;
        $form->save();

        $this->syncSampleTypeCategoriesByNames($form, self::WASTE_WATER_CATEGORIES);
        $this->patchWasteWaterTrfSections($form);

        $this->command?->info('Visible TRF '.self::WASTE_WATER_DOCUMENT_CODE.' → '.implode(', ', self::WASTE_WATER_CATEGORIES).'.');
    }

    private function hideStandaloneSwabTrf(): void
    {
        $form = SubmissionForm::query()->where('document_code', self::SWAB_DOCUMENT_CODE)->first();
        if ($form === null) {
            return;
        }

        $form->is_hidden_from_rft = true;
        $form->save();
        $form->sampleTypeCategories()->sync([]);

        $this->command?->info('Hidden from RFT: '.self::SWAB_DOCUMENT_CODE.' ('.$form->name.').');
    }

    private function hideUnifiedTrfIfPresent(): void
    {
        $form = SubmissionForm::query()->where('document_code', self::UNIFIED_DOCUMENT_CODE)->first();
        if ($form === null) {
            return;
        }

        $form->is_hidden_from_rft = true;
        $form->save();
        $form->sampleTypeCategories()->sync([]);

        $this->command?->info('Hidden from RFT: '.self::UNIFIED_DOCUMENT_CODE.' ('.$form->name.').');
    }
}
