<?php

namespace App\Services;

use App\CapturedResult;
use App\StandardAnalytes;
use App\StandardValue;

class StandardLimitDisplayService
{
    /**
     * Resolve the display string for a captured result's main standard limit.
     */
    public function forCapturedResult(CapturedResult $captured, string|int|null $fallbackStandardId = null): ?string
    {
        $standardId = $captured->main_standard_id
            ?: $fallbackStandardId
            ?: $captured->sample?->main_standard;

        $fromStandard = $this->format($standardId, $captured->analyte_id);

        $mainValue = $captured->main_value;
        if ($mainValue && $mainValue !== 'NS') {
            if ($fromStandard !== null && $this->isLimitTypeOnly((string) $mainValue)) {
                return $fromStandard;
            }

            return (string) $mainValue;
        }

        return $fromStandard;
    }

    /**
     * True when stored main_value is only a limit qualifier (e.g. "max") without the numeric standard.
     */
    private function isLimitTypeOnly(string $mainValue): bool
    {
        $normalized = strtolower(trim($mainValue));

        return in_array($normalized, [
            'max', 'min', 'less_than', 'greater_than', '<', '>',
        ], true);
    }

    /**
     * @return array{display: string, value: mixed}
     */
    public function resolve(string|int|null $standardId, string|int|null $analyteId, mixed $capturedValue = null): array
    {
        if (! $standardId) {
            return ['display' => $capturedValue ?: 'NS', 'value' => $capturedValue];
        }

        try {
            $stdAnalyte = StandardAnalytes::query()
                ->where('standard_id', $standardId)
                ->where('analyte_id', $analyteId)
                ->first();

            if (! $stdAnalyte) {
                return ['display' => $capturedValue ?: 'NS', 'value' => $capturedValue];
            }

            $displayValue = '';
            $rawValue = '';
            $limitType = '';

            if ($stdAnalyte->standard_value_type === 'is_range') {
                $displayValue = $stdAnalyte->low.' - '.$stdAnalyte->high;
                $rawValue = $displayValue;
            } elseif ($stdAnalyte->standard_value_type === 'is_standard_value') {
                $sv = StandardValue::find($stdAnalyte->standard_value_id);
                if ($sv) {
                    if ($sv->code === 'IsValue') {
                        $rawValue = $stdAnalyte->standard_is_value;
                        $limitType = $stdAnalyte->value_type;
                    } else {
                        $rawValue = $sv->code;
                        $displayValue = $sv->code;
                    }
                }
            }

            if ($displayValue === '' && $rawValue !== '') {
                if ($limitType === 'less_than' || $limitType === '<') {
                    $displayValue = '< '.$rawValue;
                } elseif ($limitType === 'greater_than' || $limitType === '>') {
                    $displayValue = '> '.$rawValue;
                } elseif ($limitType && $rawValue) {
                    $displayValue = strtolower((string) $limitType).' '.$rawValue;
                } else {
                    $displayValue = (string) $rawValue;
                }
            }

            if ($displayValue === '') {
                $displayValue = 'NS';
            }

            return ['display' => $displayValue, 'value' => $rawValue];
        } catch (\Exception $e) {
            return ['display' => $capturedValue ?: '-', 'value' => $capturedValue];
        }
    }

    public function format(string|int|null $standardId, string|int|null $analyteId, mixed $capturedValue = null): ?string
    {
        $info = $this->resolve($standardId, $analyteId, $capturedValue);
        $display = $info['display'] ?? null;

        if ($display === null || $display === '' || $display === 'NS' || $display === '-') {
            return null;
        }

        return (string) $display;
    }

    public function formatMainValueFromEditForm(string $standardValue, string $limitType): string
    {
        $standardValue = trim($standardValue);
        $limitType = strtoupper(trim($limitType));

        if ($limitType === 'RANGE') {
            return $standardValue;
        }

        if ($limitType === 'MIN') {
            return 'min '.$standardValue;
        }

        return 'max '.$standardValue;
    }

    /**
     * @return array{standard_value: string, limit_type: string}
     */
    public function parseEditFormFromMainValue(?string $mainValue): array
    {
        $mainValue = trim((string) $mainValue);

        if ($mainValue === '' || $mainValue === 'NS') {
            return ['standard_value' => '', 'limit_type' => 'MAX'];
        }

        if (preg_match('/^(min|max)\s+(\d+(?:\.\d+)?)$/i', $mainValue, $matches)) {
            return [
                'standard_value' => $matches[2],
                'limit_type' => strtoupper($matches[1]),
            ];
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\s+(min|max)$/i', $mainValue, $matches)) {
            return [
                'standard_value' => $matches[1],
                'limit_type' => strtoupper($matches[2]),
            ];
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)$/i', $mainValue)) {
            return [
                'standard_value' => $mainValue,
                'limit_type' => 'RANGE',
            ];
        }

        return [
            'standard_value' => $mainValue,
            'limit_type' => 'MAX',
        ];
    }
}
