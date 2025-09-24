<?php

namespace App\Livewire\Samples;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\SampleType;
use App\AnalysisType;
use App\AnalysisElements;
use App\Company;
use App\SampleTypeCategory;
use App\Lab;
use App\Analyte;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SampleTypeManager extends Component
{
    use WithPagination, WithFileUploads;

    // Sample Types Management
    public $selectedSampleType = null;
    public $showAnalysisTypes = false;
    public $editingSampleType = null;
    public $showSampleTypeModal = false;
    
    // Sample Type Form
    public $sampleTypeForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'category_id' => null,
        'rating_header_id' => null,
        'report_format_id' => null,
        'active' => true
    ];

    // Analysis Types Management
    public $analysisTypes = [];
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
        'active' => true
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
        'show_on_report' => true
    ];

    // Supporting Data
    public $companies = [];
    public $categories = [];
    public $ratingHeaders = [];
    public $reportFormats = [];
    public $labs = [];
    public $analytes = [];
    public $methods = [];
    public $equipment = [];
    public $operators = [];

    // Search and Filter
    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '1'; // Default to active only

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'sampleTypeForm.name' => 'required|string|max:255',
        'sampleTypeForm.code' => 'required|string|max:255|unique:sample_types,code',
        'sampleTypeForm.description' => 'nullable|string|max:255',
        'sampleTypeForm.category_id' => 'required|exists:sample_type_categories,id',
        'analysisTypeForm.name' => 'required|string|max:255',
        'analysisTypeForm.code' => 'required|string|max:255',
        'analysisTypeForm.lab_id' => 'required|exists:labs,id',
        'elementForm.analyte_id' => 'required|exists:analytes,id',
        'elementForm.method' => 'nullable|exists:analysis_methods,id',
        'elementForm.equipment_id' => 'nullable|exists:equipment,id',
        'elementForm.operator_id' => 'nullable|exists:users,id',
    ];

    protected $messages = [
        'sampleTypeForm.name.required' => 'Sample type name is required.',
        'sampleTypeForm.code.required' => 'Sample type code is required.',
        'sampleTypeForm.code.unique' => 'This sample type code already exists.',
        'analysisTypeForm.name.required' => 'Analysis type name is required.',
        'analysisTypeForm.lab_id.required' => 'Lab selection is required.',
        'elementForm.analyte_id.required' => 'Analyte selection is required.',
    ];

    public function mount()
    {
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->companies = Company::all();
        $this->categories = SampleTypeCategory::all();
        $this->ratingHeaders = \App\Models\RatingHeader::all();
        $this->reportFormats = \App\ReportFormat::where('is_active', 1)->get();
        $this->labs = Lab::all();
        $this->analytes = Analyte::where('active', 1)->get();
        $this->methods = AnalysisMethod::where('active', 1)->get();
        $this->equipment = Equipment::where('active', 1)->get();
        $this->operators = User::where('active', 1)->get();
    }

    public function getSampleTypesProperty()
    {
        $query = SampleType::with(['analysis_types' => function($q) {
            $q->orderBy('level', 'asc');
        }, 'reportFormat']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('sample_type_category', $this->categoryFilter);
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

    public function updatedCategoryFilter()
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
        $this->categoryFilter = '';
        $this->statusFilter = '1'; // Reset to active only
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Sample Type Methods
    public function showCreateSampleTypeModal()
    {
        $this->resetSampleTypeForm();
        $this->showSampleTypeModal = true;
    }

    public function showEditSampleTypeModal($id)
    {
        $sampleType = SampleType::findOrFail($id);
        $this->sampleTypeForm = [
            'name' => $sampleType->name,
            'code' => $sampleType->code,
            'description' => $sampleType->description,
            'category_id' => $sampleType->sample_type_category,
            'rating_header_id' => $sampleType->rating_header_id,
            'report_format_id' => $sampleType->report_format_id,
            'active' => $sampleType->active
        ];
        $this->editingSampleType = $id;
        $this->showSampleTypeModal = true;
    }

    public function saveSampleType()
    {
        $this->validate([
            'sampleTypeForm.name' => 'required|string|max:255',
            'sampleTypeForm.code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sample_types', 'code')->ignore($this->editingSampleType)
            ],
            'sampleTypeForm.description' => 'nullable|string|max:255',
            'sampleTypeForm.category_id' => 'required|exists:sample_type_categories,id',
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingSampleType) {
                $sampleType = SampleType::findOrFail($this->editingSampleType);
                $sampleType->update([
                    'name' => $this->sampleTypeForm['name'],
                    'code' => $this->sampleTypeForm['code'],
                    'description' => $this->sampleTypeForm['description'],
                    'sample_type_category' => $this->sampleTypeForm['category_id'],
                    'rating_header_id' => $this->sampleTypeForm['rating_header_id'],
                    'report_format_id' => $this->sampleTypeForm['report_format_id'],
                    'active' => $this->sampleTypeForm['active'],
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Sample type updated successfully!';
            } else {
                SampleType::create([
                    'name' => $this->sampleTypeForm['name'],
                    'code' => $this->sampleTypeForm['code'],
                    'description' => $this->sampleTypeForm['description'],
                    'sample_type_category' => $this->sampleTypeForm['category_id'],
                    'rating_header_id' => $this->sampleTypeForm['rating_header_id'],
                    'report_format_id' => $this->sampleTypeForm['report_format_id'],
                    'active' => $this->sampleTypeForm['active'],
                    'company_id' => getUserCompany(),
                ]);
                $this->message = 'Sample type created successfully!';
            }

            DB::commit();
            $this->closeSampleTypeModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteSampleType($id)
    {
        try {
            DB::beginTransaction();

            $sampleType = SampleType::findOrFail($id);
            
            // Delete associated analysis types and elements
            foreach ($sampleType->analysis_types as $analysisType) {
                $analysisType->analysis_elements()->delete();
                $analysisType->delete();
            }
            
            $sampleType->delete();

            DB::commit();
            $this->message = 'Sample type deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function cloneSampleType($id)
    {
        try {
            DB::beginTransaction();

            $originalSampleType = SampleType::findOrFail($id);
            $newSampleType = $originalSampleType->replicate();
            $newSampleType->name = $originalSampleType->name . ' (Copy)';
            $newSampleType->code = $originalSampleType->code . '_copy';
            $newSampleType->save();

            // Clone analysis types and elements
            foreach ($originalSampleType->analysis_types as $analysisType) {
                $newAnalysisType = $analysisType->replicate();
                $newAnalysisType->sample_type_id = $newSampleType->id;
                $newAnalysisType->save();

                foreach ($analysisType->analysis_elements as $element) {
                    $newElement = $element->replicate();
                    $newElement->analysis_type_id = $newAnalysisType->id;
                    $newElement->save();
                }
            }

            DB::commit();
            $this->message = 'Sample type cloned successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function selectSampleType($id)
    {
        $this->selectedSampleType = $id;
        $this->loadAnalysisTypes();
        $this->showAnalysisTypes = true;
    }

    public function closeSampleTypeModal()
    {
        $this->showSampleTypeModal = false;
        $this->resetSampleTypeForm();
    }

    public function resetSampleTypeForm()
    {
        $this->sampleTypeForm = [
            'name' => '',
            'code' => '',
            'description' => '',
            'category_id' => null,
            'rating_header_id' => null,
            'report_format_id' => null,
            'active' => true
        ];
        $this->editingSampleType = null;
    }

    // Analysis Type Methods
    public function loadAnalysisTypes()
    {
        if ($this->selectedSampleType) {
            $this->analysisTypes = AnalysisType::where('sample_type_id', $this->selectedSampleType)
                ->with(['analysis_elements' => function($q) {
                    $q->with(['analyte', 'mmethod', 'ltmethod', 'equipment', 'operator'])
                      ->orderBy('level', 'asc');
                }])
                ->orderBy('level', 'asc')
                ->get();
        }
    }

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
            'active' => $analysisType->active
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
                ]);
                $this->message = 'Analysis type updated successfully!';
            } else {
                AnalysisType::create([
                    'name' => $this->analysisTypeForm['name'],
                    'code' => $this->analysisTypeForm['code'],
                    'description' => $this->analysisTypeForm['description'],
                    'sample_type_id' => $this->selectedSampleType,
                    'lab_id' => $this->analysisTypeForm['lab_id'],
                    'level' => $this->analysisTypeForm['level'],
                    'active' => $this->analysisTypeForm['active'],
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
            'active' => true
        ];
        $this->editingAnalysisType = null;
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
            'show_on_report' => $element->show_on_report
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
            'show_on_report' => true
        ];
        $this->editingElement = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.samples.sample-type-manager');
    }
}