<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\SampleCondition;
use App\SampleType;

class SampleConditionImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['sample_type_code'] ?? null)) {
            $errors[] = 'Sample type code is required';
        } else {
            if (!SampleType::where('code', $row['sample_type_code'])->where('company_id', $this->batch->company_id)->exists()) {
                $errors[] = "Sample type '{$row['sample_type_code']}' does not exist";
            }
        }

        if (empty($row['condition_name'] ?? null)) {
            $errors[] = 'Condition name is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $sampleType = SampleType::where('code', $row['sample_type_code'])->where('company_id', $this->batch->company_id)->first();
        if (!$sampleType) {
            $sampleType = SampleType::where('company_id', $this->batch->company_id)->first();
        }

        return [
            'sample_type_id' => $sampleType?->id,
            'name' => $row['condition_name'],
            'short_name' => $row['short_name'] ?? null,
            'reporting_time' => $row['reporting_time'] ?? null,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            SampleCondition::create($transformedData);
            
            $identifier = "{$originalRow['sample_type_code']}/{$originalRow['condition_name']}";
            $this->recordUpsert($identifier, 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import sample condition: {$e->getMessage()}");
        }
    }

    protected function deleteRow(array $originalRow): bool
    {
        $sampleTypeCode = trim((string) ($originalRow['sample_type_code'] ?? $this->fuzzyGet($originalRow, ['sample_type_code'], '')));
        $conditionName = trim((string) ($originalRow['condition_name'] ?? $this->fuzzyGet($originalRow, ['condition_name', 'name'], '')));

        if ($conditionName === '') {
            $this->batch->addError($this->rowNumber, 'condition_name is required to delete.', $originalRow);

            return false;
        }

        $query = SampleCondition::query()->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($conditionName)]);
        if ($sampleTypeCode !== '') {
            $sampleType = \App\SampleType::query()
                ->where('company_id', $this->batch->company_id)
                ->where('code', $sampleTypeCode)
                ->first();
            if ($sampleType) {
                $query->where('sample_type_id', $sampleType->id);
            }
        }

        $condition = $query->first();
        if ($condition === null) {
            $this->batch->addWarning($this->rowNumber, 'Sample condition not found for delete.', $originalRow);

            return false;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn($condition->getTable(), 'active')) {
            $condition->active = 0;
            $condition->save();
        } else {
            $condition->delete();
        }

        return true;
    }
}
