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
use App\InvoicableItem;
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
        'lab_section_id' => null,
        'lab_id' => null,
        'level' => 1,
        'active' => true,
        'has_no_result' => false,
        'reporting_time' => null,
        'include_hygiene_score' => false,
        'include_sanitizer_efficiency' => false,
        'invoicable_item_id' => null
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
    public $labSections = [];
    public $analytes = [];
    public $methods = [];
    public $equipment = [];
    public $operators = [];
    public $remedyHeaders = [];

    // Search and Filter
    public $search = '';
    public $labFilter = '';
    public $statusFilter = '';

    // Lab searchable dropdown
    public $labSearch = '';
    public $showLabDropdown = false;
    
    // Lab section searchable dropdown
    public $labSectionSearch = '';
    public $showLabSectionDropdown = false;

    // Invoicable item searchable dropdown
    public $invoicableItemSearch = '';
    public $showInvoicableItemDropdown = false;

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
        $this->labSections = \App\SampleAnalysisStage::where('active', 1)->get();
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
        
        // Get the mapped invoicable item if exists
        $invoicableItemId = $analysisType->invoicableItems()->first()?->id;
        
        $this->analysisTypeForm = [
            'name' => $analysisType->name,
            'code' => $analysisType->code,
            'description' => $analysisType->description,
            'lab_section_id' => $analysisType->lab_section_id,
            'lab_id' => $analysisType->lab_id,
            'level' => $analysisType->level,
            'active' => $analysisType->active,
            'has_no_result' => (bool) $analysisType->has_no_result,
            'reporting_time' => $analysisType->reporting_time,
            'include_hygiene_score' => (bool) $analysisType->include_hygiene_score,
            'include_sanitizer_efficiency' => (bool) $analysisType->include_sanitizer_efficiency,
            'invoicable_item_id' => $invoicableItemId
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
                    'lab_section_id' => $this->analysisTypeForm['lab_section_id'],
                    'lab_id' => $this->analysisTypeForm['lab_id'],
                    'level' => $this->analysisTypeForm['level'],
                    'active' => $this->analysisTypeForm['active'],
                    'has_no_result' => $this->analysisTypeForm['has_no_result'] ?? false,
                    'reporting_time' => $this->analysisTypeForm['reporting_time'],
                    'include_hygiene_score' => $this->analysisTypeForm['include_hygiene_score'] ?? false,
                    'include_sanitizer_efficiency' => $this->analysisTypeForm['include_sanitizer_efficiency'] ?? false,
                ]);
                
                // Update invoicable item mapping
                $this->updateInvoicableItemMapping($analysisType);
                
                $this->message = 'Analysis type updated successfully!';
            } else {
                $analysisType = AnalysisType::create([
                    'name' => $this->analysisTypeForm['name'],
                    'code' => $this->analysisTypeForm['code'],
                    'description' => $this->analysisTypeForm['description'],
                    'sample_type_id' => $this->sampleTypeId,
                    'lab_section_id' => $this->analysisTypeForm['lab_section_id'],
                    'lab_id' => $this->analysisTypeForm['lab_id'],
                    'level' => $this->analysisTypeForm['level'],
                    'active' => $this->analysisTypeForm['active'],
                    'has_no_result' => $this->analysisTypeForm['has_no_result'] ?? false,
                    'reporting_time' => $this->analysisTypeForm['reporting_time'],
                    'include_hygiene_score' => $this->analysisTypeForm['include_hygiene_score'] ?? false,
                    'include_sanitizer_efficiency' => $this->analysisTypeForm['include_sanitizer_efficiency'] ?? false,
                    'company_id' => getUserCompany(),
                ]);
                
                // Create invoicable item mapping
                $this->updateInvoicableItemMapping($analysisType);
                
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
            'lab_section_id' => null,
            'lab_id' => null,
            'level' => 1,
            'active' => true,
            'has_no_result' => false,
            'reporting_time' => null,
            'include_hygiene_score' => false,
            'include_sanitizer_efficiency' => false,
            'invoicable_item_id' => null
        ];
        $this->editingAnalysisType = null;
        $this->labSearch = '';
        $this->showLabDropdown = false;
        $this->labSectionSearch = '';
        $this->showLabSectionDropdown = false;
        $this->invoicableItemSearch = '';
        $this->showInvoicableItemDropdown = false;
    }

    /**
     * Update the analysis type to invoicable item mapping
     */
    protected function updateInvoicableItemMapping($analysisType)
    {
        // Sync the invoicable item - this will remove old mappings and add the new one
        if ($this->analysisTypeForm['invoicable_item_id']) {
            $analysisType->invoicableItems()->sync([$this->analysisTypeForm['invoicable_item_id']]);
        } else {
            // Clear the mapping if no item is selected
            $analysisType->invoicableItems()->detach();
        }
    }

    /**
     * Select an invoicable item
     */
    public function selectInvoicableItem($itemId)
    {
        $this->analysisTypeForm['invoicable_item_id'] = $itemId;
        $this->showInvoicableItemDropdown = false;
        $this->invoicableItemSearch = '';
    }

    /**
     * Get filtered invoicable items for dropdown
     */
    public function getFilteredInvoicableItemsProperty()
    {
        $query = InvoicableItem::where('active', 1);

        if ($this->invoicableItemSearch) {
            $query->where(function($q) {
                $q->where('item_code', 'like', "%{$this->invoicableItemSearch}%")
                  ->orWhere('item_name', 'like', "%{$this->invoicableItemSearch}%");
            });
        }

        return $query->orderBy('item_name')->limit(20)->get();
    }

    /**
     * Get selected invoicable item
     */
    public function getSelectedInvoicableItemProperty()
    {
        if (isset($this->analysisTypeForm['invoicable_item_id'])) {
            return InvoicableItem::find($this->analysisTypeForm['invoicable_item_id']);
        }
        return null;
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

    // Lab searchable dropdown methods
    public function selectLab($labId): void
    {
        $this->analysisTypeForm['lab_id'] = $labId;
        $this->labSearch = '';
        $this->showLabDropdown = false;
    }

    public function updatedLabSearch(): void
    {
        $this->showLabDropdown = !empty($this->labSearch);
    }

    public function getFilteredLabsProperty()
    {
        if (empty($this->labSearch)) {
            return [];
        }
        
        return Lab::where('name', 'like', '%' . $this->labSearch . '%')
            ->where('active', 1)
            ->limit(10)
            ->get();
    }

    public function getSelectedLabProperty()
    {
        if (empty($this->analysisTypeForm['lab_id'])) {
            return null;
        }
        
        return Lab::find($this->analysisTypeForm['lab_id']);
    }

    // Lab section searchable dropdown methods
    public function selectLabSection($labSectionId): void
    {
        $labSection = \App\SampleAnalysisStage::find($labSectionId);
        if ($labSection) {
            $this->analysisTypeForm['lab_section_id'] = $labSectionId;
            $this->analysisTypeForm['lab_id'] = $labSection->lab_id;
            $this->labSectionSearch = '';
            $this->showLabSectionDropdown = false;
        }
    }

    public function updatedLabSectionSearch(): void
    {
        $this->showLabSectionDropdown = !empty($this->labSectionSearch);
    }

    public function getFilteredLabSectionsProperty()
    {
        if (empty($this->labSectionSearch)) {
            return [];
        }
        
        return \App\SampleAnalysisStage::where('active', 1)
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->labSectionSearch . '%')
                  ->orWhere('code', 'like', '%' . $this->labSectionSearch . '%');
            })
            ->limit(10)
            ->get();
    }

    public function getSelectedLabSectionProperty()
    {
        if (empty($this->analysisTypeForm['lab_section_id'])) {
            return null;
        }
        
        return \App\SampleAnalysisStage::find($this->analysisTypeForm['lab_section_id']);
    }

    public function render()
    {
        return view('livewire.analysis.analysis-type-manager');
    }
}