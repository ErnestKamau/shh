<?php

namespace App\Livewire\Lab;

use App\InventorySubCategories;
use App\LabCategoryItems;
use App\LabInventoryCategory;
use App\LabSubCategory;
use App\ReportingUnit;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;

class LabSubCategoryDetails extends Component
{
    use WithFileUploads;

    public $subCategoryId;

    public $subCategory;

    public $subCategoryForm = [
        'name' => '',
        'description' => '',
        'category_id' => null,
        'reporting_unit' => null,
        'rate' => '',
        'image' => null,
    ];

    public $showItemModal = false;

    public $editingItem = null;

    public $itemForm = [
        'reagent_id' => null,
        'amount_used' => '',
        'unit_measure_id' => null,
    ];

    public $imageUpload = null;

    public $message = '';

    public $messageType = '';

    public $categories = [];

    public $reportingUnits = [];

    public $reagents = [];

    public $categoryItems = [];

    public $categorySearch = '';

    public $reportingUnitSearch = '';

    public $reagentSearch = '';

    public $itemUnitSearch = '';

    public $showCategoryDropdown = false;

    public $showReportingUnitDropdown = false;

    public $showReagentDropdown = false;

    public $showItemUnitDropdown = false;

    protected function getSubCategoryRules(): array
    {
        return [
            'subCategoryForm.name' => 'required|string|max:255',
            'subCategoryForm.description' => 'nullable|string',
            'subCategoryForm.category_id' => 'required|uuid|exists:lab_inventory_category,id',
            'subCategoryForm.reporting_unit' => 'required|uuid|exists:reporting_units,id',
            'subCategoryForm.rate' => 'nullable|string|max:255',
            'imageUpload' => 'nullable|image|max:2048',
        ];
    }

