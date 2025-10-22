<?php

namespace App\Livewire\Billing;

use App\InvoicableItem;
use App\ModulePreConfigs;
use App\Services\DynamicsItemSyncService;
use Livewire\Component;
use Livewire\WithPagination;

class InvoicableItemManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $search = '';
    public $itemTypeFilter = '';
    public $currencyFilter = '';
    public $statusFilter = '1'; // Default to active
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // Modal and form properties
    public $showItemModal = false;
    public $editingItem = false;
    public $itemForm = [];

    // Dropdown search properties
    public $currencySearch = '';
    public $showCurrencyDropdown = false;

    // Message properties
    public $message = '';
    public $messageType = 'success';

    // Dynamics sync properties
    public $showSyncModal = false;
    public $isSyncing = false;
    public $syncProgress = '';
    public $syncResult = null;

    protected function rules(): array
    {
        $rules = [
            'itemForm.item_code' => 'required|string|max:255',
            'itemForm.item_name' => 'required|string|max:255',
            'itemForm.description' => 'nullable|string',
            'itemForm.item_type' => 'nullable|string|max:255',
            'itemForm.item_category_code' => 'nullable|string|max:255',
            'itemForm.unit_price' => 'required|numeric|min:0',
            'itemForm.unit_cost' => 'required|numeric|min:0',
            'itemForm.currency_id' => 'required|integer|exists:module_pre_configs,id',
            'itemForm.price_includes_tax' => 'boolean',
            'itemForm.tax_group_code' => 'nullable|string|max:255',
            'itemForm.base_unit_of_measure' => 'nullable|string|max:255',
            'itemForm.gtin' => 'nullable|string|max:255',
            'itemForm.blocked' => 'boolean',
            'itemForm.active' => 'boolean',
        ];

        if (!$this->editingItem) {
            $rules['itemForm.item_code'] .= '|unique:invoicable_items,item_code';
        } else {
            $rules['itemForm.item_code'] .= '|unique:invoicable_items,item_code,' . $this->itemForm['id'];
        }

        return $rules;
    }

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedItemTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCurrencyFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getInvoicableItemsProperty()
    {
        $query = InvoicableItem::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('item_code', 'like', "%{$this->search}%")
                  ->orWhere('item_name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->itemTypeFilter) {
            $query->where('item_type', $this->itemTypeFilter);
        }

        if ($this->currencyFilter) {
            $query->where('currency_id', $this->currencyFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        return $query->orderBy('item_name', 'asc')->paginate($this->perPage);
    }

    public function getCurrenciesProperty()
    {
        return ModulePreConfigs::where('type', 'Currency')->orderBy('name')->get();
    }

    public function getItemTypesProperty()
    {
        return InvoicableItem::select('item_type')
            ->distinct()
            ->whereNotNull('item_type')
            ->orderBy('item_type')
            ->pluck('item_type');
    }

    public function getFilteredCurrenciesProperty()
    {
        $query = ModulePreConfigs::where('type', 'Currency');

        if ($this->currencySearch) {
            $query->where('name', 'like', "%{$this->currencySearch}%");
        }

        return $query->orderBy('name')->get();
    }

    public function getSelectedCurrencyProperty()
    {
        if (isset($this->itemForm['currency_id'])) {
            return ModulePreConfigs::find($this->itemForm['currency_id']);
        }
        return null;
    }

    public function clearFilters(): void
    {
        $this->resetFilters();
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->itemTypeFilter = '';
        $this->currencyFilter = '';
        $this->statusFilter = '1';
    }

    public function showCreateItemModal(): void
    {
        $this->resetForm();
        $this->editingItem = false;
        $this->showItemModal = true;
    }

    public function showEditItemModal($itemId): void
    {
        $item = InvoicableItem::findOrFail($itemId);
        
        $this->itemForm = [
            'id' => $item->id,
            'item_code' => $item->item_code,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'item_type' => $item->item_type,
            'item_category_code' => $item->item_category_code,
            'unit_price' => $item->unit_price,
            'unit_cost' => $item->unit_cost,
            'currency_id' => $item->currency_id,
            'price_includes_tax' => $item->price_includes_tax,
            'tax_group_code' => $item->tax_group_code,
            'base_unit_of_measure' => $item->base_unit_of_measure,
            'gtin' => $item->gtin,
            'blocked' => $item->blocked,
            'active' => $item->active,
        ];

        $this->editingItem = true;
        $this->showItemModal = true;
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm(): void
    {
        $this->itemForm = [
            'item_code' => '',
            'item_name' => '',
            'description' => '',
            'item_type' => 'Service',
            'item_category_code' => '',
            'unit_price' => 0,
            'unit_cost' => 0,
            'currency_id' => null,
            'price_includes_tax' => false,
            'tax_group_code' => '',
            'base_unit_of_measure' => 'PCS',
            'gtin' => '',
            'blocked' => false,
            'active' => true,
        ];
    }

    public function saveItem(): void
    {
        $this->validate();

        if ($this->editingItem) {
            $item = InvoicableItem::findOrFail($this->itemForm['id']);
            $item->update($this->itemForm);
            $this->showMessage('Invoicable item updated successfully!', 'success');
        } else {
            InvoicableItem::create($this->itemForm);
            $this->showMessage('Invoicable item created successfully!', 'success');
        }

        $this->closeItemModal();
    }

    public function deleteItem($itemId): void
    {
        $item = InvoicableItem::findOrFail($itemId);
        
        // Check if item is used in any invoices or quotations
        $usedInInvoices = \App\InvoiceDetails::where('invoicable_item_id', $itemId)->exists();
        $usedInQuotations = \App\QuotationDetails::where('invoicable_item_id', $itemId)->exists();

        if ($usedInInvoices || $usedInQuotations) {
            $this->showMessage('Cannot delete item. It is used in invoices or quotations. You can deactivate it instead.', 'danger');
            return;
        }

        $item->delete();
        $this->showMessage('Invoicable item deleted successfully!', 'success');
    }

    public function cloneItem($itemId): void
    {
        $item = InvoicableItem::findOrFail($itemId);
        
        $newItem = $item->replicate();
        $newItem->item_code = $item->item_code . '-COPY';
        $newItem->item_name = $item->item_name . ' (Copy)';
        $newItem->active = false; // Set to inactive by default
        $newItem->save();

        $this->showMessage('Invoicable item cloned successfully!', 'success');
    }

    public function selectCurrency($currencyId): void
    {
        $this->itemForm['currency_id'] = $currencyId;
        $this->showCurrencyDropdown = false;
        $this->currencySearch = '';
    }

    public function showMessage($message, $type = 'success'): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    // Dynamics Sync Methods
    public function showSyncConfirmationModal(): void
    {
        $this->showSyncModal = true;
        $this->syncResult = null;
        $this->syncProgress = '';
    }

    public function closeSyncModal(): void
    {
        $this->showSyncModal = false;
        $this->syncResult = null;
        $this->syncProgress = '';
        $this->isSyncing = false;
    }

    public function pullItemsFromDynamics(): void
    {
        $this->isSyncing = true;
        $this->syncProgress = 'Connecting to Dynamics 365...';
        
        // Dispatch browser event to update UI
        $this->dispatch('sync-progress-update', progress: $this->syncProgress);

        try {
            $this->syncProgress = 'Fetching items from Dynamics...';
            $this->dispatch('sync-progress-update', progress: $this->syncProgress);

            $syncService = new DynamicsItemSyncService();
            $result = $syncService->syncItems();

            $this->syncResult = $result;
            $this->isSyncing = false;

            if ($result['success']) {
                $this->syncProgress = 'Sync completed successfully!';
                $this->showMessage($result['message'], 'success');
            } else {
                $this->syncProgress = 'Sync failed!';
                $this->showMessage($result['message'], 'danger');
            }

        } catch (\Exception $e) {
            $this->isSyncing = false;
            $this->syncProgress = 'Sync failed!';
            $this->syncResult = [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'new_count' => 0,
                'updated_count' => 0,
                'total_fetched' => 0,
            ];
            $this->showMessage('Sync failed: ' . $e->getMessage(), 'danger');
        }
    }

    public function render()
    {
        return view('livewire.billing.invoicable-item-manager', [
            'invoicableItems' => $this->invoicableItems,
            'currencies' => $this->currencies,
            'itemTypes' => $this->itemTypes,
            'filteredCurrencies' => $this->filteredCurrencies,
            'selectedCurrency' => $this->selectedCurrency,
        ]);
    }
}
