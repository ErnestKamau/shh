<?php

namespace App\Livewire\Crm\Customer;

use App\Models\CRM\CRMCustomer;
use App\Country;
use App\Models\System\SystemConfiguration;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use Illuminate\Support\Facades\Auth;

class CustomerList extends BaseCrmComponent
{
    public $search = '';
    public $activeFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $selectedCustomers = [];
    public $showForm = false;
    public $editingCustomer = null;
    public $countries;
    public $accounts = [];
    public $account_settings;
    public $allCustomers = [];
    public $showFilters = false;
    public $perPage = 10;
    public $customerIdToDelete = null;

    public $visibleColumns = [
        'postal_address' => true,
        'physical_address' => true,
        'website' => true,
        'fax' => true,
        'phone1' => true,
        'phone2' => true,
        'country' => true,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'activeFilter' => ['except' => ''],
        'startDate' => ['except' => '', 'as' => 'start'],
        'endDate' => ['except' => '', 'as' => 'end'],
    ];



    public function mount()
    {
        $this->initialize();
        $this->checkPermission('crm.permission');

        // Check if user is from lab department (skip if lab_department_id not configured)
        $lab_department = SystemConfiguration::where('key', 'lab_department_id')->first();
        if ($lab_department !== null && Auth::user()->department_id != $lab_department->value) {
            return redirect()->route('client-dashboard-home', ['isKecu' => 'true']);
        }

        $this->countries = Country::orderBy('name')->get();
        $this->account_settings = getConfigTypeByName('Account Settings');
        if (isset($this->account_settings->id)) {
            $this->accounts = getconfigByID($this->account_settings->id);
        }

        // Load all customers for filter dropdown
        $this->allCustomers = CRMCustomer::where('company_id', $this->getUserCompany())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getCustomersProperty()
    {
        $query = CRMCustomer::where('company_id', $this->getUserCompany())
            ->with('country');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->activeFilter !== '') {
            $query->where('active', $this->activeFilter);
        }

        // Date range filter
        if ($this->startDate) {
            $query->where('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->where('created_at', '<=', $this->endDate . ' 23:59:59');
        }

        // Customer filter (for filtering by specific customers)
        if (!empty($this->selectedCustomers)) {
            $query->whereIn('id', $this->selectedCustomers);
        }

        return $query->orderByRaw('active DESC')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function toggleColumn($column)
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = !$this->visibleColumns[$column];
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->activeFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->selectedCustomers = [];
        $this->resetPage();
    }

    public function openAddForm()
    {
        $this->editingCustomer = null;
        $this->showForm = true;
        $this->dispatch('show-customer-form');
    }

    public function openEditForm($customerId)
    {
        $this->editingCustomer = CRMCustomer::find($customerId);
        $this->showForm = true;
        $this->dispatch('show-customer-form');
    }

    #[On('customer-saved')]
    #[On('customer-form-closed')]
    #[On('customer-created')]
    #[On('customer-updated')]
    public function closeForm()
    {
        $this->showForm = false;
        $this->editingCustomer = null;
        $this->dispatch('hide-customer-form');
    }







    public function updatedActiveFilter()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->customerIdToDelete = $id;
        $this->dispatch('show-customer-delete-modal');
    }

    public function cancelDelete()
    {
        $this->customerIdToDelete = null;
        $this->dispatch('hide-customer-delete-modal');
    }

    public function deleteCustomer()
    {
        $id = $this->customerIdToDelete;
        $customer = CRMCustomer::find($id);

        if ($customer) {
            $customer->delete();
            $this->showSuccess('Customer deleted successfully.');
            $this->dispatch('hide-customer-delete-modal');
            $this->customerIdToDelete = null;
        } else {
            $this->showError('Customer not found.');
        }
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');

        $filters = [
            'company_id' => $this->getUserCompany(),
            'search' => $this->search,
            'activeFilter' => $this->activeFilter,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'selectedCustomers' => $this->selectedCustomers,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        return (new \App\Exports\CRM\CustomersExport($filters))->download('crm_customers_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        $companyId = $this->getUserCompany();
        $activeCount = CRMCustomer::where('company_id', $companyId)->where('active', '1')->count();
        $inactiveCount = CRMCustomer::where('company_id', $companyId)->where('active', '0')->count();

        return view('livewire.crm.customer.customer-list', [
            'customers' => $this->customers,
            'countries' => $this->countries,
            'accounts' => $this->accounts,
            'account_settings' => $this->account_settings,
            'allCustomers' => $this->allCustomers,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
        ])->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => true])
            ->section('content2');
    }
}
