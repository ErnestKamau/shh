<?php

namespace App\Livewire\Billing;

use Livewire\Component;
use Livewire\WithPagination;
use App\ZohoCustomers;
use App\Services\DynamicsCustomerSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DynamicsCustomerManager extends Component
{
    use WithPagination;

    // Customer Management
    public $editingCustomer = null;
    public $showCustomerModal = false;
    
    // Customer Form
    public $customerForm = [
        'name' => '',
        'email' => '',
        'status' => 'active',
        'currency_id' => '',
        'currency_code' => '',
        'zoho_contact_id' => '',
    ];

    // Search and Filter
    public $search = '';
    public $statusFilter = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // UI State
    public $message = '';
    public $messageType = '';

    // Dynamics sync properties
    public $showSyncModal = false;
    public $isSyncing = false;
    public $syncProgress = '';
    public $syncResult = null;

    protected $rules = [
        'customerForm.name' => 'required|string|max:255',
        'customerForm.email' => 'nullable|email|max:100',
        'customerForm.status' => 'required|string|max:255',
        'customerForm.currency_id' => 'required|string|max:255',
        'customerForm.currency_code' => 'required|string|max:255',
        'customerForm.zoho_contact_id' => 'required|string|max:255',
    ];

    protected $messages = [
        'customerForm.name.required' => 'Customer name is required.',
        'customerForm.currency_id.required' => 'Currency ID is required.',
        'customerForm.currency_code.required' => 'Currency code is required.',
        'customerForm.zoho_contact_id.required' => 'Zoho Contact ID is required.',
    ];

    public function getCustomersProperty()
    {
        $query = ZohoCustomers::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('zoho_contact_id', 'like', '%' . $this->search . '%')
                  ->orWhere('currency_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy('name', 'asc')->paginate($this->perPage);
    }

    public function updatedSearch()
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
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function showCreateCustomerModal()
    {
        $this->resetCustomerForm();
        $this->showCustomerModal = true;
    }

    public function showEditCustomerModal($id)
    {
        $customer = ZohoCustomers::findOrFail($id);
        $this->customerForm = [
            'name' => $customer->name,
            'email' => $customer->email,
            'status' => $customer->status,
            'currency_id' => $customer->currency_id,
            'currency_code' => $customer->currency_code,
            'zoho_contact_id' => $customer->zoho_contact_id,
        ];
        $this->editingCustomer = $id;
        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->validate([
            'customerForm.name' => 'required|string|max:255',
            'customerForm.email' => 'nullable|email|max:100',
            'customerForm.status' => 'required|string|max:255',
            'customerForm.currency_id' => 'required|string|max:255',
            'customerForm.currency_code' => 'required|string|max:255',
            'customerForm.zoho_contact_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('zoho_customers', 'zoho_contact_id')->ignore($this->editingCustomer)
            ],
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingCustomer) {
                $customer = ZohoCustomers::findOrFail($this->editingCustomer);
                $customer->update([
                    'name' => $this->customerForm['name'],
                    'email' => $this->customerForm['email'],
                    'status' => $this->customerForm['status'],
                    'currency_id' => $this->customerForm['currency_id'],
                    'currency_code' => $this->customerForm['currency_code'],
                    'zoho_contact_id' => $this->customerForm['zoho_contact_id'],
                ]);
                $this->message = 'Customer updated successfully!';
            } else {
                ZohoCustomers::create([
                    'name' => $this->customerForm['name'],
                    'email' => $this->customerForm['email'],
                    'status' => $this->customerForm['status'],
                    'currency_id' => $this->customerForm['currency_id'],
                    'currency_code' => $this->customerForm['currency_code'],
                    'zoho_contact_id' => $this->customerForm['zoho_contact_id'],
                ]);
                $this->message = 'Customer created successfully!';
            }

            DB::commit();
            $this->closeCustomerModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteCustomer($id)
    {
        try {
            DB::beginTransaction();

            $customer = ZohoCustomers::findOrFail($id);
            $customer->delete();

            DB::commit();
            $this->message = 'Customer deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerForm();
    }

    public function resetCustomerForm()
    {
        $this->customerForm = [
            'name' => '',
            'email' => '',
            'status' => 'active',
            'currency_id' => '',
            'currency_code' => '',
            'zoho_contact_id' => '',
        ];
        $this->editingCustomer = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    // Dynamics Sync Methods
    public function showSyncConfirmationModal()
    {
        $this->showSyncModal = true;
        $this->syncResult = null;
        $this->syncProgress = '';
    }

    public function closeSyncModal()
    {
        $this->showSyncModal = false;
        $this->syncResult = null;
        $this->syncProgress = '';
        $this->isSyncing = false;
    }

    public function pullCustomersFromDynamics()
    {
        $this->isSyncing = true;
        $this->syncProgress = 'Connecting to Dynamics 365...';
        
        // Dispatch browser event to update UI
        $this->dispatch('sync-progress-update', progress: $this->syncProgress);

        try {
            $this->syncProgress = 'Fetching customers from Dynamics...';
            $this->dispatch('sync-progress-update', progress: $this->syncProgress);

            $syncService = new DynamicsCustomerSyncService();
            $result = $syncService->syncCustomers();

            $this->syncResult = $result;
            $this->isSyncing = false;

            if ($result['success']) {
                $this->syncProgress = 'Sync completed successfully!';
                $this->message = $result['message'];
                $this->messageType = 'success';
            } else {
                $this->syncProgress = 'Sync failed!';
                $this->message = $result['message'];
                $this->messageType = 'error';
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
            $this->message = 'Sync failed: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.billing.dynamics-customer-manager');
    }
}
