<?php

namespace App\Imports\Personnel;

use App\Imports\BaseImporter;
use App\InventoryDepartment;

class DepartmentImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['name'] ?? null)) {
            $errors[] = 'Department name is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        return [
            'name' => $row['name'],
            'active' => $row['is_active'] ?? 1,
            'module' => 'organizational',
            'inventory_location_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            InventoryDepartment::updateOrCreate(
                ['name' => $transformedData['name'], 'module' => 'organizational'],
                $transformedData
            );

            $this->recordUpsert($transformedData['name'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import department: {$e->getMessage()}");
        }
    }
}
