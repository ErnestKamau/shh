<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Standards;
use App\StandardAnalytes;
use App\Analyte;

class StandardImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['code'] ?? null)) {
            $errors[] = 'Code is required';
        }

        if (empty($row['main_standard'] ?? null)) {
            $errors[] = 'Main standard is required';
        }

        if (empty($row['analyte_codes'] ?? null)) {
            $errors[] = 'Analyte codes are required (comma-separated)';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $analyteCodes = array_map('trim', explode(',', $row['analyte_codes'] ?? ''));

        return [
            'code' => $row['code'],
            'main_standard' => $row['main_standard'],
            'is_qc_standard' => $row['is_qc_standard'] ?? 0,
            'qc_type' => $row['qc_type'] ?? null,
            'analyte_codes' => $analyteCodes,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $analyteCodes = $transformedData['analyte_codes'];
            unset($transformedData['analyte_codes']);

            $standard = Standards::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            // Associate analytes
            foreach ($analyteCodes as $code) {
                $analyte = Analyte::where('code', $code)->where('company_id', $this->batch->company_id)->first();
                if ($analyte) {
                    StandardAnalytes::firstOrCreate([
                        'standard_id' => $standard->id,
                        'analyte_id' => $analyte->id,
                    ]);
                }
            }

            $this->recordUpsert($transformedData['code'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import standard: {$e->getMessage()}");
        }
    }
}
