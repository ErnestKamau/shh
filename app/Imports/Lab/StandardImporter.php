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

        if (!$this->hasFuzzy($row, ['code', 'standard_number', 'tzs_number', 'standard_code'])) {
            $errors[] = 'Standard code/number is required';
        }

        if (!$this->hasFuzzy($row, ['main_standard', 'title', 'standard_name', 'name'])) {
            $errors[] = 'Main standard/title is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $code = $this->fuzzyGet($row, ['code', 'standard_number', 'tzs_number', 'standard_code']);
        $mainStandard = $this->fuzzyGet($row, ['main_standard', 'title', 'standard_name', 'name']);
        $isQc = $this->fuzzyGet($row, ['is_qc_standard', 'is_qc', 'qc'], 0);
        $qcType = $this->fuzzyGet($row, ['qc_type', 'type']);
        $analyteCodesRaw = $this->fuzzyGet($row, ['analyte_codes', 'analytes', 'parameters']);
        
        $analyteCodes = [];
        if (!empty($analyteCodesRaw)) {
            $analyteCodes = array_map('trim', explode(',', (string)$analyteCodesRaw));
        }

        return [
            'code' => $code,
            'main_standard' => $mainStandard,
            'is_qc_standard' => (int)$isQc,
            'qc_type' => $qcType,
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
