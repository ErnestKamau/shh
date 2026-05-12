<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Analyte;

class AnalyteImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['code'] ?? null)) {
            $errors[] = 'Code is required';
        } elseif (Analyte::where('code', $row['code'])->where('company_id', $this->batch->company_id)->exists()) {
            // Allow duplicates for upsert
        }

        if (empty($row['name'] ?? null)) {
            $errors[] = 'Name is required';
        }

        if (!empty($row['decimal_places'] ?? null) && !is_numeric($row['decimal_places'])) {
            $errors[] = 'Decimal places must be numeric';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        return [
            'code' => $row['code'],
            'name' => $row['name'],
            'decimal_places' => $row['decimal_places'] ?? 2,
            'reporting_symbol' => $row['reporting_symbol'] ?? null,
            'reporting_unit' => $row['reporting_unit'] ?? null,
            'equipment_id' => null, // Would resolve from equipment_code
            'non_detectable' => $row['non_detectable'] ?? 0,
            'non_accredited' => $row['non_accredited'] ?? 0,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $analyteName = $transformedData['code'] ?? 'unknown';
            
            Analyte::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($analyteName, 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analyte: {$e->getMessage()}");
        }
    }
}
