<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\LabSubCategory;
use App\LabInventoryCategory;
use App\LabCategoryItems;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\ReportingUnit;
use Illuminate\Support\Facades\Storage;

class LabSubCategoryManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination, WithFileUploads;

    // Sub-Category Management
    public $editingSubCategory = null;
    public $showSubCategoryModal = false;
    
    // Sub-Category Form
    public $subCategoryForm = [
        'name' => '',
        'description' => '',
        'category_id' => null,
        'reporting_unit' => null,
        'rate' => '',
        'image' => null,
    ];

    // Temporary file upload
    public $imageUpload = null;

    // Search and Filter
    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';

    // UI State
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // Supporting Data
    public $categories = [];
    public $reportingUnits = [];

    protected $rules = [
        'subCategoryForm.name' => 'required|string|max:255',
        'subCategoryForm.description' => 'nullable|string',
        'subCategoryForm.category_id' => 'required|uuid|exists:lab_inventory_category,id',
        'subCategoryForm.reporting_unit' => 'required|uuid|exists:reporting_units,id',
        'subCategoryForm.rate' => 'nullable|string|max:255',
        'imageUpload' => 'nullable|image|max:2048',
    ];

    protected $messages = [
        'subCategoryForm.name.required' => 'Name is required.',
        'subCategoryForm.category_id.required' => 'Category selection is required.',
        'subCategoryForm.reporting_unit.required' => 'Unit of measure is required.',
        'imageUpload.image' => 'The file must be an image.',
        'imageUpload.max' => 'The image must not be larger than 2MB.',
    ];

    public function mount(): void
    {
        $this->loadSupportingData();
    }

    public function loadSupportingData(): void
    {
        $this->categories = LabInventoryCategory::where('active', 1)->get();
        $this->reportingUnits = ReportingUnit::where('active', 1)->get();
    }

    public function getSubCategoriesProperty()
    {
        $query = LabSubCategory::with(['category', 'reportingUnit']);

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'description'], (string) $this->search);
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('active', 1);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('active', 0);
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function showCreateSubCategoryModal(): void
    {
        $this->resetSubCategoryForm();
        $this->showSubCategoryModal = true;
        $this->editingSubCategory = null;
        $this->dispatch('subcategory-modal-opened');
    }

    public function showEditSubCategoryModal($subCategoryId): void
    {
        $subCategory = LabSubCategory::findOrFail($subCategoryId);
        $this->subCategoryForm = [
            'name' => $subCategory->name,
            'description' => $subCategory->description,
            'category_id' => $subCategory->category_id,
            'reporting_unit' => $subCategory->reporting_unit,
            'rate' => $subCategory->rate,
            'image' => $subCategory->image,
        ];
        
        $this->editingSubCategory = $subCategory;
        $this->showSubCategoryModal = true;
        $this->dispatch('subcategory-modal-opened');
    }

    public function saveSubCategory(): void
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->subCategoryForm['name'],
                'description' => $this->subCategoryForm['description'],
                'category_id' => $this->subCategoryForm['category_id'],
                'reporting_unit' => $this->subCategoryForm['reporting_unit'],
                'rate' => $this->subCategoryForm['rate'],
            ];

            // Handle image upload
            if ($this->imageUpload) {
                $path = $this->imageUpload->store('subcategory', 'public');
                $data['image'] = '/storage/' . $path;
            } elseif ($this->editingSubCategory && !$this->imageUpload) {
                $data['image'] = $this->editingSubCategory->image;
            }

            if ($this->editingSubCategory) {
                $this->editingSubCategory->update($data);
                $this->message = 'Sub-category updated successfully!';
            } else {
                $data['active'] = 1;
                $data['stock'] = 0;
                LabSubCategory::create($data);
                $this->message = 'Sub-category created successfully!';
            }

            $this->messageType = 'success';
            $this->showSubCategoryModal = false;
            $this->dispatch('subcategory-modal-closed');
            $this->resetSubCategoryForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving sub-category: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteSubCategory($subCategoryId): void
    {
        try {
            $subCategory = LabSubCategory::findOrFail($subCategoryId);
            $subCategory->active = 0;
            $subCategory->save();
            
            $this->message = 'Sub-category deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error deleting sub-category: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function cloneSubCategory($subCategoryId): void
    {
        try {
            $subCategory = LabSubCategory::findOrFail($subCategoryId);
            $items = LabCategoryItems::where('sub_category_id', $subCategory->id)->get();
            
            // Create new subcategory
            $newSubCategory = LabSubCategory::create([
                'name' => $subCategory->name . ' (Copy)',
                'description' => $subCategory->description,
                'image' => $subCategory->image,
                'reporting_unit' => $subCategory->reporting_unit,
                'rate' => $subCategory->rate,
                'category_id' => $subCategory->category_id,
                'active' => 1,
                'stock' => 0,
            ]);
            
            // Clone associated items
            foreach ($items as $item) {
                LabCategoryItems::create([
                    'category_id' => $item->category_id,
                    'unit_measure_id' => $item->unit_measure_id,
                    'amount_used' => $item->amount_used,
                    'reagent_id' => $item->reagent_id,
                    'inventory_sub_category_id' => $item->inventory_sub_category_id,
                    'sub_category_id' => $newSubCategory->id,
                ]);
            }
            
            $this->message = 'Sub-category cloned successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error cloning sub-category: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function resetSubCategoryForm(): void
    {
        $this->subCategoryForm = [
            'name' => '',
            'description' => '',
            'category_id' => null,
            'reporting_unit' => null,
            'rate' => '',
            'image' => null,
        ];
        $this->imageUpload = null;
        $this->editingSubCategory = null;
        $this->resetValidation();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function closeSubCategoryModal(): void
    {
        $this->showSubCategoryModal = false;
        $this->resetSubCategoryForm();
        $this->dispatch('subcategory-modal-closed');
    }

    public function render()
    {
        return view('livewire.lab.lab-sub-category-manager');
    }
}
