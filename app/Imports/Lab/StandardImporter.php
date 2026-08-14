<?php

namespace App\Imports\Lab;

use App\Analyte;
use App\Imports\BaseImporter;
use App\StandardAnalytes;
use App\StandardValue;
use App\Standards;
use Illuminate\Support\Facades\DB;

class StandardImporter extends BaseImporter
{
    protected ?string $lastStandardCode = null;

    protected ?string $lastStandardName = null;

    protected ?bool $lastMainStandard = null;

    protected ?bool $lastIsQcStandard = null;

    protected ?string $lastQcTypeName = null;

    protected function validateRow(array $row): array
    {
        $errors = [];

        $code = $this->resolveStandardCode($row);
        $name = $this->resolveStandardName($row);
        $analyteCode = $this->resolveAnalyteCode($row);

        if ($code === '' && $name === '' && $this->lastStandardCode === null) {
            $errors[] = 'Standard code or standard name is required on the first row of a specification group';
        }

        if ($analyteCode === '') {
            $errors[] = 'Analyte code is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $code = $this->resolveStandardCode($row);
        $name = $this->resolveStandardName($row);
        $mainStandard = $this->fuzzyGet($row, ['main_standard', 'main_specification'], $this->lastMainStandard ?? 1);
        $isQc = $this->fuzzyGet($row, ['is_qc_standard', 'is_qc', 'qc'], $this->lastIsQcStandard ?? 0);
        $qcTypeName = $this->fuzzyGet($row, ['qc_type', 'type'], $this->lastQcTypeName);
        $analyteCode = $this->resolveAnalyteCode($row);

        if ($code !== '' || $name !== '') {
            $this->lastStandardCode = $code !== '' ? $code : $this->lastStandardCode;
            $this->lastStandardName = $name !== '' ? $name : $this->lastStandardName;
            $this->lastMainStandard = $this->toBool($mainStandard);
            $this->lastIsQcStandard = $this->toBool($isQc);
            $this->lastQcTypeName = $qcTypeName !== null && $qcTypeName !== '' ? (string) $qcTypeName : $this->lastQcTypeName;
        }

        $code = $this->lastStandardCode ?? '';
        $name = $this->lastStandardName ?? '';
        if ($code === '' && $name === '') {
            return false;
        }

        $standardValueType = $this->fuzzyGet($row, ['standard_value_type', 'value_type', 'standard_value_type_name']);
        $low = $this->fuzzyGet($row, ['standard_low', 'low', 'min', 'minimum', 'lower_limit', 'min_limit']);
        $high = $this->fuzzyGet($row, ['standard_high', 'high', 'max', 'maximum', 'upper_limit', 'max_limit']);
        $standardValue = $this->fuzzyGet($row, ['standard_value', 'expected_value', 'limit', 'specification', 'value']);
        $matrixOperator = $this->fuzzyGet($row, ['standard_matrix_operator', 'matrix_operator', 'operator']);

        $qcTypeId = null;
        if (! empty($this->lastQcTypeName)) {
            $qcTypeId = DB::table('qc_types')
                ->where('name', 'ILIKE', $this->lastQcTypeName)
                ->orWhere('code', 'ILIKE', $this->lastQcTypeName)
                ->value('id');
        }

        $isQcBool = (bool) ($this->lastIsQcStandard ?? false);

        return [
            'code' => (string) ($code !== '' ? $code : ('STD-'.date('Ymd-His').'-'.uniqid())),
            'name' => (string) ($name !== '' ? $name : $code),
            'main_standard' => ! $isQcBool && (bool) ($this->lastMainStandard ?? true),
            'is_qc_standard' => $isQcBool,
            'qc_type_id' => $qcTypeId,
            'analyte_code' => (string) $analyteCode,
            'standard_value_type' => $standardValueType,
            'low' => $low,
            'high' => $high,
            'standard_value' => $standardValue,
            'matrix_operator' => $matrixOperator,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $standardData = collect($transformedData)
                ->except([
                    'analyte_code',
                    'standard_value_type',
                    'low',
                    'high',
                    'standard_value',
                    'matrix_operator',
                    'company_id',
                ])
                ->toArray();

            if (empty($standardData['name'])) {
                $standardData['name'] = $standardData['code'];
            }

            $standardData['main_standard'] = (bool) ($standardData['main_standard'] ?? false);
            $standardData['is_qc_standard'] = (bool) ($standardData['is_qc_standard'] ?? false);

            $standard = Standards::updateOrCreate(
                ['code' => (string) $standardData['code']],
                $standardData
            );

            $analyte = Analyte::query()
                ->where('company_id', $this->batch->company_id)
                ->where(function ($query) use ($transformedData) {
                    $query->where('code', $transformedData['analyte_code'])
                        ->orWhere('name', 'ILIKE', $transformedData['analyte_code']);
                })
                ->first();

            if ($analyte === null) {
                throw new \Exception("Analyte not found: {$transformedData['analyte_code']}");
            }

            $limitPayload = $this->buildLimitPayload($transformedData);

            StandardAnalytes::updateOrCreate([
                'standard_id' => $standard->id,
                'analyte_id' => $analyte->id,
            ], $limitPayload);

            $this->recordUpsert($transformedData['code'], 'processed');

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import specification: {$e->getMessage()}");
        }
    }

    /**
     * @param  array<string, mixed>  $transformedData
     * @return array<string, mixed>
     */
    protected function buildLimitPayload(array $transformedData): array
    {
        $standardValueType = strtolower(trim((string) ($transformedData['standard_value_type'] ?? '')));
        $low = $transformedData['low'] ?? null;
        $high = $transformedData['high'] ?? null;
        $standardValue = $transformedData['standard_value'] ?? null;
        $matrixOperator = $transformedData['matrix_operator'] ?? null;

        $standardValueId = null;
        if ($standardValueType === 'is_range' || ($low !== null && $low !== '') || ($high !== null && $high !== '')) {
            $standardValueId = StandardValue::query()->where('code', 'IsRange')->value('id');
        } elseif ($standardValue !== null && $standardValue !== '') {
            $standardValueId = StandardValue::query()->where('code', 'IsValue')->value('id');
        }

        $valueType = null;
        if ($matrixOperator !== null && $matrixOperator !== '') {
            $valueType = strtolower((string) $matrixOperator);
        }

        return array_filter([
            'standard_value_id' => $standardValueId,
            'standard_value_type' => $standardValueType !== '' ? $standardValueType : (($low !== null || $high !== null) ? 'is_range' : 'is_standard_value'),
            'low' => $low,
            'high' => $high,
            'standard_is_value' => $standardValue,
            'matrix_operator' => $matrixOperator,
            'value_type' => $valueType,
            'expected_value' => $standardValue,
            'is_active' => 1,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    protected function resolveStandardCode(array $row): string
    {
        return (string) ($this->fuzzyGet($row, ['standard_code', 'code', 'standard_number', 'tzs_number', 'number', 'id', 'ref_std']) ?? '');
    }

    protected function resolveStandardName(array $row): string
    {
        return (string) ($this->fuzzyGet($row, ['standard_name', 'title', 'name', 'description', 'standard', 'standard_title']) ?? '');
    }

    protected function resolveAnalyteCode(array $row): string
    {
        $single = $this->fuzzyGet($row, ['analyte_code', 'analyte', 'parameter', 'parameter_name', 'chemical_name']);
        if ($single !== null && $single !== '') {
            return trim((string) $single);
        }

        $many = $this->fuzzyGet($row, ['analyte_codes', 'analytes', 'parameters']);
        if ($many !== null && $many !== '') {
            $parts = array_map('trim', explode(',', (string) $many));

            return (string) ($parts[0] ?? '');
        }

        return '';
    }

    protected function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return ! in_array(strtolower((string) $value), ['0', 'no', 'false', 'off', ''], true);
    }
}
