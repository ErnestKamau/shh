<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\LabSubCategory;
use App\LabInventoryCategory;
use App\LabCategoryItems;
use App\InventorySubCategories;
use App\ReportingUnit;
use Illuminate\Support\Facades\Storage;

class LabSubCategoryDetails extends Component
{
    use WithFileUploads;

    public $subCategoryId;
    public $subCategory;
    
    // Sub-Category Form
    public $subCategoryForm = [
        'name' => '',
        'description' => '',
        'category_id' => null,
        'reporting_unit' => null,
        'rate' => '',
        'image' => null,
    ];

    // Reagent Item Management
    public $showItemModal = false;
    public $editingItem = null;
    public $itemForm = [
        'reagent_id' => null,
        'amount_used' => '',
        'unit_measure_id' => null,
    ];

    // Temporary file upload
    public $imageUpload = null;

    // UI State
    public $message = '';
    public $messageType = '';

    // Supporting Data
    public $categories = [];
    public $reportingUnits = [];
    public $reagents = [];
    public $categoryItems = [];

    protected function getSubCategoryRules(): array
    {
        return [
            'subCategoryForm.name' => 'required|string|max:255',
            'subCategoryForm.description' => 'nullable|string',
            'subCategoryForm.category_id' => 'required|integer',
            'subCategoryForm.reporting_unit' => 'required|integer',
            'subCategoryForm.rate' => 'nullable|string|max:255',
            'imageUpload' => 'nullable|image|max:2048',
        ];
    }

    protected function getItemRules(): array
    {
        return [
            'itemForm.reagent_id' => 'required|integer|exists:inventory_sub_categories,id',
            'itemForm.amount_used' => 'required|numeric|min:0',
            'itemForm.unit_measure_id' => 'required|integer|exists:reporting_units,id',
        ];
    }

    public function mount($subCategoryId): void
    {
        $this->subCategoryId = $subCategoryId;
        $this->loadSubCategory();
        $this->loadSupportingData();
    }

    public function loadSubCategory(): void
    {
        $this->subCategory = LabSubCategory::with(['category', 'reportingUnit'])->findOrFail($this->subCategoryId);
        
        $this->subCategoryForm = [
            'name' => $this->subCategory->name,
            'description' => $this->subCategory->description,
            'category_id' => $this->subCategory->category_id,
            'reporting_unit' => $this->subCategory->reporting_unit,
            'rate' => $this->subCategory->rate,
            'image' => $this->subCategory->image,
        ];

        $this->loadCategoryItems();
    }

    public function loadSupportingData(): void
    {
        $this->categories = LabInventoryCategory::where('active', 1)->get();
        $this->reportingUnits = ReportingUnit::where('active', 1)->get();
        $this->reagents = InventorySubCategories::where('active', 1)->get();
    }

    public function loadCategoryItems(): void
    {
        $this->categoryItems = LabCategoryItems::where('sub_category_id', $this->subCategoryId)
            ->with(['reagent', 'unitMeasure'])
            ->get();
    }

    public function updateSubCategory(): void
    {
        $this->validate($this->getSubCategoryRules());

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
            } else {
                $data['image'] = $this->subCategory->image;
            }

            $this->subCategory->update($data);
            
            $this->message = 'Sub-category updated successfully!';
            $this->messageType = 'success';
            $this->loadSubCategory();
        } catch (\Exception $e) {
            $this->message = 'Error updating sub-category: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function showAddItemModal(): void
    {
        $this->resetItemForm();
        $this->showItemModal = true;
        $this->editingItem = null;
        $this->dispatch('item-modal-opened');
    }

    public function showEditItemModal($itemId): void
    {
        $item = LabCategoryItems::findOrFail($itemId);
        $this->itemForm = [
            'reagent_id' => $item->inventory_sub_category_id,
            'amount_used' => $item->amount_used,
            'unit_measure_id' => $item->unit_measure_id,
        ];
        
        $this->editingItem = $item;
        $this->showItemModal = true;
        $this->dispatch('item-modal-opened');
    }

    public function saveItem(): void
    {
        $this->validate($this->getItemRules());

        try {
            $data = [
                'reagent_id' => $this->itemForm['reagent_id'],
                'inventory_sub_category_id' => $this->itemForm['reagent_id'],
                'amount_used' => $this->itemForm['amount_used'],
                'unit_measure_id' => $this->itemForm['unit_measure_id'],
                'sub_category_id' => $this->subCategoryId,
                'category_id' => $this->subCategory->category_id,
            ];

            if ($this->editingItem) {
                $this->editingItem->update($data);
                $this->message = 'Reagent item updated successfully!';
            } else {
                LabCategoryItems::create($data);
                $this->message = 'Reagent item added successfully!';
            }

            $this->messageType = 'success';
            $this->showItemModal = false;
            $this->dispatch('item-modal-closed');
            $this->loadCategoryItems();
            $this->resetItemForm();
        } catch (\Exception $e) {
            $this->message = 'Error saving reagent item: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteItem($itemId): void
    {
        try {
            LabCategoryItems::findOrFail($itemId)->delete();
            
            $this->message = 'Reagent item deleted successfully!';
            $this->messageType = 'success';
            $this->loadCategoryItems();
        } catch (\Exception $e) {
            $this->message = 'Error deleting reagent item: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function resetItemForm(): void
    {
        $this->itemForm = [
            'reagent_id' => null,
            'amount_used' => '',
            'unit_measure_id' => null,
        ];
        $this->editingItem = null;
        $this->resetValidation();
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
        $this->dispatch('item-modal-closed');
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.lab.lab-sub-category-details');
    }
}
