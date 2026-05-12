<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\MethodSequences\MethodSequence;
use App\Models\Procedures\ProcedureWorksheet;

class AnalysisElementsImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['analysis_type_code'] ?? null)) {
            $errors[] = 'Analysis type code is required';
        } else {
            if (!AnalysisType::where('code', $row['analysis_type_code'])->where('company_id', $this->batch->company_id)->exists()) {
                $errors[] = "Analysis type '{$row['analysis_type_code']}' does not exist";
            }
        }

        if (empty($row['analyte_code'] ?? null)) {
            $errors[] = 'Analyte code is required';
        } else {
            if (!Analyte::where('code', $row['analyte_code'])->where('company_id', $this->batch->company_id)->exists()) {
                $errors[] = "Analyte '{$row['analyte_code']}' does not exist";
            }
        }

        if (empty($row['lab_section_code'] ?? null)) {
            $errors[] = 'Lab section code is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $analysisType = AnalysisType::where('code', $row['analysis_type_code'])->where('company_id', $this->batch->company_id)->first();
        $analyte = Analyte::where('code', $row['analyte_code'])->where('company_id', $this->batch->company_id)->first();
        $methodSequence = null;
        $procedureWorksheet = null;

        if (!empty($row['method_sequence_name'])) {
            $methodSequence = MethodSequence::query()->where('name', $row['method_sequence_name'])->first();
        }

        if (!empty($row['procedure_worksheet_name'])) {
            $procedureWorksheet = ProcedureWorksheet::query()->where('name', $row['procedure_worksheet_name'])->first();
        }

        return [
            'analysis_type_id' => $analysisType?->id,
            'analyte_id' => $analyte?->id,
            'lab_section_code' => $row['lab_section_code'],
            'equipment_code' => $row['equipment_code'] ?? null,
            'lod' => $row['lod'] ?? null,
            'hod' => $row['hod'] ?? null,
            'level' => $row['level'] ?? null,
            'method_sequence_id' => $methodSequence?->id,
            'procedure_worksheet_id' => $procedureWorksheet?->id,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            AnalysisElements::create($transformedData);
            
            $identifier = "{$originalRow['analysis_type_code']}/{$originalRow['analyte_code']}";
            $this->recordUpsert($identifier, 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analysis element: {$e->getMessage()}");
        }
    }
}
