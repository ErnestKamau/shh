<?php

namespace App\Imports\Equipment;

use App\Imports\BaseImporter;
use App\Models\BulkImportBatch;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\InventoryDepartment;
use Carbon\Carbon;

class EquipmentImporter extends BaseImporter
{
    protected ?string $selectedZoneId = null;

    public function __construct(?BulkImportBatch $batch = null, ?string $selectedZoneId = null)
    {
        parent::__construct($batch);
        $this->selectedZoneId = $selectedZoneId;
    }

    protected function validateRow(array $row): array
    {
        $errors = [];

        if (!$this->hasFuzzy($row, $this->nameKeys())) {
            $errors[] = 'Equipment Name is required';
        }

        if (!$this->hasFuzzy($row, ['model'])) {
            $errors[] = 'Model is required';
        }

        if (!$this->hasFuzzy($row, $this->equipmentNumberKeys())) {
            $errors[] = 'Equipment ID / Number is required';
        }

        $hasLab = $this->hasFuzzy($row, $this->labKeys());
        $hasDepartment = $this->hasFuzzy($row, $this->departmentKeys());
        if (!$hasLab && !$hasDepartment) {
            $errors[] = 'Lab/Office Name or Department is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $equipmentNumber = $this->fuzzyGet($row, $this->equipmentNumberKeys());
        $name = $this->fuzzyGet($row, $this->nameKeys());
        if (empty($name)) {
            $name = 'Unnamed Equipment';
        }
        $model = $this->fuzzyGet($row, ['model']);
        if (empty($model)) {
            $model = 'Unknown';
        }
        $serialNumber = $this->fuzzyGet($row, $this->serialKeys());
        if (empty($serialNumber) || strtolower((string) $serialNumber) === 'nil') {
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
        if (!empty($operatingSoftware)) {
            $comments[] = "Software: " . $operatingSoftware;
        }
        if (!empty($countryOfOrigin)) {
            $comments[] = "Country: " . $countryOfOrigin;
        }
        if (!empty($installationYear)) {
            $comments[] = "Year: " . $installationYear;
        }
        if (!empty($powerRequirement)) {
            $comments[] = "Power: " . $powerRequirement;
        }
        if (!empty($manualAvailability)) {
            $comments[] = "Manual: " . $manualAvailability;
        }
        $commentString = implode(' | ', $comments);

        // Parse date purchased from installation year
        $datePurchased = null;
        if (!empty($installationYear)) {
            if (is_numeric($installationYear)) {
                $datePurchased = $installationYear . "-01-01";
            } else {
                $datePurchased = $this->parseDate($installationYear) ?? date('Y-m-d');
            }
        } else {
            $datePurchased = date('Y-m-d');
        }

        // Resolve lab name / department to primary lab & matching location
        $labName = $this->fuzzyGet($row, $this->labKeys());
        $departmentName = $this->fuzzyGet($row, $this->departmentKeys());
        if (empty($labName) && empty($departmentName)) {
            $labName = 'Main Office';
            $departmentName = 'Main Office';
        } elseif (empty($departmentName)) {
            $departmentName = $labName;
        }

        $labId = null;
        $assetLocationId = null;
        $assignedDepartmentId = null;

        // Perform case-insensitive fuzzy lookup
        if (!empty($labName)) {
            $lab = \App\Lab::where('name', 'like', "%$labName%")->first();
            if ($lab) {
                $labId = $lab->id;
                if ($lab->zone) {
                    $assetLocationId = $lab->zone->inventory_location_id;
                }
            }
        }

        if (!empty($departmentName)) {
            $department = InventoryDepartment::where('module', 'organizational')
                ->where('name', 'like', "%$departmentName%")
                ->where('company_id', $this->batch->company_id)
                ->first();

            if (!$department) {
                $department = InventoryDepartment::create([
                    'name' => $departmentName,
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
                    $loc->name = ($labName ?: $departmentName) . ' Location';
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
                } catch (\Throwable $t) {
                }
            }
            $assetLocationId = $defaultLocation ? $defaultLocation->id : null;
        }

        $calibrationDate = $this->resolveCalibrationDate($row);
        $status = $this->normalizeOperationalStatus(
            $this->fuzzyGet($row, ['operational_status', 'status', 'condition'])
        );

        $calibrationDays = $this->resolveCalibrationDays($row, $calibrationDate);
        $maintainanceDays = $this->resolveMaintainanceDays($row, $calibrationDays);

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
            'equipment_number' => $equipmentNumber,
            'name' => $name,
            'description' => $name,
            'make' => $make,
            'model' => $model,
            'serial_number' => $serialNumber,
            'manufacturer' => $make,
            'asset_location_id' => $assetLocationId,
            'assigned_department' => $assignedDepartmentId,
            'date_purchased' => $datePurchased,
            'calibration_days' => $calibrationDays,
            'maintainance_days' => $maintainanceDays,
            'status' => $status,
            'condition' => $status,
            'comment' => $commentString,
            'active' => true,
            'is_disposal' => false,
            'picture' => $this->resolvePicture($row),
            'company_id' => $this->batch->company_id,

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

            '_calibration_date' => $calibrationDate,
            '_resolved_lab_id' => $labId,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $labId = $transformedData['_resolved_lab_id'] ?? null;
            $calDate = $transformedData['_calibration_date'] ?? null;
            unset($transformedData['_resolved_lab_id'], $transformedData['_calibration_date']);

            $existing = Equipment::query()
                ->where('equipment_number', $transformedData['equipment_number'])
                ->where('company_id', $this->batch->company_id)
                ->first();

            if ($existing !== null && Equipment::isValidPicturePath($existing->picture)) {
                unset($transformedData['picture']);
            }

            $equipment = Equipment::updateOrCreate(
                ['equipment_number' => $transformedData['equipment_number'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            if ($labId) {
                $equipment->lab_id = $labId;
                $equipment->save();
            }

            if ($calDate) {
                $existingLog = MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
                    ->where('type', 'Calibration')
                    ->whereDate('date', $calDate)
                    ->first();

                if (!$existingLog) {
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
            }

            $this->recordUpsert($transformedData['equipment_number'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import equipment: {$e->getMessage()}");
        }
    }

    /**
     * @return array<int, string>
     */
    protected function nameKeys(): array
    {
        return [
            'equipment_name',
            'equipment_instrument',
            'instrument',
            'equipment',
            'name',
            'item',
            'description',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function equipmentNumberKeys(): array
    {
        return [
            'equipment_id',
            'gcla_code',
            'equipment_number',
            'equipmentnumber',
            'equipment_no',
            'asset_number',
            'code',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function serialKeys(): array
    {
        return [
            'serial',
            'serial_no',
            'serial_number',
            'serialnumber',
            'sn',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function labKeys(): array
    {
        return ['lab_office_name', 'lab_name', 'office_name', 'lab', 'office'];
    }

    /**
     * @return array<int, string>
     */
    protected function departmentKeys(): array
    {
        return ['assigned_department', 'assigneddepartment', 'department', 'unit', 'section'];
    }

    protected function resolveCalibrationDate(array $row): ?string
    {
        $direct = $this->fuzzyGet($row, [
            'calibration_date',
            'previous_calibration_date',
            'previouscalibrationdate',
            'last_calibration',
            'last_cal',
            'last_calibration_date',
        ]);

        $parsed = $this->parseDate($direct);
        if ($parsed) {
            return $parsed;
        }

        $dueDate = $this->parseDate($this->fuzzyGet($row, [
            'calibration_due_date',
            'calibrationduedate',
            'due_date',
            'next_calibration_date',
        ]));
        $months = $this->fuzzyGet($row, [
            'calibration_duration_months',
            'calibration_duration_month',
            'calibration_duration',
            'calibration_interval_months',
            'interval_months',
        ]);

        if ($dueDate && is_numeric($months) && (float) $months > 0) {
            try {
                return Carbon::parse($dueDate)->subMonthsNoOverflow((int) $months)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    protected function resolveCalibrationDays(array $row, ?string $calibrationDate): int
    {
        $months = $this->fuzzyGet($row, [
            'calibration_duration_months',
            'calibration_duration_month',
            'calibration_duration',
            'calibration_interval_months',
            'interval_months',
        ]);
        if (is_numeric($months) && (float) $months > 0) {
            return max(1, (int) round(((float) $months) * 365 / 12));
        }

        $days = $this->fuzzyGet($row, [
            'calibration_interval_days',
            'calibration_days',
            'calibration_interval',
            'interval_days',
        ]);
        if (is_numeric($days) && (float) $days > 0) {
            return max(1, (int) $days);
        }

        $dueDate = $this->parseDate($this->fuzzyGet($row, [
            'calibration_due_date',
            'calibrationduedate',
            'due_date',
            'next_calibration_date',
        ]));
        if ($calibrationDate && $dueDate) {
            try {
                $diff = Carbon::parse($calibrationDate)->diffInDays(Carbon::parse($dueDate), false);
                if ($diff > 0) {
                    return (int) $diff;
                }
            } catch (\Exception $e) {
            }
        }

        return 365;
    }

    protected function resolveMaintainanceDays(array $row, int $fallback): int
    {
        $days = $this->fuzzyGet($row, [
            'intermediate_checks_interval_days',
            'maintainance_days',
            'maintenance_days',
            'maintenance_interval_days',
        ]);
        if (is_numeric($days) && (float) $days > 0) {
            return max(1, (int) $days);
        }

        return $fallback;
    }

    protected function resolvePicture(array $row): string
    {
        $raw = $this->fuzzyGet($row, ['photo', 'picture', 'image', 'image_url', 'photo_url']);

        return Equipment::sanitizePictureValue(is_scalar($raw) ? (string) $raw : null);
    }

    protected function normalizeOperationalStatus(?string $status): string
    {
        if ($status === null || trim($status) === '') {
            return 'Active';
        }

        $normalized = strtolower(trim($status));
        $map = [
            'in use' => 'Active',
            'in-use' => 'Active',
            'working' => 'Active',
            'active' => 'Active',
            'calibrated' => 'Active',
            'operational' => 'Active',
            'out of service' => 'Out Of Service',
            'out-of-service' => 'Out Of Service',
            'oos' => 'Out Of Service',
            'not in use' => 'Out Of Service',
            'idle' => 'Out Of Service',
            'obsolete' => 'Obsolete',
            'retired' => 'Obsolete',
            'disposed' => 'Obsolete',
        ];

        return $map[$normalized] ?? $status;
    }

    protected function parseDate($dateValue): ?string
    {
        if (empty($dateValue)) {
            return null;
        }

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
            return Carbon::parse((string) $dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
