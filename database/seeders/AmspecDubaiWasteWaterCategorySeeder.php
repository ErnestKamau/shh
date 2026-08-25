<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use App\SampleType;
use App\SampleTypeCategory;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Focused Amspec Dubai helper: add Waste Water category, sample type, and TRF bind.
 *
 * Does not touch Food / Water / Swab or other taxonomy rows.
 *
 * - updateOrCreate category "Waste Water"
 * - create/move sample type code "Waste Water" under that category
 * - rename TRF-WASTE-036 → TRF-WASTEWATER-036 when present
 * - bind TRF-WASTEWATER-036 to the Waste Water category
 */
class AmspecDubaiWasteWaterCategorySeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    public const CATEGORY_NAME = 'Waste Water';

    public const SAMPLE_TYPE_CODE = 'Waste Water';

    public const SAMPLE_TYPE_NAME = 'Waste Water';

    public const TRF_DOCUMENT_CODE = 'TRF-WASTEWATER-036';

    public const LEGACY_TRF_DOCUMENT_CODE = 'TRF-WASTE-036';

    public function run(): void
    {
        $companyId = $this->resolveCompanyId();
        if (! $companyId) {
            $this->command?->error('No company found. Create a company row before seeding Waste Water category.');

            return;
        }

        $category = SampleTypeCategory::query()->updateOrCreate(
            ['sample_type_category' => self::CATEGORY_NAME],
            ['active' => true],
        );
        $this->command?->info('Category: '.self::CATEGORY_NAME.' ('.$category->id.').');

        $sampleType = SampleType::query()->updateOrCreate(
            ['code' => self::SAMPLE_TYPE_CODE],
            [
                'name' => self::SAMPLE_TYPE_NAME,
                'description' => self::SAMPLE_TYPE_NAME,
                'sample_type_category' => $category->id,
                'active' => true,
                'is_results_attachable' => true,
                'company_id' => $companyId,
            ],
        );
        $this->command?->info('Sample type: '.$sampleType->code.' → category '.self::CATEGORY_NAME.'.');

        // Also move common legacy codes onto this category when present.
        SampleType::query()
            ->where(function ($query): void {
                $query->whereRaw('LOWER(TRIM(code)) = ?', ['smp wwtr'])
                    ->orWhereRaw('LOWER(TRIM(code)) = ?', ['wwtr'])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', ['waste water']);
            })
            ->where('id', '!=', $sampleType->id)
            ->update(['sample_type_category' => $category->id]);

        $this->renameLegacyWasteWaterTrfDocumentCode();
        $this->bindWasteWaterTrf($category->id);

        $this->clearCaches();
        $this->command?->info('Waste Water category seeder complete.');
    }

    private function renameLegacyWasteWaterTrfDocumentCode(): void
    {
        $legacy = SubmissionForm::query()
            ->where('document_code', self::LEGACY_TRF_DOCUMENT_CODE)
            ->first();

        if ($legacy === null) {
            return;
        }

        $existingNew = SubmissionForm::query()
            ->where('document_code', self::TRF_DOCUMENT_CODE)
            ->where('id', '!=', $legacy->id)
            ->exists();

        if ($existingNew) {
            $this->command?->warn(
                'Both '.self::LEGACY_TRF_DOCUMENT_CODE.' and '.self::TRF_DOCUMENT_CODE
                .' exist — left legacy code unchanged. Prefer consolidating manually.'
            );

            return;
        }

        $legacy->document_code = self::TRF_DOCUMENT_CODE;
        $legacy->save();
        $this->command?->info('Renamed TRF document code '.self::LEGACY_TRF_DOCUMENT_CODE.' → '.self::TRF_DOCUMENT_CODE.'.');
    }

    private function bindWasteWaterTrf(string $categoryId): void
    {
        if (! Schema::hasTable('submission_form_sample_type_categories')) {
            $this->command?->warn('Pivot submission_form_sample_type_categories missing — skip TRF bind.');

            return;
        }

        $form = SubmissionForm::query()
            ->where(function ($query): void {
                $query->where('document_code', self::TRF_DOCUMENT_CODE)
                    ->orWhere('document_code', self::LEGACY_TRF_DOCUMENT_CODE);
            })
            ->first();

        if ($form === null) {
            $this->command?->warn(
                'TRF '.self::TRF_DOCUMENT_CODE.' not found — run SubmissionFormTrfWasteWaterSeeder, then re-run this seeder to bind.'
            );

            return;
        }

        $form->is_hidden_from_rft = false;
        $form->save();
        $form->sampleTypeCategories()->syncWithoutDetaching([$categoryId]);

        $this->command?->info('Bound '.$form->document_code.' → category '.self::CATEGORY_NAME.'.');
    }

    private function resolveCompanyId(): mixed
    {
        try {
            $sessionCompanyId = session('company_id');
            if ($sessionCompanyId) {
                return $sessionCompanyId;
            }
        } catch (\Throwable) {
            // No session in artisan context.
        }

        $authUser = auth()->user();
        if ($authUser?->company_id) {
            return $authUser->company_id;
        }

        return DB::table('companies')->orderBy('id')->value('id');
    }
}
