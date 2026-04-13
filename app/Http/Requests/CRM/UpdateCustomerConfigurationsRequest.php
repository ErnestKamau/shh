<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerConfigurationsRequest extends FormRequest
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
            'show_limits' => ['nullable', 'integer', 'in:0,1'],
            'show_lod' => ['nullable', 'integer', 'in:0,1'],
            'show_test_conformance' => ['nullable', 'integer', 'in:0,1'],
            'show_grade' => ['nullable', 'integer', 'in:0,1'],
            'label_limits' => ['nullable', 'string', 'max:255'],
            'label_test_conformance' => ['nullable', 'string', 'max:255'],
            'show_standards_below_limits' => ['nullable', 'boolean'],
            'standards_to_show' => ['nullable', 'array'],
            'standards_to_show.*' => ['integer'],
            'info_columns' => ['nullable', 'array'],
            'info_columns.*' => ['string', 'max:500'],
        ];
    }
}
