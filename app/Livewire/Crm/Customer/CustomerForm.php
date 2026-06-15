<?php

namespace App\Livewire\Crm\Customer;

use App\Models\CRM\CRMCustomer;
use App\Country;
use Illuminate\Validation\Rule;
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
    public $contract_valid_from = '';
    public $contract_valid_to = '';

    public $countries = [];
    public $accounts = [];
    public $account_settings = null;
    public $countrySearch = '';
    public $showCountryDropdown = false;
    public $accountSearch = '';
    public $showAccountDropdown = false;

    public function mount($customer = null, $countries = [], $accounts = [], $account_settings = null)
    {
        $this->initialize();

        // Load countries - fallback to database if not provided
        if (!empty($countries)) {
            $this->countries = $countries;
        } else {
            $this->countries = \App\Country::orderBy('name')->get();
        }

        // Load account settings - always fetch fresh from config
        $this->account_settings = getConfigTypeByName('Account Settings');
        if (isset($this->account_settings->id)) {
            $rawAccounts = getconfigByID($this->account_settings->id);
            $this->accounts = collect($rawAccounts)
                ->values();
        } else {
            $this->accounts = collect();
        }

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
            $this->active = (bool) $customer->active;
            $this->is_internal = (bool) ($customer->is_internal ?? false);
            $this->account_status = $customer->account_status;
            $this->lpos_required = (bool) ($customer->lpos_required ?? 0);
            $this->contract_valid_from = $customer->contract_valid_from ? substr($customer->contract_valid_from, 0, 10) : '';
            $this->contract_valid_to = $customer->contract_valid_to ? substr($customer->contract_valid_to, 0, 10) : '';
        }
    }

    public function getSelectedCountryProperty()
    {
        return collect($this->countries)->firstWhere('id', (int) $this->country_id);
    }

    public function getFilteredCountriesProperty()
    {
        $search = trim(strtolower($this->countrySearch));

        return collect($this->countries)
            ->filter(function ($country) use ($search) {
                if ((string) data_get($country, 'id') === (string) $this->country_id) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower((string) data_get($country, 'name', '')), $search);
            })
            ->values();
    }

    public function selectCountry($countryId)
    {
        $this->country_id = $countryId ? (string) $countryId : '';
        $this->countrySearch = '';
        $this->showCountryDropdown = false;
    }

    public function clearCountry()
    {
        $this->country_id = '';
        $this->countrySearch = '';
        $this->showCountryDropdown = false;
    }

    public function getSelectedAccountProperty()
    {
        if (empty($this->account_status)) {
            return null;
        }

        return collect($this->accounts)->firstWhere('id', (string) $this->account_status);
    }

    public function getFilteredAccountsProperty()
    {
        $search = trim(strtolower($this->accountSearch));

        \Log::info('CustomerForm: getFilteredAccountsProperty called', [
            'accounts_count' => collect($this->accounts)->count(),
            'showAccountDropdown' => $this->showAccountDropdown,
            'accountSearch' => $this->accountSearch,
            'account_status' => $this->account_status,
            'accounts_raw' => $this->accounts
        ]);

        return collect($this->accounts)
            ->filter(function ($account) use ($search) {
                if ((string) data_get($account, 'id') === (string) $this->account_status) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower((string) data_get($account, 'key', '')), $search);
            })
            ->values();
    }

    public function selectAccountStatus($accountId)
    {
        $this->account_status = $accountId ? (string) $accountId : '';
        $this->accountSearch = '';
        $this->showAccountDropdown = false;
    }

    public function clearAccountStatus()
    {
        $this->account_status = '';
        $this->accountSearch = '';
        $this->showAccountDropdown = false;
    }

    protected function rules()
    {
        $allowedAccountIds = collect($this->accounts)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

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
            'account_status' => ['required', Rule::in($allowedAccountIds)],
            'active' => 'boolean',
            'is_internal' => 'boolean',
            'lpos_required' => 'boolean',
            'contract_valid_from' => 'nullable|date',
            'contract_valid_to' => 'nullable|date',
        ];
    }

    public function save()
    {
        $this->validate();

        if ($this->customer) {
            $this->checkPermission('crm.components.customer-list.edit');
            $customer = $this->customer;
        } else {
            $this->checkPermission('crm.components.customer-list.add');
            $customer = new CRMCustomer();
            $customer->code = getNamingConventionCode('Customers', $this->name);
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
        $customer->active = $this->active ? 1 : 0;
        $customer->is_internal = $this->is_internal ? 1 : 0;
        $customer->account_status = $this->account_status;
        $customer->lpos_required = $this->lpos_required ? 1 : 0;
        $customer->contract_valid_from = $this->contract_valid_from ?: null;
        $customer->contract_valid_to = $this->contract_valid_to ?: null;

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
