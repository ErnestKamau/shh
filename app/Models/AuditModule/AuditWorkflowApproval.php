<?php

namespace App\Models\AuditModule;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditWorkflowApproval extends Model
{
    protected $table = 'audit_workflow_approvals';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'workflow_step',
        'from_status',
        'to_status',
        'approver_id',
        'approver_name',
        'role_type',
        'iso_role',
        'remarks',
        'approved_at',
        'company_id',
    ];

    protected $casts = [
        'workflow_step' => 'integer',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Scopes
    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where('company_id', $companyId);
    }

    public function scopeForWorkflowStep($query, int $step)
    {
        return $query->where('workflow_step', $step);
    }

    public function scopeApprovers($query)
    {
        return $query->where('role_type', 'approver');
    }

    public function scopeVerifiers($query)
    {
        return $query->where('role_type', 'verifier');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('approved_at', 'asc')->orderBy('created_at', 'asc');
    }

    // Helper Methods
    public function isApprover(): bool
    {
        return $this->role_type === 'approver';
    }

    public function isVerifier(): bool
    {
        return $this->role_type === 'verifier';
    }
}
