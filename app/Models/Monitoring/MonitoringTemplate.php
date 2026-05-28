<?php

namespace App\Models\Monitoring;

use App\Lab;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class MonitoringTemplate extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'document_control_number',
        'version',
        'effective_date',
        'review_date',
        'department',
        'monitoring_category',
        'approval_workflow',
        'status',
        'is_active',
        'lab_id',
        'parent_template_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'approval_workflow' => 'array',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'review_date' => 'date',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_template_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(MonitoringTemplateField::class, 'template_id')->orderBy('sort_order');
    }

    public function formulaRules(): HasMany
    {
        return $this->hasMany(MonitoringFormulaRule::class, 'template_id');
    }

    public function readingSteps(): HasMany
    {
        return $this->hasMany(MonitoringReadingStep::class, 'template_id')->orderBy('step_number');
    }

    public function configuredFields(): HasMany
    {
        return $this->hasMany(MonitoringTemplateConfiguredField::class, 'template_id')
            ->orderBy('placement')
            ->orderBy('order');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MonitoringLog::class, 'template_id');
    }

    /**
     * Lab IDs this template applies to (multi-lab support via scope metadata).
     *
     * @return list<string>
     */
    public function scopeLabIds(): array
    {
        $metaField = $this->relationLoaded('fields')
            ? $this->fields->firstWhere('field_key', '__meta_scope_items')
            : $this->fields()->where('field_key', '__meta_scope_items')->first();

        $labs = Arr::get($metaField?->field_config ?? [], 'labs', []);

        if (is_array($labs) && $labs !== []) {
            return array_values(array_unique(array_map(
                fn ($id) => (string) $id,
                $labs,
            )));
        }

        return $this->lab_id !== null ? [(string) $this->lab_id] : [];
    }

    public function appliesToLab(?string $labId): bool
    {
        if ($labId === null || $labId === '') {
            return true;
        }

        $scopeLabIds = $this->scopeLabIds();

        if ($scopeLabIds !== []) {
            return in_array((string) $labId, $scopeLabIds, true);
        }

        return $this->lab_id === null || (string) $this->lab_id === (string) $labId;
    }
}
