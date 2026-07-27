<?php

namespace App\Console\Commands;

use App\Models\SubmissionForm;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeduplicateTrfSubmissionFormSections extends Command
{
    protected $signature = 'submission-forms:dedupe-trf-sections
                            {--form= : Limit to one form by UUID or document_code (e.g. TRF-FOOD-019)}
                            {--all : Include every submission form, not only TRF templates}
                            {--dry-run : Report duplicates without deleting anything}';

    protected $description = 'Remove duplicate TRF submission-form sections (e.g. repeated Submit & sign) created by re-seeding';

    public function handle(SubmissionFormSchemaHelper $schemaHelper): int
    {
        $forms = $this->resolveForms();

        if ($forms->isEmpty()) {
            $this->warn('No matching submission forms found.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->comment('Dry run — no sections will be deleted.');
        }

        $rows = [];
        $formsTouched = 0;
        $sectionsRemoved = 0;
        $valuesMigrated = 0;

        foreach ($forms as $form) {
            $dupes = $this->duplicateTitleCounts((string) $form->id);
            if ($dupes->isEmpty()) {
                continue;
            }

            $formsTouched++;
            $label = (string) ($form->document_code ?: $form->id);

            if ($dryRun) {
                $extraSections = (int) $dupes->sum(fn (int $count): int => max(0, $count - 1));
                $rows[] = [
                    $label,
                    $form->name,
                    $dupes->map(fn (int $count, string $title): string => "{$title}×{$count}")->implode(', '),
                    $extraSections,
                    'dry-run',
                ];

                continue;
            }

            $result = DB::transaction(function () use ($schemaHelper, $form): array {
                return $schemaHelper->deduplicateSections($form);
            });

            $sectionsRemoved += $result['removed'];
            $valuesMigrated += $result['migrated_values'];

            $rows[] = [
                $label,
                $form->name,
                $dupes->map(fn (int $count, string $title): string => "{$title}×{$count}")->implode(', '),
                $result['removed'],
                "kept {$result['kept']}, migrated {$result['migrated_values']} values",
            ];
        }

        if ($rows === []) {
            $this->info('No duplicate section titles found. Nothing to clean.');

            return self::SUCCESS;
        }

        $this->table(
            ['Form', 'Name', 'Duplicates before', 'Sections removed', 'Result'],
            $rows
        );

        if ($dryRun) {
            $this->newLine();
            $this->info("Found duplicates on {$formsTouched} form(s). Re-run without --dry-run to clean.");

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("Cleaned {$formsTouched} form(s): removed {$sectionsRemoved} section(s), migrated {$valuesMigrated} value(s).");

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, SubmissionForm>
     */
    private function resolveForms(): Collection
    {
        $query = SubmissionForm::query()->orderBy('document_code')->orderBy('name');

        if ($formOption = $this->option('form')) {
            $query->where(function ($builder) use ($formOption): void {
                $builder->where('id', $formOption)
                    ->orWhere('document_code', $formOption);
            });

            return $query->get();
        }

        if (! $this->option('all')) {
            $query->where(function ($builder): void {
                $builder->where('document_code', 'like', 'TRF-%')
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%test request form%']);
            });
        }

        return $query->get();
    }

    /**
     * @return Collection<string, int>
     */
    private function duplicateTitleCounts(string $formId): Collection
    {
        return DB::table('submission_form_sections')
            ->selectRaw('title, count(*) as cnt')
            ->where('submission_form_id', $formId)
            ->groupBy('title')
            ->havingRaw('count(*) > 1')
            ->pluck('cnt', 'title');
    }
}
