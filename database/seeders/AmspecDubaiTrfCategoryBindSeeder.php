<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use App\SampleTypeCategory;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

/**
 * Amspec Dubai RFT catalog: three active TRFs (general, Water, Waste Water).
 *
 * Visible:
 * - TRF-FOOD-019 ("Test Request Form") → every sample type category except Water / Waste Water / Leachate
 * - TRF-WATER-020 → Water (+ Leachate when present in taxonomy)
 * - TRF-WASTEWATER-036 → Waste Water
 *
 * Hidden from RFT:
 * - TRF-FOOD-FEED-021 (Food & Feed; not used on Dubai)
 * - TRF-SWAB-022 (standalone swab TRF; swab stays on general TRF bindings)
 * - TRF-AMSPEC-001 (legacy unified form, if present)
 *
 * Requires: AmspecDubaiSampleTaxonomySeeder or AmspecDubaiWasteWaterCategorySeeder (categories exist).
 */
class AmspecDubaiTrfCategoryBindSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    private const GENERAL_DOCUMENT_CODE = 'TRF-FOOD-019';

    private const GENERAL_FORM_NAME = 'Test Request Form';

    private const WATER_DOCUMENT_CODE = 'TRF-WATER-020';

    private const WASTE_WATER_DOCUMENT_CODE = 'TRF-WASTEWATER-036';

    private const WASTE_WATER_LEGACY_DOCUMENT_CODE = 'TRF-WASTE-036';

    private const FOOD_AND_FEED_DOCUMENT_CODE = 'TRF-FOOD-FEED-021';

    private const SWAB_DOCUMENT_CODE = 'TRF-SWAB-022';

    private const UNIFIED_DOCUMENT_CODE = 'TRF-AMSPEC-001';

    /**
     * Categories that must NOT use the general TRF (have their own forms).
     *
     * @var list<string>
     */
    private const EXCLUDED_FROM_GENERAL_CATEGORIES = [
        'Water',
        'Waste Water',
        'Leachate',
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
        $this->configureGeneralTrf();
        $this->configureWaterTrf();
        $this->configureWasteWaterTrf();
        $this->hideFoodAndFeedTrf();
        $this->hideStandaloneSwabTrf();
        $this->hideUnifiedTrfIfPresent();
        $this->clearCaches();
        $this->command?->info('Amspec Dubai TRF catalog set to Test Request Form / Water / Waste Water.');
    }

    private function configureGeneralTrf(): void
    {
        $form = SubmissionForm::query()->where('document_code', self::GENERAL_DOCUMENT_CODE)->first();
        if ($form === null) {
            $this->command?->warn('General TRF '.self::GENERAL_DOCUMENT_CODE.' not found — run SubmissionFormTrfFoodSeeder first.');

            return;
        }

        $form->name = self::GENERAL_FORM_NAME;
        $form->description = 'AmSpec general test request form for all sample type categories except Water and Waste Water.';
        $form->is_hidden_from_rft = false;
        $form->save();

        $generalCategories = $this->generalSampleTypeCategoryNames();
        $this->syncSampleTypeCategoriesByNames($form, $generalCategories);
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

        $this->command?->info(
            'Visible TRF '.self::GENERAL_DOCUMENT_CODE.' ('.self::GENERAL_FORM_NAME.') → '
            .($generalCategories === [] ? '(none matched)' : implode(', ', $generalCategories)).'.'
        );
    }

    /**
     * All sample type categories except Water / Waste Water / Leachate.
     *
     * @return list<string>
     */
    private function generalSampleTypeCategoryNames(): array
    {
        $excluded = collect(self::EXCLUDED_FROM_GENERAL_CATEGORIES)
            ->map(fn (string $name): string => mb_strtolower(trim($name)))
            ->all();

        return SampleTypeCategory::query()
            ->orderBy('sample_type_category')
            ->pluck('sample_type_category')
            ->filter(function (mixed $name) use ($excluded): bool {
                $normalized = mb_strtolower(trim((string) $name));

                return $normalized !== '' && ! in_array($normalized, $excluded, true);
            })
            ->values()
            ->all();
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

    private function hideFoodAndFeedTrf(): void
    {
        $form = SubmissionForm::query()->where('document_code', self::FOOD_AND_FEED_DOCUMENT_CODE)->first();
        if ($form === null) {
            return;
        }

        $form->is_hidden_from_rft = true;
        $form->is_active = false;
        $form->is_published = false;
        $form->save();
        $form->sampleTypeCategories()->sync([]);

        $this->command?->info('Hidden from RFT: '.self::FOOD_AND_FEED_DOCUMENT_CODE.' ('.$form->name.').');
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
