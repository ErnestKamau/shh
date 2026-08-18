<?php

namespace App\Services;

use App\Analyte;
use App\CapturedResult;
use App\StandardAnalytes;
use App\StandardValue;
use App\Standards;

class ResultRemarkService
{
    public function calculateRemark(?CapturedResult $capturedResult, ?string $resultValue, ?string $providedStandardLimit = null, ?string $defaultStandardLimit = null, ?string $reportingSymbol = null): string
    {
        $normalizedResult = $this->normalizeResult($resultValue);

        if ($normalizedResult === null) {
            return '-';
        }

        $normalizedProvided = $this->normalizeString($providedStandardLimit);
        $normalizedDefault = $this->normalizeString($defaultStandardLimit);

        $remark = null;

        if ($capturedResult) {
            $shouldUseStandards = false;

            if ($normalizedProvided === null && $normalizedDefault === null) {
                $shouldUseStandards = true;
            } elseif ($normalizedDefault !== null && $normalizedProvided === $normalizedDefault) {
                $shouldUseStandards = true;
            }

            if ($shouldUseStandards) {
                $remark = $this->calculateUsingCapturedResult($capturedResult, $normalizedResult, $reportingSymbol);
            }
        }

        if ($remark !== null) {
            return $remark;
        }

        if ($normalizedProvided !== null) {
            return $this->calculateFromManualLimit($normalizedResult, $normalizedProvided, $reportingSymbol);
        }

        if ($normalizedDefault !== null) {
            return $this->calculateFromManualLimit($normalizedResult, $normalizedDefault, $reportingSymbol);
        }

        return '-';
    }

    public function calculateUsingCapturedResult(CapturedResult $capturedResult, string $result, ?string $reportingSymbol = null): ?string
    {
        $sample = $capturedResult->sample;
        $analyte = $this->resolveAnalyteForCapturedResult($capturedResult);

        if (! $sample || ! $analyte) {
            return null;
        }

        $reportingSymbol = $this->normalizeReportingSymbol($reportingSymbol ?? $capturedResult->result_reporting_symbol ?? $capturedResult->reporting_symbol ?? null);

        if ($capturedResult->repeat_captured_id > 0) {
            $range = $capturedResult->repeatsampleresult;
            if (!empty($range)) {
                $parts = array_map('trim', preg_split('/-/', $range));
                if (count($parts) === 2 && $this->isNumeric($parts[0]) && $this->isNumeric($parts[1]) && ($this->isNumeric($result) || $this->isNotDetectableLikeResult(strtoupper(trim($result))))) {
                    $low = (float) $parts[0];
                    $high = (float) $parts[1];
                    $resultValue = (float) ($this->isNumeric($result) ? $result : 0);

                    return ($resultValue >= $low && $resultValue <= $high) ? 'PASS' : 'FAIL';
                }
            }
        }

        $remarks = [];

        $standards = [
            $sample->main_standard,
            $sample->secondary_standard,
            $sample->third_standard_id,
        ];

        // Interpret ND/Not Detected/Not Detectable as 0 for numeric comparisons
        $effectiveResult = $result;
        $upperResult = strtoupper(trim($result));
        if ($this->isNotDetectableLikeResult($upperResult)) {
            $effectiveResult = '0';
        }

        foreach ($standards as $standardId) {
            if (!$standardId) {
                continue;
            }

            $standard = Standards::find($standardId);

            if (!$standard) {
                continue;
            }

            $remark = $this->getResultRemarkForStandard($standard->id, $analyte->id, $effectiveResult, $reportingSymbol);

            if ($remark !== null && $remark !== '') {
                $remarks[] = $remark;
            }
        }

        if (empty($remarks)) {
            return null;
        }

        if (in_array('FAIL', $remarks, true)) {
            return 'FAIL';
        }

        if (in_array('-', $remarks, true) && in_array('PASS', $remarks, true)) {
            return 'PASS';
        }

        if (in_array('PASS', $remarks, true)) {
            return 'PASS';
        }

        if (in_array('-', $remarks, true)) {
            return '-';
        }

        return $remarks[0] ?? null;
    }

