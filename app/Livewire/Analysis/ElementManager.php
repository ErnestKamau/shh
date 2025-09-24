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
        'formular_id' => null
    ];

    // Supporting Data
    public $analytes = [];
    public $methods = [];
    public $equipment = [];
    public $operators = [];
    public $remedyHeaders = [];
    public $reportingUnits = [];
    public $formulars = [];

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
        'elementForm.reporting_unit' => 'required|string|max:255',
        'elementForm.decimal_places' => 'required|integer|min:0|max:10',
        'elementForm.significant_figures' => 'required|integer|min:1|max:10',
        'elementForm.level' => 'required|integer|min:1',
        'elementForm.remark_is_manual' => 'boolean',
        'elementForm.result_is_calculated' => 'boolean',
        'elementForm.formular_id' => 'nullable|integer',
        'elementForm.recommend_remedies' => 'boolean',
        'elementForm.remedy_header_id' => 'nullable|integer',
    ];

    public function getRules()
    {
        $rules = $this->rules;
        
        // Make formular_id required if result_is_calculated is true
        if ($this->elementForm['result_is_calculated']) {
            $rules['elementForm.formular_id'] = 'required|integer';
        }
        
        // Make remedy_header_id required if recommend_remedies is true
        if ($this->elementForm['recommend_remedies']) {
            $rules['elementForm.remedy_header_id'] = 'required|integer';
        }
        
        return $rules;
    }

    protected $messages = [
        'elementForm.analyte_id.required' => 'Analyte selection is required.',
        'elementForm.reporting_unit.required' => 'Reporting unit is required.',
    ];

    public function mount($analysisTypeId = null)
    {
        $this->analysisTypeId = $analysisTypeId;
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->analytes = Analyte::where('active', 1)->get();
        $this->methods = AnalysisMethod::where('active', 1)->get();
        $this->equipment = Equipment::where('active', 1)->get();
        $this->operators = User::where('active', 1)->get();
        $this->remedyHeaders = \App\Models\RemedyHeader::all();
        $this->reportingUnits = ReportingUnit::where('active', 1)->get();
        $this->formulars = collect([]); // Empty for now as requested
    }

    public function getElementsProperty()
    {
        $query = AnalysisElements::with(['analyte', 'mmethod', 'ltmethod', 'equipment', 'operator', 'remedyHeader'])
            ->where('analysis_type_id', $this->analysisTypeId);

        if ($this->search) {
            $query->whereHas('analyte', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('active', $this->statusFilter === 'active');
        }

        return $query->orderBy('level', 'asc')->paginate($this->perPage);
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
        $this->showElementModal = true;
        $this->editingElement = null;
        $this->dispatch('element-modal-opened');
    }

    public function showEditElementModal($elementId)
    {
        $element = AnalysisElements::findOrFail($elementId);
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
            'formular_id' => $element->formular_id
        ];
        $this->editingElement = $element;
        $this->showElementModal = true;
        $this->dispatch('element-modal-opened');
    }

    public function saveElement()
    {
        $this->validate($this->getRules());

        try {
            $data = array_merge($this->elementForm, [
                'analysis_type_id' => $this->analysisTypeId
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
            'formular_id' => null
        ];
        $this->editingElement = null;
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
                foreach ($elementIds as $index => $elementId) {
                    $updated = AnalysisElements::where('id', $elementId)
                        ->where('analysis_type_id', $this->analysisTypeId)
                        ->update(['level' => $index + 1]);
                        
                    Log::info('Updated element', [
                        'elementId' => $elementId,
                        'newLevel' => $index + 1,
                        'rowsAffected' => $updated
                    ]);
                }
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