<?php

namespace App\Services;

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
            return $this->calculateFromManualLimit($normalizedResult, $normalizedProvided);
        }

        if ($normalizedDefault !== null) {
            return $this->calculateFromManualLimit($normalizedResult, $normalizedDefault);
        }

        return '-';
    }

    public function calculateUsingCapturedResult(CapturedResult $capturedResult, string $result, ?string $reportingSymbol = null): ?string
    {
        $sample = $capturedResult->sample;
        $analyte = $capturedResult->analyte;

        if (!$sample || !$analyte) {
            return null;
        }

        $reportingSymbol = $this->normalizeString($reportingSymbol ?? $capturedResult->result_reporting_symbol ?? $capturedResult->reporting_symbol ?? null);

        if ($capturedResult->repeat_captured_id > 0) {
            $range = $capturedResult->repeatsampleresult;
            if (!empty($range)) {
                $parts = array_map('trim', preg_split('/-/', $range));
                if (count($parts) === 2 && $this->isNumeric($parts[0]) && $this->isNumeric($parts[1]) && ($this->isNumeric($result) || in_array(strtoupper(trim($result)), ['ND', 'NOT DETECTED'], true))) {
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

        // Interpret ND/Not Detected as 0 for numeric comparisons
        $effectiveResult = $result;
        $upperResult = strtoupper(trim($result));
        if (in_array($upperResult, ['ND', 'NOT DETECTED'], true)) {
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

    protected function getResultRemarkForStandard(int $standardId, int $analyteId, string $result, ?string $reportingSymbol): ?string
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
        } elseif (in_array($upperResult, ['ND', 'NOT DETECTED'], true)) {
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
                switch ($code) {
                    case 'NS':
                        return '-';
                    case 'NIL':
                    case 'ND':
                        if ($effectiveNumericResult !== null && $effectiveNumericResult <= 0) {
                            return 'PASS';
                        }
                        if (in_array($upperResult, ['ND', 'NOT DETECTED', 'NIL', 'ABSENT'], true)) {
                            return 'PASS';
                        }
                        return 'FAIL';
                    case 'ABSENT':
                        return in_array($upperResult, ['ABSENT', 'ND', 'NOT DETECTED', 'NIL', '0'], true) ? 'PASS' : 'FAIL';
                    default:
                        if ($upperResult === $code) {
                            return 'PASS';
                        }
                        return '-';
                }
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

        if ($symbol === '>') {
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
        } elseif ($symbol === '<') {
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

    protected function calculateFromManualLimit(string $result, string $standardLimit): string
    {
        if ($standardLimit === '') {
            return '-';
        }

        $resultTrim = trim($result);
        $standardTrim = trim($standardLimit);

        $resultUpper = strtoupper($resultTrim);
        $standardUpper = strtoupper($standardTrim);

        // Treat ND/Not Detected as 0 for comparisons
        $effectiveResult = $resultTrim;
        if (in_array($resultUpper, ['ND', 'NOT DETECTED'], true)) {
            $effectiveResult = '0';
        }

        if ($standardUpper === 'ABSENT') {
            if (in_array($resultUpper, ['ABSENT', 'ND', 'NOT DETECTED', 'NIL', '0', ''], true)) {
                return 'PASS';
            }

            if (in_array($resultUpper, ['PRESENT', 'TN'], true)) {
                return 'FAIL';
            }

            return '-';
        }

        if ($standardUpper === 'ND' || $standardUpper === 'NIL') {
            if (in_array($resultUpper, ['ND', 'NOT DETECTED', 'NIL', 'ABSENT'], true)) {
                return 'PASS';
            }

            if ($this->isNumeric($effectiveResult) && (float) $effectiveResult <= 0) {
                return 'PASS';
            }

            if ($this->isNumeric($effectiveResult) && (float) $effectiveResult > 0) {
                return 'FAIL';
            }

            return '-';
        }

        if ($standardUpper === 'NS') {
            return '-';
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
}
