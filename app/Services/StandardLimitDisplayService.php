<?php

namespace App\Services;

use App\Analyte;
use App\CapturedResult;
use App\StandardAnalytes;
use App\Standards;
use App\StandardValue;

class StandardLimitDisplayService
{
    /** @var array<string, StandardAnalytes|false> */
    private array $standardAnalyteCache = [];

    /**
     * Resolve the display string for a captured result's effective standard limit.
     * Prefers main, then secondary, then third when the analyte only exists on a later specification.
     */
    public function forCapturedResult(CapturedResult $captured, string|int|null $fallbackStandardId = null): ?string
    {
        return $this->resolveCapturedResultSpecification($captured, $fallbackStandardId)['limit'];
    }

    /**
     * Resolve the standard name (or code) for the specification that supplies the limit.
     */
    public function standardNameForCapturedResult(CapturedResult $captured, string|int|null $fallbackStandardId = null): ?string
    {
        return $this->resolveCapturedResultSpecification($captured, $fallbackStandardId)['name'];
    }

    /**
     * Pick the first usable specification among main → secondary → third.
     *
     * @return array{standard_id: ?string, name: ?string, limit: ?string}
     */
    public function resolveCapturedResultSpecification(
        CapturedResult $captured,
        string|int|null $fallbackStandardId = null,
    ): array {
        $sample = $captured->relationLoaded('sample')
            ? $captured->sample
            : $captured->sample()->first();

        $candidates = [
            [
                'standard_id' => $captured->main_standard_id
                    ?: $fallbackStandardId
                    ?: $sample?->main_standard,
                'captured_fk' => $captured->main_standard_id,
                'stored_value' => $captured->main_value,
            ],
            [
                'standard_id' => $captured->secondary_standard_id ?: $sample?->secondary_standard,
                'captured_fk' => $captured->secondary_standard_id,
                'stored_value' => $captured->secondary_value,
            ],
            [
                'standard_id' => $captured->third_standard_id ?: $sample?->third_standard_id,
                'captured_fk' => $captured->third_standard_id,
                'stored_value' => null,
            ],
        ];

        $seen = [];
        $firstAssignedId = null;

        foreach ($candidates as $candidate) {
            $standardId = $this->nonEmptyId($candidate['standard_id'] ?? null);
            if ($standardId === null) {
                continue;
            }

            if ($firstAssignedId === null) {
                $firstAssignedId = $standardId;
            }

            if (isset($seen[$standardId])) {
                continue;
            }
            $seen[$standardId] = true;

            $limit = $this->limitForSpecificationCandidate(
                $standardId,
                $captured->analyte_id,
                $candidate['stored_value'] ?? null,
                $candidate['captured_fk'] ?? null,
            );

            if ($limit === null) {
                continue;
            }

            return [
                'standard_id' => $standardId,
                'name' => $this->resolveStandardDisplayName($standardId),
                'limit' => $limit,
            ];
        }

        return [
            'standard_id' => $firstAssignedId,
            'name' => $firstAssignedId !== null ? $this->resolveStandardDisplayName($firstAssignedId) : null,
            'limit' => null,
        ];
    }

    private function limitForSpecificationCandidate(
        string|int|null $standardId,
        string|int|null $analyteId,
        mixed $storedValue,
        string|int|null $capturedStandardForeignKey = null,
    ): ?string {
        $fromStandard = $this->format($standardId, $analyteId, null, $capturedStandardForeignKey);
        $stored = $this->usableStoredLimitValue($storedValue);

        if ($stored !== null) {
            if ($fromStandard !== null && $this->isLimitTypeOnly($stored)) {
                return $fromStandard;
            }

            return $stored;
        }

        return $fromStandard;
    }

    private function usableStoredLimitValue(mixed $value): ?string
    {
        if ($value === null || $value === false) {
            return null;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '' || in_array($trimmed, ['NS', '-', '—', '–', 'N/A', 'n/a', 'NA'], true)) {
            return null;
        }

        return $trimmed;
    }

