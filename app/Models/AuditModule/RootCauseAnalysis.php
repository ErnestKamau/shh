<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RootCauseAnalysis extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'root_cause_analyses';

    protected $fillable = [
        'non_conformance_id',
        'root_cause_method_id',
        'method_name',
        'analysis_data',
        'root_cause_description',
        'contributing_factors',
        'evidence_supporting_rca',
        'status_id',
        'status_name',
        'approved_by',
        'approved_date',
        'approval_comments',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'analysis_data' => 'array',
        'approved_date' => 'date',
    ];

    // Relationships
    public function nonConformance(): BelongsTo
    {
        return $this->belongsTo(NonConformance::class, 'non_conformance_id');
    }

    public function rootCauseMethod(): BelongsTo
    {
        return $this->belongsTo(RootCauseMethod::class, 'root_cause_method_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(RcaStatus::class, 'status_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AuditAttachment::class, 'attachable');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', 'DRAFT'));
    }

    public function scopeSubmitted($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', 'SUBMIT'));
    }

    public function scopeApproved($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', 'APPROVE'));
    }

    // Helper Methods
    public function isApproved(): bool
    {
        return $this->status && $this->status->code === 'APPROVE';
    }
}
