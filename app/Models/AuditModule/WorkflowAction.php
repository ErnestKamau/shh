<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditConfigurationForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowAction extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditConfigurationForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'workflow_actions';

    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'color_code',
        'badge_class',
        'requires_remarks',
        'min_remarks_length',
        'requires_target_status',
        'is_active',
        'order_index',
        'company_id',
    ];

    protected $casts = [
        'requires_remarks' => 'boolean',
        'requires_target_status' => 'boolean',
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'min_remarks_length' => 'integer',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(WorkflowActionRule::class, 'workflow_action_id');
    }

    public function activeRules(): HasMany
    {
        return $this->rules()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index', 'asc');
    }
}
