<?php

namespace Database\Seeders;

use App\Models\SubmissionForm;
use Database\Seeders\Concerns\BuildsSubmissionFormTrfSections;
use Illuminate\Database\Seeder;

/**
 * Amspec Dubai RFT catalog: Food / Water / Swab only, category-bound, Step 3 cascade.
 *
 * Visible:
 * - TRF-FOOD-019 → Food
 * - TRF-WATER-020 → Water
 * - TRF-SWAB-022 → Swab (created if missing)
 *
 * Other TRF templates are hidden from Request For Testing (not deleted).
 *
 * Requires: AmspecDubaiSampleTaxonomySeeder (categories exist).
 */
class AmspecDubaiTrfCategoryBindSeeder extends Seeder
{
    use BuildsSubmissionFormTrfSections;

    /**
     * @var list<string>
     */
    private const VISIBLE_DOCUMENT_CODES = [
        'TRF-FOOD-019',
        'TRF-WATER-020',
        'TRF-SWAB-022',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const FORM_CATEGORIES = [
        'TRF-FOOD-019' => ['Food'],
        'TRF-WATER-020' => ['Water'],
        'TRF-SWAB-022' => ['Swab'],
    ];

    public function run(): void
    {
        $this->ensureSwabTrf();

        foreach (self::FORM_CATEGORIES as $documentCode => $categoryNames) {
            $form = SubmissionForm::query()
                ->where('document_code', $documentCode)
                ->first();

            if (! $form) {
                $this->command?->warn("TRF {$documentCode} not found — skip.");

                continue;
            }

            $form->is_hidden_from_rft = false;
            $form->is_active = true;
            $form->is_published = true;
            $form->save();

            $this->syncSampleTypeCategoriesByNames($form, $categoryNames);
            $this->patchStep3ForDocumentCode($form, $documentCode);
            $this->removeRowElementsByName($form, [
                'test_requirements',
                'test_category',
            ]);

            $this->command?->info("Visible TRF {$documentCode} → ".implode(', ', $categoryNames)." ({$form->name}).");
        }

        $this->hideOtherTrfsFromRft();
        $this->clearCaches();
        $this->command?->info('Amspec Dubai TRF catalog set to Food / Water / Swab.');
    }

    private function ensureSwabTrf(): void
    {
        $form = $this->createOrRefreshTrfSubmissionForm([
            'name' => 'Test Request Form - Swab',
            'document_code' => 'TRF-SWAB-022',
            'description' => 'AmSpec test request form for swab samples.',
            'naming_convention_prefix' => 'TRFS',
            'naming_convention_format' => 'TRFS-{YYYY}{MM}-{0000}',
            'print_template_name' => 'layouts.lab.invoice.print-trf-amspec-food',
        ]);

        if ($form->sections()->exists()) {
            $this->command?->info('Test Request Form - Swab structure already exists; patching fields.');
            $this->patchCustomerDetailsSection($form);
            $this->patchCollectionDataSection($form, true);
            $this->patchSampleRowsSection($form, $this->swabTrfRowFields());
            $this->patchMiscellaneousSection($form);
        } else {
            $this->createCustomerDetailsSection($form, 1);
            $this->createCollectionDataSection($form, 2, true);
            $this->createSampleRowsSection($form, 3, 'Test & sample information', $this->swabTrfRowFields());
            $this->createMiscellaneousSection($form, 4);
            $this->createSubmitAndSignSection($form, 5);
            $this->command?->info('Created Test Request Form - Swab (TRF-SWAB-022).');
        }
    }

    private function hideOtherTrfsFromRft(): void
    {
        $query = SubmissionForm::query()
            ->where('form_type', 'template')
            ->where(function ($q): void {
                $q->where('document_code', 'like', 'TRF%')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->whereNotIn('document_code', self::VISIBLE_DOCUMENT_CODES);

        $hidden = 0;
        foreach ($query->get() as $form) {
            if (! (bool) $form->is_hidden_from_rft) {
                $form->is_hidden_from_rft = true;
                $form->save();
                $hidden++;
            }

            // Detach leftover sample-type pivots so category cards stay clean if unhidden later.
            $form->sampleTypes()->sync([]);

            $this->command?->info("Hidden from RFT: {$form->document_code} ({$form->name}).");
        }

        $this->command?->info("Hidden {$hidden} extra TRF(s) from Request For Testing.");
    }

    private function patchStep3ForDocumentCode(SubmissionForm $form, string $documentCode): void
    {
        $fields = match ($documentCode) {
            'TRF-FOOD-019' => $this->foodTrfRowFields(),
            'TRF-WATER-020' => $this->waterTrfRowFields(),
            'TRF-SWAB-022' => $this->swabTrfRowFields(),
            default => $this->foodTrfRowFields(),
        };

        $this->patchSampleRowsSection($form, $fields);
    }
}
