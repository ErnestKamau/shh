<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\CRMCustomer;
use App\Country;
use App\ModulePreConfigs;
use App\ZohoCustomers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
        'lpos_required' => false,
        'zoho_customer_id' => null
    ];

    // Supporting Data
    public $countries = [];
    public $accounts = [];
    public $zohoCustomers = [];

    // Dropdown State
    public $countrySearch = '';
    public $accountSearch = '';
    public $zohoCustomerSearch = '';
    public $showCountryDropdown = false;
    public $showAccountDropdown = false;
    public $showZohoCustomerDropdown = false;

    // Search and Filter
    public $search = '';
    public $countryFilter = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Bulk Operations
    public $selectedCustomers = [];
    public $selectAll = false;
    public $bulkAction = '';

    // Export
    public $exportFormat = 'csv';

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
        'customerForm.zoho_customer_id' => 'nullable|exists:zoho_customers,id',
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
        $this->countries = Cache::remember('countries', 3600, function() {
            return Country::orderBy('name')->get();
        });
        
        $this->accounts = Cache::remember('account_settings', 1800, function() {
            $account_settings = getConfigTypeByName('Account Settings');
            if (isset($account_settings->id)) {
                return getconfigByID($account_settings->id);
            }
            return [];
        });
        
        $this->zohoCustomers = ZohoCustomers::where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'customer_no', 'name', 'currency_code']);
    }

    public function getCustomersProperty()
    {
        $query = CRMCustomer::with(['country', 'currencyinfo','zohocustomer'])
            ->orderBy('name');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('telephone1', 'like', '%' . $this->search . '%')
                  ->orWhere('physical_address', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->countryFilter) {
            $query->where('country_id', $this->countryFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
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
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    // Bulk Operations
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = $this->customers->pluck('id')->toArray();
        } else {
            $this->selectedCustomers = [];
        }
    }

    public function updatedSelectedCustomers()
    {
        $this->selectAll = count($this->selectedCustomers) === $this->customers->count();
    }

    public function bulkDelete()
    {
        if (empty($this->selectedCustomers)) {
            $this->message = 'Please select customers to delete.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();
            
            CRMCustomer::whereIn('id', $this->selectedCustomers)->delete();
            
            DB::commit();
            
            $this->selectedCustomers = [];
            $this->selectAll = false;
            $this->message = 'Selected customers deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function bulkStatusUpdate($status)
    {
        if (empty($this->selectedCustomers)) {
            $this->message = 'Please select customers to update.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();
            
            CRMCustomer::whereIn('id', $this->selectedCustomers)
                ->update(['active' => $status]);
            
            DB::commit();
            
            $this->selectedCustomers = [];
            $this->selectAll = false;
            $this->message = 'Selected customers updated successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Export Functionality
    public function exportCustomers()
    {
        $customers = CRMCustomer::with(['country'])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('telephone1', 'like', '%' . $this->search . '%')
                      ->orWhere('physical_address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->countryFilter, function ($query) {
                $query->where('country_id', $this->countryFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('name')
            ->get();

        if ($this->exportFormat === 'csv') {
            return $this->exportToCsv($customers);
        } elseif ($this->exportFormat === 'excel') {
            return $this->exportToExcel($customers);
        }

        $this->message = 'Invalid export format selected.';
        $this->messageType = 'error';
    }

    private function exportToCsv($customers)
    {
        $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Code', 'Name', 'Email', 'Phone 1', 'Phone 2', 'Country', 
                'Postal Address', 'Physical Address', 'Website', 'Fax', 
                'VAT Number', 'Credit Days', 'Status', 'Created Date'
            ]);

            // CSV Data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->code,
                    $customer->name,
                    $customer->email,
                    $customer->telephone1,
                    $customer->telephone2 ?? '',
                    $customer->country->name ?? '',
                    $customer->postal_address,
                    $customer->physical_address,
                    $customer->website ?? '',
                    $customer->fax ?? '',
                    $customer->vat_no ?? '',
                    $customer->credit_days ?? '',
                    $customer->active ? 'Active' : 'Inactive',
                    $customer->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($customers)
    {
        // This would require Laravel Excel package
        $this->message = 'Excel export requires Laravel Excel package. Please install it first.';
        $this->messageType = 'error';
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Customer Methods
    public function showCreateCustomerModal()
    {
        $this->resetCustomerForm();
        $this->resetDropdownStates();
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
            'lpos_required' => $customer->lpos_required == 1,
            'zoho_customer_id' => $customer->zoho_customer_id
        ];
        
        $this->resetDropdownStates();
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
            $customer->zoho_customer_id = $this->customerForm['zoho_customer_id'];

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
        $this->resetDropdownStates();
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
            'lpos_required' => false,
            'zoho_customer_id' => null
        ];
        $this->editingCustomer = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    // Dropdown Methods
    public function toggleCountryDropdown()
    {
        $this->showCountryDropdown = !$this->showCountryDropdown;
        if ($this->showCountryDropdown) {
            $this->showAccountDropdown = false;
            $this->showZohoCustomerDropdown = false;
        }
    }

    public function toggleAccountDropdown()
    {
        $this->showAccountDropdown = !$this->showAccountDropdown;
        if ($this->showAccountDropdown) {
            $this->showCountryDropdown = false;
            $this->showZohoCustomerDropdown = false;
        }
    }

    public function toggleZohoCustomerDropdown()
    {
        $this->showZohoCustomerDropdown = !$this->showZohoCustomerDropdown;
        if ($this->showZohoCustomerDropdown) {
            $this->showCountryDropdown = false;
            $this->showAccountDropdown = false;
        }
    }

    public function selectCountry($countryId)
    {
        $this->customerForm['country_id'] = $countryId;
        $this->showCountryDropdown = false;
        $this->countrySearch = '';
    }

    public function selectAccount($accountId)
    {
        $this->customerForm['account_status'] = $accountId;
        $this->showAccountDropdown = false;
        $this->accountSearch = '';
    }

    public function selectZohoCustomer($zohoCustomerId)
    {
        $this->customerForm['zoho_customer_id'] = $zohoCustomerId;
        $this->showZohoCustomerDropdown = false;
        $this->zohoCustomerSearch = '';
    }

    public function clearZohoCustomer()
    {
        $this->customerForm['zoho_customer_id'] = null;
        $this->zohoCustomerSearch = '';
    }

    public function getFilteredCountriesProperty()
    {
        if (empty($this->countrySearch)) {
            return collect($this->countries)->take(50);
        }
        
        return collect($this->countries)->filter(function($country) {
            return stripos($country->name ?? '', $this->countrySearch) !== false;
        })->take(50);
    }

    public function getFilteredAccountsProperty()
    {
        if (empty($this->accountSearch)) {
            return collect($this->accounts);
        }
        
        return collect($this->accounts)->filter(function($account) {
            $key = is_object($account) ? $account->key : ($account['key'] ?? '');
            return stripos($key, $this->accountSearch) !== false;
        });
    }

    public function getFilteredZohoCustomersProperty()
    {
        if (empty($this->zohoCustomerSearch)) {
            return collect($this->zohoCustomers)->take(50);
        }
        
        return collect($this->zohoCustomers)->filter(function($zc) {
            return stripos($zc->name ?? '', $this->zohoCustomerSearch) !== false ||
                   stripos($zc->customer_no ?? '', $this->zohoCustomerSearch) !== false;
        })->take(50);
    }

    public function getSelectedCountryNameProperty()
    {
        if ($this->customerForm['country_id']) {
            $country = collect($this->countries)->firstWhere('id', $this->customerForm['country_id']);
            return $country ? ($country->name ?? '') : '';
        }
        return '';
    }

    public function getSelectedAccountNameProperty()
    {
        if ($this->customerForm['account_status']) {
            $account = collect($this->accounts)->firstWhere('id', $this->customerForm['account_status']);
            if ($account) {
                return is_object($account) ? $account->key : ($account['key'] ?? '');
            }
        }
        return '';
    }

    public function getSelectedZohoCustomerNameProperty()
    {
        if ($this->customerForm['zoho_customer_id']) {
            $zc = collect($this->zohoCustomers)->firstWhere('id', $this->customerForm['zoho_customer_id']);
            return $zc ? "{$zc->name} ({$zc->customer_no})" : '';
        }
        return '';
    }

    public function resetDropdownStates()
    {
        $this->countrySearch = '';
        $this->accountSearch = '';
        $this->zohoCustomerSearch = '';
        $this->showCountryDropdown = false;
        $this->showAccountDropdown = false;
        $this->showZohoCustomerDropdown = false;
    }

    public function render()
    {
        return view('livewire.crm.customer-manager', [
            'customers' => $this->customers
        ]);
    }
}

