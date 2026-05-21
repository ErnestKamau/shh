<?php

namespace App\Http\Requests\Registry;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRegistryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registry.components.approval queue.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'comment' => ['nullable', 'string', 'max:2000'],
            'action_name' => ['nullable', 'in:approve,reject'],
        ];
    }
}
