<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\LabInventoryCategory;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class LabInventoryCategoryManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination, WithFileUploads;

    // Category Management
    public $editingCategory = null;
    public $showCategoryModal = false;
    
    // Category Form
    public $categoryForm = [
        'name' => '',
        'description' => '',
        'image' => null,
    ];

    // Temporary file upload
    public $imageUpload = null;

    // Search and Filter
    public $search = '';
    public $statusFilter = '';

    // UI State
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'categoryForm.name' => 'required|string|max:255',
        'categoryForm.description' => 'nullable|string',
        'imageUpload' => 'nullable|image|max:2048',
    ];

    protected $messages = [
        'categoryForm.name.required' => 'Category name is required.',
        'imageUpload.image' => 'The file must be an image.',
        'imageUpload.max' => 'The image must not be larger than 2MB.',
    ];

    public function mount()
    {
        // Initial setup
    }

    public function getCategoriesProperty()
    {
        $query = LabInventoryCategory::where('active', 1);

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'description'], (string) $this->search);
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function showCreateCategoryModal()
    {
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
        $this->editingCategory = null;
        $this->dispatch('category-modal-opened');
    }

    public function showEditCategoryModal($categoryId)
    {
        $category = LabInventoryCategory::findOrFail($categoryId);
        $this->categoryForm = [
            'name' => $category->name,
            'description' => $category->description,
            'image' => $category->image,
        ];
        
        $this->editingCategory = $category;
        $this->showCategoryModal = true;
        $this->dispatch('category-modal-opened');
    }

    public function saveCategory()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->categoryForm['name'],
                'description' => $this->categoryForm['description'],
            ];

            // Handle image upload
            if ($this->imageUpload) {
                $path = $this->imageUpload->store('categories', 'public');
                $data['image'] = '/storage/' . $path;
            } elseif ($this->editingCategory && !$this->imageUpload) {
                // Keep existing image if no new upload
                $data['image'] = $this->editingCategory->image;
            }

            if ($this->editingCategory) {
                $this->editingCategory->update($data);
                $this->message = 'Category updated successfully!';
            } else {
                $data['company_id'] = getUserCompany();
                $data['inventory_location_id'] = getCurrentUserLocation()->id ?? null;
                $data['active'] = 1;
                LabInventoryCategory::create($data);
                $this->message = 'Category created successfully!';
            }

            $this->messageType = 'success';
            $this->showCategoryModal = false;
            $this->dispatch('category-modal-closed');
            $this->resetCategoryForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving category: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteCategory($categoryId)
    {
        try {
            $category = LabInventoryCategory::findOrFail($categoryId);
            $category->active = 0;
            $category->save();
            
            $this->message = 'Category deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error deleting category: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function resetCategoryForm()
    {
        $this->categoryForm = [
            'name' => '',
            'description' => '',
            'image' => null,
        ];
        $this->imageUpload = null;
        $this->editingCategory = null;
        $this->resetValidation();
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

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
        $this->dispatch('category-modal-closed');
    }

    public function render()
    {
        return view('livewire.lab.lab-inventory-category-manager');
    }
}
