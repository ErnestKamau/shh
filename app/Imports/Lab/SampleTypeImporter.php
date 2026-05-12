<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\SampleType;

class SampleTypeImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['code'] ?? null)) {
            $errors[] = 'Code is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        return [
            'code' => $row['code'],
            'is_results_attachable' => $row['is_results_attachable'] ?? 0,
            'disposal_count' => $row['disposal_count'] ?? null,
            'report_template_id' => null,
            'default_product_id' => null,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            SampleType::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($transformedData['code'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import sample type: {$e->getMessage()}");
        }
    }
}
