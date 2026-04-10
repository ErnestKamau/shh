<?php

namespace App\Models\AuditModule;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AuditChecklist extends Model
{
    use SoftDeletes;

    protected $table = 'audit_checklists';

    protected $fillable = [
        'name',
        'code',
        'description',
        'audit_type_id',
        'iso_standard',
        'is_active',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function auditType(): BelongsTo
    {
        return $this->belongsTo(AuditType::class, 'audit_type_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AuditChecklistItem::class, 'audit_checklist_id')->orderBy('order_index');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'checklist_id');
    }

    public function auditsMany(): BelongsToMany
    {
        return $this->belongsToMany(Audit::class, 'audit_checklist_audit', 'audit_checklist_id', 'audit_id')
            ->withPivot('order_index')
            ->withTimestamps()
            ->orderByPivot('order_index');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where('company_id', $companyId);
    }
}
