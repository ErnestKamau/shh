<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditChecklistItem extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'audit_checklist_items';

    protected $fillable = [
        'audit_checklist_id',
        'item_number',
        'iso_clause',
        'requirement',
        'guidance',
        'evidence_required',
        'order_index',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(AuditChecklist::class, 'audit_checklist_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AuditChecklistItemResponse::class, 'audit_checklist_item_id');
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
