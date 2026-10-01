<?php

namespace App\Http\Requests\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinalisePurchaseOrderRequest extends FormRequest
{
    public const ACTION_CLOSE = 'close';

    public const ACTION_CANCEL = 'cancel';

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_CLOSE);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([self::ACTION_CLOSE, self::ACTION_CANCEL])],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Give a reason for closing or cancelling this purchase order.',
            'reason.min' => 'The reason must be at least 3 characters.',
        ];
    }
}
