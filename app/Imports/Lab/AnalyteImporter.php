<?php

namespace App\Imports\Lab;

use App\Analyte;
use App\Imports\BaseImporter;

class AnalyteImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        $code = $this->fuzzyGet($row, ['code', 'analyte_code', 'parameter_code', 'id']);
        $name = $this->fuzzyGet($row, ['name', 'analyte_name', 'parameter', 'parameter_name', 'title']);

        if (empty($code) && empty($name)) {
            $errors[] = 'Either Code or Name is required';
        }

        $decimalPlaces = $this->fuzzyGet($row, ['decimal_places', 'decimals']);
        if ($decimalPlaces !== null && $decimalPlaces !== '' && ! is_numeric($decimalPlaces)) {
            $errors[] = 'Decimal places must be numeric';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $code = trim((string) $this->fuzzyGet($row, ['code', 'analyte_code', 'parameter_code', 'id'], ''));
        $name = trim((string) $this->fuzzyGet($row, ['name', 'analyte_name', 'parameter', 'parameter_name', 'title'], ''));

        if ($code === '' && $name !== '') {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]+/', '_', $name) ?? '', 0, 100));
            $code = trim($code, '_') ?: ('AN-'.uniqid());
        }

        if ($name === '' && $code !== '') {
            $name = $code;
        }

        $nonDetectable = $this->fuzzyGet($row, ['non_detectable'], 0);
        $nonAccredited = $this->fuzzyGet($row, ['non_accredited'], 0);
        $showOnReport = $this->fuzzyGet($row, ['show_on_report', 'show_on_reports'], 1);

        return [
            'code' => $code,
            'name' => $name,
            'decimal_places' => is_numeric($this->fuzzyGet($row, ['decimal_places', 'decimals']))
                ? (int) $this->fuzzyGet($row, ['decimal_places', 'decimals'])
                : 2,
            'reporting_symbol' => $this->fuzzyGet($row, ['reporting_symbol', 'symbol']),
            'reporting_unit' => $this->fuzzyGet($row, ['reporting_unit', 'unit', 'units']),
            'equipment_id' => null,
            'non_detectable' => ! in_array(strtolower((string) $nonDetectable), ['0', 'no', 'false', 'off', ''], true) ? 1 : 0,
            'non_accredited' => ! in_array(strtolower((string) $nonAccredited), ['0', 'no', 'false', 'off', ''], true) ? 1 : 0,
            'show_on_report' => in_array(strtolower((string) $showOnReport), ['0', 'no', 'false', 'off'], true) ? 0 : 1,
            'active' => 1,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            Analyte::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($transformedData['code'], 'upserted');

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analyte: {$e->getMessage()}");
        }
    }
}
