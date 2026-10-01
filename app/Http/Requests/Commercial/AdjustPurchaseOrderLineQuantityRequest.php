<?php

namespace App\Http\Requests\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustPurchaseOrderLineQuantityRequest extends FormRequest
{
    public const DIRECTION_TOP_UP = 'top_up';

    public const DIRECTION_REDUCE = 'reduce';

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_AMEND);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'line_id' => ['required', 'uuid', 'exists:customer_purchase_order_lines,id'],
            'direction' => ['required', Rule::in([self::DIRECTION_TOP_UP, self::DIRECTION_REDUCE])],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000000'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Enter the quantity to add or remove.',
            'quantity.min' => 'The quantity must be at least 1.',
            'reason.required' => 'Give a reason for this change (e.g. the customer\'s amended PO reference).',
        ];
    }
}
