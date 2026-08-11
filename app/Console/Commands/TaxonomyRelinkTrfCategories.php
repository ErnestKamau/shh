<?php

namespace App\Console\Commands;

use App\Models\SubmissionForm;
use App\SampleTypeCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Maps existing AmSpec TRF submission forms to sample type categories
 * by matching document code or form name keywords.
 *
 * Mapping rules (case-insensitive, first match wins per form):
 *   FOOD / FEED  → "Food"       category
 *   SWAB         → "Swab"       category (falls back to "Food" if no Swab category exists)
 *   ICE          → "Ice"        category (falls back to "Water" if no Ice category)
 *   WATER        → "Water"      category (excludes WASTE)
 *   WASTE        → "Waste Water" or "Water" category (prefers longest/exact match)
 *
 * Usage:
 *   php artisan taxonomy:relink-trf-categories
 *   php artisan taxonomy:relink-trf-categories --dry-run
 *   php artisan taxonomy:relink-trf-categories --force   (re-sync even when already linked)
 */
class TaxonomyRelinkTrfCategories extends Command
{
    protected $signature = 'taxonomy:relink-trf-categories
                            {--dry-run : Preview changes without persisting them}
                            {--force : Re-sync even when a form already has category links}';

    protected $description = 'Link existing TRF submission forms to sample type categories based on document code / name keywords (FOOD/FEED→Food, SWAB→Swab, ICE→Ice/Water, WATER→Water, WASTE→Waste Water/Water).';

    /**
     * Keyword → ordered list of category name substrings (first match found in DB wins).
     * Each entry: [keyword, [preferred_substring, fallback_substring, ...]].
     *
     * @var list<array{0: string, 1: list<string>}>
     */
    private const KEYWORD_MAP = [
        ['WASTE', ['waste water', 'waste', 'water']],
        ['WATER', ['water']],
        ['FOOD',  ['food']],
        ['FEED',  ['food']],
        ['SWAB',  ['swab', 'food']],
        ['ICE',   ['ice/water', 'ice', 'water']],
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('submission_form_sample_type_categories')) {
            $this->error('Pivot table submission_form_sample_type_categories does not exist. Run migrations first.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        /** @var \Illuminate\Support\Collection<string, SampleTypeCategory> $categories */
        $categories = SampleTypeCategory::all()->keyBy(fn ($c) => strtolower((string) $c->sample_type_category));

        if ($categories->isEmpty()) {
            $this->warn('No sample type categories found in the database.');

            return self::SUCCESS;
        }

        $this->line('Available categories: '.implode(', ', $categories->keys()->all()));
        $this->newLine();

        $forms = SubmissionForm::query()
            ->where('is_active', true)
            ->where('form_type', 'template')
            ->where(function ($q): void {
                $q->where('document_code', 'like', 'TRF-%')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->get();

        $linked = 0;
        $skipped = 0;
        $noMatch = 0;

        foreach ($forms as $form) {
            if (! $force && $form->sampleTypeCategories()->exists()) {
                $this->line("  <fg=yellow>SKIP</> {$form->document_code} — {$form->name} (already linked)");
                $skipped++;

                continue;
            }

            $category = $this->resolveCategory($form, $categories);

            if ($category === null) {
                $this->line("  <fg=gray>NONE</> {$form->document_code} — {$form->name} (no matching category)");
                $noMatch++;

                continue;
            }

            $this->line("  <fg=green>LINK</> {$form->document_code} — {$form->name} → {$category->sample_type_category}");

            if (! $dryRun) {
                DB::table('submission_form_sample_type_categories')
                    ->insertOrIgnore([
                        'submission_form_id' => $form->id,
                        'sample_type_category_id' => $category->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            $linked++;
        }

        $suffix = $dryRun ? ' (dry-run — no changes saved)' : '';
        $this->newLine();
        $this->info("Done{$suffix}: {$linked} linked, {$skipped} skipped (already linked), {$noMatch} with no matching category.");

        return self::SUCCESS;
    }

    /**
     * Resolve the best-matching category for a form based on keyword rules.
     *
     * @param  \Illuminate\Support\Collection<string, SampleTypeCategory>  $categories
     */
    private function resolveCategory(SubmissionForm $form, \Illuminate\Support\Collection $categories): ?SampleTypeCategory
    {
        $haystack = strtoupper(trim((string) $form->document_code.' '.(string) $form->name));

        foreach (self::KEYWORD_MAP as [$keyword, $categorySubstrings]) {
            if (! str_contains($haystack, $keyword)) {
                continue;
            }

            // WATER rule: must not also contain WASTE (that goes to the WASTE rule above it).
            if ($keyword === 'WATER' && str_contains($haystack, 'WASTE')) {
                continue;
            }

            // Walk preferred → fallback substrings and return the first match found in DB.
            foreach ($categorySubstrings as $substring) {
                foreach ($categories as $storedName => $category) {
                    if (str_contains($storedName, $substring)) {
                        return $category;
                    }
                }
            }
        }

        return null;
    }
}
