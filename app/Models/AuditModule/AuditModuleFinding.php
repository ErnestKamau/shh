<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AuditModuleFinding extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'audit_module_findings';

    protected $fillable = [
        'finding_number',
        'audit_id',
        'finding_category_id',
        'finding_category_name',
        'iso_clause',
        'sop_reference',
        'requirement',
        'observation',
        'objective_evidence',
        'risk_level_id',
        'risk_level_name',
        'responsible_person',
        'responsible_user_id',
        'response_due_date',
        'status_id',
        'status_name',
        'order_index',
        'created_by',
    ];

    protected $casts = [
        'response_due_date' => 'date',
        'order_index' => 'integer',
    ];

    // Relationships
    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function findingCategory(): BelongsTo
    {
        return $this->belongsTo(FindingCategory::class, 'finding_category_id');
    }

    public function riskLevel(): BelongsTo
    {
        return $this->belongsTo(RiskLevel::class, 'risk_level_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(FindingStatus::class, 'status_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nonConformance(): HasOne
    {
        return $this->hasOne(NonConformance::class, 'audit_finding_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AuditAttachment::class, 'attachable');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', 'OPEN'));
    }

    public function scopeClosed($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', 'CLOSED'));
    }

    // Helper Methods
    public static function generateFindingNumber(int $auditId): string
    {
        $audit = Audit::find($auditId);
        $count = static::where('audit_id', $auditId)->count() + 1;
        return $audit ? $audit->audit_number . '-F' . str_pad($count, 2, '0', STR_PAD_LEFT) : 'F-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function requiresCapa(): bool
    {
        return $this->findingCategory && $this->findingCategory->requires_capa;
    }

    public function hasNonConformance(): bool
    {
        return $this->nonConformance()->exists();
    }
}







