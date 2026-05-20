<?php

namespace App\Http\Requests\Registry;

use Illuminate\Foundation\Http\FormRequest;

class RejectRegistryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registry.components.approval queue.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:2000'],
        ];
    }
}
