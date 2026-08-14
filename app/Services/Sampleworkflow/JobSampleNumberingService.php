<?php

namespace App\Services\Sampleworkflow;

use App\Models\JobNumberSequence;
use App\Models\SampleSequence;
use App\Models\TechnicalJobNumberSequence;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    /** Prefix for technical-user (sandbox) job numbers so production sequences stay untouched. */
    public const TECHNICAL_JOB_PREFIX = 'T';

    /**
     * Generate job/batch number: YYMMDD + 3-digit yearly sequence (resets each calendar year).
     * Example: 260428001 — sequence continues across days; next year starts again at 001.
     *
     * Technical (sandbox) jobs use a separate counter and a leading T, e.g. T260428001.
     */
    public function generateJobNumber(?Carbon $at = null, bool $technical = false): string
    {
        if ($technical) {
            return $this->generateTechnicalJobNumber($at);
        }

        $at ??= Carbon::now();
        $datePart = $at->format('ymd');
        $yearPart = $at->format('y');

        return DB::transaction(function () use ($datePart, $yearPart): string {
            $next = $this->claimNextYearlySequence(JobNumberSequence::class, $yearPart);

            return $datePart.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sandbox job number: T + YYMMDD + 3-digit yearly sequence from technical_job_number_sequences.
     * Example: T260428001
     */
    public function generateTechnicalJobNumber(?Carbon $at = null): string
    {
        $at ??= Carbon::now();
        $datePart = $at->format('ymd');
        $yearPart = $at->format('y');

        return DB::transaction(function () use ($datePart, $yearPart): string {
            $next = $this->claimNextYearlySequence(TechnicalJobNumberSequence::class, $yearPart);

            return self::TECHNICAL_JOB_PREFIX.$datePart.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Increment and return the next sequence for a calendar year (YY).
     * Seeds from legacy daily rows when a yearly row does not exist yet.
     *
     * @param  class-string<JobNumberSequence|TechnicalJobNumberSequence>  $modelClass
     */
    private function claimNextYearlySequence(string $modelClass, string $yearPart): int
    {
        $sequence = $modelClass::query()
            ->where('date_ymd', $yearPart)
            ->lockForUpdate()
            ->first();

        if ($sequence === null) {
            $legacyMax = (int) $modelClass::query()
                ->where('date_ymd', 'like', $yearPart.'%')
                ->where('date_ymd', '!=', $yearPart)
                ->sum('last_sequence');

            $sequence = $modelClass::query()->create([
                'date_ymd' => $yearPart,
                'last_sequence' => $legacyMax,
            ]);

            $modelClass::query()
                ->where('date_ymd', 'like', $yearPart.'%')
                ->where('date_ymd', '!=', $yearPart)
                ->delete();

            $sequence = $modelClass::query()
                ->where('id', $sequence->id)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                throw new RuntimeException("Failed to lock yearly job number sequence for year {$yearPart}.");
            }
        }

        $next = (int) $sequence->last_sequence + 1;

        if ($next > 999) {
            throw new RuntimeException("Yearly job number limit exceeded for year {$yearPart}.");
        }

        $sequence->update(['last_sequence' => $next]);

        return $next;
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
     * The category may hold several checkbox selections ("microbiology,chemistry");
     * the first recognised one wins because a sample code carries a single category.
     *
     * @param  array<string, mixed>  $row
     */
    public function resolveCategoryPrefixFromRow(array $row): string
    {
        $category = strtolower(trim((string) ($row['test_category'] ?? '')));

        if ($category !== '') {
            $unknown = [];

            foreach (preg_split('/[,;|]+/', $category) ?: [] as $token) {
                $token = trim($token);

                // "1" / "0" are legacy artefacts of checkbox maps flattened by value.
                if ($token === '' || preg_match('/^[01]$/', $token) === 1) {
                    continue;
                }

                $prefix = match ($token) {
                    'microbiology', 'micro' => self::PREFIX_MICROBIOLOGY,
                    'legionella' => self::PREFIX_LEGIONELLA,
                    'chemistry', 'chemical_analysis', 'chemical' => self::PREFIX_CHEMISTRY,
                    default => null,
                };

                if ($prefix !== null) {
                    return $prefix;
                }

                $unknown[] = $token;
            }

            if ($unknown !== []) {
                throw new InvalidArgumentException('Unknown test category: '.implode(',', $unknown));
            }
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
        if (preg_match('/^(T?\d{9})-([MLC])(\d{3})$/', $sampleCode, $matches)) {
            return $matches[1] . '-' . $matches[3];
        }

        return $sampleCode;
    }

    /**
     * COA report number linked to job.
     * Example: 260428001-R01 (or configured amendment pattern when amended)
     */
    public function reportNumber(string $jobNumber, int $revision = 1, bool $useAmendmentFormat = false): string
    {
        $this->assertValidJobNumber($jobNumber);
        $revision = max(1, $revision);

        if ($useAmendmentFormat || $revision > 1) {
            return app(AmendmentReportConfigurationService::class)
                ->formatReportNumber($jobNumber, $revision);
        }

        return $jobNumber . '-R' . str_pad((string) $revision, 2, '0', STR_PAD_LEFT);
    }

    public function syncReportNumbersForBatch(SampleHeader $batch, int $revision, bool $useAmendmentFormat = false): void
    {
        $jobNumber = (string) $batch->batch_code;

        if (! $this->isJobNumberFormat($jobNumber)) {
            return;
        }

        $reportNumber = $this->reportNumber($jobNumber, $revision, $useAmendmentFormat);

        SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->update(['report_number' => $reportNumber]);
    }

    /**
     * Apply configured amendment wording/numbering when a batch is amended:
     * report numbers + optional sample-code suffixes.
     *
     * Original jobs use is_amendment = 1 (revision 1). Suffixes must only apply
     * for true amendments (revision > 1).
     */
    public function applyAmendmentNumbering(SampleHeader $batch, int $revision): void
    {
        $revision = max(1, $revision);

        if ($revision <= 1) {
            $this->clearSampleCodeSuffixesForBatch($batch);
            $this->syncReportNumbersForBatch($batch, 1, false);

            return;
        }

        $this->syncReportNumbersForBatch($batch, $revision, true);
        $this->syncSampleCodeSuffixesForBatch($batch, $revision);
    }

    /**
     * Append (or refresh) the configured amendment suffix on every sample code.
     * Leave sample codes unchanged when the suffix format is blank.
     * Revision 1 (original job) clears any accidental suffix instead of applying -V1.
     */
    public function syncSampleCodeSuffixesForBatch(SampleHeader $batch, int $revision): void
    {
        $revision = max(1, $revision);

        if ($revision <= 1) {
            $this->clearSampleCodeSuffixesForBatch($batch);

            return;
        }

        $config = app(AmendmentReportConfigurationService::class);
        $suffix = $config->formatSampleNumberSuffix($revision);

        $samples = SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->get(['id', 'sample_code']);

        foreach ($samples as $sample) {
            $current = trim((string) ($sample->sample_code ?? ''));
            if ($current === '') {
                continue;
            }

            $base = $config->stripSampleNumberSuffix($current);
            $next = $suffix === '' ? $base : $base.$suffix;

            if ($next !== $current) {
                $sample->sample_code = $next;
                $sample->save();
            }
        }
    }

    /**
     * Strip amendment suffixes from sample codes (heal accidental -V1 on original jobs).
     */
    public function clearSampleCodeSuffixesForBatch(SampleHeader $batch): void
    {
        $config = app(AmendmentReportConfigurationService::class);

        $samples = SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->get(['id', 'sample_code']);

        foreach ($samples as $sample) {
            $current = trim((string) ($sample->sample_code ?? ''));
            if ($current === '') {
                continue;
            }

            $base = $config->stripSampleNumberSuffix($current);
            if ($base !== $current) {
                $sample->sample_code = $base;
                $sample->save();
            }
        }
    }

    public function isJobNumberFormat(string $value): bool
    {
        return (bool) preg_match('/^T?\d{9}$/', $value);
    }

    public function isTechnicalJobNumber(string $value): bool
    {
        return (bool) preg_match('/^T\d{9}$/', $value);
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

        try {
            app(TestRequestFormPdfService::class)->generateAndStore($instance->fresh([
                'values.element',
                'submissionForm.sampleTypeCategories',
                'batches.samples',
            ]) ?? $instance);
            app(SubmissionFormInstanceDocumentAttachmentService::class)
                ->attachTestRequestForm($instance, null, regenerate: false);
        } catch (\Throwable $exception) {
            Log::warning('TRF PDF refresh after job number persist failed.', [
                'instance_id' => $instance->id,
                'job_number' => $jobNumber,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function assertValidJobNumber(string $jobNumber): void
    {
        if (! $this->isJobNumberFormat($jobNumber)) {
            throw new InvalidArgumentException("Invalid job number format: {$jobNumber}");
        }
    }
}
