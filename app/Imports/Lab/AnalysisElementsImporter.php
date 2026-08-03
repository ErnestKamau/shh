<?php

namespace App\Imports\Lab;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Imports\BaseImporter;
use App\Models\MethodSequences\MethodSequence;
use App\Models\Procedures\ProcedureWorksheet;
use App\SampleAnalysisStage;
use Illuminate\Support\Facades\DB;

class AnalysisElementsImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        $analysisTypeCode = trim((string) ($row['analysis_type_code'] ?? ''));
        $analyteCode = trim((string) ($row['analyte_code'] ?? ''));
        $labSectionCode = trim((string) ($row['lab_section_code'] ?? ''));

        if ($analysisTypeCode === '') {
            $errors[] = 'Analysis type code is required';
        } elseif (! AnalysisType::where('code', $analysisTypeCode)->where('company_id', $this->batch->company_id)->exists()) {
            $errors[] = "Analysis type '{$analysisTypeCode}' does not exist — import Analysis Types first";
        }

        if ($analyteCode === '') {
            $errors[] = 'Analyte code is required';
        } elseif (! Analyte::where('code', $analyteCode)->where('company_id', $this->batch->company_id)->exists()) {
            $errors[] = "Analyte '{$analyteCode}' does not exist — import Analytes first";
        }

        if ($labSectionCode === '') {
            $errors[] = 'Lab section code is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $analysisTypeCode = trim((string) ($row['analysis_type_code'] ?? ''));
        $analyteCode = trim((string) ($row['analyte_code'] ?? ''));
        $sectionCode = trim((string) ($row['lab_section_code'] ?? ''));
        $equipmentCode = trim((string) ($row['equipment_code'] ?? ''));
        $equipmentName = trim((string) ($row['equipment'] ?? $row['equipment_name'] ?? ''));

        $analysisType = AnalysisType::where('code', $analysisTypeCode)
            ->where('company_id', $this->batch->company_id)
            ->first();
        $analyte = Analyte::where('code', $analyteCode)
            ->where('company_id', $this->batch->company_id)
            ->first();

        $methodSequence = null;
        $procedureWorksheet = null;

        if (! empty($row['method_sequence_name'])) {
            $methodSequence = MethodSequence::query()->where('name', $row['method_sequence_name'])->first();
        }

        if (! empty($row['procedure_worksheet_name'])) {
            $procedureWorksheet = ProcedureWorksheet::query()->where('name', $row['procedure_worksheet_name'])->first();
        }

        return [
            'analysis_type_id' => $analysisType?->id,
            'analyte_id' => $analyte?->id,
            'lab_section_id' => $this->resolveLabSectionId($sectionCode, $analysisType?->lab_id),
            'equipment_id' => $this->resolveEquipmentId(
                $equipmentCode !== '' ? $equipmentCode : null,
                $equipmentName !== '' ? $equipmentName : null,
            ),
            'lod' => $row['lod'] ?? null,
            'hod' => $row['hod'] ?? null,
            'level' => $row['level'] ?? null,
            'method_sequence_id' => $methodSequence?->id,
            'has_method_sequence' => $methodSequence !== null,
            'procedure_worksheet_id' => $procedureWorksheet?->id,
            'active' => 1,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            if (empty($transformedData['analysis_type_id']) || empty($transformedData['analyte_id'])) {
                throw new \Exception('Analysis type and analyte must both resolve before creating parameters');
            }

            $existing = AnalysisElements::where('analysis_type_id', $transformedData['analysis_type_id'])
                ->where('analyte_id', $transformedData['analyte_id'])
                ->first();

            if ($existing) {
                $existing->update($transformedData);
                $action = 'updated';
            } else {
                AnalysisElements::create($transformedData);
                $action = 'inserted';
            }

            $identifier = ($originalRow['analysis_type_code'] ?? '').'/'.($originalRow['analyte_code'] ?? '');
            $this->recordUpsert($identifier, $action);

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analysis element: {$e->getMessage()}");
        }
    }

    protected function resolveLabSectionId(string $sectionCode, ?string $labId): ?string
    {
        if ($sectionCode === '') {
            return null;
        }

        // analysis_elements.lab_section_id references sample_analysis_stages (operational
        // departments, is_sample_stage = 0), not the Monitoring module's lab_sections table.
        $labSection = SampleAnalysisStage::query()
            ->where('company_id', $this->batch->company_id)
            ->where('is_sample_stage', 0)
            ->where(function ($q) use ($sectionCode) {
                $q->where('code', $sectionCode)
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower($sectionCode)]);
            })
            ->first();

        if ($labSection) {
            if ($labId && empty($labSection->lab_id)) {
                $labSection->update(['lab_id' => $labId]);
            }

            return (string) $labSection->id;
        }

        try {
            $labSection = DB::transaction(fn () => SampleAnalysisStage::create([
                'code' => $sectionCode,
                'company_id' => $this->batch->company_id,
                'name' => $sectionCode,
                'lab_id' => $labId,
                'is_sample_stage' => 0,
                'active' => 1,
            ]));

            return (string) $labSection->id;
        } catch (\Exception $e) {
            \Log::warning('Could not create lab section during analysis elements import: '.$e->getMessage());

            return null;
        }
    }
}
