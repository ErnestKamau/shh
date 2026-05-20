<?php

namespace App\Http\Requests\Registry;

use Illuminate\Foundation\Http\FormRequest;

class CreateRegistryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registry.components.requests.add') ?? false;
    }

    public function rules(): array
    {
        return [
            'request_category_id' => ['required', 'uuid', 'exists:registry_request_categories,id'],
            'subject' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'direction' => ['nullable', 'in:incoming,outgoing'],
            'submitting_party' => ['nullable', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:255'],
            'entity_id' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
