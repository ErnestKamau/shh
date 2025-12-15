<?php

namespace App\Services\Equipment;

use App\Models\Equipments\EquipmentDisposal;
use App\Models\Equipments\EquipmentDisposalAuditLog;
use Illuminate\Support\Facades\Auth;

class DisposalAuditService
{
    /**
     * Log an action for a disposal
     *
     * @param EquipmentDisposal $disposal
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @return EquipmentDisposalAuditLog
     */
    public function logAction(
        EquipmentDisposal $disposal,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): EquipmentDisposalAuditLog {
        return EquipmentDisposalAuditLog::log(
            $disposal,
            $action,
            $oldValues,
            $newValues,
            $description
        );
    }

    /**
     * Log disposal creation
     *
     * @param EquipmentDisposal $disposal
     * @return EquipmentDisposalAuditLog
     */
    public function logCreation(EquipmentDisposal $disposal): EquipmentDisposalAuditLog
    {
        return $this->logAction(
            $disposal,
            'created',
            null,
            $disposal->toArray(),
            "Disposal request created for equipment: {$disposal->equipment->name} ({$disposal->equipment->equipment_number})"
        );
    }

    /**
     * Log disposal update
     *
     * @param EquipmentDisposal $disposal
     * @param array $oldValues
     * @param array $newValues
     * @return EquipmentDisposalAuditLog
     */
    public function logUpdate(
        EquipmentDisposal $disposal,
        array $oldValues,
        array $newValues
    ): EquipmentDisposalAuditLog {
        $changes = [];
        foreach ($newValues as $key => $newValue) {
            if (!isset($oldValues[$key]) || $oldValues[$key] != $newValue) {
                $changes[$key] = [
                    'old' => $oldValues[$key] ?? null,
                    'new' => $newValue,
                ];
            }
        }

        $description = 'Disposal request updated. Changed fields: ' . implode(', ', array_keys($changes));

        return $this->logAction(
            $disposal,
            'updated',
            $oldValues,
            $newValues,
            $description
        );
    }

    /**
     * Log file upload
     *
     * @param EquipmentDisposal $disposal
     * @param string $fileName
     * @param string $fileType
     * @return EquipmentDisposalAuditLog
     */
    public function logFileUpload(
        EquipmentDisposal $disposal,
        string $fileName,
        string $fileType
    ): EquipmentDisposalAuditLog {
        return $this->logAction(
            $disposal,
            'file_uploaded',
            null,
            ['file_name' => $fileName, 'file_type' => $fileType],
            "File uploaded: {$fileName} (Type: {$fileType})"
        );
    }

    /**
     * Log file deletion
     *
     * @param EquipmentDisposal $disposal
     * @param string $fileName
     * @return EquipmentDisposalAuditLog
     */
    public function logFileDeletion(
        EquipmentDisposal $disposal,
        string $fileName
    ): EquipmentDisposalAuditLog {
        return $this->logAction(
            $disposal,
            'file_deleted',
            ['file_name' => $fileName],
            null,
            "File deleted: {$fileName}"
        );
    }

    /**
     * Log signature addition
     *
     * @param EquipmentDisposal $disposal
     * @param int $step
     * @return EquipmentDisposalAuditLog
     */
    public function logSignatureAdded(
        EquipmentDisposal $disposal,
        int $step
    ): EquipmentDisposalAuditLog {
        return $this->logAction(
            $disposal,
            'signature_added',
            null,
            ['approval_step' => $step],
            "Digital signature added for approval step {$step}"
        );
    }

    /**
     * Log disposal execution
     *
     * @param EquipmentDisposal $disposal
     * @param array $executionDetails
     * @return EquipmentDisposalAuditLog
     */
    public function logExecution(
        EquipmentDisposal $disposal,
        array $executionDetails
    ): EquipmentDisposalAuditLog {
        return $this->logAction(
            $disposal,
            'executed',
            ['status' => $disposal->getOriginal('status')],
            array_merge(['status' => 'executed'], $executionDetails),
            "Disposal executed. Method: {$executionDetails['final_disposal_method']}, Date: {$executionDetails['disposal_date']}"
        );
    }

    /**
     * Log disposal closure
     *
     * @param EquipmentDisposal $disposal
     * @return EquipmentDisposalAuditLog
     */
    public function logClosure(EquipmentDisposal $disposal): EquipmentDisposalAuditLog
    {
        return $this->logAction(
            $disposal,
            'closed',
            ['status' => $disposal->getOriginal('status')],
            ['status' => 'closed'],
            "Disposal record closed and locked."
        );
    }

    /**
     * Get audit trail for a disposal
     *
     * @param EquipmentDisposal $disposal
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAuditTrail(EquipmentDisposal $disposal)
    {
        return $disposal->auditLogs()->with('user')->get();
    }
}

