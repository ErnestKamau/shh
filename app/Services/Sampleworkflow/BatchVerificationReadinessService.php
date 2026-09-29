<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\SampleHeader;
use Illuminate\Support\Collection;

class BatchVerificationReadinessService
{
    public const REPORT_LEVEL_FINAL = '0';

    public const REPORT_LEVEL_PRELIMINARY = '1';

    public const REPORT_LEVEL_DRAFT = '2';

    /** Partial / interim report — distinct from Preliminary and Draft. */
    public const REPORT_LEVEL_PARTIAL_INTERIM = '3';

    /**
     * Placeholder / unfinished values that do not count as entered results.
     *
     * @var list<string>
     */
    private const UNENTERED_RESULT_VALUES = ['', 'no attachment'];

    /**
     * Whether the batch has at least one capturable result with a real value entered.
     */
    public function hasEnteredResults(SampleHeader|string $batch): bool
    {
        return $this->enteredResultsCount($batch) > 0;
    }

    /**
     * Whether the batch may be moved from Samples In Lab to Sample Verification.
     *
     * Batches with only "no result capture" analysis types are allowed through.
     * Batches that need result capture must have every capturable result entered
     * unless the selected report level is Partial / interim.
     */
    public function canMoveToVerification(SampleHeader|string $batch, string|int|null $reportLevel = null): bool
    {
        return $this->blockingReason($batch, $reportLevel) === null;
    }

    /**
     * Whether the Send for Verification action may open (user can still pick Partial / interim).
     */
    public function canOpenVerificationModal(SampleHeader|string $batch): bool
    {
        return $this->canMoveToVerification($batch)
            || $this->hasEnteredResults($batch);
    }

    public function isPartialInterimLevel(string|int|null $reportLevel): bool
    {
        return (string) $reportLevel === self::REPORT_LEVEL_PARTIAL_INTERIM;
    }

    public function isPartialInterimBatch(SampleHeader $batch): bool
    {
        return (int) ($batch->prelim_report_status ?? 0) === (int) self::REPORT_LEVEL_PARTIAL_INTERIM;
    }

    public function blockingReason(SampleHeader|string $batch, string|int|null $reportLevel = null): ?string
    {
        $batchId = $batch instanceof SampleHeader ? (string) $batch->id : (string) $batch;

        return $this->blockingReasonForResults(
            CapturedResult::query()
                ->where('sample_header_id', $batchId)
                ->whereValidUuidAnalyteId()
                ->get(['id', 'result', 'has_no_result_capture']),
            $reportLevel
        );
    }

    /**
     * @param  Collection<int, CapturedResult>|iterable<int, CapturedResult>  $rows
     */
    public function blockingReasonForResults(iterable $rows, string|int|null $reportLevel = null): ?string
    {
        $collection = $rows instanceof Collection ? $rows : collect($rows);
        $capturable = $collection->filter(fn (CapturedResult $row): bool => $this->requiresResultCapture($row));
        $allowPartial = $this->isPartialInterimLevel($reportLevel);

        if ($capturable->isEmpty()) {
            if ($collection->isEmpty()) {
                return 'No results have been set up for this batch. Capture or configure sample results before sending to verification.';
            }

            // Only has_no_result_capture rows — nothing to enter.
            return null;
        }

        $entered = $this->countEntered($capturable);
        $missing = $capturable->count() - $entered;

        if ($entered === 0) {
            return $allowPartial
                ? 'Enter at least one sample result before sending a Partial / interim report to verification.'
                : 'No results have been entered for this batch. Capture all sample results before sending to verification.';
        }

        if ($allowPartial) {
            return null;
        }

        if ($missing > 0) {
            return sprintf(
                '%d sample result(s) are still missing. Capture all results before sending to verification, or choose Partial / interim report.',
                $missing
            );
        }

        return null;
    }

    public function enteredResultsCount(SampleHeader|string $batch): int
    {
        $batchId = $batch instanceof SampleHeader ? (string) $batch->id : (string) $batch;

        $rows = CapturedResult::query()
            ->where('sample_header_id', $batchId)
            ->whereValidUuidAnalyteId()
            ->get(['id', 'result', 'has_no_result_capture']);

        return $this->countEntered(
            $rows->filter(fn (CapturedResult $row): bool => $this->requiresResultCapture($row))
        );
    }

    public function isEnteredResult(CapturedResult $row): bool
    {
        $resultText = strtolower(trim((string) ($row->result ?? '')));

        return $resultText !== '' && ! in_array($resultText, self::UNENTERED_RESULT_VALUES, true);
    }

    public function requiresResultCapture(CapturedResult $row): bool
    {
        return ! (bool) ($row->has_no_result_capture ?? false);
    }

    /**
     * @param  Collection<int, CapturedResult>  $rows
     */
    protected function countEntered(Collection $rows): int
    {
        return $rows->filter(fn (CapturedResult $row): bool => $this->isEnteredResult($row))->count();
    }
}
