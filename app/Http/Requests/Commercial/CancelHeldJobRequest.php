<?php

namespace App\Http\Requests\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;

class CancelHeldJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_CANCEL_HELD_JOB);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'sample_header_id' => ['required', 'uuid', 'exists:sample_headers,id'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sample_header_id.required' => 'Select the held job.',
            'sample_header_id.exists' => 'The held job no longer exists.',
            'reason.required' => 'Give a reason for cancelling the held job (e.g. the customer will not issue a PO; samples returned).',
            'reason.min' => 'The reason must be at least 3 characters.',
        ];
    }
}
