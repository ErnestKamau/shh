<?php

namespace App\Models\Registry;

use App\Models\Registry\Concerns\ScopesByCompany;
use App\User;
use Database\Factories\Registry\RegistryRequestFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistryRequest extends Model
{
    /** @use HasFactory<RegistryRequestFactory> */
    use HasFactory;
    use HasUuids;
    use ScopesByCompany;
    use SoftDeletes;

    protected static function newFactory(): RegistryRequestFactory
    {
        return RegistryRequestFactory::new();
    }

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference_no',
        'request_category_id',
        'workflow_definition_id',
        'subject',
        'description',
        'entity_type',
        'entity_id',
        'metadata',
        'priority',
        'direction',
        'current_stage',
        'status',
        'submitting_party',
        'submitted_by',
        'assigned_to',
        'received_by',
        'received_at',
        'closed_at',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'received_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RegistryRequestCategory::class, 'request_category_id');
    }

    public function workflowDefinition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(RegistryRequestAction::class)->orderByDesc('performed_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RegistryRequestAssignment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RegistryRequestDocument::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RegistryRequestStatusLog::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CLOSED, self::STATUS_CANCELLED]);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, self::STATUS_CANCELLED], true);
    }
}
