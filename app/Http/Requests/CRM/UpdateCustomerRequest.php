<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'postal_address' => ['nullable', 'string'],
            'physical_address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'vat_no' => ['nullable', 'string', 'max:100'],
            'fax' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email'],
            'phone1' => ['required', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'credit_day' => ['nullable', 'integer', 'min:0'],
            'account_id' => ['nullable', 'integer'],
            'active' => ['nullable', 'boolean'],
            'lpos_required' => ['nullable', 'boolean'],
            'zoho_code' => ['nullable'],
            'currency_id' => ['nullable', 'integer'],
            'show_limits' => ['nullable', 'integer', 'in:0,1'],
            'show_lod' => ['nullable', 'integer', 'in:0,1'],
            'show_test_conformance' => ['nullable', 'integer', 'in:0,1'],
            'show_grade' => ['nullable', 'integer', 'in:0,1'],
            'contract_valid_from' => ['nullable', 'date'],
            'contract_valid_to' => ['nullable', 'date'],
        ];
    }
}
