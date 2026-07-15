<?php

namespace App\Services\Qc;

use App\CapturedResult;
use App\StandardAnalytes;

class QcPassFailEvaluator
{
    public const STATUS_PASSED = 'PASSED';

    public const STATUS_FAILED = 'FAILED';

    /**
     * Evaluate a captured result against repeat tolerance or standard analyte bands.
     */
    public function evaluate(
        CapturedResult $capturedResult,
        ?float $repeatTolerancePercent = null,
        mixed $mainStandardId = null,
    ): string {
        if ((int) ($capturedResult->repeat_captured_id ?? 0) > 0) {
            return $this->evaluateRepeat(
                $capturedResult,
                $repeatTolerancePercent ?? 0.0
            );
        }

        return $this->evaluateAgainstStandard(
            $capturedResult,
            $mainStandardId
        );
    }

    public function evaluateRepeat(CapturedResult $capturedResult, float $tolerancePercent): string
    {
        $previous = CapturedResult::query()->find($capturedResult->repeat_captured_id);

        if (
            ! $previous
            || ! is_numeric($previous->result)
            || ! is_numeric($capturedResult->result)
        ) {
            return self::STATUS_PASSED;
        }

        $previousValue = (float) $previous->result;
        $currentValue = (float) $capturedResult->result;
        $delta = ($tolerancePercent / 100) * $previousValue;
        $low = $previousValue - $delta;
        $high = $previousValue + $delta;

        if ($currentValue < $low || $currentValue > $high) {
            return self::STATUS_FAILED;
        }

        return self::STATUS_PASSED;
    }

    public function evaluateAgainstStandard(CapturedResult $capturedResult, mixed $mainStandardId): string
    {
        if (! is_numeric($capturedResult->result)) {
            return self::STATUS_PASSED;
        }

        $standardId = $mainStandardId ?? 0;
        if (! $standardId || (is_numeric($standardId) && (float) $standardId <= 0)) {
            return self::STATUS_PASSED;
        }

        $standardAnalyte = StandardAnalytes::query()
            ->where('standard_id', $standardId)
            ->where('analyte_id', $capturedResult->analyte_id)
            ->first();

        if (! $standardAnalyte) {
            return self::STATUS_PASSED;
        }

        $result = (float) $capturedResult->result;
        if ($result < (float) $standardAnalyte->low || $result > (float) $standardAnalyte->high) {
            return self::STATUS_FAILED;
        }

        return self::STATUS_PASSED;
    }

    /**
     * Compute low/high bands from expected value and tolerances.
     *
     * @return array{low: float, high: float}
     */
    public function computeToleranceBands(
        float $expectedValue,
        float $tolerance1,
        float $tolerance2,
        bool $useAbsolute,
    ): array {
        if ($useAbsolute) {
            return [
                'low' => $tolerance1,
                'high' => $tolerance2,
            ];
        }

        return [
            'low' => $expectedValue - $tolerance1,
            'high' => $expectedValue + $tolerance2,
        ];
    }
}
