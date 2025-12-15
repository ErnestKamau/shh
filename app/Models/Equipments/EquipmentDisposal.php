<?php

namespace App\Models\Equipments;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentDisposal extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'equipment_id',
        'evaluation_id',
        'justification',
        'proposed_method',
        'risk_level',
        'regulatory_category',
        'requested_by',
        'status',
        'final_disposal_method',
        'disposal_date',
        'executed_by',
        'witness_id',
        'compliance_checklist_json',
        'decommissioning_checklist_json',
        'decommissioning_date',
        'decommissioned_by',
        'equipment_labeled',
        'label_photo_path',
        'removed_from_calibration_schedule',
        'removed_from_maintenance_schedule',
        'utilities_disconnected',
        'data_wiped',
        'storage_devices_removed',
        'transport_company',
        'transport_details',
        'waste_handler_company',
        'waste_handler_license',
        'disposal_certificate_path',
        'sha_hash',
        'company_id',
        'pdf_report_path',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'decommissioning_date' => 'date',
        'compliance_checklist_json' => 'array',
        'decommissioning_checklist_json' => 'array',
        'equipment_labeled' => 'boolean',
        'removed_from_calibration_schedule' => 'boolean',
        'removed_from_maintenance_schedule' => 'boolean',
        'utilities_disconnected' => 'boolean',
        'data_wiped' => 'boolean',
        'storage_devices_removed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the equipment this disposal belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the user who requested the disposal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who executed the disposal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    /**
     * Get the witness user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_id');
    }

    /**
     * Get all files attached to this disposal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(EquipmentDisposalFile::class, 'disposal_id');
    }

    /**
     * Get all approvals for this disposal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(EquipmentDisposalApproval::class, 'disposal_id')->orderBy('step');
    }

    /**
     * Get all audit logs for this disposal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(EquipmentDisposalAuditLog::class, 'disposal_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the evaluation report for this disposal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(EquipmentEvaluation::class, 'evaluation_id');
    }

    /**
     * Get the user who decommissioned the equipment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function decommissioner(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'decommissioned_by');
    }

    /**
     * Check if disposal is locked (executed or closed)
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return in_array($this->status, ['executed', 'closed']);
    }

    /**
     * Check if disposal can be edited
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Get the current approval step
     *
     * @return int
     */
    public function getCurrentApprovalStep(): int
    {
        $lastApproval = $this->approvals()->whereNotNull('decision')->orderBy('step', 'desc')->first();
        return $lastApproval ? $lastApproval->step + 1 : 1;
    }

    /**
     * Scope to filter by status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by company
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $companyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

