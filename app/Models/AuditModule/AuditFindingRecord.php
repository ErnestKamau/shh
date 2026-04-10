<?php

namespace App\Models\AuditModule;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AuditFindingRecord extends Model
{
    use SoftDeletes;

    protected $table = 'audit_finding_records';

    protected $fillable = [
        'audit_id',
        'finding_number',
        'finding_category_id',
        'clause_reference',
        'description',
        'requirement',
        'objective_evidence',
        'risk_level_id',
        'risk_justification',
        'responsible_user_id',
        'responsible_party',
        'response_due_date',
        'status',
        'checklist_item_id',
        'checklist_response',
        'notes',
        'order_index',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'response_due_date' => 'date',
            'order_index' => 'integer',
        ];
    }

    // Relationships
    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FindingCategory::class, 'finding_category_id');
    }

    public function riskLevel(): BelongsTo
    {
        return $this->belongsTo(RiskLevel::class, 'risk_level_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(AuditChecklistItem::class, 'checklist_item_id');
    }

    public function createdBy(): BelongsTo
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

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(AuditActivityLog::class, 'loggable');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'Open');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'In Review');
    }

    public function scopeNcRaised($query)
    {
        return $query->where('status', 'NC Raised');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Closed');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNonConformities($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('requires_capa', true);
        });
    }

    // Helpers
    public function requiresCapa(): bool
    {
        return $this->category && $this->category->requires_capa;
    }

    public function hasNonConformance(): bool
    {
        return $this->nonConformance()->exists();
    }

    public function isOverdue(): bool
    {
        if (!$this->response_due_date) {
            return false;
        }
        return $this->response_due_date->isPast() && $this->status !== 'Closed';
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? 'Uncategorized';
    }

    public function getRiskLevelNameAttribute(): string
    {
        return $this->riskLevel?->name ?? 'Not Assessed';
    }
}

