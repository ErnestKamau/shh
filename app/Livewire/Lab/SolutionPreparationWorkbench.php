<?php

namespace App\Livewire\Lab;

use App\Analyte;
use App\LabCategoryItems;
use App\LabSubCategory;
use App\Models\PreparationStep;
use App\Models\SolutionPreparation;
use App\Models\SolutionPreparationStepTemplate;
use App\Services\Preparation\PreparationRunService;
use App\Services\Preparation\PreparationSummaryBuilder;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SolutionPreparationWorkbench extends Component
{
    public string $preparationId;

    public $message = '';

    public $messageType = '';

    public $approvalNotes = '';

    public $rejectReason = '';

    public $showAdHocModal = false;

    public bool $showCompleteStepModal = false;

    public ?string $completingStepId = null;

    public bool $completeWithSaveResults = false;

    public bool $goToNextStepAfterComplete = true;

    public ?string $highlightedNextStepId = null;

    public ?string $activeStepId = null;

    public $adHocForm = [
        'step_name' => '',
        'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
        'ingredient_id' => null,
        'description' => '',
        'save_to_template' => false,
    ];

    /** @var array<string, array<string, mixed>> */
    public array $resultInputs = [];

    /** @var array<int, array<string, mixed>> */
    public array $inoculatedRows = [];

    public function mount(string $preparationId, PreparationRunService $runService): void
    {
        $this->preparationId = $preparationId;
        $preparation = SolutionPreparation::findOrFail($preparationId);
        $runService->moveToAwaitingApprovalIfEligible($preparation);
        $this->loadResultInputs();
        $this->ensureActiveStep();
    }

    public function getPreparationProperty(): SolutionPreparation
    {
        return SolutionPreparation::with([
            'solution.reportingUnit',
            'solution.alternativeSolution',
            'preparedUom',
            'preparer',
            'approver',
            'sourcePreparation',
            'steps.ingredient.reagent',
            'steps.ingredient.unitMeasure',
            'steps.controls.controlSolution',
            'steps.results',
            'steps.media',
            'inoculatedMedia',
        ])->findOrFail($this->preparationId);
    }

    public function getIngredientsProperty()
    {
        return LabCategoryItems::where('sub_category_id', $this->preparation->solution_id)
            ->with('reagent')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPreparationPanelProperty(): array
    {
        return app(PreparationSummaryBuilder::class)->build($this->preparation);
    }

    public function getControlSolutionsProperty()
    {
        return LabSubCategory::where('active', 1)->orderBy('name')->get();
    }

    public function getActiveStepProperty(): ?PreparationStep
    {
        if ($this->activeStepId === null) {
            return null;
        }

        return $this->preparation->steps->first(fn (PreparationStep $step) => (string) $step->id === (string) $this->activeStepId);
    }

    public function setActiveStep(string $stepId): void
    {
        if (! $this->preparation->steps->contains(fn (PreparationStep $step) => (string) $step->id === (string) $stepId)) {
            return;
        }

        $this->activeStepId = $stepId;
        $this->highlightedNextStepId = null;
    }

    public function getCompletingStepProperty(): ?PreparationStep
    {
        if ($this->completingStepId === null) {
            return null;
        }

        return $this->preparation->steps->first(
            fn (PreparationStep $step) => (string) $step->id === (string) $this->completingStepId
        );
    }

    public function getNextStepAfterCompletingProperty(): ?PreparationStep
    {
        if ($this->completingStepId === null) {
            return null;
        }

        return $this->findFollowingStep($this->completingStepId);
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function openAdHocModal(): void
    {
        $this->showAdHocModal = true;
    }

    public function closeAdHocModal(): void
    {
        $this->showAdHocModal = false;
    }

    public function openCompleteStepModal(string $stepId, bool $withSaveResults = false): void
    {
        if (! $this->preparation->steps->contains(fn (PreparationStep $step) => (string) $step->id === (string) $stepId)) {
            return;
        }

        $this->completingStepId = $stepId;
        $this->completeWithSaveResults = $withSaveResults;
        $this->goToNextStepAfterComplete = true;
        $this->showCompleteStepModal = true;
    }

    public function closeCompleteStepModal(): void
    {
        $this->showCompleteStepModal = false;
        $this->completingStepId = null;
        $this->completeWithSaveResults = false;
    }

    public function confirmCompleteStep(PreparationRunService $runService): void
    {
        if ($this->completingStepId === null) {
            return;
        }

        $stepId = $this->completingStepId;
        $nextInSequence = $this->findFollowingStep($stepId);

        try {
            $step = PreparationStep::where('preparation_id', $this->preparationId)->findOrFail($stepId);

            if ($this->completeWithSaveResults && $step->isAnalysisStep()) {
                $rows = [];
                foreach ($this->resultInputs as $row) {
                    if ((string) ($row['preparation_step_id'] ?? '') === (string) $stepId) {
                        $rows[] = $row;
                    }
                }
                $runService->saveStepResults($step, $rows);
                $this->loadResultInputs();
            }

            $runService->completeStep($step);
            $this->closeCompleteStepModal();
            $this->applyPostCompleteNavigation($nextInSequence);
            $this->message = 'Step completed.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    protected function loadResultInputs(): void
    {
        $prep = $this->preparation;
        foreach ($prep->steps as $step) {
            if (! $step->isAnalysisStep()) {
                continue;
            }
            foreach ($step->selected_analytes ?? [] as $analyteId) {
                $key = "{$step->id}-sample-{$analyteId}";
                $existing = $step->results->first(fn ($r) => $r->analyte_id == $analyteId && ! $r->is_control);
                $this->resultInputs[$key] = [
                    'preparation_step_id' => $step->id,
                    'analyte_id' => $analyteId,
                    'is_control' => false,
                    'control_solution_id' => null,
                    'result' => $existing?->result ?? '',
                    'remark' => $existing?->remark ?? '',
                ];
            }
            foreach ($step->controls as $control) {
                foreach ($step->selected_analytes ?? [] as $analyteId) {
                    $key = "{$step->id}-control-{$control->control_solution_id}-{$analyteId}";
                    $existing = $step->results->first(fn ($r) => $r->analyte_id == $analyteId && $r->is_control && $r->control_solution_id == $control->control_solution_id);
                    $this->resultInputs[$key] = [
                        'preparation_step_id' => $step->id,
                        'analyte_id' => $analyteId,
                        'is_control' => true,
                        'control_solution_id' => $control->control_solution_id,
                        'result' => $existing?->result ?? '',
                        'remark' => $existing?->remark ?? '',
                    ];
                }
            }
        }

        $this->inoculatedRows = $prep->inoculatedMedia->map(fn ($m) => [
            'id' => $m->id,
            'lab_category_item_id' => $m->lab_category_item_id,
            'result' => $m->result,
            'notes' => $m->notes,
        ])->toArray();
    }

    public function syncTemplate(PreparationRunService $runService): void
    {
        $added = $runService->syncTemplate($this->preparation);
        $this->message = $added > 0 ? "Added {$added} step(s) from template." : 'No new template steps to sync.';
        $this->messageType = 'success';
    }


    public function saveStepResultsForStep(string $stepId, PreparationRunService $runService): void
    {
        $rows = [];
        foreach ($this->resultInputs as $row) {
            if ((string) ($row['preparation_step_id'] ?? '') === (string) $stepId) {
                $rows[] = $row;
            }
        }

        $step = PreparationStep::where('preparation_id', $this->preparationId)->findOrFail($stepId);
        $runService->saveStepResults($step, $rows);
        $this->loadResultInputs();
        $this->message = 'Results saved for this step.';
        $this->messageType = 'success';
    }


    public function uncompleteStep(string $stepId, PreparationRunService $runService): void
    {
        try {
            $step = PreparationStep::where('preparation_id', $this->preparationId)->findOrFail($stepId);
            $runService->uncompleteStep($step);
            $this->activeStepId = $stepId;
            $this->highlightedNextStepId = null;
            $this->message = 'Step reopened.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function deleteStep(string $stepId, PreparationRunService $runService): void
    {
        $step = PreparationStep::where('preparation_id', $this->preparationId)->findOrFail($stepId);
        $runService->deleteStep($step);
        $this->message = 'Step removed.';
        $this->messageType = 'success';
    }

    public function saveResults(PreparationRunService $runService): void
    {
        $grouped = [];
        foreach ($this->resultInputs as $row) {
            $grouped[$row['preparation_step_id']][] = $row;
        }

        foreach ($grouped as $stepId => $rows) {
            $step = PreparationStep::findOrFail($stepId);
            $runService->saveStepResults($step, $rows);
        }

        $this->loadResultInputs();
        $this->message = 'Results saved.';
        $this->messageType = 'success';
    }

    public function saveInoculatedMedia(PreparationRunService $runService): void
    {
        $runService->saveInoculatedMedia($this->preparation, $this->inoculatedRows);
        $this->loadResultInputs();
        $this->message = 'Inoculated media saved.';
        $this->messageType = 'success';
    }

    public function addInoculatedRow(): void
    {
        $this->inoculatedRows[] = ['lab_category_item_id' => null, 'result' => '', 'notes' => ''];
    }

    public function forceComplete(PreparationRunService $runService): void
    {
        try {
            $runService->forceComplete($this->preparation);
            $this->message = 'Preparation submitted for approval.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function approve(PreparationRunService $runService): void
    {
        try {
            $runService->approve($this->preparation, $this->approvalNotes);
            $this->message = 'Preparation approved and stock updated.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function reject(PreparationRunService $runService): void
    {
        $runService->reject($this->preparation, $this->rejectReason);
        $this->message = 'Preparation rejected.';
        $this->messageType = 'success';
    }

    public function deletePreparation(PreparationRunService $runService): void
    {
        try {
            $runService->deletePreparation($this->preparation);
            $this->redirect(route('solutions-preparation-index'), navigate: true);
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function saveAdHocStep(PreparationRunService $runService): void
    {
        $this->validate(['adHocForm.step_name' => 'required|string|max:255']);
        try {
            $runService->addAdHocStep(
                $this->preparation,
                $this->adHocForm,
                (bool) ($this->adHocForm['save_to_template'] ?? false)
            );
            $this->showAdHocModal = false;
            $this->activeStepId = null;
            $this->ensureActiveStep();
            $this->loadResultInputs();
            $this->message = 'Step added.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function getAnalyteName(string $id): string
    {
        return Analyte::find($id)?->name ?? $id;
    }

    protected function ensureActiveStep(): void
    {
        $steps = $this->preparation->steps;

        if ($this->activeStepId !== null && $steps->contains(fn (PreparationStep $step) => (string) $step->id === (string) $this->activeStepId)) {
            return;
        }

        $next = $steps->first(fn (PreparationStep $step) => ! $step->isCompleted());

        $this->activeStepId = $next?->id ?? $steps->first()?->id;
    }

    protected function findFollowingStep(string $stepId): ?PreparationStep
    {
        $steps = $this->preparation->steps->values();
        $index = $steps->search(fn (PreparationStep $step) => (string) $step->id === (string) $stepId);

        if ($index === false || $index >= $steps->count() - 1) {
            return null;
        }

        return $steps[$index + 1];
    }

    protected function applyPostCompleteNavigation(?PreparationStep $nextInSequence): void
    {
        $steps = SolutionPreparation::with('steps')
            ->findOrFail($this->preparationId)
            ->steps;

        $nextIncomplete = null;

        if ($nextInSequence !== null) {
            $following = $steps->first(
                fn (PreparationStep $step) => (string) $step->id === (string) $nextInSequence->id
            );
            if ($following !== null && ! $following->isCompleted()) {
                $nextIncomplete = $following;
            }
        }

        if ($nextIncomplete === null) {
            $nextIncomplete = $steps->first(fn (PreparationStep $step) => ! $step->isCompleted());
        }

        if ($this->goToNextStepAfterComplete && $nextIncomplete !== null) {
            $this->activeStepId = $nextIncomplete->id;
            $this->highlightedNextStepId = null;

            return;
        }

        $this->activeStepId = null;
        $this->highlightedNextStepId = $nextIncomplete?->id;
    }

    public function render()
    {
        return view('livewire.lab.solution-preparation-workbench');
    }
}
