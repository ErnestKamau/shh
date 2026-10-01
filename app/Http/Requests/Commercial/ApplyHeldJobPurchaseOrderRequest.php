<?php

namespace App\Http\Requests\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;

class ApplyHeldJobPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_APPLY_TO_HELD_JOB);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'sample_header_id' => ['required', 'uuid', 'exists:sample_headers,id'],
            'customer_purchase_order_id' => ['required', 'uuid', 'exists:customer_purchase_orders,id'],
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
            'customer_purchase_order_id.required' => 'Select the purchase order that covers these samples.',
            'customer_purchase_order_id.uuid' => 'Select the purchase order that covers these samples.',
            'customer_purchase_order_id.exists' => 'The selected PO no longer exists.',
        ];
    }
}
