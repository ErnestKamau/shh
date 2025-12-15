<?php

namespace App\Imports;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\InventoryDepartment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EquipmentImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header and 0-index
            
            // Skip empty rows
            if ($row->filter()->isEmpty()) {
                continue;
            }

            // WithHeadingRow converts headers to lowercase and replaces spaces with underscores
            // So "Date Purchased" becomes "date_purchased", "Purchased On" becomes "purchased_on"
            $name = $this->getValue($row, ['name']);
            $equipmentNumber = $this->getValue($row, ['equipment_number', 'equipmentnumber']);
            $make = $this->getValue($row, ['make']);
            $model = $this->getValue($row, ['model']);
            $serialNumber = $this->getValue($row, ['serial_number', 'serialnumber']);
            $manufacturer = $this->getValue($row, ['manufacturer']);
            $departmentName = $this->getValue($row, ['assigned_department', 'assigneddepartment', 'department']);
            $datePurchased = $this->getValue($row, ['date_purchased', 'datepurchased', 'purchased_on', 'purchasedon']);
            $previousCalibrationDate = $this->getValue($row, ['previous_calibration_date', 'previouscalibrationdate']);
            $calibrationInterval = $this->getValue($row, ['calibration_interval_days', 'calibrationintervaldays', 'calibration_interval', 'calibrationinterval']);
            $previousMaintainanceDate = $this->getValue($row, ['previous_maintainance_date', 'previousmaintainancedate', 'previous_maintenance_date', 'previousmaintenancedate']);
            $intermediateChecksInterval = $this->getValue($row, ['intermediate_checks_interval_days', 'intermediatechecksintervaldays', 'intermediate_checks_interval', 'intermediatechecksinterval']);

            // Trim all string values (dates might be DateTime objects or numbers)
            $name = is_string($name) ? trim($name ?? '') : (string)($name ?? '');
            $equipmentNumber = is_string($equipmentNumber) ? trim($equipmentNumber ?? '') : (string)($equipmentNumber ?? '');
            $make = is_string($make) ? trim($make ?? '') : (string)($make ?? '');
            $model = is_string($model) ? trim($model ?? '') : (string)($model ?? '');
            $serialNumber = is_string($serialNumber) ? trim($serialNumber ?? '') : (string)($serialNumber ?? '');
            $manufacturer = is_string($manufacturer) ? trim($manufacturer ?? '') : (string)($manufacturer ?? '');
            $departmentName = is_string($departmentName) ? trim($departmentName ?? '') : (string)($departmentName ?? '');
            $calibrationInterval = is_string($calibrationInterval) ? trim($calibrationInterval ?? '') : (string)($calibrationInterval ?? '');
            $previousMaintainanceDate = $previousMaintainanceDate; // Keep as-is for date parsing
            $intermediateChecksInterval = is_string($intermediateChecksInterval) ? trim($intermediateChecksInterval ?? '') : (string)($intermediateChecksInterval ?? '');
            // Keep date values as-is (might be DateTime, string, or number)
            $datePurchased = $datePurchased;
            $previousCalibrationDate = $previousCalibrationDate;

            // Check for duplicate equipment number
            $existingEquipment = Equipment::where('equipment_number', $equipmentNumber)
                ->where('company_id', getUserCompany())
                ->first();
            
            if ($existingEquipment) {
                $this->errorCount++;
                $this->errors[] = "Row {$rowNumber}: Equipment number '{$equipmentNumber}' already exists.";
                continue;
            }

            // Parse and validate date (nullable)
            $parsedDatePurchased = null;
            if (!empty($datePurchased)) {
                $parsedDatePurchased = $this->parseDate($datePurchased);
                if (!$parsedDatePurchased) {
                    $this->errorCount++;
                    $dateValueType = gettype($datePurchased);
                    $dateValueDisplay = is_object($datePurchased) ? get_class($datePurchased) : (string)$datePurchased;
                    $this->errors[] = "Row {$rowNumber}: The date purchased is not a valid date. Value: '{$dateValueDisplay}' (type: {$dateValueType})";
                    continue;
                }
            }

            // Validate row data
            $validator = Validator::make([
                'name' => $name,
                'equipment_number' => $equipmentNumber,
                'make' => $make,
                'model' => $model,
                'department_name' => $departmentName,
                'calibration_interval' => $calibrationInterval,
                'intermediate_checks_interval' => $intermediateChecksInterval,
            ], [
                'name' => 'required|string|max:255',
                'equipment_number' => 'required|string|max:255',
                'make' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'department_name' => 'required|string',
                'calibration_interval' => 'required|integer|min:0',
                'intermediate_checks_interval' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                $this->errorCount++;
                $this->errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                DB::beginTransaction();

                // Get or create department
                $department = InventoryDepartment::where('module', 'organizational')
                    ->where('name', $departmentName)
                    ->first();

                if (!$department) {
                    $locationId = null;
                    if (function_exists('getCurrentUserLocation')) {
                        $location = getCurrentUserLocation();
                        $locationId = $location ? $location->id : null;
                    }
                    
                    $department = InventoryDepartment::create([
                        'name' => $departmentName,
                        'module' => 'organizational',
                        'company_id' => getUserCompany(),
                        'location_id' => $locationId,
                        'active' => 1,
                    ]);
                }

                // Prepare equipment data
                $equipmentData = [
                    'name' => $name,
                    'equipment_number' => $equipmentNumber,
                    'description' => $name, // Use name as description
                    'make' => $make,
                    'model' => $model,
                    'serial_number' => $serialNumber ?: null,
                    'manufacturer' => $manufacturer ?: null,
                    'assigned_department' => $department->id,
                    'date_purchased' => $parsedDatePurchased ? $parsedDatePurchased->format('Y-m-d') : null,
                    'calibration_days' => (int)$calibrationInterval,
                    'calibration_notification_in_days' => (int)$calibrationInterval,
                    'maintainance_days' => 365, // Default to 365 days
                    'maintainance_notification_in_days' => (int)$intermediateChecksInterval,
                    'status' => 'Active',
                    'condition' => 'Active',
                    'warranty_date' => null, // Nullable
                    'picture' => '/images/default-equipment.png', // Default picture for bulk import
                    'active' => true,
                    'company_id' => getUserCompany(),
                    'is_disposal' => 0,
                ];

                // Create equipment
                $equipment = Equipment::create($equipmentData);

                // Create calibration log if previous calibration date provided and valid
                if (!empty($previousCalibrationDate)) {
                    $calibrationDate = $this->parseDate($previousCalibrationDate);
                    if ($calibrationDate) {
                        MaintainanceCalibrationLog::create([
                            'equipment_id' => $equipment->id,
                            'type' => 'Calibration',
                            'date' => $calibrationDate->format('Y-m-d'),
                            'notes' => 'from the bulk upload',
                            'overseen_by' => auth()->id(),
                            'edit_by' => auth()->id(),
                            'certificate' => 'no-document',
                        ]);
                    } else {
                        // Log warning but don't fail the import
                        $this->errors[] = "Row {$rowNumber}: Previous calibration date is invalid, skipping calibration log creation.";
                    }
                }

                // Create maintenance log if previous maintenance date provided and valid
                if (!empty($previousMaintainanceDate)) {
                    $maintenanceDate = $this->parseDate($previousMaintainanceDate);
                    if ($maintenanceDate) {
                        MaintainanceCalibrationLog::create([
                            'equipment_id' => $equipment->id,
                            'type' => 'Maintainance',
                            'date' => $maintenanceDate->format('Y-m-d'),
                            'notes' => 'from the bulk upload',
                            'overseen_by' => auth()->id(),
                            'edit_by' => auth()->id(),
                            'certificate' => 'no-document',
                        ]);
                    } else {
                        // Log warning but don't fail the import
                        $this->errors[] = "Row {$rowNumber}: Previous maintenance date is invalid, skipping maintenance log creation.";
                    }
                }

                DB::commit();
                $this->successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->errorCount++;
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    /**
     * Get value from row by trying multiple possible column names
     * WithHeadingRow normalizes headers to lowercase with underscores
     */
    protected function getValue(Collection $row, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Check if key exists in collection (case-insensitive for safety)
            $normalizedKey = strtolower(str_replace([' ', '-'], '_', $key));
            
            // Try exact match first
            if ($row->has($key)) {
                $value = $row->get($key);
                return $value !== null ? $value : null;
            }
            
            // Try normalized key
            if ($row->has($normalizedKey)) {
                $value = $row->get($normalizedKey);
                return $value !== null ? $value : null;
            }
            
            // Try case-insensitive search
            foreach ($row->keys() as $rowKey) {
                if (strtolower(str_replace([' ', '-'], '_', $rowKey)) === $normalizedKey) {
                    $value = $row->get($rowKey);
                    return $value !== null ? $value : null;
                }
            }
        }
        return null;
    }

    /**
     * Parse date from various formats
     * Handles DateTime objects, Excel serial dates (numbers), and string dates
     */
    protected function parseDate($dateValue): ?Carbon
    {
        if (empty($dateValue)) {
            return null;
        }

        // If it's already a DateTime or Carbon instance
        if ($dateValue instanceof \DateTime || $dateValue instanceof Carbon) {
            return Carbon::instance($dateValue);
        }

        // If it's a numeric value (Excel serial date)
        if (is_numeric($dateValue)) {
            try {
                // Excel serial date: days since January 1, 1900
                // PHP timestamp: seconds since January 1, 1970
                // Excel epoch: 1900-01-01
                // PHP epoch: 1970-01-01
                // Difference: 25569 days
                $excelEpoch = 25569; // Days between 1900-01-01 and 1970-01-01
                $timestamp = ($dateValue - $excelEpoch) * 86400; // Convert days to seconds
                return Carbon::createFromTimestamp($timestamp);
            } catch (\Exception $e) {
                // If conversion fails, try as regular timestamp
                try {
                    return Carbon::createFromTimestamp($dateValue);
                } catch (\Exception $e2) {
                    return null;
                }
            }
        }

        // Convert to string if not already
        $dateString = (string)$dateValue;
        if (empty(trim($dateString))) {
            return null;
        }

        // Try common date formats (including d/m/y with 2-digit year)
        $formats = [
            'Y-m-d',           // 2025-12-15
            'Y/m/d',           // 2025/12/15
            'd-m-Y',           // 15-12-2025
            'd/m/Y',           // 15/12/2025
            'd/m/y',           // 15/12/25 (2-digit year)
            'd-m-y',           // 15-12-25 (2-digit year)
            'm/d/Y',           // 12/15/2025
            'm/d/y',           // 12/15/25 (2-digit year)
            'd.m.Y',           // 15.12.2025
            'd.m.y',           // 15.12.25 (2-digit year)
            'Y-m-d H:i:s',     // 2025-12-15 00:00:00
            'Y-m-d H:i:s.u',   // 2025-12-15 00:00:00.000000
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's flexible parser as last resort
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
