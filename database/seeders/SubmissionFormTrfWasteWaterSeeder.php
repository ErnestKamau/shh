<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

class SubmissionFormTrfWasteWaterSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public const DOCUMENT_CODE = 'TRF-WASTEWATER-036';

    public const LEGACY_DOCUMENT_CODE = 'TRF-WASTE-036';

    public function run(): void
    {
        $this->renameLegacyDocumentCodeIfNeeded();

        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => 'Test Request Form - Waste Water',
            'document_code' => self::DOCUMENT_CODE,
            'description' => 'AmSpec LWS-036 test request form for waste water samples.',
            'naming_convention_prefix' => 'TRFWW',
            'naming_convention_format' => 'TRFWW-{YYYY}{MM}-{0000}',
        ]);

        $this->syncSampleTypeCategoriesByNames($form, ['Waste Water']);
        $this->syncSampleTypesByCodes($form, ['SMP WWTR', 'Waste Water', 'WWTR']);

        if ($form->sections()->exists()) {
            $this->command?->info('Test Request Form - Waste Water structure already exists; patching fields.');
            $this->patchWasteWaterTrfSections($form);
        } else {
            $this->createCustomerDetailsSection($form, 1);
            $this->createWasteWaterCollectionDataSection($form, 2, true);
            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->wasteWaterTrfRowFields());
            $this->createMiscellaneousSection($form, 4);
            $this->createSubmitAndSignSection($form, 5);
        }

        $this->clearCaches();

        $this->command?->info('Test Request Form - Waste Water seeded successfully ('.self::DOCUMENT_CODE.').');
    }

    private function renameLegacyDocumentCodeIfNeeded(): void
    {
        $legacy = SubmissionForm::query()
            ->where('document_code', self::LEGACY_DOCUMENT_CODE)
            ->first();

        if ($legacy === null) {
            return;
        }

        $existingNew = SubmissionForm::query()
            ->where('document_code', self::DOCUMENT_CODE)
            ->where('id', '!=', $legacy->id)
            ->exists();

        if ($existingNew) {
            $this->command?->warn(
                'Both '.self::LEGACY_DOCUMENT_CODE.' and '.self::DOCUMENT_CODE
                .' exist — using the new code form; leave legacy for manual cleanup.'
            );

            return;
        }

        $legacy->document_code = self::DOCUMENT_CODE;
        $legacy->save();
        $this->command?->info('Renamed TRF document code '.self::LEGACY_DOCUMENT_CODE.' → '.self::DOCUMENT_CODE.'.');
    }
}
