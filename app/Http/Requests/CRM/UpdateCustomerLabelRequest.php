<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerLabelRequest extends FormRequest
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
            'column' => [
                'required',
                'string',
                Rule::in([
                    'unit_configurable_name',
                    'sample_point_configurable_name',
                    'product_configurable_name',
                ]),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
