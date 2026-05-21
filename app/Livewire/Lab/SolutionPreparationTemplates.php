<?php

namespace App\Livewire\Lab;

use App\AnalysisType;
use App\LabCategoryItems;
use App\LabSubCategory;
use App\Models\SolutionPreparationStepTemplate;
use App\SampleType;
use App\Services\Preparation\PreparationTemplateService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SolutionPreparationTemplates extends Component
{
    public string $subCategoryId;

    public string $activeTab = 'templates';

    public $showTemplateModal = false;

    public ?string $editingTemplateId = null;

    public bool $deleteFromPreparations = false;

    public $templateForm = [
        'step_number' => 1,
        'step_name' => '',
        'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
        'ingredient_id' => null,
        'description' => '',
        'notes' => '',
        'sample_type_id' => null,
        'analysis_type_id' => null,
        'selected_analytes' => [],
        'result_type' => null,
        'standard_id' => null,
    ];

    /** @var array<int, array{control_solution_id: string|null, label: string}> */
    public array $templateControls = [];

    public $alternativeSolutionId = null;

    public $cloneName = '';

    public $scaleFactor = '1';

    public $message = '';

    public $messageType = '';

    public $sampleTypes = [];

    public $analysisTypes = [];

    public $analyteOptions = [];

    public $controlSolutions = [];

    public $ingredients = [];

    protected PreparationTemplateService $templateService;

    public function boot(PreparationTemplateService $templateService): void
    {
        $this->templateService = $templateService;
    }

    public function mount(string $subCategoryId): void
    {
        $this->subCategoryId = $subCategoryId;
        $this->loadSupportingData();
        $solution = LabSubCategory::find($subCategoryId);
        $this->alternativeSolutionId = $solution?->alternative_solution_id;
    }

    public function loadSupportingData(): void
    {
        $this->sampleTypes = SampleType::where('active', 1)->orderBy('name')->get();
        $this->controlSolutions = LabSubCategory::where('active', 1)
            ->where('id', '!=', $this->subCategoryId)
            ->orderBy('name')
            ->get();
        $this->ingredients = LabCategoryItems::where('sub_category_id', $this->subCategoryId)
            ->with('reagent')
            ->get();
    }

    public function getTemplatesProperty()
    {
        return SolutionPreparationStepTemplate::where('lab_sub_category_id', $this->subCategoryId)
            ->with(['ingredient.reagent', 'controls.controlSolution'])
            ->orderBy('step_number')
            ->get();
    }

    public function getSolutionProperty(): ?LabSubCategory
    {
        return LabSubCategory::with('alternativeSolution')->find($this->subCategoryId);
    }

    public function updatedTemplateFormSampleTypeId($value): void
    {
        $this->templateForm['analysis_type_id'] = null;
        $this->templateForm['selected_analytes'] = [];
        $this->analysisTypes = $value
            ? AnalysisType::where('sample_type_id', $value)->where('active', 1)->orderBy('name')->get()
            : collect();
        $this->analyteOptions = [];
    }

    public function updatedTemplateFormAnalysisTypeId($value): void
    {
        $this->templateForm['selected_analytes'] = [];
        if (! $value) {
            $this->analyteOptions = [];

            return;
        }

        $response = app(\App\Http\Controllers\Lab\SolutionPreparationAjaxController::class)->analytes($value);
        $this->analyteOptions = json_decode($response->getContent(), true) ?? [];
    }

    public function showAddTemplateModal(): void
    {
        $max = (int) SolutionPreparationStepTemplate::where('lab_sub_category_id', $this->subCategoryId)->max('step_number');
        $this->resetTemplateForm();
        $this->templateForm['step_number'] = $max + 1;
        $this->editingTemplateId = null;
        $this->showTemplateModal = true;
    }

    public function showEditTemplateModal(string $id): void
    {
        $template = SolutionPreparationStepTemplate::with('controls')->findOrFail($id);
        $this->templateForm = [
            'step_number' => $template->step_number,
            'step_name' => $template->step_name,
            'step_type' => $template->step_type,
            'ingredient_id' => $template->ingredient_id,
            'description' => $template->description ?? '',
            'notes' => $template->notes ?? '',
            'sample_type_id' => $template->sample_type_id,
            'analysis_type_id' => $template->analysis_type_id,
            'selected_analytes' => $template->selected_analytes ?? [],
            'result_type' => $template->result_type,
            'standard_id' => $template->standard_id,
        ];
        $this->templateControls = $template->controls->map(fn ($c) => [
            'control_solution_id' => $c->control_solution_id,
            'label' => $c->label ?? '',
        ])->toArray();

        if ($template->sample_type_id) {
            $this->analysisTypes = AnalysisType::where('sample_type_id', $template->sample_type_id)->where('active', 1)->get();
        }
        if ($template->analysis_type_id) {
            $this->updatedTemplateFormAnalysisTypeId($template->analysis_type_id);
        }

        $this->editingTemplateId = $id;
        $this->showTemplateModal = true;
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'templateForm.step_number' => 'required|integer|min:1',
            'templateForm.step_name' => 'required|string|max:255',
            'templateForm.step_type' => 'required|string',
        ]);

        try {
            $controls = array_values(array_filter($this->templateControls, fn ($c) => ! empty($c['control_solution_id'])));

            if ($this->editingTemplateId) {
                $template = SolutionPreparationStepTemplate::findOrFail($this->editingTemplateId);
                $oldName = $template->step_name;
                $this->templateService->updateTemplate($template, $this->templateForm, $controls, $oldName);
                $this->message = 'Template updated.';
            } else {
                $this->templateService->addTemplate($this->subCategoryId, $this->templateForm, $controls);
                $this->message = 'Template added.';
            }

            $this->messageType = 'success';
            $this->showTemplateModal = false;
            $this->resetTemplateForm();
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first() ?? $e->getMessage();
            $this->messageType = 'danger';
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteTemplate(string $id): void
    {
        try {
            $template = SolutionPreparationStepTemplate::findOrFail($id);
            $this->templateService->deleteTemplate($template, $this->deleteFromPreparations);
            $this->message = 'Template deleted.';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function addControlRow(): void
    {
        $this->templateControls[] = ['control_solution_id' => null, 'label' => ''];
    }

    public function removeControlRow(int $index): void
    {
        unset($this->templateControls[$index]);
        $this->templateControls = array_values($this->templateControls);
    }

    public function saveAlternative(): void
    {
        try {
            $this->templateService->setAlternativeSolution(
                LabSubCategory::findOrFail($this->subCategoryId),
                $this->alternativeSolutionId ?: null
            );
            $this->message = 'Alternative solution updated.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function cloneSolution(): void
    {
        $this->validate(['cloneName' => 'required|string|max:255']);
        $source = LabSubCategory::findOrFail($this->subCategoryId);
        $this->templateService->cloneSolution($source, $this->cloneName);
        $this->message = 'Solution cloned. Open Solutions Management to view it.';
        $this->messageType = 'success';
    }

    public function cloneScaled(): void
    {
        $this->validate([
            'cloneName' => 'required|string|max:255',
            'scaleFactor' => 'required|numeric|min:0.0001',
        ]);
        $source = LabSubCategory::findOrFail($this->subCategoryId);
        $this->templateService->cloneSolution($source, $this->cloneName, (float) $this->scaleFactor);
        $this->message = 'Scaled clone created.';
        $this->messageType = 'success';
    }

    public function closeTemplateModal(): void
    {
        $this->showTemplateModal = false;
        $this->resetTemplateForm();
    }

    protected function resetTemplateForm(): void
    {
        $this->templateForm = [
            'step_number' => 1,
            'step_name' => '',
            'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
            'ingredient_id' => null,
            'description' => '',
            'notes' => '',
            'sample_type_id' => null,
            'analysis_type_id' => null,
            'selected_analytes' => [],
            'result_type' => null,
            'standard_id' => null,
        ];
        $this->templateControls = [];
        $this->editingTemplateId = null;
    }

    public function render()
    {
        return view('livewire.lab.solution-preparation-templates');
    }
}
