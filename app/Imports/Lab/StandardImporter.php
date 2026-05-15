<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Standards;
use App\StandardAnalytes;
use App\Analyte;
use Illuminate\Support\Facades\DB;

class StandardImporter extends BaseImporter
{
    protected ?string $lastStandardCode = null;
    protected ?string $lastMainStandard = null;

    protected function validateRow(array $row): array
    {
        $errors = [];

        $code = $this->fuzzyGet($row, ['code', 'standard_number', 'tzs_number', 'standard_code', 'number', 'id', 'ref_std', 'ref_std_tzs_iso', 'tzs', 'tzs_standards']);
        $mainStandard = $this->fuzzyGet($row, ['main_standard', 'title', 'standard_name', 'name', 'description', 'standard', 'standard_title', 'matrix', 'category', 'environment', 'source']);

        if (empty($code) && empty($mainStandard) && empty($this->lastStandardCode) && empty($this->lastMainStandard)) {
            $errors[] = 'Either Standard Code or Title is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $code = $this->fuzzyGet($row, ['code', 'standard_number', 'tzs_number', 'standard_code', 'number', 'id', 'ref_std', 'ref_std_tzs_iso', 'tzs', 'tzs_standards']);
        $mainStandard = $this->fuzzyGet($row, ['main_standard', 'title', 'standard_name', 'name', 'description', 'standard', 'standard_title', 'matrix', 'category', 'environment', 'source']);
        $isQc = $this->fuzzyGet($row, ['is_qc_standard', 'is_qc', 'qc'], 0);
        $qcTypeName = $this->fuzzyGet($row, ['qc_type', 'type']);
        $analyteCodesRaw = $this->fuzzyGet($row, ['analyte_codes', 'analytes', 'parameters', 'analyte', 'parameter', 'chemical_name', 'parameter_name']);
        
        $standardValue = $this->fuzzyGet($row, ['standard_value', 'expected_value', 'limit', 'specification', 'value']);
        $low = $this->fuzzyGet($row, ['low', 'min', 'minimum', 'lower_limit', 'min_limit']);
        $high = $this->fuzzyGet($row, ['high', 'max', 'maximum', 'upper_limit', 'max_limit']);
        
        // Use last known standard if current row is empty (grouped rows)
        if (empty($code) && empty($mainStandard)) {
            $code = $this->lastStandardCode;
            $mainStandard = $this->lastMainStandard;
        } else {
            $this->lastStandardCode = $code;
            $this->lastMainStandard = $mainStandard;
        }

        $analyteCodes = [];
        if (!empty($analyteCodesRaw)) {
            $analyteCodes = array_map('trim', explode(',', (string)$analyteCodesRaw));
        }

        if (empty($code) && empty($mainStandard)) {
            return false; // Skip if still no standard identity
        }

        // Lookup QC Type ID if provided
        $qcTypeId = null;
        if (!empty($qcTypeName)) {
            $qcTypeId = DB::table('qc_types')
                ->where('name', 'ILIKE', $qcTypeName)
                ->orWhere('code', 'ILIKE', $qcTypeName)
                ->value('id');
        }

        $isQcBool = (bool)($isQc && !in_array(strtolower((string)$isQc), ['0', 'no', 'false', 'off']));

        return [
            'code' => (string)($code ?: ($mainStandard ?: 'STD-' . date('Ymd-His') . '-' . uniqid())),
            'name' => (string)($mainStandard ?: $code),
            'main_standard' => !$isQcBool,
            'is_qc_standard' => $isQcBool,
            'qc_type_id' => $qcTypeId,
            'analyte_codes' => $analyteCodes,
            'standard_value' => $standardValue,
            'low' => $low,
            'high' => $high,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $analyteCodes = $transformedData['analyte_codes'] ?? [];
            $standardValue = $transformedData['standard_value'] ?? null;
            $low = $transformedData['low'] ?? null;
            $high = $transformedData['high'] ?? null;
            
            // Cleanup data for Standards model
            $standardData = collect($transformedData)
                ->except(['analyte_codes', 'standard_value', 'low', 'high', 'company_id'])
                ->toArray();
            
            // Standard name is handled in transformRow, but as a safety:
            if (empty($standardData['name'])) {
                $standardData['name'] = $standardData['code'];
            }

            // Ensure booleans are true booleans for Postgres
            $standardData['main_standard'] = (bool)($standardData['main_standard'] ?? false);
            $standardData['is_qc_standard'] = (bool)($standardData['is_qc_standard'] ?? false);

            $standard = Standards::updateOrCreate(
                ['code' => (string)$standardData['code']],
                $standardData
            );

            // Associate analytes
            foreach ($analyteCodes as $code) {
                // Try finding analyte by code first, then by name
                $analyte = Analyte::where('code', $code)
                    ->where('company_id', $this->batch->company_id)
                    ->first();
                
                if (!$analyte) {
                    $analyte = Analyte::where('name', 'ILIKE', '%' . $code . '%')
                        ->where('company_id', $this->batch->company_id)
                        ->first();
                }

                if ($analyte) {
                    StandardAnalytes::updateOrCreate([
                        'standard_id' => $standard->id,
                        'analyte_id' => $analyte->id,
                    ], [
                        'expected_value' => $standardValue,
                        'low' => $low,
                        'high' => $high,
                        'is_active' => 1
                    ]);
                }
            }

            $this->recordUpsert($transformedData['code'], 'processed');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import standard: {$e->getMessage()}");
        }
    }
}
