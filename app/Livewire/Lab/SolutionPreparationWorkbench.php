<?php

namespace App\Livewire\Lab;

use App\Analyte;
use App\LabCategoryItems;
use App\LabSubCategory;
use App\Models\PreparationStep;
use App\Models\SolutionPreparation;
use App\Models\SolutionPreparationStepTemplate;
use App\Services\Preparation\PreparationRunService;
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
    }

    public function getPreparationProperty(): SolutionPreparation
    {
        return SolutionPreparation::with([
            'solution.reportingUnit',
            'preparer',
            'approver',
            'steps.ingredient.reagent',
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

    public function getControlSolutionsProperty()
    {
        return LabSubCategory::where('active', 1)->orderBy('name')->get();
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

    public function completeStep(string $stepId, PreparationRunService $runService): void
    {
        try {
            $step = PreparationStep::where('preparation_id', $this->preparationId)->findOrFail($stepId);
            $runService->completeStep($step);
            $this->message = 'Step completed.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function uncompleteStep(string $stepId, PreparationRunService $runService): void
    {
        try {
            $step = PreparationStep::where('preparation_id', $this->preparationId)->findOrFail($stepId);
            $runService->uncompleteStep($step);
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

    public function render()
    {
        return view('livewire.lab.solution-preparation-workbench');
    }
}
