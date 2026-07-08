<?php

namespace App\Http\Requests\Registry;

use Illuminate\Foundation\Http\FormRequest;

class AssignRegistryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registry.components.assignments.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => ['required', 'uuid', 'exists:users,id'],
            'role_context' => ['nullable', 'string', 'max:100'],
        ];
    }
}
