<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;

class CustomerDetailsTab extends BaseCrmComponent
{
    public $customer;
    public $isEditing = false;
    public $countries = [];

    // Editable Properties
    public $name;
    public $email;
    public $physical_address;
    public $postal_address;
    public $website;
    public $fax;
    public $telephone1;
    public $telephone2;
    public $credit_days;
    public $country_id;
    public $active;
    public $lpos_required;
    public $is_internal;
    public $account_status;
    public $accounts = [];
    public $account_settings = null;

    public function mount($customer)
    {
        $this->customer = $customer;
        $this->countries = \App\Country::all();
        
        // Load Account Settings
        $this->account_settings = getConfigTypeByName('Account Settings');
        if (isset($this->account_settings->id)) {
            $this->accounts = getconfigByID($this->account_settings->id);
        }
    }

    public function edit()
    {
        $this->isEditing = true;
        
        $this->name = $this->customer->name;
        $this->email = $this->customer->email;
        $this->physical_address = $this->customer->physical_address;
        $this->postal_address = $this->customer->postal_address;
        $this->website = $this->customer->website;
        $this->fax = $this->customer->fax;
        $this->telephone1 = $this->customer->telephone1;
        $this->telephone2 = $this->customer->telephone2;
        $this->credit_days = $this->customer->credit_days;
        $this->country_id = $this->customer->country_id;
        // Cast to boolean
        $this->active = (bool) $this->customer->active;
        $this->lpos_required = (bool) $this->customer->lpos_required;
        $this->is_internal = (bool) ($this->customer->is_internal ?? false);
        $this->account_status = $this->customer->account_status;
    }

    public function cancel()
    {
        $this->isEditing = false;
        $this->resetValidation();
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
            'active' => 'boolean',
            'lpos_required' => 'boolean',
            'is_internal' => 'boolean',
            'account_status' => 'nullable|exists:system_configurations,id',
        ];
    }

    public function save()
    {
        $this->checkPermission('crm.components.customer-list.edit');
        $this->validate();

        $this->customer->name = $this->name;
        $this->customer->email = $this->email;
        $this->customer->physical_address = $this->physical_address;
        $this->customer->postal_address = $this->postal_address;
        $this->customer->website = $this->website;
        $this->customer->fax = $this->fax;
        $this->customer->telephone1 = $this->telephone1;
        $this->customer->telephone2 = $this->telephone2;
        $this->customer->credit_days = $this->credit_days;
        $this->customer->country_id = $this->country_id;
        // Cast back to integer
        $this->customer->active = $this->active ? 1 : 0;
        $this->customer->lpos_required = $this->lpos_required ? 1 : 0;
        $this->customer->is_internal = $this->is_internal ? 1 : 0;
        $this->customer->account_status = $this->account_status;

        $this->customer->save();

        $this->showSuccess('Customer updated successfully.');
        $this->isEditing = false;
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-details-tab');
    }
}
