<?php

namespace App\Services\Equipment;

use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentDisposal;
use App\Models\Equipments\MaintainanceCalibrationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EquipmentDecommissioningService
{
    protected $auditService;

    public function __construct(DisposalAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Start decommissioning process
     *
     * @param EquipmentDisposal $disposal
     * @return array
     */
    public function startDecommissioning(EquipmentDisposal $disposal): array
    {
        DB::beginTransaction();

        try {
            $equipment = $disposal->equipment;

            // Update equipment status
            $equipment->status = 'OUT OF SERVICE - PENDING DISPOSAL';
            $equipment->active = 0;
            $equipment->save();

            // Initialize decommissioning checklist
            $checklist = $this->getDefaultChecklist($equipment);
            
            $disposal->decommissioning_checklist_json = $checklist;
            $disposal->decommissioning_date = now();
            $disposal->decommissioned_by = auth()->id();
            $disposal->save();

            // Log decommissioning start
            $this->auditService->logAction(
                $disposal,
                'decommissioning_started',
                ['status' => $equipment->getOriginal('status')],
                ['status' => 'OUT OF SERVICE - PENDING DISPOSAL'],
                "Decommissioning process initiated for equipment {$equipment->name}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Decommissioning process started successfully.',
                'checklist' => $checklist,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error starting decommissioning: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get default decommissioning checklist
     *
     * @param Equipment $equipment
     * @return array
     */
    protected function getDefaultChecklist(Equipment $equipment): array
    {
        $checklist = [
            'equipment_labeled' => [
                'required' => true,
                'completed' => false,
                'label' => 'Equipment labeled "OUT OF SERVICE - FOR DISPOSAL" (ISO 17025 Clause 6.4.13)',
            ],
            'removed_from_calibration_schedule' => [
                'required' => true,
                'completed' => false,
                'label' => 'Removed from calibration schedule',
            ],
            'removed_from_maintenance_schedule' => [
                'required' => true,
                'completed' => false,
                'label' => 'Removed from maintenance schedule',
            ],
            'utilities_disconnected' => [
                'required' => true,
                'completed' => false,
                'label' => 'Utilities disconnected (power, water, gas, etc.)',
            ],
        ];

        // Add data security items if equipment is electronic
        if ($equipment->isElectronic()) {
            $checklist['data_wiped'] = [
                'required' => true,
                'completed' => false,
                'label' => 'Data wiped from electronic storage',
            ];
            $checklist['storage_devices_removed'] = [
                'required' => true,
                'completed' => false,
                'label' => 'Storage devices physically removed',
            ];
            $checklist['factory_reset'] = [
                'required' => true,
                'completed' => false,
                'label' => 'Factory reset performed',
            ];
        }

        return $checklist;
    }

    /**
     * Label equipment and upload photo evidence
     *
     * @param EquipmentDisposal $disposal
     * @param mixed $photo
     * @return array
     */
    public function labelEquipment(EquipmentDisposal $disposal, $photo): array
    {
        try {
            // Store label photo
            $path = $photo->store('equipment-disposals/labels/' . $disposal->id, 'public');
            $relativePath = '/storage/' . $path;

            $disposal->equipment_labeled = true;
            $disposal->label_photo_path = $relativePath;
            
            // Update checklist
            $checklist = $disposal->decommissioning_checklist_json ?? [];
            if (isset($checklist['equipment_labeled'])) {
                $checklist['equipment_labeled']['completed'] = true;
                $checklist['equipment_labeled']['completed_at'] = now()->toIso8601String();
                $checklist['equipment_labeled']['completed_by'] = auth()->user()->name;
            }
            $disposal->decommissioning_checklist_json = $checklist;
            $disposal->save();

            // Log action
            $this->auditService->logAction(
                $disposal,
                'equipment_labeled',
                null,
                ['labeled' => true, 'photo_path' => $relativePath],
                "Equipment labeled with photo evidence uploaded"
            );

            return [
                'success' => true,
                'message' => 'Equipment labeled successfully.',
                'photo_path' => $relativePath,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error labeling equipment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Remove equipment from calibration schedule
     *
     * @param Equipment $equipment
     * @return int Number of schedule entries cancelled
     */
    public function removeFromCalibrationSchedule(Equipment $equipment): int
    {
        // Find future scheduled calibrations
        $futureCalibrations = MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'calibration')
            ->whereDate('date', '>', now())
            ->get();

        $count = $futureCalibrations->count();

        // Mark them as cancelled or delete them
        foreach ($futureCalibrations as $log) {
            // Option 1: Delete
            // $log->delete();
            
            // Option 2: Mark as cancelled (if you have a status field)
            $log->notes = '[CANCELLED - Equipment Disposal] ' . ($log->notes ?? '');
            $log->save();
        }

        return $count;
    }

    /**
     * Remove equipment from maintenance schedule
     *
     * @param Equipment $equipment
     * @return int Number of schedule entries cancelled
     */
    public function removeFromMaintenanceSchedule(Equipment $equipment): int
    {
        // Find future scheduled maintenance
        $futureMaintenance = MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'maintainance')
            ->whereDate('date', '>', now())
            ->get();

        $count = $futureMaintenance->count();

        // Mark them as cancelled
        foreach ($futureMaintenance as $log) {
            $log->notes = '[CANCELLED - Equipment Disposal] ' . ($log->notes ?? '');
            $log->save();
        }

        return $count;
    }

    /**
     * Check data security compliance for electronic equipment
     *
     * @param Equipment $equipment
     * @return array
     */
    public function checkDataSecurityCompliance(Equipment $equipment): array
    {
        if (!$equipment->isElectronic()) {
            return [
                'required' => false,
                'compliant' => true,
                'message' => 'Not applicable - equipment is not electronic',
            ];
        }

        // Check if all data security items are completed
        // This would typically be done through the checklist
        return [
            'required' => true,
            'compliant' => false, // Will be true once checklist is complete
            'items' => [
                'Data wiping required',
                'Storage device removal required',
                'Factory reset required',
            ],
        ];
    }

    /**
     * Complete decommissioning process
     *
     * @param EquipmentDisposal $disposal
     * @param array $checklist
     * @return array
     */
    public function completeDecommissioning(EquipmentDisposal $disposal, array $checklist): array
    {
        DB::beginTransaction();

        try {
            $equipment = $disposal->equipment;

            // Verify all required items are completed
            foreach ($checklist as $key => $item) {
                if ($item['required'] && !$item['completed']) {
                    return [
                        'success' => false,
                        'message' => "Cannot complete: {$item['label']} is not completed.",
                    ];
                }
            }

            // Update disposal record
            $disposal->decommissioning_checklist_json = $checklist;
            $disposal->equipment_labeled = true;
            $disposal->removed_from_calibration_schedule = true;
            $disposal->removed_from_maintenance_schedule = true;
            $disposal->utilities_disconnected = true;
            
            if ($equipment->isElectronic()) {
                $disposal->data_wiped = true;
                $disposal->storage_devices_removed = true;
            }
            
            $disposal->save();

            // Remove from schedules
            $calibrationCount = $this->removeFromCalibrationSchedule($equipment);
            $maintenanceCount = $this->removeFromMaintenanceSchedule($equipment);

            // Update equipment status
            $equipment->status = 'DECOMMISSIONED';
            $equipment->save();

            // Log completion
            $this->auditService->logAction(
                $disposal,
                'decommissioning_completed',
                null,
                [
                    'calibration_schedules_cancelled' => $calibrationCount,
                    'maintenance_schedules_cancelled' => $maintenanceCount,
                ],
                "Decommissioning completed. {$calibrationCount} calibration and {$maintenanceCount} maintenance schedules cancelled."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Decommissioning completed successfully.',
                'calibration_schedules_cancelled' => $calibrationCount,
                'maintenance_schedules_cancelled' => $maintenanceCount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error completing decommissioning: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Revert decommissioning if disposal is cancelled
     *
     * @param EquipmentDisposal $disposal
     * @return array
     */
    public function revertDecommissioning(EquipmentDisposal $disposal): array
    {
        DB::beginTransaction();

        try {
            $equipment = $disposal->equipment;

            // Revert equipment status
            $equipment->status = 'IN SERVICE';
            $equipment->active = 1;
            $equipment->save();

            // Clear decommissioning data
            $disposal->decommissioning_checklist_json = null;
            $disposal->equipment_labeled = false;
            $disposal->removed_from_calibration_schedule = false;
            $disposal->removed_from_maintenance_schedule = false;
            $disposal->utilities_disconnected = false;
            $disposal->data_wiped = false;
            $disposal->storage_devices_removed = false;
            $disposal->save();

            // Log reversion
            $this->auditService->logAction(
                $disposal,
                'decommissioning_reverted',
                ['status' => 'DECOMMISSIONED'],
                ['status' => 'IN SERVICE'],
                "Decommissioning reverted - equipment returned to service"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Decommissioning reverted successfully. Equipment returned to service.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error reverting decommissioning: ' . $e->getMessage(),
            ];
        }
    }
}

