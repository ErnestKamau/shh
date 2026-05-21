<?php

namespace App\Models;

use App\LabCategoryItems;
use App\LabSubCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class SolutionPreparationStepTemplate extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public const STEP_TYPE_REGULAR = 'Regular Step (Ingredient-based)';

    public const STEP_TYPE_ANALYSIS = 'analysis';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'solution_preparation_step_templates';

    protected $fillable = [
        'lab_sub_category_id',
        'step_number',
        'step_name',
        'step_type',
        'ingredient_id',
        'description',
        'notes',
        'sample_type_id',
        'analysis_type_id',
        'selected_analytes',
        'result_type',
        'analyte_result_types',
        'standard_id',
    ];

    protected function casts(): array
    {
        return [
            'selected_analytes' => 'array',
            'analyte_result_types' => 'array',
        ];
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(LabSubCategory::class, 'lab_sub_category_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(LabCategoryItems::class, 'ingredient_id');
    }

    public function controls(): HasMany
    {
        return $this->hasMany(SolutionPreparationStepTemplateControl::class, 'template_step_id');
    }

    public function isAnalysisStep(): bool
    {
        return $this->step_type === self::STEP_TYPE_ANALYSIS;
    }

    public function isRegularStep(): bool
    {
        return $this->step_type === self::STEP_TYPE_REGULAR;
    }

    /**
     * @param  array<int, array{control_solution_id: string, label?: string|null}>  $controls
     */
    public function syncTemplateControls(array $controls): void
    {
        $this->controls()->delete();

        foreach ($controls as $control) {
            if (empty($control['control_solution_id'])) {
                continue;
            }
            $this->controls()->create([
                'control_solution_id' => $control['control_solution_id'],
                'label' => $control['label'] ?? null,
            ]);
        }
    }
}
