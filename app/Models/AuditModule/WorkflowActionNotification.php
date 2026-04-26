<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowActionNotification extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'workflow_action_notifications';

    protected $fillable = [
        'workflow_action_rule_id',
        'notification_type',
        'recipient_type',
        'user_id',
        'role_name',
        'department_id',
        'custom_email',
        'custom_phone',
        'send_email',
        'send_sms',
        'email_template',
        'sms_template',
        'template_variables',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_sms' => 'boolean',
        'is_active' => 'boolean',
        'template_variables' => 'array',
    ];

    public function workflowActionRule(): BelongsTo
    {
        return $this->belongsTo(WorkflowActionRule::class, 'workflow_action_rule_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id');
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
}
