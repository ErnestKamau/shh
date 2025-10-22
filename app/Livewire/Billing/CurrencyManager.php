<?php

namespace App\Livewire\Billing;

use App\Models\Currency;
use App\Services\DynamicsCurrencySyncService;
use Livewire\Component;
use Livewire\WithPagination;

class CurrencyManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $search = '';
    public $statusFilter = '1'; // Default to active
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // Modal and form properties
    public $showCurrencyModal = false;
    public $editingCurrency = false;
    public $currencyForm = [];

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
            'currencyForm.code' => 'required|string|max:255',
            'currencyForm.description' => 'nullable|string|max:255',
            'currencyForm.iso_code' => 'nullable|string|max:255',
            'currencyForm.iso_numeric_code' => 'nullable|string|max:255',
            'currencyForm.exchange_rate_amt' => 'required|numeric|min:0',
            'currencyForm.currency_factor' => 'required|numeric|min:0',
            'currencyForm.active' => 'boolean',
        ];

        if (!$this->editingCurrency) {
            $rules['currencyForm.code'] .= '|unique:currencies,code';
        } else {
            $rules['currencyForm.code'] .= '|unique:currencies,code,' . $this->currencyForm['id'];
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

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getCurrenciesProperty()
    {
        $query = Currency::query();

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('iso_code', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        return $query->orderBy('code', 'asc')->paginate($this->perPage);
    }

    public function showCreateCurrencyModal(): void
    {
        $this->resetCurrencyForm();
        $this->editingCurrency = false;
        $this->showCurrencyModal = true;
    }

    public function showEditCurrencyModal($currencyId): void
    {
        $currency = Currency::findOrFail($currencyId);
        
        $this->currencyForm = [
            'id' => $currency->id,
            'code' => $currency->code,
            'description' => $currency->description,
            'iso_code' => $currency->iso_code,
            'iso_numeric_code' => $currency->iso_numeric_code,
            'exchange_rate_amt' => $currency->exchange_rate_amt,
            'currency_factor' => $currency->currency_factor,
            'active' => $currency->active,
        ];

        $this->editingCurrency = true;
        $this->showCurrencyModal = true;
    }

    public function saveCurrency(): void
    {
        $this->validate();

        try {
            if ($this->editingCurrency) {
                $currency = Currency::findOrFail($this->currencyForm['id']);
                $currency->update($this->currencyForm);
                $this->message = 'Currency updated successfully!';
            } else {
                Currency::create($this->currencyForm);
                $this->message = 'Currency created successfully!';
            }

            $this->messageType = 'success';
            $this->showCurrencyModal = false;
            $this->resetCurrencyForm();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->message = 'Error saving currency: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deleteCurrency($currencyId): void
    {
        try {
            $currency = Currency::findOrFail($currencyId);
            
            // Check if currency is in use
            if ($currency->invoices()->count() > 0 || $currency->quotations()->count() > 0 || $currency->invoicableItems()->count() > 0) {
                $this->message = 'Cannot delete currency. It is in use by invoices, quotations, or invoicable items.';
                $this->messageType = 'warning';
                return;
            }

            $currency->delete();
            $this->message = 'Currency deleted successfully!';
            $this->messageType = 'success';
            $this->resetPage();
        } catch (\Exception $e) {
            $this->message = 'Error deleting currency: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function toggleStatus($currencyId): void
    {
        try {
            $currency = Currency::findOrFail($currencyId);
            $currency->active = !$currency->active;
            $currency->save();

            $this->message = 'Currency status updated successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error updating currency status: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function resetCurrencyForm(): void
    {
        $this->currencyForm = [
            'code' => '',
            'description' => '',
            'iso_code' => '',
            'iso_numeric_code' => '',
            'exchange_rate_amt' => 0,
            'currency_factor' => 1,
            'active' => true,
        ];
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showCurrencyModal = false;
        $this->resetCurrencyForm();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '1';
        $this->perPage = 25;
        $this->resetPage();
    }

    public function clearMessage(): void
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
        $this->isSyncing = false;
        $this->syncProgress = '';
        $this->syncResult = null;
    }

    public function pullCurrenciesFromDynamics(): void
    {
        $this->isSyncing = true;
        $this->syncProgress = 'Connecting to Dynamics 365...';
        $this->syncResult = null;

        try {
            $syncService = new DynamicsCurrencySyncService();
            
            $this->syncProgress = 'Fetching currencies from Dynamics...';
            $result = $syncService->syncCurrencies();

            $this->syncResult = $result;
            $this->isSyncing = false;

            if ($result['success']) {
                $this->message = $result['message'];
                $this->messageType = 'success';
                $this->resetPage(); // Refresh the list
            } else {
                $this->message = $result['message'];
                $this->messageType = 'danger';
            }
        } catch (\Exception $e) {
            $this->syncResult = [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
            $this->isSyncing = false;
            $this->message = 'Sync failed: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function render()
    {
        return view('livewire.billing.currency-manager', [
            'currencies' => $this->currencies,
        ]);
    }
}