    /**
     * Auto-calculated PASS/FAIL for a captured result. Returns null when the
     * remark is manual or no numeric/spec comparison is possible.
     */
    public function autoRemarkForCapturedResult(CapturedResult $capturedResult, ?string $resultValue): ?string
    {
        if ((int) ($capturedResult->remark_is_manual ?? 0) === 1) {
            return null;
        }

        $capturedResult->loadMissing(['sample', 'my_analyte']);

        $reportingSymbol = $capturedResult->result_reporting_symbol
            ?? $capturedResult->reporting_symbol
            ?? null;

        $remark = $this->calculateRemark(
            $capturedResult,
            $resultValue,
            null,
            null,
            $reportingSymbol,
        );

        if (! in_array($remark, ['PASS', 'FAIL'], true)) {
            $fallbackLimit = $this->normalizeString($capturedResult->main_value);
            if ($fallbackLimit !== null) {
                $remark = $this->calculateRemark(
                    null,
                    $resultValue,
                    null,
                    $fallbackLimit,
                    $reportingSymbol,
                );
            }
        }

        return in_array($remark, ['PASS', 'FAIL'], true) ? $remark : null;
    }

    protected function getResultRemarkForStandard(string|int $standardId, string|int $analyteId, string $result, ?string $reportingSymbol): ?string
    {
        $analyteGuide = StandardAnalytes::where('analyte_id', $analyteId)
            ->where('standard_id', $standardId)
            ->first();

        if (!$analyteGuide || !isset($analyteGuide->standard_value_type)) {
            return null;
        }

        $upperResult = strtoupper(trim($result));
        $isNumeric = $this->isNumeric($result);

        // Interpretation for quantitative comparison
        $effectiveNumericResult = null;
        if ($isNumeric) {
            $effectiveNumericResult = (float) $result;
        } elseif ($this->isNotDetectableLikeResult($upperResult)) {
            $effectiveNumericResult = 0.0;
        }

        $svtCompact = strtolower(preg_replace('/[\s_]+/', '', (string) $analyteGuide->standard_value_type));

        // Case 1: Range Limits
        if ($svtCompact === 'isrange') {
            if ($effectiveNumericResult !== null && $this->isNumeric($analyteGuide->low) && $this->isNumeric($analyteGuide->high)) {
                $low = (float) $analyteGuide->low;
                $high = (float) $analyteGuide->high;

                return ($low <= $effectiveNumericResult && $effectiveNumericResult <= $high) ? 'PASS' : 'FAIL';
            }

            return null;
        }

        // Case 2: Standard Value (IsValue, ND, Nil, etc.)
        if ($svtCompact === 'isstandardvalue') {
            $standardValue = $analyteGuide->standardValue ?? ($analyteGuide->standard_value_id ? StandardValue::find($analyteGuide->standard_value_id) : null);

            if ($standardValue) {
                $code = strtoupper(trim($standardValue->code));

                // If code is IsValue, it's a numeric threshold (Max 100, etc.)
                if ($code === 'ISVALUE' && $analyteGuide->standard_is_value !== null) {
                    if ($effectiveNumericResult !== null) {
                        return $this->evaluateNumericValue($effectiveNumericResult, (float) $analyteGuide->standard_is_value, $analyteGuide->value_type, $reportingSymbol);
                    }
                    return null;
                }

                // Handle qualitative codes
                if ($code === 'NS' || $code === 'NOT SPECIFIED') {
                    return '-';
                }

                if ($this->isNotDetectableStandardCode($code)) {
                    return $this->evaluateNotDetectableResult($upperResult, $effectiveNumericResult);
                }

                if ($code === 'ABSENT') {
                    return $this->isAbsentLikeResult($upperResult) ? 'PASS' : 'FAIL';
                }

                if ($upperResult === $code) {
                    return 'PASS';
                }

                return '-';
            }
        }

        if ($upperResult === 'TN') {
            return 'FAIL';
        }

        if ($upperResult === 'PRESENT' && $reportingSymbol !== null && $reportingSymbol !== '') {
            return 'FAIL';
        }

        return null;
    }

