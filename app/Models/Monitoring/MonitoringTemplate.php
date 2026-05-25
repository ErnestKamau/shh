<?php

namespace App\Models\Monitoring;

use App\Lab;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
