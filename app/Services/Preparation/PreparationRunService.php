<?php

namespace App\Services\Preparation;

use App\LabSubCategory;
use App\Models\PreparationInoculatedMedia;
use App\Models\PreparationStep;
use App\Models\PreparationStepControl;
use App\Models\PreparationStepMedia;
use App\Models\PreparationStepResult;
use App\Models\SolutionPreparation;
use App\Models\SolutionPreparationStepTemplate;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreparationRunService
{
    public function __construct(
        protected PreparationTemplateService $templateService,
        protected StockMovementService $stockMovementService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPreparationRecord(array $data, ?string $userId = null): SolutionPreparation
    {
        return DB::transaction(function () use ($data, $userId) {
            $preparation = SolutionPreparation::create([
                'solution_id' => $data['solution_id'],
                'preparation_number' => SolutionPreparation::generatePreparationNumber(),
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'is_new_batch' => (bool) ($data['is_new_batch'] ?? false),
                'prepared_by' => $userId ?? auth()->id(),
                'prepared_at' => now(),
                'status' => 'preparing',
                'notes' => $data['notes'] ?? null,
                'quantity_prepared' => $data['quantity_prepared'] ?? null,
                'uom_id' => $data['uom_id'] ?? null,
                'ingredient_payload' => $data['ingredient_payload'] ?? null,
                'alternative_aware' => (bool) ($data['alternative_aware'] ?? false),
                'source_id' => $data['source_id'] ?? null,
            ]);

            $this->materializePreparationTemplateSteps($preparation);

            return $preparation->fresh(['steps.controls', 'steps.results', 'solution']);
        });
    }

    public function createWithAlternative(array $data, bool $withAlternative = false): SolutionPreparation
    {
        $primary = $this->createPreparationRecord($data);

        if (! $withAlternative) {
            return $primary;
        }

        $solution = LabSubCategory::find($data['solution_id']);
        if (! $solution?->alternative_solution_id) {
            return $primary;
        }

        $altData = $data;
        $altData['solution_id'] = $solution->alternative_solution_id;
        $altData['alternative_aware'] = true;
        $altData['source_id'] = $primary->id;

        $this->createPreparationRecord($altData);

        return $primary;
    }

    public function materializePreparationTemplateSteps(SolutionPreparation $preparation): void
    {
        $templates = SolutionPreparationStepTemplate::query()
            ->where('lab_sub_category_id', $preparation->solution_id)
            ->with('controls')
            ->orderBy('step_number')
            ->get();

        foreach ($templates as $template) {
            $this->templateService->copyTemplateToPreparationStep(
                $template,
                $preparation->id,
                $template->step_number
            );
        }
    }

    public function syncTemplate(SolutionPreparation $preparation): int
    {
        if (! $preparation->isInProgress()) {
            return 0;
        }

        $added = 0;
        $templates = SolutionPreparationStepTemplate::query()
            ->where('lab_sub_category_id', $preparation->solution_id)
            ->with('controls')
            ->orderBy('step_number')
            ->get();

        foreach ($templates as $template) {
            if ($preparation->steps()->where('step_name', $template->step_name)->exists()) {
                continue;
            }

            $maxStep = (int) $preparation->steps()->max('step_number');
            $this->templateService->copyTemplateToPreparationStep($template, $preparation->id, $maxStep + 1);
            $added++;
        }

        if ($added > 0) {
            $this->templateService->renumberPreparationSteps($preparation);
        }

        return $added;
    }

    public function completeStep(PreparationStep $step, ?string $userId = null): void
    {
        if ($step->isAnalysisStep()) {
            $blockers = $step->getCompletionBlockers();
            if (count($blockers) > 0) {
                throw ValidationException::withMessages(['step' => implode(' ', $blockers)]);
            }
        }

        $step->markCompleted($userId);
        $this->moveToAwaitingApprovalIfEligible($step->preparation);
    }

    public function uncompleteStep(PreparationStep $step): void
    {
        $preparation = $step->preparation;
        if (! $preparation->isInProgress()) {
            throw ValidationException::withMessages(['step' => 'Cannot uncomplete steps on a closed preparation.']);
        }

        $step->uncomplete();

        if ($preparation->status === 'awaiting_approval') {
            $preparation->update(['status' => 'preparing']);
        }
    }

    public function deleteStep(PreparationStep $step): void
    {
        $preparation = $step->preparation;
        if (! $preparation->isInProgress()) {
            throw ValidationException::withMessages(['step' => 'Cannot delete steps on a closed preparation.']);
        }

        DB::transaction(function () use ($step, $preparation) {
            $step->results()->delete();
            $step->controls()->delete();
            $step->media()->delete();
            $step->diluents()->delete();
            $step->delete();
            $this->templateService->renumberPreparationSteps($preparation);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addAdHocStep(SolutionPreparation $preparation, array $data, bool $saveToTemplate = false): PreparationStep
    {
        if (! $preparation->isInProgress()) {
            throw ValidationException::withMessages(['preparation' => 'Preparation is not in progress.']);
        }

        return DB::transaction(function () use ($preparation, $data, $saveToTemplate) {
            $maxStep = (int) $preparation->steps()->max('step_number');
            $stepNumber = $maxStep + 1;

            $ingredientId = null;
            if (($data['step_type'] ?? '') === SolutionPreparationStepTemplate::STEP_TYPE_REGULAR) {
                $ingredientId = $this->templateService->resolveTemplateIngredientForForm(
                    $preparation->solution_id,
                    $data
                );
            }

            $step = PreparationStep::create([
                'preparation_id' => $preparation->id,
                'step_number' => $stepNumber,
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

            foreach ($data['controls'] ?? [] as $control) {
                if (! empty($control['control_solution_id'])) {
                    PreparationStepControl::create([
                        'preparation_step_id' => $step->id,
                        'control_solution_id' => $control['control_solution_id'],
                        'label' => $control['label'] ?? null,
                    ]);
                }
            }

            if ($saveToTemplate) {
                $maxTemplate = (int) SolutionPreparationStepTemplate::where('lab_sub_category_id', $preparation->solution_id)->max('step_number');
                $this->templateService->addTemplate($preparation->solution_id, array_merge($data, [
                    'step_number' => $maxTemplate + 1,
                ]), $data['controls'] ?? []);
            }

            return $step;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    public function saveStepResults(PreparationStep $step, array $results): void
    {
        foreach ($results as $row) {
            PreparationStepResult::updateOrCreate(
                [
                    'preparation_step_id' => $step->id,
                    'analyte_id' => $row['analyte_id'] ?? null,
                    'is_control' => (bool) ($row['is_control'] ?? false),
                    'control_solution_id' => $row['control_solution_id'] ?? null,
                ],
                [
                    'result' => $row['result'] ?? null,
                    'remark' => $this->normalizeResultRemark($row['remark'] ?? null),
                    'method_id' => $row['method_id'] ?? null,
                    'analyst_id' => $row['analyst_id'] ?? auth()->id(),
                    'standard_limit' => $row['standard_limit'] ?? null,
                    'standard_value' => $row['standard_value'] ?? null,
                ]
            );
        }

        $step->refresh();
        if ($step->isAnalysisStep() && $step->isCompleted()) {
            $step->markCompleted();
        }

        $this->moveToAwaitingApprovalIfEligible($step->preparation);
    }

    /**
     * @param  array<int, array<string, mixed>>  $mediaResults
     */
    public function saveMediaResults(SolutionPreparation $preparation, array $mediaResults): void
    {
        $lastStep = $preparation->steps()->orderByDesc('step_number')->first();
        if (! $lastStep) {
            return;
        }

        foreach ($mediaResults as $row) {
            PreparationStepMedia::firstOrCreate([
                'preparation_step_id' => $lastStep->id,
                'lab_category_item_id' => $row['lab_category_item_id'],
            ]);
        }

        $this->moveToAwaitingApprovalIfEligible($preparation);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function saveInoculatedMedia(SolutionPreparation $preparation, array $rows): void
    {
        foreach ($rows as $row) {
            if (! empty($row['id'])) {
                PreparationInoculatedMedia::where('id', $row['id'])
                    ->where('preparation_id', $preparation->id)
                    ->update([
                        'lab_category_item_id' => $row['lab_category_item_id'] ?? null,
                        'media_id' => $row['media_id'] ?? null,
                        'result' => $row['result'] ?? null,
                        'notes' => $row['notes'] ?? null,
                    ]);
            } else {
                PreparationInoculatedMedia::create([
                    'preparation_id' => $preparation->id,
                    'lab_category_item_id' => $row['lab_category_item_id'] ?? null,
                    'media_id' => $row['media_id'] ?? null,
                    'result' => $row['result'] ?? null,
                    'notes' => $row['notes'] ?? null,
                ]);
            }
        }

        $this->moveToAwaitingApprovalIfEligible($preparation);
    }

    public function forceComplete(SolutionPreparation $preparation): void
    {
        if ($preparation->inoculatedMedia()->count() === 0 && $preparation->requiresInoculatedMedia()) {
            throw ValidationException::withMessages(['preparation' => 'Inoculated media results are required.']);
        }

        $missing = $preparation->getMissingResultReasons();
        if (count($missing) > 0) {
            throw ValidationException::withMessages(['preparation' => implode(' ', $missing)]);
        }

        if (! $preparation->allStepsCompleted()) {
            throw ValidationException::withMessages(['preparation' => 'All steps must be completed first.']);
        }

        $preparation->update(['status' => 'awaiting_approval']);
    }

    public function moveToAwaitingApprovalIfEligible(SolutionPreparation $preparation): void
    {
        $preparation->refresh();

        if (! $preparation->canMoveToAwaitingApproval() || $preparation->isAwaitingApproval()) {
            return;
        }

        $preparation->update(['status' => 'awaiting_approval']);
    }

    protected function normalizeResultRemark(mixed $remark): ?string
    {
        if ($remark === null || $remark === '') {
            return null;
        }

        $value = strtolower((string) $remark);

        return in_array($value, ['pass', 'fail'], true) ? $value : null;
    }

    public function approve(SolutionPreparation $preparation, ?string $notes = null, ?string $userId = null): SolutionPreparation
    {
        if ($preparation->is_new_batch && ! $preparation->expiry_date) {
            throw ValidationException::withMessages([
                'expiry_date' => 'An expiry date is required before approving a new solution batch.',
            ]);
        }

        $missing = $preparation->getMissingResultReasons();
        if (count($missing) > 0 || ! $preparation->allStepsCompleted()) {
            throw ValidationException::withMessages(['preparation' => implode(' ', $missing) ?: 'All steps must be completed.']);
        }

        return DB::transaction(function () use ($preparation, $notes, $userId) {
            $preparation->update([
                'status' => 'completed',
                'approved_by' => $userId ?? auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $this->stockMovementService->createPreparationStockMovement($preparation->fresh(['solution']));

            return $preparation->fresh();
        });
    }

    public function reject(SolutionPreparation $preparation, ?string $reason = null): SolutionPreparation
    {
        $preparation->update([
            'status' => 'cancelled',
            'approval_notes' => $reason,
        ]);

        return $preparation->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePreparationRecord(SolutionPreparation $preparation, array $data): SolutionPreparation
    {
        if ($preparation->status === 'completed') {
            throw ValidationException::withMessages(['preparation' => 'Cannot update a completed preparation.']);
        }

        if ($preparation->status === 'cancelled') {
            throw ValidationException::withMessages(['preparation' => 'Cannot update a cancelled preparation.']);
        }

        return DB::transaction(function () use ($preparation, $data) {
            $preparation->update([
                'quantity_prepared' => $data['quantity_prepared'] ?? null,
                'uom_id' => $data['uom_id'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'is_new_batch' => (bool) ($data['is_new_batch'] ?? false),
                'notes' => $data['notes'] ?? null,
            ]);

            return $preparation->fresh(['solution', 'steps.controls', 'steps.results']);
        });
    }

    public function deletePreparation(SolutionPreparation $preparation): void
    {
        if ($preparation->status === 'completed') {
            throw ValidationException::withMessages(['preparation' => 'Cannot delete a completed preparation.']);
        }

        $preparation->delete();
    }

    /**
     * @param  array<int, string>  $orderedStepIds
     */
    public function reorderSteps(SolutionPreparation $preparation, array $orderedStepIds): void
    {
        if (! $preparation->isInProgress()) {
            return;
        }

        $number = 1;
        foreach ($orderedStepIds as $stepId) {
            PreparationStep::where('id', $stepId)
                ->where('preparation_id', $preparation->id)
                ->update(['step_number' => $number++]);
        }
    }
}
