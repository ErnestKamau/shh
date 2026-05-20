<?php

namespace App\Imports\Equipment;

use App\Imports\BaseImporter;
use App\Models\Assets\AssetType;

class AssetTypeImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['code'] ?? null)) {
            $errors[] = 'Code is required';
        }

        if (empty($row['description'] ?? null)) {
            $errors[] = 'Description is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        return [
            'asset_code' => $row['code'],
            'description' => $row['description'] ?? ('Asset Type: ' . $row['code']),
            'is_active' => $row['is_active'] ?? 1,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            AssetType::updateOrCreate(
                ['asset_code' => $transformedData['asset_code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($transformedData['asset_code'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import asset type: {$e->getMessage()}");
        }
    }
}
