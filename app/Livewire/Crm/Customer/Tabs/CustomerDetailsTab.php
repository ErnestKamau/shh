<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Services\Commercial\AccountPaymentTermsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class CustomerDetailsTab extends BaseCrmComponent
{
    use WithFileUploads;

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
    public $payment_terms_note = '';
    public $payment_method = null;
    public $country_id;
    public $active;
    public $lpos_required;
    public $is_internal;
    public $account_status;
    public $accounts = [];
    public $account_settings = null;
    public $countrySearch = '';
    public $accountSearch = '';
    public $showCountryDropdown = false;
    public $showAccountDropdown = false;

    public $logoFile = null;

    public function mount($customer)
    {
        $this->customer = $customer->fresh(['country']);
        $this->countries = \App\Country::orderBy('name')->get();

        $this->account_settings = getConfigTypeByName('Account Settings');
        if (isset($this->account_settings->id)) {
            $this->accounts = collect(getconfigByID($this->account_settings->id))
                ->filter(fn ($account) => (bool) data_get($account, 'status', true))
                ->values();
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
        $this->payment_terms_note = $this->customer->payment_terms_note ?? '';
        $this->payment_method = $this->customer->payment_method;
        $this->country_id = $this->customer->country_id;
        $this->active = (bool) $this->customer->active;
        $this->lpos_required = (bool) $this->customer->lpos_required;
        $this->is_internal = (bool) ($this->customer->is_internal ?? false);
        $this->account_status = $this->customer->account_status;
        $this->logoFile = null;
    }

    public function cancel()
    {
        $this->isEditing = false;
        $this->logoFile = null;
        $this->resetValidation();
    }

    public function getSelectedCountryProperty()
    {
        return collect($this->countries)->firstWhere('id', (int) $this->country_id);
    }

    public function getFilteredCountriesProperty()
    {
        $search = trim(strtolower($this->countrySearch));

        return collect($this->countries)
            ->when($search !== '', function ($countries) use ($search) {
                return $countries->filter(function ($country) use ($search) {
                    return str_contains(strtolower((string) data_get($country, 'name', '')), $search);
                });
            })
            ->values();
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
            ->when($search !== '', function ($accounts) use ($search, $termsService) {
                return $accounts->filter(function ($account) use ($search, $termsService) {
                    $haystack = strtolower(
                        $termsService->displayLabel($account).' '
                        .(string) data_get($account, 'key', '').' '
                        .(string) data_get($account, 'value', '')
                    );

                    return str_contains($haystack, $search);
                });
            })
            ->take(50)
            ->values();
    }

    public function selectCountry($countryId)
    {
        $this->country_id = (string) $countryId;
        $this->countrySearch = '';
        $this->showCountryDropdown = false;
    }

    public function clearCountry()
    {
        $this->country_id = '';
        $this->countrySearch = '';
    }

    public function selectAccountStatus($accountId)
    {
        $this->account_status = (string) $accountId;
        $this->accountSearch = '';
        $this->showAccountDropdown = false;

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
    }

    protected function rules()
    {
        $terms = app(AccountPaymentTermsService::class)
            ->resolveFromConfigId($this->account_status ? (string) $this->account_status : null);

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'physical_address' => 'required|string',
            'postal_address' => 'nullable|string',
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
            'active' => 'boolean',
            'lpos_required' => 'boolean',
            'is_internal' => 'boolean',
            'account_status' => 'nullable|exists:system_configurations,id',
            'logoFile' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
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
        $this->customer->country_id = $this->country_id;
        $this->customer->active = $this->active ? 1 : 0;
        $this->customer->lpos_required = $this->lpos_required ? 1 : 0;
        $this->customer->is_internal = $this->is_internal ? 1 : 0;
        $this->customer->account_status = $this->account_status;

        app(AccountPaymentTermsService::class)->syncCustomerFromAccountSetting(
            $this->customer,
            $this->account_status ? (string) $this->account_status : null,
            is_numeric($this->credit_days) ? (int) $this->credit_days : null,
            $this->payment_terms_note,
            $this->payment_method
        );

        if ($this->logoFile) {
            $this->storeCustomerLogo();
        }

        $this->customer->save();
        $this->customer = $this->customer->fresh(['country']);

        $this->showSuccess('Customer updated successfully.');
        $this->isEditing = false;
        $this->logoFile = null;
        $this->dispatch('customer-updated');
    }

    public function removeLogo(): void
    {
        $this->checkPermission('crm.components.customer-list.edit');

        if (filled($this->customer->logo)) {
            $relative = ltrim(preg_replace('#^/storage/#', '', (string) $this->customer->logo), '/');
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
            $this->customer->logo = null;
            $this->customer->save();
            $this->customer = $this->customer->fresh(['country']);
        }

        $this->logoFile = null;
        $this->dispatch('customer-updated');
        $this->showSuccess(__('crm.logo_removed'));
    }

    protected function storeCustomerLogo(): void
    {
        $previousRelative = null;
        if (filled($this->customer->logo)) {
            $previousRelative = ltrim(preg_replace('#^/storage/#', '', (string) $this->customer->logo), '/');
        }

        $extension = strtolower((string) $this->logoFile->getClientOriginalExtension());
        $storedName = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '.png');
        $path = $this->logoFile->storeAs('crm-customer-logos', $storedName, 'public');

        $this->customer->logo = '/storage/' . $path;

        if ($previousRelative && $previousRelative !== $path && Storage::disk('public')->exists($previousRelative)) {
            Storage::disk('public')->delete($previousRelative);
        }
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-details-tab');
    }
}