    protected function getItemRules(): array
    {
        return [
            'itemForm.reagent_id' => 'required|uuid|exists:inventory_sub_categories,id',
            'itemForm.amount_used' => 'required|numeric|min:0',
            'itemForm.unit_measure_id' => 'required|uuid|exists:reporting_units,id',
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

        $this->syncSelectSearchLabels();
        $this->loadCategoryItems();
    }

    public function loadSupportingData(): void
    {
        $this->categories = LabInventoryCategory::where('active', 1)->orderBy('name')->get();
        $this->reportingUnits = ReportingUnit::where('active', 1)->orderBy('name')->get();
        $this->reagents = InventorySubCategories::where('active', 1)->orderBy('name')->get();
    }

    public function loadCategoryItems(): void
    {
        $this->categoryItems = $this->subCategory
            ->categoryItems()
            ->with(['reagent', 'unitMeasure'])
            ->get();
    }

    public function getSelectedCategoryProperty(): ?LabInventoryCategory
    {
        return $this->findInCollection($this->categories, $this->subCategoryForm['category_id'] ?? null);
    }

    public function getSelectedReportingUnitProperty(): ?ReportingUnit
    {
        return $this->findInCollection($this->reportingUnits, $this->subCategoryForm['reporting_unit'] ?? null);
    }

    public function getSelectedReagentProperty(): ?InventorySubCategories
    {
        return $this->findInCollection($this->reagents, $this->itemForm['reagent_id'] ?? null);
    }

    public function getSelectedItemUnitProperty(): ?ReportingUnit
    {
        return $this->findInCollection($this->reportingUnits, $this->itemForm['unit_measure_id'] ?? null);
    }

    public function getFilteredCategoriesProperty(): Collection
    {
        return $this->filterCollection($this->categories, $this->categorySearch, $this->subCategoryForm['category_id'] ?? null);
    }

    public function getFilteredReportingUnitsProperty(): Collection
    {
        return $this->filterCollection($this->reportingUnits, $this->reportingUnitSearch, $this->subCategoryForm['reporting_unit'] ?? null);
    }

    public function getFilteredReagentsProperty(): Collection
    {
        return $this->filterCollection($this->reagents, $this->reagentSearch, $this->itemForm['reagent_id'] ?? null, ['name', 'code']);
    }

    public function getFilteredItemUnitsProperty(): Collection
    {
        return $this->filterCollection($this->reportingUnits, $this->itemUnitSearch, $this->itemForm['unit_measure_id'] ?? null);
    }

    public function selectCategory(string $categoryId): void
    {
        $this->subCategoryForm['category_id'] = $categoryId;
        $selected = $this->findInCollection($this->categories, $categoryId);
        $this->categorySearch = $selected ? (string) $selected->name : '';
        $this->showCategoryDropdown = false;
    }

    public function clearCategory(): void
    {
        $this->subCategoryForm['category_id'] = null;
        $this->categorySearch = '';
        $this->showCategoryDropdown = false;
    }

    public function selectReportingUnit(string $unitId): void
    {
        $this->subCategoryForm['reporting_unit'] = $unitId;
        $selected = $this->findInCollection($this->reportingUnits, $unitId);
        $this->reportingUnitSearch = $selected ? (string) $selected->name : '';
        $this->showReportingUnitDropdown = false;
    }

    public function clearReportingUnit(): void
    {
        $this->subCategoryForm['reporting_unit'] = null;
        $this->reportingUnitSearch = '';
        $this->showReportingUnitDropdown = false;
    }

    public function selectReagent(string $reagentId): void
    {
        $this->itemForm['reagent_id'] = $reagentId;
        $selected = $this->findInCollection($this->reagents, $reagentId);
        $this->reagentSearch = $selected ? (string) $selected->name : '';
        $this->showReagentDropdown = false;
    }

    public function clearReagent(): void
    {
        $this->itemForm['reagent_id'] = null;
        $this->reagentSearch = '';
        $this->showReagentDropdown = false;
    }

    public function selectItemUnit(string $unitId): void
    {
        $this->itemForm['unit_measure_id'] = $unitId;
        $selected = $this->findInCollection($this->reportingUnits, $unitId);
        $this->itemUnitSearch = $selected ? (string) $selected->name : '';
        $this->showItemUnitDropdown = false;
    }

    public function clearItemUnit(): void
    {
        $this->itemForm['unit_measure_id'] = null;
        $this->itemUnitSearch = '';
        $this->showItemUnitDropdown = false;
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

    public function showEditItemModal(string $itemId): void
    {
        $item = LabCategoryItems::findOrFail($itemId);
        $this->itemForm = [
            'reagent_id' => $item->inventory_sub_category_id,
            'amount_used' => $item->amount_used,
            'unit_measure_id' => $item->unit_measure_id,
        ];

        $this->syncItemSelectSearchLabels();
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

    public function deleteItem(string $itemId): void
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
        $this->reagentSearch = '';
        $this->itemUnitSearch = '';
        $this->showReagentDropdown = false;
        $this->showItemUnitDropdown = false;
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

    protected function syncSelectSearchLabels(): void
    {
        $this->categorySearch = $this->selectedCategory ? (string) $this->selectedCategory->name : '';
        $this->reportingUnitSearch = $this->selectedReportingUnit ? (string) $this->selectedReportingUnit->name : '';
    }

    protected function syncItemSelectSearchLabels(): void
    {
        $this->reagentSearch = $this->selectedReagent ? (string) $this->selectedReagent->name : '';
        $this->itemUnitSearch = $this->selectedItemUnit ? (string) $this->selectedItemUnit->name : '';
    }

    protected function findInCollection($collection, mixed $id): mixed
    {
        $selectedId = (string) ($id ?? '');
        if ($selectedId === '') {
            return null;
        }

        return collect($collection)->first(fn ($item) => (string) $item->id === $selectedId);
    }

    /**
     * @param  array<int, string>  $searchFields
     */
    protected function filterCollection($collection, string $search, mixed $selectedId, array $searchFields = ['name']): Collection
    {
        $needle = trim(strtolower($search));
        $selectedId = (string) ($selectedId ?? '');

        return collect($collection)
            ->filter(function ($item) use ($needle, $selectedId, $searchFields) {
                if ((string) $item->id === $selectedId) {
                    return false;
                }

                if ($needle === '') {
                    return true;
                }

                foreach ($searchFields as $field) {
                    $value = strtolower((string) ($item->{$field} ?? ''));

                    if ($value !== '' && str_contains($value, $needle)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }
}