    protected function evaluateNumericValue(float $result, float $standardValue, ?string $valueType, ?string $reportingSymbol): ?string
    {
        $valueType = strtolower($valueType ?? '');
        $symbol = trim($reportingSymbol ?? '');

        // Quantitative Interpretation: If result is '< 1', treat it as 0 (None Detected)
        if ($symbol === '<' && $result == 1.0) {
            $result = 0.0;
        }

        if (in_array($symbol, ['>', '≥', '>='], true)) {
            if (in_array($valueType, ['max', ''], true)) {
                return $result < $standardValue ? 'PASS' : 'FAIL';
            }

            if (in_array($valueType, ['min'], true)) {
                return $result >= $standardValue ? 'PASS' : 'FAIL';
            }

            if ($valueType === 'less_than') {
                return $result < $standardValue ? 'PASS' : 'FAIL';
            }

            if ($valueType === 'greater_than') {
                return $result > $standardValue ? 'PASS' : 'FAIL';
            }
        } elseif (in_array($symbol, ['<', '≤', '<='], true)) {
            if (in_array($valueType, ['max', ''], true)) {
                return $result <= $standardValue ? 'PASS' : 'FAIL';
            }

            if (in_array($valueType, ['min'], true)) {
                return $result > $standardValue ? 'PASS' : 'FAIL';
            }

            if ($valueType === 'less_than') {
                return $result < $standardValue ? 'PASS' : 'FAIL';
            }

            if ($valueType === 'greater_than') {
                return $result > $standardValue ? 'PASS' : 'FAIL';
            }
        } else {
            if (in_array($valueType, ['max', ''], true)) {
                return $result <= $standardValue ? 'PASS' : 'FAIL';
            }

            if (in_array($valueType, ['min'], true)) {
                return $result >= $standardValue ? 'PASS' : 'FAIL';
            }

            if ($valueType === 'less_than') {
                return $result < $standardValue ? 'PASS' : 'FAIL';
            }

            if ($valueType === 'greater_than') {
                return $result > $standardValue ? 'PASS' : 'FAIL';
            }
        }

        return null;
    }

    protected function calculateFromManualLimit(string $result, string $standardLimit, ?string $reportingSymbol = null): string
    {
        if ($standardLimit === '') {
            return '-';
        }

        $resultTrim = trim($result);
        $standardTrim = trim($standardLimit);
        $symbol = $this->normalizeReportingSymbol($reportingSymbol);

        $resultUpper = strtoupper($resultTrim);
        $standardUpper = strtoupper($standardTrim);

        // Treat ND / Not Detected / Not Detectable as 0 for comparisons
        $effectiveResult = $resultTrim;
        if ($this->isNotDetectableLikeResult($resultUpper)) {
            $effectiveResult = '0';
        }

        if ($standardUpper === 'ABSENT') {
            if ($this->isAbsentLikeResult($resultUpper) || $resultUpper === '') {
                return 'PASS';
            }

            if (in_array($resultUpper, ['PRESENT', 'TN'], true)) {
                return 'FAIL';
            }

            return '-';
        }

        if ($this->isNotDetectableStandardCode($standardUpper)) {
            return $this->evaluateNotDetectableResult(
                $resultUpper,
                $this->isNumeric($effectiveResult) ? (float) $effectiveResult : null
            );
        }

        if ($standardUpper === 'NS' || $standardUpper === 'NOT SPECIFIED') {
            return '-';
        }

        if (preg_match('/^(min|max)\s+(\d+(?:\.\d+)?)$/i', $standardTrim, $matches)) {
            return $this->evaluateManualLimitOperator($effectiveResult, $matches[2], strtolower($matches[1]), $symbol);
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\s+(min|max)$/i', $standardTrim, $matches)) {
            return $this->evaluateManualLimitOperator($effectiveResult, $matches[1], strtolower($matches[2]), $symbol);
        }

        // Handle numeric limits with optional prefixes (MAX, MIN, <, >, <=, >=)
        if (preg_match('/^(MAX|MIN|<|>|<=|>=)?\s*(\d+(?:\.\d+)?)$/i', $standardUpper, $matches)) {
            $prefix = $matches[1] !== '' ? $matches[1] : 'MAX'; // Default to MAX
            $standardNum = (float) $matches[2];

            if ($this->isNumeric($effectiveResult)) {
                $resultNum = (float) $effectiveResult;

                if (in_array($prefix, ['MAX', '<', '<='], true)) {
                    return $resultNum <= $standardNum ? 'PASS' : 'FAIL';
                }

                if (in_array($prefix, ['MIN', '>', '>='], true)) {
                    return $resultNum >= $standardNum ? 'PASS' : 'FAIL';
                }
            }
        }

        // Handle simple numeric comparison
        if ($this->isNumeric($effectiveResult) && $this->isNumeric($standardTrim)) {
            $resultNum = (float) $effectiveResult;
            $standardNum = (float) $standardTrim;

            return $resultNum <= $standardNum ? 'PASS' : 'FAIL';
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)$/i', $standardTrim, $matches)) {
            if ($this->isNumeric($effectiveResult)) {
                $resultNum = (float) $effectiveResult;
                $low = (float) $matches[1];
                $high = (float) $matches[2];

                return ($resultNum >= $low && $resultNum <= $high) ? 'PASS' : 'FAIL';
            }

            return '-';
        }

