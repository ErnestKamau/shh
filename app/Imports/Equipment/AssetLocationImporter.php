<?php

namespace App\Imports\Equipment;

use App\Imports\BaseImporter;
use App\Models\Assets\AssetLocation;

class AssetLocationImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['code'] ?? null)) {
            $errors[] = 'Code is required';
        }

        if (empty($row['name'] ?? null)) {
            $errors[] = 'Name is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        return [
            'location_code' => $row['code'],
            'name' => $row['name'],
            'is_active' => $row['is_active'] ?? 1,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            AssetLocation::updateOrCreate(
                ['location_code' => $transformedData['location_code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($transformedData['location_code'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import asset location: {$e->getMessage()}");
        }
    }
}
