<?php

namespace App\Services\Sampleworkflow;

use App\Models\JobNumberSequence;
use App\Models\SampleSequence;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\SampleDetails;
use App\SampleHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class JobSampleNumberingService
{
    public const PREFIX_MICROBIOLOGY = 'M';

    public const PREFIX_LEGIONELLA = 'L';

    public const PREFIX_CHEMISTRY = 'C';

    /**
     * Shared sequence key for numeric sample codes (no category letter in the code).
     * Category prefixes (C/M/L) remain for classification elsewhere but are not embedded.
     */
    public const SEQUENCE_KEY = '';

    /**
     * Generate job/batch number: YYMMDD + 3-digit daily sequence (resets each calendar day).
     * Example: 260428001
     */
    public function generateJobNumber(?Carbon $at = null): string
    {
        $at ??= Carbon::now();
        $datePart = $at->format('ymd');

        return DB::transaction(function () use ($datePart): string {
            $sequence = JobNumberSequence::query()
                ->where('date_ymd', $datePart)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = JobNumberSequence::query()->create([
                    'date_ymd' => $datePart,
                    'last_sequence' => 0,
                ]);

                $sequence = JobNumberSequence::query()
                    ->where('id', $sequence->id)
                    ->lockForUpdate()
                    ->first();
            }

            $next = (int) $sequence->last_sequence + 1;

            if ($next > 999) {
                throw new RuntimeException("Daily job number limit exceeded for {$datePart}.");
            }

            $sequence->update(['last_sequence' => $next]);

            return $datePart . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * @throws InvalidArgumentException when zero or more than one category is selected
     */
    public function resolveCategoryPrefix(?bool $microbiology, ?bool $legionella, ?bool $chemistry): string
    {
        $selected = [];

        if ($microbiology) {
            $selected[] = self::PREFIX_MICROBIOLOGY;
        }

        if ($legionella) {
            $selected[] = self::PREFIX_LEGIONELLA;
        }

        if ($chemistry) {
            $selected[] = self::PREFIX_CHEMISTRY;
        }

        if (count($selected) === 0) {
            throw new InvalidArgumentException('Exactly one test category must be selected per sample row.');
        }

        if (count($selected) > 1) {
            throw new InvalidArgumentException('Only one test category may be selected per sample row.');
        }

        return $selected[0];
    }

    /**
     * Resolve prefix from TRF row test_category string or legacy boolean flags.
     *
     * @param  array<string, mixed>  $row
     */
    public function resolveCategoryPrefixFromRow(array $row): string
    {
        $category = strtolower(trim((string) ($row['test_category'] ?? '')));

        if ($category !== '') {
            return match ($category) {
                'microbiology', 'micro' => self::PREFIX_MICROBIOLOGY,
                'legionella' => self::PREFIX_LEGIONELLA,
                'chemistry', 'chemical_analysis', 'chemical' => self::PREFIX_CHEMISTRY,
                default => throw new InvalidArgumentException("Unknown test category: {$category}"),
            };
        }

        $microbiology = filter_var($row['microbiology'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $legionella = filter_var($row['legionella'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $chemistry = filter_var(
            $row['chemistry'] ?? $row['chemical_analysis'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        return $this->resolveCategoryPrefix($microbiology, $legionella, $chemistry);
    }

    /**
     * Next lab sample code for a job.
     * Example: 260428001-001 (category letter is not included).
     *
     * @param  string  $prefix  Retained for API compatibility; not embedded in the code.
     */
    public function nextSampleCode(string $jobNumber, string $prefix = self::PREFIX_CHEMISTRY): string
    {
        $this->assertValidJobNumber($jobNumber);

        $sequenceNo = SampleSequence::getNextSampleSequence($jobNumber, self::SEQUENCE_KEY);

        return $jobNumber . '-' . str_pad((string) $sequenceNo, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Numeric suffix only (e.g. 001) from a generated sample code.
     * Accepts both legacy (260428001-C001) and current (260428001-001) formats.
     */
    public function sampleNumberFromCode(string $sampleCode): string
    {
        if (preg_match('/-(?:[MLC])?(\d{3})$/', $sampleCode, $matches)) {
            return $matches[1];
        }

        throw new InvalidArgumentException("Cannot parse sample number from code: {$sampleCode}");
    }

    /**
     * Display helper: strip legacy category letter (C/M/L) from job sample codes.
     * 260716003-C001 → 260716003-001
     */
    public function stripCategoryPrefixFromSampleCode(string $sampleCode): string
    {
        if (preg_match('/^(\d{9})-([MLC])(\d{3})$/', $sampleCode, $matches)) {
            return $matches[1] . '-' . $matches[3];
        }

        return $sampleCode;
    }

    /**
     * COA report number linked to job.
     * Example: 260428001-R01
     */
    public function reportNumber(string $jobNumber, int $revision = 1): string
    {
        $this->assertValidJobNumber($jobNumber);
        $revision = max(1, $revision);

        return $jobNumber . '-R' . str_pad((string) $revision, 2, '0', STR_PAD_LEFT);
    }

    public function syncReportNumbersForBatch(SampleHeader $batch, int $revision): void
    {
        $jobNumber = (string) $batch->batch_code;

        if (! $this->isJobNumberFormat($jobNumber)) {
            return;
        }

        $reportNumber = $this->reportNumber($jobNumber, $revision);

        SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->update(['report_number' => $reportNumber]);
    }

    public function isJobNumberFormat(string $value): bool
    {
        return (bool) preg_match('/^\d{9}$/', $value);
    }

    public function inferPrefixFromAnalysisTypeName(?string $name): string
    {
        $normalized = strtolower(trim((string) $name));

        if ($normalized === '') {
            return self::PREFIX_CHEMISTRY;
        }

        if (str_contains($normalized, 'legionella')) {
            return self::PREFIX_LEGIONELLA;
        }

        if (str_contains($normalized, 'micro') || str_contains($normalized, 'bacter')) {
            return self::PREFIX_MICROBIOLOGY;
        }

        return self::PREFIX_CHEMISTRY;
    }

    public function persistJobNumberOnSubmissionInstance(SubmissionFormInstance $instance, string $jobNumber): void
    {
        $jobElement = SubmissionFormElement::query()
            ->whereHas('holder.section', function ($query) use ($instance): void {
                $query->where('submission_form_id', $instance->submission_form_id);
            })
            ->where('name', 'job_number')
            ->first();

        if ($jobElement) {
            SubmissionFormInstanceValue::updateOrCreate(
                [
                    'submission_form_instance_id' => $instance->id,
                    'submission_form_element_id' => $jobElement->id,
                ],
                ['value' => $jobNumber],
            );
        }

    }

    private function assertValidJobNumber(string $jobNumber): void
    {
        if (! $this->isJobNumberFormat($jobNumber)) {
            throw new InvalidArgumentException("Invalid job number format: {$jobNumber}");
        }
    }
}
