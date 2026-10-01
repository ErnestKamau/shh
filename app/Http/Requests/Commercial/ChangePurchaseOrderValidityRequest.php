<?php

namespace App\Http\Requests\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;

class ChangePurchaseOrderValidityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_AMEND);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'valid_to.required' => 'Enter the new expiry date.',
            'valid_to.after_or_equal' => 'The expiry date must be on or after the start date.',
            'reason.required' => 'Give a reason for changing the validity.',
        ];
    }
}
