<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePortalAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name_or_organisation' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'zone' => ['required', 'string', 'max:255'],
            'tin_number' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:50'],
        ];
    }
}
