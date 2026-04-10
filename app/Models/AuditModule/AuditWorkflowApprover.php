<?php

namespace App\Models\AuditModule;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditWorkflowApprover extends Model
{
    use SoftDeletes;

    protected $table = 'audit_workflow_approvers';

    protected $fillable = [
        'workflow_step',
        'role_type',
        'user_id',
        'iso_role',
        'is_required',
        'approval_type',
        'module',
        'company_id',
    ];

    protected $casts = [
        'workflow_step' => 'integer',
        'is_required' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }


    public function scopeApprovers($query)
    {
        return $query->where('role_type', 'approver');
    }

    public function scopeVerifiers($query)
    {
        return $query->where('role_type', 'verifier');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // Helper Methods
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    public function isApprover(): bool
    {
        return $this->role_type === 'approver';
    }

    public function isVerifier(): bool
    {
        return $this->role_type === 'verifier';
    }
}
