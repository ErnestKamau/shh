<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'postal_address' => ['nullable', 'string'],
            'physical_address' => ['required', 'string', 'max:500'],
            'website' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'string', 'exists:countries,id'],
            'city_id' => ['nullable', 'string', 'exists:cities,id'],
            'vat_no' => ['nullable', 'string', 'max:100'],
            'trade_license' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:1000'],
            'fax' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email'],
            'phone1' => ['required', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'credit_day' => ['nullable', 'integer', 'min:0'],
            'account_id' => ['required', 'integer'],
            'active' => ['nullable', 'boolean'],
            'lpos_required' => ['nullable', 'boolean'],
            'zoho_code' => ['nullable'],
            'currency_id' => ['nullable', 'integer'],
            'contract_valid_from' => ['nullable', 'date'],
            'contract_valid_to' => ['nullable', 'date'],
        ];
    }
}
