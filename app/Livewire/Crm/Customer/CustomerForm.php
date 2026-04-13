<?php

namespace App\Livewire\Crm\Customer;

use App\Models\CRM\CRMCustomer;
use App\Country;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;

class CustomerForm extends BaseCrmComponent
{
    public $customer = null;
    public $name = '';
    public $code = '';
    public $email = '';
    public $physical_address = '';
    public $postal_address = '';
    public $website = '';
    public $fax = '';
    public $telephone1 = '';
    public $telephone2 = '';
    public $credit_days = '';
    public $country_id = '';
    public $active = false;

    public $is_internal = false;

    public $account_status = '';
    public $lpos_required = false;
    
    public $countries = [];
    public $accounts = [];
    public $account_settings = null;

    public function mount($customer = null, $countries = [], $accounts = [], $account_settings = null)
    {
        $this->initialize();
        $this->countries = $countries;
        $this->accounts = $accounts;
        $this->account_settings = $account_settings;
        
        if ($customer) {
            $this->customer = $customer;
            $this->name = $customer->name;
            $this->code = $customer->code;
            $this->email = $customer->email;
            $this->physical_address = $customer->physical_address;
            $this->postal_address = $customer->postal_address;
            $this->website = $customer->website;
            $this->fax = $customer->fax;
            $this->telephone1 = $customer->telephone1;
            $this->telephone2 = $customer->telephone2;
            $this->credit_days = $customer->credit_days;
            $this->country_id = $customer->country_id;
            // Cast to boolean for Livewire binding
            $this->active = (bool) $customer->active;
            $this->is_internal = (bool) ($customer->is_internal ?? false);
            $this->account_status = $customer->account_status;
            // Cast to boolean
            $this->lpos_required = (bool) ($customer->lpos_required ?? 0);
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'physical_address' => 'required|string',
            'postal_address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'fax' => 'nullable|string|max:255',
            'telephone1' => 'required|string|max:255',
            'telephone2' => 'nullable|string|max:255',
            'credit_days' => 'nullable|integer',
            'country_id' => 'nullable|exists:countries,id',
            'account_status' => 'required|exists:system_configurations,id',
            'active' => 'boolean',
            'is_internal' => 'boolean',
            'lpos_required' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        if ($this->customer) {
            // Edit mode
            $this->checkPermission('CRM.components.Customer-List.Edit');
            $customer = $this->customer;
        } else {
            // Add mode
            $this->checkPermission('CRM.components.Customer-List.Add');
            $customer = new CRMCustomer();
            $customer->code = getNamingConventionCode("Customers", $this->name);
        }

        $customer->name = $this->name;
        $customer->physical_address = $this->physical_address;
        $customer->postal_address = $this->postal_address;
        $customer->company_id = $this->getUserCompany();
        $customer->website = $this->website;
        $customer->email = $this->email;
        $customer->fax = $this->fax;
        $customer->telephone1 = $this->telephone1;
        $customer->telephone2 = $this->telephone2;
        $customer->credit_days = $this->credit_days;
        $customer->country_id = $this->country_id;
        // Cast back to integer/boolean as needed (Laravel usually handles true -> 1)
        $customer->active = $this->active ? 1 : 0;
        $customer->is_internal = $this->is_internal ? 1 : 0;
        $customer->account_status = $this->account_status;
        $customer->lpos_required = $this->lpos_required ? 1 : 0;
        
        $customer->save();

        $this->showSuccess($this->customer ? 'Customer edited successfully.' : 'Customer added successfully.');
        $this->dispatch('customer-saved');
        $this->close();
    }

    public function close()
    {
        $this->dispatch('customer-form-closed');
    }

    public function render()
    {
        return view('livewire.crm.customer.customer-form');
    }
}

