<?php

namespace App\Http\Requests\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\Services\Commercial\EnquiryPurchaseOrderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeEnquiryPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CustomerPurchaseOrder::PERMISSION_CHANGE_AT_RECEPTION);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in([
                EnquiryPurchaseOrderService::MODE_BLANKET,
                EnquiryPurchaseOrderService::MODE_SINGLE,
                EnquiryPurchaseOrderService::MODE_NONE,
            ])],
            'customer_purchase_order_id' => [
                'nullable',
                'required_if:mode,'.EnquiryPurchaseOrderService::MODE_BLANKET,
                'uuid',
                'exists:customer_purchase_orders,id',
            ],
            'client_po_number' => [
                'nullable',
                'required_if:mode,'.EnquiryPurchaseOrderService::MODE_SINGLE,
                'string',
                'max:255',
            ],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mode.required' => 'Choose how this request is covered.',
            'customer_purchase_order_id.required_if' => 'Select the purchase order.',
            'customer_purchase_order_id.exists' => 'The selected PO no longer exists.',
            'client_po_number.required_if' => 'Enter the customer\'s PO number.',
            'file.mimes' => 'The PO document must be a PDF, JPEG or PNG.',
            'file.max' => 'The PO document may not be larger than 10 MB.',
            'reason.required' => 'Give a reason for changing the PO (e.g. the customer sent a corrected PO).',
            'reason.min' => 'The reason must be at least 3 characters.',
        ];
    }
}
