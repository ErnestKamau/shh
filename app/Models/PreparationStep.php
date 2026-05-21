<?php

namespace App\Models;

use App\LabCategoryItems;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class PreparationStep extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'preparation_steps';

    protected $fillable = [
        'preparation_id',
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
        'quantity_used',
        'uom_id',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'selected_analytes' => 'array',
            'analyte_result_types' => 'array',
            'completed_at' => 'datetime',
            'quantity_used' => 'decimal:4',
        ];
    }

    public function preparation(): BelongsTo
    {
        return $this->belongsTo(SolutionPreparation::class, 'preparation_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(LabCategoryItems::class, 'ingredient_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function controls(): HasMany
    {
        return $this->hasMany(PreparationStepControl::class, 'preparation_step_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(PreparationStepResult::class, 'preparation_step_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PreparationStepMedia::class, 'preparation_step_id');
    }

    public function diluents(): HasMany
    {
        return $this->hasMany(PreparationStepDiluent::class, 'preparation_step_id');
    }

    public function isAnalysisStep(): bool
    {
        return $this->step_type === SolutionPreparationStepTemplate::STEP_TYPE_ANALYSIS;
    }

    public function isRegularStep(): bool
    {
        return $this->step_type === SolutionPreparationStepTemplate::STEP_TYPE_REGULAR;
    }

    public function isCompleted(): bool
    {
        if ($this->isRegularStep()) {
            return $this->completed_at !== null;
        }

        return count($this->getCompletionBlockers()) === 0;
    }

    /**
     * @return list<string>
     */
    public function getCompletionBlockers(): array
    {
        if ($this->isRegularStep()) {
            return $this->completed_at ? [] : ['Step not marked complete.'];
        }

        $blockers = [];
        $analyteIds = $this->selected_analytes ?? [];

        foreach ($analyteIds as $analyteId) {
            $hasSample = $this->results()
                ->where('analyte_id', $analyteId)
                ->where('is_control', false)
                ->whereNotNull('result')
                ->where('result', '!=', '')
                ->exists();

            if (! $hasSample) {
                $blockers[] = "Missing sample result for analyte {$analyteId}.";
            }
        }

        $controls = $this->relationLoaded('controls') ? $this->controls : $this->controls()->get();
        foreach ($controls as $control) {
            foreach ($analyteIds as $analyteId) {
                $hasControl = $this->results()
                    ->where('analyte_id', $analyteId)
                    ->where('is_control', true)
                    ->where('control_solution_id', $control->control_solution_id)
                    ->whereNotNull('result')
                    ->where('result', '!=', '')
                    ->exists();

                if (! $hasControl) {
                    $blockers[] = "Missing control result for analyte {$analyteId} (control {$control->control_solution_id}).";
                }
            }
        }

        if ($this->media()->exists()) {
            $preparation = $this->preparation;
            foreach ($this->media as $mediaRow) {
                $hasInoculated = $preparation->inoculatedMedia()
                    ->where(function ($q) use ($mediaRow) {
                        $q->where('lab_category_item_id', $mediaRow->lab_category_item_id)
                            ->orWhere('media_id', $mediaRow->lab_category_item_id);
                    })
                    ->whereNotNull('result')
                    ->where('result', '!=', '')
                    ->exists();

                if (! $hasInoculated) {
                    $blockers[] = 'Missing inoculated media result for configured media ingredient.';
                }
            }
        }

        return $blockers;
    }

    public function markCompleted(?string $userId = null): void
    {
        $this->update([
            'completed_at' => now(),
            'completed_by' => $userId ?? auth()->id(),
        ]);
    }

    public function uncomplete(): void
    {
        $this->update([
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }
}
