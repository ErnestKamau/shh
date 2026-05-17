<?php

namespace App\Imports\Equipment;

use App\Imports\BaseImporter;
use App\Models\Equipments\Equipment;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;

class EquipmentImporter extends BaseImporter
{
    protected ?string $selectedZoneId = null;

    public function __construct(?\App\Models\BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch);
        $this->selectedZoneId = $selectedZoneId;
    }

    protected function validateRow(array $row): array
    {
        $errors = [];

        if (!$this->hasFuzzy($row, ['equipment_instrument', 'instrument', 'name', 'equipment_name'])) {
            $errors[] = 'Equipment/Instrument Name is required';
        }

        if (!$this->hasFuzzy($row, ['model'])) {
            $errors[] = 'Model is required';
        }

        if (!$this->hasFuzzy($row, ['gcla_code', 'equipment_number', 'equipment_no', 'code'])) {
            $errors[] = 'GCLA code (Equipment Number) is required';
        }

        if (!$this->hasFuzzy($row, ['lab_office_name', 'lab_name', 'office_name', 'lab', 'office'])) {
            $errors[] = 'Lab/Office Name is required';
        }

        if (!$this->hasFuzzy($row, ['status'])) {
            $errors[] = 'Status is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $equipmentNumber = $this->fuzzyGet($row, ['gcla_code', 'equipment_number', 'equipment_no', 'code']);
        $name = $this->fuzzyGet($row, ['equipment_instrument', 'instrument', 'name', 'equipment_name']);
        if (empty($name)) {
            $name = 'Unnamed Equipment';
        }
        $model = $this->fuzzyGet($row, ['model']);
        if (empty($model)) {
            $model = 'Unknown';
        }
        $serialNumber = $this->fuzzyGet($row, ['serial_no', 'serial_number', 'sn']);
        if (empty($serialNumber) || strtolower($serialNumber) === 'nil') {
            $serialNumber = 'NIL';
        }

        // Resiliently resolve "Make" (database-required)
        $make = $this->fuzzyGet($row, ['make', 'brand', 'manufacturer']);
        if (empty($make)) {
            if (!empty($model)) {
                $parts = preg_split('/[\s\(\-\#]/', $model);
                $make = trim($parts[0] ?? '');
            }
            if (empty($make)) {
                $make = 'Unknown';
            }
        }

        // Map metadata into comments
        $operatingSoftware = $this->fuzzyGet($row, ['operating_software', 'software']);
        $countryOfOrigin = $this->fuzzyGet($row, ['country_of_origin', 'country']);
        $installationYear = $this->fuzzyGet($row, ['installation_year', 'year']);
        $powerRequirement = $this->fuzzyGet($row, ['power_requirement', 'power']);
        $manualAvailability = $this->fuzzyGet($row, ['manual_availability', 'manual']);

        $comments = [];
        if (!empty($operatingSoftware)) $comments[] = "Software: " . $operatingSoftware;
        if (!empty($countryOfOrigin)) $comments[] = "Country: " . $countryOfOrigin;
        if (!empty($installationYear)) $comments[] = "Year: " . $installationYear;
        if (!empty($powerRequirement)) $comments[] = "Power: " . $powerRequirement;
        if (!empty($manualAvailability)) $comments[] = "Manual: " . $manualAvailability;
        $commentString = implode(' | ', $comments);

        // Parse date purchased from installation year
        $datePurchased = null;
        if (!empty($installationYear)) {
            if (is_numeric($installationYear)) {
                $datePurchased = $installationYear . "-01-01";
            } else {
                try {
                    $datePurchased = \Carbon\Carbon::parse($installationYear)->format('Y-m-d');
                } catch (\Exception $e) {
                    $datePurchased = date('Y-m-d');
                }
            }
        } else {
            $datePurchased = date('Y-m-d');
        }

        // Resolve lab name to primary lab & matching location
        $labName = $this->fuzzyGet($row, ['lab_office_name', 'lab_name', 'office_name', 'lab', 'office']);
        if (empty($labName)) {
            $labName = 'Main Office';
        }
        $labId = null;
        $assetLocationId = null;
        $assignedDepartmentId = null;

        // Perform case-insensitive fuzzy lookup
        $lab = \App\Lab::where('name', 'like', "%$labName%")->first();
        if ($lab) {
            $labId = $lab->id;
            if ($lab->zone) {
                $assetLocationId = $lab->zone->inventory_location_id;
            }
            
            // Look up or create InventoryDepartment matching lab name
            $department = \App\InventoryDepartment::where('module', 'organizational')
                ->where('name', 'like', "%$labName%")
                ->where('company_id', $this->batch->company_id)
                ->first();

            if (!$department) {
                $department = \App\InventoryDepartment::create([
                    'name' => $labName,
                    'module' => 'organizational',
                    'company_id' => $this->batch->company_id,
                    'location_id' => $assetLocationId,
                    'active' => 1,
                ]);
            }
            $assignedDepartmentId = $department->id;
        }

        // Zone selection override from dropdown UI
        if ($this->selectedZoneId) {
            $zone = \App\Zone::find($this->selectedZoneId);
            if ($zone) {
                $assetLocationId = $zone->inventory_location_id;
                $firstLab = \App\Lab::where('zone_id', $zone->id)
                    ->where('company_id', $this->batch->company_id)
                    ->first();
                if ($firstLab) {
                    $labId = $firstLab->id;
                    $labNameFallback = $firstLab->name;
                    $department = \App\InventoryDepartment::where('module', 'organizational')
                        ->where('name', 'like', "%$labNameFallback%")
                        ->where('company_id', $this->batch->company_id)
                        ->first();

                    if (!$department) {
                        $department = \App\InventoryDepartment::create([
                            'name' => $labNameFallback,
                            'module' => 'organizational',
                            'company_id' => $this->batch->company_id,
                            'location_id' => $assetLocationId,
                            'active' => 1,
                        ]);
                    }
                    $assignedDepartmentId = $department->id;
                }
            }
        }

        // Hardening: Ensure asset_location_id exists in asset_locations table
        if ($assetLocationId) {
            if (!\App\Models\Assets\AssetLocation::where('id', $assetLocationId)->exists()) {
                try {
                    $loc = new \App\Models\Assets\AssetLocation();
                    $loc->id = $assetLocationId;
                    $loc->location_code = 'LOC-' . strtoupper(substr($assetLocationId, 0, 8));
                    $loc->name = $labName . ' Location';
                    $loc->is_active = true;
                    $loc->save();
                } catch (\Throwable $t) {
                    // Fallback to any existing location if creation fails
                    $fallbackLoc = \App\Models\Assets\AssetLocation::first();
                    $assetLocationId = $fallbackLoc ? $fallbackLoc->id : null;
                }
            }
        } else {
            // Find the first location in asset_locations, or create a default one
            $defaultLocation = \App\Models\Assets\AssetLocation::first();
            if (!$defaultLocation) {
                try {
                    $defaultLocation = new \App\Models\Assets\AssetLocation();
                    $defaultLocation->location_code = 'LOC-DEFAULT';
                    $defaultLocation->name = 'Default Location';
                    $defaultLocation->is_active = true;
                    $defaultLocation->save();
                } catch (\Throwable $t) {}
            }
            $assetLocationId = $defaultLocation ? $defaultLocation->id : null;
        }

        $status = $this->fuzzyGet($row, ['status']);

        return [
            'equipment_number' => $equipmentNumber,
            'name' => $name,
            'description' => $name,
            'make' => $make,
            'model' => $model,
            'serial_number' => $serialNumber,
            'asset_location_id' => $assetLocationId,
            'assigned_department' => $assignedDepartmentId,
            'date_purchased' => $datePurchased,
            'calibration_days' => 365,
            'maintainance_days' => 365,
            'status' => $status ?? 'Active',
            'condition' => $status ?? 'Active',
            'comment' => $commentString,
            'active' => true,
            'is_disposal' => false,
            'picture' => '/images/default-equipment.png',
            'company_id' => $this->batch->company_id,
            '_resolved_lab_id' => $labId,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $labId = $transformedData['_resolved_lab_id'] ?? null;
            unset($transformedData['_resolved_lab_id']);

            $equipment = Equipment::updateOrCreate(
                ['equipment_number' => $transformedData['equipment_number'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            if ($labId) {
                $equipment->lab_id = $labId;
                $equipment->save();
            }

            $this->recordUpsert($transformedData['equipment_number'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import equipment: {$e->getMessage()}");
        }
    }
}
