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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AnalysisTypeManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

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
        'lab_ids' => [],
        'level' => 1,
        'active' => true,
        'has_no_result' => false,
        'reporting_time' => null,
        'include_hygiene_score' => false,
        'include_sanitizer_efficiency' => false,
        'invoicable_item_id' => null,
        'procedure_worksheet_id' => null,
        'uses_grouped_procedures' => false,
        'grouped_worksheet_holder_id' => null,
        'hybrid_worksheet_id' => null,
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
        'analysisTypeForm.lab_ids' => 'required|array|min:1',
        'analysisTypeForm.lab_ids.*' => 'uuid|exists:labs,id',
        'elementForm.analyte_id' => 'required|exists:analytes,id',
        'elementForm.method' => 'nullable|exists:analysis_methods,id',
        'elementForm.equipment_id' => 'nullable|exists:equipment,id',
        'elementForm.operator_id' => 'nullable|exists:users,id',
    ];

    protected $messages = [
        'analysisTypeForm.name.required' => 'Analysis type name is required.',
        'analysisTypeForm.code.required' => 'Analysis type code is required.',
        'analysisTypeForm.lab_ids.required' => 'At least one lab is required.',
        'analysisTypeForm.lab_ids.min' => 'At least one lab is required.',
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
                }, 'lab', 'labs', 'procedureWorksheet']);

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
        $invoicableItemId = null;
        if (Str::isUuid((string) $analysisType->id)) {
            $invoicableItemId = $analysisType->invoicableItems()->first()?->id;
        }
        
        $labIds = [];
        if (Schema::hasTable('analysis_type_lab_relation')) {
            $labIds = $analysisType->labs()->pluck('labs.id')->toArray();
        }
        if (empty($labIds) && !empty($analysisType->lab_id)) {
            $labIds = [(string) $analysisType->lab_id];
        }

        $this->analysisTypeForm = [
            'name' => $analysisType->name,
            'code' => $analysisType->code,
            'description' => $analysisType->description,
            'lab_section_id' => $analysisType->lab_section_id,
            'lab_id' => $analysisType->lab_id,
            'lab_ids' => $labIds,
            'level' => $analysisType->level,
            'active' => $analysisType->active,
            'has_no_result' => (bool) $analysisType->has_no_result,
            'reporting_time' => $analysisType->reporting_time,
            'include_hygiene_score' => (bool) $analysisType->include_hygiene_score,
            'include_sanitizer_efficiency' => (bool) $analysisType->include_sanitizer_efficiency,
            'invoicable_item_id' => $invoicableItemId,
            'procedure_worksheet_id' => $analysisType->procedure_worksheet_id,
            'uses_grouped_procedures' => ! empty($analysisType->grouped_worksheet_holder_id) || ! empty($analysisType->hybrid_worksheet_id),
            'grouped_worksheet_holder_id' => $analysisType->grouped_worksheet_holder_id,
            'hybrid_worksheet_id' => $analysisType->hybrid_worksheet_id,
        ];
        $this->editingAnalysisType = $id;
        $this->showAnalysisTypeModal = true;
    }

    public function saveAnalysisType()
    {
        $this->validate([
            'analysisTypeForm.name' => 'required|string|max:255',
            'analysisTypeForm.code' => 'required|string|max:255',
            'analysisTypeForm.lab_ids' => 'required|array|min:1',
            'analysisTypeForm.lab_ids.*' => 'uuid|exists:labs,id',
            'analysisTypeForm.grouped_worksheet_holder_id' => 'nullable|uuid|exists:grouped_worksheet_holders,id',
            'analysisTypeForm.hybrid_worksheet_id' => 'nullable|uuid|exists:hybrid_worksheets,id',
            'analysisTypeForm.procedure_worksheet_id' => 'nullable|uuid|exists:procedure_worksheets,id',
        ]);

        $usesGroupedProcedures = (bool) ($this->analysisTypeForm['uses_grouped_procedures'] ?? false);
        $groupedHolderId = $usesGroupedProcedures ? ($this->analysisTypeForm['grouped_worksheet_holder_id'] ?? null) : null;
        $hybridWorksheetId = $usesGroupedProcedures ? ($this->analysisTypeForm['hybrid_worksheet_id'] ?? null) : null;

        if ($usesGroupedProcedures && empty($groupedHolderId) && empty($hybridWorksheetId)) {
            $this->addError('analysisTypeForm.grouped_worksheet_holder_id', 'Select a grouped pipeline or hybrid worksheet.');
            $this->messageType = 'error';

            return;
        }

        if (! empty($groupedHolderId) && ! empty($hybridWorksheetId)) {
            $this->addError('analysisTypeForm.grouped_worksheet_holder_id', 'Select only one grouped pipeline or hybrid worksheet.');
            $this->messageType = 'error';

            return;
        }

        if (($usesGroupedProcedures && (! empty($groupedHolderId) || ! empty($hybridWorksheetId)))
            && ! empty($this->analysisTypeForm['procedure_worksheet_id'])) {
            $this->addError('analysisTypeForm.grouped_worksheet_holder_id', 'Use either grouped/hybrid worksheets or a single procedure worksheet, not both.');
            $this->messageType = 'error';

            return;
        }

        $procedureWorksheetId = $usesGroupedProcedures
            ? null
            : ($this->analysisTypeForm['procedure_worksheet_id'] ?? null);

        $selectedLabIds = array_values(array_unique(array_filter((array) ($this->analysisTypeForm['lab_ids'] ?? []))));
        $primaryLabId = $selectedLabIds[0] ?? null;

        try {
            DB::beginTransaction();

            if ($this->editingAnalysisType) {
                $analysisType = AnalysisType::findOrFail($this->editingAnalysisType);
                $analysisType->update([
                    'name' => $this->analysisTypeForm['name'],
                    'code' => $this->analysisTypeForm['code'],
                    'description' => $this->analysisTypeForm['description'],
                    'lab_section_id' => $this->analysisTypeForm['lab_section_id'],
                    'lab_id' => $primaryLabId,
                    'level' => $this->analysisTypeForm['level'],
                    'active' => $this->analysisTypeForm['active'],
                    'has_no_result' => $this->analysisTypeForm['has_no_result'] ?? false,
                    'reporting_time' => $this->analysisTypeForm['reporting_time'],
                    'include_hygiene_score' => $this->analysisTypeForm['include_hygiene_score'] ?? false,
                    'include_sanitizer_efficiency' => $this->analysisTypeForm['include_sanitizer_efficiency'] ?? false,
                    'procedure_worksheet_id' => $procedureWorksheetId,
                    'grouped_worksheet_holder_id' => $groupedHolderId,
                    'hybrid_worksheet_id' => $hybridWorksheetId,
                ]);
                
                // Cascade update to elements if "Has No Result Captured" and worksheet is set
                if (($this->analysisTypeForm['has_no_result'] ?? false) && ! empty($procedureWorksheetId)) {
                    AnalysisElements::whereRaw('analysis_type_id::text = ?', [(string) $analysisType->id])->update([
                        'procedure_worksheet_id' => $procedureWorksheetId,
                    ]);
                }

                // Update invoicable item mapping
                $this->updateInvoicableItemMapping($analysisType);
                $this->syncAnalysisTypeLabs($analysisType, $selectedLabIds);
                
                $this->message = 'Analysis type updated successfully!';
            } else {
                $analysisType = AnalysisType::create([
                    'name' => $this->analysisTypeForm['name'],
                    'code' => $this->analysisTypeForm['code'],
                    'description' => $this->analysisTypeForm['description'],
                    'sample_type_id' => $this->sampleTypeId,
                    'lab_section_id' => $this->analysisTypeForm['lab_section_id'],
                    'lab_id' => $primaryLabId,
                    'level' => $this->analysisTypeForm['level'],
                    'active' => $this->analysisTypeForm['active'],
                    'has_no_result' => $this->analysisTypeForm['has_no_result'] ?? false,
                    'reporting_time' => $this->analysisTypeForm['reporting_time'],
                    'include_hygiene_score' => $this->analysisTypeForm['include_hygiene_score'] ?? false,
                    'include_sanitizer_efficiency' => $this->analysisTypeForm['include_sanitizer_efficiency'] ?? false,
                    'company_id' => getUserCompany(),
                    'procedure_worksheet_id' => $procedureWorksheetId,
                    'grouped_worksheet_holder_id' => $groupedHolderId,
                    'hybrid_worksheet_id' => $hybridWorksheetId,
                ]);
                
                // Create invoicable item mapping
                $this->updateInvoicableItemMapping($analysisType);
                $this->syncAnalysisTypeLabs($analysisType, $selectedLabIds);
                
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
            AnalysisElements::whereRaw('analysis_type_id::text = ?', [(string) $analysisType->id])->delete();
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
            'lab_ids' => [],
            'level' => 1,
            'active' => true,
            'has_no_result' => false,
            'reporting_time' => null,
            'include_hygiene_score' => false,
            'include_sanitizer_efficiency' => false,
            'invoicable_item_id' => null,
            'procedure_worksheet_id' => null,
            'uses_grouped_procedures' => false,
            'grouped_worksheet_holder_id' => null,
            'hybrid_worksheet_id' => null,
        ];
        $this->editingAnalysisType = null;
        $this->labSearch = '';
        $this->showLabDropdown = false;
        $this->labSectionSearch = '';
        $this->showLabSectionDropdown = false;
        $this->invoicableItemSearch = '';
        $this->showInvoicableItemDropdown = false;
        $this->procedureWorksheetSearch = '';
        $this->showProcedureWorksheetDropdown = false;
        $this->pipelineWorksheetSearch = '';
        $this->showPipelineWorksheetDropdown = false;
    }

    public function updatedAnalysisTypeFormUsesGroupedProcedures($value): void
    {
        if (! $value) {
            $this->analysisTypeForm['grouped_worksheet_holder_id'] = null;
            $this->analysisTypeForm['hybrid_worksheet_id'] = null;
            $this->pipelineWorksheetSearch = '';
            $this->showPipelineWorksheetDropdown = false;
        }
    }

    /**
     * Update the analysis type to invoicable item mapping
     */
    protected function updateInvoicableItemMapping($analysisType)
    {
        // Legacy environments may still have integer analysis type IDs while the pivot uses UUIDs.
        // Skip pivot sync to avoid PostgreSQL UUID cast errors until IDs are normalized.
        if (!Str::isUuid((string) $analysisType->id)) {
            return;
        }

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
            $this->elements = AnalysisElements::whereRaw('analysis_type_id::text = ?', [(string) $this->selectedAnalysisType])
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
                $analysisType = AnalysisType::find($this->selectedAnalysisType);
                AnalysisElements::create([
                    'analyte_id' => $this->elementForm['analyte_id'],
                    'analysis_type_id' => $this->selectedAnalysisType,
                    'procedure_worksheet_id' => $analysisType->procedure_worksheet_id ?? null,
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
        $labId = (string) $labId;
        $selected = (array) ($this->analysisTypeForm['lab_ids'] ?? []);

        if (in_array($labId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn ($id): bool => (string) $id !== $labId));
        } else {
            $selected[] = $labId;
            $selected = array_values(array_unique($selected));
        }

        $this->analysisTypeForm['lab_ids'] = $selected;
        $this->analysisTypeForm['lab_id'] = $selected[0] ?? null;
        $this->labSearch = '';
        $this->showLabDropdown = true;
    }

    public function updatedLabSearch(): void
    {
        $this->showLabDropdown = true;
    }

    public function getFilteredLabsProperty()
    {
        $query = Lab::query()
            ->where('active', 1);

        if (!empty($this->labSearch)) {
            $query->where(function ($q): void {
                $q->where('name', 'like', '%' . $this->labSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->labSearch . '%');
            });
        }

        return $query->orderBy('name')->limit(20)->get();
    }

    public function getSelectedLabProperty()
    {
        if (empty($this->analysisTypeForm['lab_id'])) {
            return null;
        }
        
        return Lab::find($this->analysisTypeForm['lab_id']);
    }

    public function getSelectedLabsProperty()
    {
        $ids = array_values(array_unique(array_filter((array) ($this->analysisTypeForm['lab_ids'] ?? []))));
        if (empty($ids)) {
            return collect();
        }

        return Lab::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    protected function syncAnalysisTypeLabs(AnalysisType $analysisType, array $labIds): void
    {
        if (!Schema::hasTable('analysis_type_lab_relation')) {
            return;
        }

        $analysisType->labs()->sync($labIds);
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

    public function clearLabSectionSelection(): void
    {
        $this->analysisTypeForm['lab_section_id'] = null;
        $this->analysisTypeForm['lab_id'] = null;
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

    // Procedure Worksheet Searchable Dropdown
    public $procedureWorksheetSearch = '';
    public $showProcedureWorksheetDropdown = false;

    public function updatedProcedureWorksheetSearch()
    {
        $this->showProcedureWorksheetDropdown = !empty($this->procedureWorksheetSearch);
    }

    public function selectProcedureWorksheet($id)
    {
        $this->analysisTypeForm['procedure_worksheet_id'] = $id;
        $this->analysisTypeForm['grouped_worksheet_holder_id'] = null;
        $this->analysisTypeForm['hybrid_worksheet_id'] = null;
        $this->analysisTypeForm['uses_grouped_procedures'] = false;
        $this->procedureWorksheetSearch = '';
        $this->showProcedureWorksheetDropdown = false;
    }

    public $pipelineWorksheetSearch = '';

    public $showPipelineWorksheetDropdown = false;

    public function updatedPipelineWorksheetSearch(): void
    {
        $this->showPipelineWorksheetDropdown = true;
    }

    public function clearPipelineWorksheetSelection(): void
    {
        $this->analysisTypeForm['grouped_worksheet_holder_id'] = null;
        $this->analysisTypeForm['hybrid_worksheet_id'] = null;
        $this->pipelineWorksheetSearch = '';
    }

    public function selectPipelineWorksheet(string $type, string $id): void
    {
        if ($type === 'grouped') {
            $this->analysisTypeForm['grouped_worksheet_holder_id'] = $id;
            $this->analysisTypeForm['hybrid_worksheet_id'] = null;
        } else {
            $this->analysisTypeForm['hybrid_worksheet_id'] = $id;
            $this->analysisTypeForm['grouped_worksheet_holder_id'] = null;
        }

        $this->analysisTypeForm['procedure_worksheet_id'] = null;
        $this->pipelineWorksheetSearch = '';
        $this->showPipelineWorksheetDropdown = false;
    }

    /**
     * @return array<int, array{type: string, id: string, name: string, subtitle: string|null}>
     */
    public function getFilteredPipelineWorksheetsProperty(): array
    {
        $search = trim($this->pipelineWorksheetSearch);
        $options = [];

        $groupedQuery = \App\Models\GroupedWorksheets\GroupedWorksheetHolder::query()
            ->where('is_active', true);

        if ($search !== '') {
            $groupedQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        foreach ($groupedQuery->orderBy('name')->limit(15)->get() as $holder) {
            $options[] = [
                'type' => 'grouped',
                'id' => (string) $holder->id,
                'name' => $holder->name,
                'subtitle' => 'Grouped pipeline',
            ];
        }

        $hybridQuery = \App\Models\HybridWorksheets\HybridWorksheet::query()
            ->where('is_active', true);

        if ($search !== '') {
            $hybridQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        foreach ($hybridQuery->orderBy('name')->limit(15)->get() as $hybrid) {
            $options[] = [
                'type' => 'hybrid',
                'id' => (string) $hybrid->id,
                'name' => $hybrid->name,
                'subtitle' => 'Hybrid worksheet',
            ];
        }

        usort($options, fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        return array_slice($options, 0, 20);
    }

    public function getSelectedPipelineWorksheetProperty(): ?array
    {
        if (! empty($this->analysisTypeForm['grouped_worksheet_holder_id'])) {
            $holder = \App\Models\GroupedWorksheets\GroupedWorksheetHolder::find($this->analysisTypeForm['grouped_worksheet_holder_id']);
            if ($holder) {
                return [
                    'type' => 'grouped',
                    'id' => (string) $holder->id,
                    'name' => $holder->name,
                    'subtitle' => 'Grouped pipeline',
                ];
            }
        }

        if (! empty($this->analysisTypeForm['hybrid_worksheet_id'])) {
            $hybrid = \App\Models\HybridWorksheets\HybridWorksheet::find($this->analysisTypeForm['hybrid_worksheet_id']);
            if ($hybrid) {
                return [
                    'type' => 'hybrid',
                    'id' => (string) $hybrid->id,
                    'name' => $hybrid->name,
                    'subtitle' => 'Hybrid worksheet',
                ];
            }
        }

        return null;
    }

    public function getFilteredProcedureWorksheetsProperty()
    {
        if (empty($this->procedureWorksheetSearch)) {
            return [];
        }

        return \App\Models\Procedures\ProcedureWorksheet::where('is_active', 1)
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->procedureWorksheetSearch . '%')
                  ->orWhere('description', 'like', '%' . $this->procedureWorksheetSearch . '%');
            })
            ->limit(10)
            ->get();
    }

    public function getSelectedProcedureWorksheetProperty()
    {
        if (empty($this->analysisTypeForm['procedure_worksheet_id'])) {
            return null;
        }

        return \App\Models\Procedures\ProcedureWorksheet::find($this->analysisTypeForm['procedure_worksheet_id']);
    }

    public function render()
    {
        return view('livewire.analysis.analysis-type-manager');
    }
}