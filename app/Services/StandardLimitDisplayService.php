<?php

namespace App\Services;

use App\CapturedResult;
use App\StandardAnalytes;
use App\Standards;
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
     * Resolve the standard name (or code) for a captured result.
     */
    public function standardNameForCapturedResult(CapturedResult $captured, string|int|null $fallbackStandardId = null): ?string
    {
        $standardId = $captured->main_standard_id
            ?: $fallbackStandardId
            ?: $captured->sample?->main_standard;

        if (! $standardId) {
            return null;
        }

        $standard = Standards::query()->find($standardId);
        if (! $standard) {
            return null;
        }

        $name = trim((string) ($standard->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $code = trim((string) ($standard->code ?? ''));

        return $code !== '' ? $code : null;
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
     * Build display main_value from the richer Edit Standard Limit form.
     *
     * @param  array{
     *     value_type?: string,
     *     range_low?: string|null,
     *     range_high?: string|null,
     *     standard_value_id?: string|null,
     *     matrix_operator?: string|null,
     *     matrix_value?: string|null,
     *     standard_value?: string|null,
     *     limit_type?: string|null
     * }  $payload
     */
    public function formatMainValueFromStructuredEditForm(array $payload): string
    {
        $valueType = $payload['value_type'] ?? null;

        // Backward compatibility with the previous free-text + MAX/MIN/RANGE form.
        if ($valueType === null && array_key_exists('standard_value', $payload)) {
            return $this->formatMainValueFromEditForm(
                (string) ($payload['standard_value'] ?? ''),
                (string) ($payload['limit_type'] ?? 'MAX'),
            );
        }

        if (($valueType ?? 'use_value') === 'range') {
            $low = trim((string) ($payload['range_low'] ?? ''));
            $high = trim((string) ($payload['range_high'] ?? ''));

            return trim($low.' - '.$high);
        }

        $standardValueId = $payload['standard_value_id'] ?? null;
        $standardValue = $standardValueId ? StandardValue::find($standardValueId) : null;

        if (! $standardValue) {
            return 'NS';
        }

        if ($standardValue->code === 'IsValue') {
            $operator = strtolower(trim((string) ($payload['matrix_operator'] ?? '')));
            $actual = trim((string) ($payload['matrix_value'] ?? ''));

            if ($operator === 'less_than' || $operator === '<') {
                return '< '.$actual;
            }

            if ($operator === 'greater_than' || $operator === '>') {
                return '> '.$actual;
            }

            if (in_array($operator, ['min', 'max'], true) && $actual !== '') {
                return $operator.' '.$actual;
            }

            return $actual !== '' ? $actual : 'NS';
        }

        return (string) ($standardValue->code ?: $standardValue->name ?: 'NS');
    }

    /**
     * @return array{
     *     value_type: string,
     *     range_low: string,
     *     range_high: string,
     *     standard_value_id: string|null,
     *     matrix_operator: string,
     *     matrix_value: string,
     *     standard_value: string,
     *     limit_type: string
     * }
     */
    /**
     * Resolve Edit Standard Limit modal fields for a captured result.
     * Prefers an override on captured_results.main_value; otherwise uses the
     * sample's main standard analyte configuration (e.g. ABSENT).
     *
     * @return array{
     *     value_type: string,
     *     range_low: string,
     *     range_high: string,
     *     standard_value_id: string|null,
     *     matrix_operator: string,
     *     matrix_value: string,
     *     standard_value: string,
     *     limit_type: string
     * }
     */
    public function structuredEditFormForCapturedResult(CapturedResult $captured): array
    {
        $fromMain = $this->parseStructuredEditFormFromMainValue($captured->main_value);

        if ($this->structuredEditFormHasSelection($fromMain)) {
            return $fromMain;
        }

        $standardId = $captured->main_standard_id
            ?: $captured->sample?->main_standard;

        if ($standardId && $captured->analyte_id) {
            $fromAnalyte = $this->structuredEditFormFromStandardAnalyte($standardId, $captured->analyte_id);
            if ($fromAnalyte !== null) {
                return $fromAnalyte;
            }
        }

        $display = $this->forCapturedResult($captured, $standardId);
        if ($display !== null && trim($display) !== '') {
            $fromDisplay = $this->parseStructuredEditFormFromMainValue($display);
            if ($this->structuredEditFormHasSelection($fromDisplay)) {
                return $fromDisplay;
            }
        }

        return $fromMain;
    }

    /**
     * @param  array{
     *     value_type?: string,
     *     range_low?: string,
     *     range_high?: string,
     *     standard_value_id?: string|null
     * }  $data
     */
    public function structuredEditFormHasSelection(array $data): bool
    {
        if (($data['value_type'] ?? 'use_value') === 'range') {
            return trim((string) ($data['range_low'] ?? '')) !== ''
                || trim((string) ($data['range_high'] ?? '')) !== '';
        }

        return trim((string) ($data['standard_value_id'] ?? '')) !== '';
    }

    /**
     * @return array{
     *     value_type: string,
     *     range_low: string,
     *     range_high: string,
     *     standard_value_id: string|null,
     *     matrix_operator: string,
     *     matrix_value: string,
     *     standard_value: string,
     *     limit_type: string
     * }|null
     */
    protected function structuredEditFormFromStandardAnalyte(string|int $standardId, string|int $analyteId): ?array
    {
        $stdAnalyte = StandardAnalytes::query()
            ->where('standard_id', $standardId)
            ->where('analyte_id', $analyteId)
            ->first();

        if (! $stdAnalyte) {
            return null;
        }

        $svt = strtolower(preg_replace('/[\s_]+/', '', (string) $stdAnalyte->standard_value_type) ?? '');

        if ($svt === 'isrange') {
            return [
                'value_type' => 'range',
                'range_low' => (string) ($stdAnalyte->low ?? ''),
                'range_high' => (string) ($stdAnalyte->high ?? ''),
                'standard_value_id' => null,
                'matrix_operator' => '',
                'matrix_value' => '',
                'standard_value' => '',
                'limit_type' => 'RANGE',
            ];
        }

        if ($svt === 'isstandardvalue' && ! empty($stdAnalyte->standard_value_id)) {
            $standardValue = StandardValue::query()->find($stdAnalyte->standard_value_id);

            $result = [
                'value_type' => 'use_value',
                'range_low' => '',
                'range_high' => '',
                'standard_value_id' => (string) $stdAnalyte->standard_value_id,
                'matrix_operator' => '',
                'matrix_value' => '',
                'standard_value' => (string) ($standardValue?->code ?? ''),
                'limit_type' => 'MAX',
            ];

            if ($standardValue && strcasecmp((string) $standardValue->code, 'IsValue') === 0) {
                $result['matrix_operator'] = strtolower((string) ($stdAnalyte->value_type ?? 'max'));
                $result['matrix_value'] = (string) ($stdAnalyte->standard_is_value ?? '');
            }

            return $result;
        }

        return null;
    }

    public function parseStructuredEditFormFromMainValue(?string $mainValue): array
    {
        $legacy = $this->parseEditFormFromMainValue($mainValue);
        $mainValue = trim((string) $mainValue);

        $result = [
            'value_type' => 'use_value',
            'range_low' => '',
            'range_high' => '',
            'standard_value_id' => null,
            'matrix_operator' => '',
            'matrix_value' => '',
            'standard_value' => $legacy['standard_value'],
            'limit_type' => $legacy['limit_type'],
        ];

        if ($mainValue === '' || $mainValue === 'NS') {
            return $result;
        }

        if ($legacy['limit_type'] === 'RANGE') {
            $parts = preg_split('/\s*-\s*/', $mainValue) ?: [];
            $result['value_type'] = 'range';
            $result['range_low'] = trim((string) ($parts[0] ?? ''));
            $result['range_high'] = trim((string) ($parts[1] ?? ''));

            return $result;
        }

        if (in_array($legacy['limit_type'], ['MIN', 'MAX'], true) && $legacy['standard_value'] !== '') {
            $isValue = StandardValue::query()->where('code', 'IsValue')->first();
            $result['value_type'] = 'use_value';
            $result['standard_value_id'] = $isValue?->id;
            $result['matrix_operator'] = strtolower($legacy['limit_type']);
            $result['matrix_value'] = $legacy['standard_value'];

            return $result;
        }

        if (preg_match('/^([<>])\s*(\d+(?:\.\d+)?)$/', $mainValue, $matches)) {
            $isValue = StandardValue::query()->where('code', 'IsValue')->first();
            $result['value_type'] = 'use_value';
            $result['standard_value_id'] = $isValue?->id;
            $result['matrix_operator'] = $matches[1] === '<' ? 'less_than' : 'greater_than';
            $result['matrix_value'] = $matches[2];

            return $result;
        }

        $lookup = StandardValue::query()
            ->where(function ($query) use ($mainValue) {
                $query->where('code', $mainValue)
                    ->orWhere('name', $mainValue);
            })
            ->first();

        if ($lookup) {
            $result['value_type'] = 'use_value';
            $result['standard_value_id'] = $lookup->id;
        }

        return $result;
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
