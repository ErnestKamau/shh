<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

trait ReadsAmSpecParametersSpreadsheet
{
    public const AMSPEC_PARAMETERS_PATH = 'database/seeders/data/Amspec Parameters List.xlsx';

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function readAmSpecParameterRows(?string $path = null): Collection
    {
        $absolutePath = base_path($path ?? self::AMSPEC_PARAMETERS_PATH);

        if (! is_file($absolutePath)) {
            throw new \RuntimeException("AmSpec parameters spreadsheet not found: {$absolutePath}");
        }

        $spreadsheet = IOFactory::load($absolutePath);
        $rows = collect();

        $lastLabSection = null;
        $lastSampleType = null;
        $lastAnalysisType = null;

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetRows = $sheet->toArray(null, true, true, false);
            $headerMap = $this->detectAmSpecHeaderMap($sheetRows);

            if ($headerMap === null) {
                continue;
            }

            foreach ($sheetRows as $index => $row) {
                if ($index <= $headerMap['index']) {
                    continue;
                }

                $normalized = $this->normalizeAmSpecRow($this->mapAmSpecRow($row, $headerMap['columns']));

                if ($this->shouldSkipAmSpecRow($normalized)) {
                    continue;
                }

                $sectionDepartment = $this->resolveAmSpecField($normalized, [
                    'section_department',
                    'sectiondepartment',
                    'lab_section',
                ]);
                $matrixCategory = $this->resolveAmSpecField($normalized, [
                    'matrix_category',
                    'sample_type',
                    'matrixcategory',
                ], prefix: 'matrixcategory');
                $subMatrix = $this->resolveAmSpecField($normalized, [
                    'sub_matrix',
                    'analysis_type',
                    'matrixsubcategory',
                    'matrix_sub_category',
                ], prefix: 'matrixsubcategory');
                $parameterName = $this->resolveAmSpecField($normalized, [
                    'name_of_parameters_as_in_report_coa',
                    'nameofparametersasinreportcoa',
                    'parameters',
                    'parameter_name',
                    'internalparametersname',
                    'internal_parameters_name',
                ], prefix: 'nameofparameters');
                $method = $this->resolveAmSpecField($normalized, [
                    'test_method_sop',
                    'testmethodsop',
                    'method',
                ], prefix: 'testmethod');
                $unit = $this->resolveAmSpecField($normalized, [
                    'unit',
                    'reporting_unit',
                ]);
                $decimalPlaces = $this->resolveAmSpecField($normalized, [
                    'decimal_places',
                    'decimalplaces',
                ]);
                $accreditation = $this->resolveAmSpecField($normalized, [
                    'accreditation_scope',
                    'accreditationscopeaccreditednonaccredited',
                    'accreditation',
                    'accredited_nonaccredited',
                ], prefix: 'accreditationscope') ?? 'Accredited';
                $instrument = $this->resolveAmSpecField($normalized, [
                    'instrument',
                    'instrumentused',
                    'equipment',
                ], prefix: 'instrument');

                if (empty($sectionDepartment) && $lastLabSection) {
                    $sectionDepartment = $lastLabSection;
                } elseif (! empty($sectionDepartment)) {
                    $lastLabSection = $sectionDepartment;
                }

                if (empty($matrixCategory) && $lastSampleType) {
                    $matrixCategory = $lastSampleType;
                } elseif (! empty($matrixCategory)) {
                    $lastSampleType = $matrixCategory;
                }

                if (empty($subMatrix) || in_array($subMatrix, ['—', '-'], true)) {
                    $subMatrix = $lastAnalysisType ?? 'Default Analysis Type';
                } else {
                    $lastAnalysisType = $subMatrix;
                }

                if (empty($matrixCategory) || empty($parameterName) || empty($method)) {
                    continue;
                }

                $rows->push([
                    'section_department' => $sectionDepartment,
                    'matrix_category' => $matrixCategory,
                    'sub_matrix' => $subMatrix,
                    'parameter_name' => $parameterName,
                    'method' => $method,
                    'reporting_unit' => $unit,
                    'decimal_places' => is_numeric($decimalPlaces) ? (int) $decimalPlaces : 2,
                    'accreditation' => $accreditation,
                    'instrument' => $instrument,
                    'sample_type_code' => $this->generateAmSpecCode($matrixCategory),
                    'sample_type_name' => $matrixCategory,
                    'analysis_type_code' => $this->generateAmSpecCode($subMatrix),
                    'analysis_type_name' => $subMatrix,
                    'analyte_code' => $this->generateAmSpecCode($parameterName),
                    'analyte_name' => $parameterName,
                    'lab_section_code' => $sectionDepartment ? $this->generateAmSpecCode($sectionDepartment) : null,
                    'lab_section_name' => $sectionDepartment,
                ]);
            }
        }

