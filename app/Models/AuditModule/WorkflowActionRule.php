<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowActionRule extends Model
{
    use SoftDeletes;

    protected $table = 'workflow_action_rules';

    protected $fillable = [
        'workflow_action_id',
        'from_status_id',
        'from_status_name',
        'target_status_id',
        'target_status_name',
        'target_type',
        'validate_progression',
        'validation_rules',
        'conditions',
        'success_message',
        'error_message',
        'is_active',
        'order_index',
        'company_id',
    ];

    protected $casts = [
        'validate_progression' => 'boolean',
        'is_active' => 'boolean',
        'validation_rules' => 'array',
        'conditions' => 'array',
        'order_index' => 'integer',
    ];

    public function workflowAction(): BelongsTo
    {
        return $this->belongsTo(WorkflowAction::class, 'workflow_action_id');
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(AuditStatus::class, 'from_status_id');
    }

    public function targetStatus(): BelongsTo
    {
        return $this->belongsTo(AuditStatus::class, 'target_status_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(WorkflowActionNotification::class, 'workflow_action_rule_id');
    }

    public function activeNotifications(): HasMany
    {
        return $this->notifications()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhere('company_id', 0);
        });
    }

    public function scopeForStatus($query, $statusId = null, $statusName = null)
    {
        if ($statusId) {
            return $query->where('from_status_id', $statusId);
        }
        if ($statusName) {
            return $query->where('from_status_name', $statusName);
        }
        return $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index', 'asc');
    }
}
