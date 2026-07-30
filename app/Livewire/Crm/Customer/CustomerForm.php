<?php

namespace App\Livewire\Crm\Customer;

use App\Models\CRM\CRMCustomer;
use App\Country;
use App\Services\Commercial\AccountPaymentTermsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\WithFileUploads;

class CustomerForm extends BaseCrmComponent
{
    use WithFileUploads;

    public $customer = null;
    public $name = '';
    public $code = '';
    public $contact_person = '';
    public $designation = '';
    public $email = '';
    public $physical_address = '';
    public $postal_address = '';
    public $billing_address = '';
    public $website = '';
    public $fax = '';
    public $telephone1 = '';
    public $telephone2 = '';
    public $credit_days = '';
    public $payment_terms_note = '';
    public $payment_method = null;
    public $country_id = '';
    public $active = false;
    public $is_internal = false;
    public $account_status = '';
    public $lpos_required = false;
    public $vat_no = '';
    public $trade_license = '';
    public $contract_valid_from = '';
    public $contract_valid_to = '';
    public $vatRegistrationCertificateFile = null;
    public ?string $existingVatRegistrationCertificateUrl = null;

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
                ->filter(fn ($account) => (bool) data_get($account, 'status', true))
                ->values();
        } else {
            $this->accounts = collect();
        }

        if ($customer) {
            $this->customer = $customer;
            $this->name = $customer->name;
            $this->code = $customer->code;
            $this->contact_person = $customer->contact_person ?? '';
            $this->designation = $customer->designation ?? '';
            $this->email = $customer->email;
            $this->physical_address = $customer->physical_address;
            $this->postal_address = $customer->postal_address;
            $this->billing_address = $customer->billing_address ?? '';
            $this->website = $customer->website;
            $this->fax = $customer->fax;
            $this->telephone1 = $customer->telephone1;
            $this->telephone2 = $customer->telephone2;
            $this->credit_days = $customer->credit_days;
            $this->payment_terms_note = $customer->payment_terms_note ?? '';
            $this->payment_method = $customer->payment_method;
            $this->country_id = $customer->country_id;
            $this->active = (bool) $customer->active;
            $this->is_internal = (bool) ($customer->is_internal ?? false);
            $this->account_status = $customer->account_status;
            $this->lpos_required = (bool) ($customer->lpos_required ?? 0);
            $this->vat_no = $customer->vat_no ?? '';
            $this->trade_license = $customer->trade_license ?? '';
            $this->contract_valid_from = $customer->contract_valid_from ? substr($customer->contract_valid_from, 0, 10) : '';
            $this->contract_valid_to = $customer->contract_valid_to ? substr($customer->contract_valid_to, 0, 10) : '';
            $this->existingVatRegistrationCertificateUrl = $customer->vatRegistrationCertificateUrl();
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

    public function getSelectedAccountTermsProperty(): array
    {
        return app(AccountPaymentTermsService::class)
            ->resolveFromConfigId($this->account_status ? (string) $this->account_status : null);
    }

    public function getPaymentMethodOptionsProperty(): array
    {
        return AccountPaymentTermsService::paymentMethodOptions();
    }

    public function getFilteredAccountsProperty()
    {
        $search = trim(strtolower($this->accountSearch));
        $termsService = app(AccountPaymentTermsService::class);

        return collect($this->accounts)
            ->filter(function ($account) use ($search, $termsService) {
                if ((string) data_get($account, 'id') === (string) $this->account_status) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                $haystack = strtolower(
                    $termsService->displayLabel($account).' '
                    .(string) data_get($account, 'key', '').' '
                    .(string) data_get($account, 'value', '')
                );

                return str_contains($haystack, $search);
            })
            ->values();
    }

    public function selectAccountStatus($accountId)
    {
        $this->account_status = $accountId ? (string) $accountId : '';
        $this->accountSearch = '';
        $this->showAccountDropdown = false;

        if (! $accountId) {
            return;
        }

        $terms = app(AccountPaymentTermsService::class)
            ->resolveFromConfigId((string) $accountId);

        if (! $terms['allows_custom_days']) {
            $this->credit_days = $terms['days'];
            $this->payment_terms_note = '';
            $this->payment_method = $terms['payment_method'];
        } elseif ($terms['billing_type'] !== 'other') {
            $this->payment_terms_note = '';
            $this->payment_method = null;
        }
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

        $terms = app(AccountPaymentTermsService::class)
            ->resolveFromConfigId($this->account_status ? (string) $this->account_status : null);

        return [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'physical_address' => 'required|string',
            'postal_address' => 'nullable|string',
            'billing_address' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'fax' => 'nullable|string|max:255',
            'telephone1' => 'required|string|max:255',
            'telephone2' => 'nullable|string|max:255',
            'credit_days' => $terms['billing_type'] === 'other'
                ? 'required|integer|min:0|max:3650'
                : 'nullable|integer|min:0|max:3650',
            'payment_method' => $terms['billing_type'] === 'other'
                ? ['required', Rule::in(array_keys(AccountPaymentTermsService::paymentMethodOptions()))]
                : 'nullable|string|max:100',
            'payment_terms_note' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'account_status' => ['required', Rule::in($allowedAccountIds)],
            'active' => 'boolean',
            'is_internal' => 'boolean',
            'lpos_required' => 'boolean',
            'vat_no' => 'nullable|string|max:100',
            'trade_license' => 'nullable|string|max:255',
            'contract_valid_from' => 'nullable|date',
            'contract_valid_to' => 'nullable|date',
            'vatRegistrationCertificateFile' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240',
        ];
    }

    public function save()
    {
        $this->account_settings = getConfigTypeByName('Account Settings');
        if (isset($this->account_settings->id)) {
            $this->accounts = collect(getconfigByID($this->account_settings->id))
                ->filter(fn ($account) => (bool) data_get($account, 'status', true))
                ->values();
        }

        if (collect($this->accounts)->isEmpty()) {
            $this->addError(
                'account_status',
                'Account settings are not configured. Ask an administrator to set up Account Settings under System Configuration.'
            );

            return;
        }

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
        $customer->contact_person = $this->contact_person;
        $customer->designation = $this->designation;
        $customer->physical_address = $this->physical_address;
        $customer->postal_address = $this->postal_address;
        $customer->billing_address = $this->billing_address;
        $customer->company_id = $this->getUserCompany();
        $customer->website = $this->website;
        $customer->email = $this->email;
        $customer->fax = $this->fax;
        $customer->telephone1 = $this->telephone1;
        $customer->telephone2 = $this->telephone2;
        $customer->country_id = $this->country_id;
        $customer->active = $this->active ? 1 : 0;
        $customer->is_internal = $this->is_internal ? 1 : 0;
        $customer->account_status = $this->account_status;
        $customer->lpos_required = $this->lpos_required ? 1 : 0;
        $customer->vat_no = $this->vat_no;
        $customer->trade_license = $this->trade_license;
        $customer->contract_valid_from = $this->contract_valid_from ?: null;
        $customer->contract_valid_to = $this->contract_valid_to ?: null;

        app(AccountPaymentTermsService::class)->syncCustomerFromAccountSetting(
            $customer,
            $this->account_status ? (string) $this->account_status : null,
            is_numeric($this->credit_days) ? (int) $this->credit_days : null,
            $this->payment_terms_note,
            $this->payment_method
        );

        $customer->save();

        if ($this->vatRegistrationCertificateFile) {
            $this->storeVatRegistrationCertificate($customer);
        }

        $this->showSuccess($this->customer ? 'Customer edited successfully.' : 'Customer added successfully.');
        $this->dispatch('customer-saved');
        $this->close();
    }

    public function removeVatRegistrationCertificate(): void
    {
        if (! $this->customer) {
            $this->vatRegistrationCertificateFile = null;
            $this->existingVatRegistrationCertificateUrl = null;

            return;
        }

        $this->checkPermission('crm.components.customer-list.edit');

        if (filled($this->customer->vat_registration_certificate)) {
            $relative = ltrim(preg_replace('#^/storage/#', '', (string) $this->customer->vat_registration_certificate), '/');
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
            $this->customer->vat_registration_certificate = null;
            $this->customer->save();
        }

        $this->vatRegistrationCertificateFile = null;
        $this->existingVatRegistrationCertificateUrl = null;
    }

    protected function storeVatRegistrationCertificate(CRMCustomer $customer): void
    {
        $previousRelative = null;
        if (filled($customer->vat_registration_certificate)) {
            $previousRelative = ltrim(preg_replace('#^/storage/#', '', (string) $customer->vat_registration_certificate), '/');
        }

        $extension = strtolower((string) $this->vatRegistrationCertificateFile->getClientOriginalExtension());
        $storedName = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '.pdf');
        $path = $this->vatRegistrationCertificateFile->storeAs('crm-customer-vat-certificates', $storedName, 'public');

        $customer->vat_registration_certificate = '/storage/' . $path;
        $customer->save();

        if ($previousRelative && $previousRelative !== $path && Storage::disk('public')->exists($previousRelative)) {
            Storage::disk('public')->delete($previousRelative);
        }
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
