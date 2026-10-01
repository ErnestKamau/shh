<?php

namespace App\Http\Requests\Commercial;

use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderInvoicingPeriod;
use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerPurchaseOrderDetailsRequest extends FormRequest
{
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
            'po_number' => ['required', 'string', 'max:255'],
            'invoicing_mode' => ['required', Rule::enum(PurchaseOrderInvoicingMode::class)],
            'invoicing_period' => ['nullable', 'required_if:invoicing_mode,'.PurchaseOrderInvoicingMode::Periodic->value, Rule::enum(PurchaseOrderInvoicingPeriod::class)],
            'expiry_notice_days' => ['required', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'line_thresholds' => ['nullable', 'array'],
            'line_thresholds.*' => ['nullable', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'po_number.required' => 'The PO number cannot be empty.',
            'invoicing_period.required_if' => 'Choose how often periodic invoices are raised.',
            'file.mimes' => 'The PO file must be a PDF, JPEG or PNG.',
            'file.max' => 'The PO file must not be larger than 10 MB.',
            'line_thresholds.*.integer' => 'Alert levels must be whole numbers.',
            'reason.required' => 'Give a reason for this change.',
        ];
    }
}
