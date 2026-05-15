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

        if (!$this->hasFuzzy($row, ['equipment_number', 'equipment_no', 's/n', 'id'])) {
            $errors[] = 'Equipment number is required';
        }

        if (!$this->hasFuzzy($row, ['make', 'manufacturer', 'brand'])) {
            $errors[] = 'Make/Manufacturer is required';
        }

        if (!$this->hasFuzzy($row, ['model', 'type'])) {
            $errors[] = 'Model is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $equipmentNumber = $this->fuzzyGet($row, ['equipment_number', 'equipment_no', 's/n', 'id']);
        $make = $this->fuzzyGet($row, ['make', 'manufacturer', 'brand']);
        $model = $this->fuzzyGet($row, ['model', 'type']);
        $serialNumber = $this->fuzzyGet($row, ['serial_number', 'serial_no', 'sn']);
        
        $assetTypeCode = $this->fuzzyGet($row, ['asset_type_code', 'asset_type', 'category']);
        $assetLocationCode = $this->fuzzyGet($row, ['asset_location_code', 'asset_location', 'location', 'department']);

        $assetType = AssetType::where(function($q) use ($assetTypeCode) {
                $q->where('asset_code', $assetTypeCode)->orWhere('description', 'like', "%$assetTypeCode%");
            })
            ->where('company_id', $this->batch->company_id)
            ->first();

        $assetLocation = AssetLocation::where(function($q) use ($assetLocationCode) {
                $q->where('location_code', $assetLocationCode)->orWhere('name', 'like', "%$assetLocationCode%");
            })
            ->where('company_id', $this->batch->company_id)
            ->first();

        return [
            'equipment_number' => $equipmentNumber,
            'make' => $make,
            'model' => $model,
            'serial_number' => $serialNumber,
            'asset_type_id' => $assetType?->id,
            'asset_location_id' => $assetLocation?->id,
            'calibration_days' => $this->fuzzyGet($row, ['calibration_days', 'calibration_interval', 'cal_days']),
            'maintainance_days' => $this->fuzzyGet($row, ['maintenance_days', 'maintenance_interval', 'maint_days']),
            'requires_daily_log' => $this->fuzzyGet($row, ['requires_daily_log', 'daily_log'], 0),
            'daily_log_value_type' => $this->fuzzyGet($row, ['daily_log_value_type', 'log_type']),
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
