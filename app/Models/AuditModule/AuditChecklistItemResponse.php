<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditChecklistItemResponse extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'audit_checklist_item_responses';

    protected $fillable = [
        'audit_id',
        'audit_checklist_item_id',
        'compliance_status',
        'audit_question',
        'evidence_collected',
        'observation',
        'findings',
        'auditor_notes',
        'audited_by',
        'audited_at',
        'requires_follow_up',
        'follow_up_notes',
        'company_id',
    ];

    protected $casts = [
        'audited_at' => 'datetime',
        'requires_follow_up' => 'boolean',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(AuditChecklistItem::class, 'audit_checklist_item_id');
    }

    public function auditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where('company_id', $companyId);
    }

    public function scopeCompliant($query)
    {
        return $query->where('compliance_status', 'compliant');
    }

    public function scopeNonCompliant($query)
    {
        return $query->where('compliance_status', 'non_compliant');
    }

    public function scopePending($query)
    {
        return $query->where('compliance_status', 'pending');
    }

    public function getComplianceStatusBadgeClassAttribute(): string
    {
        return match($this->compliance_status) {
            'compliant' => 'success',
            'non_compliant' => 'danger',
            'observation' => 'warning',
            'not_applicable' => 'secondary',
            'pending' => 'info',
            default => 'secondary',
        };
    }
}
