<?php

namespace App\Imports\Equipment;

use App\Imports\BaseImporter;
use App\Models\Equipments\Equipment;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;

class EquipmentImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['equipment_number'] ?? null)) {
            $errors[] = 'Equipment number is required';
        }

        if (empty($row['make'] ?? null)) {
            $errors[] = 'Make is required';
        }

        if (empty($row['model'] ?? null)) {
            $errors[] = 'Model is required';
        }

        if (empty($row['serial_number'] ?? null)) {
            $errors[] = 'Serial number is required';
        }

        if (empty($row['asset_type_code'] ?? null)) {
            $errors[] = 'Asset type code is required';
        } else {
            if (!AssetType::where('asset_code', $row['asset_type_code'])->where('company_id', $this->batch->company_id)->exists()) {
                $errors[] = "Asset type '{$row['asset_type_code']}' does not exist";
            }
        }

        if (empty($row['asset_location_code'] ?? null)) {
            $errors[] = 'Asset location code is required';
        } else {
            if (!AssetLocation::where('location_code', $row['asset_location_code'])->where('company_id', $this->batch->company_id)->exists()) {
                $errors[] = "Asset location '{$row['asset_location_code']}' does not exist";
            }
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $assetType = AssetType::where('asset_code', $row['asset_type_code'])->where('company_id', $this->batch->company_id)->first();
        $assetLocation = AssetLocation::where('location_code', $row['asset_location_code'])->where('company_id', $this->batch->company_id)->first();

        return [
            'equipment_number' => $row['equipment_number'],
            'make' => $row['make'],
            'model' => $row['model'],
            'serial_number' => $row['serial_number'],
            'asset_type_id' => $assetType?->id,
            'asset_location_id' => $assetLocation?->id,
            'calibration_days' => $row['calibration_days'] ?? null,
            'maintainance_days' => $row['maintenance_days'] ?? null,
            'requires_daily_log' => $row['requires_daily_log'] ?? 0,
            'daily_log_value_type' => $row['daily_log_value_type'] ?? null,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            Equipment::updateOrCreate(
                ['equipment_number' => $transformedData['equipment_number'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($transformedData['equipment_number'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import equipment: {$e->getMessage()}");
        }
    }
}
