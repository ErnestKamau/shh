<?php

namespace App\Livewire\Analysis;

use Livewire\Component;
use Livewire\WithPagination;
use App\AnalysisType;
use App\AnalysisElements;
use App\Lab;
use App\Analyte;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AnalysisTypeManager extends Component
{
    use WithPagination;

    // Sample Type ID
    public $sampleTypeId;

    // Analysis Types Management
    public $selectedAnalysisType = null;
    public $showElements = false;
    public $editingAnalysisType = null;
    public $showAnalysisTypeModal = false;
    
    // Analysis Type Form
    public $analysisTypeForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'lab_id' => null,
        'level' => 1,
        'active' => true,
        'reporting_time' => null
    ];

    // Elements Management
    public $elements = [];
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
        'remedy_header_id' => null
    ];

    // Supporting Data
    public $labs = [];
    public $analytes = [];
    public $methods = [];
    public $equipment = [];
    public $operators = [];
    public $remedyHeaders = [];

    // Search and Filter
    public $search = '';
    public $labFilter = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'analysisTypeForm.name' => 'required|string|max:255',
        'analysisTypeForm.code' => 'required|string|max:255',
        'analysisTypeForm.lab_id' => 'required|exists:labs,id',
        'elementForm.analyte_id' => 'required|exists:analytes,id',
        'elementForm.method' => 'nullable|exists:analysis_methods,id',
        'elementForm.equipment_id' => 'nullable|exists:equipment,id',
        'elementForm.operator_id' => 'nullable|exists:users,id',
    ];

    protected $messages = [
        'analysisTypeForm.name.required' => 'Analysis type name is required.',
        'analysisTypeForm.code.required' => 'Analysis type code is required.',
        'analysisTypeForm.lab_id.required' => 'Lab selection is required.',
        'elementForm.analyte_id.required' => 'Analyte selection is required.',
    ];

    public function mount($sampleTypeId = null)
    {
        $this->sampleTypeId = $sampleTypeId;
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->labs = Lab::all();
        $this->analytes = Analyte::where('active', 1)->get();
        $this->methods = AnalysisMethod::where('active', 1)->get();
        $this->equipment = Equipment::where('active', 1)->get();
        $this->operators = User::where('active', 1)->get();
        $this->remedyHeaders = \App\Models\RemedyHeader::all();
    }

    public function getAnalysisTypesProperty()
    {
        $query = AnalysisType::with(['analysis_elements' => function($q) {
            $q->with(['analyte', 'mmethod', 'ltmethod', 'equipment', 'operator'])
              ->orderBy('level', 'asc');
        }, 'lab']);

        // Filter by sample type if provided
        if ($this->sampleTypeId) {
            $query->where('sample_type_id', $this->sampleTypeId);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->labFilter) {
            $query->where('lab_id', $this->labFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        return $query->orderBy('name', 'asc')->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLabFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->labFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Analysis Type Methods
    public function showCreateAnalysisTypeModal()
    {
        $this->resetAnalysisTypeForm();
        $this->showAnalysisTypeModal = true;
    }

    public function showEditAnalysisTypeModal($id)
    {
        $analysisType = AnalysisType::findOrFail($id);
        $this->analysisTypeForm = [
            'name' => $analysisType->name,
            'code' => $analysisType->code,
            'description' => $analysisType->description,
            'lab_id' => $analysisType->lab_id,
            'level' => $analysisType->level,
            'active' => $analysisType->active,
            'reporting_time' => $analysisType->reporting_time
        ];
        $this->editingAnalysisType = $id;
        $this->showAnalysisTypeModal = true;
    }

    public function saveAnalysisType()
    {
        $this->validate([
            'analysisTypeForm.name' => 'required|string|max:255',
            'analysisTypeForm.code' => 'required|string|max:255',
            'analysisTypeForm.lab_id' => 'required|exists:labs,id',
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingAnalysisType) {
                $analysisType = AnalysisType::findOrFail($this->editingAnalysisType);
                $analysisType->update([
                    'name' => $this->analysisTypeForm['name'],
                    'code' => $this->analysisTypeForm['code'],
                    'description' => $this->analysisTypeForm['description'],
                    'lab_id' => $this->analysisTypeForm['lab_id'],
                    'level' => $this->analysisTypeForm['level'],
                    'active' => $this->analysisTypeForm['active'],
                    'reporting_time' => $this->analysisTypeForm['reporting_time'],
                ]);
                $this->message = 'Analysis type updated successfully!';
            } else {
                AnalysisType::create([
                    'name' => $this->analysisTypeForm['name'],
                    'code' => $this->analysisTypeForm['code'],
                    'description' => $this->analysisTypeForm['description'],
                    'sample_type_id' => $this->sampleTypeId,
                    'lab_id' => $this->analysisTypeForm['lab_id'],
                    'level' => $this->analysisTypeForm['level'],
                    'active' => $this->analysisTypeForm['active'],
                    'reporting_time' => $this->analysisTypeForm['reporting_time'],
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Analysis type created successfully!';
            }

            DB::commit();
            $this->loadAnalysisTypes();
            $this->closeAnalysisTypeModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteAnalysisType($id)
    {
        try {
            DB::beginTransaction();

            $analysisType = AnalysisType::findOrFail($id);
            $analysisType->analysis_elements()->delete();
            $analysisType->delete();

            DB::commit();
            $this->loadAnalysisTypes();
            $this->message = 'Analysis type deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function selectAnalysisType($id)
    {
        $this->selectedAnalysisType = $id;
        $this->loadElements();
        $this->showElements = true;
    }

    public function closeAnalysisTypeModal()
    {
        $this->showAnalysisTypeModal = false;
        $this->resetAnalysisTypeForm();
    }

    public function resetAnalysisTypeForm()
    {
        $this->analysisTypeForm = [
            'name' => '',
            'code' => '',
            'description' => '',
            'lab_id' => null,
            'level' => 1,
            'active' => true,
            'reporting_time' => null
        ];
        $this->editingAnalysisType = null;
    }

    public function loadAnalysisTypes()
    {
        // This method is called to refresh the analysis types list
        // The actual data loading is handled by the getAnalysisTypesProperty computed property
        $this->dispatch('$refresh');
    }

    // Element Methods
    public function loadElements()
    {
        if ($this->selectedAnalysisType) {
            $this->elements = AnalysisElements::where('analysis_type_id', $this->selectedAnalysisType)
                ->with(['analyte', 'mmethod', 'ltmethod', 'equipment', 'operator'])
                ->orderBy('level', 'asc')
                ->get();
        }
    }

    public function showCreateElementModal()
    {
        $this->resetElementForm();
        $this->showElementModal = true;
    }

    public function showEditElementModal($id)
    {
        $element = AnalysisElements::findOrFail($id);
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
            'remedy_header_id' => $element->remedy_header_id
        ];
        $this->editingElement = $id;
        $this->showElementModal = true;
    }

    public function saveElement()
    {
        $this->validate([
            'elementForm.analyte_id' => 'required|exists:analytes,id',
            'elementForm.method' => 'nullable|exists:analysis_methods,id',
            'elementForm.equipment_id' => 'nullable|exists:equipment,id',
            'elementForm.operator_id' => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingElement) {
                $element = AnalysisElements::findOrFail($this->editingElement);
                $element->update([
                    'analyte_id' => $this->elementForm['analyte_id'],
                    'method' => $this->elementForm['method'],
                    'equipment_id' => $this->elementForm['equipment_id'],
                    'operator_id' => $this->elementForm['operator_id'],
                    'reporting_unit' => $this->elementForm['reporting_unit'],
                    'decimal_places' => $this->elementForm['decimal_places'],
                    'significant_figures' => $this->elementForm['significant_figures'],
                    'lod' => $this->elementForm['lod'],
                    'hod' => $this->elementForm['hod'],
                    'level' => $this->elementForm['level'],
                    'active' => $this->elementForm['active'],
                    'non_detectable' => $this->elementForm['non_detectable'],
                    'non_accredited' => $this->elementForm['non_accredited'],
                    'show_on_report' => $this->elementForm['show_on_report']
                ]);
                $this->message = 'Analysis element updated successfully!';
            } else {
                AnalysisElements::create([
                    'analyte_id' => $this->elementForm['analyte_id'],
                    'analysis_type_id' => $this->selectedAnalysisType,
                    'method' => $this->elementForm['method'],
                    'equipment_id' => $this->elementForm['equipment_id'],
                    'operator_id' => $this->elementForm['operator_id'],
                    'reporting_unit' => $this->elementForm['reporting_unit'],
                    'decimal_places' => $this->elementForm['decimal_places'],
                    'significant_figures' => $this->elementForm['significant_figures'],
                    'lod' => $this->elementForm['lod'],
                    'hod' => $this->elementForm['hod'],
                    'level' => $this->elementForm['level'],
                    'active' => $this->elementForm['active'],
                    'non_detectable' => $this->elementForm['non_detectable'],
                    'non_accredited' => $this->elementForm['non_accredited'],
                    'show_on_report' => $this->elementForm['show_on_report'],
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Analysis element created successfully!';
            }

            DB::commit();
            $this->loadElements();
            $this->closeElementModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteElement($id)
    {
        try {
            AnalysisElements::findOrFail($id)->delete();
            $this->loadElements();
            $this->message = 'Analysis element deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeElementModal()
    {
        $this->showElementModal = false;
        $this->resetElementForm();
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
            'remedy_header_id' => null
        ];
        $this->editingElement = null;
    }

    public function updatedElementFormRecommendRemedies()
    {
        if (!$this->elementForm['recommend_remedies']) {
            $this->elementForm['remedy_header_id'] = null;
        }
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.analysis.analysis-type-manager');
    }
}