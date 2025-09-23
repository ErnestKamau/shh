<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\CRMCustomer;
use App\Country;
use App\ModulePreConfigs;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerManager extends Component
{
    use WithPagination;

    // Customer Management
    public $selectedCustomer = null;
    public $editingCustomer = null;
    public $showCustomerModal = false;
    
    // Customer Form
    public $customerForm = [
        'name' => '',
        'postal_address' => '',
        'physical_address' => '',
        'website' => '',
        'fax' => '',
        'email' => '',
        'telephone1' => '',
        'telephone2' => '',
        'country_id' => null,
        'credit_days' => null,
        'active' => true,
        'account_status' => null,
        'vat_no' => '',
        'lpos_required' => false
    ];

    // Supporting Data
    public $countries = [];
    public $accounts = [];

    // Search and Filter
    public $search = '';
    public $countryFilter = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'customerForm.name' => 'required|string|max:255',
        'customerForm.postal_address' => 'required|string|max:500',
        'customerForm.physical_address' => 'required|string|max:500',
        'customerForm.email' => 'required|email|max:255',
        'customerForm.telephone1' => 'required|string|max:50',
        'customerForm.country_id' => 'required|exists:countries,id',
        'customerForm.account_status' => 'required|exists:module_pre_configs,id',
    ];

    protected $messages = [
        'customerForm.name.required' => 'Customer name is required.',
        'customerForm.postal_address.required' => 'Postal address is required.',
        'customerForm.physical_address.required' => 'Physical address is required.',
        'customerForm.email.required' => 'Email address is required.',
        'customerForm.email.email' => 'Please enter a valid email address.',
        'customerForm.telephone1.required' => 'Primary phone number is required.',
        'customerForm.country_id.required' => 'Country selection is required.',
        'customerForm.account_status.required' => 'Account settings selection is required.',
    ];

    public function mount()
    {
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->countries = Country::orderBy('name')->get();
        
        $account_settings = getConfigTypeByName('Account Settings');
        if (isset($account_settings->id)) {
            $this->accounts = getconfigByID($account_settings->id);
        } else {
            $this->accounts = [];
        }
    }

    public function getCustomersProperty()
    {
        $query = CRMCustomer::with(['country', 'currencyinfo'])
            ->where('active', 1)
            ->orderBy('name');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->countryFilter) {
            $query->where('country_id', $this->countryFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCountryFilter()
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
        $this->countryFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Customer Methods
    public function showCreateCustomerModal()
    {
        $this->resetCustomerForm();
        $this->showCustomerModal = true;
    }

    public function showEditCustomerModal($id)
    {
        $customer = CRMCustomer::findOrFail($id);
        
        $this->customerForm = [
            'name' => $customer->name,
            'postal_address' => $customer->postal_address,
            'physical_address' => $customer->physical_address,
            'website' => $customer->website ?? '',
            'fax' => $customer->fax ?? '',
            'email' => $customer->email,
            'telephone1' => $customer->telephone1,
            'telephone2' => $customer->telephone2 ?? '',
            'country_id' => $customer->country_id,
            'credit_days' => $customer->credit_days,
            'active' => $customer->active == 1,
            'account_status' => $customer->account_status,
            'vat_no' => $customer->vat_no ?? '',
            'lpos_required' => $customer->lpos_required == 1
        ];
        
        $this->editingCustomer = $customer;
        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingCustomer) {
                // Update existing customer
                $customer = $this->editingCustomer;
            } else {
                // Check for duplicate name
                $existingCustomer = CRMCustomer::where('name', $this->customerForm['name'])->first();
                if ($existingCustomer) {
                    $this->message = 'Customer with this name already exists.';
                    $this->messageType = 'error';
                    return;
                }

                // Create new customer
                $customer = new CRMCustomer();
                $customer->code = getNamingConventionCode("Customers", $this->customerForm['name']);
            }

            $customer->name = $this->customerForm['name'];
            $customer->postal_address = $this->customerForm['postal_address'];
            $customer->physical_address = $this->customerForm['physical_address'];
            $customer->website = $this->customerForm['website'];
            $customer->fax = $this->customerForm['fax'];
            $customer->email = $this->customerForm['email'];
            $customer->telephone1 = $this->customerForm['telephone1'];
            $customer->telephone2 = $this->customerForm['telephone2'];
            $customer->country_id = $this->customerForm['country_id'];
            $customer->credit_days = $this->customerForm['credit_days'];
            $customer->active = $this->customerForm['active'] ? 1 : 0;
            $customer->account_status = $this->customerForm['account_status'];
            $customer->vat_no = $this->customerForm['vat_no'];
            $customer->lpos_required = $this->customerForm['lpos_required'] ? 1 : 0;

            $customer->save();

            DB::commit();
            
            $this->closeCustomerModal();
            $this->message = $this->editingCustomer ? 'Customer updated successfully!' : 'Customer created successfully!';
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

            $customer = CRMCustomer::findOrFail($id);
            
            // Soft delete - set active to 0
            $customer->active = 0;
            $customer->save();

            // Cascade effects on related entities
            $customer->contacts()->update(['active' => 0]);
            $customer->units()->update(['active' => 0]);
            
            // Update related users
            \App\User::where('client_id', $customer->id)->update(['active' => 0]);

            DB::commit();
            
            $this->message = 'Customer deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function viewCustomer($id)
    {
        return redirect()->route('livewire.customer-profile', ['customerId' => $id]);
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
            'postal_address' => '',
            'physical_address' => '',
            'website' => '',
            'fax' => '',
            'email' => '',
            'telephone1' => '',
            'telephone2' => '',
            'country_id' => null,
            'credit_days' => null,
            'active' => true,
            'account_status' => null,
            'vat_no' => '',
            'lpos_required' => false
        ];
        $this->editingCustomer = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.customer-manager', [
            'customers' => $this->customers
        ]);
    }
}

