<?php

namespace App\Imports;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\InventoryDepartment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EquipmentImport extends BaseImporter
{
    protected ?string $selectedZoneId = null;

    public function __construct(?BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch);
        $this->selectedZoneId = $selectedZoneId;
        if ($this->batch->module === 'generic') {
            $this->batch->update(['module' => 'inventory', 'form_type' => 'equipment']);
        }
    }

    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($this->fuzzyGet($row, ['equipment_instrument', 'instrument', 'name', 'equipment_name', 'item', 'description']))) {
            $errors[] = 'Equipment Name is required';
        }

        if (empty($this->fuzzyGet($row, ['gcla_code', 'equipment_number', 'equipmentnumber', 'asset_number', 'code']))) {
            $errors[] = 'Equipment Number is required';
        }

        if (empty($this->fuzzyGet($row, ['model']))) {
            $errors[] = 'Model is required';
        }

        $labName = $this->fuzzyGet($row, ['lab_office_name', 'lab_name', 'office_name', 'lab', 'office']);
        $departmentName = $this->fuzzyGet($row, ['assigned_department', 'assigneddepartment', 'department', 'unit', 'section']);
        if (empty($labName) && empty($departmentName)) {
            $errors[] = 'Lab/Office Name or Assigned Department is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $equipmentNumber = $this->fuzzyGet($row, ['gcla_code', 'equipment_number', 'equipmentnumber', 'asset_number', 'code']);
        
        // Check for duplicates
        $existing = Equipment::where('equipment_number', $equipmentNumber)
            ->where('company_id', $this->batch->company_id)
            ->first();
        
        if ($existing) {
            return false; // Skip duplicates or handle accordingly
        }

        $name = $this->fuzzyGet($row, ['equipment_instrument', 'instrument', 'name', 'equipment_name', 'item', 'description']);
        if (empty($name)) {
            $name = 'Unnamed Equipment';
        }
        $model = $this->fuzzyGet($row, ['model']);
        if (empty($model)) {
            $model = 'Unknown';
        }
        $serialNumber = $this->fuzzyGet($row, ['serial_no', 'serial_number', 'serialnumber', 'sn']);
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

        // Parse date purchased from installation year or date_purchased
        $datePurchasedValue = $this->fuzzyGet($row, ['date_purchased', 'datepurchased', 'purchased_on', 'purchasedon', 'date_of_purchase']);
        $datePurchased = null;
        if (!empty($datePurchasedValue)) {
            $datePurchased = $this->parseDate($datePurchasedValue);
        } elseif (!empty($installationYear)) {
            if (is_numeric($installationYear)) {
                $datePurchased = $installationYear . "-01-01";
            } else {
                $datePurchased = $this->parseDate($installationYear);
            }
        }
        if (empty($datePurchased)) {
            $datePurchased = date('Y-m-d');
        }

        // Resolve lab name / assigned department / locations
        $labName = $this->fuzzyGet($row, ['lab_office_name', 'lab_name', 'office_name', 'lab', 'office']);
        $departmentName = $this->fuzzyGet($row, ['assigned_department', 'assigneddepartment', 'department', 'unit', 'section']);
        if (empty($labName) && empty($departmentName)) {
            $labName = 'Main Office';
            $departmentName = 'Main Office';
        }
        
        $labId = null;
        $assetLocationId = null;
        $assignedDepartmentId = null;

        // Perform case-insensitive fuzzy lookup for Lab
        if (!empty($labName)) {
            $lab = \App\Lab::where('name', 'like', "%$labName%")->first();
            if ($lab) {
                $labId = $lab->id;
                if ($lab->zone) {
                    $assetLocationId = $lab->zone->inventory_location_id;
                }
                
                // Fall back department to Lab Name if none specified
                if (empty($departmentName)) {
                    $departmentName = $labName;
                }
            }
        }

        // Handle Department resolution/creation
        if (!empty($departmentName)) {
            $department = InventoryDepartment::where('module', 'organizational')
                ->where('name', 'like', "%$departmentName%")
                ->where('company_id', $this->batch->company_id)
                ->first();

            if (!$department) {
                $locationId = $assetLocationId;
                if (empty($locationId)) {
                    try {
                        if (function_exists('getCurrentUserLocation')) {
                            $loc = getCurrentUserLocation();
                            $locationId = $loc->id ?? null;
                        }
                    } catch (\Throwable $t) {}
                }

                $department = InventoryDepartment::create([
                    'name' => $departmentName,
                    'module' => 'organizational',
                    'company_id' => $this->batch->company_id,
                    'location_id' => $locationId,
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
                    $department = InventoryDepartment::where('module', 'organizational')
                        ->where('name', 'like', "%$labNameFallback%")
                        ->where('company_id', $this->batch->company_id)
                        ->first();

                    if (!$department) {
                        $department = InventoryDepartment::create([
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

        $prevCalDate = $this->fuzzyGet($row, ['previous_calibration_date', 'previouscalibrationdate', 'last_calibration', 'last_cal']);
        $prevMaintDate = $this->fuzzyGet($row, ['previous_maintainance_date', 'previousmaintainancedate', 'previous_maintenance_date', 'last_maintenance', 'last_maint']);
        
        $status = $this->fuzzyGet($row, ['status']);

        // Parse new optional fields
        $purchasePrice = $this->fuzzyGet($row, ['purchase_price', 'purchaseprice', 'price', 'cost']);
        $supplierName = $this->fuzzyGet($row, ['supplier_name', 'suppliername', 'supplier', 'vendor']);
        $installationDate = $this->fuzzyGet($row, ['installation_date', 'installationdate', 'date_of_installation', 'installed_on']);
        $commissioningDate = $this->fuzzyGet($row, ['commissioning_date', 'commissioningdate', 'date_of_commissioning', 'commissioned_on']);
        $detectionLimit = $this->fuzzyGet($row, ['detection_limit', 'detectionlimit', 'dl']);
        $toleranceLimit = $this->fuzzyGet($row, ['tolerance_limit', 'tolerancelimit', 'tl']);
        $warranty = $this->fuzzyGet($row, ['warranty', 'warranty_duration', 'warranty_period']);
        $environment = $this->fuzzyGet($row, ['environment', 'operating_environment', 'env']);
        $endOfLife = $this->fuzzyGet($row, ['end_of_life', 'endoflife', 'eol']);
        $endOfService = $this->fuzzyGet($row, ['end_of_service', 'endofservice', 'eos']);

        return [
            'name' => $name,
            'equipment_number' => $equipmentNumber,
            'description' => $name,
            'make' => $make,
            'model' => $model,
            'serial_number' => $serialNumber,
            'manufacturer' => $make,
            'assigned_department' => $assignedDepartmentId,
            'date_purchased' => $datePurchased,
            'calibration_days' => 365,
            'maintainance_days' => 365,
            'status' => $status ?? 'Active',
            'condition' => $status ?? 'Active',
            'comment' => $commentString,
            'active' => true,
            'company_id' => $this->batch->company_id,
            'is_disposal' => 0,
            'picture' => '/images/default-equipment.png',
            'asset_location_id' => $assetLocationId,
            
            // New fields
            'purchase_price' => is_numeric($purchasePrice) ? (float) $purchasePrice : null,
            'supplier_name' => $supplierName ?: null,
            'installation_date' => $installationDate ? $this->parseDate($installationDate) : null,
            'commissioning_date' => $commissioningDate ? $this->parseDate($commissioningDate) : null,
            'detection_limit' => $detectionLimit ?: null,
            'tolerance_limit' => $toleranceLimit ?: null,
            'warranty' => $warranty ?: null,
            'environment' => $environment ?: null,
            'end_of_life' => $endOfLife ? $this->parseDate($endOfLife) : null,
            'end_of_service' => $endOfService ? $this->parseDate($endOfService) : null,

            '_calibration_date' => $this->parseDate($prevCalDate),
            '_maintenance_date' => $this->parseDate($prevMaintDate),
            '_resolved_lab_id' => $labId,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        $calDate = $transformedData['_calibration_date'] ?? null;
        $maintDate = $transformedData['_maintenance_date'] ?? null;
        $labId = $transformedData['_resolved_lab_id'] ?? null;
        
        unset($transformedData['_calibration_date'], $transformedData['_maintenance_date'], $transformedData['_resolved_lab_id']);

        $equipment = Equipment::create($transformedData);

        if ($labId) {
            $equipment->lab_id = $labId;
            $equipment->save();
        }

        if ($calDate) {
            MaintainanceCalibrationLog::create([
                'equipment_id' => $equipment->id,
                'type' => 'Calibration',
                'date' => $calDate,
                'notes' => 'Imported from Excel',
                'overseen_by' => $this->batch->user_id,
                'edit_by' => $this->batch->user_id,
                'certificate' => 'no-document',
            ]);
        }

        if ($maintDate) {
            MaintainanceCalibrationLog::create([
                'equipment_id' => $equipment->id,
                'type' => 'Maintainance',
                'date' => $maintDate,
                'notes' => 'Imported from Excel',
                'overseen_by' => $this->batch->user_id,
                'edit_by' => $this->batch->user_id,
                'certificate' => 'no-document',
            ]);
        }

        $this->recordUpsert($equipment->equipment_number, 'inserted');
        return true;
    }

    /**
     * Parse date from various formats.
     */
    protected function parseDate($dateValue): ?string
    {
        if (empty($dateValue)) return null;

        if ($dateValue instanceof \DateTime || $dateValue instanceof Carbon) {
            return $dateValue->format('Y-m-d');
        }

        if (is_numeric($dateValue)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse((string)$dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
