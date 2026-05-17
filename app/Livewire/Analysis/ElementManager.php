<?php

namespace App\Livewire\Analysis;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use App\User;
use App\ReportingUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ElementManager extends Component
{
    use WithPagination;

    // Analysis Type ID
    public $analysisTypeId;

    // Elements Management
    public $editingElement = null;
    public $showElementModal = false;
    
    // Element Form
    public $elementForm = [
        'analyte_id' => null,
        'method' => null,
        'equipment_id' => null,
        'operator_id' => null,
        'reporting_unit' => '',
        'decimal_places' => 2,
        'significant_figures' => 3,
        'lod' => null,
        'hod' => null,
        'level' => 1,
        'active' => true,
        'non_detectable' => false,
        'non_accredited' => false,
        'show_on_report' => true,
        'recommend_remedies' => false,
        'remedy_header_id' => null,
        'remark_is_manual' => false,
        'result_is_calculated' => false,
        'formular_id' => null,
        'has_method_sequence' => false,
        'method_sequence_id' => null,
        'stage_header_id' => null,
    ];

    // Supporting Data
    public $analytes = [];
    public $methods = [];
    public $equipment = [];
    public $operators = [];
    public $remedyHeaders = [];
    public $reportingUnits = [];
    public $formulars = [];
    public $methodSequences = [];

    public $stageHeaders = [];

    // Searchable Select Properties
    public $analyteSearch = '';
    public $methodSearch = '';
    public $equipmentSearch = '';
    public $operatorSearch = '';
    public $remedyHeaderSearch = '';
    public $formularSearch = '';
    public $methodSequenceSearch = '';
    public $reportingUnitSearch = '';

    public $showAnalyteDropdown = false;
    public $showMethodDropdown = false;
    public $showEquipmentDropdown = false;
    public $showOperatorDropdown = false;
    public $showRemedyHeaderDropdown = false;
    public $showFormularDropdown = false;
    public $showMethodSequenceDropdown = false;
    public $showReportingUnitDropdown = false;

    public $selectedAnalyteName = '';
    public $selectedMethodName = '';
    public $selectedEquipmentName = '';
    public $selectedOperatorName = '';
    public $selectedRemedyHeaderName = '';
    public $selectedFormularName = '';
    public $selectedMethodSequenceName = '';

    public $filteredAnalytes = [];
    public $filteredMethods = [];
    public $filteredEquipment = [];
    public $filteredOperators = [];
    public $filteredRemedyHeaders = [];
    public $filteredFormulars = [];
    public $filteredMethodSequences = [];
    public $filteredReportingUnits = [];

    // Search and Filter
    public $search = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'elementForm.analyte_id' => 'required|exists:analytes,id',
        'elementForm.method' => 'nullable|exists:analysis_methods,id',
        'elementForm.equipment_id' => 'nullable|exists:equipment,id',
        'elementForm.operator_id' => 'nullable|exists:users,id',
        'elementForm.reporting_unit' => 'nullable|string|max:255',
        'elementForm.decimal_places' => 'nullable|integer|min:0|max:10',
        'elementForm.significant_figures' => 'nullable|integer|min:1|max:10',
        'elementForm.level' => 'nullable|integer|min:1',
        'elementForm.remark_is_manual' => 'boolean',
        'elementForm.result_is_calculated' => 'boolean',
        'elementForm.formular_id' => 'nullable|uuid|exists:formulas,id',
        'elementForm.recommend_remedies' => 'boolean',
        'elementForm.remedy_header_id' => 'nullable|uuid|exists:remedy_headers,id',
        'elementForm.has_method_sequence' => 'boolean',
        'elementForm.method_sequence_id' => 'nullable',
        'elementForm.stage_header_id' => 'nullable|exists:stage_headers,id',
    ];

    public function getRules()
    {
        $rules = $this->rules;
        
        // Make formular_id required if result_is_calculated is true
        if ($this->elementForm['result_is_calculated']) {
            $rules['elementForm.formular_id'] = 'required|uuid|exists:formulas,id';
        }
        
        // Make remedy_header_id required if recommend_remedies is true
        if ($this->elementForm['recommend_remedies']) {
            $rules['elementForm.remedy_header_id'] = 'required|uuid|exists:remedy_headers,id';
        }
        
        if ($this->elementForm['has_method_sequence']) {
            $rules['elementForm.stage_header_id'] = 'required|exists:stage_headers,id';
        }

        return $rules;
    }

    protected $messages = [
        'elementForm.analyte_id.required' => 'Analyte selection is required.',
        'elementForm.stage_header_id.required' => 'A stage header is required when "Use stage header workflow" is enabled.',
    ];

    public function mount($analysisTypeId = null)
    {
        $this->analysisTypeId = $analysisTypeId;
        $this->loadInitialData();
        $this->initializeNullLevels();
    }

    public function loadInitialData()
    {
        $this->analytes = Analyte::where('active', 1)->get();
        $this->methods = AnalysisMethod::where('active', 1)->get();
        $this->equipment = Equipment::where('active', 1)->get();
        $this->operators = User::where('active', 1)->where('is_client', 0)->get();
        $this->remedyHeaders = \App\Models\RemedyHeader::all();
        $this->reportingUnits = ReportingUnit::where('active', 1)->get();
        $this->formulars = \App\Models\Formulars\Formula::where('is_active', 1)->get();
        $this->methodSequences = collect([]);
        $this->stageHeaders = collect([]);
    }

    public function getAnalysisTypeProperty()
    {
        return AnalysisType::find($this->analysisTypeId);
    }

    public function getElementsProperty()
    {
        $query = AnalysisElements::with([
            'analyte', 
            'mmethod', 
            'ltmethod', 
            'equipment', 
            'operator', 
            'remedyHeader', 
            'formular',
            'methodSequence.activeVersion',
            'methodSequence.latestVersion',
            'procedureWorksheet'
        ])->where('analysis_type_id', $this->analysisTypeId);

        if ($this->search) {
            $query->whereHas('analyte', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('active', $this->statusFilter === 'active');
        }

        return $query->orderBy('level', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function showCreateElementModal()
    {
        $this->resetElementForm();
        // Preload all active options for dropdowns
        $this->filteredAnalytes = Analyte::where('active', 1)->orderBy('name')->get();
        $this->filteredMethods = AnalysisMethod::where('active', 1)->orderBy('name')->get();
        $this->filteredEquipment = Equipment::where('active', 1)->orderBy('name')->get();
        $this->filteredOperators = User::where('active', 1)->where('is_client', 0)->orderBy('name')->get();
        $this->filteredReportingUnits = ReportingUnit::orderBy('name')->get();
        $this->showElementModal = true;
        $this->editingElement = null;
        $this->dispatch('element-modal-opened');
    }

    public function showEditElementModal($elementId)
    {
        $element = AnalysisElements::findOrFail($elementId);
        
        // Set editing element first to ensure wire:key updates
        $this->editingElement = $element;
        
        // Reset form data only (preserves editingElement)
        $this->resetFormDataOnly();
        
        // Then populate with element data
        $this->elementForm = [
            'analyte_id' => $element->analyte_id,
            'method' => $element->method,
            'equipment_id' => $element->equipment_id,
            'operator_id' => $element->operator_id,
            'reporting_unit' => $element->reporting_unit,
            'decimal_places' => $element->decimal_places,
            'significant_figures' => $element->significant_figures,
            'lod' => $element->lod,
            'hod' => $element->hod,
            'level' => $element->level,
            'active' => $element->active,
            'non_detectable' => $element->non_detectable,
            'non_accredited' => $element->non_accredited,
            'show_on_report' => $element->show_on_report,
            'recommend_remedies' => $element->recommend_remedies ?? false,
            'remedy_header_id' => $element->remedy_header_id,
            'remark_is_manual' => $element->remark_is_manual ?? false,
            'result_is_calculated' => $element->result_is_calculated ?? false,
            'formular_id' => $element->formular_id,
            'has_method_sequence' => $element->has_method_sequence ?? false,
            'method_sequence_id' => $element->method_sequence_id,
            'stage_header_id' => $element->stage_header_id,
        ];
        
        // Set selected names for searchable selects
        $this->selectedAnalyteName = $element->analyte->name ?? '';
        $this->analyteSearch = $this->selectedAnalyteName;
        
        $this->selectedMethodName = $element->mmethod->name ?? $element->ltmethod->name ?? '';
        $this->methodSearch = $this->selectedMethodName;
        
        $this->selectedEquipmentName = $element->equipment->name ?? '';
        $this->equipmentSearch = $this->selectedEquipmentName;
        
        $this->selectedOperatorName = $element->operator->name ?? '';
        $this->operatorSearch = $this->selectedOperatorName;
        
        $this->selectedRemedyHeaderName = $element->remedyHeader->name ?? '';
        $this->remedyHeaderSearch = $this->selectedRemedyHeaderName;
        
        $this->selectedFormularName = $element->formular_id ? \App\Models\Formulars\Formula::find($element->formular_id)?->name : '';
        $this->formularSearch = $this->selectedFormularName;
        
        $this->selectedMethodSequenceName = $element->methodSequence->name ?? '';
        $this->methodSequenceSearch = $this->selectedMethodSequenceName;
        
        $this->loadMethodSequencesForAnalyte();
        $this->reloadStageHeaderOptions();

        $this->showElementModal = true;
        $this->dispatch('element-modal-opened');
    }

    public function saveElement()
    {
        // Backward compatibility: older records can have null numeric defaults.
        $this->elementForm['decimal_places'] = $this->elementForm['decimal_places'] ?? 2;
        $this->elementForm['significant_figures'] = $this->elementForm['significant_figures'] ?? 3;
        $this->elementForm['level'] = $this->elementForm['level'] ?? 1;

        if (! $this->elementForm['has_method_sequence']) {
            $this->elementForm['stage_header_id'] = null;
            $this->elementForm['method_sequence_id'] = null;
        } else {
            $this->elementForm['method_sequence_id'] = null;
        }

        $this->validate($this->getRules());

        try {
            $data = array_merge($this->elementForm, [
                'analysis_type_id' => $this->analysisTypeId,
                'procedure_worksheet_id' => $this->analysisType->procedure_worksheet_id ?? null,
            ]);

            if ($this->editingElement) {
                $this->editingElement->update($data);
                $this->message = 'Element updated successfully!';
            } else {
                AnalysisElements::create($data);
                $this->message = 'Element created successfully!';
            }

            $this->messageType = 'success';
            $this->showElementModal = false;
            $this->dispatch('element-modal-closed');
            $this->loadElements();
        } catch (\Exception $e) {
            $this->message = 'Error saving element: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteElement($elementId)
    {
        try {
            AnalysisElements::findOrFail($elementId)->delete();
            $this->message = 'Element deleted successfully!';
            $this->messageType = 'success';
            $this->loadElements();
        } catch (\Exception $e) {
            $this->message = 'Error deleting element: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function resetElementForm()
    {
        $this->elementForm = [
            'analyte_id' => null,
            'method' => null,
            'equipment_id' => null,
            'operator_id' => null,
            'reporting_unit' => '',
            'decimal_places' => 2,
            'significant_figures' => 3,
            'lod' => null,
            'hod' => null,
            'level' => 1,
            'active' => true,
            'non_detectable' => false,
            'non_accredited' => false,
            'show_on_report' => true,
            'recommend_remedies' => false,
            'remedy_header_id' => null,
            'remark_is_manual' => false,
            'result_is_calculated' => false,
            'formular_id' => null,
            'has_method_sequence' => false,
            'method_sequence_id' => null
        ];
        
        // Reset searchable select properties
        $this->analyteSearch = '';
        $this->methodSearch = '';
        $this->equipmentSearch = '';
        $this->operatorSearch = '';
        $this->remedyHeaderSearch = '';
        $this->formularSearch = '';
        $this->methodSequenceSearch = '';
        
        $this->selectedAnalyteName = '';
        $this->selectedMethodName = '';
        $this->selectedEquipmentName = '';
        $this->selectedOperatorName = '';
        $this->selectedRemedyHeaderName = '';
        $this->selectedFormularName = '';
        $this->selectedMethodSequenceName = '';
        
        $this->showAnalyteDropdown = false;
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
        $this->showOperatorDropdown = false;
        $this->showRemedyHeaderDropdown = false;
        $this->showFormularDropdown = false;
        $this->showMethodSequenceDropdown = false;
        
        $this->editingElement = null;
    }

    public function resetFormDataOnly()
    {
        $this->elementForm = [
            'analyte_id' => null,
            'method' => null,
            'equipment_id' => null,
            'operator_id' => null,
            'reporting_unit' => '',
            'decimal_places' => 2,
            'significant_figures' => 3,
            'lod' => null,
            'hod' => null,
            'level' => 1,
            'active' => true,
            'non_detectable' => false,
            'non_accredited' => false,
            'show_on_report' => true,
            'recommend_remedies' => false,
            'remedy_header_id' => null,
            'remark_is_manual' => false,
            'result_is_calculated' => false,
            'formular_id' => null,
            'has_method_sequence' => false,
            'method_sequence_id' => null
        ];
        
        // Reset searchable select properties
        $this->analyteSearch = '';
        $this->methodSearch = '';
        $this->equipmentSearch = '';
        $this->operatorSearch = '';
        $this->remedyHeaderSearch = '';
        $this->formularSearch = '';
        $this->methodSequenceSearch = '';
        
        $this->selectedAnalyteName = '';
        $this->selectedMethodName = '';
        $this->selectedEquipmentName = '';
        $this->selectedOperatorName = '';
        $this->selectedRemedyHeaderName = '';
        $this->selectedFormularName = '';
        $this->selectedMethodSequenceName = '';
        
        $this->showAnalyteDropdown = false;
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
        $this->showOperatorDropdown = false;
        $this->showRemedyHeaderDropdown = false;
        $this->showFormularDropdown = false;
        $this->showMethodSequenceDropdown = false;
    }

    public function updatedElementFormRecommendRemedies()
    {
        if (!$this->elementForm['recommend_remedies']) {
            $this->elementForm['remedy_header_id'] = null;
        }
    }

    public function updatedElementFormResultIsCalculated()
    {
        if (!$this->elementForm['result_is_calculated']) {
            $this->elementForm['formular_id'] = null;
        }
    }

    public function updatedElementFormHasMethodSequence()
    {
        if (! $this->elementForm['has_method_sequence']) {
            $this->elementForm['method_sequence_id'] = null;
            $this->elementForm['stage_header_id'] = null;
        }
    }

    public function updatedElementFormAnalyteId()
    {
        $this->loadMethodSequencesForAnalyte();
        $this->reloadStageHeaderOptions();
    }

    public function updatedElementFormMethod()
    {
        $this->reloadStageHeaderOptions();
    }

    protected function reloadStageHeaderOptions(): void
    {
        $analyteId = $this->elementForm['analyte_id'] ?? null;
        $methodId = $this->elementForm['method'] ?? null;

        if (! $analyteId) {
            $this->stageHeaders = collect([]);
            $this->elementForm['stage_header_id'] = null;

            return;
        }

        $this->stageHeaders = \App\Models\StageHeader::query()
            ->where('analyte_id', $analyteId)
            ->when($methodId, fn ($q) => $q->where('method_id', $methodId))
            ->orderBy('name')
            ->get();

        $ids = $this->stageHeaders->pluck('id')->map(fn ($id) => (string) $id)->all();
        $currentId = $this->elementForm['stage_header_id'] ? (string) $this->elementForm['stage_header_id'] : null;
        if ($currentId !== null && ! in_array($currentId, $ids, true)) {
            $this->elementForm['stage_header_id'] = null;
        }
    }

    protected function loadMethodSequencesForAnalyte()
    {
        if ($this->elementForm['analyte_id']) {
            $this->methodSequences = \App\Models\MethodSequences\MethodSequence::where('analyte_id', $this->elementForm['analyte_id'])
                ->where('is_active', true)
                ->with(['activeVersion', 'latestVersion'])
                ->get();
        } else {
            $this->methodSequences = collect([]);
        }
    }

    protected function initializeNullLevels(): void
    {
        $elementsWithNullLevel = AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
            ->whereNull('level')
            ->get();
        
        if ($elementsWithNullLevel->count() > 0) {
            $maxLevel = AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
                ->max('level') ?? 0;
            
            foreach ($elementsWithNullLevel as $index => $element) {
                $element->update(['level' => $maxLevel + $index + 1]);
            }
        }
    }

    // Searchable Select Methods
    public function searchAnalytes()
    {
        $this->showAnalyteDropdown = true;
        $search = $this->analyteSearch;
        
        $this->filteredAnalytes = Analyte::where('active', 1)
            ->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get();
    }

    public function selectAnalyte($id)
    {
        $analyte = collect($this->analytes)->firstWhere('id', $id);
        if (!$analyte) {
            return;
        }

        $this->elementForm['analyte_id'] = $id;
        $this->selectedAnalyteName = $analyte->name;
        $this->analyteSearch = $analyte->name;
        $this->showAnalyteDropdown = false;
        $this->loadMethodSequencesForAnalyte();
        $this->reloadStageHeaderOptions();
    }

    public function clearAnalyte()
    {
        $this->elementForm['analyte_id'] = null;
        $this->selectedAnalyteName = '';
        $this->analyteSearch = '';
        $this->methodSequences = collect([]);
        $this->stageHeaders = collect([]);
        $this->elementForm['stage_header_id'] = null;
    }

    public function searchMethods()
    {
        $this->showMethodDropdown = true;
        $search = $this->methodSearch;
        
        $this->filteredMethods = AnalysisMethod::where('active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectMethod($id)
    {
        $method = collect($this->methods)->firstWhere('id', $id);
        if (!$method) {
            return;
        }

        $this->elementForm['method'] = $id;
        $this->selectedMethodName = $method->name;
        $this->methodSearch = $method->name;
        $this->showMethodDropdown = false;
    }

    public function clearMethod()
    {
        $this->elementForm['method'] = null;
        $this->selectedMethodName = '';
        $this->methodSearch = '';
    }

    public function searchEquipment()
    {
        $this->showEquipmentDropdown = true;
        $search = $this->equipmentSearch;
        
        $this->filteredEquipment = Equipment::where('active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectEquipment($id)
    {
        $equipment = collect($this->equipment)->firstWhere('id', $id);
        if (!$equipment) {
            return;
        }

        $this->elementForm['equipment_id'] = $id;
        $this->selectedEquipmentName = $equipment->name;
        $this->equipmentSearch = $equipment->name;
        $this->showEquipmentDropdown = false;
    }

    public function clearEquipment()
    {
        $this->elementForm['equipment_id'] = null;
        $this->selectedEquipmentName = '';
        $this->equipmentSearch = '';
    }

    public function searchOperators()
    {
        $this->showOperatorDropdown = true;
        $search = $this->operatorSearch;
        
        $this->filteredOperators = User::where('active', 1)
            ->where('is_client', 0)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectOperator($id)
    {
        $operator = collect($this->operators)->firstWhere('id', $id);
        if (!$operator) {
            return;
        }

        $this->elementForm['operator_id'] = $id;
        $this->selectedOperatorName = $operator->name;
        $this->operatorSearch = $operator->name;
        $this->showOperatorDropdown = false;
    }

    public function clearOperator()
    {
        $this->elementForm['operator_id'] = null;
        $this->selectedOperatorName = '';
        $this->operatorSearch = '';
    }

    public function searchRemedyHeaders()
    {
        $this->showRemedyHeaderDropdown = true;
        $search = $this->remedyHeaderSearch;
        
        $this->filteredRemedyHeaders = \App\Models\RemedyHeader::where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectRemedyHeader($id)
    {
        $remedyHeader = collect($this->remedyHeaders)->firstWhere('id', $id);
        if (!$remedyHeader) {
            return;
        }

        $this->elementForm['remedy_header_id'] = $id;
        $this->selectedRemedyHeaderName = $remedyHeader->name;
        $this->remedyHeaderSearch = $remedyHeader->name;
        $this->showRemedyHeaderDropdown = false;
    }

    public function clearRemedyHeader()
    {
        $this->elementForm['remedy_header_id'] = null;
        $this->selectedRemedyHeaderName = '';
        $this->remedyHeaderSearch = '';
    }

    public function searchFormulars()
    {
        $this->showFormularDropdown = true;
        $search = $this->formularSearch;
        
        $this->filteredFormulars = \App\Models\Formulars\Formula::where('is_active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectFormular($id)
    {
        $formular = \App\Models\Formulars\Formula::query()
            ->where('is_active', 1)
            ->find($id);
        if (!$formular) {
            return;
        }

        $this->elementForm['formular_id'] = (string) $formular->id;
        $this->selectedFormularName = $formular->name;
        $this->formularSearch = $formular->name;
        $this->showFormularDropdown = false;
    }

    public function clearFormular()
    {
        $this->elementForm['formular_id'] = null;
        $this->selectedFormularName = '';
        $this->formularSearch = '';
    }

    public function searchMethodSequences()
    {
        $this->showMethodSequenceDropdown = true;
        $search = $this->methodSequenceSearch;
        
        $this->filteredMethodSequences = \App\Models\MethodSequences\MethodSequence::where('is_active', true)
            ->where('name', 'like', '%' . $search . '%')
            ->with(['activeVersion', 'latestVersion'])
            ->limit(10)
            ->get();
    }

    public function selectMethodSequence($id)
    {
        $methodSequence = collect($this->methodSequences)->firstWhere('id', $id);
        if (!$methodSequence) {
            return;
        }

        $this->elementForm['method_sequence_id'] = $id;
        $this->selectedMethodSequenceName = $methodSequence->name;
        $this->methodSequenceSearch = $methodSequence->name;
        $this->showMethodSequenceDropdown = false;
    }

    public function clearMethodSequence()
    {
        $this->elementForm['method_sequence_id'] = null;
        $this->selectedMethodSequenceName = '';
        $this->methodSequenceSearch = '';
    }

    public function searchReportingUnits(): void
    {
        $this->showReportingUnitDropdown = true;
        $search = $this->reportingUnitSearch;
        
        $this->filteredReportingUnits = ReportingUnit::where('active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(50)
            ->get();
    }

    public function selectReportingUnit($unit): void
    {
        $this->elementForm['reporting_unit'] = $unit;
        $this->reportingUnitSearch = '';
        $this->showReportingUnitDropdown = false;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function closeElementModal()
    {
        $this->showElementModal = false;
        $this->resetElementForm();
        $this->dispatch('element-modal-closed');
    }

    #[On('reorderElements')]
    public function reorderElements($elementIds)
    {
        return $this->updateElementOrder($elementIds);
    }

    public function updateElementOrder($elementIds)
    {
        try {
            Log::info('updateElementOrder called', [
                'elementIds' => $elementIds,
                'analysisTypeId' => $this->analysisTypeId
            ]);
            
            DB::transaction(function () use ($elementIds) {
                // Get ALL elements for this analysis type ordered by current level
                $allElements = AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
                    ->orderBy('level', 'asc')
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();
                
                // Calculate the starting position (offset) of the first dragged element in the full list
                $firstDraggedId = $elementIds[0];
                $startPosition = array_search($firstDraggedId, $allElements);
                
                // Remove all dragged elements from the full list
                $remainingElements = array_diff($allElements, $elementIds);
                
                // Insert the reordered elements at the correct position
                $newOrder = array_merge(
                    array_slice(array_values($remainingElements), 0, $startPosition),
                    $elementIds,
                    array_slice(array_values($remainingElements), $startPosition)
                );
                
                // Update all elements with their new levels
                foreach ($newOrder as $index => $elementId) {
                    AnalysisElements::where('id', $elementId)
                        ->where('analysis_type_id', $this->analysisTypeId)
                        ->update(['level' => $index + 1]);
                }
                
                Log::info('Elements reordered successfully', [
                    'totalElements' => count($newOrder),
                    'newOrder' => $newOrder
                ]);
            });
            
            $this->message = 'Elements reordered successfully!';
            $this->messageType = 'success';
            
            // Refresh the elements list to show the new order
            $this->dispatch('$refresh');
            
        } catch (\Exception $e) {
            Log::error('Failed to reorder elements', [
                'error' => $e->getMessage(),
                'elementIds' => $elementIds
            ]);
            
            $this->message = 'Failed to reorder elements: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function loadElements()
    {
        // This method is called to refresh the elements list
        // The actual data loading is handled by the getElementsProperty computed property
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.analysis.element-manager');
    }
}