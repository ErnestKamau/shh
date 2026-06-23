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
    public function forCapturedResult(CapturedResult $captured): ?string
    {
        if ($captured->main_value) {
            return (string) $captured->main_value;
        }

        $standardId = $captured->main_standard_id ?: $captured->sample?->main_standard;

        return $this->format($standardId, $captured->analyte_id);
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
}