        return $rows;
    }

    /**
     * @param  list<string>  $aliases
     */
    private function resolveAmSpecField(array $row, array $aliases, ?string $prefix = null): ?string
    {
        foreach ($aliases as $alias) {
            if (! empty($row[$alias])) {
                return is_string($row[$alias]) ? trim($row[$alias]) : (string) $row[$alias];
            }
        }

        if ($prefix !== null) {
            foreach ($row as $key => $value) {
                if (str_starts_with($key, $prefix) && $value !== null && $value !== '') {
                    return is_string($value) ? trim($value) : (string) $value;
                }
            }
        }

        return null;
    }

    /**
     * @param  list<list<mixed>>  $sheetRows
     * @return array{index: int, columns: array<int, string>}|null
     */
    private function detectAmSpecHeaderMap(array $sheetRows): ?array
    {
        foreach ($sheetRows as $index => $row) {
            $columns = [];
            foreach ($row as $colIndex => $cell) {
                $cleanKey = $this->normalizeAmSpecHeaderKey((string) $cell);
                if ($cleanKey !== '') {
                    $columns[$colIndex] = $cleanKey;
                }
            }

            $columnKeys = array_values($columns);
            $hasSection = $this->columnKeysMatch($columnKeys, ['sectiondepartment', 'section_department', 'lab_section']);
            $hasMatrix = $this->columnKeysMatch($columnKeys, ['matrixcategory', 'matrix_category', 'sample_type'], prefix: true);
            $hasParameter = $this->columnKeysMatch($columnKeys, [
                'nameofparametersasinreportcoa',
                'parameters',
                'parameter_name',
                'internalparametersname',
            ], prefix: true);

            if ($hasSection && ($hasMatrix || $hasParameter)) {
                return ['index' => $index, 'columns' => $columns];
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $needles
     */
    private function columnKeysMatch(array $columnKeys, array $needles, bool $prefix = false): bool
    {
        foreach ($columnKeys as $key) {
            foreach ($needles as $needle) {
                if ($prefix && str_starts_with($key, $needle)) {
                    return true;
                }
                if ($key === $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  list<mixed>  $row
     * @param  array<int, string>  $columns
     * @return array<string, mixed>
     */
    private function mapAmSpecRow(array $row, array $columns): array
    {
        $mapped = [];
        foreach ($columns as $colIndex => $key) {
            $mapped[$key] = $row[$colIndex] ?? null;
        }

        return $mapped;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeAmSpecRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $cleanKey = $this->normalizeAmSpecHeaderKey((string) $key);
            if ($cleanKey === '') {
                continue;
            }
            $normalized[$cleanKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    private function normalizeAmSpecHeaderKey(string $key): string
    {
        return preg_replace('/[^a-z0-9_]/', '', strtolower(trim($key))) ?? '';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function shouldSkipAmSpecRow(array $row): bool
    {
        $values = array_filter(array_map(
            static fn ($value) => is_string($value) ? trim($value) : $value,
            $row
        ), static fn ($value) => $value !== null && $value !== '');

        return $values === [];
    }

    protected function generateAmSpecCode(?string $name): string
    {
        if (empty($name)) {
            return 'CODE-'.uniqid();
        }

        $slug = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $slug = preg_replace('/_+/', '_', (string) $slug);
        $slug = trim((string) $slug, '_');

        return strtoupper(substr($slug, 0, 100));
    }

    /**
     * @return Collection<int, array{code: string, name: string}>
     */
    protected function uniqueAmSpecSampleTypes(?string $path = null): Collection
    {
        return $this->readAmSpecParameterRows($path)
            ->unique('matrix_category')
            ->map(fn (array $row) => [
                'code' => $row['sample_type_code'],
                'name' => $row['sample_type_name'],
            ])
            ->values();
    }

}
