<?php

namespace App\Http\Requests\Api\Portal\Submissions;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionFormInstanceRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'portal_account_id' => ['nullable', 'uuid'],
            'crm_customer_id' => ['nullable', 'uuid'],
            'portal_request_id' => ['nullable', 'uuid'],
            'sample_type_id' => ['nullable', 'uuid'],
            'priority' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
