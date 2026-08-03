<?php

namespace App\Console\Commands;

use App\LabSubCategory;
use App\Models\SampleCapturedTestStagesTrack;
use App\Models\SolutionPreparation;
use App\Models\System\SystemConfiguration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BackfillSolutionExpiryDataCommand extends Command
{
    protected $signature = 'solutions:backfill-expiry-data
        {--solution=* : Solution UUID whose current batch should receive the verified expiry date}
        {--expiry-date= : Verified expiry date (YYYY-MM-DD) for the selected solutions}
        {--show-missing : List active media and control batches that have no expiry date}
        {--dry-run : Report changes without saving them}';

    protected $description = 'Backfill solution preparation and worksheet expiry dates from verified batch data';

    public function handle(): int
    {
        if (! Schema::hasColumn('solution_preparations', 'expiry_date')) {
            $this->error('The solution_preparations.expiry_date column is missing. Run migrations first.');

            return self::FAILURE;
        }

        $solutionIds = array_values(array_filter(array_map('strval', (array) $this->option('solution'))));
        $expiryDate = $this->normalizedExpiryDate($this->option('expiry-date'));
        $dryRun = (bool) $this->option('dry-run');

        if (($solutionIds !== [] && $expiryDate === null) || ($solutionIds === [] && $this->option('expiry-date'))) {
            $this->error('Use --solution and --expiry-date together. Dates are never guessed for laboratory solutions.');

            return self::FAILURE;
        }

        $assignedSolutions = 0;
        if ($solutionIds !== []) {
            $solutions = LabSubCategory::query()->whereIn('id', $solutionIds)->get();
            $missingIds = array_diff($solutionIds, $solutions->pluck('id')->map(fn ($id): string => (string) $id)->all());

            if ($missingIds !== []) {
                $this->error('Unknown solution UUID(s): '.implode(', ', $missingIds));

                return self::FAILURE;
            }

            foreach ($solutions as $solution) {
                if (! $solution->current_batch_number) {
                    $this->warn("Skipped {$solution->name}: it has no current batch number.");

                    continue;
                }

                $assignedSolutions++;
                if (! $dryRun) {
                    $solution->update(['batch_expiry_date' => $expiryDate]);
                }
            }
        }

        $expiryByBatch = $this->expiryBySolutionBatch($solutionIds, $expiryDate);
        $preparationsUpdated = $this->backfillPreparations($expiryByBatch, $dryRun);
        $worksheetItemsUpdated = $this->backfillWorksheetItems($expiryByBatch, $dryRun);

        $prefix = $dryRun ? 'Would update' : 'Updated';
        $this->info("{$prefix} {$assignedSolutions} current solution batch(es).");
        $this->info("{$prefix} {$preparationsUpdated} preparation record(s).");
        $this->info("{$prefix} {$worksheetItemsUpdated} saved worksheet item(s).");

        if ((bool) $this->option('show-missing')) {
            $this->displayMissingBatchExpiries();
        }

        return self::SUCCESS;
    }

    protected function displayMissingBatchExpiries(): void
    {
        $categoryIds = SystemConfiguration::query()
            ->whereIn('key', ['media_solution_type_id', 'control_solution_type_id'])
            ->pluck('value')
            ->filter()
            ->values();

        if ($categoryIds->isEmpty()) {
            $this->warn('Media and control solution categories are not configured.');

            return;
        }

        $solutions = LabSubCategory::query()
            ->whereIn('category_id', $categoryIds)
            ->where('active', 1)
            ->whereNotNull('current_batch_number')
            ->whereNull('batch_expiry_date')
            ->orderBy('name')
            ->get(['id', 'name', 'current_batch_number', 'batch_prepared_date']);

        if ($solutions->isEmpty()) {
            $this->info('No active media or control batches are missing expiry dates.');

            return;
        }

        $this->warn('These active batches need verified expiry dates:');
        $this->table(
            ['Solution UUID', 'Name', 'Batch', 'Prepared'],
            $solutions->map(fn (LabSubCategory $solution): array => [
                (string) $solution->id,
                (string) $solution->name,
                (string) $solution->current_batch_number,
                (string) ($solution->batch_prepared_date ?? 'N/A'),
            ])->all()
        );
    }

    protected function normalizedExpiryDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', trim((string) $value));
        } catch (Throwable) {
            $this->error('The expiry date must use YYYY-MM-DD format.');

            return null;
        }

        if ($date === false || $date->format('Y-m-d') !== trim((string) $value)) {
            $this->error('The expiry date must be a valid YYYY-MM-DD date.');

            return null;
        }

        return $date->format('Y-m-d');
    }

    /**
     * @param  list<string>  $assignedSolutionIds
     * @return array<string, string>
     */
    protected function expiryBySolutionBatch(array $assignedSolutionIds, ?string $assignedExpiryDate): array
    {
        $expiryByBatch = [];

        LabSubCategory::query()
            ->whereNotNull('current_batch_number')
            ->whereNotNull('batch_expiry_date')
            ->get(['id', 'current_batch_number', 'batch_expiry_date'])
            ->each(function (LabSubCategory $solution) use (&$expiryByBatch): void {
                $expiryByBatch[$this->batchKey($solution->id, $solution->current_batch_number)] =
                    Carbon::parse($solution->batch_expiry_date)->format('Y-m-d');
            });

        if ($assignedExpiryDate !== null) {
            LabSubCategory::query()
                ->whereIn('id', $assignedSolutionIds)
                ->whereNotNull('current_batch_number')
                ->get(['id', 'current_batch_number'])
                ->each(function (LabSubCategory $solution) use (&$expiryByBatch, $assignedExpiryDate): void {
                    $expiryByBatch[$this->batchKey($solution->id, $solution->current_batch_number)] = $assignedExpiryDate;
                });
        }

        SolutionPreparation::query()
            ->whereNotNull('batch_number')
            ->whereNotNull('expiry_date')
            ->get(['solution_id', 'batch_number', 'expiry_date'])
            ->each(function (SolutionPreparation $preparation) use (&$expiryByBatch): void {
                $expiryByBatch[$this->batchKey($preparation->solution_id, $preparation->batch_number)] =
                    $preparation->expiry_date->format('Y-m-d');
            });

        if (Schema::hasTable('solution_batch_history')) {
            DB::table('solution_batch_history')
                ->whereNotNull('batch_number')
                ->whereNotNull('expiry_date')
                ->select(['solution_id', 'batch_number', 'expiry_date'])
                ->orderBy('created_at')
                ->get()
                ->each(function (object $batch) use (&$expiryByBatch): void {
                    $expiryByBatch[$this->batchKey($batch->solution_id, $batch->batch_number)] =
                        Carbon::parse($batch->expiry_date)->format('Y-m-d');
                });
        }

        return $expiryByBatch;
    }

    /**
     * @param  array<string, string>  $expiryByBatch
     */
    protected function backfillPreparations(array $expiryByBatch, bool $dryRun): int
    {
        $updated = 0;

        SolutionPreparation::query()
            ->whereNull('expiry_date')
            ->whereNotNull('batch_number')
            ->orderBy('id')
            ->chunk(200, function ($preparations) use ($expiryByBatch, $dryRun, &$updated): void {
                foreach ($preparations as $preparation) {
                    /** @var SolutionPreparation $preparation */
                    $expiry = $expiryByBatch[$this->batchKey($preparation->solution_id, $preparation->batch_number)] ?? null;
                    if ($expiry === null) {
                        continue;
                    }

                    $updated++;
                    if (! $dryRun) {
                        $preparation->update(['expiry_date' => $expiry]);
                    }
                }
            });

        return $updated;
    }

    /**
     * @param  array<string, string>  $expiryByBatch
     */
    protected function backfillWorksheetItems(array $expiryByBatch, bool $dryRun): int
    {
        $updated = 0;

        SampleCapturedTestStagesTrack::query()
            ->where(function ($query): void {
                $query->whereNotNull('controls_data')->orWhereNotNull('media_data');
            })
            ->orderBy('id')
            ->chunk(200, function ($tracks) use ($expiryByBatch, $dryRun, &$updated): void {
                foreach ($tracks as $track) {
                    /** @var SampleCapturedTestStagesTrack $track */
                    $controlsData = $track->controls_data;
                    $mediaData = $track->media_data;
                    $changed = false;

                    $changed = $this->fillItemExpiries(
                        $controlsData,
                        'controls_items',
                        ['preparation'],
                        $expiryByBatch,
                        $updated
                    ) || $changed;
                    $changed = $this->fillItemExpiries(
                        $mediaData,
                        'media_items',
                        ['preparation_number', 'remark'],
                        $expiryByBatch,
                        $updated
                    ) || $changed;

                    if ($changed && ! $dryRun) {
                        $track->controls_data = $controlsData;
                        $track->media_data = $mediaData;
                        $track->save();
                    }
                }
            });

        return $updated;
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  list<string>  $batchFields
     * @param  array<string, string>  $expiryByBatch
     */
    protected function fillItemExpiries(
        ?array &$data,
        string $itemsKey,
        array $batchFields,
        array $expiryByBatch,
        int &$updated
    ): bool {
        if (! isset($data[$itemsKey]) || ! is_array($data[$itemsKey])) {
            return false;
        }

        $changed = false;
        foreach ($data[$itemsKey] as &$item) {
            if (! is_array($item) || ! empty($item['expiry']) || empty($item['id'])) {
                continue;
            }

            $batchNumber = null;
            foreach ($batchFields as $batchField) {
                if (! empty($item[$batchField])) {
                    $batchNumber = (string) $item[$batchField];
                    break;
                }
            }

            if ($batchNumber === null) {
                continue;
            }

            $expiry = $expiryByBatch[$this->batchKey($item['id'], $batchNumber)] ?? null;
            if ($expiry === null) {
                continue;
            }

            $item['expiry'] = $expiry;
            $updated++;
            $changed = true;
        }
        unset($item);

        return $changed;
    }

    protected function batchKey(mixed $solutionId, mixed $batchNumber): string
    {
        return (string) $solutionId.'|'.trim((string) $batchNumber);
    }
}
