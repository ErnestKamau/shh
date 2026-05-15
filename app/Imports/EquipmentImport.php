<?php

namespace App\Imports;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\InventoryDepartment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EquipmentImport extends BaseImporter
{
    public function __construct(?BulkImportBatch $batch = null)
    {
        parent::__construct($batch);
        if ($this->batch->module === 'generic') {
            $this->batch->update(['module' => 'inventory', 'form_type' => 'equipment']);
        }
    }

    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($this->fuzzyGet($row, ['name', 'equipment_name']))) {
            $errors[] = 'Equipment Name is required';
        }

        if (empty($this->fuzzyGet($row, ['equipment_number', 'equipmentnumber', 'asset_number']))) {
            $errors[] = 'Equipment Number is required';
        }

        if (empty($this->fuzzyGet($row, ['make', 'brand']))) {
            $errors[] = 'Make is required';
        }

        if (empty($this->fuzzyGet($row, ['model']))) {
            $errors[] = 'Model is required';
        }

        $dept = $this->fuzzyGet($row, ['assigned_department', 'assigneddepartment', 'department', 'unit', 'section']);
        if (empty($dept)) {
            $errors[] = 'Assigned Department is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $equipmentNumber = $this->fuzzyGet($row, ['equipment_number', 'equipmentnumber', 'asset_number']);
        
        // Check for duplicates
        $existing = Equipment::where('equipment_number', $equipmentNumber)
            ->where('company_id', $this->batch->company_id)
            ->first();
        
        if ($existing) {
            return false; // Skip duplicates or handle accordingly
        }

        $departmentName = $this->fuzzyGet($row, ['assigned_department', 'assigneddepartment', 'department', 'unit', 'section']);
        $department = InventoryDepartment::where('module', 'organizational')
            ->where('name', 'like', "%$departmentName%")
            ->where('company_id', $this->batch->company_id)
            ->first();

        if (!$department) {
            $department = InventoryDepartment::create([
                'name' => $departmentName,
                'module' => 'organizational',
                'company_id' => $this->batch->company_id,
                'active' => 1,
            ]);
        }

        $datePurchased = $this->fuzzyGet($row, ['date_purchased', 'datepurchased', 'purchased_on', 'purchasedon', 'date_of_purchase']);
        $prevCalDate = $this->fuzzyGet($row, ['previous_calibration_date', 'previouscalibrationdate', 'last_calibration', 'last_cal']);
        $prevMaintDate = $this->fuzzyGet($row, ['previous_maintainance_date', 'previousmaintainancedate', 'previous_maintenance_date', 'last_maintenance', 'last_maint']);

        return [
            'name' => $this->fuzzyGet($row, ['name', 'equipment_name']),
            'equipment_number' => $equipmentNumber,
            'description' => $this->fuzzyGet($row, ['description', 'name']),
            'make' => $this->fuzzyGet($row, ['make', 'brand']),
            'model' => $this->fuzzyGet($row, ['model']),
            'serial_number' => $this->fuzzyGet($row, ['serial_number', 'serialnumber', 'sn']),
            'manufacturer' => $this->fuzzyGet($row, ['manufacturer', 'mfr']),
            'assigned_department' => $department->id,
            'date_purchased' => $this->parseDate($datePurchased),
            'calibration_days' => (int) $this->fuzzyGet($row, ['calibration_interval_days', 'calibration_interval'], 365),
            'maintainance_days' => (int) $this->fuzzyGet($row, ['intermediate_checks_interval_days', 'maintenance_interval'], 365),
            'status' => 'Active',
            'condition' => 'Active',
            'active' => true,
            'company_id' => $this->batch->company_id,
            'is_disposal' => 0,
            'picture' => '/images/default-equipment.png',
            '_calibration_date' => $this->parseDate($prevCalDate),
            '_maintenance_date' => $this->parseDate($prevMaintDate),
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        $calDate = $transformedData['_calibration_date'] ?? null;
        $maintDate = $transformedData['_maintenance_date'] ?? null;
        
        unset($transformedData['_calibration_date'], $transformedData['_maintenance_date']);

        $equipment = Equipment::create($transformedData);

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
