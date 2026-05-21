<?php

namespace App\Services\Preparation;

use App\LabCategoryItems;
use App\LabSubCategory;
use App\Models\PreparationStep;
use App\Models\PreparationStepControl;
use App\Models\SolutionPreparation;
use App\Models\SolutionPreparationStepTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreparationTemplateService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{control_solution_id: string, label?: string|null}>  $controls
     */
    public function addTemplate(string $solutionId, array $data, array $controls = []): SolutionPreparationStepTemplate
    {
        $ingredientId = $this->resolveTemplateIngredientForForm($solutionId, $data);

        return DB::transaction(function () use ($solutionId, $data, $controls, $ingredientId) {
            $template = SolutionPreparationStepTemplate::create([
                'lab_sub_category_id' => $solutionId,
                'step_number' => $data['step_number'],
                'step_name' => $data['step_name'],
                'step_type' => $data['step_type'],
                'ingredient_id' => $ingredientId,
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'sample_type_id' => $data['sample_type_id'] ?? null,
                'analysis_type_id' => $data['analysis_type_id'] ?? null,
                'selected_analytes' => $data['selected_analytes'] ?? [],
                'result_type' => $data['result_type'] ?? null,
                'analyte_result_types' => $data['analyte_result_types'] ?? [],
                'standard_id' => $data['standard_id'] ?? null,
            ]);

            if ($template->isAnalysisStep()) {
                $template->syncTemplateControls($controls);
            }

            $this->propagateNewTemplateToOpenPreparations($template);

            return $template->fresh(['controls']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{control_solution_id: string, label?: string|null}>  $controls
     */
    public function updateTemplate(SolutionPreparationStepTemplate $template, array $data, array $controls = [], ?string $oldStepName = null): SolutionPreparationStepTemplate
    {
        $ingredientId = $this->resolveTemplateIngredientForForm($template->lab_sub_category_id, $data);
        $oldName = $oldStepName ?? $template->step_name;
        $oldNumber = $template->step_number;

        return DB::transaction(function () use ($template, $data, $controls, $ingredientId, $oldName, $oldNumber) {
            $template->update([
                'step_number' => $data['step_number'],
                'step_name' => $data['step_name'],
                'step_type' => $data['step_type'],
                'ingredient_id' => $ingredientId,
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'sample_type_id' => $data['sample_type_id'] ?? null,
                'analysis_type_id' => $data['analysis_type_id'] ?? null,
                'selected_analytes' => $data['selected_analytes'] ?? [],
                'result_type' => $data['result_type'] ?? null,
                'analyte_result_types' => $data['analyte_result_types'] ?? [],
                'standard_id' => $data['standard_id'] ?? null,
            ]);

            if ($template->isAnalysisStep()) {
                $template->syncTemplateControls($controls);
            } else {
                $template->controls()->delete();
            }

            $this->syncMatchingInProgressSteps($template, $oldName, $oldNumber);

            return $template->fresh(['controls']);
        });
    }

    public function deleteTemplate(SolutionPreparationStepTemplate $template, bool $deleteFromPreparations = false): void
    {
        DB::transaction(function () use ($template, $deleteFromPreparations) {
            if ($deleteFromPreparations) {
                $preparations = SolutionPreparation::query()
                    ->where('solution_id', $template->lab_sub_category_id)
                    ->where('status', 'preparing')
                    ->get();

                foreach ($preparations as $preparation) {
                    $steps = $preparation->steps()->where('step_name', $template->step_name)->get();
                    foreach ($steps as $step) {
                        $step->results()->delete();
                        $step->controls()->delete();
                        $step->delete();
                    }
                    $this->renumberPreparationSteps($preparation);
                }
            }

            $template->controls()->delete();
            $template->delete();

            $this->renumberTemplates($template->lab_sub_category_id);
        });
    }

    public function cloneSolution(LabSubCategory $source, string $newName, ?float $scaleFactor = null): LabSubCategory
    {
        return DB::transaction(function () use ($source, $newName, $scaleFactor) {
            $clone = $source->replicate(['stock', 'current_batch_number', 'batch_prepared_date', 'batch_expiry_date', 'batch_status']);
            $clone->name = $newName;
            $clone->stock = 0;
            $clone->current_batch_number = null;
            $clone->batch_prepared_date = null;
            $clone->batch_expiry_date = null;
            $clone->alternative_solution_id = null;
            $clone->save();

            $ingredientMap = [];
            foreach ($source->categoryItems as $item) {
                $newItem = $item->replicate();
                $newItem->sub_category_id = $clone->id;
                if ($scaleFactor !== null) {
                    $newItem->amount_used = (float) $item->amount_used * $scaleFactor;
                }
                $newItem->save();
                $ingredientMap[$item->id] = $newItem->id;
            }

            foreach ($source->templates()->with('controls')->orderBy('step_number')->get() as $template) {
                $newTemplate = $template->replicate();
                $newTemplate->lab_sub_category_id = $clone->id;
                if ($newTemplate->ingredient_id && isset($ingredientMap[$newTemplate->ingredient_id])) {
                    $newTemplate->ingredient_id = $ingredientMap[$newTemplate->ingredient_id];
                }
                $newTemplate->save();

                foreach ($template->controls as $control) {
                    $newTemplate->controls()->create([
                        'control_solution_id' => $control->control_solution_id,
                        'label' => $control->label,
                    ]);
                }
            }

            return $clone;
        });
    }

    public function setAlternativeSolution(LabSubCategory $solution, ?string $alternativeId): void
    {
        if ($alternativeId) {
            $alt = LabSubCategory::findOrFail($alternativeId);
            if ($alt->category_id !== $solution->category_id) {
                throw ValidationException::withMessages([
                    'alternative_solution_id' => 'Alternative must be in the same category.',
                ]);
            }
        }

        $solution->alternative_solution_id = $alternativeId;
        $solution->save();
    }

    public function isIngredientUsedInTemplates(string $ingredientId): bool
    {
        return SolutionPreparationStepTemplate::where('ingredient_id', $ingredientId)->exists();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function resolveTemplateIngredientForForm(string $solutionId, array $data): ?string
    {
        if (($data['step_type'] ?? '') === SolutionPreparationStepTemplate::STEP_TYPE_ANALYSIS) {
            return null;
        }

        $ingredientId = $data['ingredient_id'] ?? null;
        if (! $ingredientId) {
            throw ValidationException::withMessages(['ingredient_id' => 'Ingredient is required for regular steps.']);
        }

        $exists = LabCategoryItems::where('id', $ingredientId)
            ->where('sub_category_id', $solutionId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages(['ingredient_id' => 'Invalid ingredient for this solution.']);
        }

        return $ingredientId;
    }

    protected function propagateNewTemplateToOpenPreparations(SolutionPreparationStepTemplate $template): void
    {
        $preparations = SolutionPreparation::query()
            ->where('solution_id', $template->lab_sub_category_id)
            ->where('status', 'preparing')
            ->get();

        foreach ($preparations as $preparation) {
            if ($preparation->steps()->where('step_name', $template->step_name)->exists()) {
                continue;
            }

            $maxStep = (int) $preparation->steps()->max('step_number');
            $this->copyTemplateToPreparationStep($template, $preparation->id, $maxStep + 1);
            $this->renumberPreparationSteps($preparation);
        }
    }

    protected function syncMatchingInProgressSteps(SolutionPreparationStepTemplate $template, string $oldName, int $oldNumber): void
    {
        $preparations = SolutionPreparation::query()
            ->where('solution_id', $template->lab_sub_category_id)
            ->where('status', 'preparing')
            ->get();

        foreach ($preparations as $preparation) {
            $step = $preparation->steps()
                ->where('step_number', $oldNumber)
                ->where('step_name', $oldName)
                ->first();

            if (! $step) {
                continue;
            }

            $step->update([
                'step_number' => $template->step_number,
                'step_name' => $template->step_name,
                'step_type' => $template->step_type,
                'ingredient_id' => $template->ingredient_id,
                'description' => $template->description,
                'notes' => $template->notes,
                'sample_type_id' => $template->sample_type_id,
                'analysis_type_id' => $template->analysis_type_id,
                'selected_analytes' => $template->selected_analytes,
                'result_type' => $template->result_type,
                'analyte_result_types' => $template->analyte_result_types,
                'standard_id' => $template->standard_id,
            ]);

            if ($template->isAnalysisStep() && $step->controls()->count() === 0) {
                foreach ($template->controls as $control) {
                    PreparationStepControl::create([
                        'preparation_step_id' => $step->id,
                        'control_solution_id' => $control->control_solution_id,
                        'label' => $control->label,
                    ]);
                }
            }
        }
    }

    public function copyTemplateToPreparationStep(SolutionPreparationStepTemplate $template, string $preparationId, int $stepNumber): PreparationStep
    {
        $step = PreparationStep::create([
            'preparation_id' => $preparationId,
            'step_number' => $stepNumber,
            'step_name' => $template->step_name,
            'step_type' => $template->step_type,
            'ingredient_id' => $template->ingredient_id,
            'description' => $template->description,
            'notes' => $template->notes,
            'sample_type_id' => $template->sample_type_id,
            'analysis_type_id' => $template->analysis_type_id,
            'selected_analytes' => $template->selected_analytes,
            'result_type' => $template->result_type,
            'analyte_result_types' => $template->analyte_result_types,
            'standard_id' => $template->standard_id,
        ]);

        if ($template->isAnalysisStep()) {
            foreach ($template->controls as $control) {
                PreparationStepControl::create([
                    'preparation_step_id' => $step->id,
                    'control_solution_id' => $control->control_solution_id,
                    'label' => $control->label,
                ]);
            }
        }

        return $step;
    }

    public function renumberPreparationSteps(SolutionPreparation $preparation): void
    {
        $steps = $preparation->steps()->orderBy('step_number')->get();
        $number = 1;
        foreach ($steps as $step) {
            $step->update(['step_number' => $number++]);
        }
    }

    public function renumberTemplates(string $solutionId): void
    {
        $templates = SolutionPreparationStepTemplate::where('lab_sub_category_id', $solutionId)
            ->orderBy('step_number')
            ->get();

        $number = 1;
        foreach ($templates as $template) {
            $template->update(['step_number' => $number++]);
        }
    }
}
