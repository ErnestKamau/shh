<?php

namespace Database\Seeders;

use App\Company;
use App\Models\SubmissionForm;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Brazil (brl) TRF templates: Exportation products, Food & Feed, and Food.
 * Distinct document codes so Dubai TRFs are left untouched.
 */
class AmspecBrazilTrfSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public function run(): void
    {
        $brazilCompanyId = $this->resolveBrazilCompanyId();
        if ($brazilCompanyId === null) {
            $this->command?->error(
                'Brazil company not found (expected id '.AmSpecSeedData::BRAZIL_COMPANY_ID
                .' or companies.code = brl). Run Phase1FoundationSeeder / SystemSetupSeeder first.'
            );

            return;
        }

        $this->seedFoodLikeForm(
            companyId: $brazilCompanyId,
            name: 'Test Request Form - Exportation products',
            documentCode: 'TRF-BRL-EXPORT-001',
            description: 'AmSpec Brazil test request form for Exportation products.',
            namingPrefix: 'TRBEX',
            namingFormat: 'TRBEX-{YYYY}{MM}-{0000}',
            categoryNames: ['Food & Feed', 'Food and Feed', 'Food'],
            detachFoodAndFeedFromFoodOnly: false,
            perSampleExportationInfo: true,
        );

        $this->seedFoodLikeForm(
            companyId: $brazilCompanyId,
            name: 'Test Request Form - Food & Feed',
            documentCode: 'TRF-BRL-FOOD-FEED-021',
            description: 'AmSpec Brazil test request form for Food & Feed samples.',
            namingPrefix: 'TRBFF',
            namingFormat: 'TRBFF-{YYYY}{MM}-{0000}',
            categoryNames: ['Food & Feed', 'Food and Feed'],
            detachFoodAndFeedFromFoodOnly: false,
        );

        $this->seedFoodLikeForm(
            companyId: $brazilCompanyId,
            name: 'Test Request Form - Food',
            documentCode: 'TRF-BRL-FOOD-019',
            description: 'AmSpec Brazil test request form for Food samples.',
            namingPrefix: 'TRBF',
            namingFormat: 'TRBF-{YYYY}{MM}-{0000}',
            categoryNames: ['Food'],
            detachFoodAndFeedFromFoodOnly: true,
        );

        $this->clearCaches();

        $this->command?->info('Brazil TRFs seeded for company '.$brazilCompanyId.'.');
    }

    /**
     * @param  list<string>  $categoryNames
     */
    private function seedFoodLikeForm(
        string $companyId,
        string $name,
        string $documentCode,
        string $description,
        string $namingPrefix,
        string $namingFormat,
        array $categoryNames,
        bool $detachFoodAndFeedFromFoodOnly,
        bool $perSampleExportationInfo = false,
    ): void {
        // Prefer an existing Brazil form with the same name (may already use a Dubai-style code).
        $existingByName = null;
        if (Schema::hasColumn('submission_forms', 'company_id')) {
            $existingByName = SubmissionForm::query()
                ->where('company_id', $companyId)
                ->where('name', $name)
                ->first();
        }

        $resolvedDocumentCode = $existingByName !== null
            ? (string) $existingByName->document_code
            : $documentCode;

        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => $name,
            'document_code' => $resolvedDocumentCode,
            'description' => $description,
            'naming_convention_prefix' => $namingPrefix,
            'naming_convention_format' => $namingFormat,
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-food',
            'company_id' => $companyId,
        ]);

        $this->syncSampleTypeCategoriesByNames($form, $categoryNames);
        $this->ensureBrazilTrfHasAnyCategoryLink($form);

        if ($detachFoodAndFeedFromFoodOnly) {
            $this->detachFoodAndFeedSampleTypes($form);
        }

        $extraSamplingApparatus = [
            ['value' => 'air_sampler', 'label' => 'Air sampler'],
        ];

        if ($form->sections()->exists()) {
            $this->command?->info("{$name} structure already exists; patching fields.");
            $this->patchCustomerDetailsSection($form);
            $this->patchCollectionDataSection($form, true, $extraSamplingApparatus);
            $this->patchSampleRowsSection($form, $this->foodTrfRowFields());
            if ($perSampleExportationInfo) {
                $this->patchExportationInfoSection($form);
            } else {
                $this->patchMiscellaneousSection($form);
            }
            $this->patchSubmitAndSignSection($form);
            $this->promoteCollectionFieldsToSampleRows($form, $extraSamplingApparatus);
        } else {
            $this->createCustomerDetailsSection($form, 1);
            $this->createCollectionDataSection($form, 2, true, $extraSamplingApparatus);
            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->foodTrfRowFields());
            if ($perSampleExportationInfo) {
                $this->createExportationInfoSection($form, 4);
            } else {
                $this->createMiscellaneousSection($form, 4);
            }
            $this->createSubmitAndSignSection($form, 5);
            $this->promoteCollectionFieldsToSampleRows($form, $extraSamplingApparatus);
        }

        $this->command?->info("{$name} ({$documentCode}) ready for Brazil.");
    }

    private function resolveBrazilCompanyId(): ?string
    {
        if (Company::query()->whereKey(AmSpecSeedData::BRAZIL_COMPANY_ID)->exists()) {
            return AmSpecSeedData::BRAZIL_COMPANY_ID;
        }

        $byCode = Company::query()
            ->whereRaw('LOWER(TRIM(code)) = ?', ['brl'])
            ->value('id');

        return $byCode !== null ? (string) $byCode : null;
    }

    /**
     * Brazil DBs often lack Food / Food & Feed category rows. Fall back to any
     * existing category so Request For Testing can list the TRF cards.
     */
    private function ensureBrazilTrfHasAnyCategoryLink(SubmissionForm $form): void
    {
        if (! Schema::hasTable('submission_form_sample_type_categories')) {
            return;
        }

        if ($form->sampleTypeCategories()->exists()) {
            return;
        }

        $fallbackIds = \App\SampleTypeCategory::query()
            ->orderBy('id')
            ->limit(5)
            ->pluck('id')
            ->all();

        if ($fallbackIds === []) {
            $this->command?->warn("No sample type categories exist to link to {$form->name}.");

            return;
        }

        $form->sampleTypeCategories()->syncWithoutDetaching($fallbackIds);
        $this->command?->warn(
            "Linked {$form->name} to fallback sample type categor".(count($fallbackIds) === 1 ? 'y' : 'ies')
            .' (Food/Food & Feed categories were missing).'
        );
    }
}