    protected function resolveStandardDisplayName(string|int $standardId): ?string
    {
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
    public function resolve(
        string|int|null $standardId,
        string|int|null $analyteId,
        mixed $capturedValue = null,
        string|int|null $capturedStandardForeignKey = null,
    ): array {
        if (! $standardId && ! $capturedStandardForeignKey) {
            return ['display' => $capturedValue ?: 'NS', 'value' => $capturedValue];
        }

        try {
            $stdAnalyte = $this->findStandardAnalyte($standardId, $analyteId, $capturedStandardForeignKey);

            if (! $stdAnalyte) {
                return ['display' => $capturedValue ?: 'NS', 'value' => $capturedValue];
            }

            $displayValue = '';
            $rawValue = '';
            $limitType = '';

            $svtCompact = strtolower(preg_replace('/[\s_]+/', '', (string) $stdAnalyte->standard_value_type) ?? '');

            if ($svtCompact === 'isrange') {
                $displayValue = $stdAnalyte->low.' - '.$stdAnalyte->high;
                $rawValue = $displayValue;
            } elseif ($svtCompact === 'isstandardvalue') {
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

    public function format(
        string|int|null $standardId,
        string|int|null $analyteId,
        mixed $capturedValue = null,
        string|int|null $capturedStandardForeignKey = null,
    ): ?string {
        $info = $this->resolve($standardId, $analyteId, $capturedValue, $capturedStandardForeignKey);
        $display = $info['display'] ?? null;

        if ($display === null || $display === '' || $display === 'NS' || $display === '-') {
            return null;
        }

        return (string) $display;
    }

    /**
     * Find the specification row for an analyte.
     *
     * Captured results sometimes store standards_analytes.id in main_standard_id
     * (legacy), and specs may be linked to a duplicate analyte with the same name/code.
     */
    public function findStandardAnalyte(
        string|int|null $standardId,
        string|int|null $analyteId,
        string|int|null $capturedStandardForeignKey = null,
    ): ?StandardAnalytes {
        $analyteKey = $this->nonEmptyId($analyteId);
        $candidateIds = [];
        foreach ([$standardId, $capturedStandardForeignKey] as $id) {
            $normalized = $this->nonEmptyId($id);
            if ($normalized !== null && ! in_array($normalized, $candidateIds, true)) {
                $candidateIds[] = $normalized;
            }
        }

        if ($candidateIds === []) {
            return null;
        }

        $cacheKey = implode('|', $candidateIds).'|'.($analyteKey ?? '');
        if (array_key_exists($cacheKey, $this->standardAnalyteCache)) {
            $cached = $this->standardAnalyteCache[$cacheKey];

            return $cached === false ? null : $cached;
        }

        $resolved = $this->matchStandardAnalyte($candidateIds, $analyteKey);
        $this->standardAnalyteCache[$cacheKey] = $resolved ?? false;

        return $resolved;
    }

    /**
     * @param  list<string>  $candidateIds
     */
    private function matchStandardAnalyte(array $candidateIds, ?string $analyteKey): ?StandardAnalytes
    {
        if ($analyteKey !== null) {
            foreach ($candidateIds as $standardId) {
                $direct = StandardAnalytes::query()
                    ->where('standard_id', $standardId)
                    ->where('analyte_id', $analyteKey)
                    ->first();
                if ($direct) {
                    return $direct;
                }
            }
        }

        foreach ($candidateIds as $foreignKey) {
            $byPrimaryKey = StandardAnalytes::query()->find($foreignKey);
            if (! $byPrimaryKey) {
                continue;
            }

            if ($analyteKey === null || (string) $byPrimaryKey->analyte_id === $analyteKey) {
                return $byPrimaryKey;
            }

            $viaRealStandard = StandardAnalytes::query()
                ->where('standard_id', $byPrimaryKey->standard_id)
                ->where('analyte_id', $analyteKey)
                ->first();
            if ($viaRealStandard) {
                return $viaRealStandard;
            }
        }

        if ($analyteKey === null) {
            return null;
        }

        $analyte = Analyte::query()->find($analyteKey);
        if (! $analyte) {
            return null;
        }

        $name = strtolower(trim((string) ($analyte->name ?? '')));
        $code = strtolower(trim((string) ($analyte->code ?? '')));
        if ($name === '' && $code === '') {
            return null;
        }

        $siblingIds = Analyte::query()
            ->where(function ($query) use ($name, $code): void {
                if ($name !== '') {
                    $query->orWhereRaw('LOWER(TRIM(name)) = ?', [$name]);
                }
                if ($code !== '') {
                    $query->orWhereRaw('LOWER(TRIM(code)) = ?', [$code]);
                }
            })
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        if ($siblingIds === []) {
            return null;
        }

        $standardIds = $candidateIds;
        foreach ($candidateIds as $foreignKey) {
            $byPrimaryKey = StandardAnalytes::query()->find($foreignKey);
            if ($byPrimaryKey?->standard_id) {
                $realId = (string) $byPrimaryKey->standard_id;
                if (! in_array($realId, $standardIds, true)) {
                    $standardIds[] = $realId;
                }
            }
        }

        foreach ($standardIds as $standardId) {
            $match = StandardAnalytes::query()
                ->where('standard_id', $standardId)
                ->whereIn('analyte_id', $siblingIds)
                ->first();
            if ($match) {
                return $match;
            }
        }

        return null;
    }

    private function nonEmptyId(string|int|null $value): ?string
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        return (string) $value;
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
        $stdAnalyte = $this->findStandardAnalyte($standardId, $analyteId, $standardId);

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
