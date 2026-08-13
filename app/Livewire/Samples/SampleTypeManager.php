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
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Livewire\Concerns\HandlesLabTaxonomyBulkImport;
use App\Models\Equipments\Equipment;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SampleTypeManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use HandlesLabTaxonomyBulkImport;
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Sample Types Management
    public $selectedSampleType = null;
    public $showAnalysisTypes = false;
    public $editingSampleType = null;
    public $showSampleTypeModal = false;
    
    // Lab Sections
    public $labSections = [];
    public $labSectionSearch = '';
    public $showLabSectionDropdown = false;

    // Sample Type Form
    public $sampleTypeForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'category_id' => null,
        'rating_header_id' => null,
        'report_format_id' => null,
        'default_product_id' => null,
        'disposal_count' => 0,
        'active' => true,
        'is_results_attachable' => false,
        'exhibit_returned_on_reception' => false,
        'sample_analysis_stage_ids' => []
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
    public $companyProducts = [];
    public $labs = [];
    public $analytes = [];
    public $methods = [];
    public $equipment = [];
    public $operators = [];

    // Search and Filter
    public $search = '';
    public $categoryFilter = '';

    // Searchable dropdown properties
    public $categorySearch = '';
    public $showCategoryDropdown = false;
    public $ratingHeaderSearch = '';
    public $showRatingHeaderDropdown = false;
    public $reportFormatSearch = '';
    public $showReportFormatDropdown = false;
    public $companyProductSearch = '';
    public $showCompanyProductDropdown = false;
    public $labSearch = '';
    public $showLabDropdown = false;

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // Category Creation Modal
    public $showCategoryModal = false;
    public $categoryForm = [
        'sample_type_category' => '',
        'active' => true,
    ];

    // Product Creation Modal
    public $showProductModal = false;
    public $productForm = [
        'name' => '',
        'active' => true,
    ];

    protected $rules = [
        'sampleTypeForm.name' => 'required|string|max:255',
        'sampleTypeForm.code' => 'required|string|max:255|unique:sample_types,code',
        'sampleTypeForm.description' => 'nullable|string|max:255',
        'sampleTypeForm.category_id' => 'required|exists:sample_type_categories,id',
        'sampleTypeForm.disposal_count' => 'nullable|integer|min:0',
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
        'sampleTypeForm.category_id.required' => 'Sample type category is required.',
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
        $this->companyProducts = \App\Models\CRM\CompanyProduct::where('active', 1)->get();
        $this->labs = Lab::all();
        $this->analytes = Analyte::where('active', 1)->get();
        $this->methods = AnalysisMethod::where('active', 1)->get();
        $this->equipment = Equipment::where('active', 1)->get();
        $this->operators = User::where('active', 1)->get();
        $this->labSections = \App\SampleAnalysisStage::where('active', 1)
            ->where('is_sample_stage', 0)
            ->orderBy('name')
            ->get();
    }

    public function getSampleTypesProperty()
    {
        $query = SampleType::with(['analysis_types' => function($q) {
            $q->orderBy('level', 'asc');
        }, 'reportFormat', 'sampleAnalysisStages', 'sampleTypeCategory']);

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) $this->search);
        }

        if ($this->categoryFilter !== '' && $this->categoryFilter !== null) {
            $query->where('sample_type_category', $this->categoryFilter);
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

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Sample Type Methods
    public function showCreateSampleTypeModal()
    {
        $this->resetSampleTypeForm();
        if (empty($this->sampleTypeForm['category_id'])) {
            $this->sampleTypeForm['category_id'] = SampleTypeCategory::query()->orderBy('id')->value('id');
        }
        $this->showSampleTypeModal = true;
    }

    public function showEditSampleTypeModal($id)
    {
        $sampleType = SampleType::findOrFail($id);
        $supportsExhibitReturnedOnReception = $this->supportsExhibitReturnedOnReception();
        $this->sampleTypeForm = [
            'name' => $sampleType->name,
            'code' => $sampleType->code,
            'description' => $sampleType->description,
            'category_id' => $sampleType->sample_type_category,
            'rating_header_id' => $sampleType->rating_header_id,
            'report_format_id' => $sampleType->report_format_id,
            'default_product_id' => $sampleType->default_product_id,
            'disposal_count' => $sampleType->disposal_count,
            'active' => (bool)$sampleType->active,
            'is_results_attachable' => (bool)$sampleType->is_results_attachable,
            'exhibit_returned_on_reception' => $supportsExhibitReturnedOnReception ? (bool) $sampleType->exhibit_returned_on_reception : false,
            'sample_analysis_stage_ids' => $sampleType->sampleAnalysisStages->pluck('id')->toArray(),
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

            $categoryId = $this->sampleTypeForm['category_id']
                ?: $this->ensureSampleTypeCategoryId();

            if ($this->editingSampleType) {
                $sampleType = SampleType::findOrFail($this->editingSampleType);
                $payload = [
                    'name' => $this->sampleTypeForm['name'],
                    'code' => $this->sampleTypeForm['code'],
                    'description' => $this->sampleTypeForm['description'],
                    'sample_type_category' => $categoryId,
                    'rating_header_id' => $this->sampleTypeForm['rating_header_id'],
                    'report_format_id' => $this->sampleTypeForm['report_format_id'],
                    'default_product_id' => $this->sampleTypeForm['default_product_id'],
                    'disposal_count' => $this->sampleTypeForm['disposal_count'] ?? 0,
                    'active' => $this->sampleTypeForm['active'],
                    'is_results_attachable' => $this->sampleTypeForm['is_results_attachable'],
                    'company_id' => getUserCompany(),
                ];

                if ($this->supportsExhibitReturnedOnReception()) {
                    $payload['exhibit_returned_on_reception'] = (bool) ($this->sampleTypeForm['exhibit_returned_on_reception'] ?? false);
                }

                $sampleType->update($payload);

                // Sync with analysis types
                $sampleType->analysis_types()->update(['has_no_result' => $this->sampleTypeForm['is_results_attachable']]);

                $sampleType->sampleAnalysisStages()->sync($this->sampleTypeForm['sample_analysis_stage_ids'] ?? []);
                $this->message = 'Sample type updated successfully!';
            } else {
                $payload = [
                    'name' => $this->sampleTypeForm['name'],
                    'code' => $this->sampleTypeForm['code'],
                    'description' => $this->sampleTypeForm['description'],
                    'sample_type_category' => $categoryId,
                    'rating_header_id' => $this->sampleTypeForm['rating_header_id'],
                    'report_format_id' => $this->sampleTypeForm['report_format_id'],
                    'default_product_id' => $this->sampleTypeForm['default_product_id'],
                    'disposal_count' => $this->sampleTypeForm['disposal_count'] ?? 0,
                    'active' => $this->sampleTypeForm['active'],
                    'is_results_attachable' => $this->sampleTypeForm['is_results_attachable'],
                    'company_id' => getUserCompany(),
                ];

                if ($this->supportsExhibitReturnedOnReception()) {
                    $payload['exhibit_returned_on_reception'] = (bool) ($this->sampleTypeForm['exhibit_returned_on_reception'] ?? false);
                }

                $sampleType = SampleType::create($payload);
                $sampleType->sampleAnalysisStages()->sync($this->sampleTypeForm['sample_analysis_stage_ids'] ?? []);
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

    private function ensureSampleTypeCategoryId(): int
    {
        $existingCategoryId = SampleTypeCategory::query()->orderBy('id')->value('id');

        if ($existingCategoryId) {
            return (int) $existingCategoryId;
        }

        // Some databases in this project do not auto-generate sample_type_categories.id.
        // Create a default category with an explicit ID when table is empty.
        DB::statement('LOCK TABLE sample_type_categories IN EXCLUSIVE MODE');

        $existingGeneral = SampleTypeCategory::query()
            ->where('sample_type_category', 'General')
            ->orderBy('id')
            ->first();

        if ($existingGeneral) {
            $this->categories = SampleTypeCategory::all();
            return (int) $existingGeneral->id;
        }

        $nextId = ((int) SampleTypeCategory::query()->max('id')) + 1;

        $defaultCategory = new SampleTypeCategory();
        $defaultCategory->id = $nextId;
        $defaultCategory->sample_type_category = 'General';
        $defaultCategory->active = true;
        $defaultCategory->save();

        $this->categories = SampleTypeCategory::all();

        return (int) $defaultCategory->id;
    }

    private function supportsExhibitReturnedOnReception(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('sample_types', 'exhibit_returned_on_reception');
        }

        return $hasColumn;
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
            $newSampleType->is_results_attachable = $originalSampleType->is_results_attachable;
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
            'default_product_id' => null,
            'disposal_count' => 0,
            'active' => true,
            'is_results_attachable' => false,
            'exhibit_returned_on_reception' => false,
            'sample_analysis_stage_ids' => []
        ];
        $this->editingSampleType = null;
        $this->categorySearch = '';
        $this->showCategoryDropdown = false;
        $this->ratingHeaderSearch = '';
        $this->showRatingHeaderDropdown = false;
        $this->reportFormatSearch = '';
        $this->showReportFormatDropdown = false;
        $this->companyProductSearch = '';
        $this->showCompanyProductDropdown = false;
        $this->labSectionSearch = '';
        $this->showLabSectionDropdown = false;
        $this->resetValidation();
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
        $this->labSearch = '';
        $this->showLabDropdown = false;
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

    // Category searchable dropdown methods
    public function selectCategory($categoryId): void
    {
        $this->sampleTypeForm['category_id'] = $categoryId;
        $this->categorySearch = '';
        $this->showCategoryDropdown = false;
    }

    public function updatedCategorySearch(): void
    {
        $this->showCategoryDropdown = !empty($this->categorySearch);
    }

    public function getFilteredCategoriesProperty()
    {
        $query = SampleTypeCategory::query();
        
        if (!empty($this->categorySearch)) {
            $this->applyCaseInsensitiveSearch($query, ['sample_type_category'], (string) $this->categorySearch);
        }
        
        return $query->limit(10)->get();
    }

    public function getSelectedCategoryProperty()
    {
        if (empty($this->sampleTypeForm['category_id'])) {
            return null;
        }
        
        return SampleTypeCategory::find($this->sampleTypeForm['category_id']);
    }

    // Rating Header searchable dropdown methods
    public function selectRatingHeader($ratingHeaderId): void
    {
        $this->sampleTypeForm['rating_header_id'] = $ratingHeaderId;
        $this->ratingHeaderSearch = '';
        $this->showRatingHeaderDropdown = false;
    }

    public function updatedRatingHeaderSearch(): void
    {
        $this->showRatingHeaderDropdown = !empty($this->ratingHeaderSearch);
    }

    public function getFilteredRatingHeadersProperty()
    {
        if (empty($this->ratingHeaderSearch)) {
            return [];
        }
        
        $query = \App\Models\RatingHeader::query();
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->ratingHeaderSearch);

        return $query->limit(10)->get();
    }

    public function getSelectedRatingHeaderProperty()
    {
        if (empty($this->sampleTypeForm['rating_header_id'])) {
            return null;
        }
        
        return \App\Models\RatingHeader::find($this->sampleTypeForm['rating_header_id']);
    }

    // Report Format searchable dropdown methods
    public function selectReportFormat($reportFormatId): void
    {
        $this->sampleTypeForm['report_format_id'] = $reportFormatId;
        $this->reportFormatSearch = '';
        $this->showReportFormatDropdown = false;
    }

    public function updatedReportFormatSearch(): void
    {
        $this->showReportFormatDropdown = !empty($this->reportFormatSearch);
    }

    public function getFilteredReportFormatsProperty()
    {
        if (empty($this->reportFormatSearch)) {
            return [];
        }
        
        $query = \App\ReportFormat::query();
        $this->applyCaseInsensitiveSearch($query, ['report_name', 'report_code'], (string) $this->reportFormatSearch);

        return $query->limit(10)->get();
    }

    public function getSelectedReportFormatProperty()
    {
        if (empty($this->sampleTypeForm['report_format_id'])) {
            return null;
        }
        
        return \App\ReportFormat::find($this->sampleTypeForm['report_format_id']);
    }

    // Lab searchable dropdown methods (for Analysis Type modal)
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
        
        $query = Lab::query()->where('active', 1);
        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->labSearch);

        return $query->limit(10)->get();
    }

    public function getSelectedLabProperty()
    {
        if (empty($this->analysisTypeForm['lab_id'])) {
            return null;
        }
        
        return Lab::find($this->analysisTypeForm['lab_id']);
    }

    // Company Product searchable dropdown methods
    public function selectCompanyProduct($companyProductId): void
    {
        $this->sampleTypeForm['default_product_id'] = $companyProductId;
        $this->companyProductSearch = '';
        $this->showCompanyProductDropdown = false;
    }

    public function updatedCompanyProductSearch(): void
    {
        $this->showCompanyProductDropdown = !empty($this->companyProductSearch);
    }

    public function getFilteredCompanyProductsProperty()
    {
        $query = \App\Models\CRM\CompanyProduct::where('active', 1);
        
        if (!empty($this->companyProductSearch)) {
            $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->companyProductSearch);
        }
        
        return $query->limit(10)->get();
    }

    public function getSelectedCompanyProductProperty()
    {
        if (empty($this->sampleTypeForm['default_product_id'])) {
            return null;
        }
        
        return \App\Models\CRM\CompanyProduct::find($this->sampleTypeForm['default_product_id']);
    }

    // Category Creation Methods
    public function showCreateCategoryModal(): void
    {
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
        $this->showCategoryDropdown = false;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    public function resetCategoryForm(): void
    {
        $this->categoryForm = [
            'sample_type_category' => '',
            'active' => true,
        ];
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryForm.sample_type_category' => 'required|string|max:255|unique:sample_type_categories,sample_type_category',
        ]);

        try {
            DB::beginTransaction();

            $category = SampleTypeCategory::create([
                'sample_type_category' => $this->categoryForm['sample_type_category'],
                'active' => $this->categoryForm['active'] ?? true,
            ]);

            DB::commit();

            // Auto-select the newly created category
            $this->sampleTypeForm['category_id'] = $category->id;
            
            // Reload categories
            $this->categories = SampleTypeCategory::all();

            $this->message = 'Category created successfully!';
            $this->messageType = 'success';
            $this->closeCategoryModal();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Product Creation Methods
    public function showCreateProductModal(): void
    {
        $this->resetProductForm();
        $this->showProductModal = true;
        $this->showCompanyProductDropdown = false;
    }

    public function closeProductModal(): void
    {
        $this->showProductModal = false;
        $this->resetProductForm();
    }

    public function resetProductForm(): void
    {
        $this->productForm = [
            'name' => '',
            'active' => true,
        ];
    }

    public function saveProduct(): void
    {
        $this->validate([
            'productForm.name' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $product = \App\Models\CRM\CompanyProduct::create([
                'name' => $this->productForm['name'],
                'active' => $this->productForm['active'] ?? true,
            ]);

            DB::commit();

            // Auto-select the newly created product
            $this->sampleTypeForm['default_product_id'] = $product->id;
            
            // Reload company products
            $this->companyProducts = \App\Models\CRM\CompanyProduct::where('active', 1)->get();

            $this->message = 'Product created successfully!';
            $this->messageType = 'success';
            $this->closeProductModal();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    protected function labTaxonomyBulkImportFormType(): string
    {
        return 'sample_type';
    }

    protected function labTaxonomyBulkImportEntityLabel(): string
    {
        return 'Sample Type';
    }

    public function render()
    {
        return view('livewire.samples.sample-type-manager');
    }

    public function getFilteredLabSectionsProperty()
    {
        if (empty($this->labSectionSearch)) {
            return $this->labSections;
        }

        return $this->labSections->filter(function($section) {
            return stripos($section->name, $this->labSectionSearch) !== false;
        });
    }

    public function getSelectedLabSectionsProperty()
    {
        if (empty($this->sampleTypeForm['sample_analysis_stage_ids'])) {
            return collect();
        }

        return $this->labSections->whereIn('id', $this->sampleTypeForm['sample_analysis_stage_ids']);
    }

    public function toggleLabSection($id)
    {
        if (in_array($id, $this->sampleTypeForm['sample_analysis_stage_ids'])) {
            $this->sampleTypeForm['sample_analysis_stage_ids'] = array_diff($this->sampleTypeForm['sample_analysis_stage_ids'], [$id]);
        } else {
            $this->sampleTypeForm['sample_analysis_stage_ids'][] = $id;
        }
        $this->labSectionSearch = '';
        
        // Re-index array
        $this->sampleTypeForm['sample_analysis_stage_ids'] = array_values($this->sampleTypeForm['sample_analysis_stage_ids']);
    }
}