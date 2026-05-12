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
}