        if ($resultUpper === $standardUpper) {
            return 'PASS';
        }

        return '-';
    }

    public function evaluateTypedLimit(?string $result, ?string $limitValue, ?string $limitType, ?string $reportingSymbol = null): ?string
    {
        $normalizedResult = $this->normalizeResult($result);
        if ($normalizedResult === null || $limitValue === null || trim($limitValue) === '') {
            return null;
        }

        if (! $this->isNumeric($normalizedResult) || ! $this->isNumeric($limitValue)) {
            return null;
        }

        return $this->evaluateNumericValue(
            (float) $normalizedResult,
            (float) $limitValue,
            $this->normalizeLimitType($limitType),
            $reportingSymbol,
        );
    }

    protected function normalizeLimitType(?string $type): string
    {
        $normalized = strtolower(trim((string) $type));

        return match ($normalized) {
            'maximum' => 'max',
            'minimum' => 'min',
            default => $normalized,
        };
    }

    protected function normalizeResult(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function normalizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : strtoupper($trimmed);
    }

    protected function isNumeric($value): bool
    {
        if ($value === null) {
            return false;
        }

        return is_numeric($value);
    }

    protected function evaluateNotDetectableResult(string $upperResult, ?float $effectiveNumericResult): string
    {
        if ($effectiveNumericResult !== null && $effectiveNumericResult <= 0) {
            return 'PASS';
        }

        if ($this->isNotDetectableLikeResult($upperResult) || $this->isAbsentLikeResult($upperResult)) {
            return 'PASS';
        }

        if ($effectiveNumericResult !== null && $effectiveNumericResult > 0) {
            return 'FAIL';
        }

        if (in_array($upperResult, ['PRESENT', 'TN'], true)) {
            return 'FAIL';
        }

        return '-';
    }

    protected function isNotDetectableStandardCode(string $code): bool
    {
        $normalized = strtoupper(preg_replace('/[\s_\-]+/', ' ', trim($code)) ?? '');

        return in_array($normalized, [
            'ND',
            'NIL',
            'NOT DETECTABLE',
            'NOT DETECTED',
            'NON DETECTABLE',
            'NONE DETECTED',
            'NONE DETECTABLE',
        ], true);
    }

    protected function isNotDetectableLikeResult(string $upperResult): bool
    {
        $normalized = strtoupper(preg_replace('/[\s_\-]+/', ' ', trim($upperResult)) ?? '');

        return in_array($normalized, [
            'ND',
            'NIL',
            'NOT DETECTABLE',
            'NOT DETECTED',
            'NON DETECTABLE',
            'NONE DETECTED',
            'NONE DETECTABLE',
        ], true);
    }

    protected function isAbsentLikeResult(string $upperResult): bool
    {
        $normalized = strtoupper(preg_replace('/[\s_\-]+/', ' ', trim($upperResult)) ?? '');

        return in_array($normalized, [
            'ABSENT',
            'ND',
            'NIL',
            'NOT DETECTABLE',
            'NOT DETECTED',
            'NON DETECTABLE',
            'NONE DETECTED',
            'NONE DETECTABLE',
            '0',
        ], true);
    }

    protected function evaluateManualLimitOperator(
        string $result,
        string $limitValue,
        string $operator,
        ?string $reportingSymbol = null,
    ): string {
        if (! $this->isNumeric($result) || ! $this->isNumeric($limitValue)) {
            return '-';
        }

        $remark = $this->evaluateNumericValue(
            (float) $result,
            (float) $limitValue,
            $operator,
            $this->normalizeReportingSymbol($reportingSymbol),
        );

        return $remark ?? '-';
    }

    protected function normalizeReportingSymbol(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function resolveAnalyteForCapturedResult(CapturedResult $capturedResult): ?Analyte
    {
        if ($capturedResult->relationLoaded('my_analyte')) {
            return $capturedResult->my_analyte;
        }

        if (! empty($capturedResult->analyte_id)) {
            return Analyte::query()->find($capturedResult->analyte_id);
        }

        return null;
    }
}
